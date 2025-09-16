<?php
/**
 * New Detailed Reports Page - SAMPARK
 * Implementation based on prompt.md specifications
 * Advanced filtering, sorting, and data view capabilities
 */

// Capture the content
ob_start();

// Initialize variables
$current_view = $_GET['view'] ?? 'complaints';
$sort_order = $_GET['sort'] ?? 'latest';
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-t');
$division_filter = $_GET['division'] ?? '';
$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';

// User info
$user_division = $user['division'] ?? 'HQ';
$user_department = $user['department'] ?? '';

// Initialize data arrays
$report_data = $report_data ?? [];
$complaints_data = $complaints_data ?? [];
$transactions_data = $transactions_data ?? [];
$customers_data = $customers_data ?? [];
$available_columns = $available_columns ?? [];
$status_legend = [
    'pending' => ['color' => 'warning', 'label' => 'Pending'],
    'awaiting_info' => ['color' => 'secondary', 'label' => 'Awaiting Info'],
    'awaiting_feedback' => ['color' => 'info', 'label' => 'Awaiting Feedback'],
    'awaiting_approval' => ['color' => 'primary', 'label' => 'Awaiting Approval'],
    'closed' => ['color' => 'success', 'label' => 'Closed']
];
?>

<section class="py-4">
    <div class="container-xl">

        <!-- Page Header -->
        <div class="row align-items-center mb-4">
            <div class="col">
                <div class="d-flex align-items-center">
                    <div class="bg-apple-blue rounded-3 p-3 me-3">
                        <i class="fas fa-chart-line text-dark fa-lg"></i>
                    </div>
                    <div>
                        <h1 class="h3 mb-1 fw-semibold">Detailed Reports</h1>
                        <p class="text-muted mb-0">An In-depth Analysis Tool for comprehensive data exploration</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="d-flex gap-2">
                    <button class="btn btn-apple-secondary" onclick="scheduleReport()">
                        <i class="fas fa-clock me-2"></i>Schedule Report
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-apple-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-2"></i>Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="exportData('csv')">
                                <i class="fas fa-file-csv me-2"></i>Download CSV
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportData('pdf')">
                                <i class="fas fa-file-pdf me-2"></i>Download PDF
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data View Selection -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card-apple">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-database me-2"></i>Data View Selection
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group" aria-label="Data Views">
                            <input type="radio" class="btn-check" name="dataView" id="viewComplaints" value="complaints"
                                   <?= $current_view === 'complaints' ? 'checked' : '' ?> onchange="changeDataView('complaints')">
                            <label class="btn btn-outline-primary" for="viewComplaints">
                                <i class="fas fa-ticket-alt me-2"></i>Complaints
                            </label>

                            <input type="radio" class="btn-check" name="dataView" id="viewTransactions" value="transactions"
                                   <?= $current_view === 'transactions' ? 'checked' : '' ?> onchange="changeDataView('transactions')">
                            <label class="btn btn-outline-primary" for="viewTransactions">
                                <i class="fas fa-exchange-alt me-2"></i>Transactions
                            </label>

                            <input type="radio" class="btn-check" name="dataView" id="viewCustomers" value="customers"
                                   <?= $current_view === 'customers' ? 'checked' : '' ?> onchange="changeDataView('customers')">
                            <label class="btn btn-outline-primary" for="viewCustomers">
                                <i class="fas fa-users me-2"></i>Customers
                            </label>
                        </div>

                        <div class="mt-3">
                            <small class="text-muted">
                                <?php if ($current_view === 'complaints'): ?>
                                    <strong>Complaints:</strong> Exhaustive list of every complaint with joined customer and terminal details
                                <?php elseif ($current_view === 'transactions'): ?>
                                    <strong>Transactions:</strong> Log of every action related to complaints (status updates, assignments, remarks)
                                <?php else: ?>
                                    <strong>Customers:</strong> Complete list of all registered customers with contact details and registration info
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Filtering and Sorting Controls -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card-apple">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>Advanced Filtering & Sorting
                        </h5>
                        <button class="btn btn-sm btn-apple-secondary" onclick="resetFilters()">
                            <i class="fas fa-undo me-1"></i>Reset
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="reportFilters" method="GET">
                            <input type="hidden" name="view" value="<?= $current_view ?>">

                            <!-- Common Filters for Complaints & Transactions -->
                            <?php if ($current_view !== 'customers'): ?>
                            <div class="row g-3 mb-3">
                                <div class="col-md-2">
                                    <label class="form-label-apple">Sort Order</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="sort" id="sortLatest" value="latest"
                                               <?= $sort_order === 'latest' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-secondary btn-sm" for="sortLatest">Latest First</label>

                                        <input type="radio" class="btn-check" name="sort" id="sortOldest" value="oldest"
                                               <?= $sort_order === 'oldest' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-secondary btn-sm" for="sortOldest">Oldest First</label>
                                    </div>
                                </div>

                                <!-- Enhanced Date Range Filters -->
                                <div class="col-md-3">
                                    <label class="form-label-apple">Date Range</label>
                                    <select class="form-control-apple" id="dateRangeSelector" onchange="applyDateRange(this.value)">
                                        <option value="custom">Custom Range</option>
                                        <option value="last_7_days">Last 7 Days</option>
                                        <option value="last_month">Last Month</option>
                                        <option value="all_time">All Time</option>
                                    </select>
                                </div>

                                <div class="col-md-2" id="dateFromContainer">
                                    <label class="form-label-apple">Date From</label>
                                    <input type="date" class="form-control-apple" name="date_from" id="dateFrom" value="<?= $date_from ?>">
                                </div>

                                <div class="col-md-2" id="dateToContainer">
                                    <label class="form-label-apple">Date To</label>
                                    <input type="date" class="form-control-apple" name="date_to" id="dateTo" value="<?= $date_to ?>">
                                </div>

                                <!-- Role-based Division/Jurisdiction Filter -->
                                <div class="col-md-2">
                                    <label class="form-label-apple">
                                        <?php
                                        // Determine label based on user role
                                        $user_role = $user['role'] ?? 'admin';
                                        if ($user_role === 'controller_nodal') {
                                            echo 'Department (All in Division)';
                                        } elseif ($user_role === 'controller') {
                                            echo 'Division/Department';
                                        } else {
                                            echo 'Division Filter';
                                        }
                                        ?>
                                    </label>
                                    <select class="form-control-apple" name="division">
                                        <option value="">
                                            <?php
                                            if ($user_role === 'controller_nodal') {
                                                echo 'All Departments in Division';
                                            } elseif ($user_role === 'controller') {
                                                echo 'Own Department + HQ';
                                            } else {
                                                echo 'All Divisions';
                                            }
                                            ?>
                                        </option>
                                        <?php
                                        // Role-based filtering as per requirements
                                        if ($user_role === 'admin' || $user_role === 'superadmin') {
                                            // Admin – Data from all Departments and Zones, but default show own zone
                                            echo '<option value="Northern"' . ($division_filter === 'Northern' ? ' selected' : '') . '>Northern Division</option>';
                                            echo '<option value="Southern"' . ($division_filter === 'Southern' ? ' selected' : '') . '>Southern Division</option>';
                                            echo '<option value="Eastern"' . ($division_filter === 'Eastern' ? ' selected' : '') . '>Eastern Division</option>';
                                            echo '<option value="Western"' . ($division_filter === 'Western' ? ' selected' : '') . '>Western Division</option>';
                                            echo '<option value="Central"' . ($division_filter === 'Central' ? ' selected' : '') . '>Central Division</option>';
                                        } elseif ($user_role === 'controller_nodal') {
                                            // Controller Nodal - All Departments in their specific Division
                                            echo '<option value="' . htmlspecialchars($user_division) . '" selected>' . htmlspecialchars($user_division) . ' Division (All Departments)</option>';
                                        } elseif ($user_role === 'controller') {
                                            // Controller – Only data from specific department and division, for HQ data from all divisions in same zone
                                            echo '<option value="' . htmlspecialchars($user_division) . '"' . ($division_filter === $user_division ? ' selected' : '') . '>' . htmlspecialchars($user_division) . ' Division</option>';
                                            if ($user_division !== 'HQ') {
                                                echo '<option value="HQ"' . ($division_filter === 'HQ' ? ' selected' : '') . '>HQ (Same Zone)</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label-apple">Status Filter</label>
                                    <select class="form-control-apple" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="awaiting_info" <?= $status_filter === 'awaiting_info' ? 'selected' : '' ?>>Awaiting Info</option>
                                        <option value="awaiting_feedback" <?= $status_filter === 'awaiting_feedback' ? 'selected' : '' ?>>Awaiting Feedback</option>
                                        <option value="awaiting_approval" <?= $status_filter === 'awaiting_approval' ? 'selected' : '' ?>>Awaiting Approval</option>
                                        <option value="closed" <?= $status_filter === 'closed' ? 'selected' : '' ?>>Closed</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label-apple">Priority Filter</label>
                                    <select class="form-control-apple" name="priority">
                                        <option value="">All Priorities</option>
                                        <option value="critical" <?= $priority_filter === 'critical' ? 'selected' : '' ?>>Critical</option>
                                        <option value="high" <?= $priority_filter === 'high' ? 'selected' : '' ?>>High</option>
                                        <option value="medium" <?= $priority_filter === 'medium' ? 'selected' : '' ?>>Medium</option>
                                        <option value="normal" <?= $priority_filter === 'normal' ? 'selected' : '' ?>>Normal</option>
                                    </select>
                                </div>
                            </div>
                            <?php else: ?>
                            <!-- Customer-specific filters -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label-apple">Registration Division Filter</label>
                                    <select class="form-control-apple" name="division">
                                        <option value="">All Divisions</option>
                                        <?php if ($user_division === 'HQ'): ?>
                                            <option value="Northern" <?= $division_filter === 'Northern' ? 'selected' : '' ?>>Northern</option>
                                            <option value="Southern" <?= $division_filter === 'Southern' ? 'selected' : '' ?>>Southern</option>
                                            <option value="Eastern" <?= $division_filter === 'Eastern' ? 'selected' : '' ?>>Eastern</option>
                                            <option value="Western" <?= $division_filter === 'Western' ? 'selected' : '' ?>>Western</option>
                                            <option value="Central" <?= $division_filter === 'Central' ? 'selected' : '' ?>>Central</option>
                                        <?php else: ?>
                                            <option value="<?= htmlspecialchars($user_division) ?>" selected><?= htmlspecialchars($user_division) ?></option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col">
                                    <button type="submit" class="btn btn-apple-primary">
                                        <i class="fas fa-search me-2"></i>Apply Filters
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Color-Coding Legend (for Complaints view) -->
        <?php if ($current_view === 'complaints'): ?>
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <span class="fw-semibold">Status Legend:</span>
                    <?php foreach ($status_legend as $status => $config): ?>
                        <span class="badge bg-<?= $config['color'] ?> me-2">
                            <?= $config['label'] ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($current_tab === 'scheduled'): ?>
        <!-- Scheduled Report Generation -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card-apple">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-file-pdf text-apple-blue me-2"></i>
                            Generate Comprehensive Report
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8">
                                <p class="text-muted mb-4">
                                    Generate a comprehensive, multi-page PDF report perfect for printing, sharing, or offline analysis.
                                    The report includes customer summary with charts, complaint duration analysis, division vs status summary, and detailed complaint listings.
                                </p>

                                <form id="scheduledReportForm" onsubmit="generateScheduledReport(event)">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label-apple">Division and Zone</label>
                                            <select class="form-control-apple" name="report_division" required>
                                                <option value="">Select Division</option>
                                                <option value="all">All Divisions</option>
                                                <?php
                                                $divisions = ['Northern', 'Southern', 'Eastern', 'Western', 'Central'];
                                                foreach ($divisions as $div): ?>
                                                    <option value="<?= $div ?>"><?= $div ?> Division</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label-apple">Date Range</label>
                                            <select class="form-control-apple" id="reportDateRange" name="report_date_range" onchange="toggleCustomDates(this.value)" required>
                                                <option value="last_7_days">Last 7 Days</option>
                                                <option value="last_month" selected>Last Month</option>
                                                <option value="current_month">Current Month</option>
                                                <option value="last_3_months">Last 3 Months</option>
                                                <option value="custom">Custom Range</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6" id="customDateFromContainer" style="display: none;">
                                            <label class="form-label-apple">Custom From Date</label>
                                            <input type="date" class="form-control-apple" name="report_date_from" id="reportDateFrom">
                                        </div>

                                        <div class="col-md-6" id="customDateToContainer" style="display: none;">
                                            <label class="form-label-apple">Custom To Date</label>
                                            <input type="date" class="form-control-apple" name="report_date_to" id="reportDateTo">
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-apple-primary btn-lg">
                                            <i class="fas fa-file-export me-2"></i>Generate Report
                                        </button>
                                        <button type="button" class="btn btn-apple-glass ms-2" onclick="previewReport()">
                                            <i class="fas fa-eye me-2"></i>Preview
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="col-lg-4">
                                <div class="bg-light rounded-3 p-4">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="fas fa-info-circle text-primary me-2"></i>
                                        Report Includes
                                    </h6>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-chart-bar text-success me-2"></i>
                                            <strong>Customer Summary:</strong> New registrations with bar charts
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-clock text-info me-2"></i>
                                            <strong>Duration Analysis:</strong> Resolution time metrics and comparisons
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-table text-warning me-2"></i>
                                            <strong>Status Summary:</strong> Division vs complaint status pivot table
                                        </li>
                                        <li class="mb-0">
                                            <i class="fas fa-list text-primary me-2"></i>
                                            <strong>Detailed List:</strong> Complete complaint records with color coding
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Generation Status -->
        <div class="row" id="reportStatusContainer" style="display: none;">
            <div class="col-12">
                <div class="card-apple">
                    <div class="card-body text-center">
                        <div id="reportGenerationSpinner" class="mb-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Generating report...</span>
                            </div>
                        </div>
                        <h5 id="reportStatusText">Generating your comprehensive report...</h5>
                        <p class="text-muted mb-0" id="reportStatusDetails">This may take a few moments depending on the data range selected.</p>
                        <div class="progress mt-3" style="height: 8px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="reportProgress" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($current_tab === 'detailed'): ?>
        <!-- Table Functionality and Content -->
        <div class="row">
            <div class="col-12">
                <div class="card-apple">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2"></i>
                            <?php
                            switch ($current_view) {
                                case 'transactions':
                                    echo 'Transaction Log';
                                    break;
                                case 'customers':
                                    echo 'Customer Registry';
                                    break;
                                default:
                                    echo 'Complaints Data';
                                    break;
                            }
                            ?>
                        </h5>

                        <!-- Column Customization -->
                        <div class="dropdown">
                            <button class="btn btn-apple-secondary dropdown-toggle" type="button" id="columnsDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-columns me-2"></i>Columns
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" id="columnSelector">
                                <!-- Will be populated by JavaScript based on current view -->
                            </ul>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <?php if ($current_view === 'complaints'): ?>
                                <!-- Complaints Table -->
                                <table class="table table-hover align-middle mb-0" id="complaintsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0">Serial No.</th>
                                            <th class="border-0">Complaint No.</th>
                                            <th class="border-0">Complaint Date</th>
                                            <th class="border-0">Update Date</th>
                                            <th class="border-0">Duration (Hours)</th>
                                            <th class="border-0">Terminal</th>
                                            <th class="border-0">Complainant</th>
                                            <th class="border-0">Type Subtype</th>
                                            <th class="border-0">FNR No</th>
                                            <th class="border-0 description-col">Description</th>
                                            <th class="border-0 action-col">Action Taken</th>
                                            <th class="border-0">Status</th>
                                            <th class="border-0">Priority</th>
                                            <th class="border-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($complaints_data)): ?>
                                        <tr>
                                            <td colspan="14" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-search fa-2x mb-3"></i>
                                                    <h5>No complaints found</h5>
                                                    <p>Try adjusting your filters or date range</p>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($complaints_data as $index => $complaint): ?>
                                            <tr class="clickable-row status-<?= $complaint['status'] ?>"
                                                onclick="viewComplaintDetails('<?= $complaint['complaint_id'] ?>')">
                                                <td><?= $index + 1 ?></td>
                                                <td class="fw-semibold text-primary"><?= htmlspecialchars($complaint['complaint_id']) ?></td>
                                                <td><?= date('M d, Y', strtotime($complaint['date'])) ?></td>
                                                <td><?= date('M d, Y', strtotime($complaint['updated_at'])) ?></td>
                                                <td>
                                                    <span class="<?= $complaint['duration_hours'] > 48 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                                        <?= $complaint['duration_hours'] ?>h
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($complaint['shed_name'] ?? 'N/A') ?></td>
                                                <td>
                                                    <div class="fw-medium"><?= htmlspecialchars($complaint['customer_name'] ?? 'N/A') ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($complaint['company_name'] ?? '') ?></small>
                                                    <small class="d-block text-muted"><?= htmlspecialchars($complaint['customer_mobile'] ?? '') ?></small>
                                                </td>
                                                <td>
                                                    <div><?= htmlspecialchars($complaint['category'] ?? 'N/A') ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($complaint['type'] ?? '') ?></small>
                                                </td>
                                                <td><?= htmlspecialchars($complaint['fnr_number'] ?? 'N/A') ?></td>
                                                <td class="description-col">
                                                    <div class="description-text">
                                                        <?= htmlspecialchars(substr($complaint['description'] ?? '', 0, 100)) ?>
                                                        <?= strlen($complaint['description'] ?? '') > 100 ? '...' : '' ?>
                                                    </div>
                                                </td>
                                                <td class="action-col">
                                                    <div class="action-text">
                                                        <?= htmlspecialchars(substr($complaint['action_taken'] ?? 'No action taken yet', 0, 100)) ?>
                                                        <?= strlen($complaint['action_taken'] ?? '') > 100 ? '...' : '' ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $status_legend[$complaint['status']]['color'] ?>">
                                                        <?= $status_legend[$complaint['status']]['label'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $priority_class = [
                                                        'critical' => 'danger',
                                                        'high' => 'warning',
                                                        'medium' => 'info',
                                                        'normal' => 'secondary'
                                                    ][$complaint['priority']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?= $priority_class ?>">
                                                        <?= ucfirst($complaint['priority']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary btn-sm"
                                                                onclick="event.stopPropagation(); viewComplaintDetails('<?= $complaint['complaint_id'] ?>')"
                                                                title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if ($complaint['status'] !== 'closed'): ?>
                                                        <button class="btn btn-outline-success btn-sm"
                                                                onclick="event.stopPropagation(); quickReply('<?= $complaint['complaint_id'] ?>')"
                                                                title="Quick Reply">
                                                            <i class="fas fa-reply"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>

                            <?php elseif ($current_view === 'transactions'): ?>
                                <!-- Transactions Table -->
                                <table class="table table-hover align-middle mb-0" id="transactionsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0">Serial No.</th>
                                            <th class="border-0">Transaction ID</th>
                                            <th class="border-0">Complaint ID</th>
                                            <th class="border-0">Transaction Type</th>
                                            <th class="border-0">Performed By</th>
                                            <th class="border-0">From Division</th>
                                            <th class="border-0">To Division</th>
                                            <th class="border-0">Status Change</th>
                                            <th class="border-0">Remarks</th>
                                            <th class="border-0">Date & Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($transactions_data)): ?>
                                        <tr>
                                            <td colspan="10" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-exchange-alt fa-2x mb-3"></i>
                                                    <h5>No transactions found</h5>
                                                    <p>Try adjusting your filters or date range</p>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($transactions_data as $index => $transaction): ?>
                                            <tr class="clickable-row" onclick="viewTransactionDetails('<?= $transaction['transaction_id'] ?>')">
                                                <td><?= $index + 1 ?></td>
                                                <td class="fw-semibold text-primary"><?= htmlspecialchars($transaction['transaction_id']) ?></td>
                                                <td>
                                                    <a href="#" onclick="viewComplaintDetails('<?= $transaction['complaint_id'] ?>')" class="text-decoration-none">
                                                        <?= htmlspecialchars($transaction['complaint_id']) ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?= ucwords(str_replace('_', ' ', $transaction['transaction_type'])) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($transaction['user_name'] ?? 'System') ?></td>
                                                <td><?= htmlspecialchars($transaction['from_division'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($transaction['to_division'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?php if ($transaction['old_status'] !== $transaction['new_status']): ?>
                                                        <span class="badge bg-secondary"><?= $transaction['old_status'] ?></span>
                                                        <i class="fas fa-arrow-right mx-1"></i>
                                                        <span class="badge bg-success"><?= $transaction['new_status'] ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">No change</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="remarks-text">
                                                        <?= htmlspecialchars(substr($transaction['remarks'] ?? '', 0, 80)) ?>
                                                        <?= strlen($transaction['remarks'] ?? '') > 80 ? '...' : '' ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= date('M d, Y H:i', strtotime($transaction['created_at'])) ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>

                            <?php else: ?>
                                <!-- Customers Table -->
                                <table class="table table-hover align-middle mb-0" id="customersTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0">Serial No.</th>
                                            <th class="border-0">Customer ID</th>
                                            <th class="border-0">Name</th>
                                            <th class="border-0">Company</th>
                                            <th class="border-0">Email</th>
                                            <th class="border-0">Mobile</th>
                                            <th class="border-0">Type</th>
                                            <th class="border-0">Division</th>
                                            <th class="border-0">Status</th>
                                            <th class="border-0">Registration Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($customers_data)): ?>
                                        <tr>
                                            <td colspan="10" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-users fa-2x mb-3"></i>
                                                    <h5>No customers found</h5>
                                                    <p>Try adjusting your filters</p>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($customers_data as $index => $customer): ?>
                                            <tr class="clickable-row" onclick="viewCustomerDetails('<?= $customer['customer_id'] ?>')">
                                                <td><?= $index + 1 ?></td>
                                                <td class="fw-semibold text-primary"><?= htmlspecialchars($customer['customer_id']) ?></td>
                                                <td><?= htmlspecialchars($customer['name']) ?></td>
                                                <td><?= htmlspecialchars($customer['company_name']) ?></td>
                                                <td><?= htmlspecialchars($customer['email']) ?></td>
                                                <td><?= htmlspecialchars($customer['mobile']) ?></td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?= ucfirst($customer['customer_type']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($customer['division'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?php
                                                    $status_colors = [
                                                        'approved' => 'success',
                                                        'pending' => 'warning',
                                                        'rejected' => 'danger',
                                                        'suspended' => 'secondary'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?= $status_colors[$customer['status']] ?? 'secondary' ?>">
                                                        <?= ucfirst($customer['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M d, Y', strtotime($customer['created_at'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

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
                    <div class="mb-3">
                        <label class="form-label-apple">Status Update</label>
                        <select class="form-control-apple" name="new_status" required>
                            <option value="">Select Status</option>
                            <option value="awaiting_feedback">Awaiting Customer Feedback</option>
                            <option value="awaiting_info">Awaiting Additional Information</option>
                            <option value="closed">Resolve and Close</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-apple-primary">
                        <i class="fas fa-paper-plane me-2"></i>Send Reply & Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Report data and functionality
const reportData = {
    current_view: '<?= $current_view ?>',
    available_columns: <?= json_encode($available_columns) ?>,
    user_division: '<?= $user_division ?>'
};

document.addEventListener('DOMContentLoaded', function() {
    // Add small delay to ensure all DOM elements are properly rendered
    setTimeout(function() {
        initializeDataTables();
        setupColumnSelector();
        setupRowClickHandlers();
        setupQuickReplyModal();
    }, 100);
});

function initializeDataTables() {
    // Destroy existing DataTable instances first
    $('table[id*="Table"]').each(function() {
        if ($.fn.DataTable.isDataTable(this)) {
            $(this).DataTable().destroy();
        }
    });

    const tableId = '#' + reportData.current_view + 'Table';
    const table = $(tableId);

    if (table.length && table.is(':visible')) {
        // Check if table has any rows with data
        const tbody = table.find('tbody');
        const dataRows = tbody.find('tr').not('.dataTables_empty');

        // Only initialize if we have valid table structure
        if (tbody.length > 0) {
            try {
                // Validate table structure before initialization
                const headerCells = table.find('thead tr:first th').length;
                let validStructure = true;

                // Skip initialization if only the "no data" row exists
                if (dataRows.length === 0) {
                    const allRows = tbody.find('tr');
                    if (allRows.length === 1) {
                        const firstRowCells = allRows.first().find('td').length;
                        if (firstRowCells === 1 && allRows.first().find('td').attr('colspan')) {
                            // This is likely the "no data found" row, skip DataTables
                            console.log('Table contains only "no data" message, skipping DataTables initialization');
                            return;
                        }
                    }
                }

                // Check each data row has correct number of cells
                dataRows.each(function() {
                    const cellCount = $(this).find('td').length;
                    if (cellCount !== headerCells && cellCount > 0) {
                        console.warn(`Row has ${cellCount} cells, expected ${headerCells}`);
                        validStructure = false;
                    }
                });

                if (validStructure && dataRows.length > 0) {
                    table.DataTable({
                        pageLength: 25,
                        lengthMenu: [10, 25, 50, 100],
                        responsive: true,
                        dom: '<"top"lf>rt<"bottom"ip><"clear">',
                        language: {
                            search: "_INPUT_",
                            searchPlaceholder: "Search records..."
                        },
                        order: [[1, 'desc']], // Default order by first sortable column
                        columnDefs: [
                            {
                                targets: 'no-sort',
                                orderable: false
                            },
                            {
                                targets: -1, // Last column (Actions)
                                orderable: false,
                                searchable: false
                            }
                        ]
                    });
                } else {
                    console.error('Table structure validation failed - column count mismatch');
                }
            } catch (error) {
                console.error('DataTables initialization error:', error);
            }
        }
    }
}

function setupColumnSelector() {
    const columnSelector = document.getElementById('columnSelector');
    if (!columnSelector) return;

    // Define available columns for each view
    const columnsConfig = {
        complaints: [
            {id: 'serial', label: 'Serial No.', default: true},
            {id: 'complaint_id', label: 'Complaint No.', default: true},
            {id: 'date', label: 'Complaint Date', default: true},
            {id: 'updated_at', label: 'Update Date', default: true},
            {id: 'duration', label: 'Duration (Hours)', default: true},
            {id: 'terminal', label: 'Terminal', default: true},
            {id: 'complainant', label: 'Complainant', default: true},
            {id: 'type_subtype', label: 'Type Subtype', default: true},
            {id: 'fnr_number', label: 'FNR No', default: true},
            {id: 'description', label: 'Description', default: true},
            {id: 'action_taken', label: 'Action Taken', default: true},
            {id: 'status', label: 'Status', default: true},
            {id: 'priority', label: 'Priority', default: true},
            {id: 'actions', label: 'Actions', default: true}
        ],
        transactions: [
            {id: 'serial', label: 'Serial No.', default: true},
            {id: 'transaction_id', label: 'Transaction ID', default: true},
            {id: 'complaint_id', label: 'Complaint ID', default: true},
            {id: 'transaction_type', label: 'Transaction Type', default: true},
            {id: 'performed_by', label: 'Performed By', default: true},
            {id: 'from_division', label: 'From Division', default: true},
            {id: 'to_division', label: 'To Division', default: true},
            {id: 'status_change', label: 'Status Change', default: true},
            {id: 'remarks', label: 'Remarks', default: true},
            {id: 'created_at', label: 'Date & Time', default: true}
        ],
        customers: [
            {id: 'serial', label: 'Serial No.', default: true},
            {id: 'customer_id', label: 'Customer ID', default: true},
            {id: 'name', label: 'Name', default: true},
            {id: 'company_name', label: 'Company', default: true},
            {id: 'email', label: 'Email', default: true},
            {id: 'mobile', label: 'Mobile', default: true},
            {id: 'customer_type', label: 'Type', default: true},
            {id: 'division', label: 'Division', default: true},
            {id: 'status', label: 'Status', default: true},
            {id: 'created_at', label: 'Registration Date', default: true},
            {id: 'designation', label: 'Designation', default: false},
            {id: 'gstin', label: 'GSTIN', default: false},
            {id: 'zone', label: 'Zone', default: false}
        ]
    };

    const columns = columnsConfig[reportData.current_view] || [];
    columnSelector.innerHTML = '';

    columns.forEach(column => {
        const li = document.createElement('li');
        li.innerHTML = `
            <label class="dropdown-item">
                <input type="checkbox" class="form-check-input me-2"
                       id="col_${column.id}"
                       ${column.default ? 'checked' : ''}
                       onchange="toggleColumn('${column.id}', this.checked)">
                ${column.label}
            </label>
        `;
        columnSelector.appendChild(li);
    });
}

function toggleColumn(columnId, show) {
    const tableElement = $('#' + reportData.current_view + 'Table');
    if (tableElement.length && $.fn.DataTable.isDataTable(tableElement[0])) {
        try {
            const table = tableElement.DataTable();
            const columnIndex = getColumnIndex(columnId);

            if (columnIndex !== -1) {
                table.column(columnIndex).visible(show);
            }
        } catch (error) {
            console.error('Error toggling column:', error);
        }
    }
}

function getColumnIndex(columnId) {
    // Map column IDs to their indices in the table
    // This would need to be implemented based on actual column structure
    return -1;
}

// Date range selector functionality
function applyDateRange(range) {
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const dateFromContainer = document.getElementById('dateFromContainer');
    const dateToContainer = document.getElementById('dateToContainer');

    const today = new Date();
    let fromDate, toDate;

    switch(range) {
        case 'last_7_days':
            fromDate = new Date(today);
            fromDate.setDate(today.getDate() - 7);
            toDate = today;
            dateFromContainer.style.display = 'none';
            dateToContainer.style.display = 'none';
            break;

        case 'last_month':
            fromDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            toDate = new Date(today.getFullYear(), today.getMonth(), 0);
            dateFromContainer.style.display = 'none';
            dateToContainer.style.display = 'none';
            break;

        case 'all_time':
            fromDate = new Date('2020-01-01'); // Set a reasonable start date
            toDate = today;
            dateFromContainer.style.display = 'none';
            dateToContainer.style.display = 'none';
            break;

        case 'custom':
        default:
            dateFromContainer.style.display = 'block';
            dateToContainer.style.display = 'block';
            return; // Don't auto-set dates for custom range
    }

    // Set the date inputs
    dateFrom.value = fromDate.toISOString().split('T')[0];
    dateTo.value = toDate.toISOString().split('T')[0];
}

// Initialize date range on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check if current dates match any preset range and update selector
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const selector = document.getElementById('dateRangeSelector');

    if (dateFrom && dateTo && selector) {
        const fromVal = dateFrom.value;
        const toVal = dateTo.value;
        const today = new Date().toISOString().split('T')[0];

        // Check if it matches last 7 days
        const last7Days = new Date();
        last7Days.setDate(last7Days.getDate() - 7);
        if (fromVal === last7Days.toISOString().split('T')[0] && toVal === today) {
            selector.value = 'last_7_days';
            applyDateRange('last_7_days');
        }
        // Check if it matches last month
        else {
            const lastMonthStart = new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1);
            const lastMonthEnd = new Date(new Date().getFullYear(), new Date().getMonth(), 0);
            if (fromVal === lastMonthStart.toISOString().split('T')[0] &&
                toVal === lastMonthEnd.toISOString().split('T')[0]) {
                selector.value = 'last_month';
                applyDateRange('last_month');
            }
        }
    }
});

function changeDataView(view) {
    const url = new URL(window.location);
    url.searchParams.set('view', view);

    // Keep existing filters that are applicable
    if (view === 'customers') {
        url.searchParams.delete('status');
        url.searchParams.delete('priority');
        url.searchParams.delete('date_from');
        url.searchParams.delete('date_to');
        url.searchParams.delete('sort');
    }

    window.location.href = url.toString();
}

function resetFilters() {
    const url = new URL(window.location);
    const view = url.searchParams.get('view') || 'complaints';
    window.location.href = url.pathname + '?view=' + view;
}

function exportData(format) {
    const form = document.getElementById('reportFilters');
    const formData = new FormData(form);
    formData.append('export', format);

    // Create temporary form for download
    const downloadForm = document.createElement('form');
    downloadForm.method = 'POST';
    downloadForm.action = `${APP_URL}/admin/reports/export`;
    downloadForm.style.display = 'none';

    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = CSRF_TOKEN;
    downloadForm.appendChild(csrfInput);

    // Add form data
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        downloadForm.appendChild(input);
    }

    document.body.appendChild(downloadForm);
    downloadForm.submit();
    document.body.removeChild(downloadForm);
}

function viewComplaintDetails(complaintId) {
    window.location.href = `${APP_URL}/admin/tickets/${complaintId}/view`;
}

function viewTransactionDetails(transactionId) {
    // Show transaction details in modal or navigate to details page
    console.log('View transaction:', transactionId);
}

function viewCustomerDetails(customerId) {
    window.location.href = `${APP_URL}/admin/customers/${customerId}`;
}

function quickReply(complaintId) {
    const replyTicketId = document.getElementById('replyTicketId');
    const quickReplyForm = document.getElementById('quickReplyForm');
    const quickReplyModal = document.getElementById('quickReplyModal');

    if (!replyTicketId || !quickReplyForm || !quickReplyModal) {
        console.error('Quick reply modal elements not found');
        return;
    }

    replyTicketId.textContent = complaintId;
    quickReplyForm.dataset.complaintId = complaintId;
    new bootstrap.Modal(quickReplyModal).show();
}

function scheduleReport() {
    // Navigate to scheduled report tab
    const url = new URL(window.location);
    url.searchParams.set('tab', 'scheduled');
    window.location.href = url.toString();
}

function setupRowClickHandlers() {
    document.querySelectorAll('.clickable-row').forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(var(--bs-primary-rgb), 0.06)';
        });
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
}

function setupQuickReplyModal() {
    const quickReplyForm = document.getElementById('quickReplyForm');
    if (!quickReplyForm) {
        console.warn('Quick reply form not found');
        return;
    }

    quickReplyForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const complaintId = this.dataset.complaintId;
        const formData = new FormData(this);
        formData.append('csrf_token', CSRF_TOKEN);

        try {
            const response = await fetch(`${APP_URL}/admin/tickets/${complaintId}/reply`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire('Success', result.message, 'success').then(() => {
                    bootstrap.Modal.getInstance(document.getElementById('quickReplyModal')).hide();
                    location.reload(); // Refresh to show updated data
                });
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to send reply', 'error');
        }
    });
}

// Scheduled Report Functions
function switchToScheduledReports() {
    const url = new URL(window.location);
    url.searchParams.set('tab', 'scheduled');
    window.location.href = url.toString();
}

function switchToDetailedReports() {
    const url = new URL(window.location);
    url.searchParams.delete('tab');
    window.location.href = url.toString();
}

function toggleCustomDates(range) {
    const customFromContainer = document.getElementById('customDateFromContainer');
    const customToContainer = document.getElementById('customDateToContainer');
    const reportDateFrom = document.getElementById('reportDateFrom');
    const reportDateTo = document.getElementById('reportDateTo');

    if (range === 'custom') {
        customFromContainer.style.display = 'block';
        customToContainer.style.display = 'block';
        reportDateFrom.setAttribute('required', 'required');
        reportDateTo.setAttribute('required', 'required');
    } else {
        customFromContainer.style.display = 'none';
        customToContainer.style.display = 'none';
        reportDateFrom.removeAttribute('required');
        reportDateTo.removeAttribute('required');
    }
}

function generateScheduledReport(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const reportStatusContainer = document.getElementById('reportStatusContainer');
    const reportProgress = document.getElementById('reportProgress');
    const reportStatusText = document.getElementById('reportStatusText');
    const reportStatusDetails = document.getElementById('reportStatusDetails');

    // Show status container
    reportStatusContainer.style.display = 'block';

    // Add CSRF token
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('action', 'generate_scheduled_report');

    // Simulate progress
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 90) progress = 90;
        reportProgress.style.width = progress + '%';
    }, 500);

    // Update status messages
    setTimeout(() => {
        reportStatusText.textContent = 'Collecting complaint data...';
        reportStatusDetails.textContent = 'Gathering complaints based on your filters.';
    }, 1000);

    setTimeout(() => {
        reportStatusText.textContent = 'Analyzing customer metrics...';
        reportStatusDetails.textContent = 'Calculating registration statistics and trends.';
    }, 3000);

    setTimeout(() => {
        reportStatusText.textContent = 'Generating charts and tables...';
        reportStatusDetails.textContent = 'Creating visual representations of the data.';
    }, 5000);

    // Make the actual request
    fetch(`${APP_URL}/admin/reports/generate-scheduled`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            return response.blob();
        }
        throw new Error('Report generation failed');
    })
    .then(blob => {
        clearInterval(progressInterval);
        reportProgress.style.width = '100%';
        reportStatusText.textContent = 'Report generated successfully!';
        reportStatusDetails.textContent = 'Your comprehensive report is ready for download.';

        // Create download link
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `SAMPARK_Comprehensive_Report_${new Date().toISOString().split('T')[0]}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        // Hide status after 3 seconds
        setTimeout(() => {
            reportStatusContainer.style.display = 'none';
            reportProgress.style.width = '0%';
        }, 3000);
    })
    .catch(error => {
        clearInterval(progressInterval);
        reportStatusText.textContent = 'Report generation failed';
        reportStatusDetails.textContent = 'Please try again or contact support if the problem persists.';

        Swal.fire({
            title: 'Error',
            text: 'Failed to generate report. Please try again.',
            icon: 'error'
        });

        setTimeout(() => {
            reportStatusContainer.style.display = 'none';
            reportProgress.style.width = '0%';
        }, 3000);
    });
}

function previewReport() {
    const form = document.getElementById('scheduledReportForm');
    const formData = new FormData(form);
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('action', 'preview_scheduled_report');

    fetch(`${APP_URL}/admin/reports/preview-scheduled`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Report Preview',
                html: data.preview_html,
                width: '80%',
                showConfirmButton: true,
                confirmButtonText: 'Generate Full Report',
                showCancelButton: true,
                cancelButtonText: 'Close'
            }).then((result) => {
                if (result.isConfirmed) {
                    generateScheduledReport({
                        preventDefault: () => {},
                        target: form
                    });
                }
            });
        } else {
            Swal.fire('Error', data.message || 'Preview failed', 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error', 'Failed to generate preview', 'error');
    });
}
</script>

<style>
/* Detailed Reports specific styles */
.card-apple {
    background: rgba(255, 255, 255, 0.98);
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

/* Scheduled Report Styles */
.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar-striped {
    background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent);
    background-size: 1rem 1rem;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
}

#reportStatusContainer .card-apple {
    border: 1px solid rgba(var(--bs-primary-rgb), 0.2);
    background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.05), rgba(var(--bs-primary-rgb), 0.1));
}

.clickable-row {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.clickable-row:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.06);
}

/* Status row coloring */
.status-pending {
    border-left: 3px solid #ffc107;
}

.status-awaiting_info {
    border-left: 3px solid #6c757d;
}

.status-awaiting_feedback {
    border-left: 3px solid #0dcaf0;
}

.status-awaiting_approval {
    border-left: 3px solid #0d6efd;
}

.status-closed {
    border-left: 3px solid #198754;
}

/* Column width controls */
.description-col {
    width: 200px;
    max-width: 200px;
}

.action-col {
    width: 200px;
    max-width: 200px;
}

.description-text,
.action-text,
.remarks-text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 180px;
}

/* Data view buttons */
.btn-check:checked + .btn-outline-primary {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
}

/* Filter controls */
.form-control-apple,
.form-control {
    border-radius: 8px;
    border: 1px solid #ddd;
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
}

.form-control-apple:focus,
.form-control:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
}

/* Badge styling */
.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}

/* Column selector dropdown */
.dropdown-menu {
    max-height: 300px;
    overflow-y: auto;
}

.dropdown-item label {
    margin-bottom: 0;
    cursor: pointer;
    width: 100%;
    padding: 0.25rem 0;
}

/* Quick Reply Modal */
.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
    border-bottom: 1px solid #eee;
    padding: 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid #eee;
    padding: 1rem 1.5rem;
}

/* Action buttons in table */
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.775rem;
}

/* Table responsive improvements */
.table-responsive {
    border-radius: 8px;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
}

.table td {
    font-size: 0.875rem;
    vertical-align: middle;
}

/* DataTables customization */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 1rem;
}

.dataTables_wrapper .dataTables_filter input {
    border-radius: 8px;
    border: 1px solid #ddd;
    padding: 0.375rem 0.75rem;
    margin-left: 0.5rem;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem 0.75rem;
    }

    .table-responsive {
        font-size: 0.8rem;
    }

    .btn-group {
        flex-direction: column;
        width: 100%;
    }

    .btn-group .btn {
        border-radius: 6px !important;
        margin-bottom: 0.25rem;
    }

    .description-col,
    .action-col {
        width: 150px;
        max-width: 150px;
    }

    .description-text,
    .action-text,
    .remarks-text {
        max-width: 130px;
    }
}

/* Print styles */
@media print {
    .card-header,
    .btn, .btn-group,
    .dropdown,
    #reportFilters {
        display: none !important;
    }

    .table {
        font-size: 0.7rem;
    }

    .badge {
        border: 1px solid #333 !important;
        color: #000 !important;
        background: transparent !important;
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