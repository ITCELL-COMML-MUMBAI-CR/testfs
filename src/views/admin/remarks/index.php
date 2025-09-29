<?php
/**
 * Admin Remarks Management Interface
 * View and manage admin remarks on closed tickets
 */
ob_start();

$currentUser = $_SESSION['user'];
$currentDepartment = $currentUser['department'];
$isSuperAdmin = $currentUser['role'] === 'superadmin';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query based on filters
$whereClauses = [];
$params = [];

// Department filter for non-superadmin users
if (!$isSuperAdmin) {
    $whereClauses[] = "ar.department = ?";
    $params[] = $currentDepartment;
}

if (isset($_GET['department']) && !empty($_GET['department'])) {
    if ($isSuperAdmin) {
        $whereClauses[] = "ar.department = ?";
        $params[] = $_GET['department'];
    }
}

if (isset($_GET['division']) && !empty($_GET['division'])) {
    $whereClauses[] = "ar.division = ?";
    $params[] = $_GET['division'];
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $whereClauses[] = "ar.remarks_category = ?";
    $params[] = $_GET['category'];
}

if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
    $whereClauses[] = "DATE(ar.created_at) >= ?";
    $params[] = $_GET['date_from'];
}

if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
    $whereClauses[] = "DATE(ar.created_at) <= ?";
    $params[] = $_GET['date_to'];
}

$whereClause = empty($whereClauses) ? '' : 'WHERE ' . implode(' AND ', $whereClauses);

// Get remarks with ticket details
$sql = "SELECT ar.*,
               u.name as admin_name, u.role as admin_role,
               c.complaint_id, c.description as ticket_description,
               c.closed_at, c.priority,
               cust.name as customer_name, cust.company_name
        FROM admin_remarks ar
        LEFT JOIN users u ON ar.admin_id = u.id
        LEFT JOIN complaints c ON ar.complaint_id = c.complaint_id
        LEFT JOIN customers cust ON c.customer_id = cust.customer_id
        {$whereClause}
        ORDER BY ar.created_at DESC
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;

$remarks = $db->fetchAll($sql, $params);

// Get total count for pagination
$countSql = "SELECT COUNT(*) as total FROM admin_remarks ar {$whereClause}";
$countParams = array_slice($params, 0, -2); // Remove limit and offset
$totalCount = $db->fetch($countSql, $countParams)['total'] ?? 0;
$totalPages = ceil($totalCount / $limit);

// Get filter options
$departments = $isSuperAdmin
    ? $db->fetchAll("SELECT DISTINCT department FROM admin_remarks ORDER BY department")
    : [['department' => $currentDepartment]];

$divisions = $db->fetchAll("SELECT DISTINCT division FROM admin_remarks ORDER BY division");
$categories = $db->fetchAll("SELECT DISTINCT remarks_category FROM admin_remarks WHERE remarks_category IS NOT NULL ORDER BY remarks_category");

// Get statistics
$statsSql = "SELECT
                COUNT(*) as total_remarks,
                COUNT(DISTINCT complaint_id) as tickets_with_remarks,
                COUNT(CASE WHEN is_recurring_issue = 1 THEN 1 END) as recurring_issues,
                COUNT(CASE WHEN created_within_3_days = 1 THEN 1 END) as timely_remarks
             FROM admin_remarks ar
             " . ($isSuperAdmin ? '' : 'WHERE ar.department = ?');

$statsParams = $isSuperAdmin ? [] : [$currentDepartment];
$stats = $db->fetch($statsSql, $statsParams);
?>

<div class="container-fluid py-4">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Remarks</p>
                                <h5 class="font-weight-bolder mb-0">
                                    <?= number_format($stats['total_remarks'] ?? 0) ?>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="ni ni-chat-round text-lg opacity-10" aria-hidden="true"></i>
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
                                    <?= number_format($stats['tickets_with_remarks'] ?? 0) ?>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                <i class="ni ni-tag text-lg opacity-10" aria-hidden="true"></i>
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
                                    <?= number_format($stats['recurring_issues'] ?? 0) ?>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                <i class="ni ni-refresh text-lg opacity-10" aria-hidden="true"></i>
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
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Timely Remarks</p>
                                <h5 class="font-weight-bolder mb-0">
                                    <?= number_format($stats['timely_remarks'] ?? 0) ?>
                                    <small class="text-sm text-success">
                                        (<?= $stats['total_remarks'] > 0 ? round(($stats['timely_remarks'] / $stats['total_remarks']) * 100, 1) : 0 ?>%)
                                    </small>
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="ni ni-time-alarm text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-lg-flex">
                        <div>
                            <h5 class="mb-0">Admin Remarks</h5>
                            <p class="text-sm mb-0">
                                Manage and review admin remarks on tickets
                            </p>
                        </div>
                        <div class="ms-auto my-auto mt-lg-0 mt-4">
                            <!-- Filter Form -->
                            <form method="GET" class="d-flex align-items-center flex-wrap">
                                <input type="hidden" name="page" value="1">

                                <?php if ($isSuperAdmin): ?>
                                <select name="department" class="form-select form-select-sm me-2 mb-2" style="width: auto;">
                                    <option value="">All Departments</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= htmlspecialchars($dept['department']) ?>"
                                                <?= ($_GET['department'] ?? '') === $dept['department'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dept['department']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php endif; ?>

                                <select name="division" class="form-select form-select-sm me-2 mb-2" style="width: auto;">
                                    <option value="">All Divisions</option>
                                    <?php foreach ($divisions as $div): ?>
                                        <option value="<?= htmlspecialchars($div['division']) ?>"
                                                <?= ($_GET['division'] ?? '') === $div['division'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($div['division']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <select name="category" class="form-select form-select-sm me-2 mb-2" style="width: auto;">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['remarks_category']) ?>"
                                                <?= ($_GET['category'] ?? '') === $cat['remarks_category'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['remarks_category']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <input type="date" name="date_from" value="<?= $_GET['date_from'] ?? '' ?>"
                                       class="form-control form-control-sm me-2 mb-2" style="width: 150px;" placeholder="From">

                                <input type="date" name="date_to" value="<?= $_GET['date_to'] ?? '' ?>"
                                       class="form-control form-control-sm me-2 mb-2" style="width: 150px;" placeholder="To">

                                <button type="submit" class="btn btn-outline-primary btn-sm mb-2">Filter</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body px-0 pb-0">
                    <?php if (empty($remarks)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-comments text-muted" style="font-size: 3rem;"></i>
                            <h4 class="text-muted mt-3">No Admin Remarks Found</h4>
                            <p class="text-muted">No admin remarks match your current filters.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Ticket</th>
                                        <th>Customer</th>
                                        <th>Department</th>
                                        <th>Category</th>
                                        <th>Remarks</th>
                                        <th>Admin</th>
                                        <th>Created</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($remarks as $remark): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0 text-sm">
                                                            <a href="/admin/tickets/<?= $remark['complaint_id'] ?>" class="text-dark font-weight-bold">
                                                                #<?= htmlspecialchars($remark['complaint_id']) ?>
                                                            </a>
                                                        </h6>
                                                        <p class="text-xs text-secondary mb-0">
                                                            Priority: <span class="badge badge-sm bg-<?= getPriorityColor($remark['priority']) ?>">
                                                                <?= ucfirst($remark['priority']) ?>
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <h6 class="mb-0 text-sm"><?= htmlspecialchars($remark['customer_name']) ?></h6>
                                                    <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($remark['company_name']) ?></p>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-xs font-weight-bold"><?= htmlspecialchars($remark['department']) ?></span>
                                                <br>
                                                <span class="text-xs text-secondary"><?= htmlspecialchars($remark['division']) ?></span>
                                            </td>
                                            <td>
                                                <?php if ($remark['remarks_category']): ?>
                                                    <span class="badge badge-sm bg-gradient-info">
                                                        <?= htmlspecialchars($remark['remarks_category']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-xs text-muted">No category</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="remarks-preview" style="max-width: 300px;">
                                                    <p class="text-xs mb-1">
                                                        <?= htmlspecialchars(substr($remark['remarks'], 0, 100)) ?>
                                                        <?= strlen($remark['remarks']) > 100 ? '...' : '' ?>
                                                    </p>
                                                    <?php if ($remark['is_recurring_issue']): ?>
                                                        <span class="badge badge-sm bg-gradient-warning">Recurring Issue</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <h6 class="mb-0 text-sm"><?= htmlspecialchars($remark['admin_name']) ?></h6>
                                                    <p class="text-xs text-secondary mb-0">
                                                        <?= ucfirst(str_replace('_', ' ', $remark['admin_type'])) ?>
                                                    </p>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="text-xs">
                                                        <?= date('d M Y', strtotime($remark['created_at'])) ?>
                                                    </span>
                                                    <br>
                                                    <span class="text-xs text-secondary">
                                                        <?= date('H:i', strtotime($remark['created_at'])) ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($remark['created_within_3_days']): ?>
                                                    <span class="badge badge-sm bg-gradient-success">Timely</span>
                                                <?php else: ?>
                                                    <span class="badge badge-sm bg-gradient-secondary">Late</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center p-3">
                                <p class="text-sm text-secondary mb-0">
                                    Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $totalCount) ?> of <?= $totalCount ?> entries
                                </p>

                                <nav aria-label="Pagination">
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $page - 1 ?><?= http_build_query(array_diff_key($_GET, ['page' => ''])) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">
                                                    <i class="fas fa-angle-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?><?= http_build_query(array_diff_key($_GET, ['page' => ''])) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $page + 1 ?><?= http_build_query(array_diff_key($_GET, ['page' => ''])) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">
                                                    <i class="fas fa-angle-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
function getPriorityColor($priority) {
    $colors = [
        'critical' => 'danger',
        'high' => 'warning',
        'medium' => 'info',
        'normal' => 'secondary'
    ];
    return $colors[$priority] ?? 'secondary';
}
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>