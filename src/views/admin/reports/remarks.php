<?php
/**
 * Admin Remarks Report
 * Shows department-wise admin remarks analysis and trends
 */

require_once __DIR__ . '/../../models/AdminRemarksModel.php';
$adminRemarksModel = new AdminRemarksModel();

$currentUser = $_SESSION['user'];
$currentDepartment = $currentUser['department'];
$isSuperAdmin = $currentUser['role'] === 'superadmin';

// Date filters
$dateFrom = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$dateTo = $_GET['date_to'] ?? date('Y-m-d'); // Today
$selectedDepartment = $_GET['department'] ?? ($isSuperAdmin ? null : $currentDepartment);
$selectedDivision = $_GET['division'] ?? null;

// Get department options for super admin
$departments = $isSuperAdmin
    ? $db->fetchAll("SELECT DISTINCT department FROM admin_remarks ORDER BY department")
    : [['department' => $currentDepartment]];

// Get divisions
$divisions = $db->fetchAll("SELECT DISTINCT division FROM admin_remarks ORDER BY division");

// Get report data
$departmentReport = $adminRemarksModel->getDepartmentRemarksReport($selectedDepartment, $dateFrom, $dateTo, $selectedDivision);
$overallStats = $adminRemarksModel->getRemarksStatistics($selectedDepartment, $dateFrom, $dateTo);
$topCategories = $adminRemarksModel->getTopRemarkCategories(10, $selectedDepartment);

// Calculate trends (compare with previous period)
$prevDateFrom = date('Y-m-d', strtotime($dateFrom . ' -1 month'));
$prevDateTo = date('Y-m-d', strtotime($dateTo . ' -1 month'));
$prevStats = $adminRemarksModel->getRemarksStatistics($selectedDepartment, $prevDateFrom, $prevDateTo);

$remarksTrend = $prevStats['total_remarks'] > 0
    ? (($overallStats['total_remarks'] - $prevStats['total_remarks']) / $prevStats['total_remarks']) * 100
    : 0;
?>

<div class="container-fluid py-4">
    <!-- Header and Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-lg-flex">
                        <div>
                            <h5 class="mb-0">Admin Remarks Report</h5>
                            <p class="text-sm mb-0">
                                Department-wise analysis of admin remarks and recurring issues
                            </p>
                        </div>
                        <div class="ms-auto my-auto">
                            <!-- Export Buttons -->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="exportToCSV()">
                                    <i class="fas fa-file-csv"></i> Export CSV
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="window.print()">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" class="row g-3">
                        <?php if ($isSuperAdmin): ?>
                        <div class="col-md-3">
                            <label class="form-label">Department</label>
                            <select name="department" class="form-select">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept['department']) ?>"
                                            <?= $selectedDepartment === $dept['department'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dept['department']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-3">
                            <label class="form-label">Division</label>
                            <select name="division" class="form-select">
                                <option value="">All Divisions</option>
                                <?php foreach ($divisions as $div): ?>
                                    <option value="<?= htmlspecialchars($div['division']) ?>"
                                            <?= $selectedDivision === $div['division'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($div['division']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">From Date</label>
                            <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">To Date</label>
                            <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Remarks</p>
                                <h5 class="font-weight-bolder mb-0">
                                    <?= number_format($overallStats['total_remarks'] ?? 0) ?>
                                    <small class="text-<?= $remarksTrend >= 0 ? 'success' : 'danger' ?> text-sm font-weight-bolder">
                                        <?= $remarksTrend >= 0 ? '+' : '' ?><?= number_format($remarksTrend, 1) ?>%
                                    </small>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="fas fa-comments text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Tickets with Remarks</p>
                                <h5 class="font-weight-bolder mb-0">
                                    <?= number_format($overallStats['tickets_with_remarks'] ?? 0) ?>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                <i class="fas fa-ticket-alt text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Recurring Issues</p>
                                <h5 class="font-weight-bolder mb-0">
                                    <?= number_format($overallStats['recurring_issues'] ?? 0) ?>
                                    <small class="text-warning text-sm">
                                        (<?= $overallStats['total_remarks'] > 0 ? round(($overallStats['recurring_issues'] / $overallStats['total_remarks']) * 100, 1) : 0 ?>%)
                                    </small>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                <i class="fas fa-redo text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Timely Response %</p>
                                <h5 class="font-weight-bolder mb-0">
                                    <?= number_format($overallStats['timely_remarks_percentage'] ?? 0, 1) ?>%
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="fas fa-clock text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Department Report Table -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Department-wise Remarks Analysis</h6>
                </div>
                <div class="card-body px-0 pb-0">
                    <?php if (empty($departmentReport)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-chart-bar text-muted" style="font-size: 3rem;"></i>
                            <h4 class="text-muted mt-3">No Data Available</h4>
                            <p class="text-muted">No admin remarks found for the selected period.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-flush" id="remarks-report-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Department</th>
                                        <th>Division</th>
                                        <th>Category</th>
                                        <th>Total Remarks</th>
                                        <th>Recurring Issues</th>
                                        <th>Timely Remarks</th>
                                        <th>Unique Tickets</th>
                                        <th>Period</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($departmentReport as $row): ?>
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold text-sm"><?= htmlspecialchars($row['department']) ?></span>
                                            </td>
                                            <td>
                                                <span class="text-sm"><?= htmlspecialchars($row['division']) ?></span>
                                            </td>
                                            <td>
                                                <?php if ($row['remarks_category']): ?>
                                                    <span class="badge badge-sm bg-gradient-info">
                                                        <?= htmlspecialchars($row['remarks_category']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-xs text-muted">General</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="font-weight-bold text-sm"><?= number_format($row['total_remarks']) ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2 text-sm"><?= number_format($row['recurring_issues']) ?></span>
                                                    <?php if ($row['recurring_issues'] > 0): ?>
                                                        <span class="badge badge-sm bg-gradient-warning">
                                                            <?= round(($row['recurring_issues'] / $row['total_remarks']) * 100, 1) ?>%
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2 text-sm"><?= number_format($row['remarks_within_3_days']) ?></span>
                                                    <span class="badge badge-sm bg-gradient-<?= ($row['remarks_within_3_days'] / $row['total_remarks']) >= 0.8 ? 'success' : 'warning' ?>">
                                                        <?= round(($row['remarks_within_3_days'] / $row['total_remarks']) * 100, 1) ?>%
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-sm"><?= number_format($row['unique_tickets']) ?></span>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="text-xs">
                                                        <?= date('d M Y', strtotime($row['first_remark_date'])) ?>
                                                    </span>
                                                    <br>
                                                    <span class="text-xs text-secondary">
                                                        to <?= date('d M Y', strtotime($row['last_remark_date'])) ?>
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Categories Chart -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Top Remark Categories</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($topCategories)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-tags text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">No categorized remarks found.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($topCategories as $index => $category): ?>
                            <div class="d-flex justify-content-between align-items-center border-radius-md mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="icon icon-sm icon-shape bg-gradient-<?= getCategoryColor($index) ?> shadow text-center border-radius-md">
                                        <span class="text-white text-xs font-weight-bold"><?= $index + 1 ?></span>
                                    </div>
                                    <div class="ms-3">
                                        <p class="text-sm mb-0 font-weight-bold"><?= htmlspecialchars($category['remarks_category']) ?></p>
                                        <p class="text-xs text-secondary mb-0">
                                            <?= $category['departments_affected'] ?> departments affected
                                            <?php if ($category['recurring_count'] > 0): ?>
                                                | <?= $category['recurring_count'] ?> recurring
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <p class="text-sm font-weight-bold mb-0"><?= number_format($category['category_count']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Export to CSV function
function exportToCSV() {
    const table = document.getElementById('remarks-report-table');
    let csv = [];
    const rows = table.querySelectorAll('tr');

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cols = row.querySelectorAll('td, th');
        const csvRow = [];

        for (let j = 0; j < cols.length; j++) {
            let cellText = cols[j].innerText.trim();
            // Clean up cell text (remove extra whitespace, newlines)
            cellText = cellText.replace(/\s+/g, ' ');
            csvRow.push(`"${cellText}"`);
        }

        csv.push(csvRow.join(','));
    }

    // Create and download CSV file
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');

    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `admin_remarks_report_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Auto-refresh chart or update data every 5 minutes
setInterval(() => {
    if (document.visibilityState === 'visible') {
        // Only refresh if page is visible
        window.location.reload();
    }
}, 300000); // 5 minutes
</script>

<?php
function getCategoryColor($index) {
    $colors = ['primary', 'info', 'success', 'warning', 'danger', 'secondary'];
    return $colors[$index % count($colors)];
}
?>