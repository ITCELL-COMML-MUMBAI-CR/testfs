<?php
/**
 * Background Refresh Service for SAMPARK
 * Handles automated ticket processing and silent updates
 */

require_once 'WorkflowEngine.php';
require_once 'ActivityLogger.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/Config.php';

class BackgroundRefreshService {
    
    private $db;
    private $workflowEngine;
    private $logger;
    private $lastProcessTime;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->workflowEngine = new WorkflowEngine();
        $this->logger = new ActivityLogger();
        $this->lastProcessTime = $this->getLastProcessTime();
    }
    
    /**
     * Main background processing function
     * Runs all automated tasks silently
     */
    public function processAutomationTasks() {
        try {
            $startTime = microtime(true);
            $results = [
                'processed_at' => date('Y-m-d H:i:s'),
                'execution_time' => 0,
                'tasks_completed' => 0,
                'errors' => []
            ];
            
            // 1. Process priority escalations
            $escalationResult = $this->processPriorityEscalations();
            $results['escalations'] = $escalationResult;
            if ($escalationResult['success']) {
                $results['tasks_completed']++;
            } else {
                $results['errors'][] = 'Priority escalation failed: ' . $escalationResult['error'];
            }
            
            // 2. Process SLA violations and auto-escalations
            $slaResult = $this->processSLAViolations();
            $results['sla_processing'] = $slaResult;
            if ($slaResult['success']) {
                $results['tasks_completed']++;
            } else {
                $results['errors'][] = 'SLA processing failed: ' . $slaResult['error'];
            }
            
            // 3. Auto-close awaiting feedback tickets
            $autoCloseResult = $this->processAutoClose();
            $results['auto_close'] = $autoCloseResult;
            if ($autoCloseResult['success']) {
                $results['tasks_completed']++;
            } else {
                $results['errors'][] = 'Auto-close failed: ' . $autoCloseResult['error'];
            }
            
            // 4. Send pending notifications
            $notificationResult = $this->processPendingNotifications();
            $results['notifications'] = $notificationResult;
            if ($notificationResult['success']) {
                $results['tasks_completed']++;
            }
            
            // 5. Update cache and statistics
            $this->updateSystemStatistics();
            $results['tasks_completed']++;
            
            // Record processing time
            $results['execution_time'] = round((microtime(true) - $startTime) * 1000, 2);
            
            // Log successful processing
            $this->logger->logSystem('background_automation', 
                "Processed {$results['tasks_completed']} automation tasks in {$results['execution_time']}ms"
            );
            
            // Update last process time
            $this->updateLastProcessTime();
            
            return [
                'success' => true,
                'results' => $results
            ];
            
        } catch (Exception $e) {
            error_log("Background automation error: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'results' => $results ?? []
            ];
        }
    }
    
    /**
     * Get updated ticket data for DataTables refresh
     */
    public function getUpdatedTicketData($userRole, $userId, $filters = []) {
        try {
            $tickets = [];
            
            switch ($userRole) {
                case 'customer':
                    $tickets = $this->getCustomerTickets($userId, $filters);
                    break;
                    
                case 'controller':
                case 'controller_nodal':
                    $tickets = $this->getControllerTickets($userId, $userRole, $filters);
                    break;
                    
                case 'admin':
                case 'superadmin':
                    $tickets = $this->getAdminTickets($filters);
                    break;
            }
            
            // Add real-time indicators
            foreach ($tickets as &$ticket) {
                $ticket['is_urgent'] = $this->isTicketUrgent($ticket);
                $ticket['sla_status'] = $this->getSLAStatus($ticket);
                $ticket['last_activity'] = $this->getLastActivity($ticket['complaint_id']);
                $ticket['priority_class'] = $this->getPriorityClass($ticket['priority']);
                $ticket['status_class'] = $this->getStatusClass($ticket['status']);
            }
            
            return [
                'success' => true,
                'data' => $tickets,
                'recordsTotal' => count($tickets),
                'recordsFiltered' => count($tickets),
                'updated_at' => date('c')
            ];
            
        } catch (Exception $e) {
            error_log("Get updated ticket data error: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Process priority escalations
     */
    private function processPriorityEscalations() {
        return $this->workflowEngine->processPriorityEscalation();
    }
    
    /**
     * Process SLA violations
     */
    private function processSLAViolations() {
        return $this->workflowEngine->processAutoEscalation();
    }
    
    /**
     * Process auto-close tickets
     */
    private function processAutoClose() {
        return $this->workflowEngine->processAutoClose();
    }
    
    /**
     * Process pending notifications
     */
    private function processPendingNotifications() {
        try {
            // Send digest emails for priority tickets
            $digestResult = $this->sendPriorityTicketDigest();
            
            // Send SLA violation alerts
            $alertResult = $this->sendSLAViolationAlerts();
            
            return ['success' => true, 'notifications_sent' => ($digestResult + $alertResult)];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get customer tickets with filters
     */
    private function getCustomerTickets($customerId, $filters) {
        $conditions = ['c.customer_id = ?'];
        $params = [$customerId];
        
        // Customer only sees active tickets by default
        if (!isset($filters['include_closed']) || !$filters['include_closed']) {
            $conditions[] = "c.status != 'closed'";
        }
        
        $this->applyFilters($conditions, $params, $filters);
        
        $sql = "SELECT c.*, 
                       cat.category, cat.type, cat.subtype,
                       s.name as shed_name, s.shed_code,
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed,
                       CASE 
                           WHEN c.status = 'awaiting_feedback' THEN TIMESTAMPDIFF(DAY, c.updated_at, NOW())
                           ELSE 0
                       END as days_since_revert,
                       CASE 
                           WHEN c.sla_deadline IS NOT NULL AND NOW() > c.sla_deadline THEN 1
                           ELSE 0
                       END as is_sla_violated
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY 
                    CASE WHEN c.priority = 'critical' THEN 1
                         WHEN c.priority = 'high' THEN 2
                         WHEN c.priority = 'medium' THEN 3
                         ELSE 4
                    END,
                    c.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get controller tickets with filters
     */
    private function getControllerTickets($userId, $userRole, $filters) {
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        
        $conditions = [];
        $params = [];
        
        // Both controller and controller_nodal see tickets in their division/department
        $conditions[] = 'c.division = ? AND c.assigned_to_department = ?';
        $params[] = $user['division'];
        $params[] = $user['department'];
        
        $this->applyFilters($conditions, $params, $filters);
        
        $sql = "SELECT c.*, 
                       cat.category, cat.type, cat.subtype,
                       s.name as shed_name, s.shed_code,
                       cust.name as customer_name, cust.email as customer_email,
                       cust.company_name, cust.mobile as customer_mobile,
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed,
                       CASE 
                           WHEN c.status = 'awaiting_feedback' THEN TIMESTAMPDIFF(DAY, c.updated_at, NOW())
                           ELSE 0
                       END as days_since_revert,
                       CASE 
                           WHEN c.sla_deadline IS NOT NULL AND NOW() > c.sla_deadline THEN 1
                           ELSE 0
                       END as is_sla_violated
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY 
                    CASE WHEN c.priority = 'critical' THEN 1
                         WHEN c.priority = 'high' THEN 2
                         WHEN c.priority = 'medium' THEN 3
                         ELSE 4
                    END,
                    c.created_at ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get admin tickets (all tickets)
     */
    private function getAdminTickets($filters) {
        $conditions = ['1=1']; // Admin sees all tickets
        $params = [];
        
        $this->applyFilters($conditions, $params, $filters);
        
        $sql = "SELECT c.*, 
                       cat.category, cat.type, cat.subtype,
                       s.name as shed_name, s.shed_code,
                       cust.name as customer_name, cust.company_name,
                       TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed,
                       CASE 
                           WHEN c.status = 'awaiting_feedback' THEN TIMESTAMPDIFF(DAY, c.updated_at, NOW())
                           ELSE 0
                       END as days_since_revert,
                       CASE 
                           WHEN c.sla_deadline IS NOT NULL AND NOW() > c.sla_deadline THEN 1
                           ELSE 0
                       END as is_sla_violated
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN shed s ON c.shed_id = s.shed_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY 
                    CASE WHEN c.priority = 'critical' THEN 1
                         WHEN c.priority = 'high' THEN 2
                         WHEN c.priority = 'medium' THEN 3
                         ELSE 4
                    END,
                    c.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Apply filters to query
     */
    private function applyFilters(&$conditions, &$params, $filters) {
        if (!empty($filters['status'])) {
            $conditions[] = 'c.status = ?';
            $params[] = $filters['status'];
        }
        
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
        
        if (!empty($filters['division'])) {
            $conditions[] = 'c.division = ?';
            $params[] = $filters['division'];
        }
    }
    
    /**
     * Check if ticket is urgent
     */
    private function isTicketUrgent($ticket) {
        return in_array($ticket['priority'], ['high', 'critical']) || 
               $ticket['is_sla_violated'] || 
               ($ticket['status'] === 'awaiting_feedback' && $ticket['days_since_revert'] >= 2);
    }
    
    /**
     * Get SLA status
     */
    private function getSLAStatus($ticket) {
        if (empty($ticket['sla_deadline'])) {
            return 'no_sla';
        }
        
        $now = time();
        $deadline = strtotime($ticket['sla_deadline']);
        $timeLeft = $deadline - $now;
        
        if ($timeLeft <= 0) {
            return 'violated';
        } elseif ($timeLeft <= 3600) { // 1 hour
            return 'critical';
        } elseif ($timeLeft <= 7200) { // 2 hours
            return 'warning';
        } else {
            return 'safe';
        }
    }
    
    /**
     * Get last activity for ticket
     */
    private function getLastActivity($complaintId) {
        $activity = $this->db->fetch(
            "SELECT created_at FROM transactions WHERE complaint_id = ? ORDER BY created_at DESC LIMIT 1",
            [$complaintId]
        );
        
        return $activity ? $activity['created_at'] : null;
    }
    
    /**
     * Get priority CSS class
     */
    private function getPriorityClass($priority) {
        $classes = [
            'normal' => 'badge-secondary',
            'medium' => 'badge-info',
            'high' => 'badge-warning',
            'critical' => 'badge-danger'
        ];
        
        return $classes[$priority] ?? 'badge-secondary';
    }
    
    /**
     * Get status CSS class
     */
    private function getStatusClass($status) {
        $classes = [
            'pending' => 'badge-primary',
            'awaiting_info' => 'badge-warning',
            'awaiting_approval' => 'badge-info',
            'awaiting_feedback' => 'badge-success',
            'closed' => 'badge-dark'
        ];
        
        return $classes[$status] ?? 'badge-secondary';
    }
    
    /**
     * Send priority ticket digest
     */
    private function sendPriorityTicketDigest() {
        // Get high priority tickets that need notification
        $sql = "SELECT COUNT(*) as count FROM complaints 
                WHERE priority IN ('high', 'critical') 
                  AND status NOT IN ('closed') 
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        $result = $this->db->fetch($sql);
        $count = $result ? $result['count'] : 0;
        
        // For now, just log the count - actual email sending would be implemented here
        if ($count > 0) {
            error_log("Priority ticket digest: {$count} high/critical tickets in last hour");
        }
        
        return $count;
    }
    
    /**
     * Send SLA violation alerts
     */
    private function sendSLAViolationAlerts() {
        // Get SLA violations that need notification  
        $sql = "SELECT COUNT(*) as count FROM complaints 
                WHERE sla_deadline IS NOT NULL 
                  AND NOW() > sla_deadline 
                  AND status NOT IN ('closed')";
        
        $result = $this->db->fetch($sql);
        $count = $result ? $result['count'] : 0;
        
        // For now, just log the count - actual notification sending would be implemented here
        if ($count > 0) {
            error_log("SLA violation alert: {$count} tickets have violated SLA");
        }
        
        return $count;
    }
    
    /**
     * Update system statistics
     */
    private function updateSystemStatistics() {
        // Update cached statistics for dashboards
        $stats = [
            'total_active_tickets' => $this->db->fetch("SELECT COUNT(*) as count FROM complaints WHERE status != 'closed'")['count'],
            'high_priority_tickets' => $this->db->fetch("SELECT COUNT(*) as count FROM complaints WHERE priority IN ('high', 'critical') AND status != 'closed'")['count'],
            'sla_violations' => $this->db->fetch("SELECT COUNT(*) as count FROM complaints WHERE sla_deadline IS NOT NULL AND NOW() > sla_deadline AND status != 'closed'")['count'],
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Cache statistics
        $this->db->query(
            "INSERT INTO system_cache (cache_key, cache_data, updated_at) VALUES ('system_stats', ?, NOW()) 
             ON DUPLICATE KEY UPDATE cache_data = VALUES(cache_data), updated_at = VALUES(updated_at)",
            [json_encode($stats)]
        );
    }
    
    /**
     * Get last process time
     */
    private function getLastProcessTime() {
        $result = $this->db->fetch(
            "SELECT cache_data FROM system_cache WHERE cache_key = 'last_automation_run'"
        );
        
        return $result ? json_decode($result['cache_data'], true) : null;
    }
    
    /**
     * Update last process time
     */
    private function updateLastProcessTime() {
        $this->db->query(
            "INSERT INTO system_cache (cache_key, cache_data, updated_at) VALUES ('last_automation_run', ?, NOW()) 
             ON DUPLICATE KEY UPDATE cache_data = VALUES(cache_data), updated_at = VALUES(updated_at)",
            [json_encode(['last_run' => date('Y-m-d H:i:s')])]
        );
    }
}