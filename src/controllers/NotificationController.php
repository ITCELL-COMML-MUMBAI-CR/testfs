<?php
/**
 * Notification Controller
 * Handles API endpoints for notification management with RBAC
 */

require_once __DIR__ . '/../controllers/BaseController.php';
require_once __DIR__ . '/../utils/NotificationService.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class NotificationController extends BaseController {

    private $notificationService;
    private $notificationModel;

    public function __construct() {
        parent::__construct();
        $this->notificationService = new NotificationService();
        $this->notificationModel = new NotificationModel();
    }

    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated() {
        return isset($_SESSION['user_id']) || isset($_SESSION['customer_id']);
    }

    /**
     * Send JSON response
     */
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Get user notifications with pagination
     */
    public function getNotifications() {
        try {
            // Verify user is authenticated
            if (!$this->isAuthenticated()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $userId = $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? null;
            $userRole = $_SESSION['user_role'] ?? 'customer';
            $division = $_SESSION['division'] ?? null;

            if (!$userId) {
                return $this->jsonResponse(['success' => false, 'error' => 'User ID not found'], 400);
            }

            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 20);
            $unreadOnly = filter_var($_GET['unread_only'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // Apply department-specific RBAC
            $notifications = $this->notificationModel->getUserNotifications($userId, $userRole, $limit, $unreadOnly, $division);

            // Calculate if there are more notifications
            // Calculate if there are more notifications
                        $totalNotifications = $this->notificationModel->getUserNotifications($userId, $userRole, $limit + 1, $unreadOnly, $division);
            $hasMore = count($totalNotifications) > $limit;

            // Format notifications for display
            $formattedNotifications = [];
            foreach ($notifications as $notification) {
                $metadata = null;
                if (!empty($notification['metadata'])) {
                    $decoded = json_decode($notification['metadata'], true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $metadata = $decoded;
                    }
                }

                $formattedNotifications[] = [
                    'id' => $notification['id'],
                    'title' => $notification['title'],
                    'message' => $notification['message'],
                    'type' => $notification['type'],
                    'priority' => $notification['priority'],
                    'user_type' => $notification['user_type'],
                    'related_id' => $notification['related_id'],
                    'related_type' => $notification['related_type'],
                    'is_read' => $notification['is_read'],
                    'action_url' => $notification['action_url'],
                    'created_at' => $notification['created_at'],
                    'read_at' => $notification['read_at'],
                    'dismissed_at' => $notification['dismissed_at'] ?? null,
                    'metadata' => $metadata
                ];
            }

            return $this->jsonResponse([
                'success' => true,
                'notifications' => $formattedNotifications,
                'page' => $page,
                'limit' => $limit,
                'has_more' => $hasMore
            ]);

        } catch (Exception $e) {
            error_log("Error getting notifications: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to load notifications'], 500);
        }
    }

    /**
     * Get notification counts
     */
    public function getNotificationCount() {
        try {
            if (!$this->isAuthenticated()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $userId = $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? null;
            $userType = $_SESSION['user_type'] ?? 'customer';

            if (!$userId) {
                return $this->jsonResponse(['success' => false, 'error' => 'User ID not found'], 400);
            }

            $counts = $this->notificationService->getNotificationCounts($userId, $userType);

            return $this->jsonResponse([
                'success' => true,
                'counts' => $counts
            ]);

        } catch (Exception $e) {
            error_log("Error getting notification count: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Failed to get notification count'], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId) {
        try {
            if (!$this->isAuthenticated()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $userId = $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? null;
            $userType = $_SESSION['user_type'] ?? 'customer';

            if (!$userId || !$notificationId) {
                return $this->jsonResponse(['success' => false, 'error' => 'Invalid parameters'], 400);
            }

            // Verify notification belongs to user (RBAC)
            $notification = $this->notificationModel->find($notificationId);
            if (!$notification) {
                return $this->jsonResponse(['success' => false, 'error' => 'Notification not found'], 404);
            }

            // Check ownership based on user type
            $isOwner = false;
            if ($userType === 'customer' && $notification['customer_id'] == $userId) {
                $isOwner = true;
            } elseif ($userType !== 'customer' && $notification['user_id'] == $userId) {
                $isOwner = true;
            }

            if (!$isOwner) {
                return $this->jsonResponse(['success' => false, 'error' => 'Access denied'], 403);
            }

            $result = $this->notificationModel->markAsRead($notificationId, $userId, $userType);

            if ($result) {
                return $this->jsonResponse(['success' => true, 'message' => 'Notification marked as read']);
            } else {
                return $this->jsonResponse(['success' => false, 'error' => 'Failed to mark as read'], 500);
            }

        } catch (Exception $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Internal error'], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead() {
        try {
            if (!$this->isAuthenticated()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $userId = $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? null;
            $userType = $_SESSION['user_type'] ?? 'customer';

            if (!$userId) {
                return $this->jsonResponse(['success' => false, 'error' => 'User ID not found'], 400);
            }

            $result = $this->notificationModel->markAllAsRead($userId, $userType);

            if ($result) {
                return $this->jsonResponse(['success' => true, 'message' => 'All notifications marked as read']);
            } else {
                return $this->jsonResponse(['success' => false, 'error' => 'Failed to mark all as read'], 500);
            }

        } catch (Exception $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Internal error'], 500);
        }
    }

    /**
     * Dismiss notification (remove from view but keep in database)
     */
    public function dismissNotification($notificationId) {
        try {
            if (!$this->isAuthenticated()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            $userId = $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? null;
            $userType = $_SESSION['user_type'] ?? 'customer';

            if (!$userId || !$notificationId) {
                return $this->jsonResponse(['success' => false, 'error' => 'Invalid parameters'], 400);
            }

            // Verify notification belongs to user (RBAC)
            $notification = $this->notificationModel->find($notificationId);
            if (!$notification) {
                return $this->jsonResponse(['success' => false, 'error' => 'Notification not found'], 404);
            }

            // Check ownership based on user type
            $isOwner = false;
            if ($userType === 'customer' && $notification['customer_id'] == $userId) {
                $isOwner = true;
            } elseif ($userType !== 'customer' && $notification['user_id'] == $userId) {
                $isOwner = true;
            }

            if (!$isOwner) {
                return $this->jsonResponse(['success' => false, 'error' => 'Access denied'], 403);
            }

            $result = $this->notificationService->dismissNotification($notificationId, $userId, $userType);

            if ($result) {
                return $this->jsonResponse(['success' => true, 'message' => 'Notification dismissed']);
            } else {
                return $this->jsonResponse(['success' => false, 'error' => 'Failed to dismiss notification'], 500);
            }

        } catch (Exception $e) {
            error_log("Error dismissing notification: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Internal error'], 500);
        }
    }

    /**
     * Create notification (Admin only)
     */
    public function createNotification() {
        try {
            if (!$this->isAuthenticated()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            // Only admins can create notifications
            $userRole = $_SESSION['user_role'] ?? '';
            if (!in_array($userRole, ['admin', 'superadmin'])) {
                return $this->jsonResponse(['success' => false, 'error' => 'Access denied'], 403);
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                return $this->jsonResponse(['success' => false, 'error' => 'Invalid JSON input'], 400);
            }

            $required = ['title', 'message', 'user_type'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    return $this->jsonResponse(['success' => false, 'error' => "Field {$field} is required"], 400);
                }
            }

            // Create notification data
            $notificationData = [
                'title' => $input['title'],
                'message' => $input['message'],
                'type' => $input['type'] ?? 'system_announcement',
                'priority' => $input['priority'] ?? 'medium',
                'user_type' => $input['user_type'],
                'expires_at' => $input['expires_at'] ?? null,
                'metadata' => $input['metadata'] ?? null
            ];

            // If specific user/customer ID provided
            if (!empty($input['user_id'])) {
                $notificationData['user_id'] = $input['user_id'];
            }
            if (!empty($input['customer_id'])) {
                $notificationData['customer_id'] = $input['customer_id'];
            }

            // Create the notification
            if (!empty($input['user_id']) || !empty($input['customer_id'])) {
                // Single user notification
                $result = $this->notificationModel->createNotification($notificationData);
                $message = $result ? 'Notification created successfully' : 'Failed to create notification';
            } else {
                // Broadcast to all users of specified type
                $result = $this->notificationModel->createSystemAnnouncement(
                    $notificationData['title'],
                    $notificationData['message'],
                    $notificationData['user_type'],
                    $notificationData['expires_at'],
                    $notificationData['priority']
                );
                $message = $result ? 'Broadcast notification created successfully' : 'Failed to create broadcast notification';
            }

            if ($result) {
                return $this->jsonResponse(['success' => true, 'message' => $message, 'notification_id' => $result]);
            } else {
                return $this->jsonResponse(['success' => false, 'error' => 'Failed to create notification'], 500);
            }

        } catch (Exception $e) {
            error_log("Error creating notification: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Internal error'], 500);
        }
    }

    /**
     * Add admin remarks to notification (Admin only)
     */
    public function addAdminRemarks($notificationId) {
        try {
            if (!$this->isAuthenticated()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            // Only admins can add remarks
            $userRole = $_SESSION['user_role'] ?? '';
            if (!in_array($userRole, ['admin', 'superadmin'])) {
                return $this->jsonResponse(['success' => false, 'error' => 'Access denied'], 403);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || empty($input['remarks'])) {
                return $this->jsonResponse(['success' => false, 'error' => 'Remarks are required'], 400);
            }

            $notification = $this->notificationModel->find($notificationId);
            if (!$notification) {
                return $this->jsonResponse(['success' => false, 'error' => 'Notification not found'], 404);
            }

            // Get existing metadata
            $metadata = [];
            if (!empty($notification['metadata'])) {
                $decoded = json_decode($notification['metadata'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $metadata = $decoded;
                }
            }

            // Add admin remarks
            $metadata['admin_remarks'] = [
                'remarks' => $input['remarks'],
                'added_by' => $_SESSION['user_name'] ?? 'Admin',
                'added_at' => date('Y-m-d H:i:s')
            ];

            $updateData = [
                'metadata' => json_encode($metadata),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->notificationModel->update($notificationId, $updateData);

            if ($result) {
                return $this->jsonResponse(['success' => true, 'message' => 'Admin remarks added successfully']);
            } else {
                return $this->jsonResponse(['success' => false, 'error' => 'Failed to add remarks'], 500);
            }

        } catch (Exception $e) {
            error_log("Error adding admin remarks: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Internal error'], 500);
        }
    }

    /**
     * Get notification statistics (Admin only)
     */
    public function getNotificationStats() {
        try {
            if (!$this->isAuthenticated()) {
                return $this->jsonResponse(['success' => false, 'error' => 'Unauthorized'], 401);
            }

            // Only admins can view stats
            $userRole = $_SESSION['user_role'] ?? '';
            if (!in_array($userRole, ['admin', 'superadmin'])) {
                return $this->jsonResponse(['success' => false, 'error' => 'Access denied'], 403);
            }

            $days = intval($_GET['days'] ?? 7);
            $userType = $_GET['user_type'] ?? null;

            $stats = $this->notificationModel->getNotificationStats(null, $userType, $days);

            return $this->jsonResponse([
                'success' => true,
                'stats' => $stats,
                'period_days' => $days
            ]);

        } catch (Exception $e) {
            error_log("Error getting notification stats: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Internal error'], 500);
        }
    }
}