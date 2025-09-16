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
    public function getUserNotifications($userId, $userType, $limit = 20, $unreadOnly = false) {
        try {
            // Check if enhanced columns exist
            $hasEnhancedColumns = $this->checkEnhancedColumns();

            if ($hasEnhancedColumns) {
                // Use enhanced query with new columns
                $sql = "SELECT * FROM {$this->table}
                        WHERE ";

                if ($userType === 'customer') {
                    $sql .= "customer_id = ?";
                } else {
                    $sql .= "(user_id = ? OR user_type = ?)";
                }

                $sql .= " AND (expires_at IS NULL OR expires_at > NOW())";

                $params = $userType === 'customer' ? [$userId] : [$userId, $userType];
            } else {
                // Use basic query for backward compatibility
                $whereClause = $userType === 'customer' ? 'customer_id = ?' : 'user_id = ?';
                $sql = "SELECT * FROM {$this->table} WHERE {$whereClause}";
                $params = [$userId];
            }

            if ($unreadOnly) {
                $sql .= " AND is_read = 0";
            }

            $sql .= " ORDER BY created_at DESC LIMIT ?";
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
    public function markAsRead($notificationId, $userId = null) {
        $data = [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s')
        ];
        
        if ($userId) {
            // Verify the notification belongs to the user
            $notification = $this->findBy(['id' => $notificationId, 'user_id' => $userId]);
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
        $sql = "UPDATE {$this->table} 
                SET is_read = 1, read_at = NOW(), updated_at = NOW()
                WHERE user_id = ? AND user_type = ? AND is_read = 0";
        
        return $this->db->query($sql, [$userId, $userType]);
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