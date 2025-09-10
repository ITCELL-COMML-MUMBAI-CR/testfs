<?php
// Capture the content
ob_start();
?>

<!-- Customer Dashboard -->
<section class="py-apple-6">
    <div class="container-xl">
        
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                    <div>
                        <h1 class="display-3 mb-2">Welcome back, <?= htmlspecialchars($customer['name']) ?></h1>
                        <p class="text-muted mb-0">
                            <i class="fas fa-building me-2"></i><?= htmlspecialchars($customer['company_name']) ?>
                            <span class="mx-2">•</span>
                            <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($customer['email']) ?>
                        </p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <a href="<?= Config::APP_URL ?>/customer/tickets/create" class="btn btn-apple-primary">
                            <i class="fas fa-plus me-2"></i>Create New Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats Cards -->
        <div class="row g-4 mb-5">
            <div class="col-6 col-md-3">
                <div class="card-apple text-center h-100">
                    <div class="card-body">
                        <div class="display-4 fw-light text-apple-blue mb-2"><?= $ticket_stats['total'] ?></div>
                        <h6 class="text-muted mb-0">Total Tickets</h6>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="card-apple text-center h-100">
                    <div class="card-body">
                        <div class="display-4 fw-light text-warning mb-2"><?= $ticket_stats['pending'] ?></div>
                        <h6 class="text-muted mb-0">Pending</h6>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="card-apple text-center h-100">
                    <div class="card-body">
                        <div class="display-4 fw-light text-info mb-2"><?= $ticket_stats['awaiting_feedback'] ?></div>
                        <h6 class="text-muted mb-0">Awaiting Feedback</h6>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="card-apple text-center h-100">
                    <div class="card-body">
                        <div class="display-4 fw-light text-danger mb-2"><?= $ticket_stats['high_priority_count'] ?></div>
                        <h6 class="text-muted mb-0">High Priority</h6>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            
            <!-- Recent Tickets -->
            <div class="col-12 col-lg-8">
                <div class="card-apple h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-ticket-alt text-apple-blue me-2"></i>
                                Recent Tickets
                            </h4>
                            <a href="<?= Config::APP_URL ?>/customer/tickets" class="btn btn-apple-glass btn-sm">
                                <i class="fas fa-eye me-1"></i>View All
                            </a>
                        </div>
                        
                        <?php if (!empty($recent_tickets)): ?>
                            <div class="table-responsive">
                                <table class="table table-apple table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Ticket ID</th>
                                            <th>Subject</th>
                                            <th>Status</th>
                                            <th>Priority</th>
                                            <th>Created</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_tickets as $ticket): ?>
                                            <tr>
                                                <td>
                                                    <code class="text-apple-blue">#<?= htmlspecialchars($ticket['complaint_id']) ?></code>
                                                </td>
                                                <td>
                                                    <div class="fw-medium"><?= htmlspecialchars($ticket['category']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($ticket['type']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-apple status-<?= str_replace('_', '-', $ticket['status']) ?>">
                                                        <?= ucwords(str_replace('_', ' ', $ticket['status'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-priority-<?= $ticket['priority'] ?>">
                                                        <?= ucfirst($ticket['priority']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?= date('M d, Y', strtotime($ticket['created_at'])) ?></small>
                                                </td>
                                                <td>
                                                    <a href="<?= Config::APP_URL ?>/customer/tickets/<?= $ticket['complaint_id'] ?>" 
                                                       class="btn btn-apple-glass btn-sm">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-ticket-alt text-muted mb-3" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mb-3">No Recent Tickets</h5>
                                <p class="text-muted mb-4">You haven't created any support tickets yet.</p>
                                <a href="<?= Config::APP_URL ?>/customer/tickets/create" class="btn btn-apple-primary">
                                    <i class="fas fa-plus me-2"></i>Create Your First Ticket
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-12 col-lg-4">
                
                <!-- Pending Feedback Alert -->
                <?php if (!empty($pending_feedback)): ?>
                    <div class="card-apple border-warning mb-4">
                        <div class="card-body">
                            <h6 class="card-title text-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Feedback Required
                            </h6>
                            <p class="card-text small text-muted mb-3">
                                You have <?= count($pending_feedback) ?> ticket(s) awaiting your feedback.
                            </p>
                            <?php foreach ($pending_feedback as $feedback): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small>
                                        <code>#<?= htmlspecialchars($feedback['complaint_id']) ?></code>
                                        <span class="text-muted">(<?= $feedback['days_pending'] ?> days)</span>
                                    </small>
                                    <a href="<?= Config::APP_URL ?>/customer/tickets/<?= $feedback['complaint_id'] ?>" 
                                       class="btn btn-warning btn-sm">Feedback</a>
                                </div>
                            <?php endforeach; ?>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Tickets auto-close after 3 days without feedback.
                            </small>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Quick Actions -->
                <div class="card-apple-glass mb-4">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-bolt text-apple-blue me-2"></i>
                            Quick Actions
                        </h6>
                        <div class="d-grid gap-2">
                            <a href="<?= Config::APP_URL ?>/customer/tickets/create" class="btn btn-apple-primary">
                                <i class="fas fa-plus me-2"></i>New Support Ticket
                            </a>
                            <a href="<?= Config::APP_URL ?>/customer/tickets" class="btn btn-apple-glass">
                                <i class="fas fa-list me-2"></i>View All Tickets
                            </a>
                            <a href="<?= Config::APP_URL ?>/customer/profile" class="btn btn-apple-glass">
                                <i class="fas fa-user-edit me-2"></i>Update Profile
                            </a>
                            <a href="<?= Config::APP_URL ?>/help" class="btn btn-apple-glass">
                                <i class="fas fa-question-circle me-2"></i>Help & Support
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Announcements -->
                <?php if (!empty($announcements)): ?>
                    <div class="card-apple">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-bullhorn text-apple-blue me-2"></i>
                                Announcements
                            </h6>
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($announcement['title']) ?></h6>
                                        <span class="badge badge-priority-<?= $announcement['priority'] ?> small">
                                            <?= ucfirst($announcement['priority']) ?>
                                        </span>
                                    </div>
                                    <p class="small text-muted mb-2"><?= nl2br(htmlspecialchars($announcement['content'])) ?></p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= date('M d, Y', strtotime($announcement['publish_date'])) ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- System Status -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card-apple-glass">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success rounded-circle me-3" style="width: 8px; height: 8px;"></div>
                                    <div>
                                        <h6 class="mb-0">System Status: All Services Operational</h6>
                                        <small class="text-muted">Last updated: <?= date('M d, Y H:i') ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Secure Connection • 
                                    <i class="fas fa-clock me-1"></i>
                                    Response Time: &lt;2s
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Auto-refresh notifications -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check for new notifications every 5 minutes
    setInterval(checkNotifications, 300000);
    
    // Check for pending feedback warnings
    checkFeedbackWarnings();
});

function checkNotifications() {
    fetch(APP_URL + '/api/notifications/count')
        .then(response => response.json())
        .then(data => {
            if (data.count > 0) {
                const badge = document.getElementById('notificationCount');
                if (badge) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline';
                }
            }
        })
        .catch(error => console.log('Notification check failed'));
}

function checkFeedbackWarnings() {
    const feedbackCards = document.querySelectorAll('.border-warning');
    if (feedbackCards.length > 0) {
        // Show toast reminder about pending feedback
        window.SAMPARK.ui.showToast(
            'You have pending feedback requests. Please provide feedback to close your tickets.',
            'warning'
        );
    }
}

// Real-time status updates
function updateTicketStatus(ticketId, newStatus) {
    // This would be called by WebSocket or polling for real-time updates
    const statusElements = document.querySelectorAll(`[data-ticket-id="${ticketId}"] .status-badge`);
    statusElements.forEach(element => {
        element.className = `badge badge-apple status-${newStatus.replace('_', '-')}`;
        element.textContent = newStatus.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    });
}

// Copy ticket ID to clipboard
function copyTicketId(ticketId) {
    window.SAMPARK.utils.copyToClipboard(ticketId)
        .then(() => {
            window.SAMPARK.ui.showToast('Ticket ID copied to clipboard', 'success');
        })
        .catch(err => {
            window.SAMPARK.ui.showToast('Failed to copy ticket ID', 'error');
        });
}
</script>

<style>
/* Dashboard specific styles */
.display-4 {
    font-weight: 300;
    line-height: 1.2;
}

.card-apple:hover {
    transform: translateY(-2px);
    box-shadow: var(--apple-shadow-medium);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: var(--apple-gray);
    font-size: 0.875rem;
}

.table td {
    border-color: rgba(151, 151, 151, 0.1);
    vertical-align: middle;
}

.badge-apple {
    font-size: 0.75rem;
    padding: 0.375em 0.75em;
}

/* Status color coding */
.status-pending { background: rgba(0, 136, 204, 0.1); color: var(--apple-blue); }
.status-awaiting-feedback { background: rgba(255, 193, 7, 0.1); color: #e6a700; }
.status-awaiting-info { background: rgba(255, 107, 107, 0.1); color: #dc3545; }
.status-awaiting-approval { background: rgba(156, 39, 176, 0.1); color: #9c27b0; }
.status-closed { background: rgba(76, 175, 80, 0.1); color: #4caf50; }

/* Priority color coding */
.badge-priority-normal { background: rgba(151, 151, 151, 0.1); color: var(--apple-gray); }
.badge-priority-medium { background: rgba(255, 193, 7, 0.1); color: #e6a700; }
.badge-priority-high { background: rgba(255, 107, 107, 0.1); color: #dc3545; }
.badge-priority-critical { background: rgba(220, 53, 69, 0.15); color: #dc3545; font-weight: 600; }

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .display-3 {
        font-size: 1.75rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
}

/* Animation for stats cards */
.card-apple .display-4 {
    animation: countUp 1s ease-out;
}

@keyframes countUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Pulse animation for pending feedback */
.border-warning {
    animation: pulse-warning 2s infinite;
}

@keyframes pulse-warning {
    0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
