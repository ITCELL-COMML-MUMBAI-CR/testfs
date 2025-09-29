<?php
/**
 * Admin Approval Review Interface
 * Allows admins to review, approve, reject, or edit ticket responses
 */

// Start output buffering for layout
ob_start();

// Data passed from controller
$complaintId = $complaint_id;
$ticket = $ticket;
$canApprove = $can_approve;
$approvalType = $approval_type;
$transactions = $transactions;
$workflowLog = $workflow_log;
$evidenceFiles = $evidence_files;
$currentUser = $user;
$pageTitle = ucfirst(str_replace('_', ' ', $approvalType)) . ' Review';
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <div>
                            <h5 class="mb-0"><?= $pageTitle ?></h5>
                            <p class="text-sm mb-0">Ticket #<?= htmlspecialchars($ticket['complaint_id']) ?></p>
                        </div>
                        <div class="ms-auto">
                            <span class="badge bg-gradient-<?= getPriorityColor($ticket['priority']) ?> me-2">
                                <?= strtoupper($ticket['priority']) ?>
                            </span>
                            <span class="badge bg-gradient-info">
                                <?= ucfirst(str_replace('_', ' ', $ticket['status'])) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Customer Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-sm font-weight-bold mb-2">Customer Details</h6>
                            <p class="text-sm mb-1"><strong>Name:</strong> <?= htmlspecialchars($ticket['customer_name']) ?></p>
                            <p class="text-sm mb-1"><strong>Company:</strong> <?= htmlspecialchars($ticket['company_name']) ?></p>
                            <p class="text-sm mb-1"><strong>Email:</strong> <?= htmlspecialchars($ticket['customer_email']) ?></p>
                            <p class="text-sm mb-1"><strong>Mobile:</strong> <?= htmlspecialchars($ticket['customer_mobile']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-sm font-weight-bold mb-2">Ticket Information</h6>
                            <p class="text-sm mb-1"><strong>Category:</strong> <?= htmlspecialchars($ticket['category']) ?></p>
                            <p class="text-sm mb-1"><strong>Type:</strong> <?= htmlspecialchars($ticket['type']) ?></p>
                            <p class="text-sm mb-1"><strong>Department:</strong> <?= htmlspecialchars($ticket['department'] ?? 'N/A') ?></p>
                            <p class="text-sm mb-1"><strong>Division:</strong> <?= htmlspecialchars($ticket['division']) ?></p>
                            <p class="text-sm mb-1"><strong>Created:</strong> <?= date('d M Y, H:i', strtotime($ticket['created_at'])) ?></p>
                        </div>
                    </div>

                    <!-- Original Complaint -->
                    <div class="mb-4">
                        <h6 class="text-sm font-weight-bold mb-2">Original Complaint</h6>
                        <div class="p-3 bg-gray-100 rounded">
                            <p class="text-sm mb-0"><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
                        </div>
                    </div>

                    <!-- Current Action Taken -->
                    <div class="mb-4">
                        <h6 class="text-sm font-weight-bold mb-2">Proposed Resolution</h6>
                        <div class="p-3 bg-light rounded">
                            <div id="original-action" <?= ($approvalType === 'dept_admin') ? '' : 'style="display: none;"' ?>>
                                <p class="text-sm mb-0"><?= nl2br(htmlspecialchars($ticket['action_taken'])) ?></p>
                            </div>
                            <div id="edit-action" style="display: none;">
                                <textarea id="edited-action-content" class="form-control" rows="5"><?= htmlspecialchars($ticket['action_taken']) ?></textarea>
                                <div class="d-flex mt-2">
                                    <button type="button" class="btn btn-sm btn-success" onclick="saveEdit()">
                                        <i class="fas fa-save"></i> Save Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary ms-2" onclick="cancelEdit()">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                            </div>
                            <?php if ($approvalType === 'dept_admin'): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="enableEdit()">
                                    <i class="fas fa-edit"></i> Edit Resolution
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Previous Approvals (for CML admin) -->
                    <?php if ($approvalType === 'cml_admin' && $ticket['dept_admin_approved_by']): ?>
                        <div class="mb-4">
                            <h6 class="text-sm font-weight-bold mb-2">Department Admin Approval</h6>
                            <div class="p-3 bg-success-light rounded">
                                <p class="text-sm mb-1">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <strong>Approved by:</strong> <?= htmlspecialchars($ticket['dept_admin_name']) ?>
                                </p>
                                <p class="text-sm mb-1">
                                    <strong>Date:</strong> <?= date('d M Y, H:i', strtotime($ticket['dept_admin_approved_at'])) ?>
                                </p>
                                <?php if ($ticket['dept_admin_remarks']): ?>
                                    <p class="text-sm mb-0">
                                        <strong>Remarks:</strong> <?= nl2br(htmlspecialchars($ticket['dept_admin_remarks'])) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Evidence Files -->
                    <?php if (!empty($evidenceFiles)): ?>
                        <div class="mb-4">
                            <h6 class="text-sm font-weight-bold mb-2">Attached Evidence</h6>
                            <div class="row">
                                <?php foreach ($evidenceFiles as $evidence): ?>
                                    <?php for ($i = 1; $i <= 3; $i++): ?>
                                        <?php if ($evidence["file_name_$i"]): ?>
                                            <div class="col-md-4 mb-2">
                                                <a href="<?= htmlspecialchars($evidence["file_path_$i"]) ?>"
                                                   class="btn btn-outline-info btn-sm d-block" target="_blank">
                                                    <i class="fas fa-file"></i> <?= htmlspecialchars($evidence["file_name_$i"]) ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Action Panel -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Approval Actions</h6>
                </div>
                <div class="card-body">
                    <form id="approval-form" action="<?= Config::getAppUrl() ?>/admin/approvals/process" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="complaint_id" value="<?= htmlspecialchars($ticket['complaint_id']) ?>">
                        <input type="hidden" name="approval_type" value="<?= $approvalType ?>">
                        <input type="hidden" name="action" id="form-action">
                        <input type="hidden" name="edited_content" id="form-edited-content">

                        <div class="mb-3">
                            <label for="admin-remarks" class="form-label">Admin Remarks</label>
                            <textarea name="remarks" id="admin-remarks" class="form-control" rows="3"
                                      placeholder="Optional remarks for this approval..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success" onclick="submitApproval('approve')">
                                <i class="fas fa-check"></i> Approve
                            </button>

                            <button type="button" class="btn btn-danger" onclick="submitApproval('reject')">
                                <i class="fas fa-times"></i> Reject
                            </button>

                            <hr>

                            <a href="<?= Config::getAppUrl() ?>/admin/approvals/pending" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Workflow Timeline -->
            <?php if (!empty($workflowLog)): ?>
                <div class="card mt-3">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">Approval Timeline</h6>
                    </div>
                    <div class="card-body">
                        <div class="timeline timeline-one-side">
                            <?php foreach ($workflowLog as $step): ?>
                                <div class="timeline-block mb-3">
                                    <span class="timeline-step">
                                        <i class="fas fa-<?= getWorkflowIcon($step['action']) ?> text-<?= getWorkflowColor($step['action']) ?>"></i>
                                    </span>
                                    <div class="timeline-content">
                                        <h6 class="text-dark text-sm font-weight-bold mb-0">
                                            <?= ucwords(str_replace('_', ' ', $step['workflow_step'])) ?>
                                        </h6>
                                        <p class="text-secondary text-xs mt-1 mb-0">
                                            <?= htmlspecialchars($step['user_name']) ?> - <?= ucfirst($step['action']) ?>
                                        </p>
                                        <p class="text-xs text-secondary mb-0">
                                            <?= date('d M Y, H:i', strtotime($step['created_at'])) ?>
                                        </p>
                                        <?php if ($step['remarks']): ?>
                                            <p class="text-xs mt-1 mb-0">
                                                <strong>Remarks:</strong> <?= htmlspecialchars($step['remarks']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
let isEditing = false;
let originalContent = '';

function enableEdit() {
    if (isEditing) return;

    isEditing = true;
    originalContent = document.getElementById('edited-action-content').value;

    document.getElementById('original-action').style.display = 'none';
    document.getElementById('edit-action').style.display = 'block';
}

function cancelEdit() {
    isEditing = false;

    document.getElementById('edited-action-content').value = originalContent;
    document.getElementById('original-action').style.display = 'block';
    document.getElementById('edit-action').style.display = 'none';
}

function saveEdit() {
    isEditing = false;
    const editedContent = document.getElementById('edited-action-content').value;

    // Update the original display
    document.getElementById('original-action').innerHTML = '<p class="text-sm mb-0">' + editedContent.replace(/\n/g, '<br>') + '</p>';

    document.getElementById('original-action').style.display = 'block';
    document.getElementById('edit-action').style.display = 'none';
}

async function submitApproval(action) {
    const form = document.getElementById('approval-form');
    const remarks = document.getElementById('admin-remarks').value.trim();

    // Check if rejection requires remarks
    if (action === 'reject' && !remarks) {
        alert('Please provide remarks for rejection.');
        return;
    }

    // Determine final action based on edit state
    let finalAction = action;
    let editedContent = '';

    if (isEditing && action === 'approve') {
        finalAction = '<?= $approvalType ?>_edit_approve';
        editedContent = document.getElementById('edited-action-content').value;
    } else if (action === 'approve') {
        finalAction = '<?= $approvalType ?>_approve';
    } else if (action === 'reject') {
        finalAction = '<?= $approvalType ?>_reject';
    }

    // Add confirmation
    const confirmMessage = action === 'approve'
        ? 'Are you sure you want to approve this ticket?'
        : 'Are you sure you want to reject this ticket?';

    if (!confirm(confirmMessage)) {
        return;
    }

    try {
        // Show loading state
        const submitButtons = document.querySelectorAll('.btn-success, .btn-danger');
        submitButtons.forEach(btn => btn.disabled = true);

        const formData = new FormData();
        formData.append('csrf_token', '<?= $csrf_token ?>');
        formData.append('complaint_id', '<?= htmlspecialchars($ticket['complaint_id']) ?>');
        formData.append('approval_type', '<?= $approvalType ?>');
        formData.append('action', finalAction);
        formData.append('remarks', remarks);
        if (editedContent) {
            formData.append('edited_content', editedContent);
        }

        const response = await fetch('<?= Config::getAppUrl() ?>/admin/approvals/process', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message);
            window.location.href = '<?= Config::getAppUrl() ?>/admin/approvals/pending';
        } else {
            alert('Error: ' + result.message);
            // Re-enable buttons
            submitButtons.forEach(btn => btn.disabled = false);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to process approval. Please try again.');
        // Re-enable buttons
        const submitButtons = document.querySelectorAll('.btn-success, .btn-danger');
        submitButtons.forEach(btn => btn.disabled = false);
    }
}

// Helper functions for workflow display
function getWorkflowIcon(action) {
    const icons = {
        'submit': 'paper-plane',
        'approve': 'check',
        'reject': 'times',
        'edit_and_approve': 'edit'
    };
    return icons[action] || 'circle';
}

function getWorkflowColor(action) {
    const colors = {
        'submit': 'info',
        'approve': 'success',
        'reject': 'danger',
        'edit_and_approve': 'warning'
    };
    return colors[action] || 'secondary';
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

function getWorkflowIcon($action) {
    $icons = [
        'submit' => 'paper-plane',
        'approve' => 'check',
        'reject' => 'times',
        'edit_and_approve' => 'edit'
    ];
    return $icons[$action] ?? 'circle';
}

function getWorkflowColor($action) {
    $colors = [
        'submit' => 'info',
        'approve' => 'success',
        'reject' => 'danger',
        'edit_and_approve' => 'warning'
    ];
    return $colors[$action] ?? 'secondary';
}

// Get content and include layout
$content = ob_get_clean();
include __DIR__ . '/../../layouts/app.php';
?>