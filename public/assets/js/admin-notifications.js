/**
 * Admin Notifications Management JavaScript
 * Handles notification management interface and RBAC functionality
 */

let notificationsTable;
let currentNotificationId = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeNotificationsTable();
    loadNotificationStats();
    bindEventHandlers();

    // Auto-refresh stats every 60 seconds
    setInterval(loadNotificationStats, 60000);
});

function initializeNotificationsTable() {
    notificationsTable = $('#notificationsTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: APP_URL + '/api/notifications?admin=true&limit=100',
            dataSrc: function(json) {
                if (json.success) {
                    return json.notifications || [];
                } else {
                    console.error('API returned error:', json.error);
                    return [];
                }
            },
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': CSRF_TOKEN
            },
            error: function(xhr, error, thrown) {
                console.error('Error loading notifications:', error, xhr.responseText);
                if (xhr.status === 401) {
                    Swal.fire('Error', 'Unauthorized access. Please login again.', 'error');
                } else if (xhr.status === 0) {
                    Swal.fire('Error', 'Network error. Please check your connection.', 'error');
                } else {
                    Swal.fire('Error', 'Failed to load notifications: ' + (xhr.responseText || error), 'error');
                }
            }
        },
        columns: [
            { data: 'id', width: '60px' },
            {
                data: 'title',
                render: function(data, type, row) {
                    return `<div class="fw-semibold">${escapeHtml(data)}</div>
                            <small class="text-muted">${escapeHtml(row.message).substring(0, 100)}${row.message.length > 100 ? '...' : ''}</small>`;
                }
            },
            {
                data: 'type',
                render: function(data) {
                    return `<span class="badge bg-secondary">${formatNotificationType(data)}</span>`;
                }
            },
            {
                data: 'priority',
                render: function(data) {
                    const colors = {
                        'critical': 'danger',
                        'urgent': 'warning',
                        'high': 'warning',
                        'medium': 'primary',
                        'low': 'secondary'
                    };
                    return `<span class="badge bg-${colors[data] || 'secondary'}">${data.toUpperCase()}</span>`;
                }
            },
            {
                data: 'user_type',
                render: function(data) {
                    return data ? `<span class="badge bg-info">${formatUserType(data)}</span>` : 'All Users';
                }
            },
            {
                data: 'related_id',
                render: function(data, type, row) {
                    if (data && row.related_type === 'ticket') {
                        return `<a href="${getTicketUrl(data)}" class="text-decoration-none" target="_blank">
                                    <i class="fas fa-ticket-alt"></i> #${data}
                                </a>`;
                    }
                    return '-';
                }
            },
            {
                data: 'created_at',
                render: function(data) {
                    return formatDateTime(data);
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    const isRead = row.is_read == 1;
                    const isDismissed = row.dismissed_at !== null;

                    let status = '<span class="badge bg-success">Active</span>';
                    if (isDismissed) {
                        status = '<span class="badge bg-secondary">Dismissed</span>';
                    } else if (isRead) {
                        status = '<span class="badge bg-info">Read</span>';
                    } else {
                        status = '<span class="badge bg-warning">Unread</span>';
                    }

                    return status;
                }
            },
            {
                data: 'id',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary" onclick="viewNotificationDetails(${data})" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="addNotificationRemarks(${data})" title="Add Remarks">
                                <i class="fas fa-comment"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="deleteNotification(${data})" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[6, 'desc']], // Sort by created_at descending
        pageLength: 25,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            emptyTable: 'No notifications found'
        }
    });
}

function loadNotificationStats() {
    fetch(APP_URL + '/api/notifications/stats?days=7', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.stats) {
            updateStatsDisplay(data.stats);
        }
    })
    .catch(error => {
        console.error('Error loading notification stats:', error);
    });
}

function updateStatsDisplay(stats) {
    // Calculate totals from stats array
    let total = 0;
    let escalations = 0;
    let critical = 0;
    let active = 0;

    if (Array.isArray(stats)) {
        stats.forEach(stat => {
            total += parseInt(stat.total_count);
            active += parseInt(stat.unread_count);

            if (stat.type === 'priority_escalated') {
                escalations += parseInt(stat.total_count);
            }

            if (stat.priority === 'critical') {
                critical += parseInt(stat.total_count);
            }
        });
    }

    document.getElementById('totalNotifications').textContent = total;
    document.getElementById('activeNotifications').textContent = active;
    document.getElementById('priorityEscalations').textContent = escalations;
    document.getElementById('criticalNotifications').textContent = critical;
}

function bindEventHandlers() {
    // Create notification form
    document.getElementById('createNotificationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        createNotification();
    });

    // Targeted notification form
    document.getElementById('targetedNotificationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        sendTargetedNotification();
    });

    // Add remarks form
    document.getElementById('addRemarksForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitAdminRemarks();
    });

    // Tab change handlers
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(e) {
            const target = e.target.getAttribute('data-bs-target');
            handleTabChange(target);
        });
    });
}

function handleTabChange(tabTarget) {
    switch(tabTarget) {
        case '#priority-escalations':
            loadPriorityEscalations();
            break;
        case '#system-announcements':
            loadSystemAnnouncements();
            break;
    }
}

function loadPriorityEscalations() {
    // Load priority escalations data
    fetch(APP_URL + '/api/notifications/stats?type=priority_escalation&days=30', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayPriorityEscalations(data.stats);
        } else {
            console.error('Failed to load priority escalations');
        }
    })
    .catch(error => {
        console.error('Error loading priority escalations:', error);
    });
}

function displayPriorityEscalations(escalations) {
    const container = document.getElementById('priority-escalations-content');
    if (!container) return;

    if (!escalations || escalations.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-muted">No priority escalations found</div>';
        return;
    }

    let html = '<div class="table-responsive"><table class="table table-striped"><thead><tr>';
    html += '<th>Date</th><th>Ticket ID</th><th>From Priority</th><th>To Priority</th><th>Reason</th>';
    html += '</tr></thead><tbody>';

    escalations.forEach(escalation => {
        html += `<tr>
            <td>${formatDateTime(escalation.created_at)}</td>
            <td><a href="${getTicketUrl(escalation.ticket_id)}" target="_blank">#${escalation.ticket_id}</a></td>
            <td><span class="badge bg-secondary">${escalation.from_priority}</span></td>
            <td><span class="badge bg-warning">${escalation.to_priority}</span></td>
            <td>${escalation.reason || 'Auto-escalated'}</td>
        </tr>`;
    });

    html += '</tbody></table></div>';
    container.innerHTML = html;
}

function loadSystemAnnouncements() {
    // Load system announcements data
    fetch(APP_URL + '/api/notifications?type=system_announcement&limit=50', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displaySystemAnnouncements(data.notifications);
        } else {
            console.error('Failed to load system announcements');
        }
    })
    .catch(error => {
        console.error('Error loading system announcements:', error);
    });
}

function displaySystemAnnouncements(announcements) {
    const container = document.getElementById('system-announcements-content');
    if (!container) return;

    if (!announcements || announcements.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-muted">No system announcements found</div>';
        return;
    }

    let html = '';
    announcements.forEach(announcement => {
        const isExpired = announcement.expires_at && new Date(announcement.expires_at) < new Date();
        html += `<div class="card mb-3 ${isExpired ? 'border-secondary' : ''}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">${escapeHtml(announcement.title)}</h6>
                <div>
                    <span class="badge bg-${getPriorityColor(announcement.priority)}">${announcement.priority.toUpperCase()}</span>
                    ${isExpired ? '<span class="badge bg-secondary ms-1">EXPIRED</span>' : ''}
                </div>
            </div>
            <div class="card-body">
                <p class="card-text">${escapeHtml(announcement.message)}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Created: ${formatDateTime(announcement.created_at)}
                        ${announcement.expires_at ? ` | Expires: ${formatDateTime(announcement.expires_at)}` : ''}
                    </small>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewNotificationDetails(${announcement.id})">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="duplicateNotification(${announcement.id})">
                            <i class="fas fa-copy"></i> Duplicate
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    });

    container.innerHTML = html;
}

function duplicateNotification(notificationId) {
    // Get notification details and pre-fill the create form
    fetch(APP_URL + `/api/notifications/${notificationId}/details`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.notification) {
            const notification = data.notification;

            // Pre-fill the create notification form
            document.getElementById('title').value = 'Copy of ' + notification.title;
            document.getElementById('message').value = notification.message;
            document.getElementById('priority').value = notification.priority;
            document.getElementById('user_type').value = notification.user_type || '';

            // Show the create modal
            showCreateNotificationModal();
        } else {
            Swal.fire('Error', 'Failed to load notification details', 'error');
        }
    })
    .catch(error => {
        console.error('Error loading notification for duplication:', error);
        Swal.fire('Error', 'Failed to duplicate notification', 'error');
    });
}

function showCreateNotificationModal() {
    const modal = new bootstrap.Modal(document.getElementById('createNotificationModal'));
    modal.show();
}

function showCreateAnnouncementModal() {
    // Reset form
    const form = document.getElementById('createNotificationForm');
    if (form) {
        form.reset();
        // Set announcement-specific defaults
        document.getElementById('type').value = 'system_announcement';
        document.getElementById('priority').value = 'medium';
        document.getElementById('user_type').value = ''; // All users
    }

    const modal = new bootstrap.Modal(document.getElementById('createNotificationModal'));
    modal.show();
}

function createNotification() {
    const form = document.getElementById('createNotificationForm');
    const formData = new FormData(form);
    const notificationData = Object.fromEntries(formData);

    // Convert checkbox values
    notificationData.send_email = form.send_email.checked;
    notificationData.send_sms = form.send_sms.checked;

    fetch(APP_URL + '/api/notifications/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: JSON.stringify(notificationData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'Notification created successfully!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('createNotificationModal')).hide();
            form.reset();
            notificationsTable.ajax.reload();
            loadNotificationStats();
        } else {
            Swal.fire('Error', data.error || 'Failed to create notification', 'error');
        }
    })
    .catch(error => {
        console.error('Error creating notification:', error);
        Swal.fire('Error', 'An error occurred while creating the notification', 'error');
    });
}

function sendTargetedNotification() {
    const form = document.getElementById('targetedNotificationForm');
    const formData = new FormData(form);
    const notificationData = Object.fromEntries(formData);
    notificationData.user_type = notificationData.target_user_type;

    fetch(APP_URL + '/api/notifications/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: JSON.stringify(notificationData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'Targeted notification sent successfully!', 'success');
            form.reset();
            notificationsTable.ajax.reload();
            loadNotificationStats();
        } else {
            Swal.fire('Error', data.error || 'Failed to send notification', 'error');
        }
    })
    .catch(error => {
        console.error('Error sending targeted notification:', error);
        Swal.fire('Error', 'An error occurred while sending the notification', 'error');
    });
}

function viewNotificationDetails(notificationId) {
    currentNotificationId = notificationId;

    fetch(APP_URL + `/api/notifications/${notificationId}/details`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.notification) {
            displayNotificationDetails(data.notification);
        } else {
            Swal.fire('Error', 'Failed to load notification details', 'error');
        }
    })
    .catch(error => {
        console.error('Error loading notification details:', error);
        Swal.fire('Error', 'An error occurred while loading notification details', 'error');
    });
}

function displayNotificationDetails(notification) {
    const content = document.getElementById('notificationDetailsContent');
    const metadata = notification.metadata || {};

    content.innerHTML = `
        <div class="row">
            <div class="col-md-8">
                <h6>Notification Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>ID:</strong></td><td>#${notification.id}</td></tr>
                    <tr><td><strong>Title:</strong></td><td>${escapeHtml(notification.title)}</td></tr>
                    <tr><td><strong>Type:</strong></td><td><span class="badge bg-secondary">${formatNotificationType(notification.type)}</span></td></tr>
                    <tr><td><strong>Priority:</strong></td><td><span class="badge bg-${getPriorityColor(notification.priority)}">${notification.priority.toUpperCase()}</span></td></tr>
                    <tr><td><strong>User Type:</strong></td><td>${notification.user_type ? formatUserType(notification.user_type) : 'All Users'}</td></tr>
                    <tr><td><strong>Created:</strong></td><td>${formatDateTime(notification.created_at)}</td></tr>
                    <tr><td><strong>Status:</strong></td><td>${getNotificationStatus(notification)}</td></tr>
                </table>
            </div>
            <div class="col-md-4">
                <h6>Actions</h6>
                <div class="d-grid gap-2">
                    ${notification.related_id && notification.related_type === 'ticket' ?
                        `<a href="${getTicketUrl(notification.related_id)}" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="fas fa-ticket-alt"></i> View Ticket #${notification.related_id}
                        </a>` : ''}
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="duplicateNotification(${notification.id})">
                        <i class="fas fa-copy"></i> Duplicate
                    </button>
                </div>
            </div>
        </div>
        <hr>
        <div>
            <h6>Message</h6>
            <div class="alert alert-info">
                ${escapeHtml(notification.message)}
            </div>
        </div>
        ${metadata.admin_remarks ? `
            <div>
                <h6>Admin Remarks</h6>
                <div class="alert alert-warning">
                    <strong>Remarks:</strong> ${escapeHtml(metadata.admin_remarks.remarks)}<br>
                    <small class="text-muted">Added by ${escapeHtml(metadata.admin_remarks.added_by)} on ${formatDateTime(metadata.admin_remarks.added_at)}</small>
                </div>
            </div>
        ` : ''}
        ${Object.keys(metadata).length > 0 ? `
            <div>
                <h6>Additional Information</h6>
                <pre class="small bg-light p-2 rounded">${JSON.stringify(metadata, null, 2)}</pre>
            </div>
        ` : ''}
    `;

    const modal = new bootstrap.Modal(document.getElementById('notificationDetailsModal'));
    modal.show();
}

function addNotificationRemarks(notificationId) {
    currentNotificationId = notificationId;
    const modal = new bootstrap.Modal(document.getElementById('addRemarksModal'));
    modal.show();
}

function showAddRemarksModal() {
    bootstrap.Modal.getInstance(document.getElementById('notificationDetailsModal')).hide();
    setTimeout(() => {
        const modal = new bootstrap.Modal(document.getElementById('addRemarksModal'));
        modal.show();
    }, 300);
}

function submitAdminRemarks() {
    if (!currentNotificationId) return;

    const form = document.getElementById('addRemarksForm');
    const formData = new FormData(form);
    const remarksData = Object.fromEntries(formData);

    fetch(APP_URL + `/api/notifications/${currentNotificationId}/remarks`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: JSON.stringify(remarksData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'Admin remarks added successfully!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('addRemarksModal')).hide();
            form.reset();
            // Refresh the details if modal is still open
            if (currentNotificationId) {
                viewNotificationDetails(currentNotificationId);
            }
        } else {
            Swal.fire('Error', data.error || 'Failed to add remarks', 'error');
        }
    })
    .catch(error => {
        console.error('Error adding remarks:', error);
        Swal.fire('Error', 'An error occurred while adding remarks', 'error');
    });
}

function deleteNotification(notificationId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will permanently delete the notification.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(APP_URL + `/api/notifications/${notificationId}/delete`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': CSRF_TOKEN
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', 'Notification has been deleted.', 'success');
                    notificationsTable.ajax.reload();
                    loadNotificationStats();
                } else {
                    Swal.fire('Error', data.error || 'Failed to delete notification', 'error');
                }
            })
            .catch(error => {
                console.error('Error deleting notification:', error);
                Swal.fire('Error', 'An error occurred while deleting the notification', 'error');
            });
        }
    });
}

function applyFilters() {
    const typeFilter = document.getElementById('typeFilter').value;
    const priorityFilter = document.getElementById('priorityFilter').value;
    const userTypeFilter = document.getElementById('userTypeFilter').value;

    // Apply DataTable column filters
    notificationsTable.column(2).search(typeFilter);
    notificationsTable.column(3).search(priorityFilter);
    notificationsTable.column(4).search(userTypeFilter);
    notificationsTable.draw();
}

function refreshNotificationStats() {
    loadNotificationStats();
    notificationsTable.ajax.reload();
    Swal.fire({
        icon: 'success',
        title: 'Refreshed',
        text: 'Notification data has been refreshed',
        timer: 1500,
        showConfirmButton: false
    });
}

function sendMaintenanceAlert() {
    Swal.fire({
        title: 'Send Maintenance Alert',
        html: `
            <div class="mb-3">
                <label class="form-label">Maintenance Window</label>
                <input type="datetime-local" id="maintenanceTime" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Duration (hours)</label>
                <input type="number" id="maintenanceDuration" class="form-control" value="2" min="1" max="24">
            </div>
            <div class="mb-3">
                <label class="form-label">Additional Details</label>
                <textarea id="maintenanceDetails" class="form-control" rows="3" placeholder="Describe what will be affected during maintenance..."></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Send Alert',
        preConfirm: () => {
            const time = document.getElementById('maintenanceTime').value;
            const duration = document.getElementById('maintenanceDuration').value;
            const details = document.getElementById('maintenanceDetails').value;

            if (!time) {
                Swal.showValidationMessage('Please select maintenance time');
                return false;
            }

            return { time, duration, details };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { time, duration, details } = result.value;
            const maintenanceDate = new Date(time);

            const notificationData = {
                type: 'maintenance_alert',
                priority: 'high',
                user_type: '', // All users
                title: `Scheduled Maintenance - ${maintenanceDate.toLocaleDateString()}`,
                message: `System maintenance is scheduled for ${maintenanceDate.toLocaleString()} (Duration: ${duration} hours). ${details || 'Please plan accordingly.'}`,
                expires_at: new Date(maintenanceDate.getTime() + (duration * 60 * 60 * 1000)).toISOString().slice(0, 19).replace('T', ' ')
            };

            createNotificationFromData(notificationData);
        }
    });
}

function sendSystemUpdate() {
    Swal.fire({
        title: 'Send System Update Notice',
        html: `
            <div class="mb-3">
                <label class="form-label">Update Version</label>
                <input type="text" id="updateVersion" class="form-control" placeholder="e.g., v2.1.0">
            </div>
            <div class="mb-3">
                <label class="form-label">Release Notes</label>
                <textarea id="releaseNotes" class="form-control" rows="4" placeholder="What's new in this update..."></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Send Notice',
        preConfirm: () => {
            const version = document.getElementById('updateVersion').value;
            const notes = document.getElementById('releaseNotes').value;

            if (!version || !notes) {
                Swal.showValidationMessage('Please fill in all fields');
                return false;
            }

            return { version, notes };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { version, notes } = result.value;

            const notificationData = {
                type: 'system_announcement',
                priority: 'medium',
                user_type: '', // All users
                title: `System Update ${version} Released`,
                message: `SAMPARK has been updated to ${version}. ${notes}`
            };

            createNotificationFromData(notificationData);
        }
    });
}

function sendTrainingNotice() {
    // Similar implementation for training notices
    Swal.fire('Info', 'Training notice functionality will be implemented', 'info');
}

function createNotificationFromData(notificationData) {
    fetch(APP_URL + '/api/notifications/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: JSON.stringify(notificationData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'Notification sent successfully!', 'success');
            notificationsTable.ajax.reload();
            loadNotificationStats();
        } else {
            Swal.fire('Error', data.error || 'Failed to send notification', 'error');
        }
    })
    .catch(error => {
        console.error('Error sending notification:', error);
        Swal.fire('Error', 'An error occurred while sending the notification', 'error');
    });
}

// Utility functions
function formatNotificationType(type) {
    const types = {
        'priority_escalated': 'Priority Escalated',
        'ticket_assigned': 'Ticket Assigned',
        'ticket_created': 'Ticket Created',
        'ticket_updated': 'Ticket Updated',
        'system_announcement': 'System Announcement',
        'maintenance_alert': 'Maintenance Alert',
        'account_update': 'Account Update',
        'info': 'Information',
        'warning': 'Warning',
        'error': 'Error'
    };
    return types[type] || type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function formatUserType(userType) {
    const types = {
        'customer': 'Customer',
        'controller': 'Controller',
        'controller_nodal': 'Controller Nodal',
        'admin': 'Admin',
        'superadmin': 'Super Admin'
    };
    return types[userType] || userType;
}

function getPriorityColor(priority) {
    const colors = {
        'critical': 'danger',
        'urgent': 'warning',
        'high': 'warning',
        'medium': 'primary',
        'low': 'secondary'
    };
    return colors[priority] || 'secondary';
}

function getNotificationStatus(notification) {
    if (notification.dismissed_at) {
        return '<span class="badge bg-secondary">Dismissed</span>';
    } else if (notification.is_read == 1) {
        return '<span class="badge bg-info">Read</span>';
    } else {
        return '<span class="badge bg-warning">Unread</span>';
    }
}

function getTicketUrl(ticketId) {
    return `${APP_URL}/admin/tickets/view/${ticketId}`;
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}