<?php
/**
 * Admin Profile View - SAMPARK
 * User profile management for admin users
 */

// Capture the content
ob_start();

// Set additional CSS for this view
$additional_css = [
    Config::getAppUrl() . '/assets/css/admin-views.css'
];

// Set page title
$page_title = 'Profile - SAMPARK';
?>

<div class="container-xl py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <div class="d-flex align-items-center">
                <div class="bg-apple-blue rounded-3 p-3 me-3">
                    <i class="fas fa-user-circle text-dark fa-lg"></i>
                </div>
                <div>
                    <h1 class="h3 mb-1 fw-semibold">My Profile</h1>
                    <p class="text-muted mb-0">Manage your account information and preferences</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Profile Content -->
        <div class="col-lg-8">
            <!-- Profile Information -->
            <div class="card card-apple mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>Profile Information
                    </h5>
                    <button class="btn btn-sm btn-apple-primary" onclick="enableEdit()">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </button>
                </div>
                <div class="card-body">
                    <form id="profileForm" onsubmit="updateProfile(event)">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-apple">Full Name *</label>
                                <input type="text" class="form-control-apple" name="name"
                                       value="<?= htmlspecialchars($user_details['name'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple">Email Address *</label>
                                <input type="email" class="form-control-apple" name="email"
                                       value="<?= htmlspecialchars($user_details['email'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple">Mobile Number</label>
                                <input type="tel" class="form-control-apple" name="mobile"
                                       value="<?= htmlspecialchars($user_details['mobile'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple">Role</label>
                                <input type="text" class="form-control-apple"
                                       value="<?= ucwords(str_replace('_', ' ', $user_details['role'] ?? '')) ?>" readonly>
                            </div>
                            <?php if (isset($user_details['department'])): ?>
                            <div class="col-md-6">
                                <label class="form-label-apple">Department</label>
                                <input type="text" class="form-control-apple" name="department"
                                       value="<?= htmlspecialchars($user_details['department'] ?? '') ?>" readonly>
                            </div>
                            <?php endif; ?>
                            <?php if (isset($user_details['division'])): ?>
                            <div class="col-md-6">
                                <label class="form-label-apple">Division</label>
                                <input type="text" class="form-control-apple" name="division"
                                       value="<?= htmlspecialchars($user_details['division'] ?? '') ?>" readonly>
                            </div>
                            <?php endif; ?>
                            <?php if (isset($user_details['zone'])): ?>
                            <div class="col-md-6">
                                <label class="form-label-apple">Zone</label>
                                <input type="text" class="form-control-apple" name="zone"
                                       value="<?= htmlspecialchars($user_details['zone'] ?? '') ?>" readonly>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="row mt-3 d-none" id="editButtons">
                            <div class="col">
                                <button type="submit" class="btn btn-apple-primary me-2">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                                <button type="button" class="btn btn-apple-secondary" onclick="cancelEdit()">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card card-apple">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-lock me-2"></i>Change Password
                    </h5>
                </div>
                <div class="card-body">
                    <form id="passwordForm" onsubmit="changePassword(event)">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label-apple">Current Password *</label>
                                <input type="password" class="form-control-apple" name="current_password" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple">New Password *</label>
                                <input type="password" class="form-control-apple" name="new_password"
                                       id="newPassword" required minlength="8">
                                <div class="form-text">Minimum 8 characters with uppercase, lowercase, number, and special character</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple">Confirm New Password *</label>
                                <input type="password" class="form-control-apple" name="confirm_password"
                                       id="confirmPassword" required>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col">
                                <button type="submit" class="btn btn-apple-primary">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Profile Summary -->
            <div class="card card-apple mb-4">
                <div class="card-body text-center">
                    <div class="bg-apple-blue rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                         style="width: 80px; height: 80px;">
                        <i class="fas fa-user-shield text-dark fa-2x"></i>
                    </div>
                    <h5 class="fw-semibold"><?= htmlspecialchars($user_details['name'] ?? 'Admin') ?></h5>
                    <div class="text-muted mb-3">
                        <span class="badge bg-<?= $user_role === 'superadmin' ? 'danger' : 'warning' ?> fs-6">
                            <?= ucwords(str_replace('_', ' ', $user_role)) ?>
                        </span>
                    </div>
                    <div class="row text-center">
                        <div class="col-12">
                            <div class="fw-semibold">Email</div>
                            <small class="text-muted"><?= htmlspecialchars($user_details['email'] ?? 'N/A') ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Status -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>Account Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Account Status</span>
                        <span class="badge bg-<?= ($user_details['status'] ?? 'active') === 'active' ? 'success' : 'danger' ?>">
                            <?= ucfirst($user_details['status'] ?? 'active') ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Last Login</span>
                        <small class="text-muted">
                            <?= isset($user_details['last_login']) ? date('M d, Y H:i', strtotime($user_details['last_login'])) : 'Never' ?>
                        </small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Member Since</span>
                        <small class="text-muted">
                            <?= isset($user_details['created_at']) ? date('M d, Y', strtotime($user_details['created_at'])) : 'N/A' ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card card-apple">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-lock me-2"></i>Security
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Password Last Changed</span>
                        <small class="text-muted">
                            <?= isset($user_details['password_changed_at']) ?
                                date('M d, Y', strtotime($user_details['password_changed_at'])) :
                                'More than 90 days ago' ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Profile management JavaScript
let isEditing = false;
let originalFormData = {};

function enableEdit() {
    isEditing = true;
    const form = document.getElementById('profileForm');
    const inputs = form.querySelectorAll('input[readonly]');
    const editButtons = document.getElementById('editButtons');

    // Store original data
    originalFormData = new FormData(form);

    // Make fields editable (except certain readonly fields)
    inputs.forEach(input => {
        if (!['role', 'department', 'division', 'zone'].includes(input.name)) {
            input.removeAttribute('readonly');
            input.classList.add('form-control-apple-editable');
        }
    });

    // Show save/cancel buttons
    editButtons.classList.remove('d-none');

    // Update edit button
    const editBtn = document.querySelector('[onclick="enableEdit()"]');
    editBtn.innerHTML = '<i class="fas fa-times me-2"></i>Cancel Edit';
    editBtn.onclick = cancelEdit;
    editBtn.classList.remove('btn-apple-primary');
    editBtn.classList.add('btn-apple-secondary');
}

function cancelEdit() {
    isEditing = false;
    const form = document.getElementById('profileForm');
    const inputs = form.querySelectorAll('input');
    const editButtons = document.getElementById('editButtons');

    // Restore original data
    for (let [key, value] of originalFormData.entries()) {
        const input = form.querySelector(`[name="${key}"]`);
        if (input) {
            input.value = value;
        }
    }

    // Make fields readonly again
    inputs.forEach(input => {
        if (!['role', 'department', 'division', 'zone'].includes(input.name)) {
            input.setAttribute('readonly', '');
            input.classList.remove('form-control-apple-editable');
        }
    });

    // Hide save/cancel buttons
    editButtons.classList.add('d-none');

    // Restore edit button
    const editBtn = document.querySelector('[onclick="cancelEdit()"]');
    editBtn.innerHTML = '<i class="fas fa-edit me-2"></i>Edit Profile';
    editBtn.onclick = enableEdit;
    editBtn.classList.add('btn-apple-primary');
    editBtn.classList.remove('btn-apple-secondary');
}

async function updateProfile(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    formData.append('csrf_token', CSRF_TOKEN);

    try {
        showLoading();
        const response = await fetch(`${APP_URL}/admin/profile`, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        hideLoading();

        if (result.success) {
            Swal.fire('Success', 'Profile updated successfully', 'success');
            cancelEdit(); // Exit edit mode
            setTimeout(() => location.reload(), 1500);
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
        Swal.fire('Error', 'Failed to update profile', 'error');
    }
}

async function changePassword(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const newPassword = formData.get('new_password');
    const confirmPassword = formData.get('confirm_password');

    // Client-side validation
    if (newPassword !== confirmPassword) {
        Swal.fire('Error', 'New passwords do not match', 'error');
        return;
    }

    if (!validatePassword(newPassword)) {
        Swal.fire('Error', 'Password must contain at least 8 characters with uppercase, lowercase, number, and special character', 'error');
        return;
    }

    formData.append('csrf_token', CSRF_TOKEN);

    try {
        showLoading();
        const response = await fetch(`${APP_URL}/admin/change-password`, {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        hideLoading();

        if (result.success) {
            Swal.fire('Success', 'Password changed successfully', 'success');
            document.getElementById('passwordForm').reset();
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
        Swal.fire('Error', 'Failed to change password', 'error');
    }
}

function validatePassword(password) {
    const minLength = 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /\d/.test(password);
    const hasNonalphas = /\W/.test(password);

    return password.length >= minLength && hasUpperCase && hasLowerCase && hasNumbers && hasNonalphas;
}

// Password confirmation validation
document.getElementById('confirmPassword').addEventListener('input', function() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = this.value;

    if (confirmPassword && newPassword !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
    }
});

// Password strength indicator
document.getElementById('newPassword').addEventListener('input', function() {
    const password = this.value;
    const isValid = validatePassword(password);

    if (password.length > 0) {
        if (isValid) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.add('is-invalid');
            this.classList.remove('is-valid');
        }
    } else {
        this.classList.remove('is-invalid', 'is-valid');
    }
});

// Utility functions
function showLoading() {
    document.getElementById('loadingOverlay')?.classList.remove('d-none');
}

function hideLoading() {
    document.getElementById('loadingOverlay')?.classList.add('d-none');
}

// Warn user before leaving if editing
window.addEventListener('beforeunload', function(e) {
    if (isEditing) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
    }
});
</script>

<style>
/* Profile page specific styles */
.form-control-apple-editable {
    background-color: rgba(var(--apple-primary-rgb), 0.05);
    border-color: var(--apple-primary);
}

.form-control-apple-editable:focus {
    box-shadow: 0 0 0 0.2rem rgba(var(--apple-primary-rgb), 0.25);
}

/* Form validation states */
.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.is-valid {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}

/* Notification preferences styling */
.form-check {
    padding: 0.75rem;
    background: rgba(var(--bs-light), 0.3);
    border-radius: var(--apple-radius-small);
    margin-bottom: 0.5rem;
}

.form-check:hover {
    background: rgba(var(--bs-light), 0.6);
}

.form-check-input:checked {
    background-color: var(--apple-primary);
    border-color: var(--apple-primary);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem 0.75rem;
    }

    .row.g-3 {
        --bs-gutter-x: 1rem;
    }
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
