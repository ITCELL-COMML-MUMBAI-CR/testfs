<?php
// Capture the content
ob_start();
?>

<!-- Customer Profile -->
<section class="py-apple-6">
    <div class="container-xl">
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-3 mb-2">My Profile</h1>
                <p class="text-muted">Manage your personal information and account settings</p>
            </div>
        </div>
        
        <div class="row g-4">
            
            <!-- Profile Form -->
            <div class="col-12 col-lg-8">
                
                <!-- Personal Information -->
                <div class="card-apple mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-user text-apple-blue me-2"></i>
                            Personal Information
                        </h5>
                        
                        <form id="profileForm">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label-apple required">Full Name</label>
                                    <input type="text" 
                                           class="form-control form-control-apple" 
                                           id="name" 
                                           name="name" 
                                           value="<?= htmlspecialchars($customer_details['name']) ?>"
                                           required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label-apple">Email Address</label>
                                    <input type="email" 
                                           class="form-control form-control-apple" 
                                           id="email" 
                                           name="email" 
                                           value="<?= htmlspecialchars($customer_details['email']) ?>"
                                           readonly>
                                    <small class="text-muted">Email cannot be changed. Contact support if needed.</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="mobile" class="form-label-apple required">Mobile Number</label>
                                    <input type="tel" 
                                           class="form-control form-control-apple" 
                                           id="mobile" 
                                           name="mobile" 
                                           value="<?= htmlspecialchars($customer_details['mobile']) ?>"
                                           pattern="[6-9][0-9]{9}"
                                           maxlength="10"
                                           required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="designation" class="form-label-apple">Designation</label>
                                    <input type="text" 
                                           class="form-control form-control-apple" 
                                           id="designation" 
                                           name="designation" 
                                           value="<?= htmlspecialchars($customer_details['designation'] ?? '') ?>">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Company Information -->
                <div class="card-apple mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-building text-apple-blue me-2"></i>
                            Company Information
                        </h5>
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="company_name" class="form-label-apple required">Company Name</label>
                                <input type="text" 
                                       class="form-control form-control-apple" 
                                       id="company_name" 
                                       name="company_name" 
                                       value="<?= htmlspecialchars($customer_details['company_name']) ?>"
                                       form="profileForm"
                                       required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Account Information -->
                <div class="card-apple mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-map-marker-alt text-apple-blue me-2"></i>
                            Account Information
                        </h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-apple">Customer ID</label>
                                <div class="d-flex align-items-center">
                                    <code class="text-apple-blue me-2"><?= htmlspecialchars($customer_details['customer_id']) ?></code>
                                    <button type="button" 
                                            class="btn btn-link btn-sm p-0" 
                                            onclick="copyCustomerId('<?= $customer_details['customer_id'] ?>')">
                                        <i class="fas fa-copy text-muted"></i>
                                    </button>
                                </div>
                                <small class="text-muted">This is your unique customer identifier</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-apple">Account Status</label>
                                <div>
                                    <span class="badge badge-apple <?= $customer_details['status'] === 'approved' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= ucfirst($customer_details['status']) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-apple">Division</label>
                                <div><?= htmlspecialchars($customer_details['division']) ?></div>
                                <small class="text-muted">Primary operating division</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-apple">Zone</label>
                                <div><?= htmlspecialchars($customer_details['zone']) ?></div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-apple">Member Since</label>
                                <div><?= date('F d, Y', strtotime($customer_details['created_at'])) ?></div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label-apple">Last Updated</label>
                                <div><?= date('F d, Y H:i', strtotime($customer_details['updated_at'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-flex gap-3 flex-wrap">
                    <button type="submit" form="profileForm" class="btn btn-apple-primary" id="saveButton">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                    <button type="button" class="btn btn-apple-glass" onclick="resetForm()">
                        <i class="fas fa-undo me-2"></i>Reset
                    </button>
                    <button type="button" class="btn btn-apple-glass" onclick="showPasswordChange()">
                        <i class="fas fa-key me-2"></i>Change Password
                    </button>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-12 col-lg-4">
                
                <!-- Profile Summary -->
                <div class="card-apple mb-4">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <div class="bg-apple-blue rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-2x"></i>
                            </div>
                        </div>
                        <h6 class="fw-semibold mb-1"><?= htmlspecialchars($customer_details['name']) ?></h6>
                        <div class="text-muted small mb-2"><?= htmlspecialchars($customer_details['company_name']) ?></div>
                        <span class="badge badge-apple <?= $customer_details['status'] === 'approved' ? 'bg-success' : 'bg-warning' ?>">
                            <?= ucfirst($customer_details['status']) ?>
                        </span>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="card-apple-glass mb-4">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-chart-line text-apple-blue me-2"></i>
                            Account Summary
                        </h6>
                        
                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <div class="fw-semibold text-apple-blue fs-4" id="totalTickets">-</div>
                                <small class="text-muted">Total Tickets</small>
                            </div>
                            <div class="col-6">
                                <div class="fw-semibold text-apple-blue fs-4" id="activeTickets">-</div>
                                <small class="text-muted">Active Tickets</small>
                            </div>
                            <div class="col-6">
                                <div class="fw-semibold text-apple-blue fs-4" id="resolvedTickets">-</div>
                                <small class="text-muted">Resolved</small>
                            </div>
                            <div class="col-6">
                                <div class="fw-semibold text-success fs-4" id="satisfactionRate">-</div>
                                <small class="text-muted">Satisfaction</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Security -->
                <div class="card-apple-glass mb-4">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-shield-alt text-apple-blue me-2"></i>
                            Security & Privacy
                        </h6>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-apple-glass btn-sm" onclick="showPasswordChange()">
                                <i class="fas fa-key me-1"></i>Change Password
                            </button>
                            <button class="btn btn-apple-glass btn-sm" onclick="showLoginHistory()">
                                <i class="fas fa-history me-1"></i>Login History
                            </button>
                            <button class="btn btn-apple-glass btn-sm" onclick="downloadData()">
                                <i class="fas fa-download me-1"></i>Download My Data
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Support -->
                <div class="card-apple-glass">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-question-circle text-apple-blue me-2"></i>
                            Need Help?
                        </h6>
                        
                        <p class="small text-muted mb-3">
                            Having trouble updating your profile? We're here to help.
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
document.addEventListener('DOMContentLoaded', function() {
    setupFormValidation();
    loadAccountStats();
    
    // Mobile number formatting
    document.getElementById('mobile').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }
    });
    
});

function setupFormValidation() {
    const form = document.getElementById('profileForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            updateProfile();
        }
    });
    
    // Real-time validation
    form.querySelectorAll('input[required]').forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });
}

function validateForm() {
    const form = document.getElementById('profileForm');
    let isValid = true;
    
    // Validate required fields
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    // Validate mobile number
    const mobile = document.getElementById('mobile');
    if (mobile.value && !/^[6-9]\d{9}$/.test(mobile.value)) {
        showFieldError(mobile, 'Please enter a valid 10-digit mobile number');
        isValid = false;
    }
    
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let message = '';
    
    if (field.hasAttribute('required') && !value) {
        message = 'This field is required';
        isValid = false;
    }
    
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        showFieldError(field, message);
    }
    
    return isValid;
}

function showFieldError(field, message) {
    field.classList.remove('is-valid');
    field.classList.add('is-invalid');
    
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.textContent = message;
    }
}

function updateProfile() {
    const form = document.getElementById('profileForm');
    const saveButton = document.getElementById('saveButton');
    
    const originalText = saveButton.innerHTML;
    saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    saveButton.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('<?= Config::getAppUrl() ?>/customer/profile', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        saveButton.innerHTML = originalText;
        saveButton.disabled = false;
        
        if (data.success) {
            window.SAMPARK.ui.showSuccess('Profile Updated', data.message);
            
            // Update form validation states
            form.querySelectorAll('.is-valid, .is-invalid').forEach(field => {
                field.classList.remove('is-valid', 'is-invalid');
            });
            
            // Update display name if changed
            const nameField = document.getElementById('name');
            if (nameField.value !== '<?= htmlspecialchars($customer_details['name']) ?>') {
                setTimeout(() => location.reload(), 1500);
            }
        } else {
            if (data.errors) {
                // Show field-specific errors
                Object.keys(data.errors).forEach(fieldName => {
                    const field = form.querySelector(`[name="${fieldName}"]`);
                    if (field) {
                        showFieldError(field, data.errors[fieldName][0]);
                    }
                });
            } else {
                window.SAMPARK.ui.showError('Update Failed', data.message);
            }
        }
    })
    .catch(error => {
        saveButton.innerHTML = originalText;
        saveButton.disabled = false;
        window.SAMPARK.ui.showError('Error', 'Failed to update profile. Please try again.');
    });
}

function resetForm() {
    const form = document.getElementById('profileForm');
    
    window.SAMPARK.ui.confirm(
        'Reset Form',
        'Are you sure you want to reset all changes?',
        'Yes, Reset',
        'Cancel'
    ).then((result) => {
        if (result.isConfirmed) {
            form.reset();
            form.querySelectorAll('.is-valid, .is-invalid').forEach(field => {
                field.classList.remove('is-valid', 'is-invalid');
            });
            window.SAMPARK.ui.showToast('Form reset successfully', 'info');
        }
    });
}

function showPasswordChange() {
    Swal.fire({
        title: 'Change Password',
        html: `
            <div class="text-start">
                <div class="mb-3">
                    <label for="currentPassword" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="currentPassword" placeholder="Enter current password">
                </div>
                <div class="mb-3">
                    <label for="newPassword" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="newPassword" placeholder="Enter new password">
                    <div class="password-strength mt-2">
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar" id="passwordStrength"></div>
                        </div>
                        <small class="text-muted" id="passwordStrengthText">Password strength</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password">
                </div>
                <small class="text-muted">
                    Password must be at least 8 characters with uppercase, lowercase, number, and special character.
                </small>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Change Password',
        confirmButtonClass: 'btn btn-apple-primary',
        cancelButtonClass: 'btn btn-apple-glass',
        width: '500px',
        preConfirm: () => {
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (!currentPassword) {
                Swal.showValidationMessage('Please enter your current password');
                return false;
            }
            
            if (!newPassword || newPassword.length < 8) {
                Swal.showValidationMessage('New password must be at least 8 characters');
                return false;
            }
            
            if (newPassword !== confirmPassword) {
                Swal.showValidationMessage('Passwords do not match');
                return false;
            }
            
            return {
                currentPassword: currentPassword,
                newPassword: newPassword,
                confirmPassword: confirmPassword
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            changePassword(result.value);
        }
    });
    
    // Add password strength checker
    setTimeout(() => {
        document.getElementById('newPassword').addEventListener('input', function() {
            const strength = calculatePasswordStrength(this.value);
            updatePasswordStrength(strength);
        });
    }, 100);
}

function calculatePasswordStrength(password) {
    let score = 0;
    let feedback = [];
    
    if (password.length >= 8) score += 25;
    else feedback.push('At least 8 characters');
    
    if (/[A-Z]/.test(password)) score += 25;
    else feedback.push('uppercase letter');
    
    if (/[a-z]/.test(password)) score += 25;
    else feedback.push('lowercase letter');
    
    if (/\d/.test(password)) score += 25;
    else feedback.push('number');
    
    if (/[^A-Za-z0-9]/.test(password)) score += 25;
    else feedback.push('special character');
    
    return {
        score: Math.min(score, 100),
        feedback: feedback
    };
}

function updatePasswordStrength(strength) {
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');
    
    if (!strengthBar || !strengthText) return;
    
    strengthBar.style.width = strength.score + '%';
    
    if (strength.score < 50) {
        strengthBar.className = 'progress-bar bg-danger';
        strengthText.textContent = 'Weak - Add: ' + strength.feedback.join(', ');
        strengthText.className = 'text-danger small';
    } else if (strength.score < 75) {
        strengthBar.className = 'progress-bar bg-warning';
        strengthText.textContent = 'Medium - Add: ' + strength.feedback.join(', ');
        strengthText.className = 'text-warning small';
    } else {
        strengthBar.className = 'progress-bar bg-success';
        strengthText.textContent = 'Strong password';
        strengthText.className = 'text-success small';
    }
}

function changePassword(passwords) {
    // Implementation for password change API call
    const formData = new FormData();
    formData.append('csrf_token', '<?= $csrf_token ?>');
    formData.append('current_password', passwords.currentPassword);
    formData.append('new_password', passwords.newPassword);
    formData.append('confirm_password', passwords.confirmPassword);
    
    fetch('<?= Config::getAppUrl() ?>/customer/change-password', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.SAMPARK.ui.showSuccess('Password Changed', 'Your password has been updated successfully.');
        } else {
            window.SAMPARK.ui.showError('Password Change Failed', data.message);
        }
    })
    .catch(error => {
        window.SAMPARK.ui.showError('Error', 'Failed to change password. Please try again.');
    });
}

function copyCustomerId(customerId) {
    window.SAMPARK.utils.copyToClipboard(customerId)
        .then(() => {
            window.SAMPARK.ui.showToast('Customer ID copied to clipboard', 'success');
        })
        .catch(err => {
            window.SAMPARK.ui.showToast('Failed to copy Customer ID', 'error');
        });
}

function loadAccountStats() {
    fetch('<?= Config::getAppUrl() ?>/api/customer/stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalTickets').textContent = data.stats.total || 0;
                document.getElementById('activeTickets').textContent = data.stats.active || 0;
                document.getElementById('resolvedTickets').textContent = data.stats.resolved || 0;
                document.getElementById('satisfactionRate').textContent = (data.stats.satisfaction_rate || 0) + '%';
            }
        })
        .catch(error => {
            console.log('Failed to load account stats');
        });
}

function showLoginHistory() {
    window.SAMPARK.ui.showInfo('Login History', 'Feature coming soon. You will be able to view your recent login activities and security events.');
}

function downloadData() {
    window.SAMPARK.ui.confirm(
        'Download My Data',
        'This will generate a comprehensive report of all your data including tickets, communications, and account information. Continue?',
        'Yes, Download',
        'Cancel'
    ).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= Config::getAppUrl() ?>/api/customer/export-data';
        }
    });
}

function contactSupport() {
    window.SAMPARK.ui.showInfo('Contact Support', 
        'Email: support@sampark.railway.gov.in<br>' +
        'Phone: 1800-XXX-XXXX<br>' +
        'Hours: Mon-Fri 9:00 AM - 6:00 PM<br><br>' +
        'Please mention your Customer ID: <?= $customer_details['customer_id'] ?>'
    );
}
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.form-control.is-valid {
    border-color: #28a745;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='m2.3 6.73.94-.94 2.5 2.5 5-5 .94.94L5.24 8.5z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-invalid {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 0.4 0.9 0.4-0.9'/%3e%3cpath d='M6 8.2V6.4'/%3e%3c/svg%3e");
    background-repeat: no-right;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.password-strength .progress {
    background-color: var(--apple-off-white);
}

/* Profile avatar animation */
.card-apple .bg-apple-blue {
    transition: all 0.3s ease;
}

.card-apple:hover .bg-apple-blue {
    transform: scale(1.05);
    box-shadow: 0 4px 16px rgba(0, 136, 204, 0.3);
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .d-flex.gap-3 {
        flex-direction: column;
    }
    
    .d-flex.gap-3 .btn {
        margin-bottom: 0.5rem;
    }
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
