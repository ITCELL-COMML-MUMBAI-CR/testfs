<?php include '../src/views/layouts/header.php'; ?>

<main class="main-content">
    <div class="container-fluid">
        <div class="row">
            
            <!-- Sidebar -->
            <?php include '../src/views/admin/sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                
                <!-- Page Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        Email Management
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkEmailModal">
                                <i class="fas fa-paper-plane me-2"></i>Send Bulk Email
                            </button>
                            <a href="/admin/emails/templates" class="btn btn-outline-secondary">
                                <i class="fas fa-file-alt me-2"></i>Manage Templates
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Email Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 bg-primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-paper-plane fa-2x"></i>
                                    </div>
                                    <div>
                                        <div class="fs-4 fw-bold"><?= number_format($email_stats['total_sent']) ?></div>
                                        <div class="small">Total Sent (30 days)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border-0 bg-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <div class="fs-4 fw-bold"><?= number_format($email_stats['successfully_sent']) ?></div>
                                        <div class="small">Successfully Sent</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border-0 bg-danger">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    </div>
                                    <div>
                                        <div class="fs-4 fw-bold"><?= number_format($email_stats['failed']) ?></div>
                                        <div class="small">Failed</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border-0 bg-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                    <div>
                                        <div class="fs-4 fw-bold"><?= number_format($email_stats['pending']) ?></div>
                                        <div class="small">Pending</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Emails -->
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2"></i>Recent Email Activity
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_emails)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No recent email activity found.</p>
                                </div>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Subject</th>
                                                <th>Recipients</th>
                                                <th>Status</th>
                                                <th>Sent Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_emails as $email): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?= htmlspecialchars($email['subject'] ?? 'No Subject') ?></div>
                                                    <small class="text-muted">ID: #<?= $email['id'] ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?= $email['recipient_count'] ?? 1 ?> recipients</span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClass = [
                                                        'sent' => 'success',
                                                        'pending' => 'warning',
                                                        'failed' => 'danger',
                                                        'queued' => 'info'
                                                    ];
                                                    $status = $email['status'] ?? 'unknown';
                                                    $class = $statusClass[$status] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?= $class ?>"><?= ucfirst($status) ?></span>
                                                </td>
                                                <td>
                                                    <div><?= date('M j, Y', strtotime($email['created_at'])) ?></div>
                                                    <small class="text-muted"><?= date('g:i A', strtotime($email['created_at'])) ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-secondary" 
                                                                onclick="viewEmailDetails(<?= $email['id'] ?>)"
                                                                data-bs-toggle="tooltip" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if ($status === 'failed'): ?>
                                                        <button type="button" class="btn btn-outline-warning"
                                                                onclick="retryEmail(<?= $email['id'] ?>)"
                                                                data-bs-toggle="tooltip" title="Retry Send">
                                                            <i class="fas fa-redo"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</main>

<!-- Bulk Email Modal -->
<div class="modal fade" id="bulkEmailModal" tabindex="-1" aria-labelledby="bulkEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="bulkEmailForm" method="POST" action="/admin/emails/send">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkEmailModalLabel">
                        <i class="fas fa-paper-plane me-2"></i>Send Bulk Email
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    
                    <!-- Recipient Selection -->
                    <div class="mb-3">
                        <label for="recipient_type" class="form-label">Recipients</label>
                        <select class="form-select" id="recipient_type" name="recipient_type" required>
                            <option value="">Choose recipients...</option>
                            <option value="all">All Users (Customers & Staff)</option>
                            <option value="customers">All Customers</option>
                            <option value="staff">Railway Staff</option>
                            <option value="admins">Administrators Only</option>
                        </select>
                        <div class="form-text">Select who should receive this email.</div>
                    </div>
                    
                    <!-- Email Template -->
                    <div class="mb-3">
                        <label for="template_id" class="form-label">Email Template (Optional)</label>
                        <select class="form-select" id="template_id" name="template_id">
                            <option value="">Create custom message</option>
                            <?php foreach ($email_templates as $template): ?>
                            <option value="<?= $template['id'] ?>">
                                <?= htmlspecialchars($template['name']) ?> (<?= ucfirst($template['type']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Choose a pre-designed template or create a custom message.</div>
                    </div>
                    
                    <!-- Subject -->
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subject" name="subject" 
                               placeholder="Enter email subject" required maxlength="200">
                        <div class="form-text">Maximum 200 characters.</div>
                    </div>
                    
                    <!-- Message -->
                    <div class="mb-3">
                        <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="message" name="message" rows="8" 
                                  placeholder="Enter your message here..." required></textarea>
                        <div class="form-text">HTML formatting is supported.</div>
                    </div>
                    
                    <!-- Send Options -->
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="send_immediately" 
                                   name="send_immediately" value="1" checked>
                            <label class="form-check-label" for="send_immediately">
                                Send immediately
                            </label>
                        </div>
                        <div class="form-text">Uncheck to queue for later processing.</div>
                    </div>
                    
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Send Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Email Details Modal -->
<div class="modal fade" id="emailDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="emailDetailsContent">
                <!-- Content loaded via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Handle bulk email form submission
    document.getElementById('bulkEmailForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        // Disable button and show loading
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        
        fetch('/admin/emails/send', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>
                    ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.main-content .container-fluid .row .col-md-9').insertBefore(alert, document.querySelector('.row.mb-4'));
                
                // Close modal and reset form
                const modal = bootstrap.Modal.getInstance(document.getElementById('bulkEmailModal'));
                modal.hide();
                this.reset();
                
                // Reload page after 2 seconds to show updated statistics
                setTimeout(() => location.reload(), 2000);
                
            } else {
                // Show error message
                let errorMessage = 'Failed to send email.';
                if (data.errors && Array.isArray(data.errors)) {
                    errorMessage = data.errors.join('<br>');
                } else if (data.message) {
                    errorMessage = data.message;
                }
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger';
                errorDiv.innerHTML = errorMessage;
                this.querySelector('.modal-body').insertBefore(errorDiv, this.querySelector('.modal-body').firstChild);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.innerHTML = 'An unexpected error occurred. Please try again.';
            this.querySelector('.modal-body').insertBefore(errorDiv, this.querySelector('.modal-body').firstChild);
        })
        .finally(() => {
            // Re-enable button
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        });
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
});

// View email details function
function viewEmailDetails(emailId) {
    const modal = new bootstrap.Modal(document.getElementById('emailDetailsModal'));
    const content = document.getElementById('emailDetailsContent');
    
    content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    modal.show();
    
    fetch(`/admin/emails/${emailId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Subject:</strong> ${data.email.subject}<br>
                            <strong>Status:</strong> <span class="badge bg-primary">${data.email.status}</span><br>
                            <strong>Recipients:</strong> ${data.email.recipient_count}<br>
                            <strong>Sent:</strong> ${data.email.created_at}
                        </div>
                        <div class="col-md-6">
                            <strong>Template:</strong> ${data.email.template_name || 'Custom'}<br>
                            <strong>Sent By:</strong> ${data.email.sent_by}<br>
                            <strong>Type:</strong> ${data.email.recipient_type}
                        </div>
                    </div>
                    <hr>
                    <div>
                        <strong>Message:</strong>
                        <div class="border p-3 mt-2">${data.email.message}</div>
                    </div>
                `;
            } else {
                content.innerHTML = '<div class="alert alert-danger">Failed to load email details.</div>';
            }
        })
        .catch(error => {
            content.innerHTML = '<div class="alert alert-danger">An error occurred while loading email details.</div>';
        });
}

// Retry email function
function retryEmail(emailId) {
    if (confirm('Are you sure you want to retry sending this email?')) {
        fetch(`/admin/emails/${emailId}/retry`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                csrf_token: '<?= $csrf_token ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to retry email: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('An error occurred while retrying the email.');
        });
    }
}
</script>

<?php include '../src/views/layouts/footer.php'; ?>