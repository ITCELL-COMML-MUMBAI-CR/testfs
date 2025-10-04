<?php
/**
 * Controller Tickets Management View (RBAC Restricted) - SAMPARK
 * Shows only tickets assigned to controller's department
 */

// Capture the content
ob_start();

// Set additional CSS for this view
$additional_css = [
    Config::getAppUrl() . '/assets/css/controller-views.css'
];

// Set page title
$page_title = 'My Department Tickets - SAMPARK';
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
                    <h1 class="h3 mb-1 fw-semibold">My Department - Closed Tickets</h1>
                    <p class="text-muted mb-0">Closed tickets from your department (<?= htmlspecialchars($user['department'] ?? 'N/A') ?>)</p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
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
    <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <div>
            <strong>Closed Tickets:</strong> This page shows only closed/resolved tickets from your department.
            Use "Search All Tickets" to search across all tickets in the system.
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
                        <div class="bg-danger bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fas fa-clock text-danger fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Overdue</div>
                            <div class="h4 mb-0 fw-semibold" id="overdueCount" data-stat="overdue">
                                <?= $tickets['overdue'] ?? 0 ?>
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
                        <div class="bg-success bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fas fa-check-circle text-success fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Resolved Today</div>
                            <div class="h4 mb-0 fw-semibold" id="resolvedTodayCount" data-stat="resolved_today">
                                <?= $tickets['resolved_today'] ?? 0 ?>
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
                        <!-- Note: Status is fixed to 'closed' for controllers -->

                        <!-- Priority Filter -->
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label-apple">Priority</label>
                            <select class="form-control-apple ticket-filter" name="priority" id="priorityFilter">
                                <option value="">All Priorities</option>
                                <option value="critical" <?= ($filters['priority'] ?? '') === 'critical' ? 'selected' : '' ?>>Critical</option>
                                <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                                <option value="medium" <?= ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="normal" <?= ($filters['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
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
                <i class="fas fa-list me-2"></i>Department Tickets
            </h5>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive" style="margin: -0.5rem;">
                <table class="table table-hover mb-0" id="controllerTicketsTable" style="margin: 0.75rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0" style="width: 120px;">Ticket ID</th>
                            <th class="border-0" style="width: 100px;">Priority</th>
                            <th class="border-0" style="width: 240px;">Customer</th>
                            <th class="border-0" style="width: 220px;">Category</th>
                            <th class="border-0" style="width: 140px;">Status</th>
                            <th class="border-0" style="width: 120px;">Created</th>
                            <th class="border-0" style="width: 300px;">Description</th>
                            <th class="border-0" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets['data'])): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-center">
                                    <i class="fas fa-inbox text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mb-2">No tickets found</h5>
                                    <p class="text-muted">No tickets are currently assigned to your department. New tickets will appear here when they're assigned.</p>
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
                                        <div class="fw-semibold text-truncate" style="max-width: 220px;"
                                             title="<?= htmlspecialchars($ticket['customer_name'] ?? 'N/A') ?>">
                                            <?= htmlspecialchars($ticket['customer_name'] ?? 'N/A') ?>
                                        </div>
                                        <?php if ($ticket['company_name']): ?>
                                        <small class="text-muted text-truncate d-block"
                                               style="max-width: 220px;"
                                               title="<?= htmlspecialchars($ticket['company_name']) ?>">
                                            <?= htmlspecialchars($ticket['company_name']) ?>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-semibold text-truncate d-block"
                                              style="max-width: 200px;"
                                              title="<?= htmlspecialchars($ticket['category'] ?? 'N/A') ?>">
                                            <?= htmlspecialchars($ticket['category'] ?? 'N/A') ?>
                                        </span>
                                        <?php if ($ticket['type']): ?>
                                        <small class="text-primary text-truncate d-block"
                                               style="max-width: 200px;"
                                               title="<?= htmlspecialchars($ticket['type']) ?>">
                                            <?= htmlspecialchars($ticket['type']) ?>
                                        </small>
                                        <?php endif; ?>
                                        <?php if ($ticket['subtype']): ?>
                                        <small class="text-muted text-truncate d-block"
                                               style="max-width: 200px;"
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
                                    <div class="text-nowrap">
                                        <div class="fw-semibold"><?= date('M d', strtotime($ticket['created_at'])) ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($ticket['created_at'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 280px;">
                                        <span class="text-muted" title="<?= htmlspecialchars($ticket['description'] ?? 'N/A') ?>">
                                            <?= htmlspecialchars(strlen($ticket['description'] ?? '') > 70 ?
                                                 substr($ticket['description'], 0, 70) . '...' :
                                                 ($ticket['description'] ?? 'N/A')) ?>
                                        </span>
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
                                                onclick="forwardTicket(<?= $ticket['complaint_id'] ?>)" title="Forward">
                                            <i class="fas fa-share"></i>
                                        </button>
                                        <?php endif; ?>

                                        <?php
                                        // Show internal remarks button for pending tickets in controller's department
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
                        <label class="form-label-apple">Forward To Department *</label>
                        <select class="form-control-apple" name="department" id="forwardDepartmentController" required>
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
// Include the same JavaScript as the original tickets page, but modified for RBAC
// Page initialization
document.addEventListener('DOMContentLoaded', function() {
    initializeBasicDataTable();
    updateStats();
    setupEventListeners();

    console.log('Controller RBAC tickets page initialized');
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
            const $table = $('#controllerTicketsTable');

            if ($table.length === 0) {
                console.warn('Table not found');
                return;
            }

            if ($.fn.DataTable.isDataTable('#controllerTicketsTable')) {
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
                        infoEmpty: "No tickets assigned to your department",
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
                console.log('DataTable initialized successfully');
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

    loadDepartments().then(() => {
        new bootstrap.Modal(document.getElementById('forwardModal')).show();
    });
}

async function loadDepartments() {
    try {
        const response = await fetch(`${APP_URL}/api/departments`);
        const data = await response.json();

        if (data.success) {
            const controllerDeptSelect = document.getElementById('forwardDepartmentController');
            if (controllerDeptSelect) {
                // For regular controllers, show only Commercial departments (as per business rules)
                const commercialDepts = data.departments.filter(dept => {
                    return dept.department_code === 'COMM' ||
                           dept.department_code === 'CML' ||
                           dept.department_code === 'Commercial' ||
                           dept.department_name.toLowerCase().includes('commercial');
                });

                controllerDeptSelect.innerHTML = '<option value="">Select Department...</option>';

                if (commercialDepts.length === 0) {
                    // Fallback: if no commercial dept found, show a manual option
                    controllerDeptSelect.innerHTML += '<option value="COMM">Commercial</option>';
                } else {
                    commercialDepts.forEach(dept => {
                        const option = document.createElement('option');
                        option.value = dept.department_code;
                        option.textContent = dept.department_name;
                        controllerDeptSelect.appendChild(option);
                    });
                }
            }
        }
    } catch (error) {
        console.error('Error loading departments:', error);
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
                document.getElementById('resolvedTodayCount').textContent = stats.resolved_today || 0;
                document.getElementById('overdueCount').textContent = stats.overdue || 0;
            }
        })
        .catch(error => console.error('Error loading stats:', error));
}
</script>

<!-- Include the same styles from the original tickets page -->
<style>
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

#controllerTicketsTable {
    font-size: 0.875rem;
}

#controllerTicketsTable th {
    background-color: #f8f9fc;
    border-bottom: 2px solid #e3e6f0;
    color: #5a5c69;
    font-weight: 600;
    padding: 1rem 0.75rem;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

#controllerTicketsTable td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f1f1;
}

#controllerTicketsTable tbody tr {
    transition: all 0.15s ease;
}

#controllerTicketsTable tbody tr:hover {
    background-color: #f8f9fc;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

.badge.bg-danger { background-color: #e74a3b !important; animation: pulse-danger 2s infinite; }
.badge.bg-warning { background-color: #f39c12 !important; }
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
    #controllerTicketsTable {
        font-size: 0.8rem;
    }

    #controllerTicketsTable th,
    #controllerTicketsTable td {
        padding: 0.75rem 0.5rem;
    }
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>