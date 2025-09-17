<?php
/**
 * Admin Notifications Management Page
 * Allows admins to view, create, and manage notifications with RBAC
 */

$page_title = 'Notification Management - SAMPARK Admin';
$additional_css = [
    
];
$additional_js = [
    Config::getAppUrl() . '/assets/js/admin-notifications.js'
];

ob_start();
?>

<div class="container-xl">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Notification Management</h1>
                    <p class="text-muted">Manage system notifications and priority escalations</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" onclick="showCreateNotificationModal()">
                        <i class="fas fa-plus"></i> Create Notification
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="refreshNotificationStats()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Notification Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row" id="notificationStats">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <div class="h3 mb-1" id="totalNotifications">-</div>
                                    <div class="text-muted">Total Notifications</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <div class="h3 mb-1" id="activeNotifications">-</div>
                                    <div>Active Notifications</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <div class="h3 mb-1" id="priorityEscalations">-</div>
                                    <div>Priority Escalations</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <div class="h3 mb-1" id="criticalNotifications">-</div>
                                    <div>Critical Alerts</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Management Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="notificationTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-notifications-tab" data-bs-toggle="tab" data-bs-target="#all-notifications" type="button" role="tab">
                        <i class="fas fa-bell"></i> All Notifications
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="priority-escalations-tab" data-bs-toggle="tab" data-bs-target="#priority-escalations" type="button" role="tab">
                        <i class="fas fa-exclamation-triangle"></i> Priority Escalations
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="system-announcements-tab" data-bs-toggle="tab" data-bs-target="#system-announcements" type="button" role="tab">
                        <i class="fas fa-bullhorn"></i> System Announcements
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="user-notifications-tab" data-bs-toggle="tab" data-bs-target="#user-notifications" type="button" role="tab">
                        <i class="fas fa-users"></i> User Notifications
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="notificationTabContent">
                <!-- All Notifications Tab -->
                <div class="tab-pane fade show active" id="all-notifications" role="tabpanel">
                    <div class="mb-3">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Filter by Type</label>
                                <select class="form-select" id="typeFilter">
                                    <option value="">All Types</option>
                                    <option value="priority_escalated">Priority Escalated</option>
                                    <option value="ticket_assigned">Ticket Assigned</option>
                                    <option value="system_announcement">System Announcement</option>
                                    <option value="maintenance_alert">Maintenance Alert</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Filter by Priority</label>
                                <select class="form-select" id="priorityFilter">
                                    <option value="">All Priorities</option>
                                    <option value="critical">Critical</option>
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Filter by User Type</label>
                                <select class="form-select" id="userTypeFilter">
                                    <option value="">All User Types</option>
                                    <option value="customer">Customer</option>
                                    <option value="controller">Controller</option>
                                    <option value="controller_nodal">Controller Nodal</option>
                                    <option value="admin">Admin</option>
                                    <option value="superadmin">Super Admin</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="button" class="btn btn-outline-primary" onclick="applyFilters()">
                                        <i class="fas fa-filter"></i> Apply Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped" id="notificationsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>User Type</th>
                                    <th>Related Ticket</th>
                                    <th>Created</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Populated via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Priority Escalations Tab -->
                <div class="tab-pane fade" id="priority-escalations" role="tabpanel">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Recent Priority Escalations</h5>
                            <div id="escalationsList">
                                <!-- Populated via AJAX -->
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h5>Escalation Settings</h5>
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="autoEscalationEnabled" checked>
                                        <label class="form-check-label" for="autoEscalationEnabled">
                                            Enable Automatic Priority Escalation
                                        </label>
                                    </div>
                                    <hr>
                                    <small class="text-muted">
                                        <strong>Escalation Rules:</strong><br>
                                        • 4+ hours: Normal → Medium<br>
                                        • 12+ hours: Medium → High<br>
                                        • 24+ hours: High → Critical
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Announcements Tab -->
                <div class="tab-pane fade" id="system-announcements" role="tabpanel">
                    <div class="d-flex justify-content-between mb-3">
                        <h5>System Announcements</h5>
                        <button type="button" class="btn btn-success" onclick="showCreateAnnouncementModal()">
                            <i class="fas fa-bullhorn"></i> Create Announcement
                        </button>
                    </div>
                    <div id="announcementsList">
                        <!-- Populated via AJAX -->
                    </div>
                </div>

                <!-- User Notifications Tab -->
                <div class="tab-pane fade" id="user-notifications" role="tabpanel">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Send Targeted Notification</h5>
                            <form id="targetedNotificationForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Target User Type</label>
                                            <select class="form-select" name="target_user_type" required>
                                                <option value="">Select User Type</option>
                                                <option value="customer">Customers</option>
                                                <option value="controller">Controllers</option>
                                                <option value="controller_nodal">Controller Nodal</option>
                                                <option value="admin">Admins</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Priority</label>
                                            <select class="form-select" name="priority">
                                                <option value="low">Low</option>
                                                <option value="medium" selected>Medium</option>
                                                <option value="high">High</option>
                                                <option value="urgent">Urgent</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Message</label>
                                    <textarea class="form-control" name="message" rows="4" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Expires At (Optional)</label>
                                    <input type="datetime-local" class="form-control" name="expires_at">
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send Notification
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <h5>Quick Actions</h5>
                            <div class="list-group">
                                <button type="button" class="list-group-item list-group-item-action" onclick="sendMaintenanceAlert()">
                                    <i class="fas fa-tools"></i> Send Maintenance Alert
                                </button>
                                <button type="button" class="list-group-item list-group-item-action" onclick="sendSystemUpdate()">
                                    <i class="fas fa-sync"></i> System Update Notice
                                </button>
                                <button type="button" class="list-group-item list-group-item-action" onclick="sendTrainingNotice()">
                                    <i class="fas fa-graduation-cap"></i> Training Notice
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Notification Modal -->
<div class="modal fade" id="createNotificationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createNotificationForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Notification Type</label>
                                <select class="form-select" name="type" required>
                                    <option value="system_announcement">System Announcement</option>
                                    <option value="maintenance_alert">Maintenance Alert</option>
                                    <option value="account_update">Account Update</option>
                                    <option value="info">Information</option>
                                    <option value="warning">Warning</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Priority</label>
                                <select class="form-select" name="priority">
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Target User Type</label>
                                <select class="form-select" name="user_type" required>
                                    <option value="">All Users</option>
                                    <option value="customer">Customers</option>
                                    <option value="controller">Controllers</option>
                                    <option value="controller_nodal">Controller Nodal</option>
                                    <option value="admin">Admins</option>
                                    <option value="superadmin">Super Admins</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Expires At (Optional)</label>
                                <input type="datetime-local" class="form-control" name="expires_at">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" name="message" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="send_email" checked>
                            <label class="form-check-label">Send Email Notification</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="send_sms">
                            <label class="form-check-label">Send SMS Notification</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Create & Send
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Notification Details Modal -->
<div class="modal fade" id="notificationDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notification Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="notificationDetailsContent">
                <!-- Populated via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="showAddRemarksModal()">
                    <i class="fas fa-comment"></i> Add Admin Remarks
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Remarks Modal -->
<div class="modal fade" id="addRemarksModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Admin Remarks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addRemarksForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Admin Remarks</label>
                        <textarea class="form-control" name="remarks" rows="4" required placeholder="Enter your administrative remarks here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Remarks
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.notification-item {
    border-left: 4px solid #dee2e6;
    transition: all 0.2s;
}

.notification-item.priority-critical {
    border-left-color: #dc3545;
    background-color: #fff5f5;
}

.notification-item.priority-high {
    border-left-color: #fd7e14;
    background-color: #fff8f0;
}

.notification-item.priority-medium {
    border-left-color: #ffc107;
    background-color: #fffbf0;
}

.notification-item.priority-low {
    border-left-color: #6f42c1;
    background-color: #f8f5ff;
}

.priority-badge {
    font-size: 0.75rem;
    padding: 2px 8px;
}

.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    background-color: transparent;
    border-bottom: 2px solid #0d6efd;
    color: #0d6efd;
}

#notificationStats .card {
    transition: transform 0.2s;
}

#notificationStats .card:hover {
    transform: translateY(-2px);
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>