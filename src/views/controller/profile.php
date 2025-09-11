<?php
/**
 * Controller Profile View - SAMPARK
 * User profile management for controller users
 */

// Capture the content
ob_start();

// Set additional CSS for this view
$additional_css = [
    Config::getAppUrl() . '/assets/css/controller-views.css'
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
                                <label class="form-label-apple">Employee ID</label>
                                <input type="text" class="form-control-apple" name="employee_id" 
                                       value="<?= htmlspecialchars($user_details['employee_id'] ?? '') ?>" readonly>
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
                                <label class="form-label-apple">Department</label>
                                <input type="text" class="form-control-apple" name="department" 
                                       value="<?= htmlspecialchars($user_details['department'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple">Division</label>
                                <input type="text" class="form-control-apple" name="division" 
                                       value="<?= htmlspecialchars($user_details['division'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple">Role</label>
                                <input type="text" class="form-control-apple" 
                                       value="<?= ucwords(str_replace('_', ' ', $user_details['role'] ?? '')) ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple">Zone</label>
                                <input type="text" class="form-control-apple" name="zone" 
                                       value="<?= htmlspecialchars($user_details['zone'] ?? '') ?>" readonly>
                            </div>
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
            <div class="card card-apple mb-4">
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

            <!-- Notification Preferences -->
            <div class="card card-apple">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bell me-2"></i>Notification Preferences
                    </h5>
                </div>
                <div class="card-body">
                    <form id="notificationForm" onsubmit="updateNotifications(event)">
                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="fw-semibold mb-3">Email Notifications</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_new_ticket" 
                                           id="notifyNewTicket" checked>
                                    <label class="form-check-label" for="notifyNewTicket">
                                        New ticket assignments
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_sla_violation" 
                                           id="notifySlaViolation" checked>
                                    <label class="form-check-label" for="notifySlaViolation">
                                        SLA violations
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_customer_response" 
                                           id="notifyCustomerResponse" checked>
                                    <label class="form-check-label" for="notifyCustomerResponse">
                                        Customer responses
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_approval_requests" 
                                           id="notifyApprovalRequests" checked>
                                    <label class="form-check-label" for="notifyApprovalRequests">
                                        Approval requests
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <h6 class="fw-semibold mb-3">System Notifications</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_system_updates" 
                                           id="notifySystemUpdates">
                                    <label class="form-check-label" for="notifySystemUpdates">
                                        System updates
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_maintenance" 
                                           id="notifyMaintenance" checked>
                                    <label class="form-check-label" for="notifyMaintenance">
                                        Maintenance schedules
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col">
                                <button type="submit" class="btn btn-apple-primary">
                                    <i class="fas fa-save me-2"></i>Save Preferences
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
                        <i class="fas fa-user text-dark fa-2x"></i>
                    </div>
                    <h5 class="fw-semibold"><?= htmlspecialchars($user_details['name'] ?? 'User') ?></h5>
                    <div class="text-muted mb-3">
                        <span class="badge bg-<?= $user_role === 'controller_nodal' ? 'success' : 'primary' ?> fs-6">
                            <?= ucwords(str_replace('_', ' ', $user_role)) ?>
                        </span>
                    </div>
                    <div class="row text-center">
                        <div class="col">
                            <div class="fw-semibold">Department</div>
                            <small class="text-muted"><?= htmlspecialchars($user_details['department'] ?? 'N/A') ?></small>
                        </div>
                    </div>
                    <div class="row text-center mt-2">
                        <div class="col-6">
                            <div class="fw-semibold">Division</div>
                            <small class="text-muted"><?= htmlspecialchars($user_details['division'] ?? 'N/A') ?></small>
                        </div>
                        <div class="col-6">
                            <div class="fw-semibold">Zone</div>
                            <small class="text-muted"><?= htmlspecialchars($user_details['zone'] ?? 'N/A') ?></small>
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
                        <span>Email Verified</span>
                        <span class="badge bg-<?= ($user_details['email_verified'] ?? false) ? 'success' : 'warning' ?>">
                            <?= ($user_details['email_verified'] ?? false) ? 'Verified' : 'Pending' ?>
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

            <!-- Quick Actions -->
            <div class="card card-apple mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= Config::getAppUrl() ?>/controller/tickets" class="btn btn-apple-primary btn-sm">
                            <i class="fas fa-ticket-alt me-2"></i>My Assigned Tickets
                        </a>
                        <a href="<?= Config::getAppUrl() ?>/controller/reports" class="btn btn-apple-secondary btn-sm">
                            <i class="fas fa-chart-line me-2"></i>Performance Report
                        </a>
                        <button class="btn btn-apple-secondary btn-sm" onclick="downloadProfile()">
                            <i class="fas fa-download me-2"></i>Download Profile Data
                        </button>
                        <a href="<?= Config::getAppUrl() ?>/controller/help" class="btn btn-apple-secondary btn-sm">
                            <i class="fas fa-question-circle me-2"></i>Help & Support
                        </a>
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Password Last Changed</span>
                        <small class="text-muted">
                            <?= isset($user_details['password_changed_at']) ? 
                                date('M d, Y', strtotime($user_details['password_changed_at'])) : 
                                'More than 90 days ago' ?>
                        </small>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Two-Factor Auth</span>
                        <span class="badge bg-<?= ($user_details['two_factor_enabled'] ?? false) ? 'success' : 'warning' ?>">
                            <?= ($user_details['two_factor_enabled'] ?? false) ? 'Enabled' : 'Disabled' ?>
                        </span>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-sm btn-apple-warning" onclick="enable2FA()">
                            <i class="fas fa-shield-alt me-2"></i>
                            <?= ($user_details['two_factor_enabled'] ?? false) ? 'Manage 2FA' : 'Enable 2FA' ?>
                        </button>
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
        if (!['employee_id', 'email'].includes(input.name)) {
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
        if (!['employee_id', 'email'].includes(input.name)) {
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
        const response = await fetch(`${APP_URL}/controller/profile`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            Swal.fire('Success', 'Profile updated successfully', 'success');
            cancelEdit(); // Exit edit mode
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
        const response = await fetch(`${APP_URL}/customer/change-password`, {
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

async function updateNotifications(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/controller/profile/notifications`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            Swal.fire('Success', 'Notification preferences updated', 'success');
        } else {
            Swal.fire('Error', result.message || 'Failed to update preferences', 'error');
        }
    } catch (error) {
        hideLoading();
        Swal.fire('Error', 'Failed to update preferences', 'error');
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

function downloadProfile() {
    Swal.fire({
        title: 'Download Profile Data',
        text: 'This will generate a PDF with your profile information and activity summary.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Download PDF',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `${APP_URL}/controller/profile/download`;
        }
    });
}

function enable2FA() {
    Swal.fire({
        title: 'Two-Factor Authentication',
        text: 'Two-factor authentication adds an extra layer of security to your account.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Setup 2FA',
        cancelButtonText: 'Later'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to 2FA setup page
            window.location.href = `${APP_URL}/controller/profile/2fa-setup`;
        }
    });
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

/* Profile avatar */
.profile-avatar {
    transition: all 0.3s ease;
}

.profile-avatar:hover {
    transform: scale(1.05);
}

/* Form sections */
.form-section {
    background: rgba(var(--apple-primary-rgb), 0.02);
    border-radius: var(--apple-radius-medium);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

/* Status badges */
.badge {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
}

/* Security indicators */
.security-status {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem;
    background: rgba(var(--bs-light), 0.5);
    border-radius: var(--apple-radius-small);
    margin-bottom: 0.75rem;
}

.security-status:last-child {
    margin-bottom: 0;
}

/* Password strength indicator */
.password-strength {
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    margin-top: 0.5rem;
    overflow: hidden;
}

.password-strength-fill {
    height: 100%;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.password-strength.weak .password-strength-fill {
    width: 25%;
    background: #dc3545;
}

.password-strength.medium .password-strength-fill {
    width: 50%;
    background: #ffc107;
}

.password-strength.good .password-strength-fill {
    width: 75%;
    background: #20c997;
}

.password-strength.strong .password-strength-fill {
    width: 100%;
    background: #198754;
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

/* Loading states */
.form-loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .card-body {
        padding: 1rem 0.75rem;
    }
    
    .row.g-3 {
        --bs-gutter-x: 1rem;
    }
    
    .btn-toolbar {
        flex-direction: column;
    }
    
    .btn-toolbar .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .security-status {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }
    
    .security-status > span:last-child {
        margin-top: 0.5rem;
    }
}

/* Print styles */
@media print {
    .btn, .btn-group, .card-header .btn,
    #editButtons, #passwordForm, #notificationForm {
        display: none !important;
    }
    
    .card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    .form-control-apple {
        border: none !important;
        background: transparent !important;
        padding: 0 !important;
    }
    
    .form-label-apple {
        font-weight: bold !important;
        color: #000 !important;
    }
}

/* Animation for edit mode transition */
.edit-transition {
    transition: all 0.3s ease;
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

/* Profile completion indicator */
.profile-completion {
    background: linear-gradient(135deg, rgba(var(--apple-primary-rgb), 0.1) 0%, transparent 100%);
    border-left: 4px solid var(--apple-primary);
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>