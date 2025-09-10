<?php
/**
 * SLA Manager for SAMPARK
 * Handles Service Level Agreement monitoring, escalation, and reporting
 */

require_once 'NotificationService.php';
require_once 'ActivityLogger.php';

class SLAManager {
    
    private $db;
    private $notificationService;
    private $logger;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->notificationService = new NotificationService();
        $this->logger = new ActivityLogger();
    }
    
    /**
     * Monitor SLA compliance for all active tickets
     */
    public function monitorSLACompliance() {
        try {
            $results = [
                'total_monitored' => 0,
                'sla_violations' => 0,
                'escalations_triggered' => 0,
                'warnings_sent' => 0,
                'actions_taken' => []
            ];
            
            // Get all active tickets with SLA definitions
            $sql = "SELECT c.*, sla.escalation_hours, sla.resolution_hours,
                           cust.name as customer_name, cust.email as customer_email,
                           u.name as assigned_user_name, u.email as assigned_user_email,
                           u.role as assigned_user_role,
                           TIMESTAMPDIFF(HOUR, c.created_at, NOW()) as hours_elapsed,
                           TIMESTAMPDIFF(HOUR, c.sla_deadline, NOW()) as hours_overdue
                    FROM complaints c
                    LEFT JOIN sla_definitions sla ON c.priority = sla.priority_level
                    LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                    LEFT JOIN users u ON c.assigned_to_user_id = u.id
                    WHERE c.status NOT IN ('closed') 
                      AND sla.is_active = 1";
            
            $tickets = $this->db->fetchAll($sql);
            $results['total_monitored'] = count($tickets);
            
            foreach ($tickets as $ticket) {
                $ticketResults = $this->processSLAForTicket($ticket);
                
                if ($ticketResults['violation']) {
                    $results['sla_violations']++;
                }
                
                if ($ticketResults['escalation_triggered']) {
                    $results['escalations_triggered']++;
                }
                
                if ($ticketResults['warning_sent']) {
                    $results['warnings_sent']++;
                }
                
                if (!empty($ticketResults['actions'])) {
                    $results['actions_taken'] = array_merge($results['actions_taken'], $ticketResults['actions']);
                }
            }
            
            // Log SLA monitoring results
            $this->logger->logSystem('sla_monitoring', 'SLA compliance monitoring completed', $results);
            
            return [
                'success' => true,
                'results' => $results
            ];
            
        } catch (Exception $e) {
            error_log("SLA monitoring error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Process SLA for individual ticket
     */
    private function processSLAForTicket($ticket) {
        $results = [
            'violation' => false,
            'escalation_triggered' => false,
            'warning_sent' => false,
            'actions' => []
        ];
        
        // Check for SLA deadline violation
        if ($ticket['sla_deadline'] && strtotime($ticket['sla_deadline']) < time()) {
            $results['violation'] = true;
            $this->handleSLAViolation($ticket);
            $results['actions'][] = "SLA violation handled for ticket {$ticket['complaint_id']}";
        }
        
        // Check for escalation based on time elapsed
        if ($ticket['escalation_hours'] && 
            $ticket['hours_elapsed'] >= $ticket['escalation_hours'] && 
            !$ticket['escalated_at']) {
            
            $escalationResult = $this->triggerEscalation($ticket);
            if ($escalationResult['success']) {
                $results['escalation_triggered'] = true;
                $results['actions'][] = "Escalation triggered for ticket {$ticket['complaint_id']}";
            }
        }
        
        // Send warning if approaching SLA deadline
        $warningResult = $this->checkAndSendSLAWarning($ticket);
        if ($warningResult['warning_sent']) {
            $results['warning_sent'] = true;
            $results['actions'][] = "SLA warning sent for ticket {$ticket['complaint_id']}";
        }
        
        return $results;
    }
    
    /**
     * Handle SLA violation
     */
    private function handleSLAViolation($ticket) {
        try {
            // Mark violation in database
            $this->markSLAViolation($ticket['complaint_id']);
            
            // Send violation notifications
            $this->sendSLAViolationNotifications($ticket);
            
            // Log violation
            $this->logger->logSecurity(
                'sla_violation',
                "SLA violation for ticket {$ticket['complaint_id']} - {$ticket['hours_overdue']} hours overdue",
                'high'
            );
            
            // Auto-escalate if configured
            if ($this->shouldAutoEscalateOnViolation($ticket)) {
                $this->autoEscalateViolatedTicket($ticket);
            }
            
        } catch (Exception $e) {
            error_log("SLA violation handling error: " . $e->getMessage());
        }
    }
    
    /**
     * Trigger escalation
     */
    private function triggerEscalation($ticket) {
        try {
            $this->db->beginTransaction();
            
            // Mark ticket as escalated
            $sql = "UPDATE complaints SET 
                    escalated_at = NOW(),
                    updated_at = NOW()
                    WHERE complaint_id = ?";
            
            $this->db->query($sql, [$ticket['complaint_id']]);
            
            // Find escalation target
            $escalationTarget = $this->findEscalationTarget($ticket);
            
            if ($escalationTarget) {
                // Reassign ticket to escalation target
                $sql = "UPDATE complaints SET 
                        assigned_to_user_id = ?
                        WHERE complaint_id = ?";
                
                $this->db->query($sql, [$escalationTarget['id'], $ticket['complaint_id']]);
            }
            
            // Send escalation notifications
            $this->sendEscalationNotifications($ticket, $escalationTarget);
            
            // Log escalation
            $this->logger->logTicket(
                $ticket['complaint_id'],
                'escalated',
                null,
                'system',
                [
                    'reason' => 'SLA escalation time exceeded',
                    'hours_elapsed' => $ticket['hours_elapsed'],
                    'escalated_to' => $escalationTarget['name'] ?? 'Management'
                ]
            );
            
            $this->db->commit();
            
            return ['success' => true, 'escalated_to' => $escalationTarget];
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Escalation error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Check and send SLA warning
     */
    private function checkAndSendSLAWarning($ticket) {
        if (!$ticket['sla_deadline']) {
            return ['warning_sent' => false];
        }
        
        $deadlineTime = strtotime($ticket['sla_deadline']);
        $currentTime = time();
        $hoursToDeadline = ($deadlineTime - $currentTime) / 3600;
        
        // Send warning if within warning threshold (e.g., 4 hours before deadline)
        $warningThreshold = $this->getSLAWarningThreshold($ticket['priority']);
        
        if ($hoursToDeadline > 0 && $hoursToDeadline <= $warningThreshold) {
            // Check if warning already sent
            if (!$this->isWarningAlreadySent($ticket['complaint_id'])) {
                $this->sendSLAWarningNotification($ticket, $hoursToDeadline);
                $this->markWarningAsSent($ticket['complaint_id']);
                return ['warning_sent' => true];
            }
        }
        
        return ['warning_sent' => false];
    }
    
    /**
     * Calculate SLA deadline for new ticket
     */
    public function calculateSLADeadline($priority, $createdAt = null) {
        if (!$createdAt) {
            $createdAt = date('Y-m-d H:i:s');
        }
        
        $slaInfo = $this->db->fetch(
            "SELECT resolution_hours, escalation_hours FROM sla_definitions 
             WHERE priority_level = ? AND is_active = 1",
            [$priority]
        );
        
        if (!$slaInfo) {
            return null;
        }
        
        $deadline = date('Y-m-d H:i:s', strtotime($createdAt . " +{$slaInfo['resolution_hours']} hours"));
        
        return [
            'deadline' => $deadline,
            'resolution_hours' => $slaInfo['resolution_hours'],
            'escalation_hours' => $slaInfo['escalation_hours']
        ];
    }
    
    /**
     * Update SLA deadline for existing ticket
     */
    public function updateSLADeadline($complaintId, $priority) {
        $slaInfo = $this->calculateSLADeadline($priority);
        
        if ($slaInfo) {
            $sql = "UPDATE complaints SET sla_deadline = ? WHERE complaint_id = ?";
            $this->db->query($sql, [$slaInfo['deadline'], $complaintId]);
            
            return $slaInfo['deadline'];
        }
        
        return null;
    }
    
    /**
     * Get SLA performance metrics
     */
    public function getSLAPerformanceMetrics($dateFrom = null, $dateTo = null, $division = null) {
        if (!$dateFrom) {
            $dateFrom = date('Y-m-01');
        }
        if (!$dateTo) {
            $dateTo = date('Y-m-t');
        }
        
        $conditions = ["c.created_at BETWEEN ? AND ?"];
        $params = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
        
        if ($division) {
            $conditions[] = "c.division = ?";
            $params[] = $division;
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT 
                    c.priority,
                    COUNT(*) as total_tickets,
                    SUM(CASE WHEN c.status = 'closed' THEN 1 ELSE 0 END) as closed_tickets,
                    SUM(CASE WHEN c.status = 'closed' AND c.closed_at <= c.sla_deadline THEN 1 ELSE 0 END) as met_sla,
                    SUM(CASE WHEN c.status = 'closed' AND c.closed_at > c.sla_deadline THEN 1 ELSE 0 END) as violated_sla,
                    SUM(CASE WHEN c.status != 'closed' AND NOW() > c.sla_deadline THEN 1 ELSE 0 END) as current_violations,
                    AVG(CASE WHEN c.status = 'closed' THEN TIMESTAMPDIFF(HOUR, c.created_at, c.closed_at) END) as avg_resolution_hours,
                    AVG(sla.resolution_hours) as sla_target_hours,
                    ROUND(
                        (SUM(CASE WHEN c.status = 'closed' AND c.closed_at <= c.sla_deadline THEN 1 ELSE 0 END) * 100.0) / 
                        NULLIF(SUM(CASE WHEN c.status = 'closed' THEN 1 ELSE 0 END), 0), 2
                    ) as sla_compliance_percentage
                FROM complaints c
                LEFT JOIN sla_definitions sla ON c.priority = sla.priority_level
                WHERE {$whereClause}
                GROUP BY c.priority
                ORDER BY 
                    CASE c.priority 
                        WHEN 'critical' THEN 1 
                        WHEN 'high' THEN 2 
                        WHEN 'medium' THEN 3 
                        ELSE 4 
                    END";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get SLA violation report
     */
    public function getSLAViolationReport($dateFrom = null, $dateTo = null, $division = null) {
        if (!$dateFrom) {
            $dateFrom = date('Y-m-01');
        }
        if (!$dateTo) {
            $dateTo = date('Y-m-t');
        }
        
        $conditions = ["c.created_at BETWEEN ? AND ?"];
        $params = [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'];
        
        if ($division) {
            $conditions[] = "c.division = ?";
            $params[] = $division;
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT 
                    c.complaint_id,
                    c.priority,
                    c.status,
                    c.created_at,
                    c.sla_deadline,
                    c.closed_at,
                    c.division,
                    cat.category,
                    cat.subtype,
                    cust.name as customer_name,
                    cust.company_name,
                    u.name as assigned_user_name,
                    CASE 
                        WHEN c.status = 'closed' THEN TIMESTAMPDIFF(HOUR, c.sla_deadline, c.closed_at)
                        ELSE TIMESTAMPDIFF(HOUR, c.sla_deadline, NOW())
                    END as hours_overdue,
                    TIMESTAMPDIFF(HOUR, c.created_at, COALESCE(c.closed_at, NOW())) as total_resolution_hours
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                LEFT JOIN users u ON c.assigned_to_user_id = u.id
                WHERE {$whereClause}
                  AND c.sla_deadline IS NOT NULL
                  AND (
                      (c.status = 'closed' AND c.closed_at > c.sla_deadline) OR
                      (c.status != 'closed' AND NOW() > c.sla_deadline)
                  )
                ORDER BY hours_overdue DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get tickets approaching SLA deadline
     */
    public function getTicketsApproachingSLA($hoursThreshold = 4) {
        $sql = "SELECT 
                    c.complaint_id,
                    c.priority,
                    c.status,
                    c.sla_deadline,
                    c.division,
                    cat.category,
                    cat.subtype,
                    cust.name as customer_name,
                    u.name as assigned_user_name,
                    u.email as assigned_user_email,
                    TIMESTAMPDIFF(HOUR, NOW(), c.sla_deadline) as hours_remaining
                FROM complaints c
                LEFT JOIN complaint_categories cat ON c.category_id = cat.category_id
                LEFT JOIN customers cust ON c.customer_id = cust.customer_id
                LEFT JOIN users u ON c.assigned_to_user_id = u.id
                WHERE c.status NOT IN ('closed')
                  AND c.sla_deadline IS NOT NULL
                  AND c.sla_deadline > NOW()
                  AND TIMESTAMPDIFF(HOUR, NOW(), c.sla_deadline) <= ?
                ORDER BY c.sla_deadline ASC";
        
        return $this->db->fetchAll($sql, [$hoursThreshold]);
    }
    
    /**
     * Update SLA definitions
     */
    public function updateSLADefinition($priority, $escalationHours, $resolutionHours, $description = null) {
        try {
            $sql = "UPDATE sla_definitions SET 
                    escalation_hours = ?,
                    resolution_hours = ?,
                    description = ?,
                    updated_at = NOW()
                    WHERE priority_level = ?";
            
            $this->db->query($sql, [$escalationHours, $resolutionHours, $description, $priority]);
            
            // Update existing tickets with new SLA
            $this->recalculateSLAForExistingTickets($priority);
            
            $this->logger->logSystem('sla_updated', "SLA definition updated for priority: {$priority}");
            
            return true;
            
        } catch (Exception $e) {
            error_log("SLA update error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate SLA compliance report
     */
    public function generateComplianceReport($dateFrom, $dateTo, $format = 'array') {
        $metrics = $this->getSLAPerformanceMetrics($dateFrom, $dateTo);
        $violations = $this->getSLAViolationReport($dateFrom, $dateTo);
        
        $report = [
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'generated_at' => date('Y-m-d H:i:s'),
            'summary' => [
                'total_tickets' => array_sum(array_column($metrics, 'total_tickets')),
                'closed_tickets' => array_sum(array_column($metrics, 'closed_tickets')),
                'met_sla' => array_sum(array_column($metrics, 'met_sla')),
                'violated_sla' => array_sum(array_column($metrics, 'violated_sla')),
                'current_violations' => array_sum(array_column($metrics, 'current_violations'))
            ],
            'by_priority' => $metrics,
            'violations' => $violations
        ];
        
        // Calculate overall compliance percentage
        $totalClosed = $report['summary']['closed_tickets'];
        $totalMet = $report['summary']['met_sla'];
        $report['summary']['overall_compliance'] = $totalClosed > 0 ? round(($totalMet / $totalClosed) * 100, 2) : 0;
        
        if ($format === 'json') {
            return json_encode($report, JSON_PRETTY_PRINT);
        } elseif ($format === 'csv') {
            return $this->exportReportToCSV($report);
        }
        
        return $report;
    }
    
    // Private helper methods
    
    private function markSLAViolation($complaintId) {
        // Could add a violations tracking table or flag
        $this->logger->logSecurity('sla_violation', "SLA violation recorded for ticket {$complaintId}");
    }
    
    private function sendSLAViolationNotifications($ticket) {
        $recipients = [];
        
        // Send to customer
        $recipients[] = [
            'customer_id' => $ticket['customer_id'],
            'email' => $ticket['customer_email'],
            'name' => $ticket['customer_name']
        ];
        
        // Send to assigned user
        if ($ticket['assigned_user_email']) {
            $recipients[] = [
                'user_id' => $ticket['assigned_to_user_id'],
                'email' => $ticket['assigned_user_email'],
                'name' => $ticket['assigned_user_name']
            ];
        }
        
        // Send to management
        $management = $this->getManagementEmails($ticket['division']);
        $recipients = array_merge($recipients, $management);
        
        $data = [
            'complaint_id' => $ticket['complaint_id'],
            'customer_name' => $ticket['customer_name'],
            'priority' => $ticket['priority'],
            'hours_overdue' => $ticket['hours_overdue'],
            'division' => $ticket['division']
        ];
        
        $this->notificationService->send('sla_violation', $recipients, $data);
    }
    
    private function shouldAutoEscalateOnViolation($ticket) {
        // Business rules for auto-escalation
        return in_array($ticket['priority'], ['high', 'critical']);
    }
    
    private function autoEscalateViolatedTicket($ticket) {
        // Find higher-level user for escalation
        $escalationTarget = $this->findEscalationTarget($ticket);
        
        if ($escalationTarget) {
            $sql = "UPDATE complaints SET 
                    assigned_to_user_id = ?,
                    escalated_at = NOW()
                    WHERE complaint_id = ?";
            
            $this->db->query($sql, [$escalationTarget['id'], $ticket['complaint_id']]);
        }
    }
    
    private function findEscalationTarget($ticket) {
        // Find nodal controller or admin in same division
        $sql = "SELECT id, name, email FROM users 
                WHERE division = ? 
                  AND role IN ('controller_nodal', 'admin')
                  AND status = 'active'
                ORDER BY 
                    CASE role 
                        WHEN 'admin' THEN 1 
                        WHEN 'controller_nodal' THEN 2 
                        ELSE 3 
                    END
                LIMIT 1";
        
        return $this->db->fetch($sql, [$ticket['division']]);
    }
    
    private function sendEscalationNotifications($ticket, $escalationTarget) {
        // Send notifications about escalation
    }
    
    private function getSLAWarningThreshold($priority) {
        $thresholds = [
            'critical' => 1, // 1 hour before deadline
            'high' => 2,     // 2 hours before deadline
            'medium' => 4,   // 4 hours before deadline
            'normal' => 8    // 8 hours before deadline
        ];
        
        return $thresholds[$priority] ?? 4;
    }
    
    private function isWarningAlreadySent($complaintId) {
        // Check if warning notification was already sent
        $sql = "SELECT id FROM notifications 
                WHERE complaint_id = ? 
                  AND type = 'warning' 
                  AND title LIKE '%SLA warning%'";
        
        $result = $this->db->fetch($sql, [$complaintId]);
        return !empty($result);
    }
    
    private function sendSLAWarningNotification($ticket, $hoursToDeadline) {
        $recipients = [];
        
        // Send to assigned user
        if ($ticket['assigned_user_email']) {
            $recipients[] = [
                'user_id' => $ticket['assigned_to_user_id'],
                'email' => $ticket['assigned_user_email'],
                'name' => $ticket['assigned_user_name']
            ];
        }
        
        $data = [
            'complaint_id' => $ticket['complaint_id'],
            'customer_name' => $ticket['customer_name'],
            'priority' => $ticket['priority'],
            'hours_remaining' => round($hoursToDeadline, 1),
            'sla_deadline' => $ticket['sla_deadline']
        ];
        
        $this->notificationService->send('sla_warning', $recipients, $data);
    }
    
    private function markWarningAsSent($complaintId) {
        // Create notification record to track warning was sent
        $sql = "INSERT INTO notifications (complaint_id, title, message, type, created_at) 
                VALUES (?, 'SLA Warning Sent', 'SLA deadline warning notification sent', 'warning', NOW())";
        
        $this->db->query($sql, [$complaintId]);
    }
    
    private function recalculateSLAForExistingTickets($priority) {
        $sql = "SELECT complaint_id, created_at FROM complaints 
                WHERE priority = ? AND status NOT IN ('closed')";
        
        $tickets = $this->db->fetchAll($sql, [$priority]);
        
        foreach ($tickets as $ticket) {
            $slaInfo = $this->calculateSLADeadline($priority, $ticket['created_at']);
            if ($slaInfo) {
                $this->updateSLADeadline($ticket['complaint_id'], $priority);
            }
        }
    }
    
    private function getManagementEmails($division) {
        $sql = "SELECT id as user_id, name, email FROM users 
                WHERE division = ? 
                  AND role IN ('admin', 'superadmin')
                  AND status = 'active'";
        
        return $this->db->fetchAll($sql, [$division]);
    }
    
    private function exportReportToCSV($report) {
        $csvData = [];
        
        // Add summary
        $csvData[] = ['SLA Compliance Report'];
        $csvData[] = ['Period', $report['period']['from'] . ' to ' . $report['period']['to']];
        $csvData[] = ['Generated', $report['generated_at']];
        $csvData[] = [];
        
        // Add summary statistics
        $csvData[] = ['Summary Statistics'];
        foreach ($report['summary'] as $key => $value) {
            $csvData[] = [ucwords(str_replace('_', ' ', $key)), $value];
        }
        $csvData[] = [];
        
        // Add priority breakdown
        $csvData[] = ['Priority Breakdown'];
        $csvData[] = ['Priority', 'Total Tickets', 'Closed', 'Met SLA', 'Violated SLA', 'Compliance %'];
        foreach ($report['by_priority'] as $priority) {
            $csvData[] = [
                $priority['priority'],
                $priority['total_tickets'],
                $priority['closed_tickets'],
                $priority['met_sla'],
                $priority['violated_sla'],
                $priority['sla_compliance_percentage']
            ];
        }
        
        // Convert to CSV string
        $output = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}
