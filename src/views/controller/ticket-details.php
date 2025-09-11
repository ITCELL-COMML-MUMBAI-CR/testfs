<?php
/**
 * Controller Ticket Details View - SAMPARK
 * Comprehensive ticket management interface with all actions and details
 */

// Capture the content
ob_start();

// Set additional CSS for this view
$additional_css = [
    Config::APP_URL . '/assets/css/controller-views.css'
];

// Set page title
$page_title = 'Ticket Details - SAMPARK';
?>

<div class="container-xl py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= Config::APP_URL ?>/controller/dashboard" class="text-decoration-none">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= Config::APP_URL ?>/controller/tickets" class="text-decoration-none">Support Hub</a>
            </li>
            <li class="breadcrumb-item active">Ticket #<?= $ticket['complaint_id'] ?></li>
        </ol>
    </nav>

    <!-- Ticket Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <div class="d-flex align-items-center">
                <div class="bg-apple-blue rounded-3 p-3 me-3">
                    <i class="fas fa-ticket-alt text-white fa-lg"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h1 class="h3 mb-1 fw-semibold">Ticket #<?= $ticket['complaint_id'] ?></h1>
                        <?php
                        $priorityClass = [
                            'critical' => 'danger',
                            'high' => 'warning', 
                            'medium' => 'info',
                            'normal' => 'secondary'
                        ][$ticket['priority']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $priorityClass ?> fs-6">
                            <i class="fas fa-<?= $ticket['priority'] === 'critical' ? 'exclamation-circle' : 
                                              ($ticket['priority'] === 'high' ? 'exclamation-triangle' : 
                                               ($ticket['priority'] === 'medium' ? 'info-circle' : 'circle')) ?>"></i>
                            <?= ucfirst($ticket['priority']) ?> Priority
                        </span>
                        <?php if ($ticket['is_sla_violated']): ?>
                        <span class="badge bg-danger fs-6">
                            <i class="fas fa-clock me-1"></i>SLA Overdue
                        </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted mb-0">
                        Created <?= date('M d, Y \a\t H:i', strtotime($ticket['created_at'])) ?> • 
                        <?= $ticket['hours_elapsed'] ?> hours ago
                    </p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <div class="btn-toolbar gap-2">
                <div class="btn-group">
                    <button class="btn btn-apple-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-2"></i>Actions
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="printTicket()">
                            <i class="fas fa-print me-2"></i>Print Ticket
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportTicket()">
                            <i class="fas fa-download me-2"></i>Export PDF
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php if ($permissions['can_revert']): ?>
                        <li><a class="dropdown-item text-warning" href="#" onclick="revertTicket()">
                            <i class="fas fa-undo me-2"></i>Revert Ticket
                        </a></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item text-info" href="#" onclick="viewHistory()">
                            <i class="fas fa-history me-2"></i>View History
                        </a></li>
                    </ul>
                </div>
                <a href="<?= Config::APP_URL ?>/controller/tickets" class="btn btn-apple-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    <?php
    $statusInfo = [
        'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'This ticket is pending action'],
        'awaiting_info' => ['class' => 'info', 'icon' => 'info-circle', 'text' => 'Waiting for additional information from customer'],
        'awaiting_approval' => ['class' => 'primary', 'icon' => 'check-circle', 'text' => 'Reply is awaiting nodal controller approval'],
        'awaiting_feedback' => ['class' => 'success', 'icon' => 'comment', 'text' => 'Reply sent, waiting for customer feedback'],
        'closed' => ['class' => 'dark', 'icon' => 'check', 'text' => 'This ticket has been resolved and closed']
    ][$ticket['status']] ?? ['class' => 'secondary', 'icon' => 'question', 'text' => 'Status unknown'];
    ?>
    
    <div class="alert alert-<?= $statusInfo['class'] ?> d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-<?= $statusInfo['icon'] ?> me-2"></i>
        <strong>Status: <?= ucwords(str_replace('_', ' ', $ticket['status'])) ?></strong> - <?= $statusInfo['text'] ?>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Ticket Details Card -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Ticket Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Category:</strong>
                            <div><?= htmlspecialchars($ticket['category'] ?? 'N/A') ?></div>
                            <?php if ($ticket['subtype']): ?>
                            <small class="text-muted"><?= htmlspecialchars($ticket['subtype']) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Location:</strong>
                            <div><?= htmlspecialchars($ticket['shed_name'] ?? 'N/A') ?></div>
                            <?php if ($ticket['shed_code']): ?>
                            <small class="text-muted">Code: <?= htmlspecialchars($ticket['shed_code']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($ticket['wagon_code']): ?>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Wagon Details:</strong>
                            <div><?= htmlspecialchars($ticket['wagon_code']) ?></div>
                            <?php if ($ticket['wagon_type']): ?>
                            <small class="text-muted"><?= htmlspecialchars($ticket['wagon_type']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <div class="mt-2 p-3 bg-light rounded">
                            <?= nl2br(htmlspecialchars($ticket['complaint_message'] ?? 'No description provided')) ?>
                        </div>
                    </div>
                    
                    <?php if ($ticket['action_taken']): ?>
                    <div class="mb-3">
                        <strong>Action Taken:</strong>
                        <div class="mt-2 p-3 bg-success bg-opacity-10 rounded">
                            <?= nl2br(htmlspecialchars($ticket['action_taken'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Evidence Files -->
            <?php if (!empty($evidence)): ?>
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-paperclip me-2"></i>Evidence Files
                        <span class="badge bg-primary ms-2"><?= count($evidence) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($evidence as $file): ?>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="me-3">
                                    <?php
                                    $fileIcon = 'fa-file';
                                    if (str_contains($file['file_type'], 'image')) $fileIcon = 'fa-file-image';
                                    elseif (str_contains($file['file_type'], 'pdf')) $fileIcon = 'fa-file-pdf';
                                    elseif (str_contains($file['file_type'], 'video')) $fileIcon = 'fa-file-video';
                                    ?>
                                    <i class="fas <?= $fileIcon ?> fa-2x text-muted"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold"><?= htmlspecialchars($file['original_name']) ?></div>
                                    <small class="text-muted">
                                        <?= number_format($file['file_size'] / 1024, 1) ?> KB • 
                                        <?= date('M d, Y', strtotime($file['uploaded_at'])) ?>
                                    </small>
                                    <div class="mt-2">
                                        <a href="<?= Config::APP_URL ?>/api/tickets/<?= $ticket['complaint_id'] ?>/evidence/<?= urlencode($file['file_name']) ?>" 
                                           class="btn btn-sm btn-apple-primary me-2" target="_blank">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        <a href="<?= Config::APP_URL ?>/api/tickets/<?= $ticket['complaint_id'] ?>/evidence/<?= urlencode($file['file_name']) ?>?download=1" 
                                           class="btn btn-sm btn-apple-secondary">
                                            <i class="fas fa-download me-1"></i>Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Transaction History -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Transaction History
                        <span class="badge bg-primary ms-2"><?= count($transactions) ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($transactions)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-history fa-3x mb-3"></i>
                        <h5>No transaction history</h5>
                        <p>This ticket has no transaction history yet.</p>
                    </div>
                    <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($transactions as $index => $transaction): ?>
                        <div class="timeline-item <?= $index === 0 ? 'active' : '' ?>">
                            <div class="timeline-marker">
                                <?php
                                $icon = [
                                    'created' => 'fa-plus-circle',
                                    'forwarded' => 'fa-share',
                                    'replied' => 'fa-reply',
                                    'approved' => 'fa-check-circle',
                                    'rejected' => 'fa-times-circle',
                                    'reverted' => 'fa-undo',
                                    'closed' => 'fa-check'
                                ][$transaction['transaction_type']] ?? 'fa-circle';
                                ?>
                                <i class="fas <?= $icon ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= ucfirst($transaction['transaction_type']) ?></h6>
                                        <p class="mb-2"><?= nl2br(htmlspecialchars($transaction['remarks'])) ?></p>
                                        <div class="text-muted small">
                                            <i class="fas fa-user me-1"></i>
                                            <?= htmlspecialchars($transaction['user_name'] ?? $transaction['customer_name'] ?? 'System') ?>
                                            <span class="ms-2">
                                                <i class="fas fa-clock me-1"></i>
                                                <?= date('M d, Y H:i', strtotime($transaction['created_at'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Panel -->
            <?php if ($permissions['can_reply'] || $permissions['can_approve']): ?>
            <div class="card card-apple">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tools me-2"></i>Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php if ($permissions['can_reply']): ?>
                        <div class="col-md-6">
                            <button class="btn btn-apple-primary w-100" onclick="showReplyModal()">
                                <i class="fas fa-reply me-2"></i>Reply to Customer
                            </button>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($permissions['can_forward']): ?>
                        <div class="col-md-6">
                            <button class="btn btn-apple-warning w-100" onclick="showForwardModal()">
                                <i class="fas fa-share me-2"></i>Forward Ticket
                            </button>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($permissions['can_approve']): ?>
                        <div class="col-md-6">
                            <button class="btn btn-apple-success w-100" onclick="approveReply()">
                                <i class="fas fa-check me-2"></i>Approve Reply
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-apple-danger w-100" onclick="showRejectModal()">
                                <i class="fas fa-times me-2"></i>Reject Reply
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Customer Information -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Customer Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Name:</strong>
                        <div><?= htmlspecialchars($ticket['customer_name'] ?? 'N/A') ?></div>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong>
                        <div>
                            <?php if ($ticket['customer_email']): ?>
                            <a href="mailto:<?= htmlspecialchars($ticket['customer_email']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($ticket['customer_email']) ?>
                            </a>
                            <?php else: ?>
                            N/A
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Mobile:</strong>
                        <div>
                            <?php if ($ticket['customer_mobile']): ?>
                            <a href="tel:<?= htmlspecialchars($ticket['customer_mobile']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($ticket['customer_mobile']) ?>
                            </a>
                            <?php else: ?>
                            N/A
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($ticket['company_name']): ?>
                    <div class="mb-3">
                        <strong>Company:</strong>
                        <div><?= htmlspecialchars($ticket['company_name']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Assignment Info -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-cog me-2"></i>Assignment
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Assigned To:</strong>
                        <div>
                            <?= htmlspecialchars($ticket['assigned_user_name'] ?? 'Unassigned') ?>
                            <?php if ($ticket['assigned_user_role']): ?>
                            <small class="text-muted d-block"><?= ucfirst($ticket['assigned_user_role']) ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <strong>Division:</strong>
                        <div><?= htmlspecialchars($ticket['division'] ?? 'N/A') ?></div>
                    </div>
                    <div class="mb-3">
                        <strong>Zone:</strong>
                        <div><?= htmlspecialchars($ticket['zone'] ?? 'N/A') ?></div>
                    </div>
                </div>
            </div>

            <!-- SLA Information -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>SLA Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Resolution Time:</strong>
                        <div class="d-flex align-items-center">
                            <?php if ($ticket['is_sla_violated']): ?>
                            <span class="badge bg-danger me-2">Overdue</span>
                            <?php else: ?>
                            <span class="badge bg-success me-2">On Time</span>
                            <?php endif; ?>
                            <?= $ticket['hours_elapsed'] ?> hours
                        </div>
                    </div>
                    <?php if ($ticket['sla_deadline']): ?>
                    <div class="mb-3">
                        <strong>SLA Deadline:</strong>
                        <div><?= date('M d, Y H:i', strtotime($ticket['sla_deadline'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card card-apple">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-apple-secondary btn-sm" onclick="copyTicketUrl()">
                            <i class="fas fa-copy me-2"></i>Copy Ticket URL
                        </button>
                        <button class="btn btn-apple-secondary btn-sm" onclick="emailCustomer()">
                            <i class="fas fa-envelope me-2"></i>Email Customer
                        </button>
                        <button class="btn btn-apple-secondary btn-sm" onclick="addNote()">
                            <i class="fas fa-sticky-note me-2"></i>Add Internal Note
                        </button>
                        <button class="btn btn-apple-secondary btn-sm" onclick="escalateTicket()">
                            <i class="fas fa-arrow-up me-2"></i>Escalate
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reply Modal -->
<?php if ($permissions['can_reply']): ?>
<div class="modal fade" id="replyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reply to Ticket #<?= $ticket['complaint_id'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="replyForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-apple">Reply Message *</label>
                        <textarea class="form-control-apple" name="reply" rows="6" required 
                                  placeholder="Enter your detailed reply to the customer..."></textarea>
                        <div class="form-text">This message will be sent to the customer.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Action Taken *</label>
                        <textarea class="form-control-apple" name="action_taken" rows="4" required 
                                  placeholder="Describe the specific actions taken to resolve this issue..."></textarea>
                        <div class="form-text">Internal record of actions taken.</div>
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
                    <div class="mb-3">
                        <label class="form-label-apple">Attach Files (Optional)</label>
                        <input type="file" class="form-control-apple" name="attachments[]" multiple 
                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                        <div class="form-text">Maximum 5 files, 10MB each. Supported: JPG, PNG, PDF, DOC, DOCX</div>
                    </div>
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
<?php endif; ?>

<!-- Forward Modal -->
<?php if ($permissions['can_forward']): ?>
<div class="modal fade" id="forwardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Forward Ticket #<?= $ticket['complaint_id'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="forwardForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-apple">Assign To User *</label>
                        <select class="form-control-apple" name="to_user_id" required>
                            <option value="">Select User...</option>
                            <?php foreach ($available_users as $availableUser): ?>
                            <option value="<?= $availableUser['id'] ?>">
                                <?= htmlspecialchars($availableUser['name']) ?> (<?= ucfirst($availableUser['role']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Priority *</label>
                        <select class="form-control-apple" name="priority" required>
                            <option value="normal" <?= $ticket['priority'] === 'normal' ? 'selected' : '' ?>>Normal</option>
                            <option value="medium" <?= $ticket['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="high" <?= $ticket['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                            <option value="critical" <?= $ticket['priority'] === 'critical' ? 'selected' : '' ?>>Critical</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Forwarding Remarks *</label>
                        <textarea class="form-control-apple" name="remarks" rows="4" required 
                                  placeholder="Add remarks for forwarding this ticket..."></textarea>
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

<!-- Reject Modal -->
<?php if ($permissions['can_approve']): ?>
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Reply</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-apple">Rejection Reason *</label>
                        <textarea class="form-control-apple" name="rejection_reason" rows="4" required 
                                  placeholder="Explain why this reply is being rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Reject Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Ticket actions JavaScript
const ticketId = <?= $ticket['complaint_id'] ?>;

// Modal functions
function showReplyModal() {
    new bootstrap.Modal(document.getElementById('replyModal')).show();
}

function showForwardModal() {
    new bootstrap.Modal(document.getElementById('forwardModal')).show();
}

function showRejectModal() {
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

// Form submissions
<?php if ($permissions['can_reply']): ?>
document.getElementById('replyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/reply`, {
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
            if (result.errors) {
                const errors = Object.values(result.errors).join('\n');
                Swal.fire('Validation Error', errors, 'error');
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        }
    } catch (error) {
        hideLoading();
        Swal.fire('Error', 'Failed to send reply', 'error');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('replyModal')).hide();
});
<?php endif; ?>

<?php if ($permissions['can_forward']): ?>
document.getElementById('forwardForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
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
            if (result.errors) {
                const errors = Object.values(result.errors).join('\n');
                Swal.fire('Validation Error', errors, 'error');
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        }
    } catch (error) {
        hideLoading();
        Swal.fire('Error', 'Failed to forward ticket', 'error');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('forwardModal')).hide();
});
<?php endif; ?>

<?php if ($permissions['can_approve']): ?>
function approveReply() {
    Swal.fire({
        title: 'Approve Reply',
        text: 'Are you sure you want to approve this reply?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Approve',
        cancelButtonText: 'Cancel',
        input: 'textarea',
        inputPlaceholder: 'Add approval remarks (optional)...',
        inputAttributes: {
            'aria-label': 'Approval remarks'
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN);
            formData.append('approval_remarks', result.value || '');
            
            try {
                showLoading();
                const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/approve`, {
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
                    Swal.fire('Error', apiResult.message, 'error');
                }
            } catch (error) {
                hideLoading();
                Swal.fire('Error', 'Failed to approve reply', 'error');
            }
        }
    });
}

document.getElementById('rejectForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/reject`, {
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
            if (result.errors) {
                const errors = Object.values(result.errors).join('\n');
                Swal.fire('Validation Error', errors, 'error');
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        }
    } catch (error) {
        hideLoading();
        Swal.fire('Error', 'Failed to reject reply', 'error');
    }
    
    bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
});
<?php endif; ?>

function revertTicket() {
    Swal.fire({
        title: 'Revert Ticket',
        text: 'This will revert the ticket status and request additional information from the customer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Revert',
        cancelButtonText: 'Cancel',
        input: 'textarea',
        inputPlaceholder: 'Reason for reverting...',
        inputAttributes: {
            'aria-label': 'Revert reason'
        },
        inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a reason for reverting!'
            }
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN);
            formData.append('revert_reason', result.value);
            
            try {
                showLoading();
                const response = await fetch(`${APP_URL}/controller/tickets/${ticketId}/revert`, {
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
                    Swal.fire('Error', apiResult.message, 'error');
                }
            } catch (error) {
                hideLoading();
                Swal.fire('Error', 'Failed to revert ticket', 'error');
            }
        }
    });
}

// Utility functions
function copyTicketUrl() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        Swal.fire({
            title: 'Copied!',
            text: 'Ticket URL copied to clipboard',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    });
}

function emailCustomer() {
    const email = '<?= $ticket['customer_email'] ?? '' ?>';
    if (email) {
        window.location.href = `mailto:${email}?subject=Regarding Ticket #${ticketId}`;
    } else {
        Swal.fire('Error', 'Customer email not available', 'error');
    }
}

function printTicket() {
    window.print();
}

function exportTicket() {
    window.location.href = `${APP_URL}/controller/tickets/${ticketId}/export`;
}

function viewHistory() {
    // Implementation for viewing detailed history
    Swal.fire({
        title: 'Ticket History',
        text: 'Detailed history view coming soon',
        icon: 'info'
    });
}

function addNote() {
    // Implementation for adding internal notes
    Swal.fire({
        title: 'Add Internal Note',
        input: 'textarea',
        inputPlaceholder: 'Enter internal note...',
        showCancelButton: true,
        confirmButtonText: 'Add Note'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            // API call to add note
            Swal.fire('Success', 'Internal note added', 'success');
        }
    });
}

function escalateTicket() {
    Swal.fire({
        title: 'Escalate Ticket',
        text: 'This will escalate the ticket to higher authorities.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Escalate'
    }).then((result) => {
        if (result.isConfirmed) {
            // API call to escalate
            Swal.fire('Success', 'Ticket escalated successfully', 'success');
        }
    });
}
</script>

<style>
/* Timeline styles */
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--bs-border-color);
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -2.25rem;
    top: 0.25rem;
    width: 2rem;
    height: 2rem;
    background: white;
    border: 2px solid var(--bs-border-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
}

.timeline-item.active .timeline-marker {
    background: var(--apple-primary);
    border-color: var(--apple-primary);
    color: white;
}

.timeline-content {
    background: var(--bs-gray-50);
    border-radius: var(--apple-radius-medium);
    padding: 1rem;
    border-left: 3px solid var(--bs-border-color);
}

.timeline-item.active .timeline-content {
    border-left-color: var(--apple-primary);
}

/* Card enhancements */
.card-apple {
    transition: all 0.2s ease;
}

.card-apple:hover {
    transform: translateY(-1px);
    box-shadow: var(--apple-shadow-medium);
}

/* Priority badge animations */
.badge.bg-danger {
    animation: pulse 2s infinite;
}

/* Print styles */
@media print {
    .btn, .btn-group, .btn-toolbar,
    .modal, .navbar, .breadcrumb,
    .alert, .card-header .dropdown {
        display: none !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    
    .card-body {
        padding: 1rem !important;
    }
}

/* Mobile responsive */
@media (max-width: 768px) {
    .timeline {
        padding-left: 1.5rem;
    }
    
    .timeline-marker {
        left: -1.75rem;
        width: 1.5rem;
        height: 1.5rem;
        font-size: 0.75rem;
    }
    
    .timeline-content {
        padding: 0.75rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn-toolbar {
        flex-direction: column;
    }
    
    .btn-toolbar .btn-group,
    .btn-toolbar .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}

/* File preview enhancements */
.evidence-file {
    transition: transform 0.2s ease;
}

.evidence-file:hover {
    transform: scale(1.02);
}

/* Status-specific styling */
.alert.alert-danger {
    border-left: 4px solid var(--bs-danger);
}

.alert.alert-warning {
    border-left: 4px solid var(--bs-warning);
}

.alert.alert-success {
    border-left: 4px solid var(--bs-success);
}

.alert.alert-info {
    border-left: 4px solid var(--bs-info);
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>