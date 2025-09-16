<?php
/**
 * Workflow Engine for SAMPARK
 * Handles ticket workflow, state transitions, and automated processes
 */

require_once 'NotificationService.php';
require_once 'ActivityLogger.php';

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
                'close' => ['controller_nodal', 'admin']
            ],
            'awaiting_info' => [
                'provide_info' => ['customer'],
                'reply' => ['controller', 'controller_nodal'],
                'close' => ['controller_nodal', 'admin']
            ],
            'awaiting_approval' => [
                'approve' => ['controller_nodal'],
                'reject' => ['controller_nodal'],
                'close' => ['controller_nodal', 'admin']
            ],
            'awaiting_feedback' => [
                'provide_feedback' => ['customer'],
                'revert' => ['controller_nodal'],
                'auto_close' => ['system']
            ],
            'closed' => [
                'reopen' => ['controller_nodal', 'admin'],
                'revert' => ['controller_nodal', 'admin']
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
                
            case 'approve':
                return $this->approveReply($ticket, $data['remarks'], $userId);
                
            case 'reject':
                return $this->rejectReply($ticket, $data['reason'], $userId);
                
            case 'provide_feedback':
                return $this->provideFeedback($ticket, $data['rating'], $data['remarks'], $userId);
                
            case 'provide_info':
                return $this->provideInfo($ticket, $data['additional_info'], $userId);
                
            case 'revert':
                return $this->revertTicket($ticket, $data['reason'], $userId);
                
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
                updated_at = NOW()
                WHERE complaint_id = ?";
        
        $this->db->query($sql, [$actionTaken, $newStatus, $ticket['complaint_id']]);
        
        return [
            'success' => true,
            'new_status' => $newStatus,
            'updates' => ['action_taken' => $actionTaken],
            'message' => $needsApproval ? 'Reply submitted for approval' : 'Reply sent to customer'
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
        
        return [
            'success' => true,
            'new_status' => 'pending',
            'updates' => [],
            'message' => 'Reply rejected and returned for revision'
        ];
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
     * Close ticket
     */
    private function closeTicket($ticket, $resolution, $closedBy) {
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
                break;
                
            case 'reply':
                if ($result['new_status'] === 'awaiting_feedback') {
                    // Send reply notification to customer
                }
                break;
                
            case 'provide_feedback':
                // Send closure confirmation
                break;
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
        
        $this->notificationService->send('ticket_escalated', $recipients, $data);
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
        
        $this->notificationService->send('ticket_auto_closed', [$recipient], $data);
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
