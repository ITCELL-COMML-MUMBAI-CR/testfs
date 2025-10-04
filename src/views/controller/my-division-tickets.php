<?php
/**
 * Controller Nodal Tickets Management View (RBAC Restricted) - SAMPARK
 * Shows only tickets within nodal controller's division/zone with their specific permissions
 */

// Capture the content
ob_start();

// Set additional CSS for this view
$additional_css = [
    Config::getAppUrl() . '/assets/css/controller-views.css'
];

// Set page title
$page_title = 'My Division Tickets - SAMPARK';
?>

<div class="container-fluid px-4 py-4" style="max-width: 95%;">
    <!-- Page Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <div class="d-flex align-items-center">
                <div class="bg-apple-blue rounded-3 p-3 me-3">
                    <i class="fas fa-ticket-alt text-dark fa-lg"></i>
                </div>
                <div>
                    <h1 class="h3 mb-1 fw-semibold">My Division - All Tickets</h1>
                    <p class="text-muted mb-0">
                        All tickets within your division (<?= htmlspecialchars($user['division'] ?? 'N/A') ?>)
                        <?= $user['zone'] ? '- Zone ' . htmlspecialchars($user['zone']) : '' ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <a href="<?= Config::getAppUrl() ?>/controller/forwarded-tickets" class="action-btn action-btn-info action-btn-with-text">
                    <i class="fas fa-share-alt"></i>Forwarded Tickets
                </a>
                <a href="<?= Config::getAppUrl() ?>/controller/search-all" class="action-btn action-btn-info action-btn-with-text">
                    <i class="fas fa-search"></i>Search All Tickets
                </a>
                <button class="action-btn action-btn-secondary action-btn-with-text" onclick="exportTickets()">
                    <i class="fas fa-download"></i>Export
                </button>
                <button class="action-btn action-btn-primary action-btn-with-text" onclick="forceRefresh()">
                    <i class="fas fa-sync-alt"></i>Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Info Notice -->
    <div class="alert alert-primary d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-sitemap me-2"></i>
        <div>
            <strong>Division View:</strong> This page shows all tickets within your division (any status).
            As a nodal controller, you can view and manage all tickets in your division.
            Use "Search All Tickets" for system-wide search.
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-apple h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fas fa-clock text-primary fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Pending</div>
                            <div class="h4 mb-0 fw-semibold" id="pendingCount" data-stat="pending">
                                <?= $tickets['pending'] ?? 0 ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-apple h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fas fa-exclamation-triangle text-warning fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small">High Priority</div>
                            <div class="h4 mb-0 fw-semibold" id="highPriorityCount" data-stat="high_priority">
                                <?= $tickets['high_priority'] ?? 0 ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-apple h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fas fa-share-alt text-info fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Forwarded</div>
                            <div class="h4 mb-0 fw-semibold" id="forwardedCount" data-stat="forwarded">
                                <?= $tickets['forwarded'] ?? 0 ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-apple h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fas fa-hourglass-half text-primary fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Awaiting Approval</div>
                            <div class="h4 mb-0 fw-semibold" id="awaitingApprovalCount" data-stat="awaiting_approval">
                                <?= $tickets['awaiting_approval'] ?? 0 ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters Panel -->
    <div class="card card-apple mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filters
            </h5>
            <button class="action-btn action-btn-secondary action-btn-compact" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse" title="Toggle Filters">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        <div class="collapse show" id="filtersCollapse">
            <div class="card-body">
                <form id="filtersForm" onsubmit="applyFilters(event)">
                    <div class="row g-3 align-items-end">
                        <!-- Status Filter -->
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label-apple">Status</label>
                            <select class="form-control-apple ticket-filter" name="status" id="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="awaiting_info" <?= ($filters['status'] ?? '') === 'awaiting_info' ? 'selected' : '' ?>>Awaiting Info</option>
                                <option value="awaiting_approval" <?= ($filters['status'] ?? '') === 'awaiting_approval' ? 'selected' : '' ?>>Awaiting Approval</option>
                                <option value="awaiting_feedback" <?= ($filters['status'] ?? '') === 'awaiting_feedback' ? 'selected' : '' ?>>Awaiting Feedback</option>
                                <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                            </select>
                        </div>

                        <!-- Priority Filter -->
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label-apple">Priority</label>
                            <select class="form-control-apple ticket-filter" name="priority" id="priorityFilter">
                                <option value="">All Priorities</option>
                                <option value="critical" <?= ($filters['priority'] ?? '') === 'critical' ? 'selected' : '' ?>>Critical</option>
                                <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                                <option value="medium" <?= ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="normal" <?= ($filters['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
                            </select>
                        </div>

                        <!-- Department Filter -->
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label-apple">Department</label>
                            <select class="form-control-apple ticket-filter" name="department" id="departmentFilter">
                                <option value="">All Departments</option>
                                <?php if (!empty($departments)): ?>
                                    <?php foreach ($departments as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept['department_code']) ?>"
                                            <?= ($filters['department'] ?? '') === $dept['department_code'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dept['department_name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Date From -->
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label-apple">Date From</label>
                            <input type="date" class="form-control-apple ticket-filter" name="date_from" id="dateFromFilter"
                                   value="<?= $filters['date_from'] ?? '' ?>">
                        </div>

                        <!-- Date To -->
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label-apple">Date To</label>
                            <input type="date" class="form-control-apple ticket-filter" name="date_to" id="dateToFilter"
                                   value="<?= $filters['date_to'] ?? '' ?>">
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-md-12 col-lg-2">
                            <div class="d-flex gap-2">
                                <button type="button" class="action-btn action-btn-secondary action-btn-with-text flex-fill" onclick="clearFilters()">
                                    <i class="fas fa-times"></i>Clear
                                </button>
                                <button type="submit" class="action-btn action-btn-primary action-btn-with-text flex-fill">
                                    <i class="fas fa-search"></i>Apply
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card card-apple">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Division Tickets
            </h5>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive" style="margin: -0.5rem;">
                <table class="table table-hover mb-0" id="nodalTicketsTable" style="margin: 0.75rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0" style="width: 120px;">Ticket ID</th>
                            <th class="border-0" style="width: 100px;">Priority</th>
                            <th class="border-0" style="width: 200px;">Customer</th>
                            <th class="border-0" style="width: 180px;">Category</th>
                            <th class="border-0" style="width: 140px;">Status</th>
                            <th class="border-0" style="width: 160px;">Assigned To</th>
                            <th class="border-0" style="width: 120px;">Created</th>
                            <th class="border-0" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets['data'])): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-center">
                                    <i class="fas fa-inbox text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mb-2">No tickets found</h5>
                                    <p class="text-muted">No tickets are currently available within your division. New tickets will appear here when created.</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($tickets['data'] as $ticket): ?>
                            <tr class="ticket-row" data-ticket-id="<?= $ticket['complaint_id'] ?>">
                                <td>
                                    <a href="<?= Config::getAppUrl() ?>/controller/tickets/<?= $ticket['complaint_id'] ?>"
                                       class="fw-semibold text-decoration-none text-primary">
                                        #<?= $ticket['complaint_id'] ?>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $priorityClass = [
                                        'critical' => 'danger',
                                        'high' => 'warning',
                                        'medium' => 'info',
                                        'normal' => 'secondary'
                                    ][$ticket['priority']] ?? 'secondary';

                                    $priorityIcon = [
                                        'critical' => 'exclamation-circle',
                                        'high' => 'exclamation-triangle',
                                        'medium' => 'info-circle',
                                        'normal' => 'circle'
                                    ][$ticket['priority']] ?? 'circle';
                                    ?>
                                    <span class="badge bg-<?= $priorityClass ?> px-2 py-1">
                                        <i class="fas fa-<?= $priorityIcon ?> me-1"></i><?= ucfirst($ticket['priority']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="text-truncate">
                                        <div class="fw-semibold text-truncate" style="max-width: 180px;"
                                             title="<?= htmlspecialchars($ticket['customer_name'] ?? 'N/A') ?>">
                                            <?= htmlspecialchars($ticket['customer_name'] ?? 'N/A') ?>
                                        </div>
                                        <?php if ($ticket['company_name']): ?>
                                        <small class="text-muted text-truncate d-block"
                                               style="max-width: 180px;"
                                               title="<?= htmlspecialchars($ticket['company_name']) ?>">
                                            <?= htmlspecialchars($ticket['company_name']) ?>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-semibold text-truncate d-block"
                                              style="max-width: 160px;"
                                              title="<?= htmlspecialchars($ticket['category'] ?? 'N/A') ?>">
                                            <?= htmlspecialchars($ticket['category'] ?? 'N/A') ?>
                                        </span>
                                        <?php if ($ticket['subtype']): ?>
                                        <small class="text-muted text-truncate d-block"
                                               style="max-width: 160px;"
                                               title="<?= htmlspecialchars($ticket['subtype']) ?>">
                                            <?= htmlspecialchars($ticket['subtype']) ?>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $statusClass = [
                                        'pending' => 'warning',
                                        'awaiting_info' => 'info',
                                        'awaiting_approval' => 'primary',
                                        'awaiting_feedback' => 'success',
                                        'closed' => 'dark'
                                    ][$ticket['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?> px-2 py-1">
                                        <?= ucwords(str_replace('_', ' ', $ticket['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="text-truncate">
                                        <span class="text-truncate d-block"
                                              style="max-width: 140px;"
                                              title="<?= htmlspecialchars($ticket['assigned_to_department'] ?? 'Unassigned') ?>">
                                            <?= htmlspecialchars($ticket['assigned_to_department'] ?? 'Unassigned') ?>
                                        </span>
                                        <?php if ($ticket['zone']): ?>
                                        <small class="text-muted text-truncate d-block"
                                               style="max-width: 140px;"
                                               title="Zone: <?= htmlspecialchars($ticket['zone']) ?>">
                                            Zone: <?= htmlspecialchars($ticket['zone']) ?>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-nowrap">
                                        <div class="fw-semibold"><?= date('M d', strtotime($ticket['created_at'])) ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($ticket['created_at'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons-group" role="group">
                                        <a href="<?= Config::getAppUrl() ?>/controller/tickets/<?= $ticket['complaint_id'] ?>"
                                           class="action-btn action-btn-primary action-btn-compact" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <?php
                                        // Show forward button for pending tickets
                                        if (in_array($ticket['status'], ['pending', 'awaiting_info'])):
                                        ?>
                                        <button class="action-btn action-btn-warning action-btn-compact"
                                                onclick="forwardTicket(<?= $ticket['complaint_id'] ?>)" title="Forward Ticket">
                                            <i class="fas fa-share"></i>
                                        </button>
                                        <?php endif; ?>

                                        <?php
                                        // Show internal remarks button for pending tickets
                                        if ($ticket['status'] === 'pending'):
                                        ?>
                                        <button class="action-btn action-btn-secondary action-btn-compact"
                                                onclick="addInternalRemarks(<?= $ticket['complaint_id'] ?>)" title="Add Internal Note">
                                            <i class="fas fa-sticky-note"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if (!empty($tickets['data']) && ($tickets['total_pages'] ?? 1) > 1): ?>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing <?= (($tickets['page'] - 1) * $tickets['per_page']) + 1 ?> to
                    <?= min($tickets['page'] * $tickets['per_page'], $tickets['total']) ?> of
                    <?= $tickets['total'] ?> entries
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($tickets['has_prev']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $tickets['page'] - 1 ?>&<?= http_build_query(array_filter($_GET, fn($k) => $k !== 'page')) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $tickets['page'] - 2);
                                   $i <= min($tickets['total_pages'], $tickets['page'] + 2);
                                   $i++): ?>
                        <li class="page-item <?= $i === $tickets['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(array_filter($_GET, fn($k) => $k !== 'page')) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($tickets['has_next']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $tickets['page'] + 1 ?>&<?= http_build_query(array_filter($_GET, fn($k) => $k !== 'page')) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- Forward Ticket Modal -->
<div class="modal fade" id="forwardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Forward Ticket #<span id="forwardTicketId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="forwardForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-apple">Forward To Zone *</label>
                        <select class="form-control-apple" name="zone" id="forwardZone" required>
                            <option value="">Select Zone...</option>
                            <!-- Zones will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Forward To Division *</label>
                        <select class="form-control-apple" name="division" id="forwardDivision" required>
                            <option value="">Select Division...</option>
                            <!-- Divisions will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Forward To Department *</label>
                        <select class="form-control-apple" name="department" id="forwardDepartment" required>
                            <option value="">Select Department...</option>
                            <!-- Departments will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Internal Remarks *</label>
                        <textarea class="form-control-apple" name="internal_remarks" rows="4" required
                                  placeholder="Add internal remarks for forwarding this ticket..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="action-btn action-btn-secondary action-btn-with-text" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>Cancel
                    </button>
                    <button type="submit" class="action-btn action-btn-primary action-btn-with-text" id="forwardSubmitBtn">
                        <i class="fas fa-share"></i>Forward Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Include the same JavaScript as the original tickets page, but adapted for nodal controller RBAC
document.addEventListener('DOMContentLoaded', function() {
    initializeBasicDataTable();
    updateStats();
    setupEventListeners();
    console.log('Controller Nodal RBAC tickets page initialized');
});

function setupEventListeners() {
    document.querySelectorAll('.ticket-filter').forEach(element => {
        element.addEventListener('change', function() {
            applyFilters();
        });
    });
}

function applyFilters(event) {
    if (event) {
        event.preventDefault();
    }

    const form = document.getElementById('filtersForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();

    for (let [key, value] of formData.entries()) {
        if (value.trim() !== '') {
            params.append(key, value);
        }
    }

    const newUrl = window.location.pathname + '?' + params.toString();
    window.history.pushState({}, '', newUrl);
    window.location.reload();
}

function clearFilters() {
    document.getElementById('filtersForm').reset();
    window.location.href = window.location.pathname;
}

function forceRefresh() {
    updateStats();
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    button.disabled = true;

    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

function initializeBasicDataTable() {
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        try {
            const $table = $('#nodalTicketsTable');

            if ($table.length === 0) {
                console.warn('Table not found');
                return;
            }

            if ($.fn.DataTable.isDataTable('#nodalTicketsTable')) {
                $table.DataTable().destroy();
                $table.empty();
            }

            const headerCols = $table.find('thead tr th').length;
            const dataRowCount = $table.find('tbody tr').not(':has(td[colspan])').length;
            const hasEmptyMessage = $table.find('tbody tr:first td[colspan]').length > 0;

            if (headerCols > 0 && dataRowCount > 0 && !hasEmptyMessage) {
                $table.DataTable({
                    paging: true,
                    searching: true,
                    ordering: false,
                    info: true,
                    responsive: true,
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    autoWidth: false,
                    deferRender: true,
                    language: {
                        search: "Search tickets:",
                        lengthMenu: "Show _MENU_ tickets",
                        info: "Showing _START_ to _END_ of _TOTAL_ tickets",
                        infoEmpty: "No tickets available in your division",
                        infoFiltered: "(filtered from _MAX_ total tickets)",
                        emptyTable: "No tickets found matching your criteria",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "Next",
                            previous: "Previous"
                        }
                    }
                });
                console.log('DataTable initialized successfully for nodal controller');
            } else {
                console.log('Skipping DataTables initialization - no data rows available');
            }
        } catch (error) {
            console.error('Error initializing DataTable:', error);
        }
    }
}

function exportTickets() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', '1');
    window.location.href = window.location.pathname + '?' + params.toString();
}

function forwardTicket(ticketId) {
    document.getElementById('forwardTicketId').textContent = ticketId;
    document.getElementById('forwardForm').dataset.ticketId = ticketId;

    loadZonesAndDivisions().then(() => {
        new bootstrap.Modal(document.getElementById('forwardModal')).show();
    });
}

async function loadZonesAndDivisions() {
    try {
        const userZone = '<?= $user['zone'] ?? '' ?>';
        const userDivision = '<?= $user['division'] ?? '' ?>';

        // Load zones
        const zonesResponse = await fetch(`${APP_URL}/api/zones`);
        const zonesData = await zonesResponse.json();

        const zoneSelect = document.getElementById('forwardZone');
        if (zoneSelect && zonesData.success) {
            zoneSelect.innerHTML = '<option value="">Select Zone...</option>';
            zonesData.zones.forEach(zone => {
                const option = document.createElement('option');
                option.value = zone.zone;
                option.textContent = `${zone.zone} - ${zone.zone_name}`;
                if (zone.zone === userZone) {
                    option.selected = true;
                }
                zoneSelect.appendChild(option);
            });
        }

        await loadDivisionsForZone(userZone || '');
        await loadDepartments();

        if (zoneSelect) {
            zoneSelect.addEventListener('change', async function() {
                const selectedZone = this.value;
                await loadDivisionsForZone(selectedZone);
            });
        }

        const divisionSelect = document.getElementById('forwardDivision');
        if (divisionSelect) {
            divisionSelect.addEventListener('change', function() {
                updateDepartmentVisibility();
            });
        }
    } catch (error) {
        console.error('Error loading zones and divisions:', error);
    }
}

async function loadDivisionsForZone(zoneCode) {
    try {
        const divisionSelect = document.getElementById('forwardDivision');
        if (!divisionSelect) return;

        const userDivision = '<?= $user['division'] ?? '' ?>';

        divisionSelect.innerHTML = '<option value="">Select Division...</option>';

        if (zoneCode) {
            const response = await fetch(`${APP_URL}/api/divisions?zone=${zoneCode}`);
            const data = await response.json();

            if (data.success) {
                data.divisions.forEach(division => {
                    const option = document.createElement('option');
                    option.value = division.division;
                    option.textContent = `${division.division} - ${division.division_name}`;
                    if (division.division === userDivision) {
                        option.selected = true;
                    }
                    divisionSelect.appendChild(option);
                });

                setTimeout(() => updateDepartmentVisibility(), 100);
            }
        }
    } catch (error) {
        console.error('Error loading divisions for zone:', error);
    }
}

let allDepartments = [];

async function loadDepartments() {
    try {
        const response = await fetch(`${APP_URL}/api/departments`);
        const data = await response.json();

        if (data.success) {
            allDepartments = data.departments;
            const nodalDeptSelect = document.getElementById('forwardDepartment');
            if (nodalDeptSelect) {
                populateDepartmentSelect(nodalDeptSelect, allDepartments);
            }
            updateDepartmentVisibility();
        }
    } catch (error) {
        console.error('Error loading departments:', error);
    }
}

function populateDepartmentSelect(selectElement, departments) {
    selectElement.innerHTML = '<option value="">Select Department...</option>';
    departments.forEach(dept => {
        const option = document.createElement('option');
        option.value = dept.department_code;
        option.textContent = dept.department_name;
        option.dataset.departmentCode = dept.department_code;
        selectElement.appendChild(option);
    });
}

function updateDepartmentVisibility() {
    const userRole = '<?= $user['role'] ?? '' ?>';
    const userDivision = '<?= $user['division'] ?? '' ?>';
    const selectedDivision = document.getElementById('forwardDivision')?.value || userDivision;
    const nodalDeptSelect = document.getElementById('forwardDepartment');

    // Filter for Commercial departments
    const commercialDepts = allDepartments.filter(dept => {
        return dept.department_code === 'COMM' ||
               dept.department_code === 'CML' ||
               dept.department_code === 'Commercial' ||
               dept.department_name.toLowerCase().includes('commercial');
    });

    if (nodalDeptSelect && userRole === 'controller_nodal') {
        if (selectedDivision && selectedDivision !== userDivision && selectedDivision !== '') {
            // Forwarding outside division - only Commercial
            if (commercialDepts.length === 0) {
                nodalDeptSelect.innerHTML = '<option value="">Select Department...</option><option value="COMM">Commercial</option>';
            } else {
                populateDepartmentSelect(nodalDeptSelect, commercialDepts);
            }
        } else {
            // Forwarding within division - all departments
            populateDepartmentSelect(nodalDeptSelect, allDepartments);
        }
    }
}

function addInternalRemarks(ticketId) {
    Swal.fire({
        title: 'Add Internal Note',
        text: 'This note will be internal only and not visible to the customer.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Add Note',
        cancelButtonText: 'Cancel',
        input: 'textarea',
        inputPlaceholder: 'Enter internal note for team reference...',
        inputAttributes: {
            'aria-label': 'Internal remarks'
        },
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide internal remarks!'
            }
            if (value.length < 5) {
                return 'Internal remarks must be at least 5 characters long!'
            }
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN);
            formData.append('internal_remarks', result.value);

            try {
                showLoading();
                const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/internal-remarks`, {
                    method: 'POST',
                    body: formData
                });

                const apiResult = await response.json();
                hideLoading();

                if (apiResult.success) {
                    Swal.fire('Success', apiResult.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    if (apiResult.errors) {
                        const errors = Object.values(apiResult.errors).join('\n');
                        Swal.fire('Validation Error', errors, 'error');
                    } else {
                        Swal.fire('Error', apiResult.message, 'error');
                    }
                }
            } catch (error) {
                hideLoading();
                Swal.fire('Error', 'Failed to add internal remarks', 'error');
            }
        }
    });
}

// Form submission for forwarding
document.getElementById('forwardForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('forwardSubmitBtn');
    const originalContent = submitBtn.innerHTML;

    // Disable button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Forwarding...';

    const ticketId = this.dataset.ticketId;
    const formData = new FormData(this);
    formData.append('csrf_token', CSRF_TOKEN);

    try {
        showLoading();
        const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/forward`, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        hideLoading();

        if (result.success) {
            Swal.fire('Success', result.message, 'success').then(() => {
                location.reload();
            });
        } else {
            // Re-enable button on error
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalContent;

            if (result.errors) {
                const errors = Object.values(result.errors).join('\n');
                Swal.fire('Validation Error', errors, 'error');
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        }
    } catch (error) {
        hideLoading();
        // Re-enable button on error
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalContent;
        Swal.fire('Error', 'Failed to forward ticket', 'error');
    }

    bootstrap.Modal.getInstance(document.getElementById('forwardModal')).hide();
});

function updateStats() {
    fetch(`${APP_URL}/api/tickets/stats`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.stats) {
                const stats = data.stats;
                document.getElementById('pendingCount').textContent = stats.pending || 0;
                document.getElementById('highPriorityCount').textContent = stats.high_priority || 0;
                document.getElementById('forwardedCount').textContent = stats.forwarded || 0;
                document.getElementById('awaitingApprovalCount').textContent = stats.awaiting_approval || 0;
            }
        })
        .catch(error => console.error('Error loading stats:', error));
}
</script>

<!-- Include the same styles from the original tickets page with some modifications -->
<style>
/* All existing styles from tickets.php plus specific ones for nodal controller */

.ticket-row:hover {
    background-color: var(--bs-light);
}

.badge {
    font-size: 0.75em;
    font-weight: 500;
}

.card-apple {
    transition: all 0.2s ease;
    border: 1px solid #e3e6f0;
}

.card-apple:hover {
    transform: translateY(-1px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08);
}

/* Simple row styling */

#nodalTicketsTable {
    font-size: 0.875rem;
}

#nodalTicketsTable th {
    background-color: #f8f9fc;
    border-bottom: 2px solid #e3e6f0;
    color: #5a5c69;
    font-weight: 600;
    padding: 1rem 0.75rem;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

#nodalTicketsTable td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f1f1;
}

#nodalTicketsTable tbody tr {
    transition: all 0.15s ease;
}

#nodalTicketsTable tbody tr:hover {
    background-color: #f8f9fc;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

/* Priority badges */
.badge.bg-danger { background-color: #e74a3b !important; animation: pulse-danger 2s infinite; }
.badge.bg-warning { background-color: #f39c12 !important; color: #212529 !important; }
.badge.bg-info { background-color: #3498db !important; }
.badge.bg-secondary { background-color: #95a5a6 !important; }
.badge.bg-success { background-color: #27ae60 !important; }
.badge.bg-primary { background-color: #3498db !important; }
.badge.bg-dark { background-color: #2c3e50 !important; }

@keyframes pulse-danger {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

@media (max-width: 1200px) {
    #nodalTicketsTable {
        font-size: 0.8rem;
    }

    #nodalTicketsTable th,
    #nodalTicketsTable td {
        padding: 0.75rem 0.5rem;
    }
}

/* Access badge specific colors */
.badge.bg-success { background-color: #28a745 !important; } /* Full Access */
.badge.bg-warning { background-color: #ffc107 !important; color: #212529 !important; } /* Limited */
.badge.bg-secondary { background-color: #6c757d !important; } /* View Only */
.badge.bg-danger { background-color: #dc3545 !important; } /* Restricted */
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>