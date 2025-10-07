<?php
/**
 * Controller for SAMPARK - Handles staff/nodal controller operations
 * Manages ticket assignments, forwarding, replies, approvals
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/NotificationService.php';
require_once __DIR__ . '/../utils/BackgroundPriorityService.php';
require_once __DIR__ . '/../utils/OnSiteNotificationService.php';

class ControllerController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireRole(['controller', 'controller_nodal']);
    }
    
    public function dashboard() {
        $user = $this->getCurrentUser();

        // Get ticket stats for overview
        $ticket_stats = $this->getTicketStats($user);

        // Prepare overview stats for dashboard cards (matching admin dashboard structure)
        $overview_stats = [
            'total_complaints' => $ticket_stats['total'] ?? 0,
            'pending_complaints' => $ticket_stats['pending'] ?? 0,
            'closed_complaints' => $ticket_stats['closed'] ?? 0,
            'registered_customers' => $this->getRegisteredCustomersCount($user)
        ];

        // Prepare division stats for the pivot table (filtered for user's access)
        $division_stats = $this->getDivisionStats($user);

        // Prepare performance data
        $performance_data = [
            'avg_resolution_time' => $this->getAverageResolutionTime($user),
            'min_resolution_time' => $this->getMinResolutionTime($user),
            'max_resolution_time' => $this->getMaxResolutionTime($user),
            'resolution_efficiency' => $this->getResolutionEfficiency($user),
            'excellent_ratings' => $this->getRatingCount($user, 'excellent'),
            'satisfactory_ratings' => $this->getRatingCount($user, 'satisfactory'),
            'unsatisfactory_ratings' => $this->getRatingCount($user, 'unsatisfactory'),
            'avg_rating' => $this->getAverageRating($user),
            'type_distribution' => $this->getComplaintTypeDistribution($user),
            'department_stats' => $this->getDepartmentStats($user)
        ];

        // Get other dashboard data
        $terminal_stats = $this->getTerminalStats($user);
        $customer_registration_stats = $this->getCustomerRegistrationStats($user);

        $data = [
            'page_title' => 'Controller Dashboard - SAMPARK',
            'user' => $user,
            'overview_stats' => $overview_stats,
            'performance_data' => $performance_data,
            'division_stats' => $division_stats,
            'terminal_stats' => $terminal_stats,
            'customer_registration_stats' => $customer_registration_stats,
            'dashboard_data' => [
                'ticket_stats' => $ticket_stats,
                'pending_tickets' => $this->getPendingTickets($user),
                'high_priority_tickets' => $this->getHighPriorityTickets($user),
                'escalated_tickets' => $this->getEscalatedTickets($user),
                'recent_activities' => $this->getRecentActivities($user)
            ],
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

        // For controller_nodal: see all tickets in their division
        // For controller: see only tickets assigned to their department
        if ($user['role'] === 'controller_nodal') {
            $conditions[] = 'c.division = ?';
            $params[] = $user['division'];
        } else {
            $conditions[] = 'c.division = ? AND c.assigned_to_department = ?';
            $params[] = $user['division'];
            $params[] = $user['department'];
        }

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
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed
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
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed
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
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed
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

        // Check if ticket is in admin approval workflow
        $isInAdminApproval = ($ticket['status'] === 'awaiting_approval');

        $canForward = in_array($user['role'], ['controller', 'controller_nodal']) &&
                     in_array($ticket['status'], ['pending', 'awaiting_info']) &&
                     !$isForwardedToOtherDept &&
                     !$isAwaitingCustomerInfo &&
                     !$isInAdminApproval;
        
        $canReply = in_array($ticket['status'], ['pending', 'awaiting_info']) &&
                   !$isAssignedToOtherDept &&
                   !$isAwaitingCustomerInfo &&
                   !$isInAdminApproval;
        
        // Per new requirements: Controller_nodal CANNOT take any action when status is 'awaiting_approval'
        // All approval actions are handled by admins only
        // Controller_nodal can only VIEW tickets in awaiting_approval status
        $canApprove = false; // Disabled - only admins can approve now

        $canRevert = $user['role'] === 'controller_nodal' &&
                    $ticket['status'] === 'closed' &&
                    !$isAssignedToOtherDept;

        $canRevertToCustomer = $user['role'] === 'controller_nodal' &&
                              $ticket['status'] === 'pending' &&
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
            'is_in_admin_approval' => $isInAdminApproval,
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

            // Send on-site notification
            $onSiteNotificationService = new OnSiteNotificationService();
            $onSiteNotificationService->notifyUsersOfForwardedTicket($ticketId, $targetDivision, $_POST['department']);
            
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

            // Controller_nodal can only reply to tickets assigned to CML department
            if ($user['role'] === 'controller_nodal' &&
                $ticket['assigned_to_department'] !== 'CML') {
                $this->json(['success' => false, 'message' => 'Controller Nodal can only close tickets assigned to CML department'], 403);
                return;
            }

            $isInterimReply = isset($_POST['is_interim_reply']) && $_POST['is_interim_reply'];
            $internalRemarks = isset($_POST['internal_remarks']) ? trim($_POST['internal_remarks']) : '';

            // Initialize approval stage and determine which department should approve
            $approvalStage = null;
            $approvalDepartment = null;

            // Determine status based on reply type
            if ($isInterimReply) {
                // Interim replies don't change status - just acknowledge receipt
                $newStatus = $ticket['status']; // Keep current status
            } else {
                // Check system settings for admin approval workflow
                $requireDeptAdminApproval = $this->db->fetch(
                    "SELECT setting_value FROM system_settings WHERE setting_key = 'require_dept_admin_approval'"
                );

                if ($requireDeptAdminApproval && $requireDeptAdminApproval['setting_value'] == '1') {
                    // Use new admin approval workflow
                    $newStatus = 'awaiting_approval';

                    // Determine approval workflow based on who is handling the ticket
                    $currentAssignedDept = $ticket['assigned_to_department'];

                    if ($user['role'] === 'controller_nodal' && $currentAssignedDept === 'CML') {
                        // Controller_nodal closing a CML-assigned ticket → Go to CML admin
                        $approvalStage = 'cml_admin';
                        $approvalDepartment = 'CML';
                    } else {
                        // Regular controller closing dept-assigned ticket → Dept admin approval
                        $approvalStage = 'dept_admin';
                        $approvalDepartment = $currentAssignedDept ?: $user['department'];
                    }
                } else {
                    // Fallback to old controller_nodal approval (legacy)
                    $newStatus = 'awaiting_approval';
                    $approvalStage = null; // No approval_stage for legacy workflow
                    $approvalDepartment = 'CML';
                }
            }
            
            // Update ticket only if it's a final reply (not interim)
            if (!$isInterimReply && !empty($_POST['action_taken'])) {
                // Check if this reply is closing the ticket
                $isClosingTicket = ($newStatus === 'awaiting_approval');

                if ($isClosingTicket) {
                    // When closing ticket: set department, set closed_at
                    // NOTE: forwarded_flag is NOT reset here - it stays until CML admin approves
                    // This allows controller_nodal to track forwarded tickets until final approval
                    if ($approvalStage === 'dept_admin' || $approvalStage === 'cml_admin') {
                        // New admin approval workflow - dept/cml admin approval required
                        $sql = "UPDATE complaints SET
                                action_taken = ?,
                                action_taken_by = ?,
                                status = ?,
                                approval_stage = ?,
                                assigned_to_department = ?,
                                department = ?,
                                closed_at = NOW(),
                                updated_at = NOW()
                                WHERE complaint_id = ?";

                        $this->db->query($sql, [
                            trim($_POST['action_taken']),
                            $user['id'],
                            $newStatus,
                            $approvalStage,
                            $approvalDepartment, // Department whose admin needs to approve
                            $approvalDepartment, // Set department to match
                            $ticketId
                        ]);
                    } else {
                        // Old controller_nodal approval workflow (legacy)
                        $sql = "UPDATE complaints SET
                                action_taken = ?,
                                action_taken_by = ?,
                                status = ?,
                                department = ?,
                                assigned_to_department = ?,
                                closed_at = NOW(),
                                updated_at = NOW()
                                WHERE complaint_id = ?";

                        $this->db->query($sql, [
                            trim($_POST['action_taken']),
                            $user['id'],
                            $newStatus,
                            $user['department'],
                            $approvalDepartment,
                            $ticketId
                        ]);
                    }
                } else {
                    // Regular reply update (non-closing)
                    $sql = "UPDATE complaints SET
                            action_taken = ?,
                            action_taken_by = ?,
                            status = ?,
                            updated_at = NOW()
                            WHERE complaint_id = ?";

                    $this->db->query($sql, [
                        trim($_POST['action_taken']),
                        $user['id'],
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

                // Notify all admin, controller, controller_nodal users about internal note
                $this->notifyInternalNote($ticketId, $ticket, $user);
            }

            // Notify users about interim reply
            if ($isInterimReply && !empty($_POST['action_taken'])) {
                $this->notifyInterimReply($ticketId, $ticket, $user);
            }

            // Handle file uploads if any
            if (!empty($_FILES['attachments']['name'][0])) {
                $this->handleEvidenceUpload($ticketId, $_FILES['attachments']);
            }
            
            // Send notifications
            $this->sendReplyNotifications($ticketId, $ticket, $user, $_POST['action_taken'], $newStatus);
            
            $this->db->commit();
            
            // Build appropriate message
            if ($isInterimReply) {
                $message = 'Interim reply sent to customer - ticket remains in current status';
            } else {
                if ($approvalStage === 'dept_admin') {
                    $deptName = $approvalDepartment ?? 'department';
                    $message = "Reply submitted for {$deptName} admin approval";
                } elseif ($approvalStage === 'cml_admin') {
                    $message = 'Reply submitted for CML admin approval';
                } else {
                    $message = 'Reply submitted for approval';
                }
            }

            $response = [
                'success' => true,
                'message' => $message
            ];

            // If ticket is being closed (awaiting approval), redirect to support hub
            if (!$isInterimReply && $newStatus === 'awaiting_approval') {
                $response['redirect'] = Config::getAppUrl() . '/controller/tickets';
            }

            $this->json($response);
            
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
                        action_taken_by = ?,
                        status = 'awaiting_feedback',
                        updated_at = NOW()
                        WHERE complaint_id = ?";

                $this->db->query($sql, [$editedActionTaken, $user['id'], $ticketId]);

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

            // Notification handled by WorkflowEngine to prevent duplicates
            
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

            // Notification handled by WorkflowEngine to prevent duplicates
            
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
                    action_taken_by = ?,
                    status = 'closed',
                    department = ?,
                    forwarded_flag = 0,
                    closed_at = NOW(),
                    updated_at = NOW()
                    WHERE complaint_id = ?";

            $this->db->query($sql, [
                trim($_POST['action_taken']),
                $user['id'],
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
        $currentView = $_GET['view'] ?? null; // User's explicit view choice
        
        $data = [
            'page_title' => 'Reports - SAMPARK',
            'user' => $user,
            'report_type' => $reportType,
            'date_range' => ['from' => $dateFrom, 'to' => $dateTo],
            'division' => $division,
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        // Initialize empty data arrays
        $data['complaints_data'] = [];
        $data['transactions_data'] = [];
        $data['customers_data'] = [];
        $data['available_columns'] = [];

        // Always load all data types so users can switch between views
        $data['complaints_data'] = $this->getComplaintsReport($user, $dateFrom, $dateTo, $division, 'all');
        $data['customers_data'] = $this->getCustomersReport($user, $dateFrom, $dateTo, $division);
        $data['transactions_data'] = $this->getTransactionsReport($user, $dateFrom, $dateTo, $division);

        switch ($reportType) {
            case 'summary':
                $data['report_data'] = $this->getSummaryReport($user, $dateFrom, $dateTo, $division);
                if (!$currentView) $_GET['view'] = 'complaints'; // Default view only if not set
                break;
            case 'performance':
                $data['report_data'] = $this->getPerformanceReport($user, $dateFrom, $dateTo, $division);
                if (!$currentView) $_GET['view'] = 'complaints';
                break;
                $data['report_data'] = [];
                if (!$currentView) $_GET['view'] = 'complaints';
                break;
            case 'customer_satisfaction':
                $data['report_data'] = $this->getCustomerSatisfactionReport($user, $dateFrom, $dateTo, $division);
                $data['complaints_data'] = $this->getComplaintsReport($user, $dateFrom, $dateTo, $division, 'closed');
                if (!$currentView) $_GET['view'] = 'complaints';
                break;
            case 'total_complaints':
                $data['report_data'] = ['type' => 'complaints', 'title' => 'All Complaints Report'];
                if (!$currentView) $_GET['view'] = 'complaints';
                break;
            case 'pending_complaints':
                $data['complaints_data'] = $this->getComplaintsReport($user, $dateFrom, $dateTo, $division, 'pending');
                $data['report_data'] = ['type' => 'complaints', 'title' => 'Pending Complaints Report'];
                if (!$currentView) $_GET['view'] = 'complaints';
                break;
            case 'closed_complaints':
                $data['complaints_data'] = $this->getComplaintsReport($user, $dateFrom, $dateTo, $division, 'closed');
                $data['report_data'] = ['type' => 'complaints', 'title' => 'Closed Complaints Report'];
                if (!$currentView) $_GET['view'] = 'complaints';
                break;
            case 'registered_customers':
                $data['report_data'] = ['type' => 'customers', 'title' => 'Registered Customers Report'];
                if (!$currentView) $_GET['view'] = 'customers'; // Default to customers view for this report
                break;
            default:
                $data['report_data'] = $this->getSummaryReport($user, $dateFrom, $dateTo, $division);
                if (!$currentView) $_GET['view'] = 'complaints';
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
        $condition = 'c.division = ?';
        $param = $user['division'];

        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN c.status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN c.status = 'awaiting_info' THEN 1 ELSE 0 END) as awaiting_info,
                    SUM(CASE WHEN c.status = 'awaiting_approval' THEN 1 ELSE 0 END) as awaiting_approval,
                    SUM(CASE WHEN c.status = 'awaiting_dept_admin_approval' THEN 1 ELSE 0 END) as awaiting_dept_admin_approval,
                    SUM(CASE WHEN c.status = 'awaiting_cml_admin_approval' THEN 1 ELSE 0 END) as awaiting_cml_admin_approval,
                    SUM(CASE WHEN c.status = 'awaiting_feedback' THEN 1 ELSE 0 END) as awaiting_feedback,
                    SUM(CASE WHEN c.status = 'closed' THEN 1 ELSE 0 END) as closed,
                    SUM(CASE WHEN c.priority IN ('high', 'critical') THEN 1 ELSE 0 END) as high_priority_count
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
                            AND c.status IN ('pending', 'awaiting_info', 'awaiting_approval', 'awaiting_dept_admin_approval', 'awaiting_cml_admin_approval')";

            $forwardedResult = $this->db->fetch($forwardedSql, [$user['division']]);
            $stats['forwarded_complaints'] = $forwardedResult['forwarded_complaints'] ?? 0;
        } else {
            $stats['forwarded_complaints'] = 0;
        }

        return $stats;
    }
    
    private function getPendingTickets($user, $limit = 10) {
        $condition = 'c.division = ?';
        $param = $user['division'];
        
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
        $condition = 'c.division = ?';
        $param = $user['division'];
        
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
        $condition = 'c.division = ?';
        $param = $user['division'];
        
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
        $condition = 'c.division = ?';
        $param = $user['division'];
        
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

            // Notify admin when ticket is awaiting approval
            if ($status === 'awaiting_approval') {
                require_once '../src/models/NotificationModel.php';
                $notificationModel = new NotificationModel();

                // Get ticket details to check approval_stage
                $ticketDetails = $this->db->fetch(
                    "SELECT approval_stage, department, assigned_to_department FROM complaints WHERE complaint_id = ?",
                    [$ticketId]
                );

                $approvalStage = $ticketDetails['approval_stage'] ?? null;
                $targetDepartment = $ticketDetails['department'] ?? $ticketDetails['assigned_to_department'];

                if ($approvalStage === 'dept_admin') {
                    // Notify department admin
                    $deptAdmins = $this->db->fetchAll(
                        "SELECT id, name FROM users WHERE role = 'admin' AND department = ? AND status = 'active'",
                        [$targetDepartment]
                    );

                    foreach ($deptAdmins as $admin) {
                        $notificationModel->createNotification([
                            'user_id' => $admin['id'],
                            'user_type' => 'admin',
                            'title' => 'Ticket Awaiting Your Approval',
                            'message' => "Controller {$user['name']} has closed ticket #{$ticketId} in {$targetDepartment} department. Your approval is required.",
                            'type' => 'approval_pending',
                            'priority' => 'high',
                            'related_id' => $ticketId,
                            'related_type' => 'ticket',
                            'action_url' => Config::getAppUrl() . '/admin/tickets/' . $ticketId . '/view',
                            'complaint_id' => $ticketId,
                        ]);
                    }
                } elseif ($approvalStage === 'cml_admin') {
                    // Notify CML admin
                    $cmlAdmins = $this->db->fetchAll(
                        "SELECT id, name FROM users WHERE role = 'admin' AND department = 'CML' AND status = 'active'"
                    );

                    foreach ($cmlAdmins as $admin) {
                        $notificationModel->createNotification([
                            'user_id' => $admin['id'],
                            'user_type' => 'admin',
                            'title' => 'CML Approval Required',
                            'message' => "Ticket #{$ticketId} from {$targetDepartment} department is awaiting your CML admin approval.",
                            'type' => 'approval_pending',
                            'priority' => 'high',
                            'related_id' => $ticketId,
                            'related_type' => 'ticket',
                            'action_url' => Config::getAppUrl() . '/admin/tickets/' . $ticketId . '/view',
                            'complaint_id' => $ticketId,
                        ]);
                    }
                }
            }
        } catch (Exception $e) {
            // Log error but don't fail the reply process
            error_log("Reply notification error: " . $e->getMessage());
        }
    }

    /**
     * Notify users when internal note is added
     */
    private function notifyInternalNote($ticketId, $ticket, $author) {
        try {
            require_once '../src/models/NotificationModel.php';
            require_once '../src/models/UserModel.php';

            $notificationModel = new NotificationModel();
            $userModel = new UserModel();

            // Get all active admin, controller, controller_nodal users
            $usersToNotify = $userModel->findAll(['status' => 'active']);

            foreach ($usersToNotify as $user) {
                // Skip the author, superadmin, and customers
                if ($user['id'] == $author['id'] ||
                    $user['role'] === 'superadmin' ||
                    $user['role'] === 'customer') {
                    continue;
                }

                // Only notify admin, controller, controller_nodal
                if (!in_array($user['role'], ['admin', 'controller', 'controller_nodal'])) {
                    continue;
                }

                $actionUrl = $this->getTicketUrlByRole($ticketId, $user['role']);

                $notificationModel->createNotification([
                    'user_id' => $user['id'],
                    'user_type' => $user['role'],
                    'title' => 'Internal Note Added',
                    'message' => "{$author['name']} added an internal note to ticket #{$ticketId} regarding {$ticket['category']} - {$ticket['type']}. Please review.",
                    'type' => 'internal_note',
                    'priority' => 'medium',
                    'related_id' => $ticketId,
                    'related_type' => 'ticket',
                    'action_url' => $actionUrl,
                    'complaint_id' => $ticketId,
                ]);
            }
        } catch (Exception $e) {
            error_log("Internal note notification error: " . $e->getMessage());
        }
    }

    /**
     * Notify users when interim reply is sent
     */
    private function notifyInterimReply($ticketId, $ticket, $author) {
        try {
            require_once '../src/models/NotificationModel.php';
            require_once '../src/models/UserModel.php';

            $notificationModel = new NotificationModel();
            $userModel = new UserModel();

            // Get all active admin, controller, controller_nodal users
            $usersToNotify = $userModel->findAll(['status' => 'active']);

            foreach ($usersToNotify as $user) {
                // Skip the author, superadmin, and customers
                if ($user['id'] == $author['id'] ||
                    $user['role'] === 'superadmin' ||
                    $user['role'] === 'customer') {
                    continue;
                }

                // Only notify admin, controller, controller_nodal
                if (!in_array($user['role'], ['admin', 'controller', 'controller_nodal'])) {
                    continue;
                }

                $actionUrl = $this->getTicketUrlByRole($ticketId, $user['role']);

                $notificationModel->createNotification([
                    'user_id' => $user['id'],
                    'user_type' => $user['role'],
                    'title' => 'Interim Reply Sent',
                    'message' => "{$author['name']} sent an interim reply to ticket #{$ticketId} regarding {$ticket['category']} - {$ticket['type']}. The ticket remains in current status.",
                    'type' => 'interim_reply',
                    'priority' => 'medium',
                    'related_id' => $ticketId,
                    'related_type' => 'ticket',
                    'action_url' => $actionUrl,
                    'complaint_id' => $ticketId,
                ]);
            }
        } catch (Exception $e) {
            error_log("Interim reply notification error: " . $e->getMessage());
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
                
                $notificationService->send('awaiting_feedback', $recipients, $data);
            }
        }
    }
    
    private function sendRevertNotifications($ticketId, $ticket, $user, $reason) {
        try {
            require_once '../src/utils/NotificationService.php';
            $notificationService = new NotificationService();

            // Get customer info
            $customer = $this->db->fetch(
                "SELECT customer_id, name, email, mobile, company_name FROM customers WHERE customer_id = ?",
                [$ticket['customer_id']]
            );

            if ($customer) {
                // Send awaiting info notification (revert means asking for more info)
                $notificationService->sendTicketAwaitingInfo($ticketId, $customer, $reason);
            }

        } catch (Exception $e) {
            // Log error but don't fail the revert process
            error_log("Revert notification error: " . $e->getMessage());
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

        // Notify admin of the department when ticket is closed
        require_once '../src/utils/OnSiteNotificationService.php';
        $onSiteNotificationService = new OnSiteNotificationService();

        // Get admin users in the same department
        $adminUsers = $this->db->fetchAll(
            "SELECT id, name FROM users WHERE role = 'admin' AND department = ? AND status = 'active'",
            [$user['department']]
        );

        foreach ($adminUsers as $admin) {
            $notificationModel = new NotificationModel();
            $notificationModel->createNotification([
                'user_id' => $admin['id'],
                'user_type' => 'admin',
                'title' => 'Ticket Closed by Controller',
                'message' => "Controller {$user['name']} has closed ticket #{$ticketId} in {$user['department']} department. Awaiting approval.",
                'type' => 'ticket_closed',
                'priority' => 'medium',
                'related_id' => $ticketId,
                'related_type' => 'ticket',
                'action_url' => Config::getAppUrl() . '/admin/tickets/' . $ticketId . '/view',
                'complaint_id' => $ticketId,
            ]);
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
                'title' => 'Priority Guidelines',
                'description' => 'Ticket prioritization and urgency levels',
                'url' => '/help/priority-guidelines'
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

    // Additional dashboard data methods

    private function getRegisteredCustomersCount($user) {
        if ($user['role'] === 'controller') {
            // For regular controllers, count customers in their department
            $sql = "SELECT COUNT(DISTINCT customer_id) as count FROM complaints
                   WHERE division = ? AND assigned_to_department = ?";
            $result = $this->db->fetch($sql, [$user['division'], $user['department']]);
        } else {
            // For nodal controllers, count customers in their division
            $sql = "SELECT COUNT(DISTINCT customer_id) as count FROM complaints WHERE division = ?";
            $result = $this->db->fetch($sql, [$user['division']]);
        }
        return $result['count'] ?? 0;
    }

    private function getDivisionStats($user) {
        $division_stats = [];

        if ($user['role'] === 'controller_nodal') {
            // For nodal controllers, show division-wide stats
            $sql = "SELECT
                       division,
                       COUNT(*) as total,
                       SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                       SUM(CASE WHEN status = 'awaiting_feedback' THEN 1 ELSE 0 END) as awaiting_feedback,
                       SUM(CASE WHEN status = 'awaiting_info' THEN 1 ELSE 0 END) as awaiting_info,
                       SUM(CASE WHEN status = 'awaiting_approval' THEN 1 ELSE 0 END) as awaiting_approval,
                       SUM(CASE WHEN status = 'awaiting_dept_admin_approval' THEN 1 ELSE 0 END) as awaiting_dept_admin_approval,
                       SUM(CASE WHEN status = 'awaiting_cml_admin_approval' THEN 1 ELSE 0 END) as awaiting_cml_admin_approval,
                       SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
                    FROM complaints
                    WHERE division = ?
                    GROUP BY division";
            $results = $this->db->fetchAll($sql, [$user['division']]);

            foreach ($results as $row) {
                $division_stats[$row['division']] = [
                    'pending' => $row['pending'],
                    'awaiting_feedback' => $row['awaiting_feedback'],
                    'awaiting_info' => $row['awaiting_info'],
                    'awaiting_approval' => $row['awaiting_approval'],
                    'awaiting_dept_admin_approval' => $row['awaiting_dept_admin_approval'],
                    'awaiting_cml_admin_approval' => $row['awaiting_cml_admin_approval'],
                    'closed' => $row['closed'],
                    'total' => $row['total']
                ];
            }
        } else {
            // For regular controllers, show their division only
            $sql = "SELECT
                       COUNT(*) as total,
                       SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                       SUM(CASE WHEN status = 'awaiting_feedback' THEN 1 ELSE 0 END) as awaiting_feedback,
                       SUM(CASE WHEN status = 'awaiting_info' THEN 1 ELSE 0 END) as awaiting_info,
                       SUM(CASE WHEN status = 'awaiting_approval' THEN 1 ELSE 0 END) as awaiting_approval,
                       SUM(CASE WHEN status = 'awaiting_dept_admin_approval' THEN 1 ELSE 0 END) as awaiting_dept_admin_approval,
                       SUM(CASE WHEN status = 'awaiting_cml_admin_approval' THEN 1 ELSE 0 END) as awaiting_cml_admin_approval,
                       SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
                    FROM complaints
                    WHERE division = ? AND assigned_to_department = ?";
            $result = $this->db->fetch($sql, [$user['division'], $user['department']]);

            $division_stats[$user['division']] = [
                'pending' => $result['pending'] ?? 0,
                'awaiting_feedback' => $result['awaiting_feedback'] ?? 0,
                'awaiting_info' => $result['awaiting_info'] ?? 0,
                'awaiting_approval' => $result['awaiting_approval'] ?? 0,
                'awaiting_dept_admin_approval' => $result['awaiting_dept_admin_approval'] ?? 0,
                'awaiting_cml_admin_approval' => $result['awaiting_cml_admin_approval'] ?? 0,
                'closed' => $result['closed'] ?? 0,
                'total' => $result['total'] ?? 0
            ];
        }

        return $division_stats;
    }

    private function getDepartmentStats($user) {
        if ($user['role'] !== 'controller_nodal') {
            return [];
        }

        $sql = "SELECT
                   assigned_to_department,
                   COUNT(*) as total,
                   SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                   SUM(CASE WHEN status = 'awaiting_feedback' THEN 1 ELSE 0 END) as awaiting_feedback,
                   SUM(CASE WHEN status = 'awaiting_info' THEN 1 ELSE 0 END) as awaiting_info,
                   SUM(CASE WHEN status = 'awaiting_approval' THEN 1 ELSE 0 END) as awaiting_approval,
                   SUM(CASE WHEN status = 'awaiting_dept_admin_approval' THEN 1 ELSE 0 END) as awaiting_dept_admin_approval,
                   SUM(CASE WHEN status = 'awaiting_cml_admin_approval' THEN 1 ELSE 0 END) as awaiting_cml_admin_approval,
                   SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
                FROM complaints
                WHERE division = ?
                GROUP BY assigned_to_department";

        $results = $this->db->fetchAll($sql, [$user['division']]);
        $department_stats = [];

        foreach ($results as $row) {
            $department_stats[$row['assigned_to_department'] ?? 'Unassigned'] = [
                'pending' => $row['pending'],
                'awaiting_feedback' => $row['awaiting_feedback'],
                'awaiting_info' => $row['awaiting_info'],
                'awaiting_approval' => $row['awaiting_approval'],
                'awaiting_dept_admin_approval' => $row['awaiting_dept_admin_approval'],
                'awaiting_cml_admin_approval' => $row['awaiting_cml_admin_approval'],
                'closed' => $row['closed'],
                'total' => $row['total']
            ];
        }

        return $department_stats;
    }

    private function getAverageResolutionTime($user) {
        $condition = $user['role'] === 'controller'
            ? 'division = ? AND assigned_to_department = ?'
            : 'division = ?';
        $params = $user['role'] === 'controller'
            ? [$user['division'], $user['department']]
            : [$user['division']];

        $sql = "SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as avg_time
                FROM complaints
                WHERE {$condition} AND status = 'closed' AND closed_at IS NOT NULL";

        $result = $this->db->fetch($sql, $params);
        return round($result['avg_time'] ?? 24, 1);
    }

    private function getMinResolutionTime($user) {
        $condition = $user['role'] === 'controller'
            ? 'division = ? AND assigned_to_department = ?'
            : 'division = ?';
        $params = $user['role'] === 'controller'
            ? [$user['division'], $user['department']]
            : [$user['division']];

        $sql = "SELECT MIN(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as min_time
                FROM complaints
                WHERE {$condition} AND status = 'closed' AND closed_at IS NOT NULL";

        $result = $this->db->fetch($sql, $params);
        return round($result['min_time'] ?? 2, 1);
    }

    private function getMaxResolutionTime($user) {
        $condition = $user['role'] === 'controller'
            ? 'division = ? AND assigned_to_department = ?'
            : 'division = ?';
        $params = $user['role'] === 'controller'
            ? [$user['division'], $user['department']]
            : [$user['division']];

        $sql = "SELECT MAX(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as max_time
                FROM complaints
                WHERE {$condition} AND status = 'closed' AND closed_at IS NOT NULL";

        $result = $this->db->fetch($sql, $params);
        return round($result['max_time'] ?? 72, 1);
    }

    private function getResolutionEfficiency($user) {
        $condition = $user['role'] === 'controller'
            ? 'division = ? AND assigned_to_department = ?'
            : 'division = ?';
        $params = $user['role'] === 'controller'
            ? [$user['division'], $user['department']]
            : [$user['division']];

        $sql = "SELECT
                    COUNT(*) as total_closed,
                    SUM(CASE WHEN TIMESTAMPDIFF(HOUR, created_at, closed_at) <= 48 THEN 1 ELSE 0 END) as resolved_on_time
                FROM complaints
                WHERE {$condition} AND status = 'closed' AND closed_at IS NOT NULL";

        $result = $this->db->fetch($sql, $params);
        $total = $result['total_closed'] ?? 0;
        $onTime = $result['resolved_on_time'] ?? 0;

        // Handle division by zero - return 0% if no closed tickets
        if ($total == 0) {
            return 0;
        }

        return round(($onTime / $total) * 100, 1);
    }

    private function getRatingCount($user, $rating) {
        $condition = $user['role'] === 'controller'
            ? 'division = ? AND assigned_to_department = ?'
            : 'division = ?';
        $params = $user['role'] === 'controller'
            ? [$user['division'], $user['department']]
            : [$user['division']];

        $sql = "SELECT COUNT(*) as count
                FROM complaints
                WHERE {$condition} AND rating = ?";

        $params[] = $rating;
        $result = $this->db->fetch($sql, $params);
        return $result['count'] ?? 0;
    }

    private function getAverageRating($user) {
        $condition = $user['role'] === 'controller'
            ? 'division = ? AND assigned_to_department = ?'
            : 'division = ?';
        $params = $user['role'] === 'controller'
            ? [$user['division'], $user['department']]
            : [$user['division']];

        $sql = "SELECT AVG(
                    CASE
                        WHEN rating = 'excellent' THEN 5
                        WHEN rating = 'satisfactory' THEN 3
                        WHEN rating = 'unsatisfactory' THEN 1
                        ELSE 3
                    END
                ) as avg_rating
                FROM complaints
                WHERE {$condition} AND rating IS NOT NULL";

        $result = $this->db->fetch($sql, $params);
        return round($result['avg_rating'] ?? 4.2, 1);
    }

    private function getComplaintTypeDistribution($user) {
        $condition = $user['role'] === 'controller'
            ? 'c.division = ? AND c.assigned_to_department = ?'
            : 'c.division = ?';
        $params = $user['role'] === 'controller'
            ? [$user['division'], $user['department']]
            : [$user['division']];

        $sql = "SELECT cat.category, COUNT(*) as count
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                WHERE {$condition}
                GROUP BY cat.category";

        $results = $this->db->fetchAll($sql, $params);
        $distribution = [];

        foreach ($results as $row) {
            $distribution[$row['category'] ?? 'Uncategorized'] = $row['count'];
        }

        return $distribution;
    }

    private function getTerminalStats($user) {
        $condition = $user['role'] === 'controller'
            ? 'c.division = ? AND c.assigned_to_department = ?'
            : 'c.division = ?';
        $params = $user['role'] === 'controller'
            ? [$user['division'], $user['department']]
            : [$user['division']];

        $sql = "SELECT s.name as terminal, COUNT(*) as count
                FROM complaints c
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                WHERE {$condition}
                GROUP BY s.name";

        $results = $this->db->fetchAll($sql, $params);
        $terminal_stats = [];

        foreach ($results as $row) {
            $terminal_stats[$row['terminal'] ?? 'Unknown'] = $row['count'];
        }

        return $terminal_stats;
    }

    private function getCustomerRegistrationStats($user) {
        $condition = $user['role'] === 'controller'
            ? 'u.division = ? AND u.department = ?'
            : 'u.division = ?';
        $params = $user['role'] === 'controller'
            ? [$user['division'], $user['department']]
            : [$user['division']];

        $sql = "SELECT
                    DATE_FORMAT(u.created_at, '%Y-%m') as month,
                    COUNT(*) as registrations
                FROM users u
                WHERE {$condition} AND u.role = 'customer'
                GROUP BY DATE_FORMAT(u.created_at, '%Y-%m')
                ORDER BY month DESC
                LIMIT 12";

        $results = $this->db->fetchAll($sql, $params);
        $registration_stats = [];

        foreach ($results as $row) {
            $registration_stats[$row['month']] = $row['registrations'];
        }

        return $registration_stats;
    }

    private function getComplaintsReport($user, $dateFrom, $dateTo, $division, $status = 'all') {
        $conditions = [];
        $params = [];

        // Role-based access control
        if ($user['role'] === 'controller') {
            $conditions[] = 'c.division = ? AND c.assigned_to_department = ?';
            $params[] = $user['division'];
            $params[] = $user['department'];
        } else {
            $conditions[] = 'c.division = ?';
            $params[] = $division;
        }

        // Date range filter
        if ($dateFrom) {
            $conditions[] = 'c.created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo) {
            $conditions[] = 'c.created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }

        // Status filter
        if ($status !== 'all') {
            $conditions[] = 'c.status = ?';
            $params[] = $status;
        }

        $whereClause = implode(' AND ', $conditions);

        $sql = "SELECT
                    c.complaint_id,
                    c.date,
                    c.time,
                    c.created_at,
                    c.updated_at,
                    c.status,
                    c.priority,
                    c.description,
                    c.action_taken,
                    c.division,
                    c.assigned_to_department,
                    c.fnr_number,
                    c.e_indent_number,
                    cat.category,
                    cat.subtype as type,
                    s.name as shed_name,
                    cust.name as customer_name,
                    cust.mobile as customer_mobile,
                    cust.company_name,
                    TIMESTAMPDIFF(HOUR, c.created_at, COALESCE(c.closed_at, NOW())) as duration_hours
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                WHERE {$whereClause}
                ORDER BY c.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    private function getCustomersReport($user, $dateFrom, $dateTo, $division) {
        $conditions = [];
        $params = [];

        // Role-based access control - filter customers by division from their complaints
        if ($user['role'] === 'controller') {
            $conditions[] = 'c.division = ? AND c.assigned_to_department = ?';
            $params[] = $user['division'];
            $params[] = $user['department'];
        } else {
            $conditions[] = 'c.division = ?';
            $params[] = $division;
        }

        // Date range filter on customer registration
        if ($dateFrom) {
            $conditions[] = 'cust.created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo) {
            $conditions[] = 'cust.created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }

        $whereClause = implode(' AND ', $conditions);

        $sql = "SELECT DISTINCT
                    cust.customer_id,
                    cust.name,
                    cust.email,
                    cust.mobile,
                    cust.company_name,
                    cust.customer_type,
                    cust.designation,
                    cust.gstin,
                    cust.created_at,
                    cust.status,
                    COUNT(c.complaint_id) as total_complaints
                FROM customers cust
                LEFT JOIN complaints c ON cust.customer_id = c.customer_id
                WHERE {$whereClause}
                GROUP BY cust.customer_id, cust.name, cust.email, cust.mobile, cust.company_name,
                         cust.customer_type, cust.designation, cust.gstin, cust.created_at, cust.status
                ORDER BY cust.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    private function getTransactionsReport($user, $dateFrom, $dateTo, $division) {
        $conditions = [];
        $params = [];

        // Role-based access control - filter transactions by division from complaints
        if ($user['role'] === 'controller') {
            $conditions[] = 'c.division = ? AND c.assigned_to_department = ?';
            $params[] = $user['division'];
            $params[] = $user['department'];
        } else {
            $conditions[] = 'c.division = ?';
            $params[] = $division;
        }

        // Date range filter on transaction creation
        if ($dateFrom) {
            $conditions[] = 't.created_at >= ?';
            $params[] = $dateFrom . ' 00:00:00';
        }
        if ($dateTo) {
            $conditions[] = 't.created_at <= ?';
            $params[] = $dateTo . ' 23:59:59';
        }

        $whereClause = implode(' AND ', $conditions);

        $sql = "SELECT
                    t.transaction_id,
                    t.complaint_id,
                    t.transaction_type,
                    t.from_division,
                    t.to_division,
                    '' as old_status,
                    '' as new_status,
                    t.remarks,
                    t.created_at,
                    COALESCE(u.name, cust.name, 'System') as user_name
                FROM transactions t
                LEFT JOIN complaints c ON t.complaint_id = c.complaint_id
                LEFT JOIN users u ON t.created_by_id = u.id
                LEFT JOIN customers cust ON t.created_by_customer_id = cust.customer_id
                WHERE {$whereClause}
                ORDER BY t.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    // New RBAC-based ticket view methods

    /**
     * Display tickets for controller's department only (RBAC restricted)
     */
    public function myDepartmentTickets() {
        $user = $this->getCurrentUser();

        if ($user['role'] !== 'controller') {
            $this->redirect('/controller/dashboard');
        }

        // Get filters from query parameters
        $filters = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];

        // Get paginated tickets for this department only
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 25;
        $tickets = $this->getDepartmentTickets($user, $page, $perPage, $filters);

        $data = [
            'page_title' => 'My Department Tickets - SAMPARK',
            'user' => $user,
            'tickets' => $tickets,
            'filters' => $filters
        ];

        $this->view('controller/my-department-tickets', $data);
    }

    /**
     * Display tickets for controller_nodal's division (RBAC restricted)
     */
    public function myDivisionTickets() {
        $user = $this->getCurrentUser();

        if ($user['role'] !== 'controller_nodal') {
            $this->redirect('/controller/dashboard');
        }

        // Get filters from query parameters
        $filters = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'department' => $_GET['department'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];

        // Get paginated tickets for this division
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 25;
        $tickets = $this->getDivisionTickets($user, $page, $perPage, $filters);

        // Get departments in this division for filter
        $departments = $this->getDepartmentsInDivision($user['division']);

        $data = [
            'page_title' => 'My Division Tickets - SAMPARK',
            'user' => $user,
            'tickets' => $tickets,
            'filters' => $filters,
            'departments' => $departments
        ];

        $this->view('controller/my-division-tickets', $data);
    }

    /**
     * Search all tickets without RBAC restrictions
     */
    public function searchAllTickets() {
        $user = $this->getCurrentUser();

        $data = [
            'page_title' => 'Search All Tickets - SAMPARK',
            'user' => $user
        ];

        if ($user['role'] === 'controller_nodal') {
            $this->view('controller/search-all-tickets-nodal', $data);
        } else {
            $this->view('controller/search-all-tickets', $data);
        }
    }

    /**
     * AJAX endpoint for search tickets data
     */
    public function searchAllTicketsData() {
        // Simple AJAX check - if not AJAX, return JSON error
        if (!$this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'AJAX request required']);
            return;
        }

        $user = $this->getCurrentUser();

        // Get search parameters
        $search = [
            'complaint_number' => $_POST['complaint_number'] ?? '',
            'date_from' => $_POST['date_from'] ?? '',
            'date_to' => $_POST['date_to'] ?? '',
            'customer_mobile' => $_POST['customer_mobile'] ?? '',
            'customer_email' => $_POST['customer_email'] ?? '',
            'status' => $_POST['status'] ?? '',
            'priority' => $_POST['priority'] ?? '',
            'zone' => $_POST['zone'] ?? '',
            'division' => $_POST['division'] ?? '',
            'department' => $_POST['department'] ?? ''
        ];

        // Validate that at least one search criteria is provided
        $hasSearchCriteria = false;
        foreach ($search as $value) {
            if (!empty(trim($value))) {
                $hasSearchCriteria = true;
                break;
            }
        }

        if (!$hasSearchCriteria) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Please provide at least one search criterion'
            ]);
            return;
        }

        try {
            $results = $this->performTicketSearch($search);

            // Format data for DataTables
            $data = [];
            foreach ($results as $ticket) {
                $row = [
                    $ticket['complaint_id'],
                    $ticket['description'] ?? '',
                    $ticket['customer_name'] ?? 'N/A',
                    $ticket['shed_name'] ?? 'N/A',
                    $ticket['category'] ?? 'N/A',
                    $ticket['status'],
                    $ticket['priority'],
                    $ticket['zone'] ?? 'N/A',
                    $ticket['division'] ?? 'N/A',
                    $ticket['assigned_to'] ?? 'Unassigned',
                    date('M d, Y H:i', strtotime($ticket['created_at'])),
                    '' // Actions column will be rendered by frontend
                ];
                $data[] = $row;
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'draw' => intval($_POST['draw'] ?? 1),
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data),
                'data' => $data
            ]);

        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ]);
        }
    }

    // Helper methods for new functionality

    private function getDepartmentTickets($user, $page, $perPage, $filters) {
        // Controller sees only CLOSED tickets from their department
        $conditions = ['c.assigned_to_department = ?', 'c.status = ?'];
        $params = [$user['department'], 'closed'];

        // Apply filters (status is fixed to 'closed' for controllers)
        if (!empty($filters['priority'])) {
            $conditions[] = 'c.priority = ?';
            $params[] = $filters['priority'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = 'c.created_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = 'c.created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $whereClause = implode(' AND ', $conditions);
        $offset = ($page - 1) * $perPage;

        // Get tickets
        $sql = "SELECT c.complaint_id, c.status, c.priority, c.description, c.created_at,
                       cat.category, cat.type, cat.subtype,
                       cust.name as customer_name, cust.company_name,
                       s.name as shed_name
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                WHERE {$whereClause}
                ORDER BY c.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $ticketData = $this->db->fetchAll($sql, $params);

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM complaints c WHERE {$whereClause}";
        $countParams = array_slice($params, 0, -2); // Remove LIMIT and OFFSET params
        $totalResult = $this->db->fetch($countSql, $countParams);
        $total = $totalResult['total'] ?? 0;

        // Get stats
        $statsParams = array_slice($params, 0, -2); // Remove LIMIT and OFFSET params
        $statsSql = "SELECT
                        SUM(CASE WHEN c.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                        SUM(CASE WHEN c.priority IN ('high', 'critical') THEN 1 ELSE 0 END) as high_priority_count,
                        SUM(CASE WHEN c.created_at >= CURDATE() AND c.status = 'closed' THEN 1 ELSE 0 END) as resolved_today_count,
                        SUM(CASE WHEN c.created_at < DATE_SUB(NOW(), INTERVAL 48 HOUR) AND c.status IN ('pending', 'awaiting_info') THEN 1 ELSE 0 END) as overdue_count
                     FROM complaints c WHERE {$whereClause}";

        $stats = $this->db->fetch($statsSql, $statsParams);

        return [
            'data' => $ticketData,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
            'has_prev' => $page > 1,
            'has_next' => $page < ceil($total / $perPage),
            'pending' => $stats['pending_count'] ?? 0,
            'high_priority' => $stats['high_priority_count'] ?? 0,
            'resolved_today' => $stats['resolved_today_count'] ?? 0,
            'overdue' => $stats['overdue_count'] ?? 0
        ];
    }

    private function getDivisionTickets($user, $page, $perPage, $filters) {
        // Controller nodal sees ALL tickets in their division (any status)
        $conditions = ['c.division = ?'];
        $params = [$user['division']];

        // Apply filters
        if (!empty($filters['status'])) {
            $conditions[] = 'c.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $conditions[] = 'c.priority = ?';
            $params[] = $filters['priority'];
        }

        if (!empty($filters['department'])) {
            $conditions[] = 'c.assigned_to_department = ?';
            $params[] = $filters['department'];
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = 'c.created_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = 'c.created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $whereClause = implode(' AND ', $conditions);
        $offset = ($page - 1) * $perPage;

        // Get tickets with assignment information
        $sql = "SELECT c.complaint_id, c.status, c.priority, c.description, c.created_at,
                       c.assigned_to_department, c.division, c.zone,
                       cat.category, cat.type, cat.subtype,
                       cust.name as customer_name, cust.company_name,
                       s.name as shed_name
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                WHERE {$whereClause}
                ORDER BY c.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $perPage;
        $params[] = $offset;

        $ticketData = $this->db->fetchAll($sql, $params);

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM complaints c WHERE {$whereClause}";
        $countParams = array_slice($params, 0, -2); // Remove LIMIT and OFFSET params
        $totalResult = $this->db->fetch($countSql, $countParams);
        $total = $totalResult['total'] ?? 0;

        // Get stats specific to controller_nodal
        $statsParams = array_slice($params, 0, -2);
        $statsSql = "SELECT
                        SUM(CASE WHEN c.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                        SUM(CASE WHEN c.priority IN ('high', 'critical') THEN 1 ELSE 0 END) as high_priority_count,
                        SUM(CASE WHEN c.forwarded_flag = 1 THEN 1 ELSE 0 END) as forwarded_count,
                        SUM(CASE WHEN c.status = 'awaiting_approval' THEN 1 ELSE 0 END) as awaiting_approval_count,
                        SUM(CASE WHEN c.status = 'awaiting_dept_admin_approval' THEN 1 ELSE 0 END) as awaiting_dept_admin_approval_count,
                        SUM(CASE WHEN c.status = 'awaiting_cml_admin_approval' THEN 1 ELSE 0 END) as awaiting_cml_admin_approval_count
                     FROM complaints c WHERE {$whereClause}";

        $stats = $this->db->fetch($statsSql, $statsParams);

        return [
            'data' => $ticketData,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage),
            'has_prev' => $page > 1,
            'has_next' => $page < ceil($total / $perPage),
            'pending' => $stats['pending_count'] ?? 0,
            'high_priority' => $stats['high_priority_count'] ?? 0,
            'forwarded' => $stats['forwarded_count'] ?? 0,
            'awaiting_approval' => $stats['awaiting_approval_count'] ?? 0,
            'awaiting_dept_admin_approval' => $stats['awaiting_dept_admin_approval_count'] ?? 0,
            'awaiting_cml_admin_approval' => $stats['awaiting_cml_admin_approval_count'] ?? 0
        ];
    }

    private function getDepartmentsInDivision($division) {
        $sql = "SELECT DISTINCT department_code, department_name
                FROM departments
                WHERE is_active = 1
                ORDER BY department_name";
        return $this->db->fetchAll($sql);
    }

    private function performTicketSearch($search) {
        $conditions = [];
        $params = [];

        // Build search conditions
        if (!empty($search['complaint_number'])) {
            $conditions[] = 'c.complaint_id LIKE ?';
            $params[] = '%' . $search['complaint_number'] . '%';
        }

        if (!empty($search['date_from'])) {
            $conditions[] = 'c.created_at >= ?';
            $params[] = $search['date_from'] . ' 00:00:00';
        }

        if (!empty($search['date_to'])) {
            $conditions[] = 'c.created_at <= ?';
            $params[] = $search['date_to'] . ' 23:59:59';
        }

        if (!empty($search['customer_mobile'])) {
            $conditions[] = 'cust.mobile LIKE ?';
            $params[] = '%' . $search['customer_mobile'] . '%';
        }

        if (!empty($search['customer_email'])) {
            $conditions[] = 'cust.email LIKE ?';
            $params[] = '%' . $search['customer_email'] . '%';
        }

        if (!empty($search['status'])) {
            $conditions[] = 'c.status = ?';
            $params[] = $search['status'];
        }

        if (!empty($search['priority'])) {
            $conditions[] = 'c.priority = ?';
            $params[] = $search['priority'];
        }

        if (!empty($search['zone'])) {
            $conditions[] = 'c.zone = ?';
            $params[] = $search['zone'];
        }

        if (!empty($search['division'])) {
            $conditions[] = 'c.division = ?';
            $params[] = $search['division'];
        }

        if (!empty($search['department'])) {
            $conditions[] = 'c.assigned_to_department = ?';
            $params[] = $search['department'];
        }

        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT c.complaint_id, c.status, c.priority, c.description, c.created_at,
                       c.zone, c.division, c.assigned_to_department,
                       cat.category, cat.type, cat.subtype,
                       cust.name as customer_name, cust.company_name,
                       s.name as shed_name,
                       c.assigned_to_department as assigned_to
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                {$whereClause}
                ORDER BY c.created_at DESC
                LIMIT 1000"; // Limit search results for performance

        return $this->db->fetchAll($sql, $params);
    }

    public function updateProfile() {
        try {
            $this->validateCSRF();
            $user = $this->getCurrentUser();

            $validator = new Validator();
            $isValid = $validator->validate($_POST, [
                'name' => 'required|min:2|max:100',
                'email' => 'required|email|unique:users,email,' . $user['id'] . ',id',
                'mobile' => 'phone'
            ]);

            if (!$isValid) {
                $this->json([
                    'success' => false,
                    'errors' => $validator->getErrors()
                ], 400);
                return;
            }

            $sql = "UPDATE users SET
                    name = ?,
                    email = ?,
                    mobile = ?,
                    updated_at = NOW()
                    WHERE id = ?";

            $this->db->query($sql, [
                trim($_POST['name']),
                trim($_POST['email']),
                trim($_POST['mobile']) ?: null,
                $user['id']
            ]);

            // Update session data
            $this->session->set('user_name', $_POST['name']);
            $this->session->set('user_email', $_POST['email']);

            $this->logActivity('profile_updated', ['user_id' => $user['id']]);

            $this->json([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);

        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to update profile. Please try again.'
            ], 500);
        }
    }

    public function changePassword() {
        $this->validateCSRF();
        $user = $this->getCurrentUser();

        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required'
        ]);

        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }

        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $this->json([
                'success' => false,
                'message' => 'New password and confirmation do not match'
            ], 400);
            return;
        }

        // Get current password hash from database
        $userData = $this->db->fetch(
            "SELECT password FROM users WHERE id = ?",
            [$user['id']]
        );

        if (!$userData) {
            $this->json([
                'success' => false,
                'message' => 'User account not found'
            ], 404);
            return;
        }

        // Verify current password
        if (!password_verify($_POST['current_password'], $userData['password'])) {
            $this->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
            return;
        }

        try {
            // Update password
            $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

            $this->db->query(
                "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?",
                [$hashedPassword, $user['id']]
            );

            $this->logActivity('password_changed', ['user_id' => $user['id']]);

            $this->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);

        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to change password. Please try again.'
            ], 500);
        }
    }
}
