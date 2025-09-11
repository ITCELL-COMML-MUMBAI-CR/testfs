<?php
/**
 * Controller Reports View - SAMPARK
 * Comprehensive reporting interface with analytics and export capabilities
 */

// Capture the content
ob_start();

// Set additional CSS for this view
$additional_css = [
    Config::APP_URL . '/assets/css/controller-views.css'
];

// Set page title
$page_title = 'Reports - SAMPARK';
?>

<div class="container-xl py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <div class="d-flex align-items-center">
                <div class="bg-apple-blue rounded-3 p-3 me-3">
                    <i class="fas fa-chart-line text-dark fa-lg"></i>
                </div>
                <div>
                    <h1 class="h3 mb-1 fw-semibold">Reports & Analytics</h1>
                    <p class="text-muted mb-0">Generate detailed reports and analyze performance metrics</p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <button class="btn btn-apple-secondary" onclick="exportReport()">
                    <i class="fas fa-download me-2"></i>Export Report
                </button>
                <button class="btn btn-apple-primary" onclick="generateReport()">
                    <i class="fas fa-sync-alt me-2"></i>Refresh Data
                </button>
            </div>
        </div>
    </div>

    <!-- Report Type Selector -->
    <div class="card card-apple mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Report Configuration
            </h5>
        </div>
        <div class="card-body">
            <form id="reportForm" onsubmit="generateReport(event)">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label-apple">Report Type</label>
                        <select class="form-control-apple" name="type" id="reportType" onchange="updateReportFields()">
                            <option value="summary" <?= $report_type === 'summary' ? 'selected' : '' ?>>Summary Report</option>
                            <option value="performance" <?= $report_type === 'performance' ? 'selected' : '' ?>>Performance Analysis</option>
                            <option value="sla" <?= $report_type === 'sla' ? 'selected' : '' ?>>SLA Compliance</option>
                            <option value="customer_satisfaction" <?= $report_type === 'customer_satisfaction' ? 'selected' : '' ?>>Customer Satisfaction</option>
                            <option value="trend" <?= $report_type === 'trend' ? 'selected' : '' ?>>Trend Analysis</option>
                            <option value="detailed" <?= $report_type === 'detailed' ? 'selected' : '' ?>>Detailed Export</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-apple">Date From</label>
                        <input type="date" class="form-control-apple" name="date_from" id="dateFrom" 
                               value="<?= $date_range['from'] ?? date('Y-m-01') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label-apple">Date To</label>
                        <input type="date" class="form-control-apple" name="date_to" id="dateTo" 
                               value="<?= $date_range['to'] ?? date('Y-m-t') ?>" required>
                    </div>
                    <?php if ($user['role'] === 'controller_nodal'): ?>
                    <div class="col-md-3">
                        <label class="form-label-apple">Division</label>
                        <select class="form-control-apple" name="division" id="division">
                            <option value="">All Divisions</option>
                            <option value="<?= htmlspecialchars($user['division']) ?>" selected>
                                <?= htmlspecialchars($user['division']) ?>
                            </option>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Additional filters based on report type -->
                <div id="additionalFilters" class="row g-3 mt-3" style="display: none;">
                    <div class="col-md-3" id="statusFilter">
                        <label class="form-label-apple">Status</label>
                        <select class="form-control-apple" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="awaiting_info">Awaiting Info</option>
                            <option value="awaiting_approval">Awaiting Approval</option>
                            <option value="awaiting_feedback">Awaiting Feedback</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="priorityFilter">
                        <label class="form-label-apple">Priority</label>
                        <select class="form-control-apple" name="priority">
                            <option value="">All Priorities</option>
                            <option value="critical">Critical</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="normal">Normal</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="categoryFilter">
                        <label class="form-label-apple">Category</label>
                        <select class="form-control-apple" name="category">
                            <option value="">All Categories</option>
                            <!-- Categories would be loaded dynamically -->
                        </select>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col">
                        <button type="submit" class="btn btn-apple-primary me-2">
                            <i class="fas fa-chart-bar me-2"></i>Generate Report
                        </button>
                        <button type="button" class="btn btn-apple-secondary" onclick="resetFilters()">
                            <i class="fas fa-times me-2"></i>Reset Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Content -->
    <div id="reportContent">
        <?php if (isset($report_data)): ?>
            <?php if ($report_type === 'summary'): ?>
                <!-- Summary Report -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card card-apple text-center">
                            <div class="card-body">
                                <div class="text-primary mb-2">
                                    <i class="fas fa-ticket-alt fa-2x"></i>
                                </div>
                                <h3 class="mb-1"><?= $report_data['total_tickets'] ?? 0 ?></h3>
                                <p class="text-muted mb-0">Total Tickets</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-apple text-center">
                            <div class="card-body">
                                <div class="text-success mb-2">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                                <h3 class="mb-1"><?= $report_data['resolved_tickets'] ?? 0 ?></h3>
                                <p class="text-muted mb-0">Resolved</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-apple text-center">
                            <div class="card-body">
                                <div class="text-warning mb-2">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                                <h3 class="mb-1"><?= $report_data['pending_tickets'] ?? 0 ?></h3>
                                <p class="text-muted mb-0">Pending</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-apple text-center">
                            <div class="card-body">
                                <div class="text-danger mb-2">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                                <h3 class="mb-1"><?= $report_data['sla_violations'] ?? 0 ?></h3>
                                <p class="text-muted mb-0">SLA Violations</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card card-apple">
                            <div class="card-header">
                                <h5 class="mb-0">Ticket Trends</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="trendsChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card card-apple">
                            <div class="card-header">
                                <h5 class="mb-0">Priority Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="priorityChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($report_type === 'performance'): ?>
                <!-- Performance Report -->
                <div class="card card-apple mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Performance Metrics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4 class="text-primary"><?= round($report_data['avg_resolution_time'] ?? 0, 1) ?>h</h4>
                                    <p class="mb-0">Average Resolution Time</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4 class="text-success"><?= round($report_data['resolution_rate'] ?? 0, 1) ?>%</h4>
                                    <p class="mb-0">Resolution Rate</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <h4 class="text-info"><?= round($report_data['customer_satisfaction'] ?? 0, 1) ?>%</h4>
                                    <p class="mb-0">Customer Satisfaction</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-apple">
                    <div class="card-header">
                        <h5 class="mb-0">Performance Trends</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceChart" height="100"></canvas>
                    </div>
                </div>

            <?php elseif ($report_type === 'sla'): ?>
                <!-- SLA Report -->
                <div class="card card-apple mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">SLA Compliance Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success bg-opacity-10 rounded-3 p-3 me-3">
                                        <i class="fas fa-check-circle text-success fa-2x"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0"><?= round($report_data['sla_compliance_rate'] ?? 0, 1) ?>%</h4>
                                        <p class="text-muted mb-0">SLA Compliance Rate</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger bg-opacity-10 rounded-3 p-3 me-3">
                                        <i class="fas fa-exclamation-circle text-danger fa-2x"></i>
                                    </div>
                                    <div>
                                        <h4 class="mb-0"><?= $report_data['total_violations'] ?? 0 ?></h4>
                                        <p class="text-muted mb-0">Total SLA Violations</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Priority Level</th>
                                        <th>SLA Target</th>
                                        <th>Average Resolution</th>
                                        <th>Compliance Rate</th>
                                        <th>Violations</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($report_data['sla_breakdown'])): ?>
                                        <?php foreach ($report_data['sla_breakdown'] as $sla): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-<?= $sla['priority_class'] ?>">
                                                    <?= ucfirst($sla['priority']) ?>
                                                </span>
                                            </td>
                                            <td><?= $sla['sla_target'] ?>h</td>
                                            <td><?= round($sla['avg_resolution'], 1) ?>h</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 100px; height: 8px;">
                                                        <div class="progress-bar bg-<?= $sla['compliance_rate'] >= 95 ? 'success' : ($sla['compliance_rate'] >= 80 ? 'warning' : 'danger') ?>" 
                                                             style="width: <?= $sla['compliance_rate'] ?>%"></div>
                                                    </div>
                                                    <?= round($sla['compliance_rate'], 1) ?>%
                                                </div>
                                            </td>
                                            <td class="<?= $sla['violations'] > 0 ? 'text-danger fw-semibold' : '' ?>">
                                                <?= $sla['violations'] ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($report_type === 'customer_satisfaction'): ?>
                <!-- Customer Satisfaction Report -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card card-apple text-center">
                            <div class="card-body">
                                <div class="text-success mb-2">
                                    <i class="fas fa-smile fa-2x"></i>
                                </div>
                                <h3 class="mb-1"><?= $report_data['excellent_ratings'] ?? 0 ?></h3>
                                <p class="text-muted mb-0">Excellent</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-apple text-center">
                            <div class="card-body">
                                <div class="text-warning mb-2">
                                    <i class="fas fa-meh fa-2x"></i>
                                </div>
                                <h3 class="mb-1"><?= $report_data['satisfactory_ratings'] ?? 0 ?></h3>
                                <p class="text-muted mb-0">Satisfactory</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-apple text-center">
                            <div class="card-body">
                                <div class="text-danger mb-2">
                                    <i class="fas fa-frown fa-2x"></i>
                                </div>
                                <h3 class="mb-1"><?= $report_data['poor_ratings'] ?? 0 ?></h3>
                                <p class="text-muted mb-0">Poor</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-apple">
                    <div class="card-header">
                        <h5 class="mb-0">Satisfaction Trends</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="satisfactionChart" height="100"></canvas>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Default empty state -->
            <div class="card card-apple">
                <div class="card-body text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-chart-bar fa-3x mb-3"></i>
                        <h5>No Report Generated</h5>
                        <p>Select your report parameters above and click "Generate Report" to view analytics.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Report generation and management
let currentChart = null;
let reportData = <?= json_encode($report_data ?? []) ?>;

document.addEventListener('DOMContentLoaded', function() {
    updateReportFields();
    if (reportData && Object.keys(reportData).length > 0) {
        initializeCharts();
    }
});

function updateReportFields() {
    const reportType = document.getElementById('reportType').value;
    const additionalFilters = document.getElementById('additionalFilters');
    
    // Show/hide additional filters based on report type
    if (reportType === 'detailed' || reportType === 'trend') {
        additionalFilters.style.display = 'flex';
    } else {
        additionalFilters.style.display = 'none';
    }
}

async function generateReport(event) {
    if (event) {
        event.preventDefault();
    }
    
    const form = document.getElementById('reportForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    for (let [key, value] of formData.entries()) {
        if (value.trim() !== '') {
            params.append(key, value);
        }
    }
    
    showLoading();
    
    try {
        // Redirect to generate new report
        window.location.href = window.location.pathname + '?' + params.toString();
    } catch (error) {
        hideLoading();
        Swal.fire('Error', 'Failed to generate report', 'error');
    }
}

function resetFilters() {
    document.getElementById('reportForm').reset();
    document.getElementById('reportType').value = 'summary';
    updateReportFields();
}

async function exportReport() {
    const form = document.getElementById('reportForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    for (let [key, value] of formData.entries()) {
        if (value.trim() !== '') {
            params.append(key, value);
        }
    }
    params.append('export', '1');
    
    try {
        // Create a temporary form for file download
        const downloadForm = document.createElement('form');
        downloadForm.method = 'POST';
        downloadForm.action = `${APP_URL}/controller/reports/export`;
        downloadForm.style.display = 'none';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = CSRF_TOKEN;
        downloadForm.appendChild(csrfInput);
        
        // Add form parameters
        for (let [key, value] of params.entries()) {
            if (key !== 'export') {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                downloadForm.appendChild(input);
            }
        }
        
        document.body.appendChild(downloadForm);
        downloadForm.submit();
        document.body.removeChild(downloadForm);
        
        Swal.fire({
            title: 'Export Started',
            text: 'Your report is being prepared for download',
            icon: 'info',
            timer: 3000,
            showConfirmButton: false
        });
    } catch (error) {
        Swal.fire('Error', 'Failed to export report', 'error');
    }
}

function initializeCharts() {
    const reportType = '<?= $report_type ?>';
    
    switch (reportType) {
        case 'summary':
            initializeSummaryCharts();
            break;
        case 'performance':
            initializePerformanceCharts();
            break;
        case 'customer_satisfaction':
            initializeSatisfactionCharts();
            break;
    }
}

function initializeSummaryCharts() {
    // Trends Chart
    const trendsCtx = document.getElementById('trendsChart');
    if (trendsCtx && reportData.trend_data) {
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: reportData.trend_data.labels || [],
                datasets: [{
                    label: 'Created',
                    data: reportData.trend_data.created || [],
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Resolved',
                    data: reportData.trend_data.resolved || [],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Ticket Creation vs Resolution Trends'
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
    
    // Priority Chart
    const priorityCtx = document.getElementById('priorityChart');
    if (priorityCtx && reportData.priority_distribution) {
        new Chart(priorityCtx, {
            type: 'doughnut',
            data: {
                labels: ['Critical', 'High', 'Medium', 'Normal'],
                datasets: [{
                    data: [
                        reportData.priority_distribution.critical || 0,
                        reportData.priority_distribution.high || 0,
                        reportData.priority_distribution.medium || 0,
                        reportData.priority_distribution.normal || 0
                    ],
                    backgroundColor: [
                        'rgb(220, 53, 69)',
                        'rgb(255, 193, 7)',
                        'rgb(54, 162, 235)',
                        'rgb(108, 117, 125)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Priority Distribution'
                    }
                }
            }
        });
    }
}

function initializePerformanceCharts() {
    const performanceCtx = document.getElementById('performanceChart');
    if (performanceCtx && reportData.performance_trend) {
        new Chart(performanceCtx, {
            type: 'bar',
            data: {
                labels: reportData.performance_trend.labels || [],
                datasets: [{
                    label: 'Resolution Time (hours)',
                    data: reportData.performance_trend.resolution_times || [],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Average Resolution Time Trend'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Hours'
                        }
                    }
                }
            }
        });
    }
}

function initializeSatisfactionCharts() {
    const satisfactionCtx = document.getElementById('satisfactionChart');
    if (satisfactionCtx && reportData.satisfaction_trend) {
        new Chart(satisfactionCtx, {
            type: 'line',
            data: {
                labels: reportData.satisfaction_trend.labels || [],
                datasets: [{
                    label: 'Satisfaction Rate (%)',
                    data: reportData.satisfaction_trend.rates || [],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Customer Satisfaction Trends'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Satisfaction Rate (%)'
                        }
                    }
                }
            }
        });
    }
}

// Print functionality
function printReport() {
    window.print();
}

// Auto-refresh functionality
function setupAutoRefresh() {
    setInterval(() => {
        generateReport();
    }, 10 * 60 * 1000); // Refresh every 10 minutes
}

// Date range presets
function setDateRange(preset) {
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const today = new Date();
    let fromDate = new Date();
    
    switch (preset) {
        case 'today':
            fromDate = new Date(today);
            break;
        case 'week':
            fromDate.setDate(today.getDate() - 7);
            break;
        case 'month':
            fromDate.setMonth(today.getMonth() - 1);
            break;
        case 'quarter':
            fromDate.setMonth(today.getMonth() - 3);
            break;
        case 'year':
            fromDate.setFullYear(today.getFullYear() - 1);
            break;
    }
    
    dateFrom.value = fromDate.toISOString().split('T')[0];
    dateTo.value = today.toISOString().split('T')[0];
}

// Utility functions
function showLoading() {
    document.getElementById('loadingOverlay')?.classList.remove('d-none');
}

function hideLoading() {
    document.getElementById('loadingOverlay')?.classList.add('d-none');
}
</script>

<style>
/* Reports page specific styles */
.card-apple {
    transition: all 0.2s ease;
}

.card-apple:hover {
    transform: translateY(-2px);
    box-shadow: var(--apple-shadow-medium);
}

/* Chart containers */
.chart-container {
    position: relative;
    height: 300px;
}

/* Metric cards */
.metric-card {
    background: linear-gradient(135deg, rgba(54, 162, 235, 0.1) 0%, transparent 100%);
    border-left: 4px solid var(--apple-primary);
}

.metric-card.success {
    background: linear-gradient(135deg, rgba(75, 192, 192, 0.1) 0%, transparent 100%);
    border-left-color: var(--bs-success);
}

.metric-card.warning {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, transparent 100%);
    border-left-color: var(--bs-warning);
}

.metric-card.danger {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, transparent 100%);
    border-left-color: var(--bs-danger);
}

/* Progress bars in tables */
.progress {
    background-color: rgba(0, 0, 0, 0.1);
}

/* Report type specific styling */
.report-summary .card {
    border-left: 4px solid var(--apple-primary);
}

.report-performance .card {
    border-left: 4px solid var(--bs-success);
}

.report-sla .card {
    border-left: 4px solid var(--bs-warning);
}

.report-satisfaction .card {
    border-left: 4px solid var(--bs-info);
}

/* Loading states */
.loading-report {
    opacity: 0.6;
    pointer-events: none;
}

.loading-report::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 2rem;
    height: 2rem;
    border: 2px solid var(--apple-primary);
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 1000;
}

@keyframes spin {
    to { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Print styles */
@media print {
    .card-header,
    .btn, .btn-group,
    .dropdown, 
    #reportForm {
        display: none !important;
    }
    
    .card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    .container-xl {
        max-width: 100% !important;
        padding: 0 !important;
    }
    
    h1, h2, h3, h4, h5 {
        color: #000 !important;
    }
    
    .table {
        font-size: 0.8rem;
    }
    
    .badge {
        border: 1px solid #ddd !important;
        color: #000 !important;
    }
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .chart-container {
        height: 250px;
    }
    
    .card-body {
        padding: 1rem 0.75rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .metric-card h3 {
        font-size: 1.5rem;
    }
    
    .btn-toolbar {
        flex-direction: column;
    }
    
    .btn-toolbar .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}

/* Animation for metric cards */
.metric-card {
    animation: slideUp 0.6s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Chart loading state */
.chart-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 200px;
    color: var(--bs-muted);
}

.chart-loading i {
    font-size: 2rem;
    margin-right: 1rem;
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>