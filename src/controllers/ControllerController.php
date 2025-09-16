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

        $conditions[] = 'c.division = ? AND c.assigned_to_department = ?';
            $params[] = $user['division'];
            $params[] = $user['department'];
        
        

        // Exclude closed complaints by default
        $conditions[] = "c.status != 'closed'";

        // For controller_nodal: exclude tickets that were forwarded OUT to other divisions
        // These should not appear anywhere in the original division
        if ($user['role'] === 'controller_nodal') {
            $conditions[] = 'NOT EXISTS (
                SELECT 1 FROM transactions t
                WHERE t.complaint_id = c.complaint_id
                AND t.transaction_type = "forwarded"
                AND t.from_division = ?
                AND t.to_division != ?
                AND t.created_at = (
                    SELECT MAX(t2.created_at)
                    FROM transactions t2
                    WHERE t2.complaint_id = c.complaint_id
                    AND t2.transaction_type = "forwarded"
                )
            )';
            $params[] = $user['division']; // from_division
            $params[] = $user['division']; // to_division (should be different)
        }
        
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
                       0 as is_sla_violated
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
        
        // Build query conditions for forwarded tickets
        // Only show tickets that controller_nodal forwarded within the division
        $conditions = [];
        $params = [];

        // Only show tickets currently assigned to this division
        $conditions[] = 'c.division = ?';
        $params[] = $user['division'];

        // Only show tickets that have been forwarded within the same division (forwarded_flag = 1)
        $conditions[] = 'c.forwarded_flag = 1';

        // Only show tickets forwarded BY controller_nodal (not controller forwards back)
        $conditions[] = 'EXISTS (
            SELECT 1 FROM transactions t
            JOIN users u ON t.created_by_id = u.id
            WHERE t.complaint_id = c.complaint_id
            AND t.transaction_type = "forwarded"
            AND u.role = "controller_nodal"
            AND t.created_at = (
                SELECT MAX(t2.created_at)
                FROM transactions t2
                WHERE t2.complaint_id = c.complaint_id
                AND t2.transaction_type = "forwarded"
            )
        )';

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
                       0 as is_sla_violated
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
                       0 as is_sla_violated
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
        
        // Get ticket transactions - separate customer-facing from internal
        $transactionSql = "SELECT t.*, 
                                  u.name as user_name, u.role as user_role, u.department as user_department, 
                                  u.division as user_division, u.zone as user_zone,
                                  cust.name as customer_name,
                                  CASE 
                                      WHEN t.remarks_type = 'customer_remarks' THEN 'customer_facing'
                                      WHEN t.remarks_type = 'interim_remarks' AND t.transaction_type = 'interim_reply' THEN 'customer_facing'
                                      ELSE 'internal_only'
                                  END as visibility
                           FROM transactions t
                           LEFT JOIN users u ON t.created_by_id = u.id
                           LEFT JOIN customers cust ON t.created_by_customer_id = cust.customer_id
                           WHERE t.complaint_id = ? 
                           ORDER BY t.created_at DESC";
        
        $transactions = $this->db->fetchAll($transactionSql, [$ticketId]);
        
        // Separate priority changes from regular transactions and organize by visibility
        $regularTransactions = [];
        $priorityChanges = [];
        $customerVisibleTransactions = [];
        $latestImportantRemark = null;
        $latestInterimReply = null;
        
        foreach ($transactions as $transaction) {
            if ($transaction['remarks_type'] === 'priority_escalation') {
                $priorityChanges[] = $transaction;
            } elseif ($transaction['remarks_type'] === 'admin_remarks') {
                // Skip admin remarks - they are displayed separately
                continue;
            } else {
                $regularTransactions[] = $transaction;
                
                // Separate customer-facing transactions
                if ($transaction['visibility'] === 'customer_facing') {
                    $customerVisibleTransactions[] = $transaction;
                    
                    // Track latest interim reply for prominent display
                    if ($transaction['transaction_type'] === 'interim_reply' && !$latestInterimReply) {
                        $latestInterimReply = $transaction;
                    }
                }
                
                // Track latest important remark (excluding system, priority escalation, and admin remarks)
                if (!$latestImportantRemark && !in_array($transaction['remarks_type'], ['priority_escalation', 'system', 'admin_remarks'])) {
                    // Prioritize certain remark types for display
                    $importantTypes = ['forwarding_remarks', 'interim_remarks', 'internal_remarks', 'customer_remarks', 'edit_audit'];
                    $remarksText = !empty($transaction['remarks']) ? $transaction['remarks'] : $transaction['internal_remarks'];
                    if (in_array($transaction['remarks_type'], $importantTypes) && !empty(trim($remarksText))) {
                        $latestImportantRemark = $transaction;
                    }
                }
            }
        }
        
        // If no important remark found, get the latest non-system transaction
        if (!$latestImportantRemark && !empty($regularTransactions)) {
            $reversed = array_reverse($regularTransactions);
            foreach ($reversed as $transaction) {
                $remarksText = !empty($transaction['remarks']) ? $transaction['remarks'] : $transaction['internal_remarks'];
                if (!empty(trim($remarksText)) && $transaction['remarks_type'] !== 'system') {
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

        // Get admin remarks history
        $adminRemarksSql = "SELECT t.*, u.name as user_name, u.role as user_role, u.department as user_department,
                                   u.division as user_division, u.zone as user_zone
                           FROM transactions t
                           LEFT JOIN users u ON t.created_by_id = u.id
                           WHERE t.complaint_id = ?
                           AND t.remarks_type = 'admin_remarks'
                           AND t.remarks IS NOT NULL AND t.remarks != ''
                           ORDER BY t.created_at DESC";
        $adminRemarks = $this->db->fetchAll($adminRemarksSql, [$ticketId]);
        
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
        
        // For controller_nodal: normally they can act on all tickets in their division
        // Only restrict actions if this is a ticket forwarded FROM another division TO this division
        // (i.e., tickets in forwarded tickets view should have limited actions)
        $isAssignedToOtherDept = ($user['role'] === 'controller_nodal' &&
                                  $ticket['assigned_to_department'] !== $user['department'] &&
                                  $ticket['forwarded_flag'] == 1);
        
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
        
        $canInterimRemarks = $user['role'] === 'controller_nodal' &&
                            $ticket['status'] === 'pending' &&
                            !$isAssignedToOtherDept;

        // Internal remarks permissions based on role - these should NOT be restricted by $isAssignedToOtherDept
        if ($user['role'] === 'controller_nodal') {
            // Controller_nodal can add internal remarks to any pending ticket in their division
            $canInternalRemarks = $ticket['status'] === 'pending' && 
                                $ticket['division'] === $user['division'];
        } elseif ($user['role'] === 'controller') {
            // Controller can add internal remarks to tickets assigned to their department
            $canInternalRemarks = $ticket['status'] === 'pending' && 
                                $ticket['assigned_to_department'] === $user['department'];
        } else {
            $canInternalRemarks = false;
        }
        
        $data = [
            'page_title' => 'Ticket #' . $ticketId . ' - SAMPARK',
            'user' => $user,
            'ticket' => $ticket,
            'transactions' => $regularTransactions,
            'priority_changes' => $priorityChanges,
            'customer_visible_transactions' => $customerVisibleTransactions,
            'latest_important_remark' => $latestImportantRemark,
            'latest_interim_reply' => $latestInterimReply,
            'admin_remarks' => $adminRemarks,
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
                'can_interim_remarks' => $canInterimRemarks,
                'can_internal_remarks' => $canInternalRemarks
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
            'internal_remarks' => 'required|min:10|max:1000'
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

            // Determine forwarded_flag based on forwarding rules:
            // 1. controller_nodal forwards to controller_nodal: forwarded_flag = 0
            // 2. controller_nodal forwards to controller: forwarded_flag = 1
            // 3. controller forwards to controller_nodal: forwarded_flag = 0
            // 4. Cross-division forwarding: forwarded_flag = 0 (ownership transfer)

            if ($targetDivision !== $user['division']) {
                // Cross-division forwarding - ownership transfer
                $forwardedFlag = 0;
            } else {
                // Intra-division forwarding - determine based on source and target roles
                if ($user['role'] === 'controller_nodal') {
                    // Controller_nodal forwarding within division
                    // Assumption: if forwarding to same department, it's to another controller_nodal
                    // if forwarding to different department, it's to a controller
                    if ($departmentValue === $user['department']) {
                        // Forwarding to same department (controller_nodal to controller_nodal)
                        $forwardedFlag = 0;
                    } else {
                        // Forwarding to different department (controller_nodal to controller)
                        $forwardedFlag = 1;
                    }
                } else {
                    // Controller forwarding within division (controller to controller_nodal)
                    $forwardedFlag = 0;
                }
            }

            $sql = "UPDATE complaints SET
                    assigned_to_department = ?,
                    division = ?,
                    zone = ?,
                    priority = ?,
                    forwarded_flag = ?,
                    " . ($resetEscalation ? "escalated_at = NULL," : "") . "
                    updated_at = NOW()
                    WHERE complaint_id = ?";

            $this->db->query($sql, [
                $departmentValue,
                $targetDivision,
                $targetZone,
                $newPriority,
                $forwardedFlag,
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
            $this->createTransaction($ticketId, 'forwarded', $_POST['internal_remarks'], $user['id'], null, 'forwarding_remarks');
            
            // Send notifications to target department
            $this->sendForwardNotifications($ticketId, $ticket, $user, $targetDivision, $_POST['internal_remarks']);
            
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
            'action_taken' => 'required|min:10|max:2000',
            'internal_remarks' => 'max:1000',
            'needs_approval' => 'boolean',
            'is_interim_reply' => 'boolean'
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
            $internalRemarks = isset($_POST['internal_remarks']) ? trim($_POST['internal_remarks']) : '';
            
            // Determine status based on reply type
            if ($isInterimReply) {
                // Interim replies don't change status - just acknowledge receipt
                $newStatus = $ticket['status']; // Keep current status
            } else {
                // All tickets need controller_nodal approval - no exceptions
                $newStatus = 'awaiting_approval';
            }
            
            // Update ticket only if it's a final reply (not interim)
            if (!$isInterimReply && !empty($_POST['action_taken'])) {
                // Check if this reply is closing the ticket (when status moves to awaiting_approval)
                $isClosingTicket = ($newStatus === 'awaiting_approval');
                
                if ($isClosingTicket) {
                    // When closing ticket: set department, reset forwarded_flag, set closed_at
                    // Set assigned_to_department to controller_nodal for approval workflow
                    $sql = "UPDATE complaints SET
                            action_taken = ?,
                            status = ?,
                            department = ?,
                            assigned_to_department = 'CML',
                            forwarded_flag = 0,
                            closed_at = NOW(),
                            updated_at = NOW()
                            WHERE complaint_id = ?";

                    $this->db->query($sql, [
                        trim($_POST['action_taken']),
                        $newStatus,
                        $user['department'],
                        $ticketId
                    ]);
                } else {
                    // Regular reply update
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
            }
            
            // Create transaction records - separate for action_taken and internal_remarks
            $transactionType = $isInterimReply ? 'interim_reply' : 'replied';
            
            // Action taken goes to customer (customer-facing)
            if (!empty($_POST['action_taken'])) {
                $this->createTransaction($ticketId, $transactionType, $_POST['action_taken'], $user['id'], null, 'customer_remarks');
            }
            
            // Internal remarks are internal only
            if (!empty($internalRemarks)) {
                $this->createTransaction($ticketId, $transactionType, $internalRemarks, $user['id'], null, 'internal_remarks');
            }
            
            // Handle file uploads if any
            if (!empty($_FILES['attachments']['name'][0])) {
                $this->handleEvidenceUpload($ticketId, $_FILES['attachments']);
            }
            
            // Send notifications
            $this->sendReplyNotifications($ticketId, $ticket, $user, $_POST['action_taken'], $newStatus);
            
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
            'approval_remarks' => 'max:500',
            'edited_action_taken' => 'max:2000'
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
            
            // Controller_nodal can only approve tickets assigned to controller_nodal
            if ($ticket['assigned_to_department'] !== 'CML') {
                $this->json(['success' => false, 'message' => 'This ticket is not ready for approval'], 400);
                return;
            }

            // Handle edited action_taken if provided
            $editedActionTaken = trim($_POST['edited_action_taken'] ?? '');
            if (!empty($editedActionTaken) && $editedActionTaken !== $ticket['action_taken']) {
                // Save original reply as transaction before updating
                $this->createTransaction($ticketId, 'original_reply_archived', $ticket['action_taken'], $user['id'], null, 'internal_remarks');

                // Update with edited reply
                $sql = "UPDATE complaints SET
                        action_taken = ?,
                        status = 'awaiting_feedback',
                        updated_at = NOW()
                        WHERE complaint_id = ?";

                $this->db->query($sql, [$editedActionTaken, $ticketId]);

                // Create transaction for new action taken (customer-facing)
                $this->createTransaction($ticketId, 'reply_approved_edited', $editedActionTaken, $user['id'], null, 'customer_remarks');

                // Create audit transaction for the edit
                $editAuditMessage = "Edited action taken: " . $editedActionTaken . " - Edited by: " . $user['name'] . ", " .$user['department'] ;
                $this->createTransaction($ticketId, 'edit_audit', $editAuditMessage, $user['id'], null, 'internal_remarks');
            } else {
                // No edits, just update status
                $sql = "UPDATE complaints SET
                        status = 'awaiting_feedback',
                        updated_at = NOW()
                        WHERE complaint_id = ?";

                $this->db->query($sql, [$ticketId]);

                // Create transaction for regular approval (customer-facing)
                $this->createTransaction($ticketId, 'reply_approved', $ticket['action_taken'], $user['id'], null, 'customer_remarks');
            }

            // Stop escalation for tickets awaiting feedback
            $priorityService = new BackgroundPriorityService();
            $priorityService->stopEscalationForStatus($ticketId, 'awaiting_feedback');

            // Create approval remarks transaction if provided
            if (!empty(trim($_POST['approval_remarks']))) {
                $this->createTransaction($ticketId, 'approval_remarks', trim($_POST['approval_remarks']), $user['id'], null, 'internal_remarks');
            }
            
            // Send notifications
            $this->sendApprovalNotifications($ticketId, $ticket, $user, 'approved');
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Reply approved and ticket sent for customer feedback'
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
            'internal_remarks' => 'required|min:10|max:500'
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
            
            // Controller_nodal can only reject tickets assigned to controller_nodal
            if ($ticket['assigned_to_department'] !== 'controller_nodal') {
                $this->json(['success' => false, 'message' => 'This ticket is not ready for rejection'], 400);
                return;
            }
            
            // Update ticket status back to pending, restore assigned_to_department, and set forwarded_flag
            $sql = "UPDATE complaints SET
                    status = 'pending',
                    assigned_to_department = department,
                    forwarded_flag = 1,
                    updated_at = NOW()
                    WHERE complaint_id = ?";

            $this->db->query($sql, [$ticketId]);
            
            // Create transaction record
            $this->createTransaction($ticketId, 'rejected', trim($_POST['internal_remarks']), $user['id'], null, 'internal_remarks');
            
            // Send notifications
            $this->sendApprovalNotifications($ticketId, $ticket, $user, 'rejected', $_POST['internal_remarks']);
            
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
            'internal_remarks' => 'required|min:10|max:500'
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
            $this->createTransaction($ticketId, 'reverted', trim($_POST['internal_remarks']), $user['id'], null, 'internal_remarks');
            
            // Send notifications
            $this->sendRevertNotifications($ticketId, $ticket, $user, $_POST['internal_remarks']);
            
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
            'info_request' => 'required|min:0|max:1000'
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
            $remarks = 'Additional information requested: ' . trim($_POST['info_request']);
            $this->createTransaction($ticketId, 'info_requested', $remarks, $user['id'], null, 'customer_remarks');
            
            // Send notification to customer
            $customer = $this->db->fetch(
                "SELECT customer_id, name, email, mobile, company_name FROM customers WHERE customer_id = ?",
                [$ticket['customer_id']]
            );

            if ($customer) {
                $this->sendInfoRequestNotification($ticketId, $customer, $_POST['info_request']);
            }
            
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
    
    public function closeTicket($ticketId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'action_taken' => 'required|min:20|max:2000',
            'internal_remarks' => 'max:1000'
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
                "SELECT * FROM complaints WHERE complaint_id = ? AND {$accessCondition} AND status IN ('pending', 'awaiting_info', 'awaiting_approval')",
                $accessParams
            );
            
            if (!$ticket) {
                $this->json(['success' => false, 'message' => 'Invalid ticket or cannot close'], 400);
                return;
            }
            
            // Update ticket with action taken, set status to closed, set closing department, and reset forwarding flag
            $sql = "UPDATE complaints SET 
                    action_taken = ?,
                    status = 'closed',
                    department = ?,
                    forwarded_flag = 0,
                    closed_at = NOW(),
                    updated_at = NOW()
                    WHERE complaint_id = ?";
            
            $this->db->query($sql, [
                trim($_POST['action_taken']),
                $user['department'],
                $ticketId
            ]);
            
            // Create transaction records - separate for action_taken and internal_remarks
            // Action taken goes to customer (customer-facing)
            $this->createTransaction($ticketId, 'closed', $_POST['action_taken'], $user['id'], null, 'customer_remarks');
            
            // Internal remarks are internal only
            if (!empty($_POST['internal_remarks'])) {
                $this->createTransaction($ticketId, 'closed', $_POST['internal_remarks'], $user['id'], null, 'internal_remarks');
            }
            
            // Create transaction for nodal approval workflow
            $this->createTransaction($ticketId, 'awaiting_nodal_approval', 'Ticket closed and sent to Controller Nodal for approval', $user['id'], null, 'internal_remarks');
            
            // Send notifications
            $this->sendCloseNotifications($ticketId, $ticket, $user, $_POST['action_taken']);
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Ticket closed successfully and forwarded to Controller Nodal for approval'
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Close ticket error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to close ticket. Please try again.'
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

    public function addInternalRemarks($ticketId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        // Only controllers and nodal controllers can add internal remarks
        if (!in_array($user['role'], ['controller', 'controller_nodal'])) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'internal_remarks' => 'required|min:5|max:1000'
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

            // Verify ticket access based on role
            $accessConditions = "complaint_id = ? AND status = 'pending'";
            $accessParams = [$ticketId];
            
            if ($user['role'] === 'controller_nodal') {
                // Controller_nodal can add internal remarks to any pending ticket in their division
                $accessConditions .= " AND division = ?";
                $accessParams[] = $user['division'];
            } elseif ($user['role'] === 'controller') {
                // Controller can add internal remarks to tickets assigned to their department
                $accessConditions .= " AND assigned_to_department = ?";
                $accessParams[] = $user['department'];
            }

            $ticket = $this->db->fetch("SELECT * FROM complaints WHERE {$accessConditions}", $accessParams);

            if (!$ticket) {
                $this->db->rollback();
                $this->json(['success' => false, 'message' => 'Invalid ticket, wrong status, or access denied'], 400);
                return;
            }

            // Create transaction record for internal remarks (doesn't change ticket status)
            $this->createTransaction($ticketId, 'internal_note', trim($_POST['internal_remarks']), $user['id'], null, 'internal_remarks');

            $this->db->commit();

            $this->json([
                'success' => true,
                'message' => 'Internal note added successfully'
            ]);

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Internal remarks error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to add internal note. Please try again.'
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
        if ($user['role'] === 'controller') {
            $condition = 'c.assigned_to_user_id = ?';
            $param = $user['id'];
        } else {
            $condition = 'c.division = ?';
            $param = $user['division'];
        }

        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN c.status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN c.status = 'awaiting_info' THEN 1 ELSE 0 END) as awaiting_info,
                    SUM(CASE WHEN c.status = 'awaiting_approval' THEN 1 ELSE 0 END) as awaiting_approval,
                    SUM(CASE WHEN c.status = 'awaiting_feedback' THEN 1 ELSE 0 END) as awaiting_feedback,
                    SUM(CASE WHEN c.status = 'closed' THEN 1 ELSE 0 END) as closed,
                    SUM(CASE WHEN c.priority IN ('high', 'critical') THEN 1 ELSE 0 END) as high_priority_count,
                FROM complaints c
                WHERE {$condition}";

        $stats = $this->db->fetch($sql, [$param]);

        // Add forwarded complaints count for controller_nodal
        if ($user['role'] === 'controller_nodal') {
            // Count tickets forwarded within this division (intra-division forwards only)
            $forwardedSql = "SELECT COUNT(*) as forwarded_complaints
                            FROM complaints c
                            WHERE c.division = ?
                            AND c.forwarded_flag = 1
                            AND c.status IN ('pending', 'awaiting_info', 'awaiting_approval')";

            $forwardedResult = $this->db->fetch($forwardedSql, [$user['division']]);
            $stats['forwarded_complaints'] = $forwardedResult['forwarded_complaints'] ?? 0;
        } else {
            $stats['forwarded_complaints'] = 0;
        }

        return $stats;
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
        
        // No longer tracking SLA violations - return empty array
        return [];
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
        
        // No longer tracking SLA deadlines
    }
    
    private function createTransaction($complaintId, $type, $remarks, $fromUserId, $toUserId = null, $remarksType = 'internal_remarks') {
        $sql = "INSERT INTO transactions (
            complaint_id, transaction_type, remarks, internal_remarks, remarks_type,
            from_user_id, to_user_id, 
            created_by_id, created_by_type, created_by_role, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'user', ?, NOW())";
        
        // Determine if this goes into remarks or internal_remarks column
        $remarksField = null;
        $internalRemarksField = null;
        
        if ($remarksType === 'customer_remarks') {
            $remarksField = $remarks; // Customer-facing content
        } else {
            $internalRemarksField = $remarks; // Internal content
        }
        
        $this->db->query($sql, [
            $complaintId,
            $type,
            $remarksField,
            $internalRemarksField,
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
        try {
            require_once '../src/utils/NotificationService.php';
            $notificationService = new NotificationService();

            if ($status === 'awaiting_feedback') {
                // Get customer info
                $customer = $this->db->fetch(
                    "SELECT customer_id, name, email, mobile, company_name FROM customers WHERE customer_id = ?",
                    [$ticket['customer_id']]
                );

                if ($customer) {
                    // Send awaiting feedback notification
                    $notificationService->sendTicketAwaitingFeedback($ticketId, $customer, $reply);
                }
            }
        } catch (Exception $e) {
            // Log error but don't fail the reply process
            error_log("Reply notification error: " . $e->getMessage());
        }
    }

    private function sendInfoRequestNotification($ticketId, $customer, $infoRequest) {
        try {
            require_once '../src/utils/NotificationService.php';
            $notificationService = new NotificationService();

            // Send awaiting info notification
            $notificationService->sendTicketAwaitingInfo($ticketId, $customer, $infoRequest);

        } catch (Exception $e) {
            // Log error but don't fail the info request process
            error_log("Info request notification error: " . $e->getMessage());
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
    
    private function sendCloseNotifications($ticketId, $ticket, $user, $actionTaken) {
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
                'closed_by' => $user['name'],
                'action_taken' => $actionTaken
            ];
            
            $recipients = [[
                'customer_id' => $customer['customer_id'],
                'email' => $customer['email'],
                'mobile' => $customer['mobile'],
                'complaint_id' => $ticketId
            ]];
            
            $notificationService->send('ticket_closed', $recipients, $data);
        }
        
        // Notify controller_nodal for approval
        $nodalController = $this->db->fetch(
            "SELECT id, name, email, mobile FROM users WHERE role = 'controller_nodal' AND division = ? AND status = 'active' LIMIT 1",
            [$user['division']]
        );
        
        if ($nodalController) {
            $data = [
                'complaint_id' => $ticketId,
                'closed_by' => $user['name'],
                'department' => $user['department']
            ];
            
            $recipients = [[
                'user_id' => $nodalController['id'],
                'email' => $nodalController['email'],
                'mobile' => $nodalController['mobile'],
                'complaint_id' => $ticketId
            ]];
            
            $notificationService->send('closed_ticket_approval_needed', $recipients, $data);
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
