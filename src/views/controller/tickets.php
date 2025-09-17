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
                <?php if ($user['role'] === 'controller_nodal'): ?>
                <a href="<?= Config::getAppUrl() ?>/controller/forwarded-tickets" class="action-btn action-btn-info action-btn-with-text">
                    <i class="fas fa-share-alt"></i>Forwarded Tickets
                </a>
                <?php endif; ?>
                <button class="action-btn action-btn-secondary action-btn-with-text" onclick="exportTickets()">
                    <i class="fas fa-download"></i>Export
                </button>
                <button class="action-btn action-btn-primary action-btn-with-text" onclick="forceRefresh()">
                    <i class="fas fa-sync-alt"></i>Refresh
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
                            <div class="h4 mb-0 fw-semibold" id="pendingCount" data-stat="pending">
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
                            <div class="h4 mb-0 fw-semibold" id="highPriorityCount" data-stat="high_priority">0</div>
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
                            <div class="h4 mb-0 fw-semibold" id="resolvedTodayCount" data-stat="resolved_today">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Cards for Controller Nodal -->
        <?php if ($user['role'] === 'controller_nodal'): ?>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-apple h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 rounded-3 p-3 me-3">
                            <i class="fas fa-share-alt text-info fa-lg"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Forwarded</div>
                            <div class="h4 mb-0 fw-semibold" id="forwardedCount" data-stat="forwarded_complaints">
                                <?= $ticket_stats['forwarded_complaints'] ?? 0 ?>
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
                                <?= $ticket_stats['awaiting_approval'] ?? 0 ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
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
                        <div class="col-md-6 col-lg-3">
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
                <i class="fas fa-list me-2"></i>Support Tickets
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
                            <th class="border-0" style="width: 160px;">Assigned To</th>
                            <th class="border-0" style="width: 120px;">Created</th>
                            <th class="border-0" style="width: 300px;">Description</th>
                            <th class="border-0" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets['data'])): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-center">
                                    <i class="fas fa-inbox text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mb-2">No tickets assigned</h5>
                                    <p class="text-muted">You don't have any tickets assigned at the moment. New tickets will appear here when they're assigned to your department.</p>
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
                                                  title="<?= htmlspecialchars($ticket['assigned_department_name'] ?? $ticket['assigned_to_department'] ?? 'N/A') ?>">
                                                <?= htmlspecialchars($ticket['assigned_department_name'] ?? $ticket['assigned_to_department'] ?? 'N/A') ?>
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
                                <td>
                                    <div class="action-buttons-group" role="group">
                                        <?php
                                        // Check if controller_nodal can take actions on this ticket
                                        $isOwnDepartment = ($ticket['assigned_to_department'] === $user['department']);
                                        $isAwaitingInfo = ($ticket['status'] === 'awaiting_info');
                                        $canTakeActions = ($user['role'] !== 'controller_nodal') || ($isOwnDepartment && !$isAwaitingInfo);
                                        
                                        $viewButtonClass = $canTakeActions ? 'action-btn-primary' : 'action-btn-secondary';
                                        $viewButtonTitle = $canTakeActions ? 'View Details - Can take actions' : 
                                                          ($isAwaitingInfo ? 'View Only - Awaiting customer info' : 'View Only - Restricted access');
                                        ?>
                                        <a href="<?= Config::getAppUrl() ?>/controller/tickets/<?= $ticket['complaint_id'] ?>" 
                                           class="action-btn <?= $viewButtonClass ?> action-btn-compact" title="<?= $viewButtonTitle ?>">
                                            <i class="fas fa-eye"></i>
                                            <?php if ($isAwaitingInfo && $user['role'] === 'controller_nodal'): ?>
                                            <i class="fas fa-clock text-warning ms-1" style="font-size: 0.7em;"></i>
                                            <?php endif; ?>
                                        </a>
                                        <?php 
                                        // Show forward button only if user can take actions
                                        if (in_array($user['role'], ['controller', 'controller_nodal']) && 
                                            in_array($ticket['status'], ['pending', 'awaiting_info']) &&
                                            $canTakeActions): 
                                        ?>
                                        <button class="action-btn action-btn-warning action-btn-compact" 
                                                onclick="forwardTicket(<?= $ticket['complaint_id'] ?>)" title="Forward">
                                            <i class="fas fa-share"></i>
                                        </button>
                                        <?php endif; ?>
                                        
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
                    <button type="button" class="action-btn action-btn-secondary action-btn-with-text" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>Cancel
                    </button>
                    <button type="submit" class="action-btn action-btn-primary action-btn-with-text">
                        <i class="fas fa-paper-plane"></i>Send Reply
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
                        <label class="form-label-apple">Internal Remarks</label>
                        <textarea class="form-control-apple" name="internal_remarks" rows="4" required 
                                  placeholder="Add internal remarks for forwarding..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="action-btn action-btn-secondary action-btn-with-text" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>Cancel
                    </button>
                    <button type="submit" class="action-btn action-btn-primary action-btn-with-text">
                        <i class="fas fa-share"></i>Forward Ticket
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
    // Initialize DataTable for tickets table
    // Use basic DataTable since we have server-rendered HTML data
    initializeBasicDataTable();
    
    // Load stats immediately
    updateStats();
    
    // Setup event listeners
    setupEventListeners();
    
    // Initialize background refresh manager if available
    if (window.backgroundRefreshManager) {
        // Set up periodic stats refresh
        setInterval(updateStats, 30000); // Refresh stats every 30 seconds
    }
    
    console.log('Controller tickets page initialized');
});

function setupEventListeners() {
    // Handle filter changes
    document.querySelectorAll('.ticket-filter').forEach(element => {
        element.addEventListener('change', function() {
            // Apply filters immediately when changed
            applyFilters();
        });
    });
    
    // Search with debounce
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 500);
        });
    }
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
    // Update stats immediately
    updateStats();
    
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

function initializeBasicTable() {
    // Fallback initialization if DataTables config is not available
    console.log('Using basic table without auto-refresh');
}

function initializeBasicDataTable() {
    // Simple DataTable initialization with built-in search
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        try {
            const $table = $('#controllerTicketsTable');
            
            // Check if table exists and has content
            if ($table.length === 0) {
                console.warn('Table #controllerTicketsTable not found');
                return;
            }
            
            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('#controllerTicketsTable')) {
                $table.DataTable().destroy();
                $table.empty(); // Clear any leftover DataTables elements
            }
            
            // Verify table structure before initializing
            const headerCols = $table.find('thead tr th').length;
            const firstRowCols = $table.find('tbody tr:first td').length;
            const hasEmptyMessage = $table.find('tbody tr:first td[colspan]').length > 0;
            const dataRowCount = $table.find('tbody tr').not(':has(td[colspan])').length;
            
            console.log(`Table structure: ${headerCols} headers, ${firstRowCols} cells in first row, ${dataRowCount} data rows, hasEmptyMessage: ${hasEmptyMessage}`);
            
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
                        search: "Search tickets:",
                        lengthMenu: "Show _MENU_ tickets",
                        info: "Showing _START_ to _END_ of _TOTAL_ tickets",
                        infoEmpty: "No tickets to display - you're all caught up!",
                        infoFiltered: "(filtered from _MAX_ total tickets)",
                        emptyTable: "No tickets found matching your criteria. Try adjusting your filters or check back later for new assignments.",
                        paginate: {
                            first: "First",
                            last: "Last", 
                            next: "Next",
                            previous: "Previous"
                        }
                    }
                });
                console.log('DataTable initialized successfully for controller tickets');
            } else {
                console.log(`Skipping DataTables initialization - no data rows available (${dataRowCount} data rows, empty message: ${hasEmptyMessage})`);
            }
        } catch (error) {
            console.error('Error initializing DataTable:', error);
            // Fallback to basic table functionality
            console.log('Using table without DataTables due to initialization error');
        }
    } else {
        console.warn('DataTable library not available');
    }
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
    
    // Default sort direction - critical tickets first for priority
    if (column === 'priority') {
        params.set('sort_dir', 'critical_first');
    } else {
        // Toggle sort direction for other columns
        const currentSort = params.get('sort_dir');
        params.set('sort_dir', currentSort === 'desc' ? 'asc' : 'desc');
    }
    
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
    
    // Load zones and divisions for nodal controllers, or just departments for controllers
    if (document.getElementById('forwardZone')) {
        // Nodal controller modal
        loadZonesAndDivisions().then(() => {
            new bootstrap.Modal(document.getElementById('forwardModal')).show();
        });
    } else {
        // Regular controller modal - load departments only
        loadDepartments().then(() => {
            new bootstrap.Modal(document.getElementById('forwardModal')).show();
        });
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
    const userRole = '<?= $user['role'] ?? '' ?>';
    const userDivision = '<?= $user['division'] ?? '' ?>';
    const selectedDivision = document.getElementById('forwardDivision')?.value || userDivision;
    const nodalDeptSelect = document.getElementById('forwardDepartment');
    const controllerDeptSelect = document.getElementById('forwardDepartmentController');
    
    console.log('updateDepartmentVisibility called:', {
        userRole,
        userDivision,
        selectedDivision,
        allDepartments: allDepartments.length,
        nodalDeptSelect: !!nodalDeptSelect,
        controllerDeptSelect: !!controllerDeptSelect
    });
    
    // Filter for Commercial departments
    const commercialDepts = allDepartments.filter(dept => {
        // Check multiple possible codes for Commercial department
        return dept.department_code === 'COMM' || 
               dept.department_code === 'CML' ||
               dept.department_code === 'Commercial' || 
               dept.department_name.toLowerCase().includes('commercial');
    });
    
    if (controllerDeptSelect && userRole === 'controller') {
        // Regular controllers can only forward to Commercial departments of same division
        console.log('Controller role: showing only Commercial departments');
        
        if (commercialDepts.length === 0) {
            // Fallback: if no commercial dept found, show a manual option
            controllerDeptSelect.innerHTML = '<option value="">Select Department...</option><option value="COMM">Commercial</option>';
        } else {
            populateDepartmentSelect(controllerDeptSelect, commercialDepts);
        }
    }
    
    if (nodalDeptSelect && userRole === 'controller_nodal') {
        // For controller_nodal: if forwarding outside their division, only show Commercial
        if (selectedDivision && selectedDivision !== userDivision && selectedDivision !== '') {
            // Forwarding outside division - only Commercial
            console.log('Nodal Controller forwarding outside division, filtering for Commercial departments');
            
            if (commercialDepts.length === 0) {
                // Fallback: if no commercial dept found, show a manual option
                nodalDeptSelect.innerHTML = '<option value="">Select Department...</option><option value="COMM">Commercial</option>';
            } else {
                populateDepartmentSelect(nodalDeptSelect, commercialDepts);
            }
        } else {
            // Forwarding within division - all departments
            console.log('Nodal Controller forwarding within division, showing all departments');
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
    // Update dashboard stats from API
    fetch(`${APP_URL}/api/tickets/stats`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.stats) {
                const stats = data.stats;
                document.getElementById('pendingCount').textContent = stats.pending || 0;
                document.getElementById('highPriorityCount').textContent = stats.high_priority || 0;
                document.getElementById('resolvedTodayCount').textContent = stats.resolved_today || 0;
                
                // Update ticket count in header
                const ticketCountElement = document.getElementById('ticketCount');
                if (ticketCountElement) {
                    ticketCountElement.textContent = stats.total || 0;
                }
            }
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
        padding: 0.75rem 0.5rem;
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
        padding: 0.5rem;
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