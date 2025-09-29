<?php
// Capture the content
ob_start();
?>

<!-- Create User -->
<section class="py-apple-6">
    <div class="container-xl">
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div>
                        <h1 class="display-3 mb-2">Create New User</h1>
                        <p class="text-muted mb-0">Add a new user to the system</p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <a href="<?= Config::getAppUrl() ?>/admin/users" class="btn btn-apple-glass">
                            <i class="fas fa-arrow-left me-2"></i>Back to Users
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Create User Form -->
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card-apple">
                    <div class="card-body">
                        <form id="createUserForm" action="<?= Config::getAppUrl() ?>/admin/users/create" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            
                            <!-- Basic Information -->
                            <h4 class="mb-4">Basic Information</h4>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="name" class="form-label-apple">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-apple" id="name" name="name" required>
                                    <div class="invalid-feedback">Please provide a name.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="designation" class="form-label-apple">Designation</label>
                                    <input type="text" class="form-control form-control-apple" id="designation" name="designation" placeholder="e.g., Chief Engineer, Assistant Manager">
                                    <div class="invalid-feedback">Please provide a designation.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label-apple">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control form-control-apple" id="email" name="email" required>
                                    <div class="invalid-feedback">Please provide a valid email address.</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="mobile" class="form-label-apple">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control form-control-apple" id="mobile" name="mobile" required>
                                    <div class="invalid-feedback">Please provide a valid phone number.</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="role" class="form-label-apple">User Role <span class="text-danger">*</span></label>
                                    <select class="form-select form-control-apple" id="role" name="role" required onchange="toggleRoleFields()">
                                        <option value="">Select Role</option>
                                        <?php foreach ($roles as $role => $label): ?>
                                            <option value="<?= $role ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a role.</div>
                                </div>
                            </div>
                            
                            <!-- Additional Information -->
                            <h4 class="mb-4">Additional Information</h4>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="login_id" class="form-label-apple">Login ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-apple" id="login_id" name="login_id" required>
                                    <div class="invalid-feedback">Please provide a login ID.</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="department" class="form-label-apple">Department <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-apple" id="department" name="department" required>
                                    <div class="invalid-feedback">Please provide a department.</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="division" class="form-label-apple">Division <span class="text-danger">*</span></label>
                                    <select class="form-select form-control-apple" id="division" name="division" required>
                                        <option value="">Select Division</option>
                                        <?php foreach ($divisions as $division): ?>
                                            <option value="<?= $division['division'] ?>"><?= $division['division'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a division.</div>
                                </div>
                            </div>
                                
                            <!-- Role-specific Fields (Controller) -->
                            <div id="controllerFields" class="role-specific-fields" style="display: none;">
                                <h4 class="mb-4">Controller Details</h4>
                                
                                <div class="row g-3 mb-4">
                                    
                                    <div class="col-12">
                                        <label for="regions" class="form-label-apple">Regions (Select multiple if applicable)</label>
                                        <select class="form-select form-control-apple" id="regions" name="regions[]" multiple>
                                            <?php foreach ($regions as $region): ?>
                                                <option value="<?= $region['id'] ?>"><?= $region['name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Access Control -->
                            <h4 class="mb-4">Account Settings</h4>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="password" class="form-label-apple">Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control form-control-apple" id="password" name="password" required minlength="8">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Minimum 8 characters, alphanumeric with at least one special character.</div>
                                    <div class="invalid-feedback">Password must be at least 8 characters.</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="password_confirm" class="form-label-apple">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control form-control-apple" id="password_confirm" name="password_confirm" required>
                                    <div class="invalid-feedback">Passwords do not match.</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label-apple">Account Status</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="statusActive" value="active" checked>
                                            <label class="form-check-label" for="statusActive">Active</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="statusInactive" value="inactive">
                                            <label class="form-check-label" for="statusInactive">Inactive</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="sendInvite" name="send_invite" checked>
                                        <label class="form-check-label" for="sendInvite">
                                            Send welcome email with login details
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="forcePasswordChange" name="force_password_change">
                                        <label class="form-check-label" for="forcePasswordChange">
                                            Force password change on first login
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
                                    <i class="fas fa-plus me-2"></i>Create User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-12 col-lg-4 mt-4 mt-lg-0">
                <!-- User Roles Info -->
                <div class="card-apple-glass mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-user-shield text-apple-blue me-2"></i>
                            User Roles
                        </h5>
                        <p class="card-text small">Select the appropriate role for the user based on their responsibilities:</p>
                        
                        <div class="roles-list">
                            <div class="role-item mb-3">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge bg-danger me-2">Admin</span>
                                    <strong>Administrator</strong>
                                </div>
                                <p class="small text-muted ms-4 mb-0">Full system access with all permissions.</p>
                            </div>
                            
                            <div class="role-item mb-3">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge bg-primary me-2">Controller</span>
                                    <strong>Controller</strong>
                                </div>
                                <p class="small text-muted ms-4 mb-0">Manages tickets and customer interactions.</p>
                            </div>
                            
                            <div class="role-item mb-3">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge bg-warning text-dark me-2">Nodal</span>
                                    <strong>Controller Nodal</strong>
                                </div>
                                <p class="small text-muted ms-4 mb-0">Nodal officer with specialized escalation access.</p>
                            </div>
                            
                            <div class="role-item">
                                <div class="d-flex align-items-center mb-1">
                                    <span class="badge bg-info me-2">Support</span>
                                    <strong>Customer Support</strong>
                                </div>
                                <p class="small text-muted ms-4 mb-0">Limited access for customer assistance only.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Password Guidelines -->
                <div class="card-apple-glass mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-lock text-apple-blue me-2"></i>
                            Password Guidelines
                        </h5>
                        <p class="card-text small">Ensure that passwords meet these requirements:</p>
                        
                        <ul class="small">
                            <li>Minimum 8 characters in length</li>
                            <li>At least one uppercase letter</li>
                            <li>At least one lowercase letter</li>
                            <li>At least one number</li>
                            <li>At least one special character</li>
                        </ul>
                        
                        <button type="button" class="btn btn-sm btn-apple-glass w-100" id="generatePasswordBtn">
                            <i class="fas fa-key me-2"></i>Generate Strong Password
                        </button>
                    </div>
                </div>
                
                <!-- Need Help? -->
                <div class="card-apple-glass">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-question-circle text-apple-blue me-2"></i>
                            Need Help?
                        </h5>
                        <p class="card-text small">Check the admin guide for detailed information on user roles and permissions.</p>
                        
                        <a href="<?= Config::getAppUrl() ?>/help/admin-guide" class="btn btn-sm btn-apple-glass w-100">
                            <i class="fas fa-book me-2"></i>View Admin Guide
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('createUserForm');
    
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        event.stopPropagation();

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        // Check if passwords match
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirm');

        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Passwords don't match");
            form.classList.add('was-validated');
            return;
        } else {
            confirmPassword.setCustomValidity("");
        }

        // Submit form via AJAX
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating User...';

        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (window.SAMPARK && window.SAMPARK.ui && window.SAMPARK.ui.showToast) {
                    window.SAMPARK.ui.showToast(data.message, 'success');
                }
                // Redirect to users page
                setTimeout(() => {
                    window.location.href = data.redirect || '<?= Config::getAppUrl() ?>/admin/users';
                }, 1000);
            } else {
                if (window.SAMPARK && window.SAMPARK.ui && window.SAMPARK.ui.showToast) {
                    window.SAMPARK.ui.showToast(data.message || 'Failed to create user', 'error');
                } else {
                    alert(data.message || 'Failed to create user');
                }

                // Handle validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = input.parentElement.querySelector('.invalid-feedback');
                            if (feedback) {
                                feedback.textContent = data.errors[field][0];
                            }
                        }
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (window.SAMPARK && window.SAMPARK.ui && window.SAMPARK.ui.showToast) {
                window.SAMPARK.ui.showToast('An error occurred while creating user', 'error');
            } else {
                alert('An error occurred while creating user');
            }
        })
        .finally(() => {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
    
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = document.getElementById('password');
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
    
    // Password strength check
    const passwordInput = document.getElementById('password');
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const minLength = 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumbers = /\d/.test(password);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        
        let strength = 0;
        
        if (password.length >= minLength) {
            strength += 1;
        }
        if (hasUpperCase) {
            strength += 1;
        }
        if (hasLowerCase) {
            strength += 1;
        }
        if (hasNumbers) {
            strength += 1;
        }
        if (hasSpecialChar) {
            strength += 1;
        }
        
        // You can use the strength variable to provide visual feedback
        // Example: update a progress bar or show a message
    });
    
    // Generate password button
    document.getElementById('generatePasswordBtn').addEventListener('click', function() {
        const length = 12;
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-+=";
        let password = "";
        
        for (let i = 0; i < length; i++) {
            const randomIndex = Math.floor(Math.random() * charset.length);
            password += charset[randomIndex];
        }
        
        document.getElementById('password').value = password;
        document.getElementById('password_confirm').value = password;
        
        // Show a toast notification
        window.SAMPARK.ui.showToast('Strong password generated', 'success');
    });
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
    } else {
        // Remove required attribute from controller-specific fields
        document.getElementById('division').required = false;
    }
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

/* Role cards */
.roles-list .role-item {
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding-bottom: 0.75rem;
}

.roles-list .role-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
    .display-3 {
        font-size: 1.75rem;
    }
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
