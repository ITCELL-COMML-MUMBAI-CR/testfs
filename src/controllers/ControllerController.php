<?php
/**
 * Controller for SAMPARK - Handles staff/nodal controller operations
 * Manages ticket assignments, forwarding, replies, approvals
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/NotificationService.php';
require_once __DIR__ . '/../utils/BackgroundPriorityService.php';

class ControllerController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireRole(['controller', 'controller_nodal']);
    }
    
    public function dashboard() {
        $user = $this->getCurrentUser();
        
        $data = [
            'page_title' => 'Controller Dashboard - SAMPARK',
            'user' => $user,
            'ticket_stats' => $this->getTicketStats($user),
            'pending_tickets' => $this->getPendingTickets($user),
            'high_priority_tickets' => $this->getHighPriorityTickets($user),
            'escalated_tickets' => $this->getEscalatedTickets($user),
            'recent_activities' => $this->getRecentActivities($user),
            'performance_metrics' => $this->getPerformanceMetrics($user),
            'sla_violations' => $this->getSLAViolations($user),
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('controller/dashboard', $data);
    }
    
    public function tickets() {
        $user = $this->getCurrentUser();
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? '';
        $priority = $_GET['priority'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        // Build query conditions based on user role and department access
        $conditions = [];
        $params = [];
        
        // All controller_nodals in Commercial department can see tickets in their division/zone
        $conditions[] = 'c.division = ? AND c.assigned_to_department = ?';
        $params[] = $user['division'];
        $params[] = $user['department'];
        
        // Exclude closed complaints by default
        $conditions[] = "c.status != 'closed'";
        
        // Add filters
        if ($status) {
            $conditions[] = 'c.status = ?';
            $params[] = $status;
        }
        
        if ($priority) {
            $conditions[] = 'c.priority = ?';
            $params[] = $priority;
        }
        
        if ($dateFrom) {
            $conditions[] = 'c.created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }
        
        if ($dateTo) {
            $conditions[] = 'c.created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT c.*, 
                       cat.category, cat.type, cat.subtype,
                       s.name as shed_name, s.shed_code,
                       cust.name as customer_name, cust.email as customer_email,
                       cust.company_name, cust.mobile as customer_mobile,
                       d.department_name as assigned_department_name,
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed,
                       CASE 
                           WHEN c.sla_deadline IS NOT NULL AND NOW() > c.sla_deadline THEN 1
                           ELSE 0
                       END as is_sla_violated
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                LEFT JOIN departments d ON c.assigned_to_department = d.department_code
                WHERE {$whereClause}
                ORDER BY 
                    CASE WHEN c.priority = 'critical' THEN 1
                         WHEN c.priority = 'high' THEN 2
                         WHEN c.priority = 'medium' THEN 3
                         ELSE 4
                    END,
                    c.created_at ASC";
        
        $tickets = $this->paginate($sql, $params, $page);
        
        $data = [
            'page_title' => 'Manage Support Tickets - SAMPARK',
            'user' => $user,
            'tickets' => $tickets,
            'filters' => [
                'status' => $status,
                'priority' => $priority,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ],
            'status_options' => Config::TICKET_STATUS,
            'priority_options' => Config::PRIORITY_LEVELS,
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('controller/tickets', $data);
    }
    
    public function forwardedTickets() {
        $user = $this->getCurrentUser();
        
        // Only controller_nodal can access this view
        if ($user['role'] !== 'controller_nodal') {
            $this->setFlash('error', 'Access denied. Only nodal controllers can view forwarded tickets.');
            $this->redirect(Config::getAppUrl() . '/controller/tickets');
            return;
        }
        
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? '';
        $priority = $_GET['priority'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $department = $_GET['department'] ?? '';
        
        // Build query conditions for forwarded tickets within the division
        $conditions = [];
        $params = [];
        
        // Only show tickets forwarded to departments within the user's division
        $conditions[] = 'c.division = ?';
        $params[] = $user['division'];
        
        // Only show tickets that have been forwarded (forwarded_flag = 1)
        $conditions[] = 'c.forwarded_flag = 1';
        
        // Exclude closed complaints by default
        $conditions[] = "c.status != 'closed'";
        
        // Add filters
        if ($status) {
            $conditions[] = 'c.status = ?';
            $params[] = $status;
        }
        
        if ($priority) {
            $conditions[] = 'c.priority = ?';
            $params[] = $priority;
        }
        
        if ($department) {
            $conditions[] = 'c.assigned_to_department = ?';
            $params[] = $department;
        }
        
        if ($dateFrom) {
            $conditions[] = 'c.created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }
        
        if ($dateTo) {
            $conditions[] = 'c.created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT c.*, 
                       cat.category, cat.type, cat.subtype,
                       s.name as shed_name, s.shed_code,
                       cust.name as customer_name, cust.email as customer_email,
                       cust.company_name, cust.mobile as customer_mobile,
                       d.department_name as assigned_department_name,
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed,
                       CASE 
                           WHEN c.sla_deadline IS NOT NULL AND NOW() > c.sla_deadline THEN 1
                           ELSE 0
                       END as is_sla_violated
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                LEFT JOIN departments d ON c.assigned_to_department = d.department_code
                WHERE {$whereClause}
                ORDER BY 
                    CASE WHEN c.priority = 'critical' THEN 1
                         WHEN c.priority = 'high' THEN 2
                         WHEN c.priority = 'medium' THEN 3
                         ELSE 4
                    END,
                    c.created_at ASC";
        
        $tickets = $this->paginate($sql, $params, $page);
        
        // Get departments for filter dropdown from departments table
        $departments = $this->db->fetchAll(
            "SELECT DISTINCT d.department_code, d.department_name
             FROM departments d
             INNER JOIN complaints c ON c.assigned_to_department = d.department_code
             WHERE c.division = ? AND c.forwarded_flag = 1 AND d.is_active = 1
             ORDER BY d.department_name",
            [$user['division']]
        );
        
        $data = [
            'page_title' => 'Forwarded Tickets - SAMPARK',
            'user' => $user,
            'tickets' => $tickets,
            'departments' => $departments,
            'filters' => [
                'status' => $status,
                'priority' => $priority,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'department' => $department
            ],
            'status_options' => Config::TICKET_STATUS,
            'priority_options' => Config::PRIORITY_LEVELS,
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('controller/forwarded-tickets', $data);
    }
    
    public function viewTicket($ticketId) {
        $user = $this->getCurrentUser();
        
        // Get ticket details with department/division access control
        $sql = "SELECT c.*, 
                       cat.category, cat.type, cat.subtype,
                       s.name as shed_name, s.shed_code, s.division, s.zone,
                       w.wagon_code, w.type as wagon_type,
                       cust.name as customer_name, cust.email as customer_email, 
                       cust.mobile as customer_mobile, cust.company_name,
                       d.department_name as assigned_department_name,
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed,
                       CASE 
                           WHEN c.sla_deadline IS NOT NULL AND NOW() > c.sla_deadline THEN 1
                           ELSE 0
                       END as is_sla_violated
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                LEFT JOIN wagon_details w ON c.wagon_id = w.wagon_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                LEFT JOIN departments d ON c.assigned_to_department = d.department_code
                WHERE c.complaint_id = ? AND c.division = ?";
        
        // Controller_nodal can view tickets across departments in their division
        // Regular controllers can only view tickets in their department
        if ($user['role'] === 'controller_nodal') {
            $params = [$ticketId, $user['division']];
        } else {
            $sql .= " AND c.assigned_to_department = ?";
            $params = [$ticketId, $user['division'], $user['department']];
        }
        
        $ticket = $this->db->fetch($sql, $params);
        
        if (!$ticket) {
            $this->setFlash('error', 'Ticket not found or access denied');
            $this->redirect(Config::getAppUrl() . '/controller/tickets');
            return;
        }
        
        // Get ticket transactions
        $transactionSql = "SELECT t.*, 
                                  u.name as user_name, u.role as user_role, u.department as user_department, 
                                  u.division as user_division, u.zone as user_zone,
                                  cust.name as customer_name
                           FROM transactions t
                           LEFT JOIN users u ON t.created_by_id = u.id
                           LEFT JOIN customers cust ON t.created_by_customer_id = cust.customer_id
                           WHERE t.complaint_id = ? 
                           ORDER BY t.created_at DESC";
        
        $transactions = $this->db->fetchAll($transactionSql, [$ticketId]);
        
        // Separate priority changes from regular transactions
        $regularTransactions = [];
        $priorityChanges = [];
        $latestImportantRemark = null;
        
        foreach ($transactions as $transaction) {
            if ($transaction['remarks_type'] === 'priority_escalation') {
                $priorityChanges[] = $transaction;
            } else {
                $regularTransactions[] = $transaction;
                
                // Track latest important remark (excluding system and priority escalation)
                if (!$latestImportantRemark && !in_array($transaction['remarks_type'], ['priority_escalation', 'system'])) {
                    // Prioritize certain remark types for display
                    $importantTypes = ['admin_remarks', 'forwarding_remarks', 'interim_remarks', 'internal_remarks'];
                    if (in_array($transaction['remarks_type'], $importantTypes) && !empty(trim($transaction['remarks']))) {
                        $latestImportantRemark = $transaction;
                    }
                }
            }
        }
        
        // If no important remark found, get the latest non-system transaction
        if (!$latestImportantRemark && !empty($regularTransactions)) {
            $reversed = array_reverse($regularTransactions);
            foreach ($reversed as $transaction) {
                if (!empty(trim($transaction['remarks'])) && $transaction['remarks_type'] !== 'system') {
                    $latestImportantRemark = $transaction;
                    break;
                }
            }
        }
        
        // Get evidence files
        $evidenceSql = "SELECT * FROM evidence WHERE complaint_id = ? ORDER BY uploaded_at ASC";
        $evidenceRaw = $this->db->fetchAll($evidenceSql, [$ticketId]);
        
        // Transform evidence data for display
        $evidence = $this->transformEvidenceForDisplay($evidenceRaw);
        
        // Get available users for forwarding (if nodal controller)
        $availableUsers = [];
        if ($user['role'] === 'controller_nodal') {
            $availableUsers = $this->getAvailableUsers($ticket['division']);
        }
        
        // Check permissions for actions
        // For controller_nodal: restrict actions on tickets that are forwarded to other departments
        $isForwardedToOtherDept = ($user['role'] === 'controller_nodal' && 
                                   $ticket['forwarded_flag'] == 1 && 
                                   $ticket['assigned_to_department'] !== $user['department']);
        
        // For controller_nodal: can only view tickets assigned to other departments (no actions)
        $isAssignedToOtherDept = ($user['role'] === 'controller_nodal' && 
                                  $ticket['assigned_to_department'] !== $user['department']);
        
        // For controller_nodal: restrict actions on tickets awaiting customer response
        $isAwaitingCustomerInfo = ($user['role'] === 'controller_nodal' && 
                                   $ticket['status'] === 'awaiting_info');
        
        $canForward = in_array($user['role'], ['controller', 'controller_nodal']) && 
                     in_array($ticket['status'], ['pending', 'awaiting_info']) &&
                     !$isForwardedToOtherDept &&
                     !$isAwaitingCustomerInfo;
        
        $canReply = in_array($ticket['status'], ['pending', 'awaiting_info']) &&
                   !$isAssignedToOtherDept &&
                   !$isAwaitingCustomerInfo;
        
        $canApprove = $user['role'] === 'controller_nodal' && 
                     $ticket['status'] === 'awaiting_approval' &&
                     !$isAssignedToOtherDept;
        
        $canRevert = $user['role'] === 'controller_nodal' && 
                    in_array($ticket['status'], ['awaiting_approval', 'closed']) &&
                    !$isAssignedToOtherDept;
        
        $canRevertToCustomer = $user['role'] === 'controller_nodal' && 
                              in_array($ticket['status'], ['pending', 'awaiting_approval']) &&
                              !$isAssignedToOtherDept;
        
        $canInterimRemarks = in_array($user['role'], ['admin', 'controller_nodal', 'controller']) && 
                            $ticket['status'] === 'pending' &&
                            !$isAssignedToOtherDept;
        
        $data = [
            'page_title' => 'Ticket #' . $ticketId . ' - SAMPARK',
            'user' => $user,
            'ticket' => $ticket,
            'transactions' => $regularTransactions,
            'priority_changes' => $priorityChanges,
            'latest_important_remark' => $latestImportantRemark,
            'evidence' => $evidence,
            'available_users' => $availableUsers,
            'is_viewing_other_dept' => $isAssignedToOtherDept,
            'is_forwarded_ticket' => $isForwardedToOtherDept,
            'is_awaiting_customer_info' => $isAwaitingCustomerInfo,
            'permissions' => [
                'can_forward' => $canForward,
                'can_reply' => $canReply,
                'can_approve' => $canApprove,
                'can_revert' => $canRevert,
                'can_revert_to_customer' => $canRevertToCustomer,
                'can_interim_remarks' => $canInterimRemarks
            ],
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('controller/ticket-details', $data);
    }
    
    public function forwardTicket($ticketId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        // Both controllers and nodal controllers can forward
        if (!in_array($user['role'], ['controller', 'controller_nodal'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        // Set up validation rules based on user role
        $validationRules = [
            'department' => 'required',
            'remarks' => 'required|min:10|max:1000'
        ];
        
        // Nodal controllers need zone and division
        if ($user['role'] === 'controller_nodal') {
            $validationRules['zone'] = 'required';
            $validationRules['division'] = 'required';
        }
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, $validationRules);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Verify ticket access and status
            $accessConditions = "complaint_id = ? AND status IN ('pending', 'awaiting_info')";
            $accessParams = [$ticketId];
            
            // Regular controllers can only forward within their division
            if ($user['role'] === 'controller') {
                $accessConditions .= " AND division = ? AND assigned_to_department = ?";
                $accessParams[] = $user['division'];
                $accessParams[] = $user['department'];
            } else {
                // Nodal controllers can forward from their division
                $accessConditions .= " AND division = ?";
                $accessParams[] = $user['division'];
            }
            
            $ticket = $this->db->fetch("SELECT * FROM complaints WHERE {$accessConditions}", $accessParams);
            
            if (!$ticket) {
                $this->db->rollback();
                $this->json(['success' => false, 'message' => 'Invalid ticket or cannot forward'], 400);
                return;
            }
            
            // Controller_nodal cannot forward tickets that are already forwarded to other departments
            if ($user['role'] === 'controller_nodal' && 
                $ticket['forwarded_flag'] == 1 && 
                $ticket['assigned_to_department'] !== $user['department']) {
                $this->db->rollback();
                $this->json(['success' => false, 'message' => 'Cannot forward tickets that are already forwarded to other departments'], 403);
                return;
            }
            
            // Controller_nodal cannot forward tickets that are awaiting customer info
            if ($user['role'] === 'controller_nodal' && 
                $ticket['status'] === 'awaiting_info') {
                $this->db->rollback();
                $this->json(['success' => false, 'message' => 'Cannot forward tickets that are awaiting customer information'], 403);
                return;
            }
            
            // Determine target division and zone
            $targetDivision = $user['role'] === 'controller_nodal' ? $_POST['division'] : $user['division'];
            $targetZone = $user['role'] === 'controller_nodal' ? $_POST['zone'] : $user['zone'];
            
            // RBAC: Validate department selection 
            if ($user['role'] === 'controller_nodal' && $targetDivision !== $user['division']) {
                // Nodal controller forwarding outside division - only Commercial department allowed
                if (!in_array($_POST['department'], ['COMM', 'CML'])) {
                    $this->json(['success' => false, 'message' => 'When forwarding outside your division, you can only forward to Commercial department'], 400);
                    return;
                }
            } elseif ($user['role'] === 'controller') {
                // Regular controller can only forward to Commercial department of same division
                if (!in_array($_POST['department'], ['COMM', 'CML'])) {
                    $this->json(['success' => false, 'message' => 'Controllers can only forward tickets to Commercial department'], 400);
                    return;
                }
                if ($targetDivision !== $user['division']) {
                    $this->json(['success' => false, 'message' => 'Controllers can only forward tickets within their division'], 400);
                    return;
                }
            }
            
            // Priority is ALWAYS reset to normal when forwarding (no user control)
            $newPriority = 'normal';
            $resetEscalation = true;
            
            $sql = "UPDATE complaints SET 
                    assigned_to_department = ?,
                    division = ?,
                    zone = ?,
                    priority = ?,
                    forwarded_flag = 1,
                    " . ($resetEscalation ? "escalated_at = NULL," : "") . "
                    updated_at = NOW()
                    WHERE complaint_id = ?";
            
            // Handle department field (could be department_code from new system)
            $departmentValue = $_POST['department'];
            
            // Validate department exists
            $deptCheck = $this->db->fetch(
                "SELECT department_code FROM departments WHERE department_code = ? AND is_active = 1",
                [$departmentValue]
            );
            
            if (!$deptCheck) {
                $this->db->rollback();
                $this->json(['success' => false, 'message' => 'Invalid department selected'], 400);
                return;
            }
            
            $this->db->query($sql, [
                $departmentValue,
                $targetDivision,
                $targetZone,
                $newPriority,
                $ticketId
            ]);
            
            // Update SLA deadline based on new priority
            $this->updateSLADeadline($ticketId, $newPriority);
            
            // Handle priority escalation for cross-division forwarding
            $priorityService = new BackgroundPriorityService();
            if ($targetDivision !== $user['division']) {
                $priorityService->resetEscalationForCrossDivisionForward($ticketId, $user['division'], $targetDivision);
            }
            
            // Create transaction record
            $this->createTransaction($ticketId, 'forwarded', $_POST['remarks'], $user['id'], null, 'forwarding_remarks');
            
            // Send notifications to target department
            $this->sendForwardNotifications($ticketId, $ticket, $user, $targetDivision, $_POST['remarks']);
            
            $this->db->commit();
            
            $forwardMessage = $user['role'] === 'controller_nodal' 
                ? 'Ticket forwarded successfully to ' . $_POST['department'] . ' department in ' . $targetDivision . ' division'
                : 'Ticket forwarded successfully to ' . $_POST['department'] . ' department';
            
            $this->json([
                'success' => true,
                'message' => $forwardMessage
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Forward ticket error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to forward ticket. Please try again.'
            ], 500);
        }
    }
    
    public function replyTicket($ticketId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'reply' => 'required|min:20|max:2000',
            'action_taken' => 'max:1000',
            'needs_approval' => 'boolean',
            'is_interim_reply' => 'boolean',
            'officer_remarks' => 'max:1000'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Verify ticket access
            if ($user['role'] === 'controller') {
                $accessCondition = "division = ? AND assigned_to_department = ?";
                $accessParams = [$ticketId, $user['division'], $user['department']];
            } else {
                $accessCondition = "division = ?";
                $accessParams = [$ticketId, $user['division']];
            }
            
            $ticket = $this->db->fetch(
                "SELECT * FROM complaints WHERE complaint_id = ? AND {$accessCondition} AND status IN ('pending', 'awaiting_info')",
                $accessParams
            );
            
            if (!$ticket) {
                $this->json(['success' => false, 'message' => 'Invalid ticket or cannot reply'], 400);
                return;
            }
            
            // Controller_nodal cannot reply to tickets assigned to other departments
            if ($user['role'] === 'controller_nodal' && 
                $ticket['assigned_to_department'] !== $user['department']) {
                $this->json(['success' => false, 'message' => 'Cannot reply to tickets assigned to other departments'], 403);
                return;
            }
            
            $isInterimReply = isset($_POST['is_interim_reply']) && $_POST['is_interim_reply'];
            $officerRemarks = isset($_POST['officer_remarks']) ? trim($_POST['officer_remarks']) : '';
            
            // Determine status based on reply type
            if ($isInterimReply) {
                // Interim replies don't change status - just acknowledge receipt
                $newStatus = $ticket['status']; // Keep current status
            } else {
                $needsApproval = isset($_POST['needs_approval']) && $_POST['needs_approval'] && $user['role'] === 'controller';
                $newStatus = $needsApproval ? 'awaiting_approval' : 'awaiting_feedback';
            }
            
            // Update ticket only if it's a final reply (not interim)
            if (!$isInterimReply && !empty($_POST['action_taken'])) {
                $sql = "UPDATE complaints SET 
                        action_taken = ?,
                        status = ?,
                        updated_at = NOW()
                        WHERE complaint_id = ?";
                
                $this->db->query($sql, [
                    trim($_POST['action_taken']),
                    $newStatus,
                    $ticketId
                ]);
            }
            
            // Create transaction record based on reply type
            $transactionType = $isInterimReply ? 'interim_reply' : 'replied';
            $transactionRemarks = $_POST['reply'];
            
            // Add officer remarks if provided
            if (!empty($officerRemarks)) {
                $transactionRemarks .= "\n\nOfficer Remarks: " . $officerRemarks;
            }
            
            $this->createTransaction($ticketId, $transactionType, $transactionRemarks, $user['id']);
            
            // Handle file uploads if any
            if (!empty($_FILES['attachments']['name'][0])) {
                $this->handleEvidenceUpload($ticketId, $_FILES['attachments']);
            }
            
            // Send notifications
            $this->sendReplyNotifications($ticketId, $ticket, $user, $_POST['reply'], $newStatus);
            
            $this->db->commit();
            
            $message = $isInterimReply ? 
                'Interim reply sent to customer - ticket remains in current status' : 
                ($newStatus === 'awaiting_approval' ? 'Reply submitted for approval' : 'Reply sent to customer successfully');
            
            $this->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Reply ticket error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to send reply. Please try again.'
            ], 500);
        }
    }
    
    public function approveReply($ticketId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        // Only nodal controllers can approve
        if ($user['role'] !== 'controller_nodal') {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'approval_remarks' => 'max:500'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Verify ticket
            $ticket = $this->db->fetch(
                "SELECT * FROM complaints WHERE complaint_id = ? AND division = ? AND status = 'awaiting_approval'",
                [$ticketId, $user['division']]
            );
            
            if (!$ticket) {
                $this->json(['success' => false, 'message' => 'Invalid ticket or cannot approve'], 400);
                return;
            }
            
            // Controller_nodal cannot approve replies for tickets assigned to other departments
            if ($ticket['assigned_to_department'] !== $user['department']) {
                $this->json(['success' => false, 'message' => 'Cannot approve replies for tickets assigned to other departments'], 403);
                return;
            }
            
            // Update ticket status
            $sql = "UPDATE complaints SET 
                    status = 'awaiting_feedback',
                    updated_at = NOW()
                    WHERE complaint_id = ?";
            
            $this->db->query($sql, [$ticketId]);
            
            // Stop escalation for tickets awaiting feedback
            $priorityService = new BackgroundPriorityService();
            $priorityService->stopEscalationForStatus($ticketId, 'awaiting_feedback');
            
            // Create transaction record
            $remarks = 'Reply approved' . (trim($_POST['approval_remarks']) ? ': ' . trim($_POST['approval_remarks']) : '');
            $this->createTransaction($ticketId, 'approved', $remarks, $user['id']);
            
            // Send notifications
            $this->sendApprovalNotifications($ticketId, $ticket, $user, 'approved');
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Reply approved and sent to customer'
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Approve reply error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to approve reply. Please try again.'
            ], 500);
        }
    }
    
    public function rejectReply($ticketId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        // Only nodal controllers can reject
        if ($user['role'] !== 'controller_nodal') {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'rejection_reason' => 'required|min:10|max:500'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Verify ticket
            $ticket = $this->db->fetch(
                "SELECT * FROM complaints WHERE complaint_id = ? AND division = ? AND status = 'awaiting_approval'",
                [$ticketId, $user['division']]
            );
            
            if (!$ticket) {
                $this->json(['success' => false, 'message' => 'Invalid ticket or cannot reject'], 400);
                return;
            }
            
            // Controller_nodal cannot reject replies for tickets assigned to other departments
            if ($ticket['assigned_to_department'] !== $user['department']) {
                $this->json(['success' => false, 'message' => 'Cannot reject replies for tickets assigned to other departments'], 403);
                return;
            }
            
            // Update ticket status back to pending
            $sql = "UPDATE complaints SET 
                    status = 'pending',
                    updated_at = NOW()
                    WHERE complaint_id = ?";
            
            $this->db->query($sql, [$ticketId]);
            
            // Create transaction record
            $this->createTransaction($ticketId, 'rejected', 'Reply rejected: ' . trim($_POST['rejection_reason']), $user['id']);
            
            // Send notifications
            $this->sendApprovalNotifications($ticketId, $ticket, $user, 'rejected', $_POST['rejection_reason']);
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Reply rejected and returned for revision'
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Reject reply error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to reject reply. Please try again.'
            ], 500);
        }
    }
    
    public function revertTicket($ticketId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        // Only nodal controllers can revert
        if ($user['role'] !== 'controller_nodal') {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'revert_reason' => 'required|min:10|max:500'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Verify ticket
            $ticket = $this->db->fetch(
                "SELECT * FROM complaints WHERE complaint_id = ? AND division = ? AND status IN ('awaiting_approval', 'closed')",
                [$ticketId, $user['division']]
            );
            
            if (!$ticket) {
                $this->json(['success' => false, 'message' => 'Invalid ticket or cannot revert'], 400);
                return;
            }
            
            // Controller_nodal cannot revert tickets assigned to other departments
            if ($ticket['assigned_to_department'] !== $user['department']) {
                $this->json(['success' => false, 'message' => 'Cannot revert tickets assigned to other departments'], 403);
                return;
            }
            
            // Update ticket status
            $sql = "UPDATE complaints SET 
                    status = 'awaiting_info',
                    closed_at = NULL,
                    updated_at = NOW()
                    WHERE complaint_id = ?";
            
            $this->db->query($sql, [$ticketId]);
            
            // Create transaction record
            $this->createTransaction($ticketId, 'reverted', 'Ticket reverted: ' . trim($_POST['revert_reason']), $user['id']);
            
            // Send notifications
            $this->sendRevertNotifications($ticketId, $ticket, $user, $_POST['revert_reason']);
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Ticket reverted for additional information'
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Revert ticket error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to revert ticket. Please try again.'
            ], 500);
        }
    }
    
    public function revertToCustomer($ticketId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        // Only nodal controllers can revert to customer
        if ($user['role'] !== 'controller_nodal') {
            $this->json(['success' => false, 'message' => 'Access denied. Only nodal controllers can revert tickets to customers.'], 403);
            return;
        }
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'info_request' => 'required|min:10|max:1000'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Verify ticket access
            $accessConditions = "complaint_id = ? AND status IN ('pending', 'awaiting_approval')";
            $accessParams = [$ticketId];
            
            if ($user['role'] === 'controller') {
                $accessConditions .= " AND division = ? AND assigned_to_department = ?";
                $accessParams[] = $user['division'];
                $accessParams[] = $user['department'];
            } else {
                $accessConditions .= " AND division = ?";
                $accessParams[] = $user['division'];
            }
            
            $ticket = $this->db->fetch("SELECT * FROM complaints WHERE {$accessConditions}", $accessParams);
            
            if (!$ticket) {
                $this->db->rollback();
                $this->json(['success' => false, 'message' => 'Invalid ticket or cannot revert'], 400);
                return;
            }
            
            // Controller_nodal cannot revert to customer for tickets assigned to other departments
            if ($ticket['assigned_to_department'] !== $user['department']) {
                $this->db->rollback();
                $this->json(['success' => false, 'message' => 'Cannot revert to customer for tickets assigned to other departments'], 403);
                return;
            }
            
            // Update ticket status to awaiting_info
            $sql = "UPDATE complaints SET 
                    status = 'awaiting_info',
                    updated_at = NOW()
                    WHERE complaint_id = ?";
            
            $this->db->query($sql, [$ticketId]);
            
            // Stop escalation for tickets awaiting info
            $priorityService = new BackgroundPriorityService();
            $priorityService->stopEscalationForStatus($ticketId, 'awaiting_info');
            
            // Create transaction record
            $remarks = 'Additional information requested from customer: ' . trim($_POST['info_request']);
            $this->createTransaction($ticketId, 'info_requested', $remarks, $user['id'], null, 'admin_remarks');
            
            // Send notification to customer
            $this->sendInfoRequestNotifications($ticketId, $ticket, $user, $_POST['info_request']);
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Information request sent to customer successfully'
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Revert to customer error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to send information request. Please try again.'
            ], 500);
        }
    }
    
    public function addInterimRemarks($ticketId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        // Check role permissions
        if (!in_array($user['role'], ['admin', 'controller_nodal', 'controller'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'interim_remarks' => 'required|min:10|max:1000'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $this->db->beginTransaction();
            
            // Verify ticket access and status
            $accessConditions = "complaint_id = ? AND status = 'pending'";
            $accessParams = [$ticketId];
            
            if ($user['role'] === 'controller') {
                $accessConditions .= " AND division = ? AND assigned_to_department = ?";
                $accessParams[] = $user['division'];
                $accessParams[] = $user['department'];
            } elseif ($user['role'] === 'controller_nodal') {
                $accessConditions .= " AND division = ?";
                $accessParams[] = $user['division'];
            }
            // Admin can access all tickets
            
            $ticket = $this->db->fetch("SELECT * FROM complaints WHERE {$accessConditions}", $accessParams);
            
            if (!$ticket) {
                $this->db->rollback();
                $this->json(['success' => false, 'message' => 'Invalid ticket, wrong status, or access denied'], 400);
                return;
            }
            
            // Controller_nodal cannot add interim remarks for tickets assigned to other departments
            if ($user['role'] === 'controller_nodal' && 
                $ticket['assigned_to_department'] !== $user['department']) {
                $this->db->rollback();
                $this->json(['success' => false, 'message' => 'Cannot add interim remarks for tickets assigned to other departments'], 403);
                return;
            }
            
            // Create transaction record for interim remarks (doesn't change ticket status)
            $remarks = 'Interim remarks: ' . trim($_POST['interim_remarks']);
            $this->createTransaction($ticketId, 'interim_remarks', $remarks, $user['id'], null, 'interim_remarks');
            
            // Send notification to customer about the interim update
            $this->sendInterimRemarksNotifications($ticketId, $ticket, $user, $_POST['interim_remarks']);
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Interim remarks added successfully. Customer has been notified.'
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Interim remarks error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to add interim remarks. Please try again.'
            ], 500);
        }
    }
    
    
    public function printTicket($ticketId) {
        $user = $this->getCurrentUser();
        
        // Get ticket details with access control
        $sql = "SELECT c.*, 
                       cat.category, cat.type, cat.subtype,
                       s.name as shed_name, s.shed_code, s.division, s.zone,
                       w.wagon_code, w.type as wagon_type,
                       cust.name as customer_name, cust.email as customer_email, 
                       cust.mobile as customer_mobile, cust.company_name,
                       d.department_name as assigned_department_name
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                LEFT JOIN wagon_details w ON c.wagon_id = w.wagon_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                LEFT JOIN departments d ON c.assigned_to_department = d.department_code
                WHERE c.complaint_id = ? AND c.division = ? AND c.assigned_to_department = ?";
        
        $params = [$ticketId, $user['division'], $user['department']];
        $ticket = $this->db->fetch($sql, $params);
        
        if (!$ticket) {
            $this->setFlash('error', 'Ticket not found or access denied');
            $this->redirect(Config::getAppUrl() . '/controller/tickets');
            return;
        }
        
        // Get transactions
        $transactionSql = "SELECT t.*, 
                                  u.name as user_name, u.role as user_role, u.department as user_department, 
                                  u.division as user_division, u.zone as user_zone,
                                  cust.name as customer_name
                           FROM transactions t
                           LEFT JOIN users u ON t.created_by_id = u.id
                           LEFT JOIN customers cust ON t.created_by_customer_id = cust.customer_id
                           WHERE t.complaint_id = ? 
                           ORDER BY t.created_at ASC";
        
        $transactions = $this->db->fetchAll($transactionSql, [$ticketId]);
        
        // Get evidence files
        $evidenceSql = "SELECT * FROM evidence WHERE complaint_id = ? ORDER BY uploaded_at ASC";
        $evidenceRaw = $this->db->fetchAll($evidenceSql, [$ticketId]);
        $evidence = $this->transformEvidenceForDisplay($evidenceRaw);
        
        $data = [
            'page_title' => 'Print Ticket #' . $ticketId,
            'user' => $user,
            'ticket' => $ticket,
            'transactions' => $transactions,
            'evidence' => $evidence,
            'is_print' => true
        ];
        
        $this->view('controller/ticket-print', $data);
    }
    
    public function exportTicket($ticketId) {
        $user = $this->getCurrentUser();
        
        // Get ticket details with access control
        $sql = "SELECT c.*, 
                       cat.category, cat.type, cat.subtype,
                       s.name as shed_name, s.shed_code, s.division, s.zone,
                       w.wagon_code, w.type as wagon_type,
                       cust.name as customer_name, cust.email as customer_email, 
                       cust.mobile as customer_mobile, cust.company_name,
                       d.department_name as assigned_department_name
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                LEFT JOIN wagon_details w ON c.wagon_id = w.wagon_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                LEFT JOIN departments d ON c.assigned_to_department = d.department_code
                WHERE c.complaint_id = ? AND c.division = ? AND c.assigned_to_department = ?";
        
        $params = [$ticketId, $user['division'], $user['department']];
        $ticket = $this->db->fetch($sql, $params);
        
        if (!$ticket) {
            $this->json(['success' => false, 'message' => 'Ticket not found or access denied'], 404);
            return;
        }
        
        // Get transactions
        $transactionSql = "SELECT t.*, 
                                  u.name as user_name, u.role as user_role, u.department as user_department, 
                                  u.division as user_division, u.zone as user_zone,
                                  cust.name as customer_name
                           FROM transactions t
                           LEFT JOIN users u ON t.created_by_id = u.id
                           LEFT JOIN customers cust ON t.created_by_customer_id = cust.customer_id
                           WHERE t.complaint_id = ? 
                           ORDER BY t.created_at ASC";
        
        $transactions = $this->db->fetchAll($transactionSql, [$ticketId]);
        
        // Get evidence files
        $evidenceSql = "SELECT * FROM evidence WHERE complaint_id = ? ORDER BY uploaded_at ASC";
        $evidenceRaw = $this->db->fetchAll($evidenceSql, [$ticketId]);
        $evidence = $this->transformEvidenceForDisplay($evidenceRaw);
        
        try {
            // Generate HTML for PDF
            $html = $this->generateTicketHTML($ticket, $transactions, $evidence, $user);
            
            // Set headers for HTML download (can be saved as PDF by browser)
            header('Content-Type: text/html; charset=UTF-8');
            header('Content-Disposition: attachment; filename="ticket_' . $ticketId . '.html"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            // Output the HTML
            echo $html;
            
        } catch (Exception $e) {
            error_log("Export ticket error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to export ticket'], 500);
        }
    }
    
    private function generateTicketHTML($ticket, $transactions, $evidence, $user) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Ticket #<?= $ticket['complaint_id'] ?></title>
            <meta charset="UTF-8">
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 20px; 
                    line-height: 1.6;
                    color: #333;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 30px; 
                    border-bottom: 2px solid #007bff;
                    padding-bottom: 20px;
                }
                .header h1 {
                    color: #007bff;
                    margin-bottom: 10px;
                }
                .section { 
                    margin: 25px 0; 
                    page-break-inside: avoid;
                }
                .section-title {
                    font-size: 18px;
                    font-weight: bold;
                    color: #007bff;
                    border-bottom: 1px solid #dee2e6;
                    padding-bottom: 5px;
                    margin-bottom: 15px;
                }
                .info-row {
                    margin: 8px 0;
                    display: flex;
                }
                .label { 
                    font-weight: bold; 
                    min-width: 120px;
                    color: #666;
                }
                .evidence { 
                    margin: 10px 0; 
                    padding: 10px;
                    background: #f8f9fa;
                    border-left: 4px solid #007bff;
                }
                .transaction { 
                    border-left: 4px solid #007bff; 
                    padding: 15px; 
                    margin: 15px 0; 
                    background: #f8f9fa;
                    border-radius: 5px;
                }
                .transaction-type {
                    font-weight: bold;
                    color: #007bff;
                    margin-bottom: 8px;
                }
                .description-box {
                    background: #f8f9fa;
                    border: 1px solid #dee2e6;
                    border-radius: 5px;
                    padding: 15px;
                    margin: 10px 0;
                }
                .badge {
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 12px;
                    font-weight: bold;
                    margin: 0 5px;
                }
                .badge-success { background: #d4edda; color: #155724; }
                .badge-warning { background: #fff3cd; color: #856404; }
                .badge-info { background: #cce7f0; color: #0c5460; }
                .badge-secondary { background: #e2e3e5; color: #383d41; }
                @media print {
                    body { margin: 10px; }
                    .page-break { page-break-before: always; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>SAMPARK - Support Ticket System</h1>
                <h2>Ticket #<?= htmlspecialchars($ticket['complaint_id']) ?></h2>
                <div>
                    <span class="badge badge-<?= 
                        $ticket['status'] === 'closed' ? 'success' : 
                        ($ticket['status'] === 'pending' ? 'warning' : 'info') ?>">
                        <?= ucwords(str_replace('_', ' ', $ticket['status'])) ?>
                    </span>
                    <span class="badge badge-<?= 
                        $ticket['priority'] === 'critical' ? 'danger' : 
                        ($ticket['priority'] === 'high' ? 'warning' : 'secondary') ?>">
                        <?= ucfirst($ticket['priority']) ?> Priority
                    </span>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">Ticket Information</div>
                <div class="info-row">
                    <span class="label">Created:</span>
                    <span><?= date('M d, Y H:i', strtotime($ticket['created_at'])) ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Category:</span>
                    <span><?= htmlspecialchars($ticket['category'] ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Location:</span>
                    <span><?= htmlspecialchars($ticket['shed_name'] ?? 'N/A') ?>
                    <?php if ($ticket['shed_code']): ?>
                    (<?= htmlspecialchars($ticket['shed_code']) ?>)
                    <?php endif; ?></span>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">Customer Information</div>
                <div class="info-row">
                    <span class="label">Name:</span>
                    <span><?= htmlspecialchars($ticket['customer_name'] ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span><?= htmlspecialchars($ticket['customer_email'] ?? 'N/A') ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Mobile:</span>
                    <span><?= htmlspecialchars($ticket['customer_mobile'] ?? 'N/A') ?></span>
                </div>
                <?php if ($ticket['company_name']): ?>
                <div class="info-row">
                    <span class="label">Company:</span>
                    <span><?= htmlspecialchars($ticket['company_name']) ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="section">
                <div class="section-title">Issue Description</div>
                <div class="description-box">
                    <?= nl2br(htmlspecialchars($ticket['description'] ?? $ticket['complaint_message'] ?? 'No description provided')) ?>
                </div>
            </div>
            
            <?php if ($ticket['action_taken']): ?>
            <div class="section">
                <div class="section-title">Action Taken</div>
                <div class="description-box">
                    <?= nl2br(htmlspecialchars($ticket['action_taken'])) ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($evidence)): ?>
            <div class="section">
                <div class="section-title">Evidence Files (<?= count($evidence) ?>)</div>
                <?php foreach ($evidence as $file): ?>
                <div class="evidence">
                    <strong><?= htmlspecialchars($file['original_name']) ?></strong><br>
                    Size: <?= number_format($file['file_size'] / 1024, 1) ?> KB • 
                    Uploaded: <?= date('M d, Y', strtotime($file['uploaded_at'])) ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="section page-break">
                <div class="section-title">Transaction History</div>
                <?php foreach ($transactions as $transaction): ?>
                <div class="transaction">
                    <div class="transaction-type"><?= ucfirst(str_replace('_', ' ', $transaction['transaction_type'])) ?></div>
                    <div><?= nl2br(htmlspecialchars($transaction['remarks'] ?? '')) ?></div>
                    <small style="color: #666; margin-top: 10px; display: block;">
                        By: <?= htmlspecialchars($transaction['user_name'] ?? $transaction['customer_name'] ?? 'System') ?>
                        <?php if ($transaction['user_department']): ?>
                        (<?= htmlspecialchars($transaction['user_department']) ?>
                        <?php if ($transaction['user_division']): ?>
                        - <?= htmlspecialchars($transaction['user_division']) ?>
                        <?php endif; ?>
                        <?php if ($transaction['user_zone']): ?>
                        - <?= htmlspecialchars($transaction['user_zone']) ?>
                        <?php endif; ?>)
                        <?php endif; ?>
                        • <?= date('M d, Y H:i', strtotime($transaction['created_at'])) ?>
                    </small>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="section" style="text-align: center; border-top: 1px solid #dee2e6; padding-top: 20px;">
                <small style="color: #666;">
                    Generated on <?= date('M d, Y H:i') ?> by <?= htmlspecialchars($user['name']) ?><br>
                    SAMPARK - Railway Customer Support System
                </small>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    public function reports() {
        $user = $this->getCurrentUser();
        
        $reportType = $_GET['type'] ?? 'summary';
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-t');
        $division = $_GET['division'] ?? $user['division'];
        
        $data = [
            'page_title' => 'Reports - SAMPARK',
            'user' => $user,
            'report_type' => $reportType,
            'date_range' => ['from' => $dateFrom, 'to' => $dateTo],
            'division' => $division,
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        switch ($reportType) {
            case 'summary':
                $data['report_data'] = $this->getSummaryReport($user, $dateFrom, $dateTo, $division);
                break;
            case 'performance':
                $data['report_data'] = $this->getPerformanceReport($user, $dateFrom, $dateTo, $division);
                break;
            case 'sla':
                $data['report_data'] = $this->getSLAReport($user, $dateFrom, $dateTo, $division);
                break;
            case 'customer_satisfaction':
                $data['report_data'] = $this->getCustomerSatisfactionReport($user, $dateFrom, $dateTo, $division);
                break;
        }
        
        $this->view('controller/reports', $data);
    }
    
    public function profile() {
        $user = $this->getCurrentUser();
        
        $userDetails = $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$user['id']]
        );
        
        $data = [
            'page_title' => 'My Profile - SAMPARK',
            'user' => $user,
            'user_details' => $userDetails,
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('controller/profile', $data);
    }
    
    public function help() {
        $user = $this->getCurrentUser();
        
        $data = [
            'page_title' => 'Help & Support - SAMPARK',
            'user' => $user,
            'user_guides' => $this->getUserGuides(),
            'faq' => $this->getControllerFAQ(),
            'contact_info' => $this->getContactInfo()
        ];
        
        $this->view('controller/help', $data);
    }
    
    // Helper methods
    
    private function getTicketStats($user) {
        $condition = $user['role'] === 'controller' ? 'c.assigned_to_user_id = ?' : 'c.division = ?';
        $param = $user['role'] === 'controller' ? $user['id'] : $user['division'];
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN c.status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN c.status = 'awaiting_info' THEN 1 ELSE 0 END) as awaiting_info,
                    SUM(CASE WHEN c.status = 'awaiting_approval' THEN 1 ELSE 0 END) as awaiting_approval,
                    SUM(CASE WHEN c.status = 'awaiting_feedback' THEN 1 ELSE 0 END) as awaiting_feedback,
                    SUM(CASE WHEN c.status = 'closed' THEN 1 ELSE 0 END) as closed,
                    SUM(CASE WHEN c.priority IN ('high', 'critical') THEN 1 ELSE 0 END) as high_priority_count,
                    SUM(CASE WHEN c.sla_deadline IS NOT NULL AND NOW() > c.sla_deadline AND c.status != 'closed' THEN 1 ELSE 0 END) as sla_violations
                FROM complaints c
                WHERE {$condition}";
        
        return $this->db->fetch($sql, [$param]);
    }
    
    private function getPendingTickets($user, $limit = 10) {
        $condition = $user['role'] === 'controller' ? 'c.assigned_to_user_id = ?' : 'c.division = ?';
        $param = $user['role'] === 'controller' ? $user['id'] : $user['division'];
        
        $sql = "SELECT c.complaint_id, c.priority, c.created_at, c.status,
                       cat.category, cat.subtype,
                       cust.name as customer_name,
                       s.name as shed_name,
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                WHERE {$condition} AND c.status IN ('pending', 'awaiting_info')
                ORDER BY 
                    CASE WHEN c.priority = 'critical' THEN 1
                         WHEN c.priority = 'high' THEN 2
                         WHEN c.priority = 'medium' THEN 3
                         ELSE 4
                    END,
                    c.created_at ASC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$param, $limit]);
    }
    
    private function getHighPriorityTickets($user, $limit = 5) {
        $condition = $user['role'] === 'controller' ? 'c.assigned_to_user_id = ?' : 'c.division = ?';
        $param = $user['role'] === 'controller' ? $user['id'] : $user['division'];
        
        $sql = "SELECT c.complaint_id, c.priority, c.created_at, c.status,
                       cat.category, cat.subtype,
                       cust.name as customer_name,
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                WHERE {$condition} 
                  AND c.priority IN ('high', 'critical')
                  AND c.status != 'closed'
                ORDER BY 
                    CASE WHEN c.priority = 'critical' THEN 1 ELSE 2 END,
                    c.created_at ASC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$param, $limit]);
    }
    
    private function getEscalatedTickets($user, $limit = 5) {
        $condition = $user['role'] === 'controller' ? 'c.assigned_to_user_id = ?' : 'c.division = ?';
        $param = $user['role'] === 'controller' ? $user['id'] : $user['division'];
        
        $sql = "SELECT c.complaint_id, c.priority, c.created_at, c.status, c.escalated_at,
                       cat.category, cat.subtype,
                       cust.name as customer_name,
                       TIMESTAMPDIFF(HOUR, c.escalated_at, NOW()) as hours_since_escalation
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                WHERE {$condition} 
                  AND c.escalated_at IS NOT NULL
                  AND c.status != 'closed'
                ORDER BY c.escalated_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$param, $limit]);
    }
    
    private function getRecentActivities($user, $limit = 10) {
        $condition = $user['role'] === 'controller' ? 'user_id = ?' : '(user_id IN (SELECT id FROM users WHERE division = ?) OR customer_id IN (SELECT customer_id FROM customers WHERE division = ?))';
        $params = $user['role'] === 'controller' ? [$user['id']] : [$user['division'], $user['division']];
        
        $sql = "SELECT action, description, complaint_id, created_at, user_role
                FROM activity_logs 
                WHERE {$condition}
                ORDER BY created_at DESC 
                LIMIT ?";
        
        $params[] = $limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    private function getPerformanceMetrics($user) {
        $condition = $user['role'] === 'controller' ? 'c.assigned_to_user_id = ?' : 'c.division = ?';
        $param = $user['role'] === 'controller' ? $user['id'] : $user['division'];
        
        $sql = "SELECT 
                    COUNT(*) as total_handled,
                    AVG(TIMESTAMPDIFF(HOUR, c.created_at, COALESCE(c.closed_at, NOW()))) as avg_resolution_hours,
                    SUM(CASE WHEN c.status = 'closed' AND c.rating = 'excellent' THEN 1 ELSE 0 END) as excellent_ratings,
                    SUM(CASE WHEN c.status = 'closed' AND c.rating = 'satisfactory' THEN 1 ELSE 0 END) as satisfactory_ratings,
                    SUM(CASE WHEN c.status = 'closed' AND c.rating = 'unsatisfactory' THEN 1 ELSE 0 END) as unsatisfactory_ratings
                FROM complaints c
                WHERE {$condition} 
                  AND c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        return $this->db->fetch($sql, [$param]);
    }
    
    private function getSLAViolations($user) {
        $condition = $user['role'] === 'controller' ? 'c.assigned_to_user_id = ?' : 'c.division = ?';
        $param = $user['role'] === 'controller' ? $user['id'] : $user['division'];
        
        $sql = "SELECT c.complaint_id, c.priority, c.created_at, c.sla_deadline,
                       cat.category, cat.subtype,
                       cust.name as customer_name,
                       TIMESTAMPDIFF(HOUR, c.sla_deadline, NOW()) as hours_overdue
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                WHERE {$condition} 
                  AND c.sla_deadline IS NOT NULL 
                  AND NOW() > c.sla_deadline 
                  AND c.status != 'closed'
                ORDER BY c.sla_deadline ASC";
        
        return $this->db->fetchAll($sql, [$param]);
    }
    
    private function getDivisions() {
        $sql = "SELECT DISTINCT division FROM shed WHERE is_active = 1 ORDER BY division";
        return $this->db->fetchAll($sql);
    }
    
    private function getAvailableUsers($division) {
        $sql = "SELECT id, name, role, department 
                FROM users 
                WHERE division = ? 
                  AND status = 'active' 
                  AND role IN ('controller', 'controller_nodal')
                ORDER BY role DESC, name ASC";
        
        return $this->db->fetchAll($sql, [$division]);
    }
    
    private function updateSLADeadline($ticketId, $priority) {
        // Get SLA hours for the priority
        $slaInfo = $this->db->fetch(
            "SELECT resolution_hours FROM sla_definitions WHERE priority_level = ? AND is_active = 1",
            [$priority]
        );
        
        if ($slaInfo) {
            $deadline = date('Y-m-d H:i:s', strtotime("+{$slaInfo['resolution_hours']} hours"));
            $this->db->query(
                "UPDATE complaints SET sla_deadline = ? WHERE complaint_id = ?",
                [$deadline, $ticketId]
            );
        }
    }
    
    private function createTransaction($complaintId, $type, $remarks, $fromUserId, $toUserId = null, $remarksType = 'internal_remarks') {
        $sql = "INSERT INTO transactions (
            complaint_id, transaction_type, remarks, remarks_type,
            from_user_id, to_user_id, 
            created_by_id, created_by_type, created_by_role, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'user', ?, NOW())";
        
        $this->db->query($sql, [
            $complaintId,
            $type,
            $remarks,
            $remarksType,
            $fromUserId,
            $toUserId,
            $fromUserId,
            $this->session->getUserRole()
        ]);
    }
    
    private function handleEvidenceUpload($complaintId, $files) {
        $uploader = new FileUploader();
        $user = $this->getCurrentUser();
        return $uploader->uploadEvidence($complaintId, $files, 'user', $user['id']);
    }
    
    private function sendForwardNotifications($ticketId, $ticket, $fromUser, $targetDivision, $remarks) {
        $notificationService = new NotificationService();
        
        // Get users in the target department/division
        $targetUsers = $this->db->fetchAll(
            "SELECT id, name, email, mobile FROM users 
             WHERE division = ? AND department = ? AND status = 'active' AND role IN ('controller', 'controller_nodal')",
            [$targetDivision, $_POST['department']]
        );
        
        if (empty($targetUsers)) {
            return; // No users to notify
        }
        
        // Prepare notification data
        $data = [
            'complaint_id' => $ticketId,
            'customer_name' => $ticket['customer_name'] ?? 'Customer',
            'forwarded_by' => $fromUser['name'],
            'department' => $_POST['department'],
            'division' => $targetDivision,
            'remarks' => $remarks
        ];
        
        // Prepare recipients
        $recipients = [];
        foreach ($targetUsers as $user) {
            $recipients[] = [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'mobile' => $user['mobile'] ?? null,
                'complaint_id' => $ticketId
            ];
        }
        
        // Send notifications using the existing template system
        $notificationService->send('ticket_forwarded', $recipients, $data);
    }
    
    private function sendReplyNotifications($ticketId, $ticket, $user, $reply, $status) {
        $notificationService = new NotificationService();
        
        if ($status === 'awaiting_feedback') {
            // Get customer info
            $customer = $this->db->fetch(
                "SELECT customer_id, name, email, mobile FROM customers WHERE customer_id = ?",
                [$ticket['customer_id']]
            );
            
            if ($customer) {
                $data = [
                    'complaint_id' => $ticketId,
                    'customer_name' => $customer['name'],
                    'reply_from' => $user['name'],
                    'reply_text' => $reply
                ];
                
                $recipients = [[
                    'customer_id' => $customer['customer_id'],
                    'email' => $customer['email'],
                    'mobile' => $customer['mobile'],
                    'complaint_id' => $ticketId
                ]];
                
                $notificationService->send('ticket_reply', $recipients, $data);
            }
        } else {
            // Notify nodal controller for approval
            $nodalController = $this->db->fetch(
                "SELECT id, name, email, mobile FROM users WHERE role = 'controller_nodal' AND division = ? AND status = 'active' LIMIT 1",
                [$user['division']]
            );
            
            if ($nodalController) {
                $data = [
                    'complaint_id' => $ticketId,
                    'reply_from' => $user['name'],
                    'department' => $user['department']
                ];
                
                $recipients = [[
                    'user_id' => $nodalController['id'],
                    'email' => $nodalController['email'],
                    'mobile' => $nodalController['mobile'],
                    'complaint_id' => $ticketId
                ]];
                
                $notificationService->send('reply_approval_needed', $recipients, $data);
            }
        }
    }
    
    private function sendApprovalNotifications($ticketId, $ticket, $user, $action, $reason = null) {
        $notificationService = new NotificationService();
        
        if ($action === 'approved') {
            // Get customer info
            $customer = $this->db->fetch(
                "SELECT customer_id, name, email, mobile FROM customers WHERE customer_id = ?",
                [$ticket['customer_id']]
            );
            
            if ($customer) {
                $data = [
                    'complaint_id' => $ticketId,
                    'customer_name' => $customer['name'],
                    'approved_by' => $user['name']
                ];
                
                $recipients = [[
                    'customer_id' => $customer['customer_id'],
                    'email' => $customer['email'],
                    'mobile' => $customer['mobile'],
                    'complaint_id' => $ticketId
                ]];
                
                $notificationService->send('reply_approved', $recipients, $data);
            }
        } else {
            // Notify assigned controller
            if ($ticket['assigned_to_user_id']) {
                $assignedUser = $this->db->fetch(
                    "SELECT id, name, email, mobile FROM users WHERE id = ?",
                    [$ticket['assigned_to_user_id']]
                );
                
                if ($assignedUser) {
                    $data = [
                        'complaint_id' => $ticketId,
                        'rejected_by' => $user['name'],
                        'reason' => $reason
                    ];
                    
                    $recipients = [[
                        'user_id' => $assignedUser['id'],
                        'email' => $assignedUser['email'],
                        'mobile' => $assignedUser['mobile'],
                        'complaint_id' => $ticketId
                    ]];
                    
                    $notificationService->send('reply_rejected', $recipients, $data);
                }
            }
        }
    }
    
    private function sendRevertNotifications($ticketId, $ticket, $user, $reason) {
        $notificationService = new NotificationService();
        
        // Get customer info
        $customer = $this->db->fetch(
            "SELECT customer_id, name, email, mobile FROM customers WHERE customer_id = ?",
            [$ticket['customer_id']]
        );
        
        if ($customer) {
            $data = [
                'complaint_id' => $ticketId,
                'customer_name' => $customer['name'],
                'reverted_by' => $user['name'],
                'reason' => $reason
            ];
            
            $recipients = [[
                'customer_id' => $customer['customer_id'],
                'email' => $customer['email'],
                'mobile' => $customer['mobile'],
                'complaint_id' => $ticketId
            ]];
            
            $notificationService->send('ticket_reverted', $recipients, $data);
        }
    }
    
    private function sendInfoRequestNotifications($ticketId, $ticket, $user, $infoRequest) {
        $notificationService = new NotificationService();
        
        // Get customer info
        $customer = $this->db->fetch(
            "SELECT customer_id, name, email, mobile FROM customers WHERE customer_id = ?",
            [$ticket['customer_id']]
        );
        
        if ($customer) {
            $data = [
                'complaint_id' => $ticketId,
                'customer_name' => $customer['name'],
                'requested_by' => $user['name'],
                'info_request' => $infoRequest
            ];
            
            $recipients = [[
                'customer_id' => $customer['customer_id'],
                'email' => $customer['email'],
                'mobile' => $customer['mobile'],
                'complaint_id' => $ticketId
            ]];
            
            $notificationService->send('info_requested', $recipients, $data);
        }
    }
    
    private function sendInterimRemarksNotifications($ticketId, $ticket, $user, $interimRemarks) {
        $notificationService = new NotificationService();
        
        // Get customer info
        $customer = $this->db->fetch(
            "SELECT customer_id, name, email, mobile FROM customers WHERE customer_id = ?",
            [$ticket['customer_id']]
        );
        
        if ($customer) {
            $data = [
                'complaint_id' => $ticketId,
                'customer_name' => $customer['name'],
                'updated_by' => $user['name'],
                'interim_remarks' => $interimRemarks
            ];
            
            $recipients = [[
                'customer_id' => $customer['customer_id'],
                'email' => $customer['email'],
                'mobile' => $customer['mobile'],
                'complaint_id' => $ticketId
            ]];
            
            $notificationService->send('interim_remarks_added', $recipients, $data);
        }
    }
    
    private function getSummaryReport($user, $dateFrom, $dateTo, $division) {
        // Implementation for summary report
        return [];
    }
    
    private function getPerformanceReport($user, $dateFrom, $dateTo, $division) {
        // Implementation for performance report
        return [];
    }
    
    private function getSLAReport($user, $dateFrom, $dateTo, $division) {
        // Implementation for SLA report
        return [];
    }
    
    private function getCustomerSatisfactionReport($user, $dateFrom, $dateTo, $division) {
        // Implementation for customer satisfaction report
        return [];
    }
    
    private function getUserGuides() {
        return [
            [
                'title' => 'Ticket Management Guide',
                'description' => 'Learn how to efficiently manage and respond to customer tickets',
                'url' => '/help/ticket-management'
            ],
            [
                'title' => 'Escalation Procedures',
                'description' => 'Understanding when and how to escalate tickets',
                'url' => '/help/escalation'
            ],
            [
                'title' => 'SLA Guidelines',
                'description' => 'Service Level Agreement requirements and deadlines',
                'url' => '/help/sla-guidelines'
            ]
        ];
    }
    
    private function getControllerFAQ() {
        return [
            [
                'question' => 'How do I forward a ticket to another department?',
                'answer' => 'Only nodal controllers can forward tickets. Use the "Forward" button on the ticket details page and select the appropriate user and priority level.'
            ],
            [
                'question' => 'What is the difference between regular and nodal controllers?',
                'answer' => 'Regular controllers handle assigned tickets, while nodal controllers can forward tickets, approve replies, and manage all tickets in their division.'
            ],
            [
                'question' => 'How are SLA deadlines calculated?',
                'answer' => 'SLA deadlines are automatically calculated based on the ticket priority and the predefined resolution hours in the system.'
            ],
            [
                'question' => 'When do I need approval for my replies?',
                'answer' => 'Regular controllers may need approval from nodal controllers for certain types of replies, especially those involving policy decisions or significant commitments.'
            ]
        ];
    }
    
    private function getContactInfo() {
        return [
            'support_email' => 'controller-support@sampark.railway.gov.in',
            'helpline' => '1800-XXX-XXXX',
            'office_hours' => 'Monday to Friday, 9:00 AM to 6:00 PM'
        ];
    }
    
    /**
     * Transform evidence data from new table structure to display format
     */
    private function transformEvidenceForDisplay($evidenceRaw) {
        $evidence = [];
        
        foreach ($evidenceRaw as $record) {
            // Check all three file columns
            for ($i = 1; $i <= 3; $i++) {
                $fileNameField = "file_name_$i";
                $fileTypeField = "file_type_$i";
                $filePathField = "file_path_$i";
                $compressedSizeField = "compressed_size_$i";
                
                if (!empty($record[$fileNameField])) {
                    $evidence[] = [
                        'file_name' => $record[$fileNameField],
                        'original_name' => $record[$fileNameField], // Use file_name as original_name
                        'file_type' => $record[$fileTypeField],
                        'file_path' => $record[$filePathField],
                        'file_size' => $record[$compressedSizeField] ?? 0,
                        'compressed_size' => $record[$compressedSizeField] ?? 0,
                        'uploaded_at' => $record['uploaded_at']
                    ];
                }
            }
        }
        
        return $evidence;
    }
}
