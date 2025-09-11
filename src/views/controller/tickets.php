<?php
/**
 * Controller Tickets Management View - SAMPARK
 * Enhanced interface for managing support tickets with advanced filtering and improved UX
 */

// Capture the content
ob_start();

// Set additional CSS for this view
$additional_css = [
    Config::getAppUrl() . '/assets/css/controller-views.css'
];

// Set page title
$page_title = 'Support Hub - SAMPARK';
?>

<div class="container-xl py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <div class="d-flex align-items-center">
                <div class="bg-apple-blue rounded-3 p-3 me-3">
                    <i class="fas fa-ticket-alt text-dark fa-lg"></i>
                </div>
                <div>
                    <h1 class="h3 mb-1 fw-semibold">Support Hub</h1>
                    <p class="text-muted mb-0">Manage and resolve customer support tickets</p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <button class="btn btn-apple-secondary" onclick="exportTickets()">
                    <i class="fas fa-download me-2"></i>Export
                </button>
                <button class="btn btn-apple-primary" onclick="forceRefresh()">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                </button>
            </div>
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
                            <div class="h4 mb-0 fw-semibold" id="pendingCount">
                                <?= $tickets['pagination']['total_results'] ?? 0 ?>
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
                            <div class="h4 mb-0 fw-semibold" id="highPriorityCount">0</div>
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
                            <div class="text-muted small">SLA Violations</div>
                            <div class="h4 mb-0 fw-semibold" id="slaViolationCount">0</div>
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
                            <div class="h4 mb-0 fw-semibold" id="resolvedTodayCount">0</div>
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
            <button class="btn btn-sm btn-apple-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        <div class="collapse show" id="filtersCollapse">
            <div class="card-body">
                <form id="filtersForm" onsubmit="applyFilters(event)">
                    <div class="row g-3">
                        <!-- Status Filter -->
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label-apple">Status</label>
                            <select class="form-control-apple" name="status" id="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="awaiting_info" <?= ($filters['status'] ?? '') === 'awaiting_info' ? 'selected' : '' ?>>Awaiting Info</option>
                                <option value="awaiting_approval" <?= ($filters['status'] ?? '') === 'awaiting_approval' ? 'selected' : '' ?>>Awaiting Approval</option>
                                <option value="awaiting_feedback" <?= ($filters['status'] ?? '') === 'awaiting_feedback' ? 'selected' : '' ?>>Awaiting Feedback</option>
                                <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                            </select>
                        </div>

                        <!-- Priority Filter -->
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label-apple">Priority</label>
                            <select class="form-control-apple" name="priority" id="priorityFilter">
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
                            <input type="date" class="form-control-apple" name="date_from" id="dateFromFilter" 
                                   value="<?= $filters['date_from'] ?? '' ?>">
                        </div>

                        <!-- Date To -->
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label-apple">Date To</label>
                            <input type="date" class="form-control-apple" name="date_to" id="dateToFilter" 
                                   value="<?= $filters['date_to'] ?? '' ?>">
                        </div>

                        <!-- Division Filter (only for nodal controllers) -->
                        <?php if ($user['role'] === 'controller_nodal'): ?>
                        <div class="col-md-6 col-lg-2">
                            <label class="form-label-apple">Division</label>
                            <select class="form-control-apple" name="division" id="divisionFilter">
                                <option value="">All Divisions</option>
                                <?php foreach ($divisions ?? [] as $division): ?>
                                <option value="<?= htmlspecialchars($division['division']) ?>" 
                                        <?= ($filters['division'] ?? '') === $division['division'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($division['division']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="row mt-3">
                        <div class="col">
                            <!-- Search Bar -->
                            <div class="input-group input-group-apple">
                                <input type="text" class="form-control" placeholder="Search by ticket ID, customer name, or description..." 
                                       name="search" id="searchInput" value="<?= $_GET['search'] ?? '' ?>">
                                <button class="btn btn-apple-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-apple-secondary" onclick="clearFilters()">
                                <i class="fas fa-times me-2"></i>Clear
                            </button>
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
                <i class="fas fa-list me-2"></i>Support Tickets
                <span class="badge bg-apple-blue ms-2" id="ticketCount">
                    <?= $tickets['pagination']['total_results'] ?? 0 ?>
                </span>
            </h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-sm btn-apple-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-sort me-2"></i>Sort By
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="sortTickets('priority')">Priority</a></li>
                        <li><a class="dropdown-item" href="#" onclick="sortTickets('created_at')">Date Created</a></li>
                        <li><a class="dropdown-item" href="#" onclick="sortTickets('updated_at')">Last Updated</a></li>
                        <li><a class="dropdown-item" href="#" onclick="sortTickets('sla_deadline')">SLA Deadline</a></li>
                    </ul>
                </div>
                <button class="btn btn-sm btn-apple-secondary" onclick="toggleView()" id="viewToggle">
                    <i class="fas fa-th-large me-2"></i>Card View
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Support Tickets</h5>
                    <div class="d-flex align-items-center">
                        <small class="text-muted last-refresh-time me-3">Last updated: --</small>
                        <span class="badge bg-primary" id="autoRefreshStatus">Auto-refresh: ON</span>
                    </div>
                </div>
                
                <table class="table table-hover mb-0" id="controllerTicketsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">
                                <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th class="border-0">Ticket ID</th>
                            <th class="border-0">Customer</th>
                            <th class="border-0">Category</th>
                            <th class="border-0">Status</th>
                            <th class="border-0">Assigned To</th>
                            <th class="border-0">Date</th>
                            <th class="border-0">Time</th>
                            <th class="border-0">Description</th>
                            <th class="border-0">SLA</th>
                            <th class="border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets['data'])): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <h5>No complaints found</h5>
                                    <p>No complaints match your current filters.</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($tickets['data'] as $ticket): ?>
                            <tr class="ticket-row" data-ticket-id="<?= $ticket['complaint_id'] ?>">
                                <td>
                                    <input type="checkbox" class="form-check-input ticket-checkbox" 
                                           value="<?= $ticket['complaint_id'] ?>">
                                </td>
                                <td>
                                    <a href="<?= Config::getAppUrl() ?>/controller/tickets/<?= $ticket['complaint_id'] ?>" 
                                       class="fw-semibold text-decoration-none">
                                        #<?= $ticket['complaint_id'] ?>
                                    </a>
                                </td>
                                <td>
                                    <?php
                                    $priorityClass = [
                                        'critical' => 'danger',
                                        'high' => 'warning', 
                                        'medium' => 'info',
                                        'normal' => 'secondary'
                                    ][$ticket['priority']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $priorityClass ?>">
                                        <i class="fas fa-<?= $ticket['priority'] === 'critical' ? 'exclamation-circle' : 
                                                          ($ticket['priority'] === 'high' ? 'exclamation-triangle' : 
                                                           ($ticket['priority'] === 'medium' ? 'info-circle' : 'circle')) ?>"></i>
                                        <?= ucfirst($ticket['priority']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($ticket['customer_name'] ?? 'N/A') ?></div>
                                        <?php if ($ticket['company_name']): ?>
                                        <small class="text-muted"><?= htmlspecialchars($ticket['company_name']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-semibold"><?= htmlspecialchars($ticket['category'] ?? 'N/A') ?></div>
                                        <?php if ($ticket['subtype']): ?>
                                        <small class="text-muted"><?= htmlspecialchars($ticket['subtype']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'pending' => 'warning',
                                        'awaiting_info' => 'info', 
                                        'awaiting_approval' => 'primary',
                                        'awaiting_feedback' => 'success',
                                        'closed' => 'dark'
                                    ][$ticket['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= ucwords(str_replace('_', ' ', $ticket['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($ticket['assigned_user_name']): ?>
                                        <div class="fw-semibold"><?= htmlspecialchars($ticket['assigned_user_name']) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <div><?= date('M d, Y', strtotime($ticket['created_at'])) ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($ticket['created_at'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($ticket['is_sla_violated']): ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-clock me-1"></i>Overdue
                                        </span>
                                        <br>
                                        <small class="text-danger"><?= $ticket['hours_elapsed'] ?>h</small>
                                    <?php else: ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>On Time
                                        </span>
                                        <br>
                                        <small class="text-muted"><?= $ticket['hours_elapsed'] ?>h</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?= Config::getAppUrl() ?>/controller/tickets/<?= $ticket['complaint_id'] ?>" 
                                           class="btn btn-sm btn-apple-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (in_array($ticket['status'], ['pending', 'awaiting_info'])): ?>
                                        <button class="btn btn-sm btn-apple-secondary" 
                                                onclick="quickReply(<?= $ticket['complaint_id'] ?>)" title="Quick Reply">
                                            <i class="fas fa-reply"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($user['role'] === 'controller_nodal' && in_array($ticket['status'], ['pending', 'awaiting_info'])): ?>
                                        <button class="btn btn-sm btn-apple-warning" 
                                                onclick="forwardTicket(<?= $ticket['complaint_id'] ?>)" title="Forward">
                                            <i class="fas fa-share"></i>
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
        <?php if (!empty($tickets['data']) && $tickets['pagination']['total_pages'] > 1): ?>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing <?= $tickets['pagination']['start'] ?? 1 ?> to 
                    <?= $tickets['pagination']['end'] ?? count($tickets['data']) ?> of 
                    <?= $tickets['pagination']['total_results'] ?? count($tickets['data']) ?> entries
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($tickets['pagination']['current_page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $tickets['pagination']['current_page'] - 1 ?><?= http_build_query(array_filter($_GET, fn($k) => $k !== 'page'), '&', '&') ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $tickets['pagination']['current_page'] - 2); 
                                   $i <= min($tickets['pagination']['total_pages'], $tickets['pagination']['current_page'] + 2); 
                                   $i++): ?>
                        <li class="page-item <?= $i === $tickets['pagination']['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?><?= http_build_query(array_filter($_GET, fn($k) => $k !== 'page'), '&', '&') ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($tickets['pagination']['current_page'] < $tickets['pagination']['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $tickets['pagination']['current_page'] + 1 ?><?= http_build_query(array_filter($_GET, fn($k) => $k !== 'page'), '&', '&') ?>">
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

    <!-- Bulk Actions Bar (hidden by default) -->
    <div class="fixed-bottom bg-white border-top shadow-lg p-3 d-none" id="bulkActionsBar">
        <div class="container-xl">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span id="selectedCount">0</span> tickets selected
                </div>
                <div class="btn-group">
                    <?php if ($user['role'] === 'controller_nodal'): ?>
                    <button class="btn btn-apple-primary" onclick="bulkForward()">
                        <i class="fas fa-share me-2"></i>Forward Selected
                    </button>
                    <?php endif; ?>
                    <button class="btn btn-apple-secondary" onclick="bulkExport()">
                        <i class="fas fa-download me-2"></i>Export Selected
                    </button>
                    <button class="btn btn-apple-secondary" onclick="clearSelection()">
                        <i class="fas fa-times me-2"></i>Clear Selection
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

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
                        <textarea class="form-control-apple" name="reply" rows="5" required 
                                  placeholder="Enter your reply to the customer..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Action Taken</label>
                        <textarea class="form-control-apple" name="action_taken" rows="3" required 
                                  placeholder="Describe the action taken to resolve this issue..."></textarea>
                    </div>
                    <?php if ($user['role'] === 'controller'): ?>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="needs_approval" id="needsApproval">
                            <label class="form-check-label" for="needsApproval">
                                This reply requires nodal controller approval
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-apple-primary">
                        <i class="fas fa-paper-plane me-2"></i>Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Forward Ticket Modal -->
<?php if ($user['role'] === 'controller_nodal'): ?>
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
                        <label class="form-label-apple">Assign To User</label>
                        <select class="form-control-apple" name="to_user_id" required>
                            <option value="">Select User...</option>
                            <!-- Users will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Priority</label>
                        <select class="form-control-apple" name="priority" required>
                            <option value="normal">Normal</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Remarks</label>
                        <textarea class="form-control-apple" name="remarks" rows="4" required 
                                  placeholder="Add forwarding remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-apple-primary">
                        <i class="fas fa-share me-2"></i>Forward Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Tickets management JavaScript
let selectedTickets = new Set();
let currentView = 'table';

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Initialize controller tickets table with auto-refresh
    if (typeof initializeControllerTicketsTable === 'function') {
        const controllerTable = initializeControllerTicketsTable('controllerTicketsTable');
        console.log('Controller tickets table initialized with background refresh');
    } else {
        console.warn('DataTable configuration not loaded - using fallback');
        initializeBasicTable();
    }
    
    updateStats();
    setupEventListeners();
});

function setupEventListeners() {
    // Update filter behavior for DataTables
    document.querySelectorAll('#filtersForm select, #filtersForm input[type="date"]').forEach(element => {
        element.addEventListener('change', function() {
            if (window.backgroundRefreshManager) {
                // Force immediate refresh when user changes filters
                window.backgroundRefreshManager.forceRefresh();
            } else {
                applyFilters();
            }
        });
    });
    
    // Search with debounce for DataTables
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (window.backgroundRefreshManager) {
                    window.backgroundRefreshManager.forceRefresh();
                } else {
                    applyFilters();
                }
            }, 500);
        });
    }
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
    
    // Preserve current page if no search term
    if (!formData.get('search')) {
        const urlParams = new URLSearchParams(window.location.search);
        const currentPage = urlParams.get('page');
        if (currentPage) {
            params.append('page', currentPage);
        }
    }
    
    window.location.href = window.location.pathname + '?' + params.toString();
}

function clearFilters() {
    document.getElementById('filtersForm').reset();
    window.location.href = window.location.pathname;
}

function forceRefresh() {
    if (window.backgroundRefreshManager) {
        window.backgroundRefreshManager.forceRefresh();
        
        // Show brief confirmation
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
        button.disabled = true;
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 2000);
    } else {
        showLoading();
        window.location.reload();
    }
}

function initializeBasicTable() {
    // Fallback initialization if DataTables config is not available
    console.log('Using basic table without auto-refresh');
}

// Legacy function for backward compatibility
function refreshTickets() {
    forceRefresh();
}

function exportTickets() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', '1');
    window.location.href = window.location.pathname + '?' + params.toString();
}

function sortTickets(column) {
    const params = new URLSearchParams(window.location.search);
    params.set('sort', column);
    
    // Toggle sort direction
    const currentSort = params.get('sort_dir');
    params.set('sort_dir', currentSort === 'desc' ? 'asc' : 'desc');
    
    window.location.href = window.location.pathname + '?' + params.toString();
}

function toggleView() {
    const button = document.getElementById('viewToggle');
    const table = document.getElementById('ticketsTable');
    
    if (currentView === 'table') {
        // Switch to card view (implement card view HTML)
        currentView = 'card';
        button.innerHTML = '<i class="fas fa-list me-2"></i>Table View';
        // Add card view implementation
    } else {
        // Switch to table view
        currentView = 'table';
        button.innerHTML = '<i class="fas fa-th-large me-2"></i>Card View';
        table.style.display = 'table';
    }
}

// Selection functions
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.ticket-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        if (selectAll.checked) {
            selectedTickets.add(parseInt(checkbox.value));
        } else {
            selectedTickets.delete(parseInt(checkbox.value));
        }
    });
    
    updateBulkActionsBar();
}

function updateSelection(checkbox) {
    if (checkbox.checked) {
        selectedTickets.add(parseInt(checkbox.value));
    } else {
        selectedTickets.delete(parseInt(checkbox.value));
    }
    
    // Update select all checkbox
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.ticket-checkbox');
    selectAll.checked = Array.from(checkboxes).every(cb => cb.checked);
    selectAll.indeterminate = selectedTickets.size > 0 && selectedTickets.size < checkboxes.length;
    
    updateBulkActionsBar();
}

function updateBulkActionsBar() {
    const bulkBar = document.getElementById('bulkActionsBar');
    const countSpan = document.getElementById('selectedCount');
    
    if (selectedTickets.size > 0) {
        bulkBar.classList.remove('d-none');
        countSpan.textContent = selectedTickets.size;
    } else {
        bulkBar.classList.add('d-none');
    }
}

function clearSelection() {
    selectedTickets.clear();
    document.querySelectorAll('.ticket-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActionsBar();
}

// Quick actions
function quickReply(ticketId) {
    document.getElementById('replyTicketId').textContent = ticketId;
    document.getElementById('quickReplyForm').dataset.ticketId = ticketId;
    new bootstrap.Modal(document.getElementById('quickReplyModal')).show();
}

function forwardTicket(ticketId) {
    document.getElementById('forwardTicketId').textContent = ticketId;
    document.getElementById('forwardForm').dataset.ticketId = ticketId;
    
    // Load available users for this division
    loadAvailableUsers().then(() => {
        new bootstrap.Modal(document.getElementById('forwardModal')).show();
    });
}

async function loadAvailableUsers() {
    try {
        const response = await fetch(`${APP_URL}/api/users/available`);
        const users = await response.json();
        
        const select = document.querySelector('#forwardModal select[name="to_user_id"]');
        select.innerHTML = '<option value="">Select User...</option>';
        
        users.forEach(user => {
            const option = document.createElement('option');
            option.value = user.id;
            option.textContent = `${user.name} (${user.role})`;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading users:', error);
    }
}

// Form submissions
document.getElementById('quickReplyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const ticketId = this.dataset.ticketId;
    const formData = new FormData(this);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/reply`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire('Success', result.message, 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Failed to send reply', 'error');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('quickReplyModal')).hide();
});

<?php if ($user['role'] === 'controller_nodal'): ?>
document.getElementById('forwardForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const ticketId = this.dataset.ticketId;
    const formData = new FormData(this);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/forward`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            Swal.fire('Success', result.message, 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    } catch (error) {
        Swal.fire('Error', 'Failed to forward ticket', 'error');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('forwardModal')).hide();
});
<?php endif; ?>

function updateStats() {
    // Update dashboard stats (this would typically come from an API)
    fetch(`${APP_URL}/api/tickets/stats`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('pendingCount').textContent = data.pending || 0;
            document.getElementById('highPriorityCount').textContent = data.high_priority || 0;
            document.getElementById('slaViolationCount').textContent = data.sla_violations || 0;
            document.getElementById('resolvedTodayCount').textContent = data.resolved_today || 0;
        })
        .catch(error => console.error('Error loading stats:', error));
}

// Bulk actions
function bulkForward() {
    if (selectedTickets.size === 0) return;
    
    // Implement bulk forward functionality
    Swal.fire({
        title: 'Forward Selected Tickets',
        text: `Forward ${selectedTickets.size} selected tickets?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Forward'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement bulk forward API call
        }
    });
}

function bulkExport() {
    if (selectedTickets.size === 0) return;
    
    const params = new URLSearchParams();
    params.append('export', '1');
    params.append('tickets', Array.from(selectedTickets).join(','));
    
    window.location.href = `${APP_URL}/controller/tickets/export?` + params.toString();
}

// Add event listeners to checkboxes
document.querySelectorAll('.ticket-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        updateSelection(this);
    });
});
</script>

<style>
/* Custom styles for tickets page */
.ticket-row:hover {
    background-color: var(--bs-light);
}

.badge {
    font-size: 0.75em;
}

.card-apple {
    transition: all 0.2s ease;
}

.card-apple:hover {
    transform: translateY(-2px);
    box-shadow: var(--apple-shadow-medium);
}

.btn-group .btn {
    border: none !important;
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

#bulkActionsBar {
    z-index: 1050;
}

.form-check-input:checked[type=checkbox] {
    background-color: var(--apple-primary);
    border-color: var(--apple-primary);
}

/* Loading state for table rows */
.ticket-row.loading {
    opacity: 0.5;
    pointer-events: none;
}

/* Priority indicators */
.badge.bg-danger {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    
    .card-body {
        padding: 1rem 0.5rem;
    }
    
    #bulkActionsBar {
        padding: 1rem;
    }
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>