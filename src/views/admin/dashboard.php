<?php
// Capture the content
ob_start();
?>

<!-- Admin Dashboard -->
<section class="py-apple-6">
    <div class="container-xl">
        
        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                    <div>
                        <h1 class="display-3 mb-2">Admin Dashboard</h1>
                        <p class="text-muted mb-0">
                            <i class="fas fa-user-shield me-2"></i><?= htmlspecialchars($admin_name) ?>
                            <span class="mx-2">•</span>
                            <i class="fas fa-calendar me-2"></i><?= date('F d, Y') ?>
                        </p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <a href="<?= Config::getAppUrl() ?>/admin/reports" class="btn btn-apple-glass">
                            <i class="fas fa-download me-2"></i>Download Reports
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
                        <div class="display-4 fw-light text-apple-blue mb-2"><?= $stats['total_tickets'] ?></div>
                        <h6 class="text-muted mb-0">Total Tickets</h6>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="card-apple text-center h-100">
                    <div class="card-body">
                        <div class="display-4 fw-light text-success mb-2"><?= $stats['total_customers'] ?></div>
                        <h6 class="text-muted mb-0">Total Customers</h6>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="card-apple text-center h-100">
                    <div class="card-body">
                        <div class="display-4 fw-light text-warning mb-2"><?= $stats['open_tickets'] ?></div>
                        <h6 class="text-muted mb-0">Open Tickets</h6>
                    </div>
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <div class="card-apple text-center h-100">
                    <div class="card-body">
                        <div class="display-4 fw-light text-danger mb-2"><?= $stats['critical_tickets'] ?></div>
                        <h6 class="text-muted mb-0">Critical Priority</h6>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            
            <!-- Ticket Distribution -->
            <div class="col-12 col-lg-6">
                <div class="card-apple h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-chart-pie text-apple-blue me-2"></i>
                                Ticket Distribution
                            </h4>
                            <div class="dropdown">
                                <button class="btn btn-apple-glass btn-sm dropdown-toggle" type="button" id="timeRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    Last 30 Days
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="timeRangeDropdown">
                                    <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                                    <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                                    <li><a class="dropdown-item" href="#">Last 90 Days</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#">Custom Range</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="chart-container" style="position: relative; height:300px; width:100%">
                            <canvas id="ticketDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Response Time Trends -->
            <div class="col-12 col-lg-6">
                <div class="card-apple h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-chart-line text-apple-blue me-2"></i>
                                Response Time Trends
                            </h4>
                            <div class="dropdown">
                                <button class="btn btn-apple-glass btn-sm dropdown-toggle" type="button" id="trendTimeRange" data-bs-toggle="dropdown" aria-expanded="false">
                                    Last 30 Days
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="trendTimeRange">
                                    <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                                    <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                                    <li><a class="dropdown-item" href="#">Last 90 Days</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#">Custom Range</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="chart-container" style="position: relative; height:300px; width:100%">
                            <canvas id="responseTimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Tickets Table -->
            <div class="col-12">
                <div class="card-apple">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-ticket-alt text-apple-blue me-2"></i>
                                Recent Tickets
                            </h4>
                            <a href="<?= Config::getAppUrl() ?>/admin/tickets" class="btn btn-apple-glass btn-sm">
                                <i class="fas fa-list me-1"></i>View All Tickets
                            </a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="recentTicketsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Customer</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Assigned To</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_tickets as $ticket): ?>
                                        <tr>
                                            <td>
                                                <code class="text-apple-blue">#<?= htmlspecialchars($ticket['complaint_id']) ?></code>
                                            </td>
                                            <td>
                                                <div class="fw-medium"><?= htmlspecialchars($ticket['customer_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($ticket['company_name']) ?></small>
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
                                                <?php if (!empty($ticket['assigned_to'])): ?>
                                                    <div class="fw-medium"><?= htmlspecialchars($ticket['assigned_to']) ?></div>
                                                <?php else: ?>
                                                    <span class="text-muted">Unassigned</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?= date('M d, Y', strtotime($ticket['created_at'])) ?></small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-apple-glass btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="<?= Config::getAppUrl() ?>/admin/tickets/<?= $ticket['complaint_id'] ?>">
                                                                <i class="fas fa-eye me-2"></i>View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="<?= Config::getAppUrl() ?>/admin/tickets/<?= $ticket['complaint_id'] ?>/edit">
                                                                <i class="fas fa-edit me-2"></i>Edit Ticket
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="<?= Config::getAppUrl() ?>/admin/tickets/<?= $ticket['complaint_id'] ?>/assign">
                                                                <i class="fas fa-user-plus me-2"></i>Assign Ticket
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button class="dropdown-item text-danger" onclick="confirmCloseTicket('<?= $ticket['complaint_id'] ?>')">
                                                                <i class="fas fa-times-circle me-2"></i>Close Ticket
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Access and System Status Row -->
            <div class="col-12 col-lg-6">
                <!-- Quick Access -->
                <div class="card-apple mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-3">
                            <i class="fas fa-bolt text-apple-blue me-2"></i>
                            Quick Access
                        </h4>
                        <div class="row g-3">
                            <div class="col-6 col-sm-4">
                                <a href="<?= Config::getAppUrl() ?>/admin/users" class="text-decoration-none">
                                    <div class="card-apple-glass text-center py-3">
                                        <i class="fas fa-users text-primary mb-2" style="font-size: 1.5rem;"></i>
                                        <h6 class="mb-0">Users</h6>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6 col-sm-4">
                                <a href="<?= Config::getAppUrl() ?>/admin/customers" class="text-decoration-none">
                                    <div class="card-apple-glass text-center py-3">
                                        <i class="fas fa-building text-success mb-2" style="font-size: 1.5rem;"></i>
                                        <h6 class="mb-0">Customers</h6>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6 col-sm-4">
                                <a href="<?= Config::getAppUrl() ?>/admin/categories" class="text-decoration-none">
                                    <div class="card-apple-glass text-center py-3">
                                        <i class="fas fa-tags text-warning mb-2" style="font-size: 1.5rem;"></i>
                                        <h6 class="mb-0">Categories</h6>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6 col-sm-4">
                                <a href="<?= Config::getAppUrl() ?>/admin/reports" class="text-decoration-none">
                                    <div class="card-apple-glass text-center py-3">
                                        <i class="fas fa-chart-bar text-info mb-2" style="font-size: 1.5rem;"></i>
                                        <h6 class="mb-0">Reports</h6>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6 col-sm-4">
                                <a href="<?= Config::getAppUrl() ?>/admin/content" class="text-decoration-none">
                                    <div class="card-apple-glass text-center py-3">
                                        <i class="fas fa-edit text-secondary mb-2" style="font-size: 1.5rem;"></i>
                                        <h6 class="mb-0">Content</h6>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6 col-sm-4">
                                <a href="<?= Config::getAppUrl() ?>/admin/settings" class="text-decoration-none">
                                    <div class="card-apple-glass text-center py-3">
                                        <i class="fas fa-cog text-dark mb-2" style="font-size: 1.5rem;"></i>
                                        <h6 class="mb-0">Settings</h6>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-lg-6">
                <!-- System Health -->
                <div class="card-apple mb-4">
                    <div class="card-body">
                        <h4 class="card-title mb-3">
                            <i class="fas fa-heartbeat text-apple-blue me-2"></i>
                            System Health
                        </h4>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div>Server Load</div>
                                    <div class="small text-success">Optimal (25%)</div>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div>Memory Usage</div>
                                    <div class="small text-success">Good (42%)</div>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 42%" aria-valuenow="42" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div>Database Performance</div>
                                    <div class="small text-success">Excellent (92%)</div>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 92%" aria-valuenow="92" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div>Storage Space</div>
                                    <div class="small text-warning">Moderate (68%)</div>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 68%" aria-valuenow="68" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <div class="col-12 mt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="small text-muted">Last checked: <?= date('M d, Y H:i') ?></div>
                                    <button class="btn btn-sm btn-apple-glass" onclick="refreshSystemHealth()">
                                        <i class="fas fa-sync-alt me-1"></i>Refresh
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Include Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#recentTicketsTable').DataTable({
        pageLength: 5,
        lengthMenu: [5, 10, 25],
        responsive: true,
        dom: '<"top"lf>rt<"bottom"ip><"clear">',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search tickets..."
        }
    });
    
    // Ticket Distribution Chart
    const ticketCtx = document.getElementById('ticketDistributionChart').getContext('2d');
    const ticketDistributionChart = new Chart(ticketCtx, {
        type: 'pie',
        data: {
            labels: ['Pending', 'In Progress', 'Awaiting Info', 'Awaiting Feedback', 'Closed'],
            datasets: [{
                data: <?= json_encode($chart_data['ticket_distribution']) ?>,
                backgroundColor: [
                    'rgba(255, 193, 7, 0.7)',  // warning - pending
                    'rgba(0, 123, 255, 0.7)',  // primary - in progress
                    'rgba(108, 117, 125, 0.7)', // secondary - awaiting info
                    'rgba(23, 162, 184, 0.7)',  // info - awaiting feedback
                    'rgba(40, 167, 69, 0.7)',   // success - closed
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Response Time Trends Chart
    const responseCtx = document.getElementById('responseTimeChart').getContext('2d');
    const responseTimeChart = new Chart(responseCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_data['response_time_labels']) ?>,
            datasets: [{
                label: 'Response Time (hours)',
                data: <?= json_encode($chart_data['response_time_data']) ?>,
                fill: false,
                borderColor: 'rgba(0, 123, 255, 1)',
                tension: 0.4,
                pointBackgroundColor: 'rgba(0, 123, 255, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Hours'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});

function confirmCloseTicket(ticketId) {
    Swal.fire({
        title: 'Close Ticket',
        text: `Are you sure you want to close ticket #${ticketId}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, close it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Send AJAX request to close the ticket
            fetch(`${APP_URL}/admin/tickets/${ticketId}/close`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Closed!', 'The ticket has been closed.', 'success');
                    // Refresh the page or update the table
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Failed to close the ticket.', 'error');
            });
        }
    });
}

function refreshSystemHealth() {
    // Show loading indicator
    const refreshBtn = document.querySelector('button i.fa-sync-alt');
    refreshBtn.classList.add('fa-spin');
    
    // Simulate API call to refresh system health data
    setTimeout(() => {
        // Stop loading indicator
        refreshBtn.classList.remove('fa-spin');
        
        // Show success message
        window.SAMPARK.ui.showToast('System health data refreshed', 'success');
    }, 1500);
}
</script>

<style>
/* Admin Dashboard specific styles */
.display-4 {
    font-weight: 300;
    line-height: 1.2;
}

.card-apple {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card-apple:hover {
    transform: translateY(-2px);
    box-shadow: var(--apple-shadow-medium);
}

.card-apple-glass:hover {
    background-color: rgba(255, 255, 255, 0.8);
}

/* DataTables customization */
.dataTables_wrapper .dataTables_length, 
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 1rem;
}

.dataTables_wrapper .dataTables_length select {
    padding: 0.375rem 1.75rem 0.375rem 0.75rem;
    border-radius: var(--apple-radius-small);
    border: 1px solid rgba(0,0,0,0.1);
}

.dataTables_wrapper .dataTables_filter input {
    border-radius: var(--apple-radius-small);
    border: 1px solid rgba(0,0,0,0.1);
    padding: 0.375rem 0.75rem;
    margin-left: 0.5rem;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.375rem 0.75rem;
    border-radius: var(--apple-radius-small);
    border: none !important;
    background: transparent !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: var(--apple-off-white) !important;
    color: var(--apple-blue) !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: var(--apple-blue) !important;
    color: white !important;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .display-3 {
        font-size: 1.75rem;
    }
    
    .chart-container {
        height: 250px !important;
    }
    
    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
}
</style>

<?php
$additional_css = [
    Config::getAppUrl() . '/libs/datatables/dataTables.bootstrap5.min.css'
];

$additional_js = [
    Config::getAppUrl() . '/libs/datatables/jquery.dataTables.min.js',
    Config::getAppUrl() . '/libs/datatables/dataTables.bootstrap5.min.js'
];

$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
