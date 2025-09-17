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
     * Get notifications for a specific user - Department-based approach
     */
    public function getUserNotifications($userId, $userRole, $limit = 20, $unreadOnly = false, $division = null) {
        try {
            // Get user's department information
            $user = null;
            if ($userRole !== 'customer') {
                $user = $this->db->fetch("SELECT division, department FROM users WHERE id = ?", [$userId]);
            }

            $sql = "SELECT n.*, c.complaint_id, c.division, c.assigned_to_department, c.customer_id as ticket_customer_id
                    FROM {$this->table} n
                    LEFT JOIN complaints c ON n.complaint_id = c.complaint_id
                    WHERE ";

            $conditions = [];
            $params = [];

            if ($userRole === 'customer') {
                // Customers see notifications for their own tickets AND broadcast notifications for customers
                $conditions[] = "(n.customer_id = ? OR (n.user_id IS NULL AND n.customer_id IS NULL AND (n.user_type IS NULL OR n.user_type = '' OR n.user_type = 'customer')))";
                $params[] = $userId;
            } else {
                // Build complex condition for department-based notifications
                $userConditions = [];

                // 1. Individual notifications for this user
                $userConditions[] = "n.user_id = " . $this->db->quote($userId);

                // 2. Role-based broadcast notifications
                $userConditions[] = "(n.user_id IS NULL AND n.customer_id IS NULL AND (n.user_type IS NULL OR n.user_type = '' OR n.user_type = " . $this->db->quote($userRole) . "))";

                // 3. Department-based notifications for controller
                if ($userRole === 'controller' && $user && $user['department']) {
                    $userConditions[] = "(n.user_id IS NULL AND n.user_type = 'controller' AND JSON_EXTRACT(n.metadata, '$.target_department') = " . $this->db->quote($user['department']) . ")";
                }

                // 4. Division-based notifications for controller_nodal
                if ($userRole === 'controller_nodal' && $user && $user['division']) {
                    $userConditions[] = "(n.user_id IS NULL AND n.user_type = 'controller_nodal' AND JSON_EXTRACT(n.metadata, '$.target_division') = " . $this->db->quote($user['division']) . ")";
                }

                // 5. Admin role-based notifications
                if (in_array($userRole, ['admin', 'superadmin'])) {
                    $userConditions[] = "(n.user_id IS NULL AND n.user_type = 'admin' AND JSON_EXTRACT(n.metadata, '$.notification_scope') = 'role')";
                }

                $conditions[] = "(" . implode(' OR ', $userConditions) . ")";
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
     * Mark notification as read - Department-based approach
     */
    public function markAsRead($notificationId, $userId = null, $userType = null) {
        if (!$userId || !$userType) {
            return false;
        }

        // Get the notification to check ownership and type
        $notification = $this->find($notificationId);
        if (!$notification) {
            return false;
        }

        // For customer notifications, verify ownership
        if ($userType === 'customer') {
            if ($notification['customer_id'] != $userId && $notification['customer_id'] !== null) {
                return false;
            }
            // Customer notifications are always individual, mark as read
            return $this->update($notificationId, [
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        // For ticket-related notifications, use department-based approach
        if ($notification['complaint_id']) {
            return $this->markTicketNotificationAsReadByDepartment($notificationId, $userId, $userType);
        }

        // For individual user notifications, verify ownership
        if ($notification['user_id'] !== null) {
            if ($notification['user_id'] != $userId) {
                return false;
            }
        }

        // Mark notification as read
        return $this->update($notificationId, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mark ticket notification as read by department - once read by any department member, it's hidden for all
     */
    private function markTicketNotificationAsReadByDepartment($notificationId, $userId, $userType) {
        // Get user's department information
        $user = $this->db->fetch("SELECT division, department FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            return false;
        }

        // Get the notification with ticket details
        $notification = $this->db->fetch(
            "SELECT n.*, c.division, c.assigned_to_department
             FROM {$this->table} n
             LEFT JOIN complaints c ON n.complaint_id = c.complaint_id
             WHERE n.id = ?",
            [$notificationId]
        );

        if (!$notification) {
            return false;
        }

        // Check if user has authority to mark this notification as read
        $canMarkAsRead = false;

        // Controller nodal can mark read for their division
        if ($userType === 'controller_nodal' && $user['division'] === $notification['division']) {
            $canMarkAsRead = true;
        }

        // Controller can mark read for their department
        if ($userType === 'controller' && $user['department'] === $notification['assigned_to_department']) {
            $canMarkAsRead = true;
        }

        // Admin/superadmin can mark any notification as read
        if (in_array($userType, ['admin', 'superadmin'])) {
            $canMarkAsRead = true;
        }

        if (!$canMarkAsRead) {
            return false;
        }

        // Mark as read and track who marked it
        $metadata = $notification['metadata'] ? json_decode($notification['metadata'], true) : [];
        $metadata['marked_read_by'] = [
            'user_id' => $userId,
            'user_type' => $userType,
            'division' => $user['division'],
            'department' => $user['department'],
            'read_at' => date('Y-m-d H:i:s')
        ];

        return $this->update($notificationId, [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'metadata' => json_encode($metadata)
        ]);
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
     * Create system announcement - creates a single broadcast notification instead of individual ones
     */
    public function createSystemAnnouncement($title, $message, $userType = null, $expiresAt = null, $priority = self::PRIORITY_MEDIUM) {
        // Create a single broadcast notification that can be displayed to all users of the specified type(s)
        $notification = $this->createNotification([
            'user_id' => null, // Null means it's a broadcast notification
            'customer_id' => null,
            'user_type' => $userType, // If null, it's for all user types
            'title' => $title,
            'message' => $message,
            'type' => self::TYPE_SYSTEM_ANNOUNCEMENT,
            'priority' => $priority,
            'expires_at' => $expiresAt,
            'metadata' => json_encode([
                'is_broadcast' => true,
                'created_by' => $_SESSION['user_id'] ?? null,
                'target_user_types' => $userType ? [$userType] : ['customer', 'controller', 'controller_nodal', 'admin', 'superadmin']
            ])
        ]);

        return $notification ? [$notification] : [];
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
     * Creates department-based notifications instead of individual user notifications
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

        // 1. Customer notification (always individual for their own tickets)
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

        // 2. Controller Nodal notification (ONE notification for the division)
        if ($ticket['division']) {
            $notifications[] = [
                'customer_id' => null,
                'user_id' => null, // NULL means it's a department-based notification
                'user_type' => 'controller_nodal',
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'priority' => $priority,
                'complaint_id' => $complaintId,
                'related_id' => $complaintId,
                'related_type' => 'ticket',
                'is_read' => 0,
                'metadata' => json_encode([
                    'target_division' => $ticket['division'],
                    'notification_scope' => 'division'
                ])
            ];
        }

        // 3. Department notification (ONE notification for the assigned department)
        if ($ticket['assigned_to_department']) {
            $notifications[] = [
                'customer_id' => null,
                'user_id' => null, // NULL means it's a department-based notification
                'user_type' => 'controller',
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'priority' => $priority,
                'complaint_id' => $complaintId,
                'related_id' => $complaintId,
                'related_type' => 'ticket',
                'is_read' => 0,
                'metadata' => json_encode([
                    'target_department' => $ticket['assigned_to_department'],
                    'notification_scope' => 'department'
                ])
            ];
        }

        // 4. Admin notification (ONE notification for admins on high priority tickets)
        if (in_array($priority, ['high', 'critical'])) {
            $notifications[] = [
                'customer_id' => null,
                'user_id' => null, // NULL means it's a role-based notification
                'user_type' => 'admin',
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'priority' => $priority,
                'complaint_id' => $complaintId,
                'related_id' => $complaintId,
                'related_type' => 'ticket',
                'is_read' => 0,
                'metadata' => json_encode([
                    'notification_scope' => 'role',
                    'target_roles' => ['admin', 'superadmin']
                ])
            ];
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