<?php
// Capture the content
ob_start();
?>

<!-- Edit User -->
<section class="py-apple-6">
    <div class="container-xl">
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div>
                        <h1 class="display-3 mb-2">Edit User</h1>
                        <p class="text-muted mb-0">
                            <i class="fas fa-user me-2"></i><?= htmlspecialchars($user_to_edit['name']) ?>
                            <span class="mx-2">•</span>
                            <i class="fas fa-id-badge me-2"></i><?= htmlspecialchars($user_to_edit['id']) ?>
                        </p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <a href="<?= Config::getAppUrl() ?>/admin/users" class="btn btn-apple-glass">
                            <i class="fas fa-arrow-left me-2"></i>Back to Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Edit User Form -->
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card-apple">
                    <div class="card-body">
                        <form id="editUserForm" action="<?= Config::getAppUrl() ?>/admin/users/<?= $user_to_edit['id'] ?>/update" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="user_id" value="<?= $user_to_edit['id'] ?>">
                            
                            <!-- Basic Information -->
                            <h4 class="mb-4">Basic Information</h4>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label-apple">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-apple" id="name" name="name" value="<?= htmlspecialchars($user_to_edit['name']) ?>" required>
                                    <div class="invalid-feedback">Please provide a name.</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label-apple">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control form-control-apple" id="email" name="email" value="<?= htmlspecialchars($user_to_edit['email']) ?>" required>
                                    <div class="invalid-feedback">Please provide a valid email address.</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="phone" class="form-label-apple">Phone Number</label>
                                    <input type="tel" class="form-control form-control-apple" id="mobile" name="mobile" value="<?= htmlspecialchars($user_to_edit['mobile'] ?? '') ?>" placeholder="Optional">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="role" class="form-label-apple">User Role <span class="text-danger">*</span></label>
                                    <select class="form-select form-control-apple" id="role" name="role" required onchange="toggleRoleFields()">
                                        <option value="">Select Role</option>
                                        <?php foreach ($roles as $role => $label): ?>
                                            <option value="<?= $role ?>" <?= $user_to_edit['role'] === $role ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a role.</div>
                                </div>
                            </div>
                            
                            <!-- Role-specific Fields (Controller) -->
                            <div id="controllerFields" class="role-specific-fields" style="display: <?= in_array($user_to_edit['role'], ['controller', 'controller_nodal']) ? 'block' : 'none' ?>;">
                                <h4 class="mb-4">Controller Details</h4>
                                
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="division" class="form-label-apple">Division <span class="text-danger">*</span></label>
                                        <select class="form-select form-control-apple" id="division" name="division">
                                            <option value="">Select Division</option>
                                            <?php foreach ($divisions as $division): ?>
                                                <option value="<?= $division['division'] ?>" <?= ($user_to_edit['division'] ?? '') === $division['division'] ? 'selected' : '' ?>><?= $division['division'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Please select a division.</div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="designation" class="form-label-apple">Designation <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-apple" id="department" name="department" value="<?= htmlspecialchars($user_to_edit['department'] ?? '') ?>">
                                        <div class="invalid-feedback">Please provide a designation.</div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="employee_id" class="form-label-apple">Employee ID <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-apple" id="employee_id" name="employee_id" value="<?= htmlspecialchars($user_to_edit['login_id'] ?? '') ?>">
                                        <div class="invalid-feedback">Please provide an employee ID.</div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="regions" class="form-label-apple">Regions (Select multiple if applicable)</label>
                                        <select class="form-select form-control-apple" id="regions" name="regions[]" multiple>
                                            <?php foreach ($regions as $region): ?>
                                                <option value="<?= $region['id'] ?>" <?= in_array($region['id'], $user_regions ?? []) ? 'selected' : '' ?>><?= $region['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Account Settings -->
                            <h4 class="mb-4">Account Settings</h4>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label-apple">Account Status</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="statusActive" value="active" <?= $user_to_edit['status'] === 'active' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="statusActive">Active</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="statusInactive" value="inactive" <?= $user_to_edit['status'] === 'inactive' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="statusInactive">Inactive</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label-apple">Last Login</label>
                                    <div>
                                        <?php if (!empty($user_to_edit['last_login'])): ?>
                                            <span class="text-muted"><?= date('M d, Y H:i', strtotime($user_to_edit['last_login'])) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Never logged in</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Password Change (Optional) -->
                            <h4 class="mb-4">Change Password (Optional)</h4>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="password" class="form-label-apple">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-apple" id="new_password" name="new_password" minlength="8">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Leave blank to keep current password.</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="password_confirm" class="form-label-apple">Confirm New Password</label>
                                    <input type="password" class="form-control form-control-apple" id="password_confirmation" name="password_confirmation">
                                    <div class="form-text">Must match new password.</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="forcePasswordChange" name="force_password_change" <?= !empty($user_to_edit['force_password_change']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="forcePasswordChange">
                                            Force password change on next login
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="sendPasswordEmail" name="send_password_email">
                                        <label class="form-check-label" for="sendPasswordEmail">
                                            Send email notification about password change
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="d-flex justify-content-end mt-4">
                                <button type="button" class="btn btn-apple-glass me-2" onclick="location.href='<?= Config::getAppUrl() ?>/admin/users'">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-apple-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-12 col-lg-4 mt-4 mt-lg-0">
                <!-- User Summary Card -->
                <div class="card-apple mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-<?= $user_to_edit['status'] === 'active' ? 'success' : 'secondary' ?> rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 64px; height: 64px;">
                                <i class="fas fa-user text-white fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-1"><?= htmlspecialchars($user_to_edit['name']) ?></h5>
                                <span class="badge <?= getRoleBadgeClass($user_to_edit['role']) ?> mb-2">
                                    <?= ucfirst(str_replace('_', ' ', $user_to_edit['role'])) ?>
                                </span>
                                <div class="small text-muted"><?= htmlspecialchars($user_to_edit['email']) ?></div>
                            </div>
                        </div>
                        
                        <div class="user-stats mb-3">
                            <div class="row g-0 text-center">
                                <div class="col-4 border-end">
                                    <h6 class="mb-1"><?= $user_stats['tickets_handled'] ?? 0 ?></h6>
                                    <small class="text-muted">Tickets</small>
                                </div>
                                <div class="col-4 border-end">
                                    <h6 class="mb-1"><?= $user_stats['logins'] ?? 0 ?></h6>
                                    <small class="text-muted">Logins</small>
                                </div>
                                <div class="col-4">
                                    <h6 class="mb-1"><?= $user_stats['days_active'] ?? 0 ?></h6>
                                    <small class="text-muted">Days</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-apple-glass" onclick="sendPasswordReset('<?= $user_to_edit['id'] ?>')">
                                <i class="fas fa-key me-2"></i>Send Password Reset
                            </button>
                            <button type="button" class="btn btn-apple-glass" data-bs-toggle="modal" data-bs-target="#activityLogModal">
                                <i class="fas fa-history me-2"></i>View Activity Log
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- System Access Info -->
                <div class="card-apple-glass mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-shield-alt text-apple-blue me-2"></i>
                            System Access
                        </h5>
                        
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                                <span>Account Created</span>
                                <span><?= date('M d, Y', strtotime($user_to_edit['created_at'])) ?></span>
                            </li>
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                                <span>Last Password Change</span>
                                <span><?= !empty($user_to_edit['password_changed_at']) ? date('M d, Y', strtotime($user_to_edit['password_changed_at'])) : 'Never' ?></span>
                            </li>
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                                <span>2FA Status</span>
                                <span class="badge <?= ($user_to_edit['two_factor_enabled'] ?? false) ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ($user_to_edit['two_factor_enabled'] ?? false) ? 'Enabled' : 'Disabled' ?>
                                </span>
                            </li>
                            <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0">
                                <span>Account Lockouts</span>
                                <span><?= $user_stats['lockouts'] ?? 0 ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Need Help? -->
                <div class="card-apple-glass">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-question-circle text-apple-blue me-2"></i>
                            Need Help?
                        </h5>
                        <p class="card-text small">Check the admin guide for detailed information on user management.</p>
                        
                        <a href="<?= Config::getAppUrl() ?>/help/admin-guide#user-management" class="btn btn-sm btn-apple-glass w-100">
                            <i class="fas fa-book me-2"></i>View Admin Guide
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Activity Log Modal -->
<div class="modal fade" id="activityLogModal" tabindex="-1" aria-labelledby="activityLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityLogModalLabel">
                    <i class="fas fa-history me-2"></i>
                    User Activity Log
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($activity_log)): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Activity</th>
                                    <th>Details</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activity_log as $log): ?>
                                    <tr>
                                        <td><?= date('M d, Y H:i:s', strtotime($log['timestamp'])) ?></td>
                                        <td><?= htmlspecialchars($log['activity']) ?></td>
                                        <td><?= htmlspecialchars($log['details']) ?></td>
                                        <td><?= htmlspecialchars($log['ip_address']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-history text-muted mb-3" style="font-size: 3rem;"></i>
                        <p class="text-muted">No activity log found for this user.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-apple-glass" data-bs-dismiss="modal">Close</button>
                <?php if (!empty($activity_log)): ?>
                    <button type="button" class="btn btn-apple-primary" onclick="exportActivityLog('<?= $user_to_edit['id'] ?>')">
                        <i class="fas fa-download me-2"></i>Export Log
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('editUserForm');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        // Check if passwords match when a new password is entered
        const password = document.getElementById('new_password');
        const confirmPassword = document.getElementById('password_confirmation');
        
        if (password.value !== '' && password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Passwords don't match");
            event.preventDefault();
            event.stopPropagation();
        } else {
            confirmPassword.setCustomValidity("");
        }
        
        form.classList.add('was-validated');
    });
    
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = document.getElementById('new_password');
        const icon = this.querySelector('i');
        
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
    
    // Initialize the role fields on page load
    toggleRoleFields();
});

// Toggle role-specific fields based on selected role
function toggleRoleFields() {
    const role = document.getElementById('role').value;
    const controllerFields = document.getElementById('controllerFields');
    
    // Hide all role-specific fields first
    document.querySelectorAll('.role-specific-fields').forEach(el => {
        el.style.display = 'none';
    });
    
    // Show appropriate fields based on role
    if (role === 'controller' || role === 'controller_nodal') {
        controllerFields.style.display = 'block';
        
        // Make controller-specific fields required
        document.getElementById('division').required = true;
        document.getElementById('department').required = true;
        document.getElementById('employee_id').required = true;
    } else {
        // Remove required attribute from controller-specific fields
        document.getElementById('division').required = false;
        document.getElementById('department').required = false;
        document.getElementById('employee_id').required = false;
    }
}

function sendPasswordReset(userId) {
    Swal.fire({
        title: 'Send Password Reset',
        text: 'Send a password reset link to this user?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        confirmButtonText: 'Yes, send reset link',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
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
                    Swal.fire('Sent!', 'Password reset link has been sent.', 'success');
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Failed to send password reset link.', 'error');
            });
        }
    });
}

function exportActivityLog(userId) {
    window.open(`${APP_URL}/admin/users/${userId}/activity-log/export`, '_blank');
}
</script>

<style>
/* Form styling */
.form-control-apple:focus,
.form-select:focus {
    border-color: var(--apple-blue);
    box-shadow: 0 0 0 0.2rem rgba(0, 113, 227, 0.25);
}

.form-label-apple {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

/* User stats */
.user-stats {
    background-color: var(--apple-off-white);
    border-radius: var(--apple-radius-medium);
    padding: 1rem 0;
    margin: 1.5rem 0;
}

/* List group custom styling */
.list-group-item {
    border-color: rgba(0,0,0,0.05);
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .display-3 {
        font-size: 1.75rem;
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

$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
