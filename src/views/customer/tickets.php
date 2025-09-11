
<?php
// Capture the content
ob_start();
?>

<!-- Customer Tickets -->
<section class="py-apple-6">
    <div class="container-xl">
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div>
                        <h1 class="display-3 mb-2">My Support Tickets</h1>
                        <p class="text-muted mb-0">View and manage your freight support requests</p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <a href="<?= Config::getAppUrl() ?>/customer/tickets/create" class="btn btn-apple-primary">
                            <i class="fas fa-plus me-2"></i>Create New Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters Card -->
        <div class="card-apple-glass mb-4">
            <div class="card-body">
                <form id="filterForm" method="GET" action="<?= Config::getAppUrl() ?>/customer/tickets">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label for="status" class="form-label-apple small">Status</label>
                            <select class="form-control form-control-apple" id="status" name="status">
                                <option value="">All Status</option>
                                <?php foreach ($status_options as $key => $label): ?>
                                    <?php if ($key !== 'closed'): // Don't show closed by default ?>
                                        <option value="<?= $key ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="priority" class="form-label-apple small">Priority</label>
                            <select class="form-control form-control-apple" id="priority" name="priority">
                                <option value="">All Priority</option>
                                <?php foreach ($priority_options as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= $filters['priority'] === $key ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="date_from" class="form-label-apple small">From Date</label>
                            <input type="date" 
                                   class="form-control form-control-apple" 
                                   id="date_from" 
                                   name="date_from" 
                                   value="<?= htmlspecialchars($filters['date_from']) ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="date_to" class="form-label-apple small">To Date</label>
                            <input type="date" 
                                   class="form-control form-control-apple" 
                                   id="date_to" 
                                   name="date_to" 
                                   value="<?= htmlspecialchars($filters['date_to']) ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-apple-primary w-100">
                                <i class="fas fa-filter me-1"></i>Filter
                            </button>
                        </div>
                        
                        <div class="col-md-2">
                            <a href="<?= Config::getAppUrl() ?>/customer/tickets" class="btn btn-apple-glass w-100">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tickets Table -->
        <div class="card-apple">
            <div class="card-body">
                <?php if (!empty($tickets['data'])): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            Showing <?= count($tickets['data']) ?> of <?= $tickets['total'] ?> tickets
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-apple-glass btn-sm" onclick="exportTickets('pdf')">
                                <i class="fas fa-file-pdf me-1"></i>PDF
                            </button>
                            <button class="btn btn-apple-glass btn-sm" onclick="exportTickets('excel')">
                                <i class="fas fa-file-excel me-1"></i>Excel
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Support Tickets</h5>
                        <div class="d-flex align-items-center">
                            <small class="text-muted last-refresh-time me-3">Last updated: --</small>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="forceRefresh()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="customerTicketsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Created</th>
                                    <th>Age</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated by DataTables AJAX -->
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-ticket-alt text-muted" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-muted mb-2">No tickets found</h5>
                        <p class="text-muted mb-4">You don't have any support tickets matching your current filters.</p>
                        <a href="<?= Config::getAppUrl() ?>/customer/tickets/create" class="btn btn-apple-primary">
                            <i class="fas fa-plus me-2"></i>Create Your First Ticket
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Help Section -->
        <div class="card-apple-glass mt-4">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-1">
                            <i class="fas fa-info-circle text-apple-blue me-2"></i>
                            Understanding Ticket Status
                        </h6>
                        <div class="d-flex flex-wrap gap-3 mt-2">
                            <small><span class="badge status-pending me-1">Pending</span> Under review</small>
                            <small><span class="badge status-awaiting-feedback me-1">Awaiting Feedback</span> Your response needed</small>
                            <small><span class="badge status-awaiting-info me-1">Awaiting Info</span> Additional details required</small>
                            <small><span class="badge status-awaiting-approval me-1">Awaiting Approval</span> Solution pending approval</small>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                        <a href="<?= Config::getAppUrl() ?>/help" class="btn btn-apple-glass btn-sm">
                            <i class="fas fa-question-circle me-1"></i>More Help
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Background refresh scripts
document.addEventListener('DOMContentLoaded', function() {
    // Initialize customer tickets table with auto-refresh
    if (typeof initializeCustomerTicketsTable === 'function') {
        const customerTable = initializeCustomerTicketsTable('customerTicketsTable');
        console.log('Customer tickets table initialized with background refresh');
    } else {
        console.warn('DataTable configuration not loaded - fallback to basic table');
        initializeBasicTable();
    }
    
    // Update filter behavior for DataTables
    document.querySelectorAll('#filterForm select, #filterForm input').forEach(field => {
        field.addEventListener('change', function() {
            if (window.backgroundRefreshManager) {
                // Force immediate refresh when user changes filters
                window.backgroundRefreshManager.forceRefresh();
            }
        });
    });
});

function forceRefresh() {
    if (window.backgroundRefreshManager) {
        window.backgroundRefreshManager.forceRefresh();
        
        // Show brief confirmation
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Refreshing...';
        button.disabled = true;
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 2000);
    }
}

function initializeBasicTable() {
    // Fallback initialization if DataTables config is not available
    console.log('Using basic table without auto-refresh');
}

function copyTicketId(ticketId) {
    if (window.SAMPARK && window.SAMPARK.utils) {
        window.SAMPARK.utils.copyToClipboard('#' + ticketId)
            .then(() => {
                window.SAMPARK.ui.showToast('Ticket ID copied to clipboard', 'success');
            })
            .catch(err => {
                window.SAMPARK.ui.showToast('Failed to copy ticket ID', 'error');
            });
    } else {
        // Fallback copy method
        navigator.clipboard.writeText('#' + ticketId).then(() => {
            alert('Ticket ID copied to clipboard');
        });
    }
}

function provideFeedback(ticketId) {
    Swal.fire({
        title: 'Provide Feedback',
        html: `
            <div class="text-start">
                <p class="mb-3">Please rate the resolution provided for your ticket and share your feedback.</p>
                
                <div class="mb-3">
                    <label class="form-label">Rating</label>
                    <div class="rating-buttons">
                        <input type="radio" name="rating" value="excellent" id="excellent">
                        <label for="excellent" class="btn btn-outline-success">
                            <i class="fas fa-smile me-1"></i>Excellent
                        </label>
                        
                        <input type="radio" name="rating" value="satisfactory" id="satisfactory">
                        <label for="satisfactory" class="btn btn-outline-warning">
                            <i class="fas fa-meh me-1"></i>Satisfactory
                        </label>
                        
                        <input type="radio" name="rating" value="unsatisfactory" id="unsatisfactory">
                        <label for="unsatisfactory" class="btn btn-outline-danger">
                            <i class="fas fa-frown me-1"></i>Unsatisfactory
                        </label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="feedbackRemarks" class="form-label">Additional Comments (Optional)</label>
                    <textarea class="form-control" id="feedbackRemarks" rows="3" 
                              placeholder="Share your experience or suggestions..."></textarea>
                    <small class="text-muted">Required if rating is unsatisfactory</small>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Submit Feedback',
        confirmButtonClass: 'btn btn-apple-primary',
        cancelButtonClass: 'btn btn-apple-glass',
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
            submitFeedback(ticketId, result.value);
        }
    });
}

function submitFeedback(ticketId, feedback) {
    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('rating', feedback.rating);
    formData.append('remarks', feedback.remarks);
    
    fetch(APP_URL + '/customer/tickets/' + ticketId + '/feedback', {
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

function shareTicket(ticketId) {
    const shareUrl = window.location.origin + APP_URL + '/customer/tickets/' + ticketId;
    
    Swal.fire({
        title: 'Share Ticket',
        html: `
            <div class="text-start">
                <p class="mb-3">Share this ticket with colleagues or support team:</p>
                <div class="input-group">
                    <input type="text" class="form-control" value="${shareUrl}" id="shareUrl" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyShareUrl()">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <div class="mt-3">
                    <strong>Ticket ID:</strong> #${ticketId}
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

function printTicket(ticketId) {
    window.open(APP_URL + '/customer/tickets/' + ticketId + '?print=1', '_blank');
}

function exportTickets(format) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('export', format);
    window.open(currentUrl.toString(), '_blank');
}

// Real-time updates are now handled by the background refresh manager
// The old startRealTimeUpdates function is replaced by the new system

// Submit feedback updated to work with DataTables refresh
function submitFeedbackUpdated(ticketId, feedback) {
    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('rating', feedback.rating);
    formData.append('remarks', feedback.remarks);
    
    fetch(APP_URL + '/customer/tickets/' + ticketId + '/feedback', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.SAMPARK.ui.showSuccess('Feedback Submitted', data.message);
            
            // Force refresh instead of page reload
            if (window.backgroundRefreshManager) {
                window.backgroundRefreshManager.forceRefresh();
            } else {
                setTimeout(() => location.reload(), 1500);
            }
        } else {
            window.SAMPARK.ui.showError('Submission Failed', data.message);
        }
    })
    .catch(error => {
        window.SAMPARK.ui.showError('Error', 'Failed to submit feedback. Please try again.');
    });
}
</script>

<style>
/* Table enhancements */
.table th {
    background-color: var(--apple-off-white);
    border-bottom: 2px solid rgba(151, 151, 151, 0.1);
    font-weight: 600;
    font-size: 0.875rem;
}

.table td {
    border-color: rgba(151, 151, 151, 0.05);
    padding: 1rem 0.75rem;
}

.table tbody tr:hover {
    background-color: rgba(238, 238, 238, 0.3);
}

/* Status badges */
.badge {
    font-size: 0.75rem;
    padding: 0.375em 0.75em;
    border-radius: var(--apple-radius-small);
}

/* Rating buttons */
.rating-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.rating-buttons input[type="radio"] {
    display: none;
}

.rating-buttons label {
    cursor: pointer;
    transition: all 0.2s ease;
}

.rating-buttons input[type="radio"]:checked + label {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Pagination */
.pagination .page-link {
    border: 1px solid rgba(151, 151, 151, 0.2);
    color: var(--apple-black);
    background: var(--apple-white);
    border-radius: var(--apple-radius-small);
    margin: 0 2px;
}

.pagination .page-link:hover {
    background: var(--apple-off-white);
    border-color: var(--apple-blue);
    color: var(--apple-blue);
}

.pagination .page-item.active .page-link {
    background: var(--apple-blue);
    border-color: var(--apple-blue);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.8rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    
    .rating-buttons {
        flex-direction: column;
    }
    
    .rating-buttons label {
        text-align: center;
    }
}

/* Auto-close warning animation */
@keyframes urgent-pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.text-danger {
    animation: urgent-pulse 2s infinite;
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>  