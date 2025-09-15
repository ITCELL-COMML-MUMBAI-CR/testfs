<?php
// Capture the content
ob_start();
?>

<!-- Admin Users Management -->
<section class="py-apple-6">
    <div class="container-xl">
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div>
                        <h1 class="display-3 mb-2">User Management</h1>
                        <p class="text-muted mb-0">Manage system users, roles and permissions</p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <a href="<?= Config::getAppUrl() ?>/admin/users/create" class="btn btn-apple-primary">
                            <i class="fas fa-plus me-2"></i>Add New User
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters Card -->
        <div class="card-apple-glass mb-4">
            <div class="card-body">
                <form id="filterForm" method="GET" action="<?= Config::getAppUrl() ?>/admin/users">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="role" class="form-label-apple small">Role</label>
                            <select class="form-control form-control-apple" id="role" name="role">
                                <option value="">All Roles</option>
                                <?php foreach ($roles as $role => $label): ?>
                                    <option value="<?= $role ?>" <?= isset($filters['role']) && $filters['role'] === $role ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="status" class="form-label-apple small">Status</label>
                            <select class="form-control form-control-apple" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="active" <?= isset($filters['status']) && $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= isset($filters['status']) && $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 d-flex gap-2">
                            <button type="submit" class="btn btn-apple-primary flex-grow-1">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="<?= Config::getAppUrl() ?>/admin/users" class="btn btn-apple-glass flex-grow-1">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="card-apple">
            <div class="card-body">
                <?php if (!empty($users)): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            Showing <?= count($users) ?> of <?= $total_users ?> users
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-apple-glass btn-sm" onclick="exportUsers('pdf')">
                                <i class="fas fa-file-pdf me-1"></i>PDF
                            </button>
                            <button class="btn btn-apple-glass btn-sm" onclick="exportUsers('excel')">
                                <i class="fas fa-file-excel me-1"></i>Excel
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>User ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <code class="text-apple-blue"><?= htmlspecialchars($user['id']) ?></code>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?> rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-medium"><?= htmlspecialchars($user['name']) ?></div>
                                                    <?php if ($user['role'] === 'controller'): ?>
                                                        <small class="text-muted"><?= htmlspecialchars($user['division'] ?? '') ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($user['email']) ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= getRoleBadgeClass($user['role']) ?>">
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $user['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= ucfirst($user['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($user['last_login'])): ?>
                                                <div><?= date('M d, Y', strtotime($user['last_login'])) ?></div>
                                                <small class="text-muted"><?= date('H:i', strtotime($user['last_login'])) ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Never</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div><?= date('M d, Y', strtotime($user['created_at'])) ?></div>
                                        </td>
                                        <td>
                                            <div class="dropdown actions-dropdown-container" data-user-id="<?= $user['id'] ?>">
                                                <button class="btn btn-apple-glass btn-sm dropdown-toggle actions-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-action="view">
                                                            <i class="fas fa-eye me-2"></i>View Details
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-action="edit">
                                                            <i class="fas fa-edit me-2"></i>Edit User
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-action="reset-password">
                                                            <i class="fas fa-key me-2"></i>Reset Password
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <?php if ($user['status'] === 'active'): ?>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" data-action="deactivate">
                                                                <i class="fas fa-ban me-2"></i>Deactivate User
                                                            </a>
                                                        </li>
                                                    <?php else: ?>
                                                        <li>
                                                            <a class="dropdown-item text-success" href="#" data-action="activate">
                                                                <i class="fas fa-check-circle me-2"></i>Activate User
                                                            </a>
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
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Users pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $current_page - 1 ?>&<?= http_build_query(array_filter($filters)) ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(array_filter($filters)) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $current_page + 1 ?>&<?= http_build_query(array_filter($filters)) ?>">
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
                        <i class="fas fa-users text-muted mb-3" style="font-size: 4rem;"></i>
                        <h4 class="text-muted mb-3">No Users Found</h4>
                        
                        <?php if (!empty(array_filter($filters))): ?>
                            <p class="text-muted mb-4">No users match your current filters. Try adjusting your search criteria.</p>
                            <a href="<?= Config::getAppUrl() ?>/admin/users" class="btn btn-apple-glass me-2">
                                <i class="fas fa-times me-1"></i>Clear Filters
                            </a>
                        <?php else: ?>
                            <p class="text-muted mb-4">There are no users in the system. Start by adding a new user.</p>
                        <?php endif; ?>
                        
                        <a href="<?= Config::getAppUrl() ?>/admin/users/create" class="btn btn-apple-primary">
                            <i class="fas fa-plus me-2"></i>Add New User
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Role Legend -->
        <div class="card-apple-glass mt-4">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-1">
                            <i class="fas fa-info-circle text-apple-blue me-2"></i>
                            User Roles
                        </h6>
                        <div class="d-flex flex-wrap gap-3 mt-2">
                            <small><span class="badge bg-danger me-1">Admin</span> Full system access</small>
                            <small><span class="badge bg-primary me-1">Controller</span> Support ticket management</small>
                            <small><span class="badge bg-warning text-dark me-1">Controller Nodal</span> Nodal officers</small>
                            <small><span class="badge bg-info me-1">Customer Support</span> Customer assistance only</small>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                        <a href="<?= Config::getAppUrl() ?>/admin/roles" class="btn btn-apple-glass btn-sm">
                            <i class="fas fa-user-shield me-1"></i>Manage Roles
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#usersTable').DataTable({
        responsive: true,
        dom: '<"top"lf>rt<"bottom"ip><"clear">',
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search users..."
        }
    });
    
    // Auto-submit filter form on change
    document.querySelectorAll('#filterForm select').forEach(field => {
        field.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    });
});

function confirmStatusChange(userId, newStatus) {
    const actionText = newStatus === 'active' ? 'activate' : 'deactivate';
    const actionIcon = newStatus === 'active' ? 'check-circle' : 'ban';
    const confirmBtnColor = newStatus === 'active' ? '#28a745' : '#dc3545';
    
    Swal.fire({
        title: `${newStatus === 'active' ? 'Activate' : 'Deactivate'} User`,
        text: `Are you sure you want to ${actionText} this user?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: confirmBtnColor,
        confirmButtonText: `Yes, ${actionText} user`,
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Send AJAX request to change user status
            fetch(`${APP_URL}/admin/users/${userId}/status`, {
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
                        `User has been ${newStatus === 'active' ? 'activated' : 'deactivated'}.`, 
                        'success'
                    );
                    // Refresh the page or update the table
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Failed to update user status.', 'error');
            });
        }
    });
}

function sendPasswordReset(userId) {
    Swal.fire({
        title: 'Reset Password',
        html: `
            <p>Generate a new random password for this user:</p>
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" id="showPasswordCheck" checked>
                <label class="form-check-label" for="showPasswordCheck">
                    Show password to copy
                </label>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        confirmButtonText: 'Yes, reset password',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const showPassword = document.getElementById('showPasswordCheck').checked;
            
            // Send AJAX request for password reset
            fetch(`${APP_URL}/admin/users/${userId}/reset-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (showPassword && data.new_password) {
                        Swal.fire({
                            title: 'Password Reset',
                            html: `
                                <p>The user's password has been reset to:</p>
                                <div class="alert alert-info mt-3">
                                    <code class="fs-5">${data.new_password}</code>
                                </div>
                                <p class="small mt-3">Make sure to provide this password to the user securely.</p>
                            `,
                            icon: 'success'
                        });
                    } else {
                        Swal.fire('Success', 'User password has been reset successfully.', 'success');
                    }
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Failed to reset password.', 'error');
            });
        }
    });
}

function exportUsers(format) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('export', format);
    window.open(currentUrl.toString(), '_blank');
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

.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.375rem 0.75rem;
    border-radius: var(--apple-radius-small);
    border: none !important;
    background: transparent !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: var(--apple-off-white) !important;
    color: var(--apple-blue) !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: var(--apple-blue) !important;
    color: white !important;
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
// Helper function to get role badge class
function getRoleBadgeClass($role) {
    switch ($role) {
        case 'admin':
        case 'superadmin':
            return 'bg-danger';
        case 'controller':
            return 'bg-primary';
        case 'controller_nodal':
            return 'bg-warning text-dark';
        case 'customer_support':
            return 'bg-info';
        default:
            return 'bg-secondary';
    }
}

$additional_css = [
    Config::getAppUrl() . '/libs/datatables/dataTables.bootstrap5.min.css',
    Config::getAppUrl() . '/assets/css/sweetalert-fixes.css'
];

$additional_js = [
    Config::getAppUrl() . '/libs/datatables/jquery.dataTables.min.js',
    Config::getAppUrl() . '/libs/datatables/dataTables.bootstrap5.min.js',
    Config::getAppUrl() . '/assets/js/admin-actions.js'
];

$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
