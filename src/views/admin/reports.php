<?php
/**
 * Admin Reports View - SAMPARK
 * Comprehensive reporting and analytics for administrators
 */
$is_logged_in = true;
$user_role = $user['role'] ?? 'admin';
$user_name = $user['name'] ?? 'Administrator';
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
                    <h1 class="h3 mb-1 fw-semibold">System Reports</h1>
                    <p class="text-muted mb-0">Comprehensive analytics and reporting dashboard</p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <button class="btn btn-apple-secondary" onclick="scheduleReport()">
                    <i class="fas fa-clock me-2"></i>Schedule
                </button>
                <button class="btn btn-apple-primary" onclick="exportDashboard()">
                    <i class="fas fa-download me-2"></i>Export All
                </button>
            </div>
        </div>
    </div>

    <!-- Key Metrics Dashboard -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card card-apple border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fas fa-ticket-alt text-primary fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small mb-1">Total Tickets</div>
                            <div class="h2 mb-0 fw-bold">1,247</div>
                            <div class="small">
                                <span class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>+12%
                                </span>
                                this month
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card card-apple border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small mb-1">Resolution Rate</div>
                            <div class="h2 mb-0 fw-bold">94.5%</div>
                            <div class="small">
                                <span class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>+2.3%
                                </span>
                                this month
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card card-apple border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fas fa-clock text-warning fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small mb-1">Avg Resolution</div>
                            <div class="h2 mb-0 fw-bold">18.4h</div>
                            <div class="small">
                                <span class="text-success">
                                    <i class="fas fa-arrow-down me-1"></i>-4.2h
                                </span>
                                this month
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card card-apple border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fas fa-users text-info fa-lg"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small mb-1">Active Users</div>
                            <div class="h2 mb-0 fw-bold">834</div>
                            <div class="small">
                                <span class="text-success">
                                    <i class="fas fa-arrow-up me-1"></i>+7%
                                </span>
                                this month
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="card card-apple mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Report Filters
            </h5>
        </div>
        <div class="card-body">
            <form id="reportFilters" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label-apple">Date From</label>
                    <input type="date" class="form-control-apple" name="date_from" value="<?= date('Y-m-01') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label-apple">Date To</label>
                    <input type="date" class="form-control-apple" name="date_to" value="<?= date('Y-m-t') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label-apple">Division</label>
                    <select class="form-control-apple" name="division">
                        <option value="">All Divisions</option>
                        <option value="Northern">Northern</option>
                        <option value="Southern">Southern</option>
                        <option value="Eastern">Eastern</option>
                        <option value="Western">Western</option>
                        <option value="Central">Central</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label-apple">Zone</label>
                    <select class="form-control-apple" name="zone">
                        <option value="">All Zones</option>
                        <option value="NR">Northern Railway</option>
                        <option value="SR">Southern Railway</option>
                        <option value="ER">Eastern Railway</option>
                        <option value="WR">Western Railway</option>
                        <option value="CR">Central Railway</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label-apple">Category</label>
                    <select class="form-control-apple" name="category">
                        <option value="">All Categories</option>
                        <option value="complaint">Complaints</option>
                        <option value="query">Queries</option>
                        <option value="suggestion">Suggestions</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="d-flex align-items-end h-100">
                        <button type="submit" class="btn btn-apple-primary w-100">
                            <i class="fas fa-search me-2"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Grid -->
    <div class="row g-4">
        <!-- Ticket Analytics -->
        <div class="col-lg-8">
            <div class="card card-apple">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Ticket Analytics</h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-apple-secondary" onclick="changeChartType('line')">Line</button>
                        <button class="btn btn-apple-secondary" onclick="changeChartType('bar')">Bar</button>
                        <button class="btn btn-apple-secondary" onclick="changeChartType('area')">Area</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="ticketAnalyticsChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="col-lg-4">
            <div class="card card-apple">
                <div class="card-header">
                    <h5 class="mb-0">Performance Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-4">
                            <div class="bg-primary bg-opacity-10 rounded-3 p-3 mb-2">
                                <i class="fas fa-tachometer-alt text-primary fa-lg"></i>
                            </div>
                            <div class="fw-bold">98.2%</div>
                            <small class="text-muted">System Uptime</small>
                        </div>
                        <div class="col-4">
                            <div class="bg-success bg-opacity-10 rounded-3 p-3 mb-2">
                                <i class="fas fa-smile text-success fa-lg"></i>
                            </div>
                            <div class="fw-bold">4.6/5</div>
                            <small class="text-muted">Satisfaction</small>
                        </div>
                        <div class="col-4">
                            <div class="bg-warning bg-opacity-10 rounded-3 p-3 mb-2">
                                <i class="fas fa-clock text-warning fa-lg"></i>
                            </div>
                            <div class="fw-bold">2.3h</div>
                            <small class="text-muted">First Response</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>SLA Compliance</span>
                            <span>92%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: 92%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Resolution Efficiency</span>
                            <span>87%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: 87%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Customer Retention</span>
                            <span>94%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: 94%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Division Comparison -->
        <div class="col-lg-6">
            <div class="card card-apple">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Division Performance</h5>
                    <button class="btn btn-sm btn-apple-secondary" onclick="exportDivisionReport()">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
                <div class="card-body">
                    <canvas id="divisionChart" height="150"></canvas>
                </div>
            </div>
        </div>

        <!-- Category Distribution -->
        <div class="col-lg-6">
            <div class="card card-apple">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Category Distribution</h5>
                    <button class="btn btn-sm btn-apple-secondary" onclick="exportCategoryReport()">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="150"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-6">
            <div class="card card-apple">
                <div class="card-header">
                    <h5 class="mb-0">Recent System Activity</h5>
                </div>
                <div class="card-body">
                    <div class="activity-timeline">
                        <div class="activity-item">
                            <div class="activity-icon bg-success">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">New user registered</div>
                                <div class="activity-meta">Customer from Delhi Division • 2 minutes ago</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon bg-primary">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Ticket #1248 created</div>
                                <div class="activity-meta">High priority complaint • 15 minutes ago</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon bg-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">SLA violation alert</div>
                                <div class="activity-meta">Ticket #1205 overdue • 23 minutes ago</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon bg-info">
                                <i class="fas fa-sync"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">System backup completed</div>
                                <div class="activity-meta">Daily backup successful • 1 hour ago</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performing Controllers -->
        <div class="col-lg-6">
            <div class="card card-apple">
                <div class="card-header">
                    <h5 class="mb-0">Top Performing Controllers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-gold rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 32px; height: 32px;">
                                                <i class="fas fa-crown text-warning"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">Rajesh Kumar</div>
                                                <small class="text-muted">Northern Division</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-semibold text-success">98.5%</div>
                                        <small class="text-muted">Resolution Rate</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-silver rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 32px; height: 32px;">
                                                <i class="fas fa-medal text-secondary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">Priya Sharma</div>
                                                <small class="text-muted">Central Division</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-semibold text-success">97.2%</div>
                                        <small class="text-muted">Resolution Rate</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-bronze rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 32px; height: 32px;">
                                                <i class="fas fa-award text-warning"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">Amit Singh</div>
                                                <small class="text-muted">Western Division</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-semibold text-success">95.8%</div>
                                        <small class="text-muted">Resolution Rate</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Reports dashboard JavaScript
let charts = {};

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    setupAutoRefresh();
});

function initializeCharts() {
    initializeTicketAnalytics();
    initializeDivisionChart();
    initializeCategoryChart();
}

function initializeTicketAnalytics() {
    const ctx = document.getElementById('ticketAnalyticsChart').getContext('2d');
    
    charts.ticketAnalytics = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
            datasets: [{
                label: 'Created',
                data: [120, 135, 158, 142, 167, 189, 203, 178, 195],
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Resolved',
                data: [115, 128, 152, 138, 161, 185, 198, 172, 188],
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
                    text: 'Monthly Ticket Trends'
                },
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

function initializeDivisionChart() {
    const ctx = document.getElementById('divisionChart').getContext('2d');
    
    charts.division = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Northern', 'Southern', 'Eastern', 'Western', 'Central'],
            datasets: [{
                label: 'Tickets Resolved',
                data: [89, 76, 92, 84, 88],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)'
                ],
                borderColor: [
                    'rgb(54, 162, 235)',
                    'rgb(255, 99, 132)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
}

function initializeCategoryChart() {
    const ctx = document.getElementById('categoryChart').getContext('2d');
    
    charts.category = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Complaints', 'Queries', 'Suggestions', 'Appreciations'],
            datasets: [{
                data: [45, 30, 15, 10],
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
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
}

function changeChartType(type) {
    if (charts.ticketAnalytics) {
        charts.ticketAnalytics.destroy();
        
        const ctx = document.getElementById('ticketAnalyticsChart').getContext('2d');
        const config = {
            type: type === 'area' ? 'line' : type,
            data: charts.ticketAnalytics?.data || {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
                datasets: [{
                    label: 'Created',
                    data: [120, 135, 158, 142, 167, 189, 203, 178, 195],
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: type === 'area' ? 'rgba(54, 162, 235, 0.3)' : 'rgba(54, 162, 235, 0.8)',
                    tension: type === 'line' || type === 'area' ? 0.4 : 0,
                    fill: type === 'area'
                }, {
                    label: 'Resolved',
                    data: [115, 128, 152, 138, 161, 185, 198, 172, 188],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: type === 'area' ? 'rgba(75, 192, 192, 0.3)' : 'rgba(75, 192, 192, 0.8)',
                    tension: type === 'line' || type === 'area' ? 0.4 : 0,
                    fill: type === 'area'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Ticket Trends'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };
        
        charts.ticketAnalytics = new Chart(ctx, config);
    }
}

function setupAutoRefresh() {
    // Refresh charts every 5 minutes
    setInterval(() => {
        refreshChartData();
    }, 5 * 60 * 1000);
}

async function refreshChartData() {
    try {
        const response = await fetch(`${APP_URL}/api/admin/dashboard-stats`);
        const data = await response.json();
        
        if (data.success) {
            updateChartData(data.stats);
        }
    } catch (error) {
        console.error('Error refreshing chart data:', error);
    }
}

function updateChartData(stats) {
    // Update chart data with new stats
    if (stats.ticket_trends && charts.ticketAnalytics) {
        charts.ticketAnalytics.data.datasets[0].data = stats.ticket_trends.created;
        charts.ticketAnalytics.data.datasets[1].data = stats.ticket_trends.resolved;
        charts.ticketAnalytics.update();
    }
    
    if (stats.division_performance && charts.division) {
        charts.division.data.datasets[0].data = stats.division_performance;
        charts.division.update();
    }
    
    if (stats.category_distribution && charts.category) {
        charts.category.data.datasets[0].data = stats.category_distribution;
        charts.category.update();
    }
}

document.getElementById('reportFilters').addEventListener('submit', function(e) {
    e.preventDefault();
    applyFilters();
});

async function applyFilters() {
    const formData = new FormData(document.getElementById('reportFilters'));
    const params = new URLSearchParams(formData);
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/api/admin/reports/filtered?${params.toString()}`);
        const data = await response.json();
        hideLoading();
        
        if (data.success) {
            updateDashboard(data.reports);
        } else {
            Swal.fire('Error', 'Failed to apply filters', 'error');
        }
    } catch (error) {
        hideLoading();
        Swal.fire('Error', 'Failed to load filtered data', 'error');
    }
}

function updateDashboard(reports) {
    // Update dashboard with filtered data
    updateChartData(reports);
    updateMetrics(reports.metrics);
}

function updateMetrics(metrics) {
    if (metrics) {
        // Update key metric cards
        document.querySelector('.border-primary .h2').textContent = metrics.total_tickets || '0';
        document.querySelector('.border-success .h2').textContent = (metrics.resolution_rate || 0) + '%';
        document.querySelector('.border-warning .h2').textContent = (metrics.avg_resolution || 0) + 'h';
        document.querySelector('.border-info .h2').textContent = metrics.active_users || '0';
    }
}

function scheduleReport() {
    Swal.fire({
        title: 'Schedule Report',
        html: `
            <div class="text-start">
                <div class="mb-3">
                    <label class="form-label">Report Type</label>
                    <select class="form-control" id="scheduleReportType">
                        <option value="daily">Daily Summary</option>
                        <option value="weekly">Weekly Analytics</option>
                        <option value="monthly">Monthly Report</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Recipients</label>
                    <input type="email" class="form-control" id="scheduleEmails" placeholder="admin@example.com, manager@example.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Schedule Time</label>
                    <input type="time" class="form-control" id="scheduleTime" value="09:00">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Schedule',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Scheduled!', 'Report has been scheduled successfully.', 'success');
        }
    });
}

function exportDashboard() {
    Swal.fire({
        title: 'Export Dashboard',
        text: 'This will generate a comprehensive PDF report with all current data.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Export PDF',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `${APP_URL}/admin/reports/export/dashboard`;
        }
    });
}

function exportDivisionReport() {
    window.location.href = `${APP_URL}/admin/reports/export/divisions`;
}

function exportCategoryReport() {
    window.location.href = `${APP_URL}/admin/reports/export/categories`;
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
/* Admin reports specific styles */
.border-start.border-4 {
    background: linear-gradient(135deg, rgba(var(--bs-primary), 0.05) 0%, transparent 100%);
}

.border-primary {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, transparent 100%);
}

.border-success {
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.05) 0%, transparent 100%);
}

.border-warning {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.05) 0%, transparent 100%);
}

.border-info {
    background: linear-gradient(135deg, rgba(13, 202, 240, 0.05) 0%, transparent 100%);
}

/* Chart containers */
canvas {
    max-height: 400px;
}

/* Activity timeline */
.activity-timeline {
    position: relative;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.activity-item:last-child {
    margin-bottom: 0;
}

.activity-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    flex-shrink: 0;
    color: white;
    font-size: 0.75rem;
}

.activity-title {
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.activity-meta {
    font-size: 0.875rem;
    color: var(--bs-text-muted);
}

/* Progress bars */
.progress {
    background-color: rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}

/* Performance badges */
.bg-gold {
    background: linear-gradient(135deg, #FFD700, #FFA500);
}

.bg-silver {
    background: linear-gradient(135deg, #C0C0C0, #A0A0A0);
}

.bg-bronze {
    background: linear-gradient(135deg, #CD7F32, #B8860B);
}

/* Card hover effects */
.card-apple {
    transition: all 0.3s ease;
}

.card-apple:hover {
    transform: translateY(-2px);
    box-shadow: var(--apple-shadow-medium);
}

/* Button group styling */
.btn-group-sm .btn {
    font-size: 0.775rem;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .row.g-4 {
        --bs-gutter-x: 1rem;
    }
    
    .card-body {
        padding: 1rem 0.75rem;
    }
    
    .activity-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .activity-icon {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
    
    canvas {
        max-height: 250px;
    }
}

/* Print styles */
@media print {
    .btn, .btn-group,
    .card-header .btn {
        display: none !important;
    }
    
    .card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    .container-xl {
        max-width: 100% !important;
    }
}

/* Loading overlay for charts */
.chart-loading {
    position: relative;
}

.chart-loading::after {
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
}

@keyframes spin {
    to { transform: translate(-50%, -50%) rotate(360deg); }
}
</style>