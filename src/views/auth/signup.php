<?php
// Capture the content
ob_start();
?>

<!-- Registration Section -->
<section class="py-apple-8">
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">
                
                <!-- Registration Card -->
                <div class="card-apple shadow-apple-medium">
                    <div class="card-body p-4 p-md-5">
                        
                        <!-- Header -->
                        <div class="text-center mb-5">
                            <h2 class="display-3 mb-2">Customer Registration</h2>
                            <p class="text-muted">Create your SAMPARK account to access freight support services</p>
                        </div>
                        
                        <!-- Registration Form -->
                        <form id="registrationForm" method="POST" action="<?= Config::APP_URL ?>/signup">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            
                            <!-- Personal Information -->
                            <div class="card-apple-glass mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-user text-apple-blue me-2"></i>
                                        Personal Information
                                    </h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="name" class="form-label-apple required">Full Name</label>
                                            <input type="text" 
                                                   class="form-control form-control-apple" 
                                                   id="name" 
                                                   name="name" 
                                                   placeholder="Enter your full name"
                                                   required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="email" class="form-label-apple required">Email Address</label>
                                            <input type="email" 
                                                   class="form-control form-control-apple" 
                                                   id="email" 
                                                   name="email" 
                                                   placeholder="Enter your email address"
                                                   required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="mobile" class="form-label-apple required">Mobile Number</label>
                                            <input type="tel" 
                                                   class="form-control form-control-apple" 
                                                   id="mobile" 
                                                   name="mobile" 
                                                   placeholder="Enter 10-digit mobile number"
                                                   pattern="[6-9][0-9]{9}"
                                                   maxlength="10"
                                                   required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Company Information -->
                            <div class="card-apple-glass mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-building text-apple-blue me-2"></i>
                                        Company Information
                                    </h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label for="company_name" class="form-label-apple required">Company Name</label>
                                            <input type="text" 
                                                   class="form-control form-control-apple" 
                                                   id="company_name" 
                                                   name="company_name" 
                                                   placeholder="Enter your company name"
                                                   required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label for="designation" class="form-label-apple">Designation</label>
                                            <input type="text" 
                                                   class="form-control form-control-apple" 
                                                   id="designation" 
                                                   name="designation" 
                                                   placeholder="Your designation">
                                        </div>
                                        
                                        <div class="col-12">
                                            <label for="gstin" class="form-label-apple">GSTIN (Optional)</label>
                                            <input type="text" 
                                                   class="form-control form-control-apple" 
                                                   id="gstin" 
                                                   name="gstin" 
                                                   placeholder="Enter GSTIN if available"
                                                   pattern="[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}[Z]{1}[0-9A-Z]{1}"
                                                   maxlength="15">
                                            <small class="text-muted">Format: 22AAAAA0000A1Z5</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Location Information -->
                            <div class="card-apple-glass mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-map-marker-alt text-apple-blue me-2"></i>
                                        Location Information
                                    </h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="zone" class="form-label-apple">Zone</label>
                                            <select class="form-control form-control-apple" id="zone" name="zone">
                                                <option value="">Select Zone</option>
                                                <?php if (isset($zones)): ?>
                                                    <?php foreach ($zones as $zone): ?>
                                                        <option value="<?= htmlspecialchars($zone['zone']) ?>">
                                                            <?= htmlspecialchars($zone['zone']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="division" class="form-label-apple required">Division</label>
                                            <select class="form-control form-control-apple" id="division" name="division" required>
                                                <option value="">Select Division</option>
                                                <?php if (isset($divisions)): ?>
                                                    <?php foreach ($divisions as $division): ?>
                                                        <option value="<?= htmlspecialchars($division['division']) ?>">
                                                            <?= htmlspecialchars($division['division']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                            <small class="text-muted">Select the division where you primarily operate</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Security Information -->
                            <div class="card-apple-glass mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-shield-alt text-apple-blue me-2"></i>
                                        Security Information
                                    </h5>
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="password" class="form-label-apple required">Password</label>
                                            <div class="position-relative">
                                                <input type="password" 
                                                       class="form-control form-control-apple" 
                                                       id="password" 
                                                       name="password" 
                                                       placeholder="Create a strong password"
                                                       required>
                                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y" onclick="togglePassword('password')">
                                                    <i class="fas fa-eye" id="passwordToggle"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback"></div>
                                            <div class="password-strength mt-2">
                                                <div class="progress" style="height: 4px;">
                                                    <div class="progress-bar" id="passwordStrength"></div>
                                                </div>
                                                <small class="text-muted" id="passwordStrengthText">Password strength</small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="password_confirmation" class="form-label-apple required">Confirm Password</label>
                                            <div class="position-relative">
                                                <input type="password" 
                                                       class="form-control form-control-apple" 
                                                       id="password_confirmation" 
                                                       name="password_confirmation" 
                                                       placeholder="Confirm your password"
                                                       required>
                                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y" onclick="togglePassword('password_confirmation')">
                                                    <i class="fas fa-eye" id="password_confirmationToggle"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Password must be at least 8 characters with uppercase, lowercase, number, and special character.
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            
                            <!-- Submit Button -->
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-apple-primary btn-lg" id="submitButton">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Create Account
                                </button>
                            </div>
                        </form>
                        
                        <!-- Login Link -->
                        <div class="text-center">
                            <p class="text-muted">
                                Already have an account? 
                                <a href="<?= Config::APP_URL ?>/login" class="text-decoration-none text-apple-blue fw-medium">
                                    Sign in here
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Information Card -->
                <div class="card-apple-glass mt-4">
                    <div class="card-body p-4 text-center">
                        <h6 class="fw-semibold mb-2">
                            <i class="fas fa-info-circle text-apple-blue me-2"></i>
                            Registration Process
                        </h6>
                        <p class="text-muted small mb-0">
                            After submitting your registration, it will be reviewed by the divisional administrator. 
                            You will receive an email confirmation once your account is approved. This process typically takes 1-2 business days.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('password_confirmation');
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');
    
    // Password strength checker
    passwordField.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        updatePasswordStrength(strength);
    });
    
    // Real-time validation
    form.querySelectorAll('input, select').forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });
    
    // Mobile number formatting
    document.getElementById('mobile').addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }
    });
    
    // GSTIN formatting
    document.getElementById('gstin').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            submitForm();
        }
    });
    
    // Division-Zone dependency
    document.getElementById('division').addEventListener('change', function() {
        updateZoneBasedOnDivision(this.value);
    });
});

function calculatePasswordStrength(password) {
    let score = 0;
    let feedback = [];
    
    // Length
    if (password.length >= 8) score += 25;
    else feedback.push('At least 8 characters');
    
    // Uppercase
    if (/[A-Z]/.test(password)) score += 25;
    else feedback.push('uppercase letter');
    
    // Lowercase
    if (/[a-z]/.test(password)) score += 25;
    else feedback.push('lowercase letter');
    
    // Numbers
    if (/\d/.test(password)) score += 25;
    else feedback.push('number');
    
    // Special characters
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

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let message = '';
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        message = 'This field is required';
    }
    
    // Specific field validations
    switch (field.id) {
        case 'email':
            if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                isValid = false;
                message = 'Please enter a valid email address';
            }
            break;
            
        case 'mobile':
            if (value && !/^[6-9]\d{9}$/.test(value)) {
                isValid = false;
                message = 'Please enter a valid 10-digit mobile number';
            }
            break;
            
        case 'gstin':
            if (value && !/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}[Z]{1}[0-9A-Z]{1}$/.test(value)) {
                isValid = false;
                message = 'Please enter a valid GSTIN';
            }
            break;
            
        case 'password':
            const strength = calculatePasswordStrength(value);
            if (value && strength.score < 75) {
                isValid = false;
                message = 'Password is too weak. ' + strength.feedback.join(', ');
            }
            break;
            
        case 'password_confirmation':
            const password = document.getElementById('password').value;
            if (value && value !== password) {
                isValid = false;
                message = 'Passwords do not match';
            }
            break;
    }
    
    // Update field appearance
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = message;
        }
    }
    
    return isValid;
}

function validateForm() {
    const form = document.getElementById('registrationForm');
    let isValid = true;
    
    // Validate all fields
    form.querySelectorAll('input[required], select[required]').forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    
    return isValid;
}

function submitForm() {
    const form = document.getElementById('registrationForm');
    const submitButton = document.getElementById('submitButton');
    
    // Show loading state
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Account...';
    submitButton.disabled = true;
    
    // Submit form
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form)
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
        } else {
            return response.text();
        }
    })
    .then(text => {
        if (text) {
            // Reset button
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
            
            // Parse response for errors
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const errorAlert = doc.querySelector('.alert-danger');
            
            if (errorAlert) {
                Swal.fire({
                    icon: 'error',
                    title: 'Registration Failed',
                    html: errorAlert.innerHTML
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Registration Failed',
                    text: 'An unexpected error occurred. Please try again.'
                });
            }
        }
    })
    .catch(error => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Unable to connect to the server. Please check your internet connection and try again.'
        });
    });
}

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const toggle = document.getElementById(fieldId + 'Toggle');
    
    if (field.type === 'password') {
        field.type = 'text';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
    }
}

function updateZoneBasedOnDivision(division) {
    // This would typically make an AJAX call to get the zone for the selected division
    // For now, we'll leave it as is since zone selection is optional
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
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.progress {
    background-color: var(--apple-off-white);
}

.password-strength {
    transition: all 0.3s ease;
}

/* Mobile improvements */
@media (max-width: 576px) {
    .card-body {
        padding: 1.5rem !important;
    }
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
