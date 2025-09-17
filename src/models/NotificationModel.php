<?php
/**
 * NotificationModel - Manages system notifications for SAMPARK
 * Handles user notifications, announcements, and messaging system
 */

require_once 'BaseModel.php';

class NotificationModel extends BaseModel {
    
    protected $table = 'notifications';
    protected $fillable = [
        'user_id',
        'customer_id',
        'user_type',
        'title',
        'message',
        'type',
        'priority',
        'related_id',
        'related_type',
        'complaint_id',
        'is_read',
        'action_url',
        'read_at',
        'expires_at',
        'metadata',
        'dismissed_at'
    ];
    
    // Notification types
    const TYPE_TICKET_CREATED = 'ticket_created';
    const TYPE_TICKET_UPDATED = 'ticket_updated'; 
    const TYPE_TICKET_ASSIGNED = 'ticket_assigned';
    const TYPE_TICKET_REPLIED = 'ticket_replied';
    const TYPE_TICKET_RESOLVED = 'ticket_resolved';
    const TYPE_TICKET_ESCALATED = 'ticket_escalated';
    const TYPE_SYSTEM_ANNOUNCEMENT = 'system_announcement';
    const TYPE_MAINTENANCE_ALERT = 'maintenance_alert';
    const TYPE_SLA_WARNING = 'sla_warning';
    const TYPE_ACCOUNT_UPDATE = 'account_update';
    
    // Priority levels
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    
    /**
     * Get notifications for a specific user
     */
    public function getUserNotifications($userId, $userType, $limit = 20, $unreadOnly = false, $division = null) {
        try {
            $sql = "SELECT n.*, c.complaint_id, c.division, c.assigned_to_department, c.customer_id as ticket_customer_id
                    FROM {$this->table} n
                    LEFT JOIN complaints c ON n.complaint_id = c.complaint_id
                    WHERE ";

            $conditions = [];
            $params = [];

            if ($userType === 'customer') {
                // Customers see notifications for their own tickets
                $conditions[] = "n.customer_id = ?";
                $params[] = $userId;
            } elseif ($userType === 'controller') {
                // Controllers see notifications assigned to them or their department
                $conditions[] = "n.user_id = ?";
                $params[] = $userId;
            } elseif ($userType === 'controller_nodal' && $division) {
                // Controller nodals see notifications for tickets in their division
                $conditions[] = "(c.division = ? AND n.user_type = 'controller_nodal' AND n.user_id = ?)";
                $params[] = $division;
                $params[] = $userId;
            } elseif (in_array($userType, ['admin', 'superadmin'])) {
                // Admins see notifications assigned to them
                $conditions[] = "n.user_id = ?";
                $params[] = $userId;
            }

            // Add expiration check
            $conditions[] = "(n.expires_at IS NULL OR n.expires_at > NOW())";

            $sql .= implode(' AND ', $conditions);

            if ($unreadOnly) {
                $sql .= " AND n.is_read = 0";
            }

            $sql .= " ORDER BY n.created_at DESC LIMIT ?";
            $params[] = $limit;

            return $this->db->fetchAll($sql, $params);

        } catch (Exception $e) {
            error_log("Error getting user notifications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get unread notification count for user
     */
    public function getUnreadCount($userId, $userType) {
        return $this->count([
            'user_id' => $userId,
            'user_type' => $userType,
            'is_read' => 0
        ]);
    }
    
    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId = null, $userType = null) {
        $data = [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($userId && $userType) {
            // Verify the notification belongs to the user
            if ($userType === 'customer') {
                $notification = $this->findBy(['id' => $notificationId, 'customer_id' => $userId]);
            } else {
                $notification = $this->findBy(['id' => $notificationId, 'user_id' => $userId]);
            }

            if (!$notification) {
                return false;
            }
        }

        return $this->update($notificationId, $data);
    }
    
    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId, $userType) {
        if ($userType === 'customer') {
            $sql = "UPDATE {$this->table}
                    SET is_read = 1, read_at = NOW(), updated_at = NOW()
                    WHERE customer_id = ? AND is_read = 0";
            return $this->db->query($sql, [$userId]);
        } else {
            $sql = "UPDATE {$this->table}
                    SET is_read = 1, read_at = NOW(), updated_at = NOW()
                    WHERE user_id = ? AND is_read = 0";
            return $this->db->query($sql, [$userId]);
        }
    }
    
    /**
     * Create notification
     */
    public function createNotification($data) {
        // Set defaults
        $data['is_read'] = 0;
        $data['priority'] = $data['priority'] ?? self::PRIORITY_MEDIUM;
        $data['type'] = $data['type'] ?? self::TYPE_SYSTEM_ANNOUNCEMENT;

        // Encode metadata if it's an array
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $data['metadata'] = json_encode($data['metadata']);
        }

        $result = $this->create($data);
        return $result ? $result['id'] : false;
    }
    
    /**
     * Create ticket-related notification
     */
    public function createTicketNotification($ticketId, $userId, $userType, $type, $title, $message, $priority = self::PRIORITY_MEDIUM) {
        return $this->createNotification([
            'user_id' => $userId,
            'user_type' => $userType,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'priority' => $priority,
            'related_id' => $ticketId,
            'related_type' => 'ticket'
        ]);
    }
    
    /**
     * Create system announcement
     */
    public function createSystemAnnouncement($title, $message, $userType = null, $expiresAt = null, $priority = self::PRIORITY_MEDIUM) {
        // If no specific user type, create for all user types
        $userTypes = $userType ? [$userType] : ['customer', 'controller', 'controller_nodal', 'admin', 'superadmin'];
        
        $notifications = [];
        
        foreach ($userTypes as $type) {
            // Get all users of this type
            $users = $this->getUsersByType($type);
            
            foreach ($users as $user) {
                $notification = $this->createNotification([
                    'user_id' => $user['id'],
                    'user_type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'type' => self::TYPE_SYSTEM_ANNOUNCEMENT,
                    'priority' => $priority,
                    'expires_at' => $expiresAt
                ]);
                
                if ($notification) {
                    $notifications[] = $notification;
                }
            }
        }
        
        return $notifications;
    }
    
    /**
     * Create SLA warning notification
     */
    public function createSLAWarning($ticketId, $userId, $userType, $hoursRemaining) {
        $title = "SLA Warning - Ticket #{$ticketId}";
        $message = "Ticket #{$ticketId} has {$hoursRemaining} hours remaining before SLA breach.";
        
        return $this->createTicketNotification(
            $ticketId,
            $userId,
            $userType,
            self::TYPE_SLA_WARNING,
            $title,
            $message,
            self::PRIORITY_HIGH
        );
    }
    
    /**
     * Get notifications by type
     */
    public function getNotificationsByType($type, $limit = 50) {
        return $this->findAll(['type' => $type], 'created_at DESC', $limit);
    }
    
    /**
     * Get notifications by priority
     */
    public function getNotificationsByPriority($priority, $limit = 50) {
        return $this->findAll(['priority' => $priority], 'created_at DESC', $limit);
    }
    
    /**
     * Get recent system announcements
     */
    public function getRecentAnnouncements($userType = null, $limit = 10) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE type = ? 
                AND (expires_at IS NULL OR expires_at > NOW())";
        
        $params = [self::TYPE_SYSTEM_ANNOUNCEMENT];
        
        if ($userType) {
            $sql .= " AND user_type = ?";
            $params[] = $userType;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Delete old notifications
     */
    public function deleteOldNotifications($daysOld = 30) {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysOld} days"));
        
        $sql = "DELETE FROM {$this->table} 
                WHERE created_at < ? 
                AND (expires_at IS NULL OR expires_at < NOW())
                AND is_read = 1";
        
        return $this->db->query($sql, [$cutoffDate]);
    }
    
    /**
     * Delete expired notifications
     */
    public function deleteExpiredNotifications() {
        $sql = "DELETE FROM {$this->table} WHERE expires_at IS NOT NULL AND expires_at < NOW()";
        return $this->db->query($sql);
    }
    
    /**
     * Get notification statistics
     */
    public function getNotificationStats($userId = null, $userType = null, $days = 7) {
        $sql = "SELECT 
                    type,
                    COUNT(*) as total_count,
                    COUNT(CASE WHEN is_read = 1 THEN 1 END) as read_count,
                    COUNT(CASE WHEN is_read = 0 THEN 1 END) as unread_count,
                    priority
                FROM {$this->table} 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $params = [$days];
        
        if ($userId && $userType) {
            $sql .= " AND user_id = ? AND user_type = ?";
            $params[] = $userId;
            $params[] = $userType;
        } elseif ($userType) {
            $sql .= " AND user_type = ?";
            $params[] = $userType;
        }
        
        $sql .= " GROUP BY type, priority ORDER BY total_count DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get notification delivery report
     */
    public function getDeliveryReport($dateFrom, $dateTo) {
        $sql = "SELECT 
                    DATE(created_at) as notification_date,
                    type,
                    user_type,
                    COUNT(*) as sent_count,
                    COUNT(CASE WHEN is_read = 1 THEN 1 END) as read_count,
                    ROUND(COUNT(CASE WHEN is_read = 1 THEN 1 END) * 100.0 / COUNT(*), 2) as read_percentage
                FROM {$this->table} 
                WHERE created_at BETWEEN ? AND ?
                GROUP BY DATE(created_at), type, user_type
                ORDER BY notification_date DESC, type, user_type";
        
        return $this->db->fetchAll($sql, [$dateFrom, $dateTo]);
    }
    
    /**
     * Create maintenance alert
     */
    public function createMaintenanceAlert($title, $message, $scheduledAt, $userTypes = ['admin', 'superadmin']) {
        $notifications = [];
        
        foreach ($userTypes as $userType) {
            $users = $this->getUsersByType($userType);
            
            foreach ($users as $user) {
                $notification = $this->createNotification([
                    'user_id' => $user['id'],
                    'user_type' => $userType,
                    'title' => $title,
                    'message' => $message,
                    'type' => self::TYPE_MAINTENANCE_ALERT,
                    'priority' => self::PRIORITY_HIGH,
                    'metadata' => json_encode(['scheduled_at' => $scheduledAt])
                ]);
                
                if ($notification) {
                    $notifications[] = $notification;
                }
            }
        }
        
        return $notifications;
    }
    
    /**
     * Get users by type (helper method)
     */
    private function getUsersByType($userType) {
        switch ($userType) {
            case 'customer':
                $sql = "SELECT customer_id as id FROM customers WHERE status = 'active'";
                return $this->db->fetchAll($sql);
            case 'controller':
            case 'controller_nodal':
            case 'admin':
            case 'superadmin':
                $sql = "SELECT id FROM users WHERE role = ? AND status = 'active'";
                return $this->db->fetchAll($sql, [$userType]);
            default:
                return [];
        }
    }
    
    /**
     * Bulk create notifications
     */
    public function bulkCreateNotifications($notifications) {
        if (empty($notifications)) {
            return false;
        }
        
        $this->beginTransaction();
        
        try {
            $results = [];
            foreach ($notifications as $notificationData) {
                $result = $this->createNotification($notificationData);
                if ($result) {
                    $results[] = $result;
                } else {
                    throw new Exception("Failed to create notification");
                }
            }
            
            $this->commit();
            return $results;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }
    
    /**
     * Get notification with metadata decoded
     */
    public function getNotificationWithMetadata($id) {
        $notification = $this->find($id);
        
        if ($notification && !empty($notification['metadata'])) {
            $decoded = json_decode($notification['metadata'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $notification['metadata'] = $decoded;
            }
        }
        
        return $notification;
    }

    /**
     * Create department-specific notifications following ticket assignment logic
     */
    public function createDepartmentNotification($complaintId, $title, $message, $type = 'ticket_created', $priority = 'medium') {
        // Get ticket details to determine who should receive notifications
        $sql = "SELECT complaint_id, customer_id, division, department, assigned_to_department, status
                FROM complaints WHERE complaint_id = ?";
        $ticket = $this->db->fetch($sql, [$complaintId]);

        if (!$ticket) {
            return false;
        }

        $notifications = [];

        // 1. Customer notification (always for their own tickets)
        if ($ticket['customer_id']) {
            $notifications[] = [
                'customer_id' => $ticket['customer_id'],
                'user_id' => null,
                'user_type' => 'customer',
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'priority' => $priority,
                'complaint_id' => $complaintId,
                'related_id' => $complaintId,
                'related_type' => 'ticket',
                'is_read' => 0
            ];
        }

        // 2. Controller Nodal notifications (for division-based notifications)
        if ($ticket['division']) {
            // Get all controller nodals for this division
            $divisionUsers = $this->db->fetchAll(
                "SELECT id FROM users WHERE role = 'controller_nodal' AND division = ? AND status = 'active'",
                [$ticket['division']]
            );

            foreach ($divisionUsers as $user) {
                $notifications[] = [
                    'customer_id' => null,
                    'user_id' => $user['id'],
                    'user_type' => 'controller_nodal',
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'priority' => $priority,
                    'complaint_id' => $complaintId,
                    'related_id' => $complaintId,
                    'related_type' => 'ticket',
                    'is_read' => 0
                ];
            }
        }

        // 3. Department-based controller notifications
        if ($ticket['assigned_to_department']) {
            // Get all controllers for the assigned department
            $departmentUsers = $this->db->fetchAll(
                "SELECT id FROM users WHERE role = 'controller' AND department = ? AND status = 'active'",
                [$ticket['assigned_to_department']]
            );

            foreach ($departmentUsers as $user) {
                $notifications[] = [
                    'customer_id' => null,
                    'user_id' => $user['id'],
                    'user_type' => 'controller',
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'priority' => $priority,
                    'complaint_id' => $complaintId,
                    'related_id' => $complaintId,
                    'related_type' => 'ticket',
                    'is_read' => 0
                ];
            }
        }

        // 4. Admin notifications (for high priority or critical tickets)
        if (in_array($priority, ['high', 'critical'])) {
            $adminUsers = $this->db->fetchAll(
                "SELECT id FROM users WHERE role IN ('admin', 'superadmin') AND status = 'active'"
            );

            foreach ($adminUsers as $user) {
                $notifications[] = [
                    'customer_id' => null,
                    'user_id' => $user['id'],
                    'user_type' => 'admin',
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'priority' => $priority,
                    'complaint_id' => $complaintId,
                    'related_id' => $complaintId,
                    'related_type' => 'ticket',
                    'is_read' => 0
                ];
            }
        }

        // Insert all notifications
        $success = true;
        foreach ($notifications as $notificationData) {
            if (!$this->create($notificationData)) {
                $success = false;
                error_log("Failed to create notification: " . json_encode($notificationData));
            }
        }

        return $success;
    }

    /**
     * Check if enhanced notification columns exist
     */
    private function checkEnhancedColumns() {
        try {
            // Try to query for the priority column - if it exists, we have enhanced columns
            $result = $this->db->fetch("SHOW COLUMNS FROM {$this->table} LIKE 'priority'");
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }
}