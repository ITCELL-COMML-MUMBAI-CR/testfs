<?php
/**
 * Safe Notification Center Component
 * Works both before and after migration without errors
 */

// Get current user info
$userId = $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? null;
$userType = $_SESSION['user_type'] ?? 'customer';

if (!$userId) {
    return;
}

// Initialize safe defaults
$unreadCount = 0;
$activeCount = 0;
$highPriorityCount = 0;

try {
    // Direct database query to avoid service layer issues
    require_once __DIR__ . '/../../config/database.php';
    $db = Database::getInstance();

    // Check if enhanced columns exist
    $columnsResult = $db->fetchAll("SHOW COLUMNS FROM notifications LIKE 'priority'");
    $hasEnhancedColumns = !empty($columnsResult);

    if ($hasEnhancedColumns) {
        // Use enhanced query
        $whereClause = $userType === 'customer' ? 'customer_id = ?' : 'user_id = ?';
        $sql = "SELECT
                    COUNT(*) as total,
                    COUNT(CASE WHEN is_read = 0 AND dismissed_at IS NULL THEN 1 END) as unread,
                    COUNT(CASE WHEN dismissed_at IS NULL THEN 1 END) as active,
                    COUNT(CASE WHEN priority IN ('high', 'critical', 'urgent') AND dismissed_at IS NULL THEN 1 END) as high_priority
                FROM notifications
                WHERE {$whereClause}
                AND (expires_at IS NULL OR expires_at > NOW())";
    } else {
        // Use basic query for backward compatibility
        $whereClause = $userType === 'customer' ? 'customer_id = ?' : 'user_id = ?';
        $sql = "SELECT
                    COUNT(*) as total,
                    COUNT(CASE WHEN is_read = 0 THEN 1 END) as unread,
                    COUNT(*) as active,
                    0 as high_priority
                FROM notifications
                WHERE {$whereClause}";
    }

    $result = $db->fetch($sql, [$userId]);
    if ($result) {
        $unreadCount = $result['unread'] ?? 0;
        $activeCount = $result['active'] ?? 0;
        $highPriorityCount = $result['high_priority'] ?? 0;
    }

} catch (Exception $e) {
    // Gracefully handle any errors
    error_log("Safe notification center error: " . $e->getMessage());
    $unreadCount = 0;
    $activeCount = 0;
    $highPriorityCount = 0;
}
?>

<!-- Notification Bell Icon with Counter -->
<div class="notification-center">
    <button type="button" class="btn btn-link nav-link position-relative" onclick="toggleNotificationPanel()"
            data-bs-toggle="tooltip" data-bs-placement="bottom" title="Notifications">
        <i class="fas fa-bell"></i>
        <?php if ($unreadCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unreadCount > 99 ? '99+' : $unreadCount ?>
                <span class="visually-hidden">unread notifications</span>
            </span>
        <?php endif; ?>
        <?php if ($highPriorityCount > 0): ?>
            <span class="position-absolute top-0 start-0 translate-middle">
                <span class="badge bg-warning rounded-pill pulse-animation" style="width: 8px; height: 8px;"></span>
            </span>
        <?php endif; ?>
    </button>
</div>

<!-- Notification Panel -->
<div id="notificationPanel" class="notification-panel d-none">
    <div class="notification-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Notifications</h6>
            <div class="d-flex gap-2">
                <?php if ($activeCount > 0): ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="markAllAsRead()">
                        <i class="fas fa-check-double"></i> Mark All Read
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeNotificationPanel()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="notification-stats mt-2">
            <small class="text-muted">
                <?= $activeCount ?> active notifications
                <?php if ($highPriorityCount > 0): ?>
                    • <span class="text-warning"><?= $highPriorityCount ?> high priority</span>
                <?php endif; ?>
            </small>
        </div>
    </div>

    <div class="notification-body">
        <div id="notificationList">
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Loading notifications...</div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Panel Overlay -->
<div id="notificationOverlay" class="notification-overlay d-none" onclick="closeNotificationPanel()"></div>

<style>
.notification-center .btn {
    border: none !important;
    padding: 0.5rem;
}

.notification-panel {
    position: fixed;
    top: 70px;
    right: 20px;
    width: 400px;
    max-width: 90vw;
    max-height: 70vh;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    z-index: 1050;
    display: flex;
    flex-direction: column;
}

.notification-header {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
    border-radius: 12px 12px 0 0;
}

.notification-body {
    flex: 1;
    overflow-y: auto;
    max-height: 400px;
}

.notification-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.1);
    z-index: 1040;
}

.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

@media (max-width: 768px) {
    .notification-panel {
        right: 10px;
        left: 10px;
        width: auto;
        top: 80px;
    }
}
</style>

<script>
let notificationPanel = null;
let notificationOverlay = null;

document.addEventListener('DOMContentLoaded', function() {
    notificationPanel = document.getElementById('notificationPanel');
    notificationOverlay = document.getElementById('notificationOverlay');

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

function toggleNotificationPanel() {
    if (notificationPanel.classList.contains('d-none')) {
        openNotificationPanel();
    } else {
        closeNotificationPanel();
    }
}

function openNotificationPanel() {
    notificationPanel.classList.remove('d-none');
    notificationOverlay.classList.remove('d-none');
    loadBasicNotifications();
}

function closeNotificationPanel() {
    notificationPanel.classList.add('d-none');
    notificationOverlay.classList.add('d-none');
}

function loadBasicNotifications() {
    const listContainer = document.getElementById('notificationList');

    listContainer.innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-bell fa-2x mb-2 text-muted"></i>
            <div>Notification system ready</div>
            <small class="text-muted">
                <?php if (!$hasEnhancedColumns ?? true): ?>
                    Run database migration to enable full features
                <?php else: ?>
                    Full notification system active
                <?php endif; ?>
            </small>
        </div>
    `;
}

function markAllAsRead() {
    // Simple implementation for now
    console.log('Mark all as read - functionality will be available after migration');
}
</script>