<?php
/**
 * API Controller for SAMPARK
 * Handles REST API endpoints for AJAX requests
 */

require_once 'BaseController.php';

class ApiController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        
        // Set JSON header for all API responses
        header('Content-Type: application/json');
        
        // Handle CORS if needed
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            }
            
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }
            
            exit(0);
        }
    }
    
    /**
     * Search sheds/terminals
     */
    public function searchSheds() {
        try {
            $query = $_GET['q'] ?? '';
            $division = $_GET['division'] ?? '';
            
            if (strlen($query) < 2) {
                $this->json([]);
                return;
            }
            
            $conditions = [
                "is_active = 1",
                "(shed_code LIKE ? OR name LIKE ? OR terminal LIKE ?)"
            ];
            
            $params = ["%{$query}%", "%{$query}%", "%{$query}%"];
            
            if ($division) {
                $conditions[] = "division = ?";
                $params[] = $division;
            }
            
            $whereClause = implode(' AND ', $conditions);
            
            $sql = "SELECT shed_id, shed_code, name, terminal, division, zone 
                    FROM shed 
                    WHERE {$whereClause}
                    ORDER BY 
                        CASE WHEN shed_code LIKE ? THEN 1 ELSE 2 END,
                        name ASC 
                    LIMIT 20";
            
            $params[] = "{$query}%"; // For ORDER BY prioritization
            
            $sheds = $this->db->fetchAll($sql, $params);
            
            $this->json($sheds);
            
        } catch (Exception $e) {
            error_log("Shed search error: " . $e->getMessage());
            $this->json(['error' => 'Search failed'], 500);
        }
    }
    
    /**
     * Get subtypes for a category type
     */
    public function getSubtypes($categoryType) {
        try {
            $sql = "SELECT category_id, category, type, subtype 
                    FROM complaint_categories 
                    WHERE type = ? AND is_active = 1 
                    ORDER BY subtype ASC";
            
            $subtypes = $this->db->fetchAll($sql, [$categoryType]);
            
            $this->json($subtypes);
            
        } catch (Exception $e) {
            error_log("Subtypes fetch error: " . $e->getMessage());
            $this->json(['error' => 'Failed to fetch subtypes'], 500);
        }
    }
    
    /**
     * Upload evidence files
     */
    public function uploadEvidence($ticketId) {
        $this->requireAuth();
        
        try {
            $user = $this->getCurrentUser();
            
            // Verify ticket access
            if ($user['role'] === 'customer') {
                $ticket = $this->db->fetch(
                    "SELECT complaint_id FROM complaints WHERE complaint_id = ? AND customer_id = ?",
                    [$ticketId, $user['customer_id']]
                );
            } else {
                $ticket = $this->db->fetch(
                    "SELECT complaint_id FROM complaints WHERE complaint_id = ?",
                    [$ticketId]
                );
            }
            
            if (!$ticket) {
                $this->json(['error' => 'Ticket not found or access denied'], 403);
                return;
            }
            
            if (empty($_FILES['files'])) {
                $this->json(['error' => 'No files uploaded'], 400);
                return;
            }
            
            $uploader = new FileUploader();
            $userType = $user['role'] === 'customer' ? 'customer' : 'user';
            $userId = $user['role'] === 'customer' ? $user['customer_id'] : $user['id'];
            
            $result = $uploader->uploadEvidence($ticketId, $_FILES['files'], $userType, $userId);
            
            if ($result['success']) {
                $this->json([
                    'success' => true,
                    'message' => 'Files uploaded successfully',
                    'files' => $result['files']
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Upload failed',
                    'errors' => $result['errors']
                ], 400);
            }
            
        } catch (Exception $e) {
            error_log("File upload error: " . $e->getMessage());
            $this->json(['error' => 'Upload failed'], 500);
        }
    }
    
    /**
     * Get evidence file
     */
    public function getEvidence($ticketId, $filename) {
        $this->requireAuth();
        
        try {
            $user = $this->getCurrentUser();
            
            // Verify ticket access
            if ($user['role'] === 'customer') {
                $ticket = $this->db->fetch(
                    "SELECT complaint_id FROM complaints WHERE complaint_id = ? AND customer_id = ?",
                    [$ticketId, $user['customer_id']]
                );
            } else {
                $ticket = $this->db->fetch(
                    "SELECT complaint_id FROM complaints WHERE complaint_id = ?",
                    [$ticketId]
                );
            }
            
            if (!$ticket) {
                http_response_code(403);
                exit('Access denied');
            }
            
            // Verify file belongs to ticket
            $evidence = $this->db->fetch(
                "SELECT file_name_1, file_name_2, file_name_3, file_type_1, file_type_2, file_type_3, file_path_1, file_path_2, file_path_3 FROM evidence WHERE complaint_id = ? AND (file_name_1 = ? OR file_name_2 = ? OR file_name_3 = ?)",
                [$ticketId, $filename, $filename, $filename]
            );
            
            if (!$evidence) {
                http_response_code(404);
                exit('File not found');
            }
            
            // Determine which file column contains the requested file
            $filePath = null;
            $fileType = null;
            
            if ($evidence['file_name_1'] === $filename) {
                $filePath = Config::getUploadPath() . $evidence['file_path_1'];
                $fileType = $evidence['file_type_1'];
            } elseif ($evidence['file_name_2'] === $filename) {
                $filePath = Config::getUploadPath() . $evidence['file_path_2'];
                $fileType = $evidence['file_type_2'];
            } elseif ($evidence['file_name_3'] === $filename) {
                $filePath = Config::getUploadPath() . $evidence['file_path_3'];
                $fileType = $evidence['file_type_3'];
            }
            
            if (!$filePath || !file_exists($filePath)) {
                http_response_code(404);
                exit('File not found on disk');
            }
            
            // Set appropriate headers
            $mimeType = mime_content_type($filePath);
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($filePath));
            header('Content-Disposition: inline; filename="' . $filename . '"');
            
            // Output file
            readfile($filePath);
            exit;
            
        } catch (Exception $e) {
            error_log("File access error: " . $e->getMessage());
            http_response_code(500);
            exit('Server error');
        }
    }
    
    /**
     * Get notifications for current user
     */
    public function getNotifications() {
        $this->requireAuth();
        
        try {
            $user = $this->getCurrentUser();
            $limit = $_GET['limit'] ?? 50;
            
            $notificationService = new NotificationService();
            $userType = $user['role'] === 'customer' ? 'customer' : 'user';
            $userId = $user['role'] === 'customer' ? $user['customer_id'] : $user['id'];
            
            $notifications = $notificationService->getNotificationHistory($userId, $userType, $limit);
            
            $this->json([
                'success' => true,
                'notifications' => $notifications
            ]);
            
        } catch (Exception $e) {
            error_log("Notifications fetch error: " . $e->getMessage());
            $this->json(['error' => 'Failed to fetch notifications'], 500);
        }
    }
    
    /**
     * Mark notification as read
     */
    public function markNotificationRead($notificationId) {
        $this->requireAuth();
        
        try {
            $user = $this->getCurrentUser();
            
            $notificationService = new NotificationService();
            $userType = $user['role'] === 'customer' ? 'customer' : 'user';
            $userId = $user['role'] === 'customer' ? $user['customer_id'] : $user['id'];
            
            $result = $notificationService->markAsRead($notificationId, $userId, $userType);
            
            if ($result) {
                $this->json(['success' => true, 'message' => 'Notification marked as read']);
            } else {
                $this->json(['error' => 'Failed to mark notification as read'], 400);
            }
            
        } catch (Exception $e) {
            error_log("Mark notification read error: " . $e->getMessage());
            $this->json(['error' => 'Operation failed'], 500);
        }
    }
    
    /**
     * Get unread notification count
     */
    public function getNotificationCount() {
        $this->requireAuth();
        
        try {
            $user = $this->getCurrentUser();
            
            $notificationService = new NotificationService();
            $userType = $user['role'] === 'customer' ? 'customer' : 'user';
            $userId = $user['role'] === 'customer' ? $user['customer_id'] : $user['id'];
            
            $count = $notificationService->getUnreadCount($userId, $userType);
            
            $this->json([
                'success' => true,
                'count' => $count
            ]);
            
        } catch (Exception $e) {
            error_log("Notification count error: " . $e->getMessage());
            $this->json(['error' => 'Failed to get notification count'], 500);
        }
    }
    
    /**
     * Get customer statistics
     */
    public function getCustomerStats() {
        $this->requireAuth();
        $this->requireRole('customer');
        
        try {
            $user = $this->getCurrentUser();
            
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status != 'closed' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as resolved,
                        ROUND(
                            AVG(
                                CASE 
                                    WHEN rating = 'excellent' THEN 100
                                    WHEN rating = 'satisfactory' THEN 75
                                    WHEN rating = 'unsatisfactory' THEN 25
                                    ELSE NULL
                                END
                            ), 0
                        ) as satisfaction_rate
                    FROM complaints 
                    WHERE customer_id = ?";
            
            $stats = $this->db->fetch($sql, [$user['customer_id']]);
            
            $this->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            error_log("Customer stats error: " . $e->getMessage());
            $this->json(['error' => 'Failed to fetch statistics'], 500);
        }
    }
    
    /**
     * Get ticket updates (for real-time polling)
     */
    public function getTicketUpdates() {
        $this->requireAuth();
        
        try {
            $user = $this->getCurrentUser();
            $lastCheck = $_GET['last_check'] ?? date('Y-m-d H:i:s', strtotime('-5 minutes'));
            
            $conditions = ["updated_at > ?"];
            $params = [$lastCheck];
            
            if ($user['role'] === 'customer') {
                $conditions[] = "customer_id = ?";
                $params[] = $user['customer_id'];
            } else {
                $conditions[] = "(assigned_to_user_id = ? OR division = ?)";
                $params[] = $user['id'];
                $params[] = $user['division'];
            }
            
            $whereClause = implode(' AND ', $conditions);
            
            $sql = "SELECT complaint_id, status, priority, updated_at 
                    FROM complaints 
                    WHERE {$whereClause}
                    ORDER BY updated_at DESC";
            
            $updates = $this->db->fetchAll($sql, $params);
            
            $this->json([
                'success' => true,
                'updates' => $updates,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            error_log("Ticket updates error: " . $e->getMessage());
            $this->json(['error' => 'Failed to fetch updates'], 500);
        }
    }
    
    /**
     * Export customer data
     */
    public function exportCustomerData() {
        $this->requireAuth();
        $this->requireRole('customer');
        
        try {
            $user = $this->getCurrentUser();
            
            // Get customer data
            $customerData = $this->db->fetch(
                "SELECT * FROM customers WHERE customer_id = ?",
                [$user['customer_id']]
            );
            
            // Get tickets
            $tickets = $this->db->fetchAll(
                "SELECT c.*, cat.category, cat.type, cat.subtype, s.name as shed_name 
                 FROM complaints c
                 LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                 LEFT JOIN shed s ON c.shed_id = s.shed_id
                 WHERE c.customer_id = ?
                 ORDER BY c.created_at DESC",
                [$user['customer_id']]
            );
            
            // Get transactions
            $transactions = $this->db->fetchAll(
                "SELECT t.*, u.name as user_name 
                 FROM transactions t
                 LEFT JOIN users u ON t.created_by_id = u.id
                 WHERE t.complaint_id IN (
                     SELECT complaint_id FROM complaints WHERE customer_id = ?
                 )
                 ORDER BY t.created_at ASC",
                [$user['customer_id']]
            );
            
            // Generate export data
            $exportData = [
                'customer_info' => $customerData,
                'tickets' => $tickets,
                'transactions' => $transactions,
                'export_date' => date('Y-m-d H:i:s'),
                'total_tickets' => count($tickets)
            ];
            
            // Create temporary file
            $filename = 'sampark_data_' . $user['customer_id'] . '_' . date('Y-m-d') . '.json';
            $tempFile = sys_get_temp_dir() . '/' . $filename;
            
            file_put_contents($tempFile, json_encode($exportData, JSON_PRETTY_PRINT));
            
            // Set download headers
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($tempFile));
            
            // Output file
            readfile($tempFile);
            
            // Clean up
            unlink($tempFile);
            exit;
            
        } catch (Exception $e) {
            error_log("Data export error: " . $e->getMessage());
            $this->json(['error' => 'Export failed'], 500);
        }
    }
    
    /**
     * Get divisions list
     */
    public function getDivisions() {
        try {
            $sql = "SELECT DISTINCT division FROM shed WHERE is_active = 1 ORDER BY division";
            $divisions = $this->db->fetchAll($sql);
            
            $this->json([
                'success' => true,
                'divisions' => $divisions
            ]);
            
        } catch (Exception $e) {
            error_log("Divisions fetch error: " . $e->getMessage());
            $this->json(['error' => 'Failed to fetch divisions'], 500);
        }
    }
    
    /**
     * Get zones list
     */
    public function getZones() {
        try {
            $division = $_GET['division'] ?? '';
            
            $conditions = ["is_active = 1"];
            $params = [];
            
            if ($division) {
                $conditions[] = "division = ?";
                $params[] = $division;
            }
            
            $whereClause = implode(' AND ', $conditions);
            
            $sql = "SELECT DISTINCT zone FROM shed WHERE {$whereClause} ORDER BY zone";
            $zones = $this->db->fetchAll($sql, $params);
            
            $this->json([
                'success' => true,
                'zones' => $zones
            ]);
            
        } catch (Exception $e) {
            error_log("Zones fetch error: " . $e->getMessage());
            $this->json(['error' => 'Failed to fetch zones'], 500);
        }
    }
    
    /**
     * Get news details
     */
    public function getNewsDetails($newsId) {
        try {
            $sql = "SELECT id, title, content, short_description, publish_date, type 
                    FROM news 
                    WHERE id = ? 
                      AND is_active = 1 
                      AND publish_date <= NOW() 
                      AND (expire_date IS NULL OR expire_date > NOW())";
            
            $news = $this->db->fetch($sql, [$newsId]);
            
            if ($news) {
                $this->json([
                    'success' => true,
                    'data' => $news
                ]);
            } else {
                $this->json(['error' => 'News not found'], 404);
            }
            
        } catch (Exception $e) {
            error_log("News fetch error: " . $e->getMessage());
            $this->json(['error' => 'Failed to fetch news'], 500);
        }
    }
    
    /**
     * Search tickets (for autocomplete)
     */
    public function searchTickets() {
        $this->requireAuth();
        
        try {
            $query = $_GET['q'] ?? '';
            $user = $this->getCurrentUser();
            
            if (strlen($query) < 3) {
                $this->json([]);
                return;
            }
            
            $conditions = ["complaint_id LIKE ?"];
            $params = ["%{$query}%"];
            
            if ($user['role'] === 'customer') {
                $conditions[] = "customer_id = ?";
                $params[] = $user['customer_id'];
            } else {
                $conditions[] = "(assigned_to_user_id = ? OR division = ?)";
                $params[] = $user['id'];
                $params[] = $user['division'];
            }
            
            $whereClause = implode(' AND ', $conditions);
            
            $sql = "SELECT complaint_id, description, status, created_at 
                    FROM complaints 
                    WHERE {$whereClause}
                    ORDER BY created_at DESC 
                    LIMIT 10";
            
            $tickets = $this->db->fetchAll($sql, $params);
            
            $this->json($tickets);
            
        } catch (Exception $e) {
            error_log("Ticket search error: " . $e->getMessage());
            $this->json(['error' => 'Search failed'], 500);
        }
    }
    
    /**
     * Validate GSTIN
     */
    public function validateGstin() {
        $gstin = $_GET['gstin'] ?? '';
        
        if (empty($gstin)) {
            $this->json(['valid' => false, 'message' => 'GSTIN is required']);
            return;
        }
        
        $pattern = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}[Z]{1}[0-9A-Z]{1}$/';
        $isValid = preg_match($pattern, $gstin);
        
        $this->json([
            'valid' => (bool)$isValid,
            'message' => $isValid ? 'Valid GSTIN' : 'Invalid GSTIN format'
        ]);
    }
    
    /**
     * Get system health status
     */
    public function getSystemHealth() {
        try {
            $health = [
                'status' => 'healthy',
                'timestamp' => date('Y-m-d H:i:s'),
                'database' => 'connected',
                'storage' => 'available',
                'services' => []
            ];
            
            // Check database
            try {
                $this->db->fetch("SELECT 1");
                $health['database'] = 'connected';
            } catch (Exception $e) {
                $health['database'] = 'disconnected';
                $health['status'] = 'unhealthy';
            }
            
            // Check storage
            $uploadPath = Config::getUploadPath();
            if (!is_dir($uploadPath) || !is_writable($uploadPath)) {
                $health['storage'] = 'unavailable';
                $health['status'] = 'degraded';
            }
            
            // Check email service
            $health['services']['email'] = $this->getSetting('enable_email') ? 'enabled' : 'disabled';
            
            // Check SMS service
            $health['services']['sms'] = $this->getSetting('enable_sms') ? 'enabled' : 'disabled';
            
            $this->json($health);
            
        } catch (Exception $e) {
            $this->json([
                'status' => 'unhealthy',
                'error' => 'Health check failed',
                'timestamp' => date('Y-m-d H:i:s')
            ], 500);
        }
    }
    
    /**
     * Get system setting
     */
    private function getSetting($key, $default = false) {
        try {
            $sql = "SELECT setting_value FROM system_settings WHERE setting_key = ?";
            $result = $this->db->fetch($sql, [$key]);
            
            if ($result) {
                return $result['setting_value'] === '1';
            }
            
            return $default;
            
        } catch (Exception $e) {
            return $default;
        }
    }
}
