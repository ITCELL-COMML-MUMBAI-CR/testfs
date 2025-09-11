<?php
// Capture the content
ob_start();
?>

<!-- Ticket Details -->
<section class="py-apple-6">
    <div class="container-xl">
        
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="<?= Config::getAppUrl() ?>/customer/dashboard" class="text-decoration-none">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="<?= Config::getAppUrl() ?>/customer/tickets" class="text-decoration-none">My Tickets</a>
                        </li>
                        <li class="breadcrumb-item active">#<?= htmlspecialchars($ticket['complaint_id']) ?></li>
                    </ol>
                </nav>
                
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div>
                        <h1 class="display-3 mb-2">
                            Ticket #<?= htmlspecialchars($ticket['complaint_id']) ?>
                            <span class="badge badge-apple status-<?= str_replace('_', '-', $ticket['status']) ?> ms-3">
                                <?= ucwords(str_replace('_', ' ', $ticket['status'])) ?>
                            </span>
                        </h1>
                        <p class="text-muted mb-0">
                            <?= htmlspecialchars($ticket['category']) ?> → <?= htmlspecialchars($ticket['type']) ?> → <?= htmlspecialchars($ticket['subtype']) ?>
                        </p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <div class="btn-group" role="group">
                            <button class="btn btn-apple-glass" onclick="printTicket()">
                                <i class="fas fa-print me-1"></i>Print
                            </button>
                            <button class="btn btn-apple-glass" onclick="shareTicket()">
                                <i class="fas fa-share me-1"></i>Share
                            </button>
                            <?php if ($requires_feedback): ?>
                                <button class="btn btn-warning" onclick="provideFeedback()">
                                    <i class="fas fa-star me-1"></i>Provide Feedback
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            
            <!-- Main Content -->
            <div class="col-12 col-lg-8">
                
                <!-- Ticket Information -->
                <div class="card-apple mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-info-circle text-apple-blue me-2"></i>
                            Ticket Information
                        </h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-apple small">Ticket ID</label>
                                <div class="d-flex align-items-center">
                                    <code class="text-apple-blue me-2">#<?= htmlspecialchars($ticket['complaint_id']) ?></code>
                                    <button class="btn btn-link btn-sm p-0" onclick="copyTicketId('<?= $ticket['complaint_id'] ?>')">
                                        <i class="fas fa-copy text-muted"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-apple small">Priority</label>
                                <div>
                                    <span class="badge badge-priority-<?= $ticket['priority'] ?>">
                                        <?= ucfirst($ticket['priority']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-apple small">Created On</label>
                                <div><?= date('F d, Y \a\t H:i', strtotime($ticket['created_at'])) ?></div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-apple small">Last Updated</label>
                                <div><?= date('F d, Y \a\t H:i', strtotime($ticket['updated_at'])) ?></div>
                            </div>
                            
                            <?php if ($ticket['fnr_number']): ?>
                                <div class="col-md-6">
                                    <label class="form-label-apple small">FNR Number</label>
                                    <div><?= htmlspecialchars($ticket['fnr_number']) ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($ticket['gstin_number'])): ?>
                                <div class="col-md-6">
                                    <label class="form-label-apple small">GSTIN</label>
                                    <div><?= htmlspecialchars($ticket['gstin_number']) ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($ticket['e_indent_number']): ?>
                                <div class="col-md-6">
                                    <label class="form-label-apple small">e-Indent Number</label>
                                    <div><?= htmlspecialchars($ticket['e_indent_number']) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Location & Incident Details -->
                <div class="card-apple mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-map-marker-alt text-apple-blue me-2"></i>
                            Location & Incident Details
                        </h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-apple small">Shed/Terminal</label>
                                <div>
                                    <div class="fw-medium"><?= htmlspecialchars($ticket['shed_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($ticket['shed_code']) ?></small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-apple small">Division/Zone</label>
                                <div>
                                    <div class="fw-medium"><?= htmlspecialchars($ticket['division']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($ticket['zone']) ?></small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-apple small">Incident Date</label>
                                <div><?= date('F d, Y', strtotime($ticket['date'])) ?></div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-apple small">Incident Time</label>
                                <div><?= date('H:i', strtotime($ticket['time'])) ?></div>
                            </div>
                            
                            <?php if ($ticket['wagon_code']): ?>
                                <div class="col-md-6">
                                    <label class="form-label-apple small">Wagon Details</label>
                                    <div>
                                        <div class="fw-medium"><?= htmlspecialchars($ticket['wagon_code']) ?></div>
                                        <small class="text-muted"><?= ucfirst($ticket['wagon_type'] ?? '') ?> Wagon</small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="card-apple mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-file-alt text-apple-blue me-2"></i>
                            Issue Description
                        </h5>
                        <div class="bg-light p-3 rounded">
                            <?= nl2br(htmlspecialchars($ticket['description'])) ?>
                        </div>
                    </div>
                </div>
                
                <!-- Supporting Documents -->
                <?php if (!empty($evidence)): ?>
                    <div class="card-apple mb-4">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-paperclip text-apple-blue me-2"></i>
                                Supporting Documents
                            </h5>
                            
                            <div class="row g-3">
                                <?php foreach ($evidence as $file): ?>
                                    <div class="col-md-4">
                                        <div class="card-apple-glass h-100">
                                            <div class="card-body text-center p-3">
                                                <?php 
                                                $extension = strtolower(pathinfo($file['original_name'], PATHINFO_EXTENSION));
                                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                                                ?>
                                                
                                                <?php if ($isImage): ?>
                                                    <img src="<?= Config::getPublicUploadPath() ?><?= htmlspecialchars($file['file_name']) ?>" 
                                                         alt="Supporting Document" 
                                                         class="img-thumbnail mb-2" 
                                                         style="max-height: 150px; cursor: pointer;"
                                                         onclick="viewImage('<?= Config::getPublicUploadPath() ?><?= htmlspecialchars($file['file_name']) ?>', '<?= htmlspecialchars($file['original_name']) ?>')">
                                                <?php else: ?>
                                                    <i class="fas fa-file-<?= $extension === 'pdf' ? 'pdf' : 'alt' ?> fa-3x text-muted mb-2"></i>
                                                <?php endif; ?>
                                                
                                                <div class="small">
                                                    <div class="fw-medium text-truncate" title="<?= htmlspecialchars($file['original_name']) ?>">
                                                        <?= htmlspecialchars($file['original_name']) ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?= number_format($file['file_size'] / 1024, 1) ?> KB
                                                    </small>
                                                </div>
                                                
                                                <div class="mt-2">
                                                    <a href="<?= Config::getPublicUploadPath() ?><?= htmlspecialchars($file['file_name']) ?>" 
                                                       target="_blank" 
                                                       class="btn btn-apple-glass btn-sm">
                                                        <i class="fas fa-eye me-1"></i>View
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
                
                <!-- Communication History -->
                <div class="card-apple">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-comments text-apple-blue me-2"></i>
                            Communication History
                        </h5>
                        
                        <div class="timeline">
                            <?php foreach ($transactions as $transaction): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker">
                                        <i class="fas fa-<?= getTransactionIcon($transaction['transaction_type']) ?> text-apple-blue"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="fw-medium">
                                                <?= getTransactionTitle($transaction['transaction_type']) ?>
                                            </div>
                                            <small class="text-muted">
                                                <?= date('M d, Y H:i', strtotime($transaction['created_at'])) ?>
                                            </small>
                                        </div>
                                        
                                        <?php if ($transaction['user_name']): ?>
                                            <div class="small text-muted mb-2">
                                                By: <?= htmlspecialchars($transaction['user_name']) ?> 
                                                (<?= ucfirst($transaction['user_role']) ?>)
                                            </div>
                                        <?php elseif ($transaction['created_by_type'] === 'customer'): ?>
                                            <div class="small text-muted mb-2">
                                                By: You (Customer)
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($transaction['remarks']): ?>
                                            <div class="bg-light p-3 rounded small">
                                                <?= nl2br(htmlspecialchars($transaction['remarks'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-12 col-lg-4">
                
                <!-- Status Card -->
                <div class="card-apple mb-4">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-<?= getStatusIcon($ticket['status']) ?> fa-3x text-apple-blue"></i>
                        </div>
                        <h6 class="fw-semibold mb-2">Current Status</h6>
                        <span class="badge badge-apple status-<?= str_replace('_', '-', $ticket['status']) ?> fs-6 px-3 py-2">
                            <?= ucwords(str_replace('_', ' ', $ticket['status'])) ?>
                        </span>
                        
                        <?php if ($ticket['status'] === 'awaiting_feedback'): ?>
                            <div class="mt-3">
                                <p class="small text-warning mb-2">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Your feedback is required to close this ticket
                                </p>
                                <button class="btn btn-warning btn-sm" onclick="provideFeedback()">
                                    <i class="fas fa-star me-1"></i>Provide Feedback
                                </button>
                            </div>
                        <?php elseif ($ticket['status'] === 'awaiting_info'): ?>
                            <div class="mt-3">
                                <p class="small text-info mb-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Additional information is required for this ticket
                                </p>
                                <button class="btn btn-info btn-sm" onclick="provideAdditionalInfo()">
                                    <i class="fas fa-plus-circle me-1"></i>Provide Additional Info
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Customer Information -->
                <div class="card-apple-glass mb-4">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-user text-apple-blue me-2"></i>
                            Customer Information
                        </h6>
                        
                        <div class="small">
                            <div class="mb-2">
                                <div class="fw-medium"><?= htmlspecialchars($ticket['customer_name']) ?></div>
                            </div>
                            <div class="mb-2">
                                <i class="fas fa-envelope me-2 text-muted"></i>
                                <?= htmlspecialchars($ticket['email']) ?>
                            </div>
                            <div class="mb-2">
                                <i class="fas fa-phone me-2 text-muted"></i>
                                <?= htmlspecialchars($ticket['mobile']) ?>
                            </div>
                            <div class="mb-0">
                                <i class="fas fa-building me-2 text-muted"></i>
                                <?= htmlspecialchars($ticket['company_name']) ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card-apple-glass mb-4">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-bolt text-apple-blue me-2"></i>
                            Quick Actions
                        </h6>
                        
                        <div class="d-grid gap-2">
                            <?php if ($requires_feedback): ?>
                                <button class="btn btn-warning" onclick="provideFeedback()">
                                    <i class="fas fa-star me-2"></i>Provide Feedback
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($ticket['status'] === 'awaiting_info'): ?>
                                <button class="btn btn-info" onclick="provideAdditionalInfo()">
                                    <i class="fas fa-plus-circle me-2"></i>Provide Additional Info
                                </button>
                            <?php endif; ?>
                            
                            <button class="btn btn-apple-glass" onclick="printTicket()">
                                <i class="fas fa-print me-2"></i>Print Ticket
                            </button>
                            
                            <button class="btn btn-apple-glass" onclick="shareTicket()">
                                <i class="fas fa-share me-2"></i>Share Ticket
                            </button>
                            
                            <a href="<?= Config::getAppUrl() ?>/customer/tickets/create" class="btn btn-apple-glass">
                                <i class="fas fa-plus me-2"></i>Create New Ticket
                            </a>
                            
                            <a href="<?= Config::getAppUrl() ?>/customer/tickets" class="btn btn-apple-glass">
                                <i class="fas fa-list me-2"></i>All My Tickets
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Help -->
                <div class="card-apple-glass">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-question-circle text-apple-blue me-2"></i>
                            Need Help?
                        </h6>
                        
                        <p class="small text-muted mb-3">
                            If you have questions about your ticket or need additional support, we're here to help.
                        </p>
                        
                        <div class="d-grid gap-2">
                            <a href="<?= Config::getAppUrl() ?>/help" class="btn btn-apple-glass btn-sm">
                                <i class="fas fa-book me-1"></i>Help Center
                            </a>
                            <button class="btn btn-apple-glass btn-sm" onclick="contactSupport()">
                                <i class="fas fa-headset me-1"></i>Contact Support
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function provideFeedback() {
    Swal.fire({
        title: 'Provide Feedback',
        html: `
            <div class="text-start">
                <p class="mb-3">Please rate the resolution provided for ticket #<?= $ticket['complaint_id'] ?> and share your feedback.</p>
                
                <div class="mb-3">
                    <label class="form-label">Rating</label>
                    <div class="rating-buttons d-flex gap-2 justify-content-center">
                        <input type="radio" name="rating" value="excellent" id="excellent" class="d-none">
                        <label for="excellent" class="btn btn-outline-success flex-fill">
                            <i class="fas fa-smile me-1"></i>Excellent
                        </label>
                        
                        <input type="radio" name="rating" value="satisfactory" id="satisfactory" class="d-none">
                        <label for="satisfactory" class="btn btn-outline-warning flex-fill">
                            <i class="fas fa-meh me-1"></i>Satisfactory
                        </label>
                        
                        <input type="radio" name="rating" value="unsatisfactory" id="unsatisfactory" class="d-none">
                        <label for="unsatisfactory" class="btn btn-outline-danger flex-fill">
                            <i class="fas fa-frown me-1"></i>Unsatisfactory
                        </label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="feedbackRemarks" class="form-label">Additional Comments</label>
                    <textarea class="form-control" id="feedbackRemarks" rows="3" 
                              placeholder="Share your experience, suggestions, or concerns..."></textarea>
                    <small class="text-muted">Required if rating is unsatisfactory</small>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Submit Feedback',
        customClass: {
            confirmButton: 'btn btn-apple-primary',
            cancelButton: 'btn btn-apple-glass'
        },
        width: '600px',
        preConfirm: () => {
            const rating = document.querySelector('input[name="rating"]:checked');
            const remarks = document.getElementById('feedbackRemarks').value.trim();
            
            if (!rating) {
                Swal.showValidationMessage('Please select a rating');
                return false;
            }
            
            if (rating.value === 'unsatisfactory' && !remarks) {
                Swal.showValidationMessage('Please provide comments for unsatisfactory rating');
                return false;
            }
            
            return {
                rating: rating.value,
                remarks: remarks
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitFeedback(result.value);
        }
    });
    
    // Add click handlers for radio button labels
    setTimeout(() => {
        document.querySelectorAll('.rating-buttons label').forEach(label => {
            label.addEventListener('click', function() {
                // Remove active class from all labels
                document.querySelectorAll('.rating-buttons label').forEach(l => l.classList.remove('active'));
                // Add active class to clicked label
                this.classList.add('active');
            });
        });
    }, 100);
}

function submitFeedback(feedback) {
    const formData = new FormData();
    formData.append('csrf_token', '<?= $csrf_token ?>');
    formData.append('rating', feedback.rating);
    formData.append('remarks', feedback.remarks);
    
    fetch('<?= Config::getAppUrl() ?>/customer/tickets/<?= $ticket['complaint_id'] ?>/feedback', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.SAMPARK.ui.showSuccess('Feedback Submitted', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            window.SAMPARK.ui.showError('Submission Failed', data.message);
        }
    })
    .catch(error => {
        window.SAMPARK.ui.showError('Error', 'Failed to submit feedback. Please try again.');
    });
}

function copyTicketId(ticketId) {
    window.SAMPARK.utils.copyToClipboard('#' + ticketId)
        .then(() => {
            window.SAMPARK.ui.showToast('Ticket ID copied to clipboard', 'success');
        })
        .catch(err => {
            window.SAMPARK.ui.showToast('Failed to copy ticket ID', 'error');
        });
}

function shareTicket() {
    const shareUrl = window.location.href;
    
    Swal.fire({
        title: 'Share Ticket',
        html: `
            <div class="text-start">
                <p class="mb-3">Share this ticket details:</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" value="${shareUrl}" id="shareUrl" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyShareUrl()">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <div>
                    <strong>Ticket ID:</strong> #<?= $ticket['complaint_id'] ?><br>
                    <strong>Status:</strong> <?= ucwords(str_replace('_', ' ', $ticket['status'])) ?><br>
                    <strong>Created:</strong> <?= date('M d, Y', strtotime($ticket['created_at'])) ?>
                </div>
            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true
    });
}

function copyShareUrl() {
    const shareUrl = document.getElementById('shareUrl');
    shareUrl.select();
    document.execCommand('copy');
    window.SAMPARK.ui.showToast('Share URL copied to clipboard', 'success');
}

function printTicket() {
    const printWindow = window.open('', '_blank');
    const ticketContent = generatePrintContent();
    
    printWindow.document.write(ticketContent);
    printWindow.document.close();
    printWindow.print();
}

function generatePrintContent() {
    return `
        <html>
        <head>
            <title>Ticket #<?= $ticket['complaint_id'] ?> - SAMPARK</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
                .header { text-align: center; border-bottom: 2px solid #0088cc; padding-bottom: 20px; margin-bottom: 20px; }
                .logo { font-size: 24px; font-weight: bold; color: #0088cc; }
                .section { margin-bottom: 20px; }
                .section h3 { color: #0088cc; border-bottom: 1px solid #eee; padding-bottom: 5px; }
                .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
                .info-item { margin-bottom: 10px; }
                .label { font-weight: bold; }
                .description { background: #f5f5f5; padding: 15px; border-radius: 5px; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo">SAMPARK</div>
                <div>Support and Mediation Portal for All Rail Cargo</div>
                <div style="margin-top: 10px;">Ticket #<?= $ticket['complaint_id'] ?></div>
            </div>
            
            <div class="section">
                <h3>Ticket Information</h3>
                <div class="info-grid">
                    <div class="info-item"><span class="label">Ticket ID:</span> #<?= $ticket['complaint_id'] ?></div>
                    <div class="info-item"><span class="label">Status:</span> <?= ucwords(str_replace('_', ' ', $ticket['status'])) ?></div>
                    <div class="info-item"><span class="label">Priority:</span> <?= ucfirst($ticket['priority']) ?></div>
                    <div class="info-item"><span class="label">Created:</span> <?= date('F d, Y \a\t H:i', strtotime($ticket['created_at'])) ?></div>
                </div>
            </div>
            
            <div class="section">
                <h3>Issue Details</h3>
                <div class="info-grid">
                    <div class="info-item"><span class="label">Category:</span> <?= htmlspecialchars($ticket['category']) ?></div>
                    <div class="info-item"><span class="label">Type:</span> <?= htmlspecialchars($ticket['type']) ?></div>
                    <div class="info-item"><span class="label">Subtype:</span> <?= htmlspecialchars($ticket['subtype']) ?></div>
                    <div class="info-item"><span class="label">Incident Date:</span> <?= date('F d, Y', strtotime($ticket['date'])) ?></div>
                </div>
            </div>
            
            <div class="section">
                <h3>Location Details</h3>
                <div class="info-grid">
                    <div class="info-item"><span class="label">Shed:</span> <?= htmlspecialchars($ticket['shed_name']) ?></div>
                    <div class="info-item"><span class="label">Division:</span> <?= htmlspecialchars($ticket['division']) ?></div>
                    <div class="info-item"><span class="label">Zone:</span> <?= htmlspecialchars($ticket['zone']) ?></div>
                    <div class="info-item"><span class="label">Shed Code:</span> <?= htmlspecialchars($ticket['shed_code']) ?></div>
                </div>
            </div>
            
            <div class="section">
                <h3>Description</h3>
                <div class="description"><?= nl2br(htmlspecialchars($ticket['description'])) ?></div>
            </div>
            
            <div class="section">
                <h3>Customer Information</h3>
                <div class="info-grid">
                    <div class="info-item"><span class="label">Name:</span> <?= htmlspecialchars($ticket['customer_name']) ?></div>
                    <div class="info-item"><span class="label">Email:</span> <?= htmlspecialchars($ticket['email']) ?></div>
                    <div class="info-item"><span class="label">Mobile:</span> <?= htmlspecialchars($ticket['mobile']) ?></div>
                    <div class="info-item"><span class="label">Company:</span> <?= htmlspecialchars($ticket['company_name']) ?></div>
                </div>
            </div>
            
            <div style="margin-top: 40px; text-align: center; color: #999; font-size: 12px;">
                Generated on <?= date('F d, Y \a\t H:i') ?> | SAMPARK Support System
            </div>
        </body>
        </html>
    `;
}

function viewImage(imageUrl, imageName) {
    Swal.fire({
        title: imageName,
        imageUrl: imageUrl,
        imageAlt: imageName,
        showCloseButton: true,
        showConfirmButton: false,
        width: '80%',
        padding: '1rem'
    });
}

function contactSupport() {
    window.SAMPARK.ui.showInfo('Contact Support', 
        'Email: support@sampark.railway.gov.in<br>' +
        'Phone: 1800-XXX-XXXX<br>' +
        'Hours: Mon-Fri 9:00 AM - 6:00 PM<br><br>' +
        'Please mention your ticket ID: #<?= $ticket['complaint_id'] ?>'
    );
}

function provideAdditionalInfo() {
    Swal.fire({
        title: 'Provide Additional Information',
        html: `
            <div class="text-start">
                <p class="mb-3">Please provide the additional information requested for ticket #<?= $ticket['complaint_id'] ?>.</p>
                
                <div class="mb-3">
                    <label for="additionalInfoText" class="form-label">Additional Information</label>
                    <textarea class="form-control" id="additionalInfoText" rows="5" 
                              placeholder="Provide the requested information, clarifications, or additional details..."></textarea>
                    <small class="text-muted">This information will be appended to your original ticket description</small>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Submit Information',
        customClass: {
            confirmButton: 'btn btn-info',
            cancelButton: 'btn btn-secondary'
        },
        width: '600px',
        preConfirm: () => {
            const additionalInfo = document.getElementById('additionalInfoText').value.trim();
            
            if (!additionalInfo) {
                Swal.showValidationMessage('Please provide the additional information');
                return false;
            }
            
            return additionalInfo;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitAdditionalInfo(result.value);
        }
    });
}

function submitAdditionalInfo(additionalInfo) {
    const formData = new FormData();
    formData.append('csrf_token', '<?= $csrf_token ?>');
    formData.append('additional_info', additionalInfo);
    
    fetch('<?= Config::getAppUrl() ?>/customer/tickets/<?= $ticket['complaint_id'] ?>/provide-info', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.SAMPARK.ui.showSuccess('Information Submitted', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            window.SAMPARK.ui.showError('Submission Failed', data.message);
        }
    })
    .catch(error => {
        window.SAMPARK.ui.showError('Error', 'Failed to submit additional information. Please try again.');
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
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--apple-off-white);
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0;
    width: 2rem;
    height: 2rem;
    background: var(--apple-white);
    border: 2px solid var(--apple-blue);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
}

.timeline-content {
    background: var(--apple-white);
    border: 1px solid rgba(151, 151, 151, 0.1);
    border-radius: var(--apple-radius-medium);
    padding: 1rem;
    box-shadow: var(--apple-shadow-soft);
}

/* Status icons */
.fa-clock { color: #ffc107; }
.fa-check-circle { color: #28a745; }
.fa-exclamation-circle { color: #dc3545; }
.fa-info-circle { color: #17a2b8; }
.fa-pause-circle { color: #6c757d; }

/* Rating button styles */
.rating-buttons label.active {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.rating-buttons label.btn-outline-success.active {
    background-color: #28a745;
    color: white;
    border-color: #28a745;
}

.rating-buttons label.btn-outline-warning.active {
    background-color: #ffc107;
    color: #212529;
    border-color: #ffc107;
}

.rating-buttons label.btn-outline-danger.active {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .timeline {
        padding-left: 1.5rem;
    }
    
    .timeline::before {
        left: 0.75rem;
    }
    
    .timeline-marker {
        left: -0.75rem;
        width: 1.5rem;
        height: 1.5rem;
        font-size: 0.6rem;
    }
    
    .rating-buttons {
        flex-direction: column;
    }
    
    .btn-group {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-group .btn {
        margin-bottom: 0.5rem;
    }
}
</style>

<?php
// Helper functions for transaction display
function getTransactionIcon($type) {
    switch ($type) {
        case 'created': return 'plus-circle';
        case 'forwarded': return 'arrow-right';
        case 'replied': return 'reply';
        case 'approved': return 'check-circle';
        case 'rejected': return 'times-circle';
        case 'reverted': return 'undo';
        case 'closed': return 'check-circle';
        case 'escalated': return 'exclamation-triangle';
        case 'feedback_submitted': return 'star';
        default: return 'circle';
    }
}

function getTransactionTitle($type) {
    switch ($type) {
        case 'created': return 'Ticket Created';
        case 'forwarded': return 'Ticket Forwarded';
        case 'replied': return 'Reply Added';
        case 'approved': return 'Reply Approved';
        case 'rejected': return 'Reply Rejected';
        case 'reverted': return 'Ticket Reverted';
        case 'closed': return 'Ticket Closed';
        case 'escalated': return 'Priority Escalated';
        case 'feedback_submitted': return 'Feedback Submitted';
        default: return ucfirst(str_replace('_', ' ', $type));
    }
}

function getStatusIcon($status) {
    switch ($status) {
        case 'pending': return 'clock';
        case 'awaiting_feedback': return 'comment';
        case 'awaiting_info': return 'info-circle';
        case 'awaiting_approval': return 'check-circle';
        case 'closed': return 'check-circle';
        default: return 'circle';
    }
}

$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
