<?php
/**
 * Notification Center Component
 * Displays notifications with RBAC support, clickable ticket IDs, and persistent display
 */

// Get current user info
$userId = $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? null;
$userType = $_SESSION['user_type'] ?? 'customer';

if (!$userId) {
    return;
}

// Load NotificationService
require_once __DIR__ . '/../../utils/NotificationService.php';
$notificationService = new NotificationService();

// Get notification counts
$notificationCounts = $notificationService->getNotificationCounts($userId, $userType);
$unreadCount = $notificationCounts['unread'] ?? 0;
$activeCount = $notificationCounts['active'] ?? 0;
$highPriorityCount = $notificationCounts['high_priority'] ?? 0;
?>

<!-- Notification Bell Icon with Counter -->
<div class="notification-center">
    <button type="button" class="btn btn-link nav-link position-relative" onclick="toggleNotificationPanel()"
            data-bs-toggle="tooltip" data-bs-placement="bottom" title="Notifications">
        <i class="fas fa-bell"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="<?= $unreadCount > 0 ? '' : 'display: none;' ?>">
            <?= $unreadCount > 99 ? '99+' : $unreadCount ?>
            <span class="visually-hidden">unread notifications</span>
        </span>
        <span class="position-absolute top-0 start-0 translate-middle" style="<?= $highPriorityCount > 0 ? '' : 'display: none;' ?>">
            <span class="badge bg-warning rounded-pill pulse-animation" style="width: 8px; height: 8px;"></span>
        </span>
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

    <div class="notification-footer">
        <button type="button" class="btn btn-sm btn-primary w-100" onclick="loadMoreNotifications()">
            <i class="fas fa-plus"></i> Load More
        </button>
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

.notification-footer {
    padding: 0.75rem;
    border-top: 1px solid #dee2e6;
    background: #f8f9fa;
    border-radius: 0 0 12px 12px;
}

.notification-item {
    padding: 12px 16px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #f0f7ff;
    border-left: 4px solid #007bff;
}

.notification-item.high-priority {
    border-left: 4px solid #dc3545;
}

.notification-item.critical-priority {
    border-left: 4px solid #dc3545;
    background-color: #fff5f5;
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

.ticket-link {
    color: #007bff;
    text-decoration: none;
    font-weight: 600;
}

.ticket-link:hover {
    text-decoration: underline;
}

.priority-badge {
    font-size: 0.75rem;
    padding: 2px 6px;
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
let currentPage = 1;
let isLoading = false;
let notificationCenterInitialized = false;
let lastUnreadCount = <?= $unreadCount ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Prevent multiple initializations
    if (notificationCenterInitialized) {
        return;
    }
    notificationCenterInitialized = true;

    notificationPanel = document.getElementById('notificationPanel');
    notificationOverlay = document.getElementById('notificationOverlay');

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-refresh notifications every 30 seconds
    setInterval(refreshNotificationCount, 30000);
});

function showNewNotificationToast(notification) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: notification.title,
        text: notification.message,
        showConfirmButton: false,
        timer: 10000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
            toast.addEventListener('click', () => {
                if (notification.action_url) {
                    window.location.href = notification.action_url;
                }
            })
        }
    });
}

function fetchNewestNotification() {
    fetch(`${APP_URL}/api/notifications?limit=1&unread_only=true`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.notifications && data.notifications.length > 0) {
            showNewNotificationToast(data.notifications[0]);
        }
    })
    .catch(error => console.error('Error fetching newest notification:', error));
}

function toggleNotificationPanel() {
    if (!notificationPanel) {
        console.error('Notification panel not found');
        return;
    }

    if (notificationPanel.classList.contains('d-none')) {
        openNotificationPanel();
    } else {
        closeNotificationPanel();
    }
}

function openNotificationPanel() {
    if (!notificationPanel || !notificationOverlay) {
        console.error('Notification panel elements not found');
        return;
    }

    notificationPanel.classList.remove('d-none');
    notificationOverlay.classList.remove('d-none');
    currentPage = 1;
    loadNotifications();
}

function closeNotificationPanel() {
    if (!notificationPanel || !notificationOverlay) {
        return;
    }

    notificationPanel.classList.add('d-none');
    notificationOverlay.classList.add('d-none');
}

function loadNotifications(page = 1) {
    if (isLoading) return;

    isLoading = true;
    const listContainer = document.getElementById('notificationList');

    if (!listContainer) {
        console.error('Notification list container not found');
        isLoading = false;
        return;
    }

    if (page === 1) {
        listContainer.innerHTML = `
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Loading notifications...</div>
            </div>
        `;
    }

    fetch(`${APP_URL}/api/notifications?page=${page}&limit=20&unread_only=true`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            if (page === 1) {
                listContainer.innerHTML = '';
            }

            if (data.notifications && data.notifications.length > 0) {
                data.notifications.forEach(notification => {
                    listContainer.appendChild(createNotificationElement(notification));
                });
            } else if (page === 1) {
                listContainer.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-bell-slash fa-2x mb-2"></i>
                        <div>No notifications found</div>
                    </div>
                `;
            }

            // Update footer button visibility
            const footerButton = document.querySelector('.notification-footer button');
            if (footerButton) {
                if (data.has_more) {
                    footerButton.style.display = 'block';
                } else {
                    footerButton.style.display = 'none';
                }
            }
        } else {
            if (page === 1 && listContainer) {
                listContainer.innerHTML = `
                    <div class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <div>Failed to load notifications</div>
                    </div>
                `;
            }
        }
    })
    .catch(error => {
        console.error('Error loading notifications:', error);
        if (page === 1 && listContainer) {
            listContainer.innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <div>Error loading notifications</div>
                </div>
            `;
        }
    })
    .finally(() => {
        isLoading = false;
    });
}

function loadMoreNotifications() {
    currentPage++;
    loadNotifications(currentPage);
}

function createNotificationElement(notification) {
    const div = document.createElement('div');
    div.className = `notification-item ${notification.is_read == 0 ? 'unread' : ''}`;

    if (notification.priority === 'high' || notification.priority === 'urgent') {
        div.classList.add('high-priority');
    } else if (notification.priority === 'critical') {
        div.classList.add('critical-priority');
    }

    const timeAgo = formatTimeAgo(notification.created_at);
    const ticketId = notification.related_id;
    const ticketUrl = getTicketUrl(ticketId, notification.related_type);

    div.innerHTML = `
        <div class="d-flex justify-content-between align-items-start mb-1">
            <div class="fw-semibold">${escapeHtml(notification.title)}</div>
            <div class="d-flex align-items-center gap-1">
                ${notification.priority !== 'medium' ? `<span class="badge priority-badge bg-${getPriorityColor(notification.priority)}">${notification.priority}</span>` : ''}
                <small class="text-muted">${timeAgo}</small>
                <button type="button" class="btn btn-sm btn-outline-secondary p-1" onclick="dismissNotification(${notification.id}, event)" title="Dismiss">
                    <i class="fas fa-times" style="font-size: 10px;"></i>
                </button>
            </div>
        </div>
        <div class="text-muted small mb-1">${escapeHtml(notification.message)}</div>
        ${ticketId && ticketUrl !== '#' ? `<div class="mt-2">
            <a href="${ticketUrl}" class="ticket-link" onclick="markAsRead(${notification.id}); closeNotificationPanel(); return true;" onError="console.error('Access denied to ticket'); return false;">
                <i class="fas fa-ticket-alt me-1"></i>View Ticket #${ticketId}
            </a>
        </div>` : ''}
    `;

    // Add click handler to mark as read
    div.addEventListener('click', (e) => {
        if (!e.target.closest('button') && !e.target.closest('a')) {
            markAsRead(notification.id);
            closeNotificationPanel();
            if (ticketUrl) {
                window.location.href = ticketUrl;
            }
        }
    });

    return div;
}

function getTicketUrl(ticketId, relatedType) {
    if (!ticketId || relatedType !== 'ticket') return '#';

    // Determine URL based on user role
    const userType = '<?= $userType ?>';

    switch (userType) {
        case 'customer':
            return `${APP_URL}/customer/tickets/${ticketId}`;
        case 'controller':
        case 'controller_nodal':
            return `${APP_URL}/controller/tickets/${ticketId}`;
        case 'admin':
        case 'superadmin':
            return `${APP_URL}/admin/tickets/${ticketId}/view`;
        default:
            return `${APP_URL}/customer/tickets/${ticketId}`;
    }
}

function getPriorityColor(priority) {
    switch (priority) {
        case 'critical': return 'danger';
        case 'urgent': return 'warning';
        case 'high': return 'warning';
        case 'low': return 'secondary';
        default: return 'primary';
    }
}

function formatTimeAgo(timestamp) {
    const now = new Date();
    const time = new Date(timestamp);
    const diffInSeconds = Math.floor((now - time) / 1000);

    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + 'm ago';
    if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + 'h ago';
    if (diffInSeconds < 604800) return Math.floor(diffInSeconds / 86400) + 'd ago';

    return time.toLocaleDateString();
}

function markAsRead(notificationId) {
    fetch(`${APP_URL}/api/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI to show as read
            const notificationElement = document.querySelector(`[onclick*="${notificationId}"]`)?.closest('.notification-item');
            if (notificationElement) {
                notificationElement.classList.remove('unread');
            }

            // Update counter
            refreshNotificationCount();
        }
    })
    .catch(error => console.error('Error marking notification as read:', error));
}

function dismissNotification(notificationId, event) {
    event.stopPropagation();

    // Instead of dismissing, mark as read
    fetch(`${APP_URL}/api/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove from UI and mark as read
            const notificationElement = event.target.closest('.notification-item');
            if (notificationElement) {
                notificationElement.classList.remove('unread');
                notificationElement.remove();
            }

            // Update counter
            refreshNotificationCount();
        }
    })
    .catch(error => console.error('Error marking notification as read:', error));
}

function markAllAsRead() {
    fetch(`${APP_URL}/api/notifications/mark-all-read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload notifications
            loadNotifications(1);
            // Update counter
            refreshNotificationCount();
        }
    })
    .catch(error => console.error('Error marking all as read:', error));
}

function refreshNotificationCount() {
    fetch(`${APP_URL}/api/notifications/count`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.counts) {
            const newUnreadCount = data.counts.unread;
            if (newUnreadCount > lastUnreadCount) {
                fetchNewestNotification();
            }
            lastUnreadCount = newUnreadCount;
            updateNotificationBadge(data.counts.unread, data.counts.high_priority);
        }
    })
    .catch(error => console.error('Error refreshing notification count:', error));
}

function updateNotificationBadge(unreadCount, highPriorityCount) {
    const badge = document.querySelector('.notification-center .badge');
    const warningIndicator = document.querySelector('.notification-center .pulse-animation')?.parentElement;

    if (badge) {
        if (unreadCount > 0) {
            badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            badge.style.display = '';
        } else {
            badge.style.display = 'none';
        }
    }

    if (warningIndicator) {
        if (highPriorityCount > 0) {
            warningIndicator.style.display = '';
        } else {
            warningIndicator.style.display = 'none';
        }
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>