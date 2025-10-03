<?php
/**
 * Admin Approvals - Pending Tickets View
 * Shows tickets awaiting admin approval (both department and CML)
 */

// Start output buffering for layout
ob_start();

// Data passed from controller
$currentUser = $current_user;
$isDeptAdmin = $is_dept_admin;
$isCmlAdmin = $is_cml_admin;
$statusFilter = $status_filter;
$pageTitle = $page_title_display;
$tickets = $tickets;
$totalCount = $total_count;
$totalPages = $total_pages;
$page = $current_page;
$limit = $limit;
$offset = $offset;
$divisions = $divisions;
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-lg-flex">
                        <div>
                            <h5 class="mb-0"><?= $pageTitle ?></h5>
                            <p class="text-sm mb-0">
                                Tickets requiring your approval - Total: <strong><?= $totalCount ?></strong>
                            </p>
                        </div>
                        <div class="ms-auto my-auto mt-lg-0 mt-4">
                            <div class="ms-auto my-auto">
                                <!-- Filter Form -->
                                <form method="GET" class="d-flex align-items-center">
                                    <input type="hidden" name="page" value="1">

                                    <?php if ($isCmlAdmin): ?>
                                    <select name="division" class="form-select form-select-sm me-2" style="width: auto;">
                                        <option value="">All Divisions</option>
                                        <?php foreach ($divisions as $div): ?>
                                            <option value="<?= htmlspecialchars($div['division']) ?>"
                                                    <?= ($_GET['division'] ?? '') === $div['division'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($div['division']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php endif; ?>

                                    <select name="priority" class="form-select form-select-sm me-2" style="width: auto;">
                                        <option value="">All Priorities</option>
                                        <option value="critical" <?= ($_GET['priority'] ?? '') === 'critical' ? 'selected' : '' ?>>Critical</option>
                                        <option value="high" <?= ($_GET['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                                        <option value="medium" <?= ($_GET['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                                        <option value="normal" <?= ($_GET['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
                                    </select>

                                    <button type="submit" class="btn btn-outline-primary btn-sm">Filter</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body px-0 pb-0">
                    <?php if (empty($tickets)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            <h4 class="text-muted mt-3">No Pending Approvals</h4>
                            <p class="text-muted">All tickets have been processed.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-flush" id="approvals-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Customer</th>
                                        <th>Category</th>
                                        <th>Priority</th>
                                        <th>Department</th>
                                        <th>Division</th>
                                        <th>Pending Time</th>
                                        <?php if ($isDeptAdmin): ?>
                                            <th>Previous Approval</th>
                                        <?php endif; ?>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tickets as $ticket): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-gradient-<?= getPriorityColor($ticket['priority']) ?> me-2"><?= strtoupper($ticket['priority']) ?></span>
                                                    <strong>#<?= htmlspecialchars($ticket['complaint_id']) ?></strong>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <h6 class="mb-0 text-sm"><?= htmlspecialchars($ticket['customer_name']) ?></h6>
                                                    <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($ticket['customer_email']) ?></p>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="text-xs font-weight-bold"><?= htmlspecialchars($ticket['category']) ?></span><br>
                                                    <span class="text-xs text-secondary"><?= htmlspecialchars($ticket['type']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-sm bg-gradient-<?= getPriorityColor($ticket['priority']) ?>">
                                                    <?= ucfirst($ticket['priority']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-xs font-weight-bold"><?= htmlspecialchars($ticket['department'] ?? 'N/A') ?></span>
                                            </td>
                                            <td>
                                                <span class="text-xs"><?= htmlspecialchars($ticket['division']) ?></span>
                                            </td>
                                            <td>
                                                <div>
                                                    <?php
                                                    $hoursPending = $ticket['hours_pending'];
                                                    $timeClass = $hoursPending > 24 ? 'text-danger' : ($hoursPending > 12 ? 'text-warning' : 'text-info');

                                                    if ($hoursPending < 1) {
                                                        echo '<span class="text-xs ' . $timeClass . '">< 1 hour</span>';
                                                    } elseif ($hoursPending < 24) {
                                                        echo '<span class="text-xs ' . $timeClass . '">' . $hoursPending . ' hours</span>';
                                                    } else {
                                                        $days = floor($hoursPending / 24);
                                                        $remainingHours = $hoursPending % 24;
                                                        echo '<span class="text-xs ' . $timeClass . '">' . $days . 'd ' . $remainingHours . 'h</span>';
                                                    }
                                                    ?>
                                                </div>
                                            </td>
                                            <?php if ($isDeptAdmin): ?>
                                                <td>
                                                    <?php if ($ticket['dept_admin_name']): ?>
                                                        <span class="text-xs text-success">✓ <?= htmlspecialchars($ticket['dept_admin_name']) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-xs text-muted">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?= Config::getAppUrl() ?>/admin/tickets/view/<?= $ticket['complaint_id'] ?>"
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-eye"></i> Review & Approve
                                                    </a>
                                                </div>
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

<script>
// Auto-refresh every 30 seconds to show new approvals
setTimeout(() => {
    window.location.reload();
}, 30000);

// Priority color helper
function getPriorityBadgeClass(priority) {
    const colors = {
        'critical': 'danger',
        'high': 'warning',
        'medium': 'info',
        'normal': 'secondary'
    };
    return colors[priority] || 'secondary';
}
</script>

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

// Get content and include layout
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
?>