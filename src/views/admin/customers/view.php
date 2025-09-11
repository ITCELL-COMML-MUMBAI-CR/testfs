<?php
// Capture the content
ob_start();
?>

<!-- Admin Customer Details -->
<section class="py-apple-6">
    <div class="container-xl">
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= Config::getAppUrl() ?>/admin/customers">Customers</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($customer['name']) ?></li>
                            </ol>
                        </nav>
                        <h1 class="display-3 mb-2">Customer Details</h1>
                        <p class="text-muted mb-0">View and manage customer information</p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <a href="<?= Config::getAppUrl() ?>/admin/customers/<?= $customer['customer_id'] ?>/edit" class="btn btn-apple-primary me-2">
                            <i class="fas fa-edit me-2"></i>Edit Customer
                        </a>
                        <a href="<?= Config::getAppUrl() ?>/admin/customers" class="btn btn-apple-glass">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <!-- Customer Information -->
            <div class="col-lg-8">
                <div class="card-apple">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>Customer Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-apple small text-muted">Customer ID</label>
                                <div class="fw-medium">
                                    <code class="text-apple-blue"><?= htmlspecialchars($customer['customer_id']) ?></code>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple small text-muted">Status</label>
                                <div>
                                    <span class="badge <?= getStatusBadgeClass($customer['status']) ?>">
                                        <?= ucfirst($customer['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple small text-muted">Full Name</label>
                                <div class="fw-medium"><?= htmlspecialchars($customer['name']) ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple small text-muted">Customer Type</label>
                                <div>
                                    <span class="badge <?= getCustomerTypeBadgeClass($customer['customer_type']) ?>">
                                        <?= ucfirst($customer['customer_type']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple small text-muted">Email Address</label>
                                <div class="fw-medium">
                                    <a href="mailto:<?= htmlspecialchars($customer['email']) ?>" class="text-apple-blue">
                                        <?= htmlspecialchars($customer['email']) ?>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple small text-muted">Mobile Number</label>
                                <div class="fw-medium">
                                    <a href="tel:<?= htmlspecialchars($customer['mobile']) ?>" class="text-apple-blue">
                                        <?= htmlspecialchars($customer['mobile']) ?>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple small text-muted">Company Name</label>
                                <div class="fw-medium"><?= htmlspecialchars($customer['company_name']) ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple small text-muted">Designation</label>
                                <div class="fw-medium"><?= htmlspecialchars($customer['designation'] ?: 'Not specified') ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple small text-muted">GSTIN</label>
                                <div class="fw-medium"><?= htmlspecialchars($customer['gstin'] ?: 'Not provided') ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple small text-muted">Division</label>
                                <div class="fw-medium"><?= htmlspecialchars($customer['division']) ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple small text-muted">Zone</label>
                                <div class="fw-medium"><?= htmlspecialchars($customer['zone']) ?></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple small text-muted">Registration Date</label>
                                <div class="fw-medium"><?= date('M d, Y H:i', strtotime($customer['created_at'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Tickets -->
                <div class="card-apple mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-ticket-alt me-2"></i>Recent Tickets
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_tickets)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ticket ID</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            <th>Priority</th>
                                            <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_tickets as $ticket): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= Config::getAppUrl() ?>/admin/tickets/<?= $ticket['complaint_id'] ?>" class="text-apple-blue">
                                                        <?= htmlspecialchars($ticket['complaint_id']) ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($ticket['description']) ?>">
                                                        <?= htmlspecialchars($ticket['description']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge <?= getStatusBadgeClass($ticket['status']) ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $ticket['status'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?= getPriorityBadgeClass($ticket['priority']) ?>">
                                                        <?= ucfirst($ticket['priority']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M d, Y', strtotime($ticket['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-ticket-alt text-muted mb-3" style="font-size: 3rem;"></i>
                                <h6 class="text-muted">No tickets found</h6>
                                <p class="text-muted mb-0">This customer hasn't created any tickets yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Statistics & Actions -->
            <div class="col-lg-4">
                <!-- Statistics -->
                <div class="card-apple">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Statistics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <div class="bg-apple-off-white rounded p-3">
                                    <h4 class="fw-light text-apple-blue mb-1"><?= $customer['total_tickets'] ?></h4>
                                    <small class="text-muted">Total Tickets</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-apple-off-white rounded p-3">
                                    <h4 class="fw-light text-warning mb-1"><?= $customer['open_tickets'] ?></h4>
                                    <small class="text-muted">Open Tickets</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-apple-off-white rounded p-3">
                                    <h4 class="fw-light text-success mb-1"><?= $customer['closed_tickets'] ?></h4>
                                    <small class="text-muted">Closed Tickets</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-apple-off-white rounded p-3">
                                    <h4 class="fw-light text-info mb-1">
                                        <?= $customer['total_tickets'] > 0 ? round(($customer['closed_tickets'] / $customer['total_tickets']) * 100) : 0 ?>%
                                    </h4>
                                    <small class="text-muted">Resolution Rate</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card-apple mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if ($customer['status'] === 'pending'): ?>
                                <button class="btn btn-success" onclick="verifyCustomer('<?= $customer['customer_id'] ?>')">
                                    <i class="fas fa-check-circle me-2"></i>Approve Customer
                                </button>
                                <button class="btn btn-danger" onclick="rejectCustomer('<?= $customer['customer_id'] ?>')">
                                    <i class="fas fa-times-circle me-2"></i>Reject Customer
                                </button>
                            <?php elseif ($customer['status'] === 'approved'): ?>
                                <button class="btn btn-warning" onclick="confirmStatusChange('<?= $customer['customer_id'] ?>', 'suspended')">
                                    <i class="fas fa-pause-circle me-2"></i>Suspend Customer
                                </button>
                            <?php elseif ($customer['status'] === 'suspended'): ?>
                                <button class="btn btn-success" onclick="confirmStatusChange('<?= $customer['customer_id'] ?>', 'approved')">
                                    <i class="fas fa-play-circle me-2"></i>Reactivate Customer
                                </button>
                            <?php endif; ?>
                            
                            <a href="<?= Config::getAppUrl() ?>/admin/tickets?customer_id=<?= $customer['customer_id'] ?>" class="btn btn-apple-glass">
                                <i class="fas fa-ticket-alt me-2"></i>View All Tickets
                            </a>
                            
                            <button class="btn btn-apple-glass" onclick="sendEmail('<?= $customer['customer_id'] ?>')">
                                <i class="fas fa-envelope me-2"></i>Send Email
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function verifyCustomer(customerId) {
    Swal.fire({
        title: 'Approve Customer',
        text: 'Approve this customer registration?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Yes, approve',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${APP_URL}/admin/customers/${customerId}/verify`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Approved!', 'Customer has been approved and notified.', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Failed to approve customer.', 'error');
            });
        }
    });
}

function rejectCustomer(customerId) {
    Swal.fire({
        title: 'Reject Customer',
        input: 'textarea',
        inputLabel: 'Rejection Reason',
        inputPlaceholder: 'Please provide a reason for rejection...',
        inputValidator: (value) => {
            if (!value || value.length < 10) {
                return 'Please provide a reason with at least 10 characters';
            }
        },
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Reject Customer',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${APP_URL}/admin/customers/${customerId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({
                    rejection_reason: result.value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Rejected!', 'Customer has been rejected and notified.', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Failed to reject customer.', 'error');
            });
        }
    });
}

function confirmStatusChange(customerId, newStatus) {
    const actionText = newStatus === 'approved' ? 'reactivate' : 'suspend';
    const actionIcon = newStatus === 'approved' ? 'check-circle' : 'pause-circle';
    const confirmBtnColor = newStatus === 'approved' ? '#28a745' : '#ffc107';
    
    Swal.fire({
        title: `${newStatus === 'approved' ? 'Reactivate' : 'Suspend'} Customer`,
        text: `Are you sure you want to ${actionText} this customer?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: confirmBtnColor,
        confirmButtonText: `Yes, ${actionText} customer`,
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${APP_URL}/admin/customers/${customerId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Updated!', 
                        `Customer has been ${newStatus === 'approved' ? 'reactivated' : 'suspended'}.`, 
                        'success'
                    );
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Failed to update customer status.', 'error');
            });
        }
    });
}

function sendEmail(customerId) {
    window.location.href = `${APP_URL}/admin/customers/${customerId}/email`;
}
</script>

<style>
/* Card enhancements */
.card-apple {
    border: 1px solid rgba(151, 151, 151, 0.1);
    border-radius: var(--apple-radius);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.card-header {
    background-color: var(--apple-off-white);
    border-bottom: 1px solid rgba(151, 151, 151, 0.1);
    padding: 1rem 1.5rem;
}

.card-body {
    padding: 1.5rem;
}

/* Form label styling */
.form-label-apple {
    font-weight: 600;
    color: var(--apple-black);
    margin-bottom: 0.5rem;
}

/* Badge styling */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: var(--apple-radius-small);
}

/* Table styling */
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

/* Mobile responsive */
@media (max-width: 768px) {
    .display-3 {
        font-size: 1.75rem;
    }
    
    .card-body {
        padding: 1rem;
    }
}
</style>

<?php
// Helper functions
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'approved': return 'bg-success';
        case 'pending': return 'bg-warning';
        case 'rejected': return 'bg-danger';
        case 'suspended': return 'bg-secondary';
        default: return 'bg-secondary';
    }
}

function getCustomerTypeBadgeClass($type) {
    switch ($type) {
        case 'individual': return 'bg-info';
        case 'corporate': return 'bg-primary';
        case 'government': return 'bg-success';
        default: return 'bg-secondary';
    }
}

function getPriorityBadgeClass($priority) {
    switch ($priority) {
        case 'critical': return 'bg-danger';
        case 'high': return 'bg-warning';
        case 'medium': return 'bg-info';
        case 'normal': return 'bg-secondary';
        default: return 'bg-secondary';
    }
}

$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
