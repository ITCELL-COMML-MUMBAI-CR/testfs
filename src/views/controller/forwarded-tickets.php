<?php
/**
 * Controller Forwarded Tickets View - SAMPARK
 * Shows complaints forwarded to departments within the division for Controller_Nodal
 */

// Capture the content
ob_start();

// Set additional CSS for this view
$additional_css = [
    Config::getAppUrl() . '/assets/css/controller-views.css'
];

// Set page title
$page_title = 'Forwarded Tickets - SAMPARK';
?>

<div class="container-fluid px-4 py-4" style="max-width: 95%;">
    <!-- Page Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <div class="d-flex align-items-center">
                <div class="bg-apple-blue rounded-3 p-3 me-3">
                    <i class="fas fa-share-alt text-dark fa-lg"></i>
                </div>
                <div>
                    <h1 class="h3 mb-1 fw-semibold">Forwarded Tickets</h1>
                    <p class="text-muted mb-0">View complaints forwarded to departments within your division</p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <a href="<?= Config::getAppUrl() ?>/controller/tickets" class="action-btn action-btn-secondary action-btn-with-text">
                    <i class="fas fa-arrow-left"></i>Back to Tickets
                </a>
                <button class="action-btn action-btn-primary action-btn-with-text" onclick="forceRefresh()">
                    <i class="fas fa-sync-alt"></i>Refresh
                </button>
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
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= htmlspecialchars($dept['department_code']) ?>" 
                                        <?= ($filters['department'] ?? '') === $dept['department_code'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['department_name']) ?>
                                </option>
                                <?php endforeach; ?>
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

    <!-- Access Level Info -->
    <div class="alert alert-info d-flex align-items-center mb-3">
        <i class="fas fa-info-circle me-2"></i>
        <div class="small">
            <strong>Access Levels:</strong> 
            <i class="fas fa-edit text-success me-1"></i> <span class="text-success">Your Department</span> - Can take actions • 
            <i class="fas fa-eye text-secondary me-1"></i> <span class="text-muted">Other Departments</span> - View only • 
            <i class="fas fa-clock text-warning me-1"></i> <span class="text-warning">Awaiting Customer Info</span> - No actions allowed
        </div>
    </div>

    <!-- Forwarded Tickets Table -->
    <div class="card card-apple">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-share-alt me-2"></i>Forwarded Tickets
                <span class="badge bg-apple-blue ms-2" id="ticketCount">
                    <?= $tickets['total'] ?? 0 ?>
                </span>
            </h5>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive" style="margin: -0.5rem;">
                <table class="table table-hover mb-0" id="forwardedTicketsTable" style="margin: 0.75rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0" style="width: 120px;">Ticket ID</th>
                            <th class="border-0" style="width: 100px;">Priority</th>
                            <th class="border-0" style="width: 240px;">Customer</th>
                            <th class="border-0" style="width: 220px;">Category</th>
                            <th class="border-0" style="width: 140px;">Status</th>
                            <th class="border-0" style="width: 160px;">Forwarded To</th>
                            <th class="border-0" style="width: 120px;">Created</th>
                            <th class="border-0" style="width: 300px;">Description</th>
                            <th class="border-0" style="width: 100px;">SLA</th>
                            <th class="border-0" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets['data'])): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-share-alt fa-3x mb-3"></i>
                                    <h5>No forwarded tickets found</h5>
                                    <p>No tickets have been forwarded to departments within your division.</p>
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
                                    <div class="text-truncate">
                                        <?php 
                                        // Check if this ticket is assigned to the current user's department
                                        $isOwnDepartment = ($ticket['assigned_to_department'] === $user['department']);
                                        $departmentClass = $isOwnDepartment ? 'text-success fw-semibold' : 'text-muted';
                                        $accessIcon = $isOwnDepartment ? 'fas fa-edit text-success' : 'fas fa-eye text-secondary';
                                        $accessTitle = $isOwnDepartment ? 'Can take actions' : 'View only';
                                        ?>
                                        <div class="d-flex align-items-center">
                                            <i class="<?= $accessIcon ?> me-2" title="<?= $accessTitle ?>"></i>
                                            <span class="<?= $departmentClass ?> text-truncate d-block" 
                                                  style="max-width: 120px;" 
                                                  title="<?= htmlspecialchars($ticket['assigned_department_name'] ?? $ticket['assigned_to_department'] ?? 'N/A') ?> - <?= $accessTitle ?>">
                                                <?= htmlspecialchars($ticket['assigned_department_name'] ?? $ticket['assigned_to_department'] ?? 'N/A') ?>
                                            </span>
                                        </div>
                                        <small class="text-muted text-truncate d-block" 
                                               style="max-width: 140px;" 
                                               title="<?= htmlspecialchars($ticket['assigned_to_department'] ?? 'N/A') ?> - <?= htmlspecialchars($ticket['division'] ?? 'N/A') ?>">
                                            <?= htmlspecialchars($ticket['assigned_to_department'] ?? 'N/A') ?> - <?= htmlspecialchars($ticket['division'] ?? 'N/A') ?>
                                        </small>
                                    </div>
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
                                <td class="text-center">
                                    <?php if ($ticket['priority'] === 'critical'): ?>
                                        <div>
                                            <span class="badge bg-danger px-2 py-1">
                                                <i class="fas fa-clock me-1"></i>Overdue
                                            </span>
                                            <div>
                                                <small class="text-danger fw-semibold"><?= $ticket['hours_elapsed'] ?>h</small>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div>
                                            <span class="badge bg-success px-2 py-1">
                                                <i class="fas fa-check me-1"></i>On Time
                                            </span>
                                            <div>
                                                <small class="text-muted"><?= $ticket['hours_elapsed'] ?>h</small>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons-group" role="group">
                                        <?php
                                        // Check if controller_nodal can take actions on this ticket
                                        $isOwnDepartment = ($ticket['assigned_to_department'] === $user['department']);
                                        $isAwaitingInfo = ($ticket['status'] === 'awaiting_info');
                                        $canTakeActions = $isOwnDepartment && !$isAwaitingInfo;
                                        
                                        $buttonClass = $canTakeActions ? 'action-btn-primary' : 'action-btn-secondary';
                                        $buttonTitle = $canTakeActions ? 'View Details - Can take actions' : 
                                                      ($isAwaitingInfo ? 'View Only - Awaiting customer info' : 'View Only - Different department');
                                        $icon = $canTakeActions ? 'fa-eye' : 'fa-eye';
                                        ?>
                                        <a href="<?= Config::getAppUrl() ?>/controller/tickets/<?= $ticket['complaint_id'] ?>" 
                                           class="action-btn <?= $buttonClass ?> action-btn-compact" title="<?= $buttonTitle ?>">
                                            <i class="fas <?= $icon ?>"></i>
                                            <?php if ($isAwaitingInfo && $isOwnDepartment): ?>
                                            <i class="fas fa-clock text-warning ms-1" style="font-size: 0.7em;"></i>
                                            <?php endif; ?>
                                        </a>
                                        
                                        <?php 
                                        // Show internal remarks button based on role and ticket assignment
                                        $canAddInternalRemarks = false;
                                        if ($user['role'] === 'controller_nodal' && $ticket['status'] === 'pending' && $ticket['division'] === $user['division']) {
                                            $canAddInternalRemarks = true;
                                        } elseif ($user['role'] === 'controller' && $ticket['status'] === 'pending' && $ticket['assigned_to_department'] === $user['department']) {
                                            $canAddInternalRemarks = true;
                                        }
                                        
                                        if ($canAddInternalRemarks):
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
        <?php if (!empty($tickets['data']) && $tickets['total_pages'] > 1): ?>
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

<script>
// Forwarded tickets management JavaScript  
let currentView = 'table';

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable for forwarded tickets table
    initializeForwardedTicketsDataTable();
    
    // Setup event listeners
    setupEventListeners();
    
    console.log('Forwarded tickets page initialized');
});

function setupEventListeners() {
    // Handle filter changes
    document.querySelectorAll('.ticket-filter').forEach(element => {
        element.addEventListener('change', function() {
            // Apply filters immediately when changed
            applyFilters();
        });
    });
}

function applyFilters(event) {
    if (event) {
        event.preventDefault();
    }
    
    // Update URL with current filters
    const form = document.getElementById('filtersForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    for (let [key, value] of formData.entries()) {
        if (value.trim() !== '') {
            params.append(key, value);
        }
    }
    
    // Update URL without page reload
    const newUrl = window.location.pathname + '?' + params.toString();
    window.history.pushState({}, '', newUrl);
    
    // Refresh the page to apply filters
    window.location.reload();
}

function clearFilters() {
    document.getElementById('filtersForm').reset();
    window.location.href = window.location.pathname;
}

function forceRefresh() {
    // Show brief confirmation
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    button.disabled = true;
    
    // Reload the page to get fresh data
    setTimeout(() => {
        window.location.reload();
    }, 1000);
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
                console.error('Error adding internal remarks:', error);
                Swal.fire('Error', 'An error occurred while adding internal remarks. Please try again.', 'error');
            }
        }
    });
}

function initializeForwardedTicketsDataTable() {
    // Simple DataTable initialization with built-in search
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        try {
            const $table = $('#forwardedTicketsTable');
            
            // Check if table exists and has content
            if ($table.length === 0) {
                console.warn('Table #forwardedTicketsTable not found');
                return;
            }
            
            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('#forwardedTicketsTable')) {
                $table.DataTable().destroy();
                $table.empty(); // Clear any leftover DataTables elements
            }
            
            // Verify table structure before initializing
            const headerCols = $table.find('thead tr th').length;
            const firstRowCols = $table.find('tbody tr:first td').length;
            const hasEmptyMessage = $table.find('tbody tr:first td[colspan]').length > 0;
            const dataRowCount = $table.find('tbody tr').not(':has(td[colspan])').length;
            
            console.log(`Forwarded table structure: ${headerCols} headers, ${firstRowCols} cells in first row, ${dataRowCount} data rows, hasEmptyMessage: ${hasEmptyMessage}`);
            
            // Only initialize DataTables if we have actual data rows (not just empty state)
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
                        search: "Search forwarded tickets:",
                        lengthMenu: "Show _MENU_ tickets",
                        info: "Showing _START_ to _END_ of _TOTAL_ forwarded tickets",
                        infoEmpty: "No forwarded tickets available",
                        infoFiltered: "(filtered from _MAX_ total tickets)",
                        emptyTable: "No forwarded tickets found matching your criteria",
                        paginate: {
                            first: "First",
                            last: "Last", 
                            next: "Next",
                            previous: "Previous"
                        }
                    }
                });
                console.log('DataTable initialized successfully for forwarded tickets');
            } else {
                console.log(`Skipping DataTables initialization for forwarded tickets - no data rows available (${dataRowCount} data rows, empty message: ${hasEmptyMessage})`);
            }
        } catch (error) {
            console.error('Error initializing forwarded tickets DataTable:', error);
            // Fallback to basic table functionality
            console.log('Using forwarded table without DataTables due to initialization error');
        }
    } else {
        console.warn('DataTable library not available');
    }
}

</script>

<style>
/* Custom styles for forwarded tickets page */
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

/* DataTable Improvements */
#forwardedTicketsTable {
    font-size: 0.875rem;
}

#forwardedTicketsTable th {
    background-color: #f8f9fc;
    border-bottom: 2px solid #e3e6f0;
    color: #5a5c69;
    font-weight: 600;
    padding: 1rem 0.75rem;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

#forwardedTicketsTable td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f1f1;
}

#forwardedTicketsTable tbody tr {
    transition: all 0.15s ease;
}

#forwardedTicketsTable tbody tr:hover {
    background-color: #f8f9fc;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

/* Priority badges */
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

/* Text truncation improvements */
.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Compact table styling */
.table-hover tbody tr:hover td {
    background-color: #f8f9fc;
}

/* Better spacing for badges and content */
.badge {
    line-height: 1.3;
    padding: 0.375rem 0.75rem;
}

/* Responsive improvements */
@media (max-width: 1200px) {
    #forwardedTicketsTable {
        font-size: 0.8rem;
    }
    
    #forwardedTicketsTable th,
    #forwardedTicketsTable td {
        padding: 0.75rem 0.5rem;
    }
}

.pagination .page-link {
    border: none;
    color: var(--apple-primary);
}

.pagination .page-item.active .page-link {
    background-color: var(--apple-primary);
    border-color: var(--apple-primary);
}

.table th {
    font-weight: 600;
    color: var(--apple-text-secondary);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
        padding: 0.5rem;
    }
    
    .card-body {
        padding: 1rem 0.5rem;
    }
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
