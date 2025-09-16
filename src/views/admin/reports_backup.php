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

                                <div class="col-md-2">
                                    <label class="form-label-apple">Date From</label>
                                    <input type="date" class="form-control-apple" name="date_from" value="<?= $date_from ?>">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label-apple">Date To</label>
                                    <input type="date" class="form-control-apple" name="date_to" value="<?= $date_to ?>">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label-apple">Division Filter</label>
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($complaints_data)): ?>
                                        <tr>
                                            <td colspan="13" class="text-center py-5">
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

    </div>
</section>

<script>
// Report data and functionality
const reportData = {
    current_view: '<?= $current_view ?>',
    available_columns: <?= json_encode($available_columns) ?>,
    user_division: '<?= $user_division ?>'
};

document.addEventListener('DOMContentLoaded', function() {
    initializeDataTables();
    setupColumnSelector();
    setupRowClickHandlers();
});

function initializeDataTables() {
    const tableId = '#' + reportData.current_view + 'Table';
    const table = $(tableId);

    if (table.length) {
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
                }
            ]
        });
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
            {id: 'status', label: 'Status', default: false},
            {id: 'priority', label: 'Priority', default: false},
            {id: 'division', label: 'Division', default: false},
            {id: 'department', label: 'Department', default: false},
            {id: 'customer_email', label: 'Customer Email', default: false},
            {id: 'rating', label: 'Rating', default: false}
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
    const table = $('#' + reportData.current_view + 'Table').DataTable();
    const columnIndex = getColumnIndex(columnId);

    if (columnIndex !== -1) {
        table.column(columnIndex).visible(show);
    }
}

function getColumnIndex(columnId) {
    // Map column IDs to their indices in the table
    // This would need to be implemented based on actual column structure
    return -1;
}

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
    window.location.href = `${APP_URL}/admin/tickets/${complaintId}`;
}

function viewTransactionDetails(transactionId) {
    // Show transaction details in modal or navigate to details page
    console.log('View transaction:', transactionId);
}

function viewCustomerDetails(customerId) {
    window.location.href = `${APP_URL}/admin/customers/${customerId}`;
}

function scheduleReport() {
    // Implementation for report scheduling
    Swal.fire({
        title: 'Schedule Report',
        text: 'Report scheduling feature coming soon',
        icon: 'info'
    });
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