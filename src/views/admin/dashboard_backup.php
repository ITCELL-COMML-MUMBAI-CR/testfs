<?php
/**
 * New Admin Dashboard - SAMPARK
 * Implementation based on prompt.md specifications
 * Shows comprehensive overview cards, performance metrics, and detailed analytics
 */

// Capture the content
ob_start();

// Prepare user info
$admin_name = htmlspecialchars($user['name'] ?? 'Administrator');
$user_division = $user['division'] ?? 'HQ';
$user_department = $user['department'] ?? '';

// Get current data
$current_date = date('Y-m-d');
$current_month_start = date('Y-m-01');

// Initialize data arrays if not provided
$dashboard_data = $dashboard_data ?? [];
$overview_stats = $overview_stats ?? [];
$performance_data = $performance_data ?? [];
$division_stats = $division_stats ?? [];
$terminal_stats = $terminal_stats ?? [];
$customer_registration_stats = $customer_registration_stats ?? [];
?>

<section class="py-4">
    <div class="container-xl">

        <!-- Welcome Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                    <div>
                        <h1 class="display-4 mb-2 fw-light">Controller Dashboard</h1>
                        <p class="text-muted mb-0">
                            <i class="fas fa-user-shield me-2"></i><?= $admin_name ?>
                            <?php if ($user_division !== 'HQ'): ?>
                                <span class="mx-2">•</span>
                                <i class="fas fa-building me-2"></i><?= htmlspecialchars($user_division) ?> Division
                            <?php endif; ?>
                            <span class="mx-2">•</span>
                            <i class="fas fa-calendar me-2"></i><?= date('F d, Y') ?>
                        </p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <button class="btn btn-apple-glass me-2" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh
                        </button>
                        <a href="<?= Config::getAppUrl() ?>/admin/reports" class="btn btn-apple-primary">
                            <i class="fas fa-chart-line me-2"></i>Detailed Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 1) Overview of Count of Complaints -->
        <div class="row g-4 mb-5">
            <div class="col-12">
                <h3 class="fw-semibold mb-3">
                    <i class="fas fa-chart-bar text-apple-blue me-2"></i>
                    Overview of Complaints
                </h3>
            </div>

            <!-- Total Complaints Lodged -->
            <div class="col-sm-6 col-lg-3">
                <div class="card-apple h-100 clickable-card"
                     onclick="showDetailedReport('total_complaints')">
                    <div class="card-body text-center">
                        <div class="icon-wrapper mb-3">
                            <i class="fas fa-ticket-alt fa-2x text-primary"></i>
                        </div>
                        <div class="display-5 fw-light text-primary mb-2" id="totalComplaints">
                            <?= number_format($overview_stats['total_complaints'] ?? 0) ?>
                        </div>
                        <h6 class="text-muted mb-0">Total Complaints Lodged</h6>
                        <small class="text-success">
                            <i class="fas fa-calendar me-1"></i>
                            Current Period
                        </small>
                    </div>
                </div>
            </div>

            <!-- Total Pending Complaints -->
            <div class="col-sm-6 col-lg-3">
                <div class="card-apple h-100 clickable-card"
                     onclick="showDetailedReport('pending_complaints')">
                    <div class="card-body text-center">
                        <div class="icon-wrapper mb-3">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                        <div class="display-5 fw-light text-warning mb-2" id="pendingComplaints">
                            <?= number_format($overview_stats['pending_complaints'] ?? 0) ?>
                        </div>
                        <h6 class="text-muted mb-0">Total Pending Complaints</h6>
                        <small class="text-info">
                            <i class="fas fa-hourglass-half me-1"></i>
                            Requires Action
                        </small>
                    </div>
                </div>
            </div>

            <!-- Total Closed Complaints -->
            <div class="col-sm-6 col-lg-3">
                <div class="card-apple h-100 clickable-card"
                     onclick="showDetailedReport('closed_complaints')">
                    <div class="card-body text-center">
                        <div class="icon-wrapper mb-3">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                        <div class="display-5 fw-light text-success mb-2" id="closedComplaints">
                            <?= number_format($overview_stats['closed_complaints'] ?? 0) ?>
                        </div>
                        <h6 class="text-muted mb-0">Total Closed Complaints</h6>
                        <small class="text-success">
                            <i class="fas fa-check me-1"></i>
                            Resolved Successfully
                        </small>
                    </div>
                </div>
            </div>

            <!-- SLA Breached Complaints -->
            <div class="col-sm-6 col-lg-3">
                <div class="card-apple h-100 clickable-card border-danger"
                     onclick="showDetailedReport('sla_breached')">
                    <div class="card-body text-center">
                        <div class="icon-wrapper mb-3">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        </div>
                        <div class="display-5 fw-light text-danger mb-2" id="slaBreachedComplaints">
                            <?= number_format($overview_stats['sla_breached'] ?? 0) ?>
                        </div>
                        <h6 class="text-muted mb-0">SLA Breached Complaints</h6>
                        <small class="text-danger">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            Immediate Attention
                        </small>
                    </div>
                </div>
            </div>

            <!-- Total Registered Customers -->
            <div class="col-sm-6 col-lg-3">
                <div class="card-apple h-100 clickable-card"
                     onclick="showDetailedReport('registered_customers')">
                    <div class="card-body text-center">
                        <div class="icon-wrapper mb-3">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                        <div class="display-5 fw-light text-info mb-2" id="registeredCustomers">
                            <?= number_format($overview_stats['registered_customers'] ?? 0) ?>
                        </div>
                        <h6 class="text-muted mb-0">Total Registered Customers</h6>
                        <small class="text-info">
                            <i class="fas fa-user-plus me-1"></i>
                            Active Users
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2) Performance Metrics -->
        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="card-apple h-100">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-tachometer-alt text-apple-blue me-2"></i>
                            Performance of Resolution Time
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-3">
                                <div class="metric-circle bg-success text-white mb-2">
                                    <?= round($performance_data['avg_resolution_time'] ?? 24, 1) ?>h
                                </div>
                                <small class="text-muted">Average Resolution Time</small>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <div class="metric-circle bg-info text-white mb-2">
                                    <?= round($performance_data['min_resolution_time'] ?? 2, 1) ?>h
                                </div>
                                <small class="text-muted">Minimum Resolution Time</small>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <div class="metric-circle bg-warning text-white mb-2">
                                    <?= round($performance_data['max_resolution_time'] ?? 72, 1) ?>h
                                </div>
                                <small class="text-muted">Maximum Resolution Time</small>
                            </div>
                        </div>

                        <!-- Resolution Time Progress Bar -->
                        <div class="mt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Resolution Efficiency</span>
                                <span><?= round($performance_data['resolution_efficiency'] ?? 85, 1) ?>%</span>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar bg-gradient bg-success"
                                     style="width: <?= $performance_data['resolution_efficiency'] ?? 85 ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card-apple h-100">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-smile text-apple-blue me-2"></i>
                            Customer Satisfaction
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-4 text-center">
                                <div class="satisfaction-metric excellent">
                                    <i class="fas fa-star-full"></i>
                                    <div class="count"><?= $performance_data['excellent_ratings'] ?? 0 ?></div>
                                    <small>Excellent</small>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="satisfaction-metric satisfactory">
                                    <i class="fas fa-thumbs-up"></i>
                                    <div class="count"><?= $performance_data['satisfactory_ratings'] ?? 0 ?></div>
                                    <small>Satisfactory</small>
                                </div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="satisfaction-metric unsatisfactory">
                                    <i class="fas fa-thumbs-down"></i>
                                    <div class="count"><?= $performance_data['unsatisfactory_ratings'] ?? 0 ?></div>
                                    <small>Unsatisfactory</small>
                                </div>
                            </div>
                        </div>

                        <!-- Average Rating -->
                        <div class="mt-3 text-center">
                            <div class="average-rating">
                                <span class="rating-value"><?= round($performance_data['avg_rating'] ?? 4.2, 1) ?></span>
                                <span class="rating-stars">
                                    <?php
                                    $rating = $performance_data['avg_rating'] ?? 4.2;
                                    for ($i = 1; $i <= 5; $i++):
                                    ?>
                                        <i class="fas fa-star <?= $i <= $rating ? 'text-warning' : 'text-muted' ?>"></i>
                                    <?php endfor; ?>
                                </span>
                                <small class="text-muted d-block">Average Customer Rating</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3) Division vs Status of Complaints -->
        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card-apple">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-chart-pie text-apple-blue me-2"></i>
                            Division vs Status of Complaints
                        </h4>
                        <button class="btn btn-apple-glass btn-sm" onclick="exportDivisionReport()">
                            <i class="fas fa-download me-1"></i>Export
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="divisionStatusTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Division</th>
                                        <th class="text-center">Pending</th>
                                        <th class="text-center">Awaiting Feedback</th>
                                        <th class="text-center">Awaiting Info</th>
                                        <th class="text-center">Awaiting Approval</th>
                                        <th class="text-center">Closed</th>
                                        <th class="text-center">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $grand_total = ['pending' => 0, 'awaiting_feedback' => 0, 'awaiting_info' => 0, 'awaiting_approval' => 0, 'closed' => 0, 'total' => 0];
                                    foreach ($division_stats as $division => $stats):
                                        foreach ($stats as $key => $value) {
                                            $grand_total[$key] += $value;
                                        }
                                    ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($division) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-warning clickable-count"
                                                  onclick="showDivisionDetails('<?= $division ?>', 'pending')">
                                                <?= number_format($stats['pending'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info clickable-count"
                                                  onclick="showDivisionDetails('<?= $division ?>', 'awaiting_feedback')">
                                                <?= number_format($stats['awaiting_feedback'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary clickable-count"
                                                  onclick="showDivisionDetails('<?= $division ?>', 'awaiting_info')">
                                                <?= number_format($stats['awaiting_info'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary clickable-count"
                                                  onclick="showDivisionDetails('<?= $division ?>', 'awaiting_approval')">
                                                <?= number_format($stats['awaiting_approval'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success clickable-count"
                                                  onclick="showDivisionDetails('<?= $division ?>', 'closed')">
                                                <?= number_format($stats['closed'] ?? 0) ?>
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold">
                                            <span class="clickable-count"
                                                  onclick="showDivisionDetails('<?= $division ?>', 'all')">
                                                <?= number_format($stats['total'] ?? 0) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-dark">
                                    <tr>
                                        <th>Grand Total</th>
                                        <th class="text-center"><?= number_format($grand_total['pending']) ?></th>
                                        <th class="text-center"><?= number_format($grand_total['awaiting_feedback']) ?></th>
                                        <th class="text-center"><?= number_format($grand_total['awaiting_info']) ?></th>
                                        <th class="text-center"><?= number_format($grand_total['awaiting_approval']) ?></th>
                                        <th class="text-center"><?= number_format($grand_total['closed']) ?></th>
                                        <th class="text-center"><?= number_format($grand_total['total']) ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4) Terminal and Type wise Total Complaints -->
        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="card-apple h-100">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-map-marker-alt text-apple-blue me-2"></i>
                            Terminal wise Complaints
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="terminalChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card-apple h-100">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-tags text-apple-blue me-2"></i>
                            Type wise Complaints
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="typeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5) Customer Registration by Division -->
        <div class="row g-4">
            <div class="col-12">
                <div class="card-apple">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-user-plus text-apple-blue me-2"></i>
                            Customer Registration by Division
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="height: 400px;">
                            <canvas id="customerRegistrationChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Dashboard data
const dashboardData = {
    division_stats: <?= json_encode($division_stats) ?>,
    terminal_stats: <?= json_encode($terminal_stats) ?>,
    type_stats: <?= json_encode($performance_data['type_distribution'] ?? []) ?>,
    customer_registration: <?= json_encode($customer_registration_stats) ?>
};

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    setupClickableElements();

    // Auto-refresh every 5 minutes
    setInterval(refreshDashboard, 5 * 60 * 1000);
});

function initializeCharts() {
    initializeTerminalChart();
    initializeTypeChart();
    initializeCustomerRegistrationChart();
}

function initializeTerminalChart() {
    const ctx = document.getElementById('terminalChart').getContext('2d');
    const terminalData = dashboardData.terminal_stats;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(terminalData),
            datasets: [{
                label: 'Total Complaints',
                data: Object.values(terminalData),
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Complaints by Terminal'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            onClick: function(event, elements) {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    const terminal = Object.keys(terminalData)[index];
                    showTerminalDetails(terminal);
                }
            }
        }
    });
}

function initializeTypeChart() {
    const ctx = document.getElementById('typeChart').getContext('2d');
    const typeData = dashboardData.type_stats;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(typeData),
            datasets: [{
                data: Object.values(typeData),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Complaints by Type'
                }
            },
            onClick: function(event, elements) {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    const type = Object.keys(typeData)[index];
                    showTypeDetails(type);
                }
            }
        }
    });
}

function initializeCustomerRegistrationChart() {
    const ctx = document.getElementById('customerRegistrationChart').getContext('2d');
    const registrationData = dashboardData.customer_registration;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: Object.keys(registrationData),
            datasets: [{
                label: 'Customer Registrations',
                data: Object.values(registrationData),
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Customer Registration Trends by Division'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function setupClickableElements() {
    // Make metric cards clickable
    document.querySelectorAll('.clickable-card').forEach(card => {
        card.style.cursor = 'pointer';
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 12px rgba(0,0,0,0.08)';
        });
    });
}

// Click handlers for detailed reports
function showDetailedReport(type) {
    const params = new URLSearchParams({
        type: type,
        date_from: '<?= $current_month_start ?>',
        date_to: '<?= $current_date ?>'
    });

    window.location.href = `${APP_URL}/admin/reports?${params.toString()}`;
}

function showDivisionDetails(division, status) {
    const params = new URLSearchParams({
        division: division,
        status: status !== 'all' ? status : '',
        date_from: '<?= $current_month_start ?>',
        date_to: '<?= $current_date ?>'
    });

    window.location.href = `${APP_URL}/admin/reports?${params.toString()}`;
}

function showTerminalDetails(terminal) {
    const params = new URLSearchParams({
        terminal: terminal,
        date_from: '<?= $current_month_start ?>',
        date_to: '<?= $current_date ?>'
    });

    window.location.href = `${APP_URL}/admin/reports?${params.toString()}`;
}

function showTypeDetails(type) {
    const params = new URLSearchParams({
        type: type,
        date_from: '<?= $current_month_start ?>',
        date_to: '<?= $current_date ?>'
    });

    window.location.href = `${APP_URL}/admin/reports?${params.toString()}`;
}

async function refreshDashboard() {
    const refreshBtn = document.querySelector('button[onclick="refreshDashboard()"] i');
    refreshBtn.classList.add('fa-spin');

    try {
        const response = await fetch(`${APP_URL}/api/admin/dashboard-refresh`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });

        if (response.ok) {
            location.reload();
        }
    } catch (error) {
        console.error('Error refreshing dashboard:', error);
    } finally {
        refreshBtn.classList.remove('fa-spin');
    }
}

function exportDivisionReport() {
    window.location.href = `${APP_URL}/admin/reports/export/division-status`;
}
</script>

<style>
/* Dashboard specific styles */
.card-apple {
    background: rgba(255, 255, 255, 0.98);
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.card-apple:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.clickable-card {
    cursor: pointer;
}

.icon-wrapper {
    width: 64px;
    height: 64px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(var(--bs-primary-rgb), 0.1);
}

.metric-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
    margin: 0 auto;
}

.satisfaction-metric {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 0.5rem;
}

.satisfaction-metric.excellent {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.satisfaction-metric.satisfactory {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.satisfaction-metric.unsatisfactory {
    background: linear-gradient(135deg, #dc3545, #e74c3c);
    color: white;
}

.satisfaction-metric i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.satisfaction-metric .count {
    font-size: 1.5rem;
    font-weight: bold;
}

.average-rating {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
}

.rating-value {
    font-size: 2rem;
    font-weight: bold;
    color: #495057;
}

.rating-stars {
    margin-left: 0.5rem;
}

.clickable-count {
    cursor: pointer;
    transition: all 0.2s ease;
}

.clickable-count:hover {
    transform: scale(1.1);
}

.badge.clickable-count:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.chart-container {
    position: relative;
}

/* Responsive design */
@media (max-width: 768px) {
    .display-4 {
        font-size: 1.75rem;
    }

    .card-body {
        padding: 1rem;
    }

    .metric-circle {
        width: 60px;
        height: 60px;
        font-size: 0.9rem;
    }

    .table-responsive {
        font-size: 0.875rem;
    }
}

/* Animation for counter updates */
@keyframes countUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.counter-animate {
    animation: countUp 0.6s ease;
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