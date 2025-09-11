<?php
/**
 * Controller for SAMPARK - Handles staff/nodal controller operations
 * Manages ticket assignments, forwarding, replies, approvals
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/NotificationService.php';

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
        $division = $_GET['division'] ?? '';
        
        // Build query conditions based on user role and department access
        $conditions = [];
        $params = [];
        
        // All controller_nodals in Commercial department can see tickets in their division/zone
        $conditions[] = 'c.division = ? AND c.assigned_to_department = ?';
        $params[] = $user['division'];
        $params[] = $user['department'];
        
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
        
        if ($division && $user['role'] === 'controller_nodal') {
            $conditions[] = 'c.division = ?';
            $params[] = $division;
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT c.*, 
                       cat.category, cat.type, cat.subtype,
                       s.name as shed_name, s.shed_code,
                       cust.name as customer_name, cust.email as customer_email,
                       cust.company_name, cust.mobile as customer_mobile,
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed,
                       CASE 
                           WHEN c.sla_deadline IS NOT NULL AND NOW() > c.sla_deadline THEN 1
                           ELSE 0
                       END as is_sla_violated
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
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
                'date_to' => $dateTo,
                'division' => $division
            ],
            'status_options' => Config::TICKET_STATUS,
            'priority_options' => Config::PRIORITY_LEVELS,
            'divisions' => $this->getDivisions(),
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('controller/tickets', $data);
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
                WHERE c.complaint_id = ? AND c.division = ? AND c.assigned_to_department = ?";
        
        $params = [$ticketId, $user['division'], $user['department']];
        
        $ticket = $this->db->fetch($sql, $params);
        
        if (!$ticket) {
            $this->setFlash('error', 'Ticket not found or access denied');
            $this->redirect(Config::APP_URL . '/controller/tickets');
            return;
        }
        
        // Get ticket transactions
        $transactionSql = "SELECT t.*, 
                                  u.name as user_name, u.role as user_role,
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
        
        // Transform evidence data for display
        $evidence = $this->transformEvidenceForDisplay($evidenceRaw);
        
        // Get available users for forwarding (if nodal controller)
        $availableUsers = [];
        if ($user['role'] === 'controller_nodal') {
            $availableUsers = $this->getAvailableUsers($ticket['division']);
        }
        
        // Check permissions for actions
        $canForward = $user['role'] === 'controller_nodal' && in_array($ticket['status'], ['pending', 'awaiting_info']);
        $canReply = in_array($ticket['status'], ['pending', 'awaiting_info']);
        $canApprove = $user['role'] === 'controller_nodal' && $ticket['status'] === 'awaiting_approval';
        $canRevert = $user['role'] === 'controller_nodal' && in_array($ticket['status'], ['awaiting_approval', 'closed']);
        
        $data = [
            'page_title' => 'Ticket #' . $ticketId . ' - SAMPARK',
            'user' => $user,
            'ticket' => $ticket,
            'transactions' => $transactions,
            'evidence' => $evidence,
            'available_users' => $availableUsers,
            'permissions' => [
                'can_forward' => $canForward,
                'can_reply' => $canReply,
                'can_approve' => $canApprove,
                'can_revert' => $canRevert
            ],
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('controller/ticket-details', $data);
    }
    
    public function forwardTicket($ticketId) {
        $this->validateCSRF();
        $user = $this->getCurrentUser();
        
        // Only nodal controllers can forward
        if ($user['role'] !== 'controller_nodal') {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'to_division' => 'required',
            'to_department' => 'required',
            'remarks' => 'required|min:10|max:1000',
            'priority' => 'required|in:normal,medium,high,critical'
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
            $ticket = $this->db->fetch(
                "SELECT * FROM complaints WHERE complaint_id = ? AND division = ? AND status IN ('pending', 'awaiting_info')",
                [$ticketId, $user['division']]
            );
            
            if (!$ticket) {
                $this->json(['success' => false, 'message' => 'Invalid ticket or cannot forward'], 400);
                return;
            }
            
            // Update ticket - Reset priority to normal when forwarded to another division  
            $newPriority = $_POST['priority'];
            if ($_POST['to_division'] !== $ticket['division']) {
                $newPriority = 'normal'; // Priority resets when forwarded to another division
            }
            
            $sql = "UPDATE complaints SET 
                    assigned_to_department = ?,
                    division = ?,
                    priority = ?,
                    forwarded_flag = 1,
                    updated_at = NOW()
                    WHERE complaint_id = ?";
            
            $this->db->query($sql, [
                $_POST['to_department'],
                $_POST['to_division'],
                $newPriority,
                $ticketId
            ]);
            
            // Update SLA deadline based on new priority
            $this->updateSLADeadline($ticketId, $newPriority);
            
            // Create transaction record
            $this->createTransaction($ticketId, 'forwarded', $_POST['remarks'], $user['id']);
            
            // Send notifications to target department
            $this->sendForwardNotifications($ticketId, $ticket, $user, $_POST['to_division'], $_POST['to_department'], $_POST['remarks']);
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Ticket forwarded successfully to ' . $_POST['to_department'] . ' department in ' . $_POST['to_division'] . ' division'
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
            'action_taken' => 'required|min:10|max:1000',
            'needs_approval' => 'boolean'
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
            $accessCondition = $user['role'] === 'controller' ? 
                "assigned_to_user_id = ?" : "division = ?";
            $accessParam = $user['role'] === 'controller' ? $user['id'] : $user['division'];
            
            $ticket = $this->db->fetch(
                "SELECT * FROM complaints WHERE complaint_id = ? AND {$accessCondition} AND status IN ('pending', 'awaiting_info')",
                [$ticketId, $accessParam]
            );
            
            if (!$ticket) {
                $this->json(['success' => false, 'message' => 'Invalid ticket or cannot reply'], 400);
                return;
            }
            
            $needsApproval = isset($_POST['needs_approval']) && $_POST['needs_approval'] && $user['role'] === 'controller';
            $newStatus = $needsApproval ? 'awaiting_approval' : 'awaiting_feedback';
            
            // Update ticket
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
            
            // Create transaction record
            $this->createTransaction($ticketId, 'replied', $_POST['reply'], $user['id']);
            
            // Handle file uploads if any
            if (!empty($_FILES['attachments']['name'][0])) {
                $this->handleEvidenceUpload($ticketId, $_FILES['attachments']);
            }
            
            // Send notifications
            $this->sendReplyNotifications($ticketId, $ticket, $user, $_POST['reply'], $newStatus);
            
            $this->db->commit();
            
            $message = $needsApproval ? 
                'Reply submitted for approval' : 
                'Reply sent to customer successfully';
            
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
            
            // Update ticket status
            $sql = "UPDATE complaints SET 
                    status = 'awaiting_feedback',
                    updated_at = NOW()
                    WHERE complaint_id = ?";
            
            $this->db->query($sql, [$ticketId]);
            
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
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'awaiting_info' THEN 1 ELSE 0 END) as awaiting_info,
                    SUM(CASE WHEN status = 'awaiting_approval' THEN 1 ELSE 0 END) as awaiting_approval,
                    SUM(CASE WHEN status = 'awaiting_feedback' THEN 1 ELSE 0 END) as awaiting_feedback,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
                    SUM(CASE WHEN priority IN ('high', 'critical') THEN 1 ELSE 0 END) as high_priority_count,
                    SUM(CASE WHEN sla_deadline IS NOT NULL AND NOW() > sla_deadline AND status != 'closed' THEN 1 ELSE 0 END) as sla_violations
                FROM complaints 
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
                    AVG(TIMESTAMPDIFF(HOUR, created_at, COALESCE(closed_at, NOW()))) as avg_resolution_hours,
                    SUM(CASE WHEN status = 'closed' AND rating = 'excellent' THEN 1 ELSE 0 END) as excellent_ratings,
                    SUM(CASE WHEN status = 'closed' AND rating = 'satisfactory' THEN 1 ELSE 0 END) as satisfactory_ratings,
                    SUM(CASE WHEN status = 'closed' AND rating = 'unsatisfactory' THEN 1 ELSE 0 END) as unsatisfactory_ratings
                FROM complaints 
                WHERE {$condition} 
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
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
    
    private function createTransaction($complaintId, $type, $remarks, $fromUserId, $toUserId = null) {
        $sql = "INSERT INTO transactions (
            complaint_id, transaction_type, remarks, 
            from_user_id, to_user_id, 
            created_by_id, created_by_type, created_by_role, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, 'user', ?, NOW())";
        
        $this->db->query($sql, [
            $complaintId,
            $type,
            $remarks,
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
    
    private function sendForwardNotifications($ticketId, $ticket, $fromUser, $toUser, $remarks) {
        $notificationService = new NotificationService();
        
        // Notify the assigned user
        $notificationService->createNotification([
            'user_id' => $toUser['id'],
            'title' => 'Ticket Assigned',
            'message' => "Ticket #{$ticketId} has been assigned to you by {$fromUser['name']}",
            'type' => 'info',
            'complaint_id' => $ticketId,
            'action_url' => Config::APP_URL . "/controller/tickets/{$ticketId}"
        ]);
        
        // Send email notification
        $notificationService->sendTicketAssignedEmail($ticketId, $toUser, $fromUser, $remarks);
    }
    
    private function sendReplyNotifications($ticketId, $ticket, $user, $reply, $status) {
        $notificationService = new NotificationService();
        
        if ($status === 'awaiting_feedback') {
            // Notify customer
            $notificationService->createNotification([
                'customer_id' => $ticket['customer_id'],
                'title' => 'Ticket Reply Received',
                'message' => "Your ticket #{$ticketId} has received a reply",
                'type' => 'success',
                'complaint_id' => $ticketId,
                'action_url' => Config::APP_URL . "/customer/tickets/{$ticketId}"
            ]);
            
            // Send email to customer
            $notificationService->sendTicketReplyEmail($ticketId, $ticket['customer_id'], $user, $reply);
        } else {
            // Notify nodal controller for approval
            $nodalController = $this->db->fetch(
                "SELECT id FROM users WHERE role = 'controller_nodal' AND division = ? AND status = 'active' LIMIT 1",
                [$user['division']]
            );
            
            if ($nodalController) {
                $notificationService->createNotification([
                    'user_id' => $nodalController['id'],
                    'title' => 'Reply Pending Approval',
                    'message' => "Ticket #{$ticketId} reply from {$user['name']} is awaiting approval",
                    'type' => 'warning',
                    'complaint_id' => $ticketId,
                    'action_url' => Config::APP_URL . "/controller/tickets/{$ticketId}"
                ]);
            }
        }
    }
    
    private function sendApprovalNotifications($ticketId, $ticket, $user, $action, $reason = null) {
        $notificationService = new NotificationService();
        
        if ($action === 'approved') {
            // Notify customer
            $notificationService->createNotification([
                'customer_id' => $ticket['customer_id'],
                'title' => 'Ticket Reply Received',
                'message' => "Your ticket #{$ticketId} has received a reply",
                'type' => 'success',
                'complaint_id' => $ticketId,
                'action_url' => Config::APP_URL . "/customer/tickets/{$ticketId}"
            ]);
        } else {
            // Notify assigned controller
            if ($ticket['assigned_to_user_id']) {
                $notificationService->createNotification([
                    'user_id' => $ticket['assigned_to_user_id'],
                    'title' => 'Reply Rejected',
                    'message' => "Your reply for ticket #{$ticketId} was rejected: {$reason}",
                    'type' => 'error',
                    'complaint_id' => $ticketId,
                    'action_url' => Config::APP_URL . "/controller/tickets/{$ticketId}"
                ]);
            }
        }
    }
    
    private function sendRevertNotifications($ticketId, $ticket, $user, $reason) {
        $notificationService = new NotificationService();
        
        // Notify customer
        $notificationService->createNotification([
            'customer_id' => $ticket['customer_id'],
            'title' => 'Additional Information Required',
            'message' => "Ticket #{$ticketId} requires additional information: {$reason}",
            'type' => 'info',
            'complaint_id' => $ticketId,
            'action_url' => Config::APP_URL . "/customer/tickets/{$ticketId}"
        ]);
        
        // Send email to customer
        $notificationService->sendTicketRevertEmail($ticketId, $ticket['customer_id'], $user, $reason);
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
