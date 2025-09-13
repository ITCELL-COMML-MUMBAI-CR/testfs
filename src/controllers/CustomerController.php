<?php
/**
 * Customer Controller for SAMPARK
 * Handles customer dashboard, tickets, and profile management
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../utils/Validator.php';
require_once __DIR__ . '/../utils/FileUploader.php';
require_once __DIR__ . '/../utils/WorkflowEngine.php';

class CustomerController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireRole('customer');
    }
    
    public function dashboard() {
        $customer = $this->getCurrentUser();
        
        $data = [
            'page_title' => 'Customer Dashboard - SAMPARK',
            'customer' => $customer,
            'ticket_stats' => $this->getTicketStats($customer['customer_id']),
            'recent_tickets' => $this->getRecentTickets($customer['customer_id']),
            'announcements' => $this->getCustomerAnnouncements($customer['division']),
            'pending_feedback' => $this->getPendingFeedbackTickets($customer['customer_id']),
            'pending_info' => $this->getPendingInfoTickets($customer['customer_id']),
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('customer/dashboard', $data);
    }
    
    public function tickets() {
        $customer = $this->getCurrentUser();
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? '';
        $priority = $_GET['priority'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        
        // Build query conditions
        $conditions = ['c.customer_id = ?'];
        $params = [$customer['customer_id']];
        
        // Exclude closed tickets by default (as per requirements)
        $conditions[] = "c.status != 'closed'";
        
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
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed,
                       CASE 
                           WHEN c.status = 'awaiting_feedback' THEN TIMESTAMPDIFF(DAY, c.updated_at, NOW())
                           ELSE 0
                       END as days_since_revert
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                WHERE {$whereClause}
                ORDER BY c.created_at DESC";
        
        $tickets = $this->paginate($sql, $params, $page);
        
        $data = [
            'page_title' => 'My Support Tickets - SAMPARK',
            'customer' => $customer,
            'tickets' => $tickets,
            'filters' => [
                'status' => $status,
                'priority' => $priority,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ],
            'status_options' => Config::TICKET_STATUS,
            'priority_options' => Config::PRIORITY_LEVELS,
            'pending_feedback' => $this->getPendingFeedbackTickets($customer['customer_id']),
            'pending_info' => $this->getPendingInfoTickets($customer['customer_id']),
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('customer/tickets', $data);
    }
    
    public function viewTicket($ticketId) {
        $customer = $this->getCurrentUser();
        
        // Get ticket details
        $sql = "SELECT c.*, 
                       cat.category, cat.type, cat.subtype,
                       s.name as shed_name, s.shed_code, s.division, s.zone,
                       w.wagon_code, w.type as wagon_type,
                       cust.name as customer_name, cust.email, cust.mobile, cust.company_name
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                LEFT JOIN wagon_details w ON c.wagon_id = w.wagon_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                WHERE c.complaint_id = ? AND c.customer_id = ?";
        
        $ticket = $this->db->fetch($sql, [$ticketId, $customer['customer_id']]);
        
        if (!$ticket) {
            $this->setFlash('error', 'Ticket not found or access denied');
            $this->redirect(Config::getAppUrl() . '/customer/tickets');
            return;
        }
        
        // Get ticket transactions (visible to customer) - exclude forwarded transactions, internal-only remarks, and awaiting approval remarks
        $transactionSql = "SELECT t.*,
                                  u.name as user_name, u.role as user_role
                           FROM transactions t
                           LEFT JOIN users u ON t.created_by_id = u.id
                           WHERE t.complaint_id = ?
                           AND t.transaction_type NOT IN ('forwarded')
                           AND (t.remarks_type IS NULL OR t.remarks_type NOT IN ('internal_remarks', 'forwarding_remarks'))
                           AND NOT (t.remarks_type = 'customer_remarks' AND t.transaction_type IN ('awaiting_approval', 'replied') AND
                                    NOT EXISTS (SELECT 1 FROM transactions t2 WHERE t2.complaint_id = t.complaint_id
                                               AND t2.transaction_type = 'approved' AND t2.created_at >= t.created_at))
                           ORDER BY t.created_at DESC";
        
        $transactions = $this->db->fetchAll($transactionSql, [$ticketId]);
        
        // Separate priority changes from regular transactions for customer view
        $regularTransactions = [];
        $priorityChanges = [];
        $latestImportantRemark = null;
        
        foreach ($transactions as $transaction) {
            if ($transaction['remarks_type'] === 'priority_escalation') {
                $priorityChanges[] = $transaction;
            } else {
                $regularTransactions[] = $transaction;
                
                // For customers, only show action taken when approved by controller nodal, customer remarks, and interim remarks
                if (!$latestImportantRemark) {
                    $importantTypes = ['customer_remarks', 'interim_remarks'];
                    $remarksText = !empty($transaction['remarks']) ? $transaction['remarks'] : $transaction['internal_remarks'];

                    // Only show customer_remarks if it's from an approved transaction or not an action taken
                    $isActionTaken = ($transaction['remarks_type'] === 'customer_remarks' &&
                                      in_array($transaction['transaction_type'], ['closed', 'action_taken']));

                    if (in_array($transaction['remarks_type'], $importantTypes) && !empty(trim($remarksText))) {
                        // Skip awaiting approval remarks that haven't been approved yet
                        if ($transaction['remarks_type'] === 'customer_remarks' &&
                            in_array($transaction['transaction_type'], ['awaiting_approval', 'replied'])) {
                            // Check if this action has been approved by controller nodal
                            $approvedSql = "SELECT COUNT(*) as approved_count FROM transactions
                                           WHERE complaint_id = ? AND transaction_type = 'approved'
                                           AND created_at >= ?";
                            $approvedCount = $this->db->fetch($approvedSql, [$transaction['complaint_id'], $transaction['created_at']]);

                            if ($approvedCount['approved_count'] > 0) {
                                $latestImportantRemark = $transaction;
                                // For interim remarks, ensure we use the correct remarks field
                                if ($transaction['remarks_type'] === 'interim_remarks') {
                                    $latestImportantRemark['display_remarks'] = !empty($transaction['remarks']) ? $transaction['remarks'] : $transaction['internal_remarks'];
                                }
                            }
                        } else if ($isActionTaken) {
                            // Check if this action has been approved by controller nodal
                            $approvedSql = "SELECT COUNT(*) as approved_count FROM transactions
                                           WHERE complaint_id = ? AND transaction_type = 'approved'
                                           AND created_at >= ?";
                            $approvedCount = $this->db->fetch($approvedSql, [$transaction['complaint_id'], $transaction['created_at']]);

                            if ($approvedCount['approved_count'] > 0) {
                                $latestImportantRemark = $transaction;
                                // For interim remarks, ensure we use the correct remarks field
                                if ($transaction['remarks_type'] === 'interim_remarks') {
                                    $latestImportantRemark['display_remarks'] = !empty($transaction['remarks']) ? $transaction['remarks'] : $transaction['internal_remarks'];
                                }
                            }
                        } else {
                            // Show other customer_remarks and interim_remarks regardless
                            $latestImportantRemark = $transaction;
                            // For interim remarks, ensure we use the correct remarks field
                            if ($transaction['remarks_type'] === 'interim_remarks') {
                                $latestImportantRemark['display_remarks'] = !empty($transaction['remarks']) ? $transaction['remarks'] : $transaction['internal_remarks'];
                            }
                        }
                    }
                }
            }
        }
        
        // If no important remark found, get the latest relevant transaction for customer
        if (!$latestImportantRemark && !empty($regularTransactions)) {
            $reversed = array_reverse($regularTransactions);
            foreach ($reversed as $transaction) {
                // Only show action taken when approved by controller nodal, customer remarks, and interim remarks
                $allowedTypes = ['customer_remarks', 'interim_remarks'];
                $remarksText = !empty($transaction['remarks']) ? $transaction['remarks'] : $transaction['internal_remarks'];

                if (in_array($transaction['remarks_type'], $allowedTypes) && !empty(trim($remarksText))) {
                    // Skip awaiting approval remarks that haven't been approved yet
                    if ($transaction['remarks_type'] === 'customer_remarks' &&
                        in_array($transaction['transaction_type'], ['awaiting_approval', 'replied'])) {
                        // Check if this action has been approved by controller nodal
                        $approvedSql = "SELECT COUNT(*) as approved_count FROM transactions
                                       WHERE complaint_id = ? AND transaction_type = 'approved'
                                       AND created_at >= ?";
                        $approvedCount = $this->db->fetch($approvedSql, [$transaction['complaint_id'], $transaction['created_at']]);

                        if ($approvedCount['approved_count'] > 0) {
                            $latestImportantRemark = $transaction;
                            // For interim remarks, ensure we use the correct remarks field
                            if ($transaction['remarks_type'] === 'interim_remarks') {
                                $latestImportantRemark['display_remarks'] = !empty($transaction['remarks']) ? $transaction['remarks'] : $transaction['internal_remarks'];
                            }
                            break;
                        }
                        continue; // Skip this transaction if not approved
                    }

                    $isActionTaken = ($transaction['remarks_type'] === 'customer_remarks' &&
                                      in_array($transaction['transaction_type'], ['closed', 'action_taken']));

                    if ($isActionTaken) {
                        // Check if this action has been approved by controller nodal
                        $approvedSql = "SELECT COUNT(*) as approved_count FROM transactions
                                       WHERE complaint_id = ? AND transaction_type = 'approved'
                                       AND created_at >= ?";
                        $approvedCount = $this->db->fetch($approvedSql, [$transaction['complaint_id'], $transaction['created_at']]);

                        if ($approvedCount['approved_count'] > 0) {
                            $latestImportantRemark = $transaction;
                            break;
                        }
                    } else {
                        // Show other customer_remarks and interim_remarks regardless
                        $latestImportantRemark = $transaction;
                        // For interim remarks, ensure we use the correct remarks field
                        if ($transaction['remarks_type'] === 'interim_remarks') {
                            $latestImportantRemark['display_remarks'] = !empty($transaction['remarks']) ? $transaction['remarks'] : $transaction['internal_remarks'];
                        }
                        break;
                    }
                }
            }
        }
        
        // Get evidence files
        $evidenceSql = "SELECT * FROM evidence WHERE complaint_id = ? ORDER BY uploaded_at ASC";
        $evidenceRaw = $this->db->fetchAll($evidenceSql, [$ticketId]);
        
        // Transform evidence data for display
        $evidence = $this->transformEvidenceForDisplay($evidenceRaw);
        
        // Check if feedback is required
        $requiresFeedback = ($ticket['status'] === 'awaiting_feedback');
        
        $data = [
            'page_title' => 'Ticket #' . $ticketId . ' - SAMPARK',
            'customer' => $customer,
            'ticket' => $ticket,
            'transactions' => $regularTransactions,
            'priority_changes' => $priorityChanges,
            'latest_important_remark' => $latestImportantRemark,
            'evidence' => $evidence,
            'requires_feedback' => $requiresFeedback,
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('customer/ticket-details', $data);
    }
    
    public function createTicket() {
        $customer = $this->getCurrentUser();
        
        // Get categories with proper structure for the form
        $categories = $this->getTicketCategories();
        
        // Get shed data
        $sheds = $this->getShedData();
        
        // Get divisions for filtering
        $divisions = $this->getDivisions();
        
        // Get wagon types with details
        $wagonTypes = $this->getWagonTypes();
        
        $data = [
            'page_title' => 'Create Support Ticket - SAMPARK',
            'customer' => $customer,
            'categories' => $categories,
            'wagon_types' => $wagonTypes,
            'sheds' => $sheds,
            'divisions' => $divisions,
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('customer/create-ticket', $data);
    }
    
    public function storeTicket() {
        $this->validateCSRF();
        $customer = $this->getCurrentUser();
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'category_id' => 'required|exists:complaint_categories,category_id',
            'shed_id' => 'required|exists:shed,shed_id',
            'wagon_type' => 'nullable',
            'description' => 'required|min:20|max:2000'
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
            
            // Generate complaint ID
            $complaintId = Config::generateComplaintNumber();
            
            // Get shed information for division/zone
            $shedInfo = $this->db->fetch("SELECT division, zone FROM shed WHERE shed_id = ?", [$_POST['shed_id']]);
            
            // Find wagon ID based on wagon type (if provided)
            $wagonId = null;
            if (!empty($_POST['wagon_type'])) {
                $wagonId = $this->getWagonIdByType($_POST['wagon_type']);
            }
            
            // Use current date and time
            $currentDate = date('Y-m-d');
            $currentTime = date('H:i:s');
            
            // Find controller_nodal in Commercial department for this division (per requirements)
            $controllerNodal = $this->findControllerNodalForDivision($shedInfo['division'], 'CML');
            
            // Insert complaint - MUST route to controller_nodal as per requirements
            $sql = "INSERT INTO complaints (
                complaint_id, category_id, date, time, shed_id, wagon_id,
                description, customer_id, fnr_number, e_indent_number,
                division, zone, status, priority, assigned_to_department, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'normal', 'CML', NOW())";
            
            $params = [
                $complaintId,
                $_POST['category_id'],
                $currentDate,
                $currentTime,
                $_POST['shed_id'],
                $wagonId,
                trim($_POST['description']),
                $customer['customer_id'],
                $_POST['fnr_number'] ?? null,
                $_POST['e_indent_number'] ?? null,
                $shedInfo['division'],
                $shedInfo['zone'],
            ];
            
            $this->db->query($sql, $params);
            
            // Create initial transaction
            $this->createTransaction($complaintId, 'created', 'Ticket created by customer', $customer['customer_id'], 'customer');
            
            // Handle file uploads
            if (!empty($_FILES['evidence'])) {
                $uploadResult = $this->handleEvidenceUpload($complaintId, $_FILES['evidence']);
                if (!$uploadResult['success']) {
                    $this->db->rollback();
                    $this->json([
                        'success' => false,
                        'message' => 'File upload failed: ' . implode(', ', $uploadResult['errors'])
                    ], 400);
                    return;
                }
            }
            
            // Send notifications to department
            $this->sendTicketCreatedNotifications($complaintId, $customer, $shedInfo);
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Support ticket created successfully',
                'ticket_id' => $complaintId,
                'redirect' => Config::getAppUrl() . '/customer/tickets/' . $complaintId
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Ticket creation error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to create ticket. Please try again.'
            ], 500);
        }
    }
    
    public function submitFeedback($ticketId) {
        $this->validateCSRF();
        $customer = $this->getCurrentUser();
        
        // Verify ticket belongs to customer and is awaiting feedback
        $ticket = $this->db->fetch(
            "SELECT * FROM complaints WHERE complaint_id = ? AND customer_id = ? AND status = 'awaiting_feedback'",
            [$ticketId, $customer['customer_id']]
        );
        
        if (!$ticket) {
            $this->json(['success' => false, 'message' => 'Invalid ticket or feedback not required'], 403);
            return;
        }
        
        $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'rating' => 'required|in:excellent,satisfactory,unsatisfactory',
            'remarks' => ($_POST['rating'] === 'unsatisfactory' ? 'required|' : '') . 'max:500'
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
            
            // Update ticket with feedback
            $sql = "UPDATE complaints SET 
                    rating = ?, 
                    rating_remarks = ?, 
                    status = 'closed',
                    closed_at = NOW(),
                    updated_at = NOW()
                    WHERE complaint_id = ?";
            
            $this->db->query($sql, [
                $_POST['rating'],
                $_POST['remarks'] ?? null,
                $ticketId
            ]);
            
            // Create feedback transaction
            $this->createTransaction(
                $ticketId, 
                'feedback_submitted', 
                "Rating: " . ucfirst($_POST['rating']) . 
                ($_POST['remarks'] ? "\nRemarks: " . $_POST['remarks'] : ''),
                $customer['customer_id'], 
                'customer'
            );
            
            $this->db->commit();
            
            $this->json([
                'success' => true,
                'message' => 'Thank you for your feedback. The ticket has been closed.'
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Feedback submission error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'Failed to submit feedback. Please try again.'
            ], 500);
        }
    }
    
    public function profile() {
        $customer = $this->getCurrentUser();
        
        $customerDetails = $this->db->fetch(
            "SELECT * FROM customers WHERE customer_id = ?",
            [$customer['customer_id']]
        );
        
        $data = [
            'page_title' => 'My Profile - SAMPARK',
            'customer' => $customer,
            'customer_details' => $customerDetails,
            'divisions' => $this->getDivisions(),
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('customer/profile', $data);
    }
    
    public function updateProfile() {
        try {
            $this->validateCSRF();
            $customer = $this->getCurrentUser();
            
            $validator = new Validator();
        $isValid = $validator->validate($_POST, [
            'name' => 'required|min:2|max:100',
            'mobile' => 'required|phone|unique:customers,mobile,' . $customer['customer_id'] . ',customer_id',
            'company_name' => 'required|min:2|max:150',
            'designation' => 'max:100'
        ]);
        
        if (!$isValid) {
            $this->json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 400);
            return;
        }
        
        try {
            $sql = "UPDATE customers SET 
                    name = ?, 
                    mobile = ?, 
                    company_name = ?, 
                    designation = ?,
                    updated_at = NOW()
                    WHERE customer_id = ?";
            
            $this->db->query($sql, [
                trim($_POST['name']),
                trim($_POST['mobile']),
                trim($_POST['company_name']),
                trim($_POST['designation']) ?: null,
                $customer['customer_id']
            ]);
            
            // Update session data
            $this->session->set('user_name', $_POST['name']);
            
            $this->logActivity('profile_updated', ['customer_id' => $customer['customer_id']]);
            
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
        } catch (Exception $e) {
            error_log("Critical profile update error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            
            $this->json([
                'success' => false,
                'message' => 'System error occurred while updating profile.'
            ], 500);
        }
    }
    
    // Helper methods
    
    private function getTicketStats($customerId) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'awaiting_feedback' THEN 1 ELSE 0 END) as awaiting_feedback,
                    SUM(CASE WHEN status = 'awaiting_info' THEN 1 ELSE 0 END) as awaiting_info,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
                    SUM(CASE WHEN priority = 'high' OR priority = 'critical' THEN 1 ELSE 0 END) as high_priority_count
                FROM complaints 
                WHERE customer_id = ?";
        
        return $this->db->fetch($sql, [$customerId]);
    }
    
    private function getRecentTickets($customerId, $limit = 5) {
        $sql = "SELECT c.*, cat.category, cat.type, s.name as shed_name
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                WHERE c.customer_id = ? AND c.status != 'closed'
                ORDER BY c.created_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$customerId, $limit]);
    }
    
    private function getCustomerAnnouncements($division) {
        $sql = "SELECT title, content, publish_date, priority
                FROM news 
                WHERE is_active = 1 
                  AND type IN ('announcement', 'alert')
                  AND (division_specific IS NULL OR division_specific = ?)
                  AND publish_date <= NOW()
                  AND (expire_date IS NULL OR expire_date > NOW())
                ORDER BY priority DESC, publish_date DESC 
                LIMIT 3";
        
        return $this->db->fetchAll($sql, [$division]);
    }
    
    private function getPendingFeedbackTickets($customerId) {
        $sql = "SELECT complaint_id, category_id, created_at, 
                       TIMESTAMPDIFF(DAY, updated_at, NOW()) as days_pending
                FROM complaints 
                WHERE customer_id = ? AND status = 'awaiting_feedback'
                ORDER BY updated_at ASC";
        
        return $this->db->fetchAll($sql, [$customerId]);
    }
    
    private function getPendingInfoTickets($customerId) {
        $sql = "SELECT complaint_id, category_id, created_at, 
                       TIMESTAMPDIFF(DAY, updated_at, NOW()) as days_pending
                FROM complaints 
                WHERE customer_id = ? AND status = 'awaiting_info'
                ORDER BY updated_at ASC";
        
        return $this->db->fetchAll($sql, [$customerId]);
    }
    
    private function getTicketCategories() {
        // Get all categories properly structured for the cascading dropdown
        $sql = "SELECT category_id, category, type, subtype 
                FROM complaint_categories 
                ORDER BY category, type, subtype";
        
        $categories = $this->db->fetchAll($sql);
        
        // Transform the data for easier use in the frontend
        $result = [];
        foreach ($categories as $cat) {
            if (!isset($result[$cat['category']])) {
                $result[$cat['category']] = [];
            }
            
            if (!isset($result[$cat['category']][$cat['type']])) {
                $result[$cat['category']][$cat['type']] = [];
            }
            
            $result[$cat['category']][$cat['type']][] = [
                'subtype' => $cat['subtype'],
                'category_id' => $cat['category_id']
            ];
        }
        
        return $result;
    }
    
    private function getShedData() {
        // Get all sheds for the dropdown
        $sql = "SELECT shed_id, shed_code, name, division, zone
                FROM shed 
                WHERE is_active = 1 
                ORDER BY division, name";
        
        return $this->db->fetchAll($sql);
    }
    
    private function getDivisions() {
        $sql = "SELECT DISTINCT division FROM shed WHERE is_active = 1 ORDER BY division";
        return $this->db->fetchAll($sql);
    }
    
    private function getWagonTypes() {
        // Get unique wagon types from the database
        $sql = "SELECT DISTINCT wagon_code, type, description 
                FROM wagon_details 
                WHERE is_active = 1 
                ORDER BY wagon_code";
        
        $wagonData = $this->db->fetchAll($sql);
        
        // Format as key-value pairs for the dropdown
        $wagonTypes = [];
        foreach ($wagonData as $wagon) {
            $label = $wagon['wagon_code'];
            if (!empty($wagon['description'])) {
                $label .= ' - ' . $wagon['description'];
            }
            $wagonTypes[$wagon['wagon_code']] = $label;
        }
        
        return $wagonTypes;
    }
    
    private function getWagonIdByType($wagonType) {
        // Find wagon by type (wagon_code)
        $wagon = $this->db->fetch(
            "SELECT wagon_id FROM wagon_details WHERE wagon_code = ? AND is_active = 1 LIMIT 1",
            [$wagonType]
        );
        
        return $wagon ? $wagon['wagon_id'] : null;
    }
    
    private function getOrCreateWagon($wagonType, $wagonCode = null) {
        if ($wagonCode) {
            // Try to find existing wagon
            $existing = $this->db->fetch(
                "SELECT wagon_id FROM wagon_details WHERE wagon_code = ?",
                [$wagonCode]
            );
            
            if ($existing) {
                return $existing['wagon_id'];
            }
            
            // Create new wagon
            $this->db->query(
                "INSERT INTO wagon_details (wagon_code, type) VALUES (?, ?)",
                [$wagonCode, $wagonType]
            );
            
            return $this->db->lastInsertId();
        }
        
        // Get default wagon for type
        $default = $this->db->fetch(
            "SELECT wagon_id FROM wagon_details WHERE type = ? LIMIT 1",
            [$wagonType]
        );
        
        return $default ? $default['wagon_id'] : null;
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
                        'id' => $record['id'] . '_' . $i, // Create a unique ID for each file slot
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
    
    private function createTransaction($complaintId, $type, $remarks, $createdById, $createdByType) {
        $sql = "INSERT INTO transactions (
            complaint_id, transaction_type, remarks, 
            created_by_id, created_by_customer_id, created_by_type, 
            created_by_role, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $complaintId,
            $type,
            $remarks,
            $createdByType === 'user' ? $createdById : null,
            $createdByType === 'customer' ? $createdById : null,
            $createdByType,
            $createdByType === 'customer' ? 'customer' : $this->session->getUserRole()
        ];
        
        $this->db->query($sql, $params);
    }
    
    private function handleEvidenceUpload($complaintId, $files) {
        $uploader = new FileUploader();
        return $uploader->uploadEvidence($complaintId, $files, 'customer', $this->getCurrentUser()['customer_id']);
    }

    private function handleFileRemoval($complaintId, $removedFileIds) {
        if (empty($removedFileIds)) {
            return;
        }

        // Get existing evidence record
        $evidence = $this->db->fetch(
            "SELECT * FROM evidence WHERE complaint_id = ?",
            [$complaintId]
        );

        if (!$evidence) {
            return;
        }

        // Prepare update data to clear removed file slots
        $updateData = [];
        $params = [];

        foreach ($removedFileIds as $fileId) {
            // fileId format is evidenceId_slot (e.g., "5_1", "5_2", "5_3")
            if (strpos($fileId, '_') !== false) {
                list($evidenceId, $slot) = explode('_', $fileId);

                if ($evidenceId == $evidence['id'] && in_array($slot, [1, 2, 3])) {
                    // Clear the file slot
                    $updateData[] = "file_name_$slot = NULL";
                    $updateData[] = "file_type_$slot = NULL";
                    $updateData[] = "file_path_$slot = NULL";
                    $updateData[] = "compressed_size_$slot = NULL";

                    // Also delete the physical file if it exists
                    $fileNameField = "file_name_$slot";
                    if (!empty($evidence[$fileNameField])) {
                        $filePath = Config::getUploadPath() . $evidence[$fileNameField];
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }
                }
            }
        }

        // Execute update if there are changes
        if (!empty($updateData)) {
            $params[] = $complaintId;
            $sql = "UPDATE evidence SET " . implode(', ', $updateData) . " WHERE complaint_id = ?";
            $this->db->query($sql, $params);
        }
    }

    private function sendTicketCreatedNotifications($complaintId, $customer, $shedInfo) {
        // Send notifications to all controller_nodals in Commercial dept of the division
        // Implementation for sending email/SMS notifications
        // This would use the notification service to notify all relevant users
    }
    
    /**
     * Find controller_nodal for initial ticket assignment (per requirements)
     */
    private function findControllerNodalForDivision($division, $department = 'CML') {
        // Per requirements: All tickets must initially flow through controller_nodal (Commercial Department)
        $sql = "SELECT id FROM users 
                WHERE role = 'controller_nodal' 
                  AND division = ? 
                  AND department = ?
                  AND status = 'active'
                ORDER BY id ASC
                LIMIT 1";
        
        $user = $this->db->fetch($sql, [$division, $department]);
        
        if (!$user) {
            // Fallback: Find any controller_nodal in the division if no Commercial one exists
            $fallback = $this->db->fetch(
                "SELECT id FROM users WHERE role = 'controller_nodal' AND division = ? AND status = 'active' LIMIT 1",
                [$division]
            );
            
            if ($fallback) {
                error_log("Warning: No Commercial controller_nodal found for division {$division}, using fallback user {$fallback['id']}");
                return $fallback['id'];
            }
            
            // Critical error: No controller_nodal found
            error_log("Critical: No controller_nodal found for division {$division}");
            throw new Exception("No controller_nodal available for ticket assignment in division {$division}");
        }
        
        return $user['id'];
    }
    
    public function changePassword() {
        $this->validateCSRF();
        $customer = $this->getCurrentUser();
        
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
        $customerData = $this->db->fetch(
            "SELECT password FROM customers WHERE customer_id = ?",
            [$customer['customer_id']]
        );
        
        if (!$customerData) {
            $this->json([
                'success' => false,
                'message' => 'Customer account not found'
            ], 404);
            return;
        }
        
        // Verify current password
        if (!password_verify($_POST['current_password'], $customerData['password'])) {
            $this->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
            return;
        }
        
        try {
            // Update password
            $this->db->query(
                "UPDATE customers SET password = ?, updated_at = NOW() WHERE customer_id = ?",
                [password_hash($_POST['new_password'], PASSWORD_DEFAULT), $customer['customer_id']]
            );
            
            // Log the password change
            $this->logActivity('password_changed', ['customer_id' => $customer['customer_id']]);
            
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
    
    /**
     * Display customer help page
     */
    public function help() {
        $customer = $this->getCurrentUser();
        
        $data = [
            'page_title' => 'Help & Support - SAMPARK',
            'customer' => $customer,
            'faqs' => $this->getCustomerFAQs(),
            'help_categories' => $this->getHelpCategories(),
            'contact_info' => $this->getHelpContactInfo(),
            'video_tutorials' => $this->getVideoTutorials(),
            'quick_actions' => $this->getQuickHelpActions(),
            'system_status' => $this->getSystemStatus(),
            'csrf_token' => $this->session->getCSRFToken()
        ];
        
        $this->view('customer/help', $data);
    }
    
    // Helper methods for help functionality
    
    private function getCustomerFAQs() {
        return [
            'Getting Started' => [
                [
                    'question' => 'How do I create my first support ticket?',
                    'answer' => 'Navigate to "Create New Ticket" from your dashboard, select the appropriate category and subcategory, fill in all required details including location and wagon information, describe your issue clearly, and upload any supporting documents.'
                ],
                [
                    'question' => 'What information should I include in my ticket?',
                    'answer' => 'Include detailed information about the issue location (shed/terminal), affected wagon numbers, specific problem description, time of occurrence, and any relevant documentation or photos as supporting documents.'
                ],
                [
                    'question' => 'How long does it take to get a response?',
                    'answer' => 'Response times vary by priority level: Critical (2 hours), High (4 hours), Medium (8 hours), Normal (24 hours). You will receive email notifications when your ticket is updated.'
                ]
            ],
            'Ticket Management' => [
                [
                    'question' => 'How can I track my ticket progress?',
                    'answer' => 'Go to "My Tickets" to see all your tickets with current status, priority level, and any responses from railway staff. Click on any ticket for detailed view and communication history.'
                ],
                [
                    'question' => 'Can I add more information to my ticket after submission?',
                    'answer' => 'Yes, you can respond to your ticket with additional information, clarifications, or new supporting documents. Railway staff may also request additional information.'
                ],
                [
                    'question' => 'What do the different ticket statuses mean?',
                    'answer' => 'Pending: Under review, In Progress: Being worked on, Resolved: Solution provided awaiting your confirmation, Closed: Issue completed, Escalated: Moved to higher authority for resolution.'
                ]
            ],
            'Account & Profile' => [
                [
                    'question' => 'How do I update my company information?',
                    'answer' => 'Go to your Profile section and click "Edit Profile". You can update company details, contact information, and operational preferences. Changes may require admin approval.'
                ],
                [
                    'question' => 'How do I change my password?',
                    'answer' => 'In your Profile section, use the "Change Password" option. You\'ll need to enter your current password and set a new secure password meeting system requirements.'
                ],
                [
                    'question' => 'Why can\'t I access certain features?',
                    'answer' => 'Some features may require account verification or specific permissions. Contact admin support if you believe you should have access to restricted features.'
                ]
            ],
            'Technical Issues' => [
                [
                    'question' => 'What file types can I upload as supporting documents?',
                    'answer' => 'Supported formats: Images (JPG, PNG, GIF), Documents (PDF, DOC, DOCX), Spreadsheets (XLS, XLSX). Maximum file size is 10MB per file, maximum 5 files per ticket.'
                ],
                [
                    'question' => 'The website is running slowly. What should I do?',
                    'answer' => 'Try refreshing the page, clearing your browser cache, or using a different browser. If issues persist, contact technical support with details about your browser and connection.'
                ],
                [
                    'question' => 'I\'m not receiving email notifications. How can I fix this?',
                    'answer' => 'Check your email spam/junk folder, verify your email address in your profile is correct, and ensure emails from @railway.gov.in are not blocked by your email provider.'
                ]
            ]
        ];
    }
    
    private function getHelpCategories() {
        return [
            [
                'name' => 'Ticket Management',
                'icon' => 'fas fa-ticket-alt',
                'description' => 'Learn how to create, track, and manage your support tickets',
                'articles_count' => 8
            ],
            [
                'name' => 'Account Settings',
                'icon' => 'fas fa-user-cog',
                'description' => 'Manage your profile, company information, and account preferences',
                'articles_count' => 5
            ],
            [
                'name' => 'Railway Operations',
                'icon' => 'fas fa-train',
                'description' => 'Understanding railway processes, wagon types, and operational procedures',
                'articles_count' => 12
            ],
            [
                'name' => 'Technical Support',
                'icon' => 'fas fa-tools',
                'description' => 'Technical help, system requirements, and troubleshooting guides',
                'articles_count' => 6
            ]
        ];
    }
    
    private function getHelpContactInfo() {
        return [
            'general_support' => [
                'title' => 'General Support',
                'phone' => '1800-111-321',
                'email' => 'support@railway.gov.in',
                'hours' => '24/7 Support Available'
            ],
            'technical_support' => [
                'title' => 'Technical Support',
                'phone' => '1800-111-322',
                'email' => 'tech.support@railway.gov.in',
                'hours' => 'Mon-Fri: 9:00 AM - 6:00 PM IST'
            ],
            'freight_operations' => [
                'title' => 'Freight Operations',
                'phone' => '1800-111-323',
                'email' => 'freight.support@railway.gov.in',
                'hours' => '24/7 Operations Center'
            ]
        ];
    }
    
    private function getVideoTutorials() {
        return [
            [
                'title' => 'Creating Your First Ticket',
                'duration' => '3:45',
                'thumbnail' => '/assets/images/tutorials/create-ticket-thumb.jpg',
                'url' => '/help/videos/create-ticket',
                'description' => 'Step-by-step guide to creating effective support tickets'
            ],
            [
                'title' => 'Tracking Ticket Progress',
                'duration' => '2:30',
                'thumbnail' => '/assets/images/tutorials/track-ticket-thumb.jpg',
                'url' => '/help/videos/track-ticket',
                'description' => 'Learn how to monitor your ticket status and responses'
            ],
            [
                'title' => 'Uploading Supporting Documents',
                'duration' => '2:15',
                'thumbnail' => '/assets/images/tutorials/upload-evidence-thumb.jpg',
                'url' => '/help/videos/upload-evidence',
                'description' => 'Best practices for uploading supporting documents and images'
            ],
            [
                'title' => 'Profile Management',
                'duration' => '4:20',
                'thumbnail' => '/assets/images/tutorials/profile-management-thumb.jpg',
                'url' => '/help/videos/profile-management',
                'description' => 'Managing your account and company information'
            ]
        ];
    }
    
    private function getQuickHelpActions() {
        return [
            [
                'title' => 'Create New Ticket',
                'icon' => 'fas fa-plus-circle',
                'url' => '/customer/tickets/create',
                'description' => 'Report a new issue or concern'
            ],
            [
                'title' => 'View My Tickets',
                'icon' => 'fas fa-list',
                'url' => '/customer/tickets',
                'description' => 'Check status of existing tickets'
            ],
            [
                'title' => 'Contact Support',
                'icon' => 'fas fa-phone',
                'url' => 'tel:1800111321',
                'description' => 'Call our 24/7 support helpline'
            ],
            [
                'title' => 'Download User Guide',
                'icon' => 'fas fa-file-pdf',
                'url' => '/assets/documents/SAMPARK-User-Guide.pdf',
                'description' => 'Complete user manual (PDF)'
            ],
            [
                'title' => 'System Status',
                'icon' => 'fas fa-server',
                'url' => '/help/system-status',
                'description' => 'Check current system availability'
            ],
            [
                'title' => 'Training Videos',
                'icon' => 'fas fa-play-circle',
                'url' => '/help/videos',
                'description' => 'Watch tutorial videos'
            ]
        ];
    }
    
    private function getSystemStatus() {
        try {
            // Check basic system health
            $dbStatus = $this->checkDatabaseConnection();
            $fileUploadStatus = $this->checkFileUploadService();
            
            return [
                'overall_status' => ($dbStatus && $fileUploadStatus) ? 'operational' : 'degraded',
                'database' => $dbStatus ? 'operational' : 'degraded',
                'file_upload' => $fileUploadStatus ? 'operational' : 'degraded',
                'last_updated' => date('Y-m-d H:i:s'),
                'maintenance_window' => 'Daily 02:00-03:00 IST'
            ];
        } catch (Exception $e) {
            return [
                'overall_status' => 'unknown',
                'database' => 'unknown',
                'file_upload' => 'unknown',
                'last_updated' => date('Y-m-d H:i:s'),
                'maintenance_window' => 'Daily 02:00-03:00 IST'
            ];
        }
    }
    
    private function checkDatabaseConnection() {
        try {
            $result = $this->db->fetch("SELECT 1 as test");
            return $result && $result['test'] == 1;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function checkFileUploadService() {
        try {
            // Check if uploads directory is writable
            $uploadsDir = Config::getUploadPath();
            return is_dir($uploadsDir) && is_writable($uploadsDir);
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function provideAdditionalInfo($ticketId) {
        $this->validateCSRF();
        $customer = $this->getCurrentUser();
        
        // Verify ticket belongs to customer and is awaiting info or pending
        $ticket = $this->db->fetch(
            "SELECT * FROM complaints WHERE complaint_id = ? AND customer_id = ? AND status IN ('awaiting_info', 'pending')",
            [$ticketId, $customer['customer_id']]
        );
        
        if (!$ticket) {
            return $this->json([
                'success' => false,
                'message' => 'Ticket not found or not available for additional information.'
            ], 404);
        }
        
        $additionalInfo = trim($_POST['additional_info'] ?? '');
        
        if (empty($additionalInfo)) {
            return $this->json([
                'success' => false,
                'message' => 'Additional information is required.'
            ], 400);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Append additional info to existing description with separator
            $currentDescription = $ticket['description'];
            $updatedDescription = $currentDescription . "\n\n--- Additional Info ---\n" . $additionalInfo;
            
            // Handle file removal first (if specified)
            if (!empty($_POST['removed_files'])) {
                $removedFiles = json_decode($_POST['removed_files'], true) ?: [];
                $this->handleFileRemoval($ticketId, $removedFiles);
            }

            // Handle supporting files if any - use same logic as ticket creation
            $uploadResult = null;
            if (!empty($_FILES['supporting_files'])) {
                $uploadResult = $this->handleEvidenceUpload($ticketId, $_FILES['supporting_files']);

                if (!$uploadResult['success']) {
                    $this->db->rollback();
                    return $this->json([
                        'success' => false,
                        'message' => 'File upload failed: ' . implode(', ', $uploadResult['errors'])
                    ], 400);
                }
            }
            
            // Update ticket description and status
            $this->db->query(
                "UPDATE complaints SET description = ?, status = 'pending', updated_at = NOW() WHERE complaint_id = ?",
                [$updatedDescription, $ticketId]
            );
            
            // Add transaction log
            $remarkText = $additionalInfo;
            if ($uploadResult && !empty($uploadResult['files'])) {
                $fileNames = array_column($uploadResult['files'], 'original_name');
                $remarkText .= " (with " . count($fileNames) . " supporting document(s): " . implode(', ', $fileNames) . ")";
            }
            
            $this->db->query(
                "INSERT INTO transactions (complaint_id, transaction_type, remarks, created_by_type, created_by_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [$ticketId, 'info_provided', $remarkText, 'customer', $customer['customer_id']]
            );
            
            // Process workflow
            $workflowEngine = new WorkflowEngine();
            $workflowResult = $workflowEngine->processTicketWorkflow(
                $ticketId,
                'provide_info',
                $customer['id'],
                'customer',
                ['additional_info' => $additionalInfo],
                true // Skip transaction since we already started one
            );
            
            if (!$workflowResult['success']) {
                throw new Exception($workflowResult['error']);
            }
            
            $this->db->commit();
            
            return $this->json([
                'success' => true,
                'message' => 'Additional information provided successfully. Your ticket is now back under review.'
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error providing additional info: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            return $this->json([
                'success' => false,
                'message' => 'Failed to submit additional information: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function uploadEvidence($ticketId) {
        $this->validateCSRF();
        $customer = $this->getCurrentUser();
        
        // Verify ticket belongs to customer
        $ticket = $this->db->fetch(
            "SELECT * FROM complaints WHERE complaint_id = ? AND customer_id = ?",
            [$ticketId, $customer['id']]
        );
        
        if (!$ticket) {
            return $this->json([
                'success' => false,
                'message' => 'Ticket not found.'
            ], 404);
        }
        
        // Check current evidence count
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM evidence WHERE complaint_id = ?",
            [$ticketId]
        );
        $currentEvidenceCount = $result['count'];
        
        if ($currentEvidenceCount >= 3) {
            return $this->json([
                'success' => false,
                'message' => 'Maximum 3 supporting documents allowed per ticket.'
            ], 400);
        }
        
        if (!isset($_FILES['evidence']) || empty($_FILES['evidence']['tmp_name'])) {
            return $this->json([
                'success' => false,
                'message' => 'No file uploaded.'
            ], 400);
        }
        
        try {
            // Process the uploaded file with compression
            $fileUploader = new FileUploader();
            $uploadResult = $fileUploader->uploadSingleEvidence($_FILES['evidence'], $ticketId);
            
            if (!$uploadResult['success']) {
                return $this->json([
                    'success' => false,
                    'message' => $uploadResult['message']
                ], 400);
            }
            
            // Insert evidence record
            $evidenceId = $this->db->query(
                "INSERT INTO evidence (complaint_id, original_name, file_name, file_size, file_type, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [
                    $ticketId,
                    $uploadResult['original_name'],
                    $uploadResult['file_name'],
                    $uploadResult['file_size'],
                    $uploadResult['file_type']
                ]
            );
            
            // Add transaction log
            $this->db->query(
                "INSERT INTO transactions (complaint_id, transaction_type, remarks, created_by_type, created_by_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [$ticketId, 'evidence_uploaded', 'New evidence file uploaded: ' . $uploadResult['original_name'], 'customer', $customer['id']]
            );
            
            return $this->json([
                'success' => true,
                'message' => 'Supporting document uploaded successfully.',
                'evidence_id' => $evidenceId,
                'file_info' => [
                    'original_name' => $uploadResult['original_name'],
                    'file_size' => $uploadResult['file_size'],
                    'file_type' => $uploadResult['file_type']
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("Error uploading evidence: " . $e->getMessage());
            
            return $this->json([
                'success' => false,
                'message' => 'Failed to upload supporting document. Please try again.'
            ], 500);
        }
    }
    
    public function deleteEvidence($ticketId, $evidenceId) {
        $this->validateCSRF();
        $customer = $this->getCurrentUser();
        
        // Verify evidence belongs to customer's ticket
        $evidence = $this->db->fetch(
            "SELECT e.*, c.customer_id FROM evidence e 
             JOIN complaints c ON e.complaint_id = c.complaint_id 
             WHERE e.id = ? AND e.complaint_id = ? AND c.customer_id = ?",
            [$evidenceId, $ticketId, $customer['id']]
        );
        
        if (!$evidence) {
            return $this->json([
                'success' => false,
                'message' => 'Supporting document not found.'
            ], 404);
        }
        
        try {
            $this->db->beginTransaction();
            
            // Delete from database
            $this->db->query("DELETE FROM evidence WHERE id = ?", [$evidenceId]);
            
            // Delete physical file
            $filePath = Config::getUploadPath() . $evidence['file_name'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Add transaction log
            $this->db->query(
                "INSERT INTO transactions (complaint_id, transaction_type, remarks, created_by_type, created_by_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [$ticketId, 'evidence_deleted', 'Evidence file deleted: ' . $evidence['original_name'], 'customer', $customer['id']]
            );
            
            $this->db->commit();
            
            return $this->json([
                'success' => true,
                'message' => 'Supporting document deleted successfully.'
            ]);
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error deleting evidence: " . $e->getMessage());
            
            return $this->json([
                'success' => false,
                'message' => 'Failed to delete supporting document. Please try again.'
            ], 500);
        }
    }
}
