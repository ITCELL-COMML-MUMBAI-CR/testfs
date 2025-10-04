<?php
/**
 * Workflow Engine for SAMPARK
 * Handles ticket workflow, state transitions, and automated processes
 */

require_once 'NotificationService.php';
require_once 'ActivityLogger.php';
require_once __DIR__ . '/../config/Config.php';

class WorkflowEngine {
    
    private $db;
    private $notificationService;
    private $logger;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->notificationService = new NotificationService();
        $this->logger = new ActivityLogger();
    }
    
    /**
     * Process ticket workflow based on current state and action
     */
    public function processTicketWorkflow($complaintId, $action, $userId, $userType, $data = [], $skipTransaction = false) {
        try {
            $transactionStarted = false;
            if (!$skipTransaction) {
                $this->db->beginTransaction();
                $transactionStarted = true;
            }
            
            // Get current ticket state
            $ticket = $this->getTicketDetails($complaintId);
            if (!$ticket) {
                throw new Exception("Ticket not found: {$complaintId}");
            }
            
            // Validate state transition
            $transition = $this->validateStateTransition($ticket['status'], $action, $userType);
            if (!$transition['valid']) {
                throw new Exception($transition['error']);
            }
            
            // Execute workflow action
            $result = $this->executeWorkflowAction($ticket, $action, $userId, $userType, $data);
            
            if ($result['success']) {
                // Update ticket state
                $this->updateTicketState($complaintId, $result['new_status'], $result['updates']);
                
                // Log workflow action
                $this->logWorkflowAction($complaintId, $action, $userId, $userType, $result);
                
                // Process automated actions
                $this->processAutomatedActions($ticket, $result['new_status'], $action);
                
                // Send notifications
                $this->sendWorkflowNotifications($ticket, $action, $result, $userId, $userType);
                
                if ($transactionStarted) {
                    $this->db->commit();
                }
                
                return [
                    'success' => true,
                    'new_status' => $result['new_status'],
                    'message' => $result['message'] ?? 'Workflow action completed successfully'
                ];
            } else {
                if ($transactionStarted) {
                    $this->db->rollback();
                }
                return [
                    'success' => false,
                    'error' => $result['error'] ?? 'Workflow action failed'
                ];
            }
            
        } catch (Exception $e) {
            if ($transactionStarted) {
                $this->db->rollback();
            }
            error_log("Workflow error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Auto-escalate tickets based on priority time rules
     */
    public function processAutoEscalation() {
        try {
            $escalatedTickets = [];

            // Process priority escalation based on time rules
            $priorityResult = $this->processPriorityEscalation();
            if ($priorityResult['success']) {
                $escalatedTickets = array_merge($escalatedTickets, $priorityResult['escalated_tickets']);
            }

            return [
                'success' => true,
                'escalated_tickets' => $escalatedTickets,
                'total_processed' => count($escalatedTickets)
            ];

        } catch (Exception $e) {
            error_log("Auto-escalation error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Process automatic ticket closure for awaiting feedback tickets
     */
    public function processAutoClose() {
        try {
            $autoCloseDays = $this->getSetting('auto_close_days', 3);
            $closedTickets = [];
            
            // Get tickets awaiting feedback beyond auto-close period
            $sql = "SELECT c.*, cust.name as customer_name, cust.email as customer_email
                    FROM complaints c
                    LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                    WHERE c.status = 'awaiting_feedback'
                      AND TIMESTAMPDIFF(DAY, c.updated_at, NOW()) >= ?";
            
            $ticketsToClose = $this->db->fetchAll($sql, [$autoCloseDays]);
            
            foreach ($ticketsToClose as $ticket) {
                $closeResult = $this->autoCloseTicket($ticket);
                if ($closeResult['success']) {
                    $closedTickets[] = $ticket['complaint_id'];
                }
            }
            
            return [
                'success' => true,
                'closed_tickets' => $closedTickets,
                'total_closed' => count($closedTickets)
            ];
            
        } catch (Exception $e) {
            error_log("Auto-close error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Process priority escalation based on business rules
     */
    public function processPriorityEscalation() {
        try {
            $escalatedTickets = [];
            
            // Get tickets that need priority escalation (per requirements)
            // Normal 0-3 hrs, Medium 3-12 hrs, High 12-24 hrs, Critical 24+ hrs
            $priorityRules = [
                'normal' => ['hours' => 3, 'escalate_to' => 'medium'],
                'medium' => ['hours' => 12, 'escalate_to' => 'high'],
                'high' => ['hours' => 24, 'escalate_to' => 'critical']
            ];
            
            foreach ($priorityRules as $currentPriority => $rule) {
                $sql = "SELECT c.*, cust.name as customer_name, cust.email as customer_email,
                               NULL as assigned_user_name, NULL as assigned_user_email
                        FROM complaints c
                        LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                        WHERE c.priority = ? 
                          AND c.status NOT IN ('closed', 'awaiting_feedback')
                          AND TIMESTAMPDIFF(HOUR, c.created_at, NOW()) >= ?
                          AND c.escalated_at IS NULL
                          AND (c.escalation_stopped IS NULL OR c.escalation_stopped = 0)";
                
                $tickets = $this->db->fetchAll($sql, [$currentPriority, $rule['hours']]);
                
                foreach ($tickets as $ticket) {
                    $escalationResult = $this->escalateTicketPriority($ticket, $rule['escalate_to']);
                    if ($escalationResult['success']) {
                        $escalatedTickets[] = [
                            'ticket_id' => $ticket['complaint_id'],
                            'old_priority' => $currentPriority,
                            'new_priority' => $rule['escalate_to']
                        ];
                    }
                }
            }
            
            return [
                'success' => true,
                'escalated_tickets' => $escalatedTickets,
                'total_escalated' => count($escalatedTickets)
            ];
            
        } catch (Exception $e) {
            error_log("Priority escalation error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate state transitions
     */
    private function validateStateTransition($currentStatus, $action, $userType) {
        $transitions = [
            'pending' => [
                'assign' => ['controller_nodal'],
                'forward' => ['controller_nodal'],
                'reply' => ['controller', 'controller_nodal'],
                'provide_info' => ['customer'],
                'close' => ['controller', 'controller_nodal']
            ],
            'awaiting_info' => [
                'provide_info' => ['customer'],
                'reply' => ['controller', 'controller_nodal'],
                'close' => ['controller', 'controller_nodal']
            ],
            'awaiting_approval' => [
                'admin_approve' => ['admin'],
                'admin_reject' => ['admin'],
                'admin_edit_approve' => ['admin']
            ],
            'awaiting_feedback' => [
                'provide_feedback' => ['customer'],
                'revert' => ['controller_nodal'],
                'auto_close' => ['system']
            ],
            'closed' => [
                'reopen' => ['controller_nodal', 'admin'],
                'revert' => ['controller_nodal', 'admin'],
                'add_admin_remarks' => ['admin']
            ]
        ];
        
        if (!isset($transitions[$currentStatus])) {
            return ['valid' => false, 'error' => "Invalid current status: {$currentStatus}"];
        }
        
        if (!isset($transitions[$currentStatus][$action])) {
            return ['valid' => false, 'error' => "Invalid action '{$action}' for status '{$currentStatus}'"];
        }
        
        $allowedRoles = $transitions[$currentStatus][$action];
        if (!in_array($userType, $allowedRoles)) {
            return ['valid' => false, 'error' => "User type '{$userType}' not allowed to perform '{$action}'"];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Execute workflow action
     */
    private function executeWorkflowAction($ticket, $action, $userId, $userType, $data) {
        switch ($action) {
            case 'assign':
                return $this->assignTicket($ticket, $data['assigned_to'], $userId);

            case 'forward':
                return $this->forwardTicket($ticket, $data['forward_to'], $data['remarks'], $userId);

            case 'reply':
                return $this->replyToTicket($ticket, $data['reply'], $data['action_taken'], $userId, $userType);

            case 'admin_approve':
                return $this->adminApprove($ticket, $data['remarks'] ?? null, $userId);

            case 'admin_reject':
                return $this->adminReject($ticket, $data['reason'], $userId);

            case 'admin_edit_approve':
                return $this->adminEditAndApprove($ticket, $data['edited_content'], $data['remarks'] ?? null, $userId);

            case 'provide_feedback':
                return $this->provideFeedback($ticket, $data['rating'], $data['remarks'], $userId);

            case 'provide_info':
                return $this->provideInfo($ticket, $data['additional_info'], $userId);

            case 'revert':
                return $this->revertTicket($ticket, $data['reason'], $userId);

            case 'add_admin_remarks':
                return $this->addAdminRemarks($ticket, $userId, $data);

            case 'close':
                return $this->closeTicket($ticket, $data['resolution'], $userId);

            case 'reopen':
                return $this->reopenTicket($ticket, $data['reason'], $userId);

            default:
                return ['success' => false, 'error' => "Unknown action: {$action}"];
        }
    }
    
    /**
     * Assign ticket to user
     */
    private function assignTicket($ticket, $assignedTo, $assignedBy) {
        $sql = "UPDATE complaints SET 
                assigned_to_department = ?,
                status = 'pending',
                updated_at = NOW()
                WHERE complaint_id = ?";
        
        $this->db->query($sql, [$assignedTo, $ticket['complaint_id']]);
        
        return [
            'success' => true,
            'new_status' => 'pending',
            'updates' => ['assigned_to_department' => $assignedTo],
            'message' => 'Ticket assigned successfully'
        ];
    }
    
    /**
     * Forward ticket to another user/department
     */
    private function forwardTicket($ticket, $forwardTo, $remarks, $forwardedBy) {
        // Check if forwarding to different division - priority resets per requirements
        $targetUser = $this->db->fetch("SELECT division FROM users WHERE id = ?", [$forwardTo]);
        $resetPriority = $targetUser && $targetUser['division'] !== $ticket['division'];
        
        $sql = "UPDATE complaints SET 
                assigned_to_department = ?,
                forwarded_flag = 1,
                status = 'pending',
                " . ($resetPriority ? "priority = 'normal', escalated_at = NULL," : "") . "
                updated_at = NOW()
                WHERE complaint_id = ?";
        
        $this->db->query($sql, [$forwardTo, $ticket['complaint_id']]);
        
        $updates = ['assigned_to_department' => $forwardTo, 'forwarded_flag' => 1];
        if ($resetPriority) {
            $updates['priority'] = 'normal';
        }
        
        return [
            'success' => true,
            'new_status' => 'pending',
            'updates' => $updates,
            'message' => 'Ticket forwarded successfully'
        ];
    }
    
    /**
     * Reply to ticket
     */
    private function replyToTicket($ticket, $reply, $actionTaken, $userId, $userType) {
        $needsApproval = $userType === 'controller' && $this->replyNeedsApproval($ticket, $reply);
        $newStatus = $needsApproval ? 'awaiting_approval' : 'awaiting_feedback';

        $sql = "UPDATE complaints SET
                action_taken = ?,
                status = ?,
                approval_stage = ?,
                updated_at = NOW()
                WHERE complaint_id = ?";

        $approvalStage = $needsApproval ? 'dept_admin' : NULL;
        $this->db->query($sql, [$actionTaken, $newStatus, $approvalStage, $ticket['complaint_id']]);

        return [
            'success' => true,
            'new_status' => $newStatus,
            'updates' => ['action_taken' => $actionTaken, 'approval_stage' => $approvalStage],
            'message' => $needsApproval ? 'Reply submitted for department admin approval' : 'Reply sent to customer'
        ];
    }
    
    /**
     * Approve reply
     */
    private function approveReply($ticket, $remarks, $approvedBy) {
        // Per requirements: Priority escalation stops permanently once reply/action is approved by controller_nodal
        $sql = "UPDATE complaints SET 
                status = 'awaiting_feedback',
                escalation_stopped = 1,
                updated_at = NOW()
                WHERE complaint_id = ?";
        
        $this->db->query($sql, [$ticket['complaint_id']]);
        
        return [
            'success' => true,
            'new_status' => 'awaiting_feedback',
            'updates' => ['escalation_stopped' => 1],
            'message' => 'Reply approved and sent to customer'
        ];
    }
    
    /**
     * Reject reply
     */
    private function rejectReply($ticket, $reason, $rejectedBy) {
        $sql = "UPDATE complaints SET
                status = 'pending',
                updated_at = NOW()
                WHERE complaint_id = ?";

        $this->db->query($sql, [$ticket['complaint_id']]);

        // Notify department controllers when reply is rejected
        require_once __DIR__ . '/../models/NotificationModel.php';
        $notificationModel = new NotificationModel();

        // Get all controllers in the ticket's department
        $departmentControllers = $this->db->fetchAll(
            "SELECT id, name FROM users
             WHERE role IN ('controller', 'controller_nodal')
             AND department = ?
             AND status = 'active'",
            [$ticket['assigned_to_department'] ?? $ticket['department']]
        );

        foreach ($departmentControllers as $controller) {
            $notificationModel->createNotification([
                'user_id' => $controller['id'],
                'user_type' => 'controller',
                'title' => 'Reply Rejected - Revision Required',
                'message' => "Your reply for ticket #{$ticket['complaint_id']} has been rejected by admin. Reason: {$reason}. Please revise and resubmit.",
                'type' => 'reply_rejected',
                'priority' => 'high',
                'related_id' => $ticket['complaint_id'],
                'related_type' => 'ticket',
                'action_url' => $this->getTicketUrlByRole($ticket['complaint_id'], 'controller'),
                'complaint_id' => $ticket['complaint_id'],
            ]);
        }

        return [
            'success' => true,
            'new_status' => 'pending',
            'updates' => [],
            'message' => 'Reply rejected and returned for revision'
        ];
    }

    /**
     * Get ticket URL based on user role
     */
    private function getTicketUrlByRole($ticketId, $role) {
        $baseUrl = Config::getAppUrl();

        switch ($role) {
            case 'customer':
                return $baseUrl . '/customer/tickets/' . $ticketId;
            case 'controller':
            case 'controller_nodal':
                return $baseUrl . '/controller/tickets/' . $ticketId;
            case 'admin':
            case 'superadmin':
                return $baseUrl . '/admin/tickets/' . $ticketId . '/view';
            default:
                return $baseUrl . '/controller/tickets/' . $ticketId;
        }
    }
    
    /**
     * Provide customer feedback
     */
    private function provideFeedback($ticket, $rating, $remarks, $customerId) {
        $sql = "UPDATE complaints SET 
                rating = ?,
                rating_remarks = ?,
                status = 'closed',
                closed_at = NOW(),
                updated_at = NOW()
                WHERE complaint_id = ?";
        
        $this->db->query($sql, [$rating, $remarks, $ticket['complaint_id']]);
        
        return [
            'success' => true,
            'new_status' => 'closed',
            'updates' => ['rating' => $rating, 'rating_remarks' => $remarks],
            'message' => 'Thank you for your feedback. Ticket has been closed.'
        ];
    }
    
    /**
     * Provide additional information when requested
     */
    private function provideInfo($ticket, $additionalInfo, $customerId) {
        // Status changes from awaiting_info back to pending for review
        $sql = "UPDATE complaints SET 
                status = 'pending',
                updated_at = NOW()
                WHERE complaint_id = ?";
        
        $this->db->query($sql, [$ticket['complaint_id']]);
        
        // Send notifications to controllers
        $this->sendInfoProvidedNotifications($ticket, $additionalInfo);
        
        return [
            'success' => true,
            'new_status' => 'pending',
            'updates' => [],
            'message' => 'Additional information provided. Ticket is now back under review.'
        ];
    }
    
    /**
     * Revert ticket for additional information
     */
    private function revertTicket($ticket, $reason, $revertedBy) {
        // Per requirements: Priority resets to "Normal" when ticket is reverted to customer
        $sql = "UPDATE complaints SET 
                status = 'awaiting_info',
                priority = 'normal',
                closed_at = NULL,
                escalated_at = NULL,
                updated_at = NOW()
                WHERE complaint_id = ?";
        
        $this->db->query($sql, [$ticket['complaint_id']]);
        
        return [
            'success' => true,
            'new_status' => 'awaiting_info',
            'updates' => ['priority' => 'normal'],
            'message' => 'Ticket reverted for additional information'
        ];
    }
    
    /**
     * Close ticket - now routes through admin approvals per new requirements
     */
    private function closeTicket($ticket, $resolution, $closedBy) {
        // Per new requirements: Closing by controller goes to department admin approval
        // Check if system settings require admin approvals
        $requireDeptApproval = $this->getSetting('require_dept_admin_approval', '1') === '1';

        if ($requireDeptApproval) {
            $sql = "UPDATE complaints SET
                    action_taken = ?,
                    status = 'awaiting_approval',
                    approval_stage = 'dept_admin',
                    updated_at = NOW()
                    WHERE complaint_id = ?";

            $this->db->query($sql, [$resolution, $ticket['complaint_id']]);

            return [
                'success' => true,
                'new_status' => 'awaiting_approval',
                'updates' => ['action_taken' => $resolution, 'approval_stage' => 'dept_admin'],
                'message' => 'Reply submitted for department admin approval'
            ];
        } else {
            // Legacy behavior - direct closure
            $sql = "UPDATE complaints SET
                    action_taken = ?,
                    status = 'closed',
                    closed_at = NOW(),
                    updated_at = NOW()
                    WHERE complaint_id = ?";

            $this->db->query($sql, [$resolution, $ticket['complaint_id']]);

            return [
                'success' => true,
                'new_status' => 'closed',
                'updates' => ['action_taken' => $resolution],
                'message' => 'Ticket closed successfully'
            ];
        }
    }
    
    /**
     * Reopen ticket
     */
    private function reopenTicket($ticket, $reason, $reopenedBy) {
        $sql = "UPDATE complaints SET 
                status = 'pending',
                closed_at = NULL,
                updated_at = NOW()
                WHERE complaint_id = ?";
        
        $this->db->query($sql, [$ticket['complaint_id']]);
        
        return [
            'success' => true,
            'new_status' => 'pending',
            'updates' => [],
            'message' => 'Ticket reopened successfully'
        ];
    }
    
    /**
     * Escalate ticket
     */
    private function escalateTicket($ticket) {
        try {
            // Mark as escalated
            $sql = "UPDATE complaints SET 
                    escalated_at = NOW(),
                    updated_at = NOW()
                    WHERE complaint_id = ?";
            
            $this->db->query($sql, [$ticket['complaint_id']]);
            
            // Send escalation notifications
            $this->sendEscalationNotifications($ticket);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Escalation error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Escalate ticket priority
     */
    private function escalateTicketPriority($ticket, $newPriority) {
        try {
            // Update priority and mark as escalated
            $sql = "UPDATE complaints SET 
                    priority = ?,
                    escalated_at = NOW(),
                    updated_at = NOW()
                    WHERE complaint_id = ?";
            
            $this->db->query($sql, [$newPriority, $ticket['complaint_id']]);
            
            
            // Send priority escalation notifications
            $this->sendPriorityEscalationNotifications($ticket, $newPriority);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Priority escalation error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Auto-close ticket
     */
    private function autoCloseTicket($ticket) {
        try {
            $sql = "UPDATE complaints SET 
                    status = 'closed',
                    closed_at = NOW(),
                    updated_at = NOW()
                    WHERE complaint_id = ?";
            
            $this->db->query($sql, [$ticket['complaint_id']]);
            
            // Log auto-close action
            $this->logger->logSystem('auto_close', "Ticket {$ticket['complaint_id']} auto-closed due to no feedback");
            
            // Send auto-close notification
            $this->sendAutoCloseNotification($ticket);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Auto-close error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    
    /**
     * Get ticket details
     */
    private function getTicketDetails($complaintId) {
        $sql = "SELECT c.*, 
                       cat.category, cat.type, cat.subtype,
                       cust.name as customer_name, cust.email as customer_email,
                       NULL as assigned_user_name, NULL as assigned_user_email
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                WHERE c.complaint_id = ?";
        
        return $this->db->fetch($sql, [$complaintId]);
    }
    
    /**
     * Update ticket state
     */
    private function updateTicketState($complaintId, $newStatus, $updates) {
        if (empty($updates)) {
            return;
        }
        
        $setParts = [];
        $params = [];
        
        foreach ($updates as $field => $value) {
            $setParts[] = "{$field} = ?";
            $params[] = $value;
        }
        
        $setParts[] = "status = ?";
        $params[] = $newStatus;
        
        $setParts[] = "updated_at = NOW()";
        $params[] = $complaintId;
        
        $sql = "UPDATE complaints SET " . implode(', ', $setParts) . " WHERE complaint_id = ?";
        $this->db->query($sql, $params);
    }
    
    /**
     * Log workflow action
     */
    private function logWorkflowAction($complaintId, $action, $userId, $userType, $result) {
        $this->logger->logTicket(
            $complaintId,
            $action,
            $userId,
            $userType,
            [
                'new_status' => $result['new_status'],
                'message' => $result['message'] ?? ''
            ]
        );
    }
    
    /**
     * Process automated actions
     */
    private function processAutomatedActions($ticket, $newStatus, $action) {
        
        // Auto-assign based on rules
        if ($newStatus === 'pending' && empty($ticket['assigned_to_department'])) {
            $autoAssignment = $this->findAutoAssignment($ticket);
            if ($autoAssignment) {
                $this->assignTicketAutomatically($ticket['complaint_id'], $autoAssignment);
            }
        }
    }
    
    /**
     * Send workflow notifications
     */
    private function sendWorkflowNotifications($ticket, $action, $result, $userId, $userType) {
        // Implementation depends on specific workflow action
        switch ($action) {
            case 'assign':
            case 'forward':
                $this->notificationService->sendTicketAssigned(
                    $ticket['complaint_id'],
                    $ticket,
                    ['department' => $result['updates']['assigned_to_department']]
                );

                // Send status change notification to customer
                $this->sendStatusChangeNotification($ticket, $result['new_status'], $action, $userType);
                break;

            case 'reply':
                if ($result['new_status'] === 'awaiting_feedback') {
                    // Send reply notification to customer
                }
                // Send status change notification
                $this->sendStatusChangeNotification($ticket, $result['new_status'], $action, $userType);
                break;

            case 'approve':
            case 'reject':
                // Send status change notification to customer and controllers
                $this->sendStatusChangeNotification($ticket, $result['new_status'], $action, $userType);
                break;

            case 'close':
                // Send closure notification to customer
                $this->sendStatusChangeNotification($ticket, $result['new_status'], $action, $userType);
                break;

            case 'reopen':
                // Send reopen notification to customer and controllers
                $this->sendStatusChangeNotification($ticket, $result['new_status'], $action, $userType);
                break;

            case 'revert':
                // Send revert notification to customer
                $this->sendStatusChangeNotification($ticket, $result['new_status'], $action, $userType);
                break;

            case 'provide_feedback':
                // Send closure confirmation
                $this->sendStatusChangeNotification($ticket, $result['new_status'], $action, $userType);
                break;
        }
    }

    /**
     * Send status change notification to relevant parties
     */
    private function sendStatusChangeNotification($ticket, $newStatus, $action, $userType) {
        try {
            require_once __DIR__ . '/../models/NotificationModel.php';
            $notificationModel = new NotificationModel();

            // Create notification for customer
            $customer = $this->db->fetch(
                "SELECT customer_id, name, email FROM customers WHERE customer_id = ?",
                [$ticket['customer_id']]
            );

            if ($customer) {
                $statusDisplay = $this->getStatusDisplayName($newStatus);
                $actionDisplay = $this->getActionDisplayName($action);
                // Create on-screen notification for customer
                $notificationModel->createNotification([
                    'title' => "Ticket Status Updated",
                    'message' => "Your ticket #{$ticket['complaint_id']} status has been changed to {$statusDisplay}",
                    'type' => 'status_change',
                    'user_id' => $customer['customer_id'],
                    'user_type' => 'customer',
                    'related_id' => $ticket['complaint_id'],
                    'related_type' => 'ticket',
                    'priority' => 'medium',
                    'action_url' => "/customer/tickets/{$ticket['complaint_id']}",
                    'metadata' => [
                        'action' => $action,
                        'new_status' => $newStatus,
                        'ticket_id' => $ticket['complaint_id']
                    ]
                ]);
            }


            // Create notification for nodal controller
            $nodalController = $this->db->fetch(
                "SELECT id FROM users WHERE role = 'controller_nodal' AND division = ? AND status = 'active' LIMIT 1",
                [$ticket['division']]
            );

            if ($nodalController) {
                $notificationModel->createNotification([
                    'title' => "Ticket Status Updated",
                    'message' => "Ticket #{$ticket['complaint_id']} in your division has been updated to {$this->getStatusDisplayName($newStatus)}",
                    'type' => 'status_change',
                    'user_id' => $nodalController['id'],
                    'user_type' => 'user',
                    'related_id' => $ticket['complaint_id'],
                    'related_type' => 'ticket',
                    'priority' => 'low',
                    'action_url' => "/controller/tickets/{$ticket['complaint_id']}",
                    'metadata' => [
                        'action' => $action,
                        'new_status' => $newStatus,
                        'ticket_id' => $ticket['complaint_id']
                    ]
                ]);
            }

        } catch (Exception $e) {
            error_log("Failed to send status change notification: " . $e->getMessage());
        }
    }

    /**
     * Get display name for status
     */
    private function getStatusDisplayName($status) {
        $statusNames = [
            'pending' => 'Pending Review',
            'awaiting_info' => 'Awaiting Information',
            'awaiting_feedback' => 'Awaiting Feedback',
            'awaiting_approval' => 'Awaiting Approval',
            'closed' => 'Closed',
            'resolved' => 'Resolved'
        ];

        return $statusNames[$status] ?? ucfirst($status);
    }

    /**
     * Get display name for action
     */
    private function getActionDisplayName($action) {
        $actionNames = [
            'assign' => 'Assigned',
            'forward' => 'Forwarded',
            'reply' => 'Replied',
            'approve' => 'Approved',
            'reject' => 'Rejected',
            'close' => 'Closed',
            'reopen' => 'Reopened',
            'revert' => 'Reverted',
            'provide_feedback' => 'Feedback Provided'
        ];

        return $actionNames[$action] ?? ucfirst($action);
    }

    /**
     * Send notifications when customer provides additional information
     */
    private function sendInfoProvidedNotifications($ticket, $additionalInfo) {
        try {
            // Get all controllers in the assigned department
            $assignedControllers = [];
            if (!empty($ticket['assigned_to_department'])) {
                $assignedControllers = $this->db->fetchAll(
                    "SELECT id, name, email, mobile FROM users
                     WHERE role = 'controller'
                       AND department = ?
                       AND division = ?
                       AND status = 'active'",
                    [$ticket['assigned_to_department'], $ticket['division']]
                );
            }

            // Get controller_nodal for the division
            $nodalController = $this->db->fetch(
                "SELECT id, name, email, mobile FROM users WHERE role = 'controller_nodal' AND division = ? AND status = 'active' LIMIT 1",
                [$ticket['division']]
            );

            $recipients = [];

            // Add all assigned controllers
            foreach ($assignedControllers as $controller) {
                $recipients[] = [
                    'user_id' => $controller['id'],
                    'email' => $controller['email'],
                    'mobile' => $controller['mobile'],
                    'complaint_id' => $ticket['complaint_id']
                ];
            }

            // Add controller_nodal if exists
            if ($nodalController) {
                // Check if nodal controller is already in recipients list
                $alreadyAdded = false;
                foreach ($recipients as $recipient) {
                    if ($recipient['user_id'] == $nodalController['id']) {
                        $alreadyAdded = true;
                        break;
                    }
                }

                if (!$alreadyAdded) {
                    $recipients[] = [
                        'user_id' => $nodalController['id'],
                        'email' => $nodalController['email'],
                        'mobile' => $nodalController['mobile'],
                        'complaint_id' => $ticket['complaint_id']
                    ];
                }
            }
            
            // Per requirements: No emails to Controllers, Controller_nodal, Admins or Superadmins
            // Only create on-screen notifications - do not send emails to staff
            if (!empty($recipients)) {
                // Create on-screen notifications instead of emails
                require_once __DIR__ . '/../models/NotificationModel.php';
                $notificationModel = new NotificationModel();

                foreach ($recipients as $recipient) {
                    $notificationModel->createNotification([
                        'title' => "Customer Provided Additional Info",
                        'message' => "Customer has provided additional information for ticket #{$ticket['complaint_id']}",
                        'type' => 'info_provided',
                        'user_id' => $recipient['user_id'],
                        'user_type' => 'user',
                        'related_id' => $ticket['complaint_id'],
                        'related_type' => 'ticket',
                        'priority' => 'medium',
                        'action_url' => "/controller/tickets/{$ticket['complaint_id']}",
                        'metadata' => json_encode([
                            'customer_name' => $ticket['customer_name'] ?? 'Customer',
                            'additional_info' => substr($additionalInfo, 0, 100) . (strlen($additionalInfo) > 100 ? '...' : '')
                        ])
                    ]);
                }
            }
            
        } catch (Exception $e) {
            error_log("Error sending info provided notifications: " . $e->getMessage());
        }
    }
    
    /**
     * Check if reply needs approval
     */
    private function replyNeedsApproval($ticket, $reply) {
        // Business rules for when replies need approval
        // For example: high priority tickets, sensitive categories, etc.
        
        if (in_array($ticket['priority'], ['high', 'critical'])) {
            return true;
        }
        
        // Check for keywords that require approval
        $approvalKeywords = ['refund', 'compensation', 'legal', 'escalate'];
        $replyLower = strtolower($reply);
        
        foreach ($approvalKeywords as $keyword) {
            if (strpos($replyLower, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    
    /**
     * Find auto-assignment for ticket
     */
    private function findAutoAssignment($ticket) {
        // Find least loaded user in the division
        $sql = "SELECT u.id, COUNT(c.complaint_id) as active_tickets
                FROM users u
                LEFT JOIN complaints c ON u.department = c.assigned_to_department AND c.status != 'closed'
                WHERE u.division = ? 
                  AND u.role = 'controller' 
                  AND u.status = 'active'
                GROUP BY u.id
                ORDER BY active_tickets ASC
                LIMIT 1";
        
        $assignment = $this->db->fetch($sql, [$ticket['division']]);
        return $assignment ? $assignment['id'] : null;
    }
    
    /**
     * Assign ticket automatically
     */
    private function assignTicketAutomatically($complaintId, $userId) {
        $sql = "UPDATE complaints SET 
                assigned_to_department = ?,
                updated_at = NOW()
                WHERE complaint_id = ?";
        
        $this->db->query($sql, [$userId, $complaintId]);
        
        $this->logger->logSystem('auto_assign', "Ticket {$complaintId} auto-assigned to user {$userId}");
    }
    
    /**
     * Send escalation notifications
     */
    private function sendEscalationNotifications($ticket) {
        // Send to nodal controller, customer, and assigned user
        $recipients = [];
        
        // Add customer
        $recipients[] = [
            'customer_id' => $ticket['customer_id'],
            'email' => $ticket['customer_email'],
            'name' => $ticket['customer_name']
        ];
        
        // Add assigned user
        if ($ticket['assigned_user_email']) {
            $recipients[] = [
                'department' => $ticket['assigned_to_department'],
                'email' => $ticket['assigned_user_email'],
                'name' => $ticket['assigned_user_name']
            ];
        }
        
        $data = [
            'complaint_id' => $ticket['complaint_id'],
            'customer_name' => $ticket['customer_name'],
            'escalation_reason' => 'Automatic priority escalation'
        ];
        
        // Per requirements: No emails to staff - only on-screen notifications
        // The NotificationService already handles this properly for escalations
    }
    
    /**
     * Send priority escalation notifications
     */
    private function sendPriorityEscalationNotifications($ticket, $newPriority) {
        // Similar to escalation notifications but for priority changes
    }
    
    /**
     * Send auto-close notification
     */
    private function sendAutoCloseNotification($ticket) {
        $recipient = [
            'customer_id' => $ticket['customer_id'],
            'email' => $ticket['customer_email'],
            'name' => $ticket['customer_name']
        ];
        
        $data = [
            'complaint_id' => $ticket['complaint_id'],
            'customer_name' => $ticket['customer_name'],
            'close_reason' => 'No feedback received within specified time'
        ];
        
        // Only send auto-close emails to customers, not staff
        $this->notificationService->send('ticket_auto_closed', [$recipient], $data);
    }

    /**
     * Admin approve - unified method for both department and CML admin
     */
    private function adminApprove($ticket, $remarks, $adminId) {
        $admin = $this->db->fetch("SELECT department, division, zone FROM users WHERE id = ?", [$adminId]);
        $currentStage = $ticket['approval_stage'] ?? 'dept_admin';

        if ($currentStage === 'dept_admin') {
            // Department admin approval - move to CML admin
            $sql = "UPDATE complaints SET
                    dept_admin_approved_by = ?,
                    dept_admin_approved_at = NOW(),
                    dept_admin_remarks = ?,
                    approval_stage = 'cml_admin',
                    assigned_to_department = 'CML',
                    updated_at = NOW()
                    WHERE complaint_id = ?";

            $this->db->query($sql, [$adminId, $remarks, $ticket['complaint_id']]);
            $this->logApprovalWorkflow($ticket['complaint_id'], 'dept_admin_review', 'approve', $adminId, null, null, $remarks);

            // Notify CML admin that ticket needs approval
            require_once __DIR__ . '/../models/NotificationModel.php';
            $notificationModel = new NotificationModel();

            $cmlAdmins = $this->db->fetchAll(
                "SELECT id, name FROM users WHERE role = 'admin' AND department = 'CML' AND status = 'active'"
            );

            foreach ($cmlAdmins as $cmlAdmin) {
                $notificationModel->createNotification([
                    'user_id' => $cmlAdmin['id'],
                    'user_type' => 'admin',
                    'title' => 'CML Approval Required',
                    'message' => "Department admin has approved ticket #{$ticket['complaint_id']}. Your CML admin approval is now required.",
                    'type' => 'approval_pending',
                    'priority' => 'high',
                    'related_id' => $ticket['complaint_id'],
                    'related_type' => 'ticket',
                    'action_url' => Config::getAppUrl() . '/admin/tickets/' . $ticket['complaint_id'] . '/view',
                    'complaint_id' => $ticket['complaint_id'],
                ]);
            }

            return [
                'success' => true,
                'new_status' => 'awaiting_approval',
                'updates' => ['approval_stage' => 'cml_admin', 'assigned_to_department' => 'CML'],
                'message' => 'Department admin approved. Sent to CML admin for approval.'
            ];
        } else {
            // CML admin approval - final approval
            // Reset forwarded_flag here as the ticket is now finalized
            $sql = "UPDATE complaints SET
                    cml_admin_approved_by = ?,
                    cml_admin_approved_at = NOW(),
                    cml_admin_remarks = ?,
                    status = 'awaiting_feedback',
                    approval_stage = 'completed',
                    escalation_stopped = 1,
                    forwarded_flag = 0,
                    updated_at = NOW()
                    WHERE complaint_id = ?";

            $this->db->query($sql, [$adminId, $remarks, $ticket['complaint_id']]);
            $this->logApprovalWorkflow($ticket['complaint_id'], 'cml_admin_review', 'approve', $adminId, null, null, $remarks);

            // Create customer-visible transaction to show the approved action_taken
            if (!empty($ticket['action_taken'])) {
                $this->db->query(
                    "INSERT INTO transactions (
                        complaint_id, transaction_type, remarks, remarks_type,
                        created_by_id, created_by_type, created_by_role, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                    [
                        $ticket['complaint_id'],
                        'approved',
                        $ticket['action_taken'],
                        'customer_remarks',
                        $adminId,
                        'user',
                        'admin'
                    ]
                );
            }

            return [
                'success' => true,
                'new_status' => 'awaiting_feedback',
                'updates' => ['approval_stage' => 'completed', 'escalation_stopped' => 1, 'forwarded_flag' => 0],
                'message' => 'CML admin approved. Sent to customer for feedback.'
            ];
        }
    }

    /**
     * Admin reject - unified method
     */
    private function adminReject($ticket, $reason, $adminId) {
        $admin = $this->db->fetch("SELECT department, role FROM users WHERE id = ?", [$adminId]);
        $currentStage = $ticket['approval_stage'] ?? 'dept_admin';

        // Log the rejection remarks as a transaction (visible to controllers and admins)
        if (!empty($reason)) {
            $adminType = $currentStage === 'dept_admin' ? 'Department Admin' : 'CML Admin';
            $transactionRemarks = "❌ {$adminType} Rejection:\n\n" . $reason;

            $this->db->query(
                "INSERT INTO transactions (
                    complaint_id, transaction_type, remarks, remarks_type,
                    created_by_id, created_by_type, created_by_role, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $ticket['complaint_id'],
                    'admin_rejected',
                    $transactionRemarks,
                    'admin_remarks',
                    $adminId,
                    'user',
                    $admin['role']
                ]
            );
        }

        // Return ticket to the department that originally closed/replied to it
        // The 'department' field stores which department handled the ticket
        $returnToDepartment = $ticket['department'] ?: $ticket['assigned_to_department'];

        $sql = "UPDATE complaints SET
                status = 'pending',
                approval_stage = NULL,
                assigned_to_department = ?,
                dept_admin_approved_by = NULL,
                dept_admin_approved_at = NULL,
                dept_admin_remarks = NULL,
                cml_admin_approved_by = NULL,
                cml_admin_approved_at = NULL,
                cml_admin_remarks = NULL,
                updated_at = NOW()
                WHERE complaint_id = ?";

        $this->db->query($sql, [$returnToDepartment, $ticket['complaint_id']]);

        $workflowStep = $currentStage === 'dept_admin' ? 'dept_admin_review' : 'cml_admin_review';
        $this->logApprovalWorkflow($ticket['complaint_id'], $workflowStep, 'reject', $adminId, null, null, null, $reason);

        // Notify department controllers when reply is rejected
        require_once __DIR__ . '/../models/NotificationModel.php';
        $notificationModel = new NotificationModel();

        // Get all controllers in the ticket's department
        $departmentControllers = $this->db->fetchAll(
            "SELECT id, name, role FROM users
             WHERE role IN ('controller', 'controller_nodal')
             AND department = ?
             AND status = 'active'",
            [$returnToDepartment]
        );

        foreach ($departmentControllers as $controller) {
            $actionUrl = $this->getTicketUrlByRole($ticket['complaint_id'], $controller['role']);

            $notificationModel->createNotification([
                'user_id' => $controller['id'],
                'user_type' => $controller['role'],
                'title' => 'Reply Rejected - Revision Required',
                'message' => "Your reply for ticket #{$ticket['complaint_id']} has been rejected by admin. Reason: {$reason}. Please revise and resubmit.",
                'type' => 'reply_rejected',
                'priority' => 'high',
                'related_id' => $ticket['complaint_id'],
                'related_type' => 'ticket',
                'action_url' => $actionUrl,
                'complaint_id' => $ticket['complaint_id'],
            ]);
        }

        $rejectMessage = $currentStage === 'dept_admin'
            ? "Department admin rejected. Returned to {$returnToDepartment} department for revision."
            : "CML admin rejected. Returned to {$returnToDepartment} department for revision.";

        return [
            'success' => true,
            'new_status' => 'pending',
            'updates' => ['assigned_to_department' => $returnToDepartment],
            'message' => $rejectMessage
        ];
    }

    /**
     * Admin edit and approve - unified method
     */
    private function adminEditAndApprove($ticket, $editedContent, $remarks, $adminId) {
        $admin = $this->db->fetch("SELECT department, role FROM users WHERE id = ?", [$adminId]);
        $currentStage = $ticket['approval_stage'] ?? 'dept_admin';

        // Log the original reply as a transaction before editing
        if (!empty($ticket['action_taken'])) {
            $transactionRemarks = "Original Reply (Before Admin Edit):\n\n" . $ticket['action_taken'];
            if ($remarks) {
                $transactionRemarks .= "\n\nAdmin Edit Remarks: " . $remarks;
            }

            $this->db->query(
                "INSERT INTO transactions (
                    complaint_id, transaction_type, remarks, remarks_type,
                    created_by_id, created_by_type, created_by_role, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $ticket['complaint_id'],
                    'admin_edited_reply',
                    $transactionRemarks,
                    'admin_remarks',
                    $adminId,
                    'user',
                    $admin['role']
                ]
            );
        }

        if ($currentStage === 'dept_admin') {
            $sql = "UPDATE complaints SET
                    action_taken = ?,
                    dept_admin_approved_by = ?,
                    dept_admin_approved_at = NOW(),
                    dept_admin_remarks = ?,
                    approval_stage = 'cml_admin',
                    assigned_to_department = 'CML',
                    updated_at = NOW()
                    WHERE complaint_id = ?";

            $this->db->query($sql, [$editedContent, $adminId, $remarks, $ticket['complaint_id']]);
            $this->logApprovalWorkflow($ticket['complaint_id'], 'dept_admin_review', 'edit_and_approve', $adminId, $ticket['action_taken'], $editedContent, $remarks);

            // Notify CML admin that ticket needs approval (with edits)
            require_once __DIR__ . '/../models/NotificationModel.php';
            $notificationModel = new NotificationModel();

            $cmlAdmins = $this->db->fetchAll(
                "SELECT id, name FROM users WHERE role = 'admin' AND department = 'CML' AND status = 'active'"
            );

            foreach ($cmlAdmins as $cmlAdmin) {
                $notificationModel->createNotification([
                    'user_id' => $cmlAdmin['id'],
                    'user_type' => 'admin',
                    'title' => 'CML Approval Required (Edited)',
                    'message' => "Department admin has edited and approved ticket #{$ticket['complaint_id']}. Your CML admin approval is now required.",
                    'type' => 'approval_pending',
                    'priority' => 'high',
                    'related_id' => $ticket['complaint_id'],
                    'related_type' => 'ticket',
                    'action_url' => Config::getAppUrl() . '/admin/tickets/' . $ticket['complaint_id'] . '/view',
                    'complaint_id' => $ticket['complaint_id'],
                ]);
            }

            return [
                'success' => true,
                'new_status' => 'awaiting_approval',
                'updates' => ['action_taken' => $editedContent, 'approval_stage' => 'cml_admin', 'assigned_to_department' => 'CML'],
                'message' => 'Department admin edited and approved. Sent to CML admin for approval.'
            ];
        } else {
            // CML admin edit and approve - final approval, reset forwarded_flag
            $sql = "UPDATE complaints SET
                    action_taken = ?,
                    cml_admin_approved_by = ?,
                    cml_admin_approved_at = NOW(),
                    cml_admin_remarks = ?,
                    status = 'awaiting_feedback',
                    approval_stage = 'completed',
                    escalation_stopped = 1,
                    forwarded_flag = 0,
                    updated_at = NOW()
                    WHERE complaint_id = ?";

            $this->db->query($sql, [$editedContent, $adminId, $remarks, $ticket['complaint_id']]);
            $this->logApprovalWorkflow($ticket['complaint_id'], 'cml_admin_review', 'edit_and_approve', $adminId, $ticket['action_taken'], $editedContent, $remarks);

            // Create customer-visible transaction to show the approved (and possibly edited) action_taken
            $this->db->query(
                "INSERT INTO transactions (
                    complaint_id, transaction_type, remarks, remarks_type,
                    created_by_id, created_by_type, created_by_role, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $ticket['complaint_id'],
                    'approved',
                    $editedContent,
                    'customer_remarks',
                    $adminId,
                    'user',
                    'admin'
                ]
            );

            return [
                'success' => true,
                'new_status' => 'awaiting_feedback',
                'updates' => ['action_taken' => $editedContent, 'approval_stage' => 'completed', 'escalation_stopped' => 1, 'forwarded_flag' => 0],
                'message' => 'CML admin edited and approved. Sent to customer for feedback.'
            ];
        }
    }

    /**
     * Add admin remarks to closed ticket
     */
    private function addAdminRemarks($ticket, $adminId, $data) {
        require_once __DIR__ . '/../models/AdminRemarksModel.php';
        $adminRemarksModel = new AdminRemarksModel();

        $adminType = $this->determineAdminType($adminId, $ticket);

        return $adminRemarksModel->addAdminRemarks(
            $ticket['complaint_id'],
            $adminId,
            $adminType,
            $data['remarks'],
            $data['remarks_category'] ?? null,
            $data['is_recurring_issue'] ?? false
        );
    }

    /**
     * Log approval workflow action
     */
    private function logApprovalWorkflow($complaintId, $workflowStep, $action, $performedBy, $originalContent = null, $editedContent = null, $remarks = null, $rejectionReason = null) {
        $user = $this->db->fetch("SELECT role FROM users WHERE id = ?", [$performedBy]);
        $userRole = $user ? $user['role'] : 'admin';

        $sql = "INSERT INTO approval_workflow_log (
                    complaint_id, workflow_step, action, performed_by, performed_by_role,
                    original_content, edited_content, remarks, rejection_reason
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $this->db->query($sql, [
            $complaintId,
            $workflowStep,
            $action,
            $performedBy,
            $userRole,
            $originalContent,
            $editedContent,
            $remarks,
            $rejectionReason
        ]);
    }

    /**
     * Determine admin type based on user and ticket context
     */
    private function determineAdminType($adminId, $ticket) {
        $admin = $this->db->fetch("SELECT department FROM users WHERE id = ?", [$adminId]);

        if ($admin && $admin['department'] === 'CML') {
            return 'cml_admin';
        }

        return 'dept_admin';
    }

    /**
     * Get system setting
     */
    private function getSetting($key, $default = null) {
        try {
            $sql = "SELECT setting_value FROM system_settings WHERE setting_key = ?";
            $result = $this->db->fetch($sql, [$key]);

            return $result ? $result['setting_value'] : $default;

        } catch (Exception $e) {
            return $default;
        }
    }
}
