<?php
// Capture the content
ob_start();
?>

<!-- Admin Customers Management -->
<section class="py-apple-6">
    <div class="container-xl">
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div>
                        <h1 class="display-3 mb-2">Customer Management</h1>
                        <p class="text-muted mb-0">Manage registered customers and their information</p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <a href="<?= Config::getAppUrl() ?>/admin/customers/create" class="btn btn-apple-primary">
                            <i class="fas fa-plus me-2"></i>Add New Customer
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card-apple-glass text-center">
                    <div class="card-body py-3">
                        <h4 class="fw-light text-apple-blue mb-1"><?= $stats['total_customers'] ?></h4>
                        <small class="text-muted">Total Customers</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card-apple-glass text-center">
                    <div class="card-body py-3">
                        <h4 class="fw-light text-success mb-1"><?= $stats['active_customers'] ?></h4>
                        <small class="text-muted">Active</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card-apple-glass text-center">
                    <div class="card-body py-3">
                        <h4 class="fw-light text-warning mb-1"><?= $stats['pending_verification'] ?></h4>
                        <small class="text-muted">Pending Verification</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card-apple-glass text-center">
                    <div class="card-body py-3">
                        <h4 class="fw-light text-info mb-1"><?= $stats['new_this_month'] ?></h4>
                        <small class="text-muted">New This Month</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters Card -->
        <div class="card-apple-glass mb-4">
            <div class="card-body">
                <form id="filterForm" method="GET" action="<?= Config::getAppUrl() ?>/admin/customers">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label for="status" class="form-label-apple small">Status</label>
                            <select class="form-control form-control-apple" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="active" <?= isset($filters['status']) && $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= isset($filters['status']) && $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="pending" <?= isset($filters['status']) && $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending Verification</option>
                                <option value="suspended" <?= isset($filters['status']) && $filters['status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                <option value="deleted" <?= isset($filters['status']) && $filters['status'] === 'deleted' ? 'selected' : '' ?>>Deleted</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="customer_type" class="form-label-apple small">Customer Type</label>
                            <select class="form-control form-control-apple" id="customer_type" name="customer_type">
                                <option value="">All Types</option>
                                <option value="individual" <?= isset($filters['customer_type']) && $filters['customer_type'] === 'individual' ? 'selected' : '' ?>>Individual</option>
                                <option value="corporate" <?= isset($filters['customer_type']) && $filters['customer_type'] === 'corporate' ? 'selected' : '' ?>>Corporate</option>
                                <option value="government" <?= isset($filters['customer_type']) && $filters['customer_type'] === 'government' ? 'selected' : '' ?>>Government</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="region" class="form-label-apple small">Region</label>
                            <select class="form-control form-control-apple" id="region" name="region">
                                <option value="">All Regions</option>
                                <?php foreach ($regions as $region): ?>
                                    <option value="<?= $region['id'] ?>" <?= isset($filters['region']) && $filters['region'] == $region['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($region['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 d-flex gap-2">
                            <button type="submit" class="btn btn-apple-primary flex-grow-1">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="<?= Config::getAppUrl() ?>/admin/customers" class="btn btn-apple-glass flex-grow-1">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Customers Table -->
        <div class="card-apple">
            <div class="card-body">
                <?php if (!empty($customers) && is_array($customers)): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            Showing <?= count($customers) ?> of <?= $total_customers ?? 0 ?> customers
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-apple-glass btn-sm" onclick="exportCustomers('pdf')">
                                <i class="fas fa-file-pdf me-1"></i>PDF
                            </button>
                            <button class="btn btn-apple-glass btn-sm" onclick="exportCustomers('excel')">
                                <i class="fas fa-file-excel me-1"></i>Excel
                            </button>
                            <button class="btn btn-apple-glass btn-sm" onclick="bulkEmailModal()">
                                <i class="fas fa-envelope me-1"></i>Bulk Email
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="customersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>Customer ID</th>
                                    <th>Name/Company</th>
                                    <th>Contact</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Tickets</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input customer-checkbox" value="<?= htmlspecialchars($customer['customer_id'] ?? '') ?>">
                                        </td>
                                        <td>
                                            <code class="text-apple-blue"><?= htmlspecialchars($customer['customer_id'] ?? 'N/A') ?></code>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-<?= getStatusColor($customer['status'] ?? 'pending') ?> rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                    <i class="fas fa-building"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-medium"><?= htmlspecialchars($customer['name'] ?? 'N/A') ?></div>
                                                    <?php if (!empty($customer['company_name'])): ?>
                                                        <small class="text-muted"><?= htmlspecialchars($customer['company_name']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-medium"><?= htmlspecialchars($customer['email'] ?? 'N/A') ?></div>
                                                <?php if (!empty($customer['mobile'])): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($customer['mobile']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge <?= getCustomerTypeBadgeClass($customer['customer_type'] ?? 'individual') ?>">
                                                <?= ucfirst($customer['customer_type'] ?? 'individual') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= getStatusBadgeClass($customer['status'] ?? 'pending') ?>">
                                                <?= ucfirst($customer['status'] ?? 'pending') ?>
                                            </span>
                                            <?php if (($customer['status'] ?? '') === 'pending'): ?>
                                                <div class="mt-1">
                                                    <small class="text-warning">
                                                        <i class="fas fa-clock"></i>
                                                        Verification pending
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <span class="fw-medium"><?= $customer['total_tickets'] ?? 0 ?></span>
                                                <?php if (($customer['open_tickets'] ?? 0) > 0): ?>
                                                    <div class="mt-1">
                                                        <small class="text-warning">
                                                            <?= $customer['open_tickets'] ?> open
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($customer['created_at'])): ?>
                                                <div><?= date('M d, Y', strtotime($customer['created_at'])) ?></div>
                                                <small class="text-muted"><?= date('H:i', strtotime($customer['created_at'])) ?></small>
                                            <?php else: ?>
                                                <div>N/A</div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-apple-glass btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="<?= Config::getAppUrl() ?>/admin/customers/<?= $customer['customer_id'] ?>">
                                                            <i class="fas fa-eye me-2"></i>View Details
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="<?= Config::getAppUrl() ?>/admin/customers/<?= $customer['customer_id'] ?>/edit">
                                                            <i class="fas fa-edit me-2"></i>Edit Customer
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="<?= Config::getAppUrl() ?>/admin/tickets?customer_id=<?= $customer['customer_id'] ?>">
                                                            <i class="fas fa-ticket-alt me-2"></i>View Tickets
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <?php if ($customer['status'] === 'pending'): ?>
                                                        <li>
                                                            <button class="dropdown-item text-success" onclick="verifyCustomer('<?= $customer['customer_id'] ?>')">
                                                                <i class="fas fa-check-circle me-2"></i>Approve Customer
                                                            </button>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if ($customer['status'] === 'approved'): ?>
                                                        <li>
                                                            <button class="dropdown-item text-warning" onclick="confirmStatusChange('<?= $customer['customer_id'] ?>', 'suspended')">
                                                                <i class="fas fa-pause-circle me-2"></i>Suspend Customer
                                                            </button>
                                                        </li>
                                                    <?php elseif ($customer['status'] === 'suspended'): ?>
                                                        <li>
                                                            <button class="dropdown-item text-success" onclick="confirmStatusChange('<?= $customer['customer_id'] ?>', 'approved')">
                                                                <i class="fas fa-play-circle me-2"></i>Reactivate Customer
                                                            </button>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <button class="dropdown-item" onclick="sendEmail('<?= $customer['customer_id'] ?>')">
                                                            <i class="fas fa-envelope me-2"></i>Send Email
                                                        </button>
                                                    </li>
                                                    <?php if ($user['role'] === 'superadmin' && $customer['status'] !== 'deleted'): ?>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button class="dropdown-item text-danger" onclick="confirmCustomerDeletion('<?= $customer['customer_id'] ?>')">
                                                                <i class="fas fa-trash me-2"></i>Delete Customer
                                                            </button>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if (($total_pages ?? 0) > 1): ?>
                        <nav aria-label="Customers pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if (($current_page ?? 1) > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= ($current_page ?? 1) - 1 ?>&<?= http_build_query(array_filter($filters ?? [])) ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, ($current_page ?? 1) - 2); $i <= min($total_pages ?? 1, ($current_page ?? 1) + 2); $i++): ?>
                                    <li class="page-item <?= $i === ($current_page ?? 1) ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(array_filter($filters ?? [])) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if (($current_page ?? 1) < ($total_pages ?? 1)): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= ($current_page ?? 1) + 1 ?>&<?= http_build_query(array_filter($filters ?? [])) ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <i class="fas fa-building text-muted mb-3" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mb-3">No Customers Found</h4>
                        
                        <?php if (!empty(array_filter($filters ?? []))): ?>
                            <p class="text-muted mb-4">No customers match your current filters. Try adjusting your search criteria.</p>
                            <a href="<?= Config::getAppUrl() ?>/admin/customers" class="btn btn-apple-glass me-2">
                                <i class="fas fa-times me-1"></i>Clear Filters
                            </a>
                        <?php else: ?>
                            <p class="text-muted mb-4">There are no customers in the system. Start by adding a new customer.</p>
                        <?php endif; ?>
                        
                        <a href="<?= Config::getAppUrl() ?>/admin/customers/create" class="btn btn-apple-primary">
                            <i class="fas fa-plus me-2"></i>Add New Customer
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
                            Customer Status Guide
                        </h6>
                        <div class="d-flex flex-wrap gap-3 mt-2">
                            <small><span class="badge bg-success me-1">Active</span> Verified and can use services</small>
                            <small><span class="badge bg-warning me-1">Pending</span> Awaiting verification</small>
                            <small><span class="badge bg-danger me-1">Suspended</span> Account temporarily disabled</small>
                            <small><span class="badge bg-secondary me-1">Inactive</span> Account deactivated</small>
                            <small><span class="badge bg-dark me-1">Deleted</span> Permanently deactivated</small>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                        <a href="<?= Config::getAppUrl() ?>/help/admin-guide#customer-management" class="btn btn-apple-glass btn-sm">
                            <i class="fas fa-question-circle me-1"></i>More Help
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bulk Email Modal -->
<div class="modal fade" id="bulkEmailModal" tabindex="-1" aria-labelledby="bulkEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkEmailModalLabel">
                    <i class="fas fa-envelope me-2"></i>
                    Send Bulk Email
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="bulkEmailForm">
                    <div class="mb-3">
                        <label for="emailSubject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="emailSubject" name="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="emailMessage" class="form-label">Message</label>
                        <textarea class="form-control" id="emailMessage" name="message" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sendToSelected">
                            <label class="form-check-label" for="sendToSelected">
                                Send only to selected customers (<span id="selectedCount">0</span> selected)
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-apple-glass" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-apple-primary" onclick="sendBulkEmail()">
                    <i class="fas fa-paper-plane me-2"></i>Send Email
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable if table exists
    if ($('#customersTable').length > 0) {
        $('#customersTable').DataTable({
            responsive: true,
            dom: '<"top"lf>rt<"bottom"ip><"clear">',
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search customers..."
            },
            columnDefs: [
                { orderable: false, targets: [0, 8] } // Disable sorting for checkbox and actions columns
            ]
        });
    }
    
    // Auto-submit filter form on change
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        document.querySelectorAll('#filterForm select').forEach(field => {
            field.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    }
    
    // Select All functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.customer-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }
    
    // Update selected count when individual checkboxes change
    const customerCheckboxes = document.querySelectorAll('.customer-checkbox');
    if (customerCheckboxes.length > 0) {
        customerCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });
    }
    
    // Only call if needed
    if (document.getElementById('selectedCount')) {
        updateSelectedCount();
    }
});

function updateSelectedCount() {
    const selected = document.querySelectorAll('.customer-checkbox:checked').length;
    const selectedCountElem = document.getElementById('selectedCount');
    if (selectedCountElem) {
        selectedCountElem.textContent = selected;
    }
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
            // Send AJAX request to change customer status
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

function sendEmail(customerId) {
    // Implementation for sending individual email
    window.location.href = `${APP_URL}/admin/customers/${customerId}/email`;
}

function bulkEmailModal() {
    new bootstrap.Modal(document.getElementById('bulkEmailModal')).show();
}

function sendBulkEmail() {
    const subject = document.getElementById('emailSubject').value;
    const message = document.getElementById('emailMessage').value;
    const sendToSelected = document.getElementById('sendToSelected').checked;
    
    if (!subject || !message) {
        Swal.fire('Error!', 'Please fill in all required fields.', 'error');
        return;
    }
    
    let customerIds = [];
    if (sendToSelected) {
        customerIds = Array.from(document.querySelectorAll('.customer-checkbox:checked')).map(cb => cb.value);
        if (customerIds.length === 0) {
            Swal.fire('Error!', 'Please select at least one customer.', 'error');
            return;
        }
    }
    
    fetch(`${APP_URL}/admin/customers/bulk-email`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: JSON.stringify({
            subject: subject,
            message: message,
            customer_ids: sendToSelected ? customerIds : null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Sent!', 'Bulk email has been sent successfully.', 'success');
            bootstrap.Modal.getInstance(document.getElementById('bulkEmailModal')).hide();
        } else {
            Swal.fire('Error!', data.message, 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error!', 'Failed to send bulk email.', 'error');
    });
}

function exportCustomers(format) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('export', format);
    window.open(currentUrl.toString(), '_blank');
}

function confirmCustomerDeletion(customerId) {
    Swal.fire({
        title: 'Delete Customer Account',
        text: 'This action will permanently deactivate the customer account. This cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, delete account',
        cancelButtonText: 'Cancel',
        input: 'textarea',
        inputLabel: 'Reason for deletion (required)',
        inputPlaceholder: 'Please provide a reason for deleting this customer account...',
        inputValidator: (value) => {
            if (!value || value.trim().length < 10) {
                return 'Please provide a detailed reason (minimum 10 characters)';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Send AJAX request to delete customer
            fetch(`${APP_URL}/admin/customers/${customerId}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify({
                    reason: result.value,
                    confirm_deletion: true
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Deleted!',
                        'Customer account has been permanently deactivated.',
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
                Swal.fire('Error!', 'Failed to delete customer account.', 'error');
            });
        }
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

/* DataTables customization */
.dataTables_wrapper .dataTables_length, 
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 1rem;
}

.dataTables_wrapper .dataTables_length select {
    padding: 0.375rem 1.75rem 0.375rem 0.75rem;
    border-radius: var(--apple-radius-small);
    border: 1px solid rgba(0,0,0,0.1);
}

.dataTables_wrapper .dataTables_filter input {
    border-radius: var(--apple-radius-small);
    border: 1px solid rgba(0,0,0,0.1);
    padding: 0.375rem 0.75rem;
    margin-left: 0.5rem;
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

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .display-3 {
        font-size: 1.75rem;
    }
    
    .table-responsive {
        font-size: 0.8rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
}
</style>

<?php
// Helper functions
function getStatusColor($status) {
    switch ($status) {
        case 'active': return 'success';
        case 'approved': return 'success';
        case 'pending': return 'warning';
        case 'suspended': return 'danger';
        case 'inactive': return 'secondary';
        case 'deleted': return 'dark';
        case 'rejected': return 'danger';
        default: return 'secondary';
    }
}

function getStatusBadgeClass($status) {
    switch ($status) {
        case 'active': return 'bg-success';
        case 'approved': return 'bg-success';
        case 'pending': return 'bg-warning';
        case 'suspended': return 'bg-danger';
        case 'inactive': return 'bg-secondary';
        case 'deleted': return 'bg-dark';
        case 'rejected': return 'bg-danger';
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

$additional_css = [
    Config::getAppUrl() . '/libs/datatables/dataTables.bootstrap5.min.css'
];

$additional_js = [
    Config::getAppUrl() . '/libs/datatables/jquery.dataTables.min.js',
    Config::getAppUrl() . '/libs/datatables/dataTables.bootstrap5.min.js'
];

$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
