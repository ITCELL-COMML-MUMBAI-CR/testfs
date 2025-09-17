<?php
/**
 * Background Priority Service
 * Handles automatic priority escalation without user interaction
 */

require_once __DIR__ . '/OnSiteNotificationService.php';

require_once __DIR__ . '/OnSiteNotificationService.php';

class BackgroundPriorityService {
    private $db;
    private $notificationService;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->notificationService = new NotificationService();
    }
    
    /**
     * Process all tickets for priority escalation
     * This method is called via AJAX or scheduled tasks
     */
    public function processAllTickets() {
        try {
            $escalatedTickets = [];
            
            // Get all tickets that are eligible for priority escalation
            $sql = "SELECT complaint_id, priority, created_at, status, division, zone, assigned_to_department,
                           escalation_stopped, customer_id
                    FROM complaints 
                    WHERE status IN ('pending', 'awaiting_approval') 
                    AND escalation_stopped = 0
                    AND priority != 'critical'
                    ORDER BY created_at ASC";
            
            $tickets = $this->db->fetchAll($sql);
            
            foreach ($tickets as $ticket) {
                $escalated = $this->checkAndEscalateTicket($ticket);
                if ($escalated) {
                    $escalatedTickets[] = $escalated;
                }
            }
            
            return [
                'success' => true,
                'processed_tickets' => count($tickets),
                'escalated_tickets' => count($escalatedTickets),
                'escalations' => $escalatedTickets,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            error_log("Background Priority Service Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * Check and escalate a single ticket if needed
     */
    private function checkAndEscalateTicket($ticket) {
        $currentPriority = $ticket['priority'];
        $createdAt = new DateTime($ticket['created_at']);
        $now = new DateTime();
        $hoursElapsed = $createdAt->diff($now)->h + ($createdAt->diff($now)->days * 24);
        
        // Determine priority based on total hours elapsed
        $newPriority = $this->determinePriorityByAge($hoursElapsed);
        
        // Only escalate if priority has changed and new priority is higher
        if ($newPriority !== $currentPriority && $this->isPriorityHigher($newPriority, $currentPriority)) {
            try {
                $this->db->beginTransaction();
                
                // Update ticket priority
                $this->db->query(
                    "UPDATE complaints SET priority = ?, updated_at = NOW() WHERE complaint_id = ?",
                    [$newPriority, $ticket['complaint_id']]
                );
                
                // Create transaction record
                $remarks = "Priority automatically escalated from {$currentPriority} to {$newPriority} after {$hoursElapsed} hours";
                $this->createSystemTransaction($ticket['complaint_id'], 'priority_escalated', $remarks, 'priority_escalation');
                
                // Send priority escalation notification using enhanced NotificationService
                $this->sendPriorityEscalationNotification($ticket['complaint_id'], $ticket, $newPriority, $currentPriority, $hoursElapsed);
                
                $this->db->commit();
                
                return [
                    'ticket_id' => $ticket['complaint_id'],
                    'old_priority' => $currentPriority,
                    'new_priority' => $newPriority,
                    'hours_elapsed' => $hoursElapsed
                ];
                
            } catch (Exception $e) {
                $this->db->rollback();
                error_log("Priority escalation error for ticket {$ticket['complaint_id']}: " . $e->getMessage());
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Determine priority based on hours elapsed since ticket creation
     */
    private function determinePriorityByAge($hoursElapsed) {
        if ($hoursElapsed >= 24) {
            return 'critical';
        } elseif ($hoursElapsed >= 12) {
            return 'high';
        } elseif ($hoursElapsed >= 4) {
            return 'medium';
        } else {
            return 'normal';
        }
    }
    
    /**
     * Check if new priority is higher than current priority
     */
    private function isPriorityHigher($newPriority, $currentPriority) {
        $priorityLevels = [
            'normal' => 1,
            'medium' => 2,
            'high' => 3,
            'critical' => 4
        ];
        
        return ($priorityLevels[$newPriority] ?? 0) > ($priorityLevels[$currentPriority] ?? 0);
    }
    
    /**
     * Stop escalation for specific ticket statuses
     */
    public function stopEscalationForStatus($complaintId, $status) {
        $stopStatuses = ['awaiting_info', 'awaiting_feedback'];
        
        if (in_array($status, $stopStatuses)) {
            $this->db->query(
                "UPDATE complaints SET escalation_stopped = 1 WHERE complaint_id = ?",
                [$complaintId]
            );
        }
    }
    
    /**
     * Reset escalation when ticket is forwarded outside division
     */
    public function resetEscalationForCrossDivisionForward($complaintId, $oldDivision, $newDivision) {
        if ($oldDivision !== $newDivision) {
            $this->db->query(
                "UPDATE complaints SET 
                 priority = 'normal',
                 escalation_stopped = 0,
                 updated_at = NOW()
                 WHERE complaint_id = ?",
                [$complaintId]
            );
            
            // Create transaction record
            $remarks = "Priority reset to normal due to cross-division forwarding from {$oldDivision} to {$newDivision}";
            $this->createSystemTransaction($complaintId, 'priority_reset', $remarks, 'system_remarks');
        }
    }
    
    /**
     * Resume escalation when ticket status changes back to pending
     */
    public function resumeEscalation($complaintId, $newStatus) {
        if ($newStatus === 'pending') {
            $this->db->query(
                "UPDATE complaints SET escalation_stopped = 0 WHERE complaint_id = ?",
                [$complaintId]
            );
        }
    }
    
    /**
     * Create system-generated transaction
     */
    private function createSystemTransaction($complaintId, $type, $remarks, $remarksType = 'system_remarks') {
        $sql = "INSERT INTO transactions (
            complaint_id, transaction_type, remarks, remarks_type,
            created_by_type, created_by_role, created_at
        ) VALUES (?, ?, ?, ?, 'user', 'system', NOW())";
        
        $this->db->query($sql, [$complaintId, $type, $remarks, $remarksType]);
    }
    
    /**
     * Send priority escalation notification using enhanced service
     */
    private function sendPriorityEscalationNotification($ticketId, $ticket, $newPriority, $oldPriority, $hoursElapsed) {
        $customer = [
            'name' => $ticket['customer_name'] ?? 'Unknown Customer',
            'customer_id' => $ticket['customer_id'] ?? null
        ];

        $escalationReason = "Automatic escalation after {$hoursElapsed} hours";

        $result = $this->notificationService->sendPriorityEscalated(
            $ticketId,
            $customer,
            $newPriority,
            $oldPriority,
            $escalationReason
        );

        // Log the notification attempt
        if (!$result['success']) {
            error_log("Failed to send priority escalation notification for ticket {$ticketId}: " . ($result['error'] ?? 'Unknown error'));
        }

        // Send on-site notification
        $onSiteNotificationService = new OnSiteNotificationService();
        $onSiteNotificationService->notifyUsersOfPriorityEscalation($ticketId);

        return $result;
    }
    
    /**
     * Get escalation statistics
     */
    public function getEscalationStats() {
        $stats = [];
        
        // Count tickets by priority
        $priorityStats = $this->db->fetchAll(
            "SELECT priority, COUNT(*) as count 
             FROM complaints 
             WHERE status IN ('pending', 'awaiting_approval') 
             GROUP BY priority"
        );
        
        foreach ($priorityStats as $stat) {
            $stats['by_priority'][$stat['priority']] = $stat['count'];
        }
        
        // Count escalation-stopped tickets
        $stoppedCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM complaints 
             WHERE escalation_stopped = 1 AND status != 'closed'"
        );
        
        $stats['escalation_stopped'] = $stoppedCount['count'];
        
        // Get recent escalations (last 24 hours)
        $recentEscalations = $this->db->fetchAll(
            "SELECT COUNT(*) as count FROM transactions 
             WHERE transaction_type = 'priority_escalated' 
             AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        
        $stats['recent_escalations'] = $recentEscalations[0]['count'] ?? 0;
        
        return $stats;
    }
}
?>