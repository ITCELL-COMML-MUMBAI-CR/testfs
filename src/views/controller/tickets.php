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

<div class="container-fluid px-4 py-4" style="max-width: 95%;">
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
                                <?= $tickets['total'] ?? 0 ?>
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
                        <!--  <div class="col">
                            Search Bar
                            <div class="input-group input-group-apple">
                                <input type="text" class="form-control" placeholder="Search by ticket ID, customer name, or description..." 
                                       name="search" id="searchInput" value="<?= $_GET['search'] ?? '' ?>">
                                <button class="btn btn-apple-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>-->
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
                    <?= $tickets['total'] ?? 0 ?>
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
                            <th class="border-0" style="width: 120px;">Ticket ID</th>
                            <th class="border-0" style="width: 100px;">Priority</th>
                            <th class="border-0" style="width: 240px;">Customer</th>
                            <th class="border-0" style="width: 220px;">Category</th>
                            <th class="border-0" style="width: 140px;">Status</th>
                            <th class="border-0" style="width: 160px;">Assigned To</th>
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
                                        <?php if (isset($ticket['assigned_user_name']) && $ticket['assigned_user_name']): ?>
                                            <div class="fw-semibold text-truncate" style="max-width: 140px;" 
                                                 title="<?= htmlspecialchars($ticket['assigned_user_name']) ?>">
                                                <?= htmlspecialchars($ticket['assigned_user_name']) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted text-truncate d-block" 
                                                  style="max-width: 140px;" 
                                                  title="<?= htmlspecialchars($ticket['assigned_to_department'] ?? 'N/A') ?>">
                                                <?= htmlspecialchars($ticket['assigned_to_department'] ?? 'N/A') ?>
                                            </span>
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
                                    <div class="text-truncate" style="max-width: 280px;">
                                        <span class="text-muted" title="<?= htmlspecialchars($ticket['description'] ?? 'N/A') ?>">
                                            <?= htmlspecialchars(strlen($ticket['description'] ?? '') > 70 ? 
                                                 substr($ticket['description'], 0, 70) . '...' : 
                                                 ($ticket['description'] ?? 'N/A')) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if ($ticket['is_sla_violated']): ?>
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
                                        <?php if (in_array($user['role'], ['controller_nodal', 'controller']) && in_array($ticket['status'], ['pending', 'awaiting_info'])): ?>
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
<?php if ($user['role'] === 'controller_nodal' || $user['role'] === 'controller'): ?>
<div class="modal fade" id="forwardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Forward Ticket #<span id="forwardTicketId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="forwardForm">
                <div class="modal-body">
                    <?php if ($user['role'] === 'controller_nodal'): ?>
                    <div class="mb-3">
                        <label class="form-label-apple">Forward To Zone</label>
                        <select class="form-control-apple" name="zone" id="forwardZone" required>
                            <option value="">Select Zone...</option>
                            <!-- Zones will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Forward To Division</label>
                        <select class="form-control-apple" name="division" id="forwardDivision" required>
                            <option value="">Select Division...</option>
                            <!-- Divisions will be loaded dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Forward To Department</label>
                        <select class="form-control-apple" name="department" id="forwardDepartment" required>
                            <option value="">Select Department...</option>
                            <!-- Departments will be loaded dynamically -->
                        </select>
                    </div>
                    <?php else: ?>
                    <div class="mb-3">
                        <label class="form-label-apple">Forward To Department</label>
                        <select class="form-control-apple" name="department" id="forwardDepartmentController" required>
                            <option value="">Select Department...</option>
                            <!-- Departments will be loaded dynamically -->
                        </select>
                    </div>
                    <?php endif; ?>
                    <!-- Priority will be auto-reset by system -->
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

// Removed checkbox selection functions - no longer needed

// Quick actions
function quickReply(ticketId) {
    document.getElementById('replyTicketId').textContent = ticketId;
    document.getElementById('quickReplyForm').dataset.ticketId = ticketId;
    new bootstrap.Modal(document.getElementById('quickReplyModal')).show();
}

function forwardTicket(ticketId) {
    document.getElementById('forwardTicketId').textContent = ticketId;
    document.getElementById('forwardForm').dataset.ticketId = ticketId;
    
    // Load zones and divisions for nodal controllers
    if (document.getElementById('forwardZone')) {
        loadZonesAndDivisions().then(() => {
            new bootstrap.Modal(document.getElementById('forwardModal')).show();
        });
    } else {
        new bootstrap.Modal(document.getElementById('forwardModal')).show();
    }
}

async function loadZonesAndDivisions() {
    try {
        // Get current user data (from PHP)
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
                // Pre-select user's zone
                if (zone.zone === userZone) {
                    option.selected = true;
                }
                zoneSelect.appendChild(option);
            });
        }
        
        // Load divisions based on selected/user's zone
        await loadDivisionsForZone(userZone || '');
        
        // Load departments for all users
        await loadDepartments();
        
        // Set up zone change handler to filter divisions
        if (zoneSelect) {
            zoneSelect.addEventListener('change', async function() {
                const selectedZone = this.value;
                await loadDivisionsForZone(selectedZone);
            });
        }
        
        // Set up division change handler to update department visibility
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
                    // Pre-select user's division
                    if (division.division === userDivision) {
                        option.selected = true;
                    }
                    divisionSelect.appendChild(option);
                });
                
                // Update department visibility after divisions are loaded
                setTimeout(() => updateDepartmentVisibility(), 100);
            }
        }
    } catch (error) {
        console.error('Error loading divisions for zone:', error);
    }
}

// Store all departments globally for filtering
let allDepartments = [];

async function loadDepartments() {
    try {
        const response = await fetch(`${APP_URL}/api/departments`);
        const data = await response.json();
        
        if (data.success) {
            allDepartments = data.departments; // Store for filtering
            
            // Load departments for nodal controller
            const nodalDeptSelect = document.getElementById('forwardDepartment');
            if (nodalDeptSelect) {
                populateDepartmentSelect(nodalDeptSelect, allDepartments);
            }
            
            // Load departments for regular controller
            const controllerDeptSelect = document.getElementById('forwardDepartmentController');
            if (controllerDeptSelect) {
                populateDepartmentSelect(controllerDeptSelect, allDepartments);
            }
            
            // Initial department visibility update
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
    const userDivision = '<?= $user['division'] ?? '' ?>';
    const selectedDivision = document.getElementById('forwardDivision')?.value || userDivision;
    const nodalDeptSelect = document.getElementById('forwardDepartment');
    
    console.log('updateDepartmentVisibility called:', {
        userDivision,
        selectedDivision,
        allDepartments: allDepartments.length,
        nodalDeptSelect: !!nodalDeptSelect
    });
    
    if (nodalDeptSelect) {
        // For controller_nodal: if forwarding outside their division, only show Commercial
        if (selectedDivision && selectedDivision !== userDivision && selectedDivision !== '') {
            // Forwarding outside division - only Commercial
            console.log('Forwarding outside division, filtering for Commercial departments');
            const commercialDepts = allDepartments.filter(dept => {
                // Check multiple possible codes for Commercial department
                return dept.department_code === 'CML' || 
                       dept.department_code === 'Commercial' || 
                       dept.department_name.toLowerCase().includes('commercial');
            });
            console.log('Found commercial departments:', commercialDepts);
            
            if (commercialDepts.length === 0) {
                // Fallback: if no commercial dept found, show a manual option
                nodalDeptSelect.innerHTML = '<option value="">Select Department...</option><option value="CML">Commercial</option>';
            } else {
                populateDepartmentSelect(nodalDeptSelect, commercialDepts);
            }
        } else {
            // Forwarding within division - all departments
            console.log('Forwarding within division, showing all departments');
            populateDepartmentSelect(nodalDeptSelect, allDepartments);
        }
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

<?php if ($user['role'] === 'controller_nodal' || $user['role'] === 'controller'): ?>
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
// Removed bulk action functions - no longer needed

// Removed checkbox event listeners - no longer needed
</script>

<style>
/* Custom styles for tickets page */
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
#controllerTicketsTable {
    font-size: 0.875rem;
}

#controllerTicketsTable th {
    background-color: #f8f9fc;
    border-bottom: 2px solid #e3e6f0;
    color: #5a5c69;
    font-weight: 600;
    padding: 0.75rem 0.5rem;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

#controllerTicketsTable td {
    padding: 0.75rem 0.5rem;
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
    #controllerTicketsTable {
        font-size: 0.8rem;
    }
    
    #controllerTicketsTable th,
    #controllerTicketsTable td {
        padding: 0.5rem 0.25rem;
    }
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