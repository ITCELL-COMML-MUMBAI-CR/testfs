<?php
/**
 * Scheduled Task Runner for SAMPARK
 * Handles automated processes like SLA monitoring, escalations, and cleanup
 */

require_once 'WorkflowEngine.php';
require_once 'SLAManager.php';
require_once 'NotificationService.php';
require_once 'ActivityLogger.php';
require_once 'FileUploader.php';

class ScheduledTaskRunner {
    
    private $db;
    private $workflowEngine;
    private $slaManager;
    private $notificationService;
    private $logger;
    private $fileUploader;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->workflowEngine = new WorkflowEngine();
        $this->slaManager = new SLAManager();
        $this->notificationService = new NotificationService();
        $this->logger = new ActivityLogger();
        $this->fileUploader = new FileUploader();
    }
    
    /**
     * Run all scheduled tasks
     */
    public function runAll() {
        $results = [
            'started_at' => date('Y-m-d H:i:s'),
            'tasks' => [],
            'total_duration' => 0,
            'errors' => []
        ];
        
        $startTime = microtime(true);
        
        try {
            // 1. SLA Monitoring and Escalation
            $results['tasks']['sla_monitoring'] = $this->runSLAMonitoring();
            
            // 2. Auto-escalation
            $results['tasks']['auto_escalation'] = $this->runAutoEscalation();
            
            // 3. Auto-closure
            $results['tasks']['auto_closure'] = $this->runAutoClosure();
            
            // 4. Priority escalation
            $results['tasks']['priority_escalation'] = $this->runPriorityEscalation();
            
            // 5. Cleanup tasks
            $results['tasks']['cleanup'] = $this->runCleanupTasks();
            
            // 6. Generate reports
            $results['tasks']['reports'] = $this->runReportGeneration();
            
            // 7. Send digest notifications
            $results['tasks']['digest_notifications'] = $this->runDigestNotifications();
            
            $endTime = microtime(true);
            $results['total_duration'] = round($endTime - $startTime, 2);
            $results['completed_at'] = date('Y-m-d H:i:s');
            $results['status'] = 'completed';
            
            // Log successful run
            $this->logger->logSystem('scheduled_tasks_completed', 'All scheduled tasks completed successfully', $results);
            
        } catch (Exception $e) {
            $results['status'] = 'failed';
            $results['error'] = $e->getMessage();
            $results['errors'][] = $e->getMessage();
            
            // Log error
            $this->logger->logSystem('scheduled_tasks_failed', 'Scheduled tasks failed: ' . $e->getMessage(), $results);
            Config::logError('Scheduled tasks failed: ' . $e->getMessage(), $results);
        }
        
        return $results;
    }
    
    /**
     * Run SLA monitoring
     */
    public function runSLAMonitoring() {
        $startTime = microtime(true);
        
        try {
            $result = $this->slaManager->monitorSLACompliance();
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            return [
                'status' => 'completed',
                'duration' => $duration,
                'result' => $result
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'duration' => round(microtime(true) - $startTime, 2)
            ];
        }
    }
    
    /**
     * Run auto-escalation
     */
    public function runAutoEscalation() {
        $startTime = microtime(true);
        
        try {
            $result = $this->workflowEngine->processAutoEscalation();
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            return [
                'status' => 'completed',
                'duration' => $duration,
                'result' => $result
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'duration' => round(microtime(true) - $startTime, 2)
            ];
        }
    }
    
    /**
     * Run auto-closure
     */
    public function runAutoClosure() {
        $startTime = microtime(true);
        
        try {
            $result = $this->workflowEngine->processAutoClose();
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            return [
                'status' => 'completed',
                'duration' => $duration,
                'result' => $result
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'duration' => round(microtime(true) - $startTime, 2)
            ];
        }
    }
    
    /**
     * Run priority escalation
     */
    public function runPriorityEscalation() {
        $startTime = microtime(true);
        
        try {
            $result = $this->workflowEngine->processPriorityEscalation();
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            return [
                'status' => 'completed',
                'duration' => $duration,
                'result' => $result
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'duration' => round(microtime(true) - $startTime, 2)
            ];
        }
    }
    
    /**
     * Run cleanup tasks
     */
    public function runCleanupTasks() {
        $startTime = microtime(true);
        $results = [];
        
        try {
            // 1. Clean up old files
            $cleanupDays = $this->getSetting('file_cleanup_days', 365);
            $filesDeleted = $this->fileUploader->cleanupOldFiles($cleanupDays);
            $results['files_deleted'] = $filesDeleted;
            
            // 2. Clean up old activity logs
            $logRetentionDays = $this->getSetting('log_retention_days', 365);
            $logsDeleted = $this->logger->cleanup($logRetentionDays);
            $results['logs_deleted'] = $logsDeleted;
            
            // 3. Clean up old notifications
            $notificationRetentionDays = $this->getSetting('notification_retention_days', 90);
            $notificationsDeleted = $this->cleanupOldNotifications($notificationRetentionDays);
            $results['notifications_deleted'] = $notificationsDeleted;
            
            // 4. Optimize database tables
            $this->optimizeDatabaseTables();
            $results['database_optimized'] = true;
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            return [
                'status' => 'completed',
                'duration' => $duration,
                'result' => $results
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'duration' => round(microtime(true) - $startTime, 2),
                'partial_results' => $results
            ];
        }
    }
    
    /**
     * Run report generation
     */
    public function runReportGeneration() {
        $startTime = microtime(true);
        $results = [];
        
        try {
            // Generate daily SLA compliance report
            if ($this->shouldGenerateDailyReport()) {
                $yesterday = date('Y-m-d', strtotime('-1 day'));
                $slaReport = $this->slaManager->generateComplianceReport($yesterday, $yesterday);
                
                // Store report in database or send to management
                $this->storeDailyReport('sla_compliance', $slaReport);
                $results['daily_sla_report'] = 'generated';
            }
            
            // Generate weekly summary report
            if ($this->shouldGenerateWeeklyReport()) {
                $weekStart = date('Y-m-d', strtotime('last monday', strtotime('-1 day')));
                $weekEnd = date('Y-m-d', strtotime('last sunday', strtotime('-1 day')));
                
                $weeklyReport = $this->generateWeeklySummary($weekStart, $weekEnd);
                $this->sendWeeklyReportToManagement($weeklyReport);
                $results['weekly_summary'] = 'generated';
            }
            
            // Generate monthly report
            if ($this->shouldGenerateMonthlyReport()) {
                $lastMonth = date('Y-m', strtotime('-1 month'));
                $monthStart = $lastMonth . '-01';
                $monthEnd = date('Y-m-t', strtotime($lastMonth . '-01'));
                
                $monthlyReport = $this->generateMonthlySummary($monthStart, $monthEnd);
                $this->sendMonthlyReportToManagement($monthlyReport);
                $results['monthly_summary'] = 'generated';
            }
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            return [
                'status' => 'completed',
                'duration' => $duration,
                'result' => $results
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'duration' => round(microtime(true) - $startTime, 2),
                'partial_results' => $results
            ];
        }
    }
    
    /**
     * Run digest notifications
     */
    public function runDigestNotifications() {
        $startTime = microtime(true);
        $results = [];
        
        try {
            // Send daily digest to nodal controllers
            if ($this->shouldSendDailyDigest()) {
                $digestSent = $this->sendDailyDigestToNodalControllers();
                $results['daily_digest'] = $digestSent;
            }
            
            // Send weekly digest to management
            if ($this->shouldSendWeeklyDigest()) {
                $digestSent = $this->sendWeeklyDigestToManagement();
                $results['weekly_digest'] = $digestSent;
            }
            
            // Send SLA warning notifications
            $warningsSent = $this->sendSLAWarningDigest();
            $results['sla_warnings'] = $warningsSent;
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            return [
                'status' => 'completed',
                'duration' => $duration,
                'result' => $results
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'duration' => round(microtime(true) - $startTime, 2),
                'partial_results' => $results
            ];
        }
    }
    
    /**
     * Run specific task by name
     */
    public function runTask($taskName) {
        switch ($taskName) {
            case 'sla_monitoring':
                return $this->runSLAMonitoring();
            case 'auto_escalation':
                return $this->runAutoEscalation();
            case 'auto_closure':
                return $this->runAutoClosure();
            case 'priority_escalation':
                return $this->runPriorityEscalation();
            case 'cleanup':
                return $this->runCleanupTasks();
            case 'reports':
                return $this->runReportGeneration();
            case 'digest_notifications':
                return $this->runDigestNotifications();
            default:
                throw new Exception("Unknown task: {$taskName}");
        }
    }
    
    // Helper methods
    
    private function cleanupOldNotifications($days) {
        $sql = "DELETE FROM notifications 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
                  AND is_read = 1";
        
        $this->db->query($sql, [$days]);
        return $this->db->getConnection()->rowCount();
    }
    
    private function optimizeDatabaseTables() {
        $tables = [
            'complaints', 'transactions', 'evidence', 'activity_logs', 
            'notifications', 'customers', 'users'
        ];
        
        foreach ($tables as $table) {
            $this->db->query("OPTIMIZE TABLE {$table}");
        }
    }
    
    private function shouldGenerateDailyReport() {
        // Check if daily report already generated for yesterday
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $sql = "SELECT id FROM reports WHERE report_type = 'daily_sla' AND report_date = ?";
        $existing = $this->db->fetch($sql, [$yesterday]);
        
        return empty($existing);
    }
    
    private function shouldGenerateWeeklyReport() {
        // Generate on Monday for previous week
        return date('l') === 'Monday';
    }
    
    private function shouldGenerateMonthlyReport() {
        // Generate on 1st of month for previous month
        return date('j') === '1';
    }
    
    private function shouldSendDailyDigest() {
        // Send daily digest at 9 AM
        return date('H') === '09';
    }
    
    private function shouldSendWeeklyDigest() {
        // Send weekly digest on Monday at 9 AM
        return date('l') === 'Monday' && date('H') === '09';
    }
    
    private function storeDailyReport($type, $data) {
        $sql = "INSERT INTO reports (report_type, report_date, report_data, generated_at) 
                VALUES (?, ?, ?, NOW())";
        
        $this->db->query($sql, [$type, date('Y-m-d', strtotime('-1 day')), json_encode($data)]);
    }
    
    private function generateWeeklySummary($startDate, $endDate) {
        $sql = "SELECT 
                    COUNT(*) as total_tickets,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_tickets,
                    AVG(CASE WHEN status = 'closed' THEN TIMESTAMPDIFF(HOUR, created_at, closed_at) END) as avg_resolution_hours,
                    SUM(CASE WHEN priority = 'critical' THEN 1 ELSE 0 END) as critical_tickets,
                    SUM(CASE WHEN escalated_at IS NOT NULL THEN 1 ELSE 0 END) as escalated_tickets
                FROM complaints 
                WHERE created_at BETWEEN ? AND ?";
        
        return $this->db->fetch($sql, [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
    }
    
    private function generateMonthlySummary($startDate, $endDate) {
        // Similar to weekly but with more detailed metrics
        return $this->generateWeeklySummary($startDate, $endDate);
    }
    
    private function sendWeeklyReportToManagement($report) {
        // Implementation for sending weekly report
        $recipients = $this->getManagementEmails();
        $this->notificationService->send('weekly_report', $recipients, $report);
    }
    
    private function sendMonthlyReportToManagement($report) {
        // Implementation for sending monthly report
        $recipients = $this->getManagementEmails();
        $this->notificationService->send('monthly_report', $recipients, $report);
    }
    
    private function sendDailyDigestToNodalControllers() {
        $nodalControllers = $this->getNodalControllers();
        $sent = 0;
        
        foreach ($nodalControllers as $controller) {
            $digestData = $this->generateNodalControllerDigest($controller['division']);
            $result = $this->notificationService->send('daily_digest', [$controller], $digestData);
            
            if ($result[0]['email_sent']) {
                $sent++;
            }
        }
        
        return $sent;
    }
    
    private function sendWeeklyDigestToManagement() {
        $recipients = $this->getManagementEmails();
        $digestData = $this->generateManagementDigest();
        $results = $this->notificationService->send('weekly_digest', $recipients, $digestData);
        
        return count(array_filter($results, function($r) { return $r['email_sent']; }));
    }
    
    private function sendSLAWarningDigest() {
        $approachingTickets = $this->slaManager->getTicketsApproachingSLA(8); // 8 hours threshold
        $sent = 0;
        
        foreach ($approachingTickets as $ticket) {
            if ($ticket['assigned_user_email']) {
                $recipients = [[
                    'user_id' => $ticket['assigned_to_user_id'],
                    'email' => $ticket['assigned_user_email'],
                    'name' => $ticket['assigned_user_name']
                ]];
                
                $data = [
                    'complaint_id' => $ticket['complaint_id'],
                    'hours_remaining' => $ticket['hours_remaining'],
                    'priority' => $ticket['priority']
                ];
                
                $result = $this->notificationService->send('sla_warning', $recipients, $data);
                if ($result[0]['email_sent']) {
                    $sent++;
                }
            }
        }
        
        return $sent;
    }
    
    private function generateNodalControllerDigest($division) {
        $sql = "SELECT 
                    COUNT(*) as total_open,
                    SUM(CASE WHEN priority IN ('high', 'critical') THEN 1 ELSE 0 END) as high_priority_count,
                    SUM(CASE WHEN NOW() > sla_deadline THEN 1 ELSE 0 END) as sla_violations,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_assignment
                FROM complaints 
                WHERE division = ? AND status != 'closed'";
        
        return $this->db->fetch($sql, [$division]);
    }
    
    private function generateManagementDigest() {
        $sql = "SELECT 
                    COUNT(*) as total_open,
                    SUM(CASE WHEN priority = 'critical' THEN 1 ELSE 0 END) as critical,
                    SUM(CASE WHEN NOW() > sla_deadline THEN 1 ELSE 0 END) as sla_violations,
                    COUNT(DISTINCT division) as divisions_with_tickets
                FROM complaints 
                WHERE status != 'closed'";
        
        return $this->db->fetch($sql);
    }
    
    private function getNodalControllers() {
        $sql = "SELECT id as user_id, name, email, division 
                FROM users 
                WHERE role = 'controller_nodal' AND status = 'active'";
        
        return $this->db->fetchAll($sql);
    }
    
    private function getManagementEmails() {
        $sql = "SELECT id as user_id, name, email 
                FROM users 
                WHERE role IN ('admin', 'superadmin') AND status = 'active'";
        
        return $this->db->fetchAll($sql);
    }
    
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
