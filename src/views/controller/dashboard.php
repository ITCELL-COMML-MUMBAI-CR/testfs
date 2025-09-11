<?php
/**
 * Controller Dashboard View - SAMPARK
 * Main dashboard for controller users with comprehensive analytics and quick actions
 */

// Capture the content
ob_start();

// Set additional CSS for this view
$additional_css = [
    Config::APP_URL . '/assets/css/controller-views.css'
];

// Set page title
$page_title = 'Controller Dashboard - SAMPARK';
?>

<div class="container-xl py-4">
    <!-- Welcome Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <div class="d-flex align-items-center">
                <div class="bg-apple-blue rounded-3 p-3 me-3">
                    <i class="fas fa-tachometer-alt text-dark fa-lg"></i>
                </div>
                <div>
                    <h1 class="h3 mb-1 fw-semibold">
                        Good <?= date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening') ?>, 
                        <?= htmlspecialchars($user['name'] ?? 'Controller') ?>
                    </h1>
                    <p class="text-muted mb-0">
                        Welcome to your controller dashboard • 
                        <span class="badge bg-<?= $user_role === 'controller_nodal' ? 'success' : 'primary' ?>">
                            <?= ucwords(str_replace('_', ' ', $user_role)) ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <button class="btn btn-apple-secondary" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                </button>
                <div class="dropdown">
                    <button class="btn btn-apple-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-plus me-2"></i>Quick Actions
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= Config::APP_URL ?>/controller/tickets">
                            <i class="fas fa-list me-2"></i>View All Tickets
                        </a></li>
                        <li><a class="dropdown-item" href="<?= Config::APP_URL ?>/controller/reports">
                            <i class="fas fa-chart-line me-2"></i>Generate Report
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= Config::APP_URL ?>/controller/help">
                            <i class="fas fa-question-circle me-2"></i>Help & Support
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card card-apple h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fas fa-clock text-primary fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small mb-1">Pending Tickets</div>
                            <div class="h2 mb-0 fw-bold" id="pendingTicketsCount">
                                <?= $ticket_stats['pending'] ?? 0 ?>
                            </div>
                            <div class="small">
                                <span class="text-success">
                                    <i class="fas fa-arrow-down me-1"></i>-12%
                                </span>
                                from yesterday
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-apple h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fas fa-exclamation-triangle text-warning fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small mb-1">High Priority</div>
                            <div class="h2 mb-0 fw-bold" id="highPriorityCount">
                                <?= $ticket_stats['high_priority_count'] ?? 0 ?>
                            </div>
                            <div class="small">
                                <span class="text-danger">
                                    <i class="fas fa-arrow-up me-1"></i>+5%
                                </span>
                                from yesterday
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-apple h-100 border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fas fa-clock text-danger fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small mb-1">SLA Violations</div>
                            <div class="h2 mb-0 fw-bold" id="slaViolationsCount">
                                <?= $ticket_stats['sla_violations'] ?? 0 ?>
                            </div>
                            <div class="small">
                                <span class="text-success">
                                    <i class="fas fa-arrow-down me-1"></i>-8%
                                </span>
                                from yesterday
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card card-apple h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small mb-1">Resolved Today</div>
                            <div class="h2 mb-0 fw-bold" id="resolvedTodayCount">
                                <?= $performance_metrics['total_handled'] ?? 0 ?>
                            </div>
                            <div class="small">
                                <span class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>+15%
                                </span>
                                from yesterday
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content Column -->
        <div class="col-lg-8">
            <!-- Pending Tickets -->
            <div class="card card-apple mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2 text-primary"></i>
                        Priority Tickets Requiring Action
                    </h5>
                    <a href="<?= Config::APP_URL ?>/controller/tickets?status=pending" class="btn btn-sm btn-apple-primary">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Ticket</th>
                                    <th class="border-0">Priority</th>
                                    <th class="border-0">Customer</th>
                                    <th class="border-0">Category</th>
                                    <th class="border-0">Hours</th>
                                    <th class="border-0">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pending_tickets)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                                            <div>Great job! No pending tickets.</div>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($pending_tickets, 0, 8) as $ticket): ?>
                                    <tr class="ticket-row" data-ticket-id="<?= $ticket['complaint_id'] ?>">
                                        <td>
                                            <a href="<?= Config::APP_URL ?>/controller/tickets/<?= $ticket['complaint_id'] ?>" 
                                               class="fw-semibold text-decoration-none">
                                                #<?= $ticket['complaint_id'] ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            $priorityClass = [
                                                'critical' => 'danger',
                                                'high' => 'warning', 
                                                'medium' => 'info',
                                                'normal' => 'secondary'
                                            ][$ticket['priority']] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $priorityClass ?>">
                                                <?= ucfirst($ticket['priority']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars($ticket['customer_name'] ?? 'N/A') ?></div>
                                        </td>
                                        <td>
                                            <div><?= htmlspecialchars($ticket['category'] ?? 'N/A') ?></div>
                                            <?php if ($ticket['subtype']): ?>
                                            <small class="text-muted"><?= htmlspecialchars($ticket['subtype']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="<?= $ticket['hours_elapsed'] > 24 ? 'text-danger fw-semibold' : 'text-muted' ?>">
                                                <?= $ticket['hours_elapsed'] ?>h
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?= Config::APP_URL ?>/controller/tickets/<?= $ticket['complaint_id'] ?>" 
                                                   class="btn btn-sm btn-apple-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-sm btn-apple-secondary" 
                                                        onclick="quickReply(<?= $ticket['complaint_id'] ?>)">
                                                    <i class="fas fa-reply"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SLA Violations -->
            <?php if (!empty($sla_violations)): ?>
            <div class="card card-apple mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        SLA Violations - Immediate Action Required
                    </h5>
                    <span class="badge bg-danger"><?= count($sla_violations) ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Ticket</th>
                                    <th class="border-0">Customer</th>
                                    <th class="border-0">Priority</th>
                                    <th class="border-0">Overdue By</th>
                                    <th class="border-0">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sla_violations as $violation): ?>
                                <tr class="table-danger">
                                    <td>
                                        <a href="<?= Config::APP_URL ?>/controller/tickets/<?= $violation['complaint_id'] ?>" 
                                           class="fw-semibold text-decoration-none">
                                            #<?= $violation['complaint_id'] ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($violation['customer_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-danger">
                                            <?= ucfirst($violation['priority']) ?>
                                        </span>
                                    </td>
                                    <td class="text-danger fw-semibold">
                                        <?= $violation['hours_overdue'] ?>h overdue
                                    </td>
                                    <td>
                                        <a href="<?= Config::APP_URL ?>/controller/tickets/<?= $violation['complaint_id'] ?>" 
                                           class="btn btn-sm btn-danger">
                                            <i class="fas fa-exclamation-circle me-1"></i>Resolve Now
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Activity -->
            <div class="card card-apple">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="activity-timeline">
                        <?php if (empty($recent_activities)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <div>No recent activity</div>
                        </div>
                        <?php else: ?>
                            <?php foreach ($recent_activities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <?php
                                    $actionIcons = [
                                        'ticket_created' => 'fa-plus-circle text-success',
                                        'ticket_forwarded' => 'fa-share text-info',
                                        'ticket_replied' => 'fa-reply text-primary',
                                        'ticket_approved' => 'fa-check-circle text-success',
                                        'ticket_rejected' => 'fa-times-circle text-danger',
                                        'ticket_closed' => 'fa-check text-success'
                                    ];
                                    $iconClass = $actionIcons[$activity['action']] ?? 'fa-circle text-muted';
                                    ?>
                                    <i class="fas <?= $iconClass ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-description">
                                        <?= htmlspecialchars($activity['description']) ?>
                                    </div>
                                    <div class="activity-meta">
                                        <span class="text-muted">
                                            <?= date('M d, Y H:i', strtotime($activity['created_at'])) ?>
                                        </span>
                                        <?php if ($activity['complaint_id']): ?>
                                        <a href="<?= Config::APP_URL ?>/controller/tickets/<?= $activity['complaint_id'] ?>" 
                                           class="ms-2 text-decoration-none">
                                            View Ticket
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Performance Metrics -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Performance (Last 30 Days)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Resolution Time</span>
                            <span class="fw-semibold">
                                <?= round($performance_metrics['avg_resolution_hours'] ?? 0, 1) ?>h avg
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-apple-blue" 
                                 style="width: <?= min(100, (($performance_metrics['avg_resolution_hours'] ?? 0) / 48) * 100) ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Customer Satisfaction</span>
                            <span class="fw-semibold">
                                <?php
                                $totalRatings = ($performance_metrics['excellent_ratings'] ?? 0) + 
                                              ($performance_metrics['satisfactory_ratings'] ?? 0) + 
                                              ($performance_metrics['unsatisfactory_ratings'] ?? 0);
                                $satisfactionRate = $totalRatings > 0 ? 
                                    round((($performance_metrics['excellent_ratings'] ?? 0) + 
                                           ($performance_metrics['satisfactory_ratings'] ?? 0)) / $totalRatings * 100) : 0;
                                ?>
                                <?= $satisfactionRate ?>%
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: <?= $satisfactionRate ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="fw-bold text-success"><?= $performance_metrics['excellent_ratings'] ?? 0 ?></div>
                            <small class="text-muted">Excellent</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-warning"><?= $performance_metrics['satisfactory_ratings'] ?? 0 ?></div>
                            <small class="text-muted">Good</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-danger"><?= $performance_metrics['unsatisfactory_ratings'] ?? 0 ?></div>
                            <small class="text-muted">Poor</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= Config::APP_URL ?>/controller/tickets?status=pending" 
                           class="btn btn-apple-primary">
                            <i class="fas fa-clock me-2"></i>View Pending Tickets
                        </a>
                        <a href="<?= Config::APP_URL ?>/controller/tickets?priority=critical" 
                           class="btn btn-apple-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>Critical Priority
                        </a>
                        <a href="<?= Config::APP_URL ?>/controller/tickets?status=awaiting_approval" 
                           class="btn btn-apple-warning">
                            <i class="fas fa-check-circle me-2"></i>Awaiting Approval
                        </a>
                        <a href="<?= Config::APP_URL ?>/controller/reports" 
                           class="btn btn-apple-secondary">
                            <i class="fas fa-chart-line me-2"></i>Generate Report
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-server me-2"></i>System Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span>System Health</span>
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Operational
                        </span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span>Database</span>
                        <span class="badge bg-success">
                            <i class="fas fa-database me-1"></i>Connected
                        </span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span>Email Service</span>
                        <span class="badge bg-success">
                            <i class="fas fa-envelope me-1"></i>Active
                        </span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span>File Storage</span>
                        <span class="badge bg-success">
                            <i class="fas fa-hdd me-1"></i>Available
                        </span>
                    </div>
                </div>
            </div>

            <!-- Escalated Tickets (if any) -->
            <?php if (!empty($escalated_tickets)): ?>
            <div class="card card-apple">
                <div class="card-header">
                    <h5 class="mb-0 text-warning">
                        <i class="fas fa-arrow-up me-2"></i>Escalated Tickets
                    </h5>
                </div>
                <div class="card-body">
                    <?php foreach ($escalated_tickets as $escalated): ?>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <a href="<?= Config::APP_URL ?>/controller/tickets/<?= $escalated['complaint_id'] ?>" 
                               class="fw-semibold text-decoration-none">
                                #<?= $escalated['complaint_id'] ?>
                            </a>
                            <div class="small text-muted">
                                <?= htmlspecialchars($escalated['customer_name'] ?? 'N/A') ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="small text-warning">
                                <?= $escalated['hours_since_escalation'] ?>h ago
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Reply Modal -->
<div class="modal fade" id="quickReplyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Reply - Ticket #<span id="replyTicketId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickReplyForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-apple">Reply Message</label>
                        <textarea class="form-control-apple" name="reply" rows="4" required 
                                  placeholder="Enter your reply to the customer..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Action Taken</label>
                        <textarea class="form-control-apple" name="action_taken" rows="3" required 
                                  placeholder="Describe the action taken to resolve this issue..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-apple-primary">
                        <i class="fas fa-paper-plane me-2"></i>Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Dashboard JavaScript functionality
let dashboardData = {};

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    setupAutoRefresh();
});

function initializeDashboard() {
    loadDashboardStats();
    updateLastRefresh();
}

async function loadDashboardStats() {
    try {
        const response = await fetch(`${APP_URL}/api/dashboard/stats`);
        if (response.ok) {
            dashboardData = await response.json();
            updateDashboardUI();
        }
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
    }
}

function updateDashboardUI() {
    // Update counters with animation
    animateCounter('pendingTicketsCount', dashboardData.pending || 0);
    animateCounter('highPriorityCount', dashboardData.high_priority || 0);
    animateCounter('slaViolationsCount', dashboardData.sla_violations || 0);
    animateCounter('resolvedTodayCount', dashboardData.resolved_today || 0);
}

function animateCounter(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const startValue = parseInt(element.textContent) || 0;
    const duration = 1000;
    const startTime = Date.now();
    
    function updateCounter() {
        const elapsed = Date.now() - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const currentValue = Math.round(startValue + (targetValue - startValue) * progress);
        
        element.textContent = currentValue.toLocaleString();
        
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        }
    }
    
    updateCounter();
}

function setupAutoRefresh() {
    // Refresh dashboard every 5 minutes
    setInterval(() => {
        loadDashboardStats();
    }, 5 * 60 * 1000);
}

function refreshDashboard() {
    showLoading();
    loadDashboardStats().then(() => {
        hideLoading();
        updateLastRefresh();
        
        // Show success message
        Swal.fire({
            title: 'Dashboard Refreshed',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    });
}

function updateLastRefresh() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', { 
        hour12: true, 
        hour: 'numeric', 
        minute: '2-digit' 
    });
    
    // Update last refresh indicator if it exists
    const refreshIndicator = document.getElementById('lastRefresh');
    if (refreshIndicator) {
        refreshIndicator.textContent = `Last updated: ${timeString}`;
    }
}

// Quick reply functionality
function quickReply(ticketId) {
    document.getElementById('replyTicketId').textContent = ticketId;
    document.getElementById('quickReplyForm').dataset.ticketId = ticketId;
    new bootstrap.Modal(document.getElementById('quickReplyModal')).show();
}

document.getElementById('quickReplyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const ticketId = this.dataset.ticketId;
    const formData = new FormData(this);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/reply`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            Swal.fire('Success', result.message, 'success').then(() => {
                bootstrap.Modal.getInstance(document.getElementById('quickReplyModal')).hide();
                // Refresh the dashboard data
                loadDashboardStats();
            });
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    } catch (error) {
        hideLoading();
        Swal.fire('Error', 'Failed to send reply', 'error');
    }
});

// Utility functions
function showLoading() {
    document.getElementById('loadingOverlay')?.classList.remove('d-none');
}

function hideLoading() {
    document.getElementById('loadingOverlay')?.classList.add('d-none');
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Alt + R to refresh
    if (e.altKey && e.key === 'r') {
        e.preventDefault();
        refreshDashboard();
    }
    
    // Alt + T to go to tickets
    if (e.altKey && e.key === 't') {
        e.preventDefault();
        window.location.href = `${APP_URL}/controller/tickets`;
    }
});
</script>

<style>
/* Dashboard specific styles */
.card-apple {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.card-apple:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

/* Metric cards with gradient backgrounds */
.card.border-start.border-primary {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, transparent 100%);
}

.card.border-start.border-warning {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.05) 0%, transparent 100%);
}

.card.border-start.border-danger {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.05) 0%, transparent 100%);
}

.card.border-start.border-success {
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.05) 0%, transparent 100%);
}

/* Activity timeline */
.activity-timeline {
    position: relative;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
    position: relative;
}

.activity-item:last-child {
    margin-bottom: 0;
}

.activity-icon {
    width: 2.5rem;
    height: 2.5rem;
    background: white;
    border: 2px solid #dee2e6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
    position: relative;
    z-index: 2;
}

.activity-content {
    flex: 1;
    padding-top: 0.25rem;
}

.activity-description {
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.activity-meta {
    font-size: 0.875rem;
}

.activity-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 1.25rem;
    top: 2.5rem;
    width: 2px;
    height: calc(100% - 1.5rem);
    background: #dee2e6;
    z-index: 1;
}

/* Table enhancements */
.table-hover tbody tr:hover {
    background-color: rgba(var(--apple-primary-rgb), 0.04);
}

.ticket-row {
    cursor: pointer;
    transition: all 0.2s ease;
}

.ticket-row:hover {
    background-color: rgba(var(--apple-primary-rgb), 0.06);
    transform: scale(1.01);
}

/* Progress bars */
.progress {
    background-color: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.6s ease;
    border-radius: 10px;
}

/* Badge animations */
.badge.bg-danger {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

/* Responsive improvements */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem 0.75rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .activity-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .activity-icon {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
    
    .activity-item::after {
        display: none;
    }
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Counter animation */
@keyframes countUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.counter-animate {
    animation: countUp 0.5s ease;
}

/* Dark mode support (future enhancement) */
@media (prefers-color-scheme: dark) {
    .card-apple {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .table-light th {
        background: rgba(255, 255, 255, 0.05);
        color: rgba(255, 255, 255, 0.9);
    }
}

/* Print styles */
@media print {
    .btn, .btn-group, .btn-toolbar,
    .dropdown, .modal {
        display: none !important;
    }
    
    .card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    .col-lg-4 {
        page-break-before: always;
    }
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>