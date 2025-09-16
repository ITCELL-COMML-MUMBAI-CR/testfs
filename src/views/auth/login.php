<?php
// Capture the content
ob_start();
?>

<!-- Login Section -->
<section class="py-apple-8">
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                
                <!-- Login Card -->
                <div class="card-apple shadow-apple-medium">
                    <div class="card-body p-4 p-md-5">
                        
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <h2 class="display-3 mb-2">Welcome Back</h2>
                            <p class="text-muted">Sign in to access your SAMPARK account</p>
                        </div>

                        <?php if (isset($_GET['session_expired']) && $_GET['session_expired'] == '1'): ?>
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-clock me-2"></i>Your session has expired. Please sign in again to continue.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= is_array($errors) ? implode('<br>', $errors) : $errors ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= is_array($success) ? implode('<br>', $success) : $success ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Login Type Switcher -->
                        <div class="mb-4">
                            <div class="card-apple-glass p-1">
                                <div class="row g-0">
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" name="loginType" id="customerLogin" value="customer" checked>
                                        <label class="btn btn-apple-glass w-100 py-2" for="customerLogin">
                                            <i class="fas fa-user me-2"></i>Customer
                                        </label>
                                    </div>
                                    <div class="col-6">
                                        <input type="radio" class="btn-check" name="loginType" id="staffLogin" value="user">
                                        <label class="btn btn-apple-glass w-100 py-2" for="staffLogin">
                                            <i class="fas fa-user-tie me-2"></i>Staff
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Login Form -->
                        <form id="loginForm" method="POST" action="<?= Config::getAppUrl() ?>/login">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            <input type="hidden" name="login_type" id="loginTypeInput" value="customer">
                            
                            <!-- Customer Login Fields -->
                            <div id="customerFields">
                                <div class="mb-3">
                                    <label for="customerEmailOrPhone" class="form-label-apple">
                                        <i class="fas fa-envelope me-2"></i>Email or Mobile Number
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-apple" 
                                           id="customerEmailOrPhone" 
                                           name="email_or_phone" 
                                           placeholder="Enter your email or mobile number"
                                           autocomplete="username">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="customerPassword" class="form-label-apple">
                                        <i class="fas fa-lock me-2"></i>Password
                                    </label>
                                    <div class="position-relative">
                                        <input type="password" 
                                               class="form-control form-control-apple" 
                                               id="customerPassword" 
                                               name="password" 
                                               placeholder="Enter your password"
                                               autocomplete="current-password">
                                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y" onclick="togglePassword('customerPassword')">
                                            <i class="fas fa-eye" id="customerPasswordToggle"></i>
                                        </button>
                                    </div>
                                </div>
                                
                            </div>
                            
                            <!-- Staff Login Fields -->
                            <div id="staffFields" style="display: none;">
                                <div class="mb-3">
                                    <label for="staffLoginId" class="form-label-apple">
                                        <i class="fas fa-id-badge me-2"></i>Login ID
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-apple" 
                                           id="staffLoginId" 
                                           name="login_id" 
                                           placeholder="Enter your login ID"
                                           autocomplete="username">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="staffPassword" class="form-label-apple">
                                        <i class="fas fa-lock me-2"></i>Password
                                    </label>
                                    <div class="position-relative">
                                        <input type="password" 
                                               class="form-control form-control-apple" 
                                               id="staffPassword" 
                                               name="password" 
                                               placeholder="Enter your password"
                                               autocomplete="current-password">
                                        <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y" onclick="togglePassword('staffPassword')">
                                            <i class="fas fa-eye" id="staffPasswordToggle"></i>
                                        </button>
                                    </div>
                                </div>
                                
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-apple-primary btn-lg" id="loginButton">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    <span id="loginButtonText">Sign In</span>
                                </button>
                            </div>
                        </form>
                        
                        <!-- Additional Links -->
                        <div class="text-center">
                            <div id="customerLinks">
                                <p class="text-muted">
                                    Don't have an account? 
                                    <a href="<?= Config::getAppUrl() ?>/signup" class="text-decoration-none text-apple-blue fw-medium">
                                        Sign up here
                                    </a>
                                </p>
                            </div>
                            
                            <div id="staffLinks" style="display: none;">
                                <p class="text-muted small">
                                    For staff account issues, contact your administrator
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Help Card -->
                <!-- <div class="card-apple-glass mt-4">
                    <div class="card-body p-4 text-center">
                        <h6 class="fw-semibold mb-2">
                            <i class="fas fa-question-circle text-apple-blue me-2"></i>
                            Need Help?
                        </h6>
                        <p class="text-muted small mb-3">
                            Having trouble accessing your account? Our support team is here to help.
                        </p>
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="<?= Config::getAppUrl() ?>/help" class="btn btn-apple-glass btn-sm w-100">
                                    <i class="fas fa-book me-1"></i>Help Guide
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="#" class="btn btn-apple-glass btn-sm w-100" onclick="showContactSupport()">
                                    <i class="fas fa-headset me-1"></i>Contact Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Login type switcher
    const customerRadio = document.getElementById('customerLogin');
    const staffRadio = document.getElementById('staffLogin');
    const customerFields = document.getElementById('customerFields');
    const staffFields = document.getElementById('staffFields');
    const customerLinks = document.getElementById('customerLinks');
    const staffLinks = document.getElementById('staffLinks');
    const loginTypeInput = document.getElementById('loginTypeInput');
    const loginButtonText = document.getElementById('loginButtonText');
    
    function switchLoginType() {
        // Clear all form fields and validation states
        document.querySelectorAll('.form-control').forEach(field => {
            field.value = '';
            field.classList.remove('is-invalid');
        });
        
        if (customerRadio.checked) {
            customerFields.style.display = 'block';
            staffFields.style.display = 'none';
            customerLinks.style.display = 'block';
            staffLinks.style.display = 'none';
            loginTypeInput.value = 'customer';
            loginButtonText.textContent = 'Sign In as Customer';
            
            // Enable customer fields, disable staff fields
            document.getElementById('customerEmailOrPhone').disabled = false;
            document.getElementById('customerPassword').disabled = false;
            document.getElementById('staffLoginId').disabled = true;
            document.getElementById('staffPassword').disabled = true;
            
            // Focus on customer email field
            setTimeout(() => document.getElementById('customerEmailOrPhone').focus(), 100);
        } else {
            customerFields.style.display = 'none';
            staffFields.style.display = 'block';
            customerLinks.style.display = 'none';
            staffLinks.style.display = 'block';
            loginTypeInput.value = 'user';
            loginButtonText.textContent = 'Sign In as Staff';
            
            // Enable staff fields, disable customer fields
            document.getElementById('customerEmailOrPhone').disabled = true;
            document.getElementById('customerPassword').disabled = true;
            document.getElementById('staffLoginId').disabled = false;
            document.getElementById('staffPassword').disabled = false;
            
            // Focus on staff login ID field
            setTimeout(() => document.getElementById('staffLoginId').focus(), 100);
        }
    }
    
    customerRadio.addEventListener('change', switchLoginType);
    staffRadio.addEventListener('change', switchLoginType);
    
    // Initialize the form state
    switchLoginType();
    
    // Form validation
    const loginForm = document.getElementById('loginForm');
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const loginType = loginTypeInput.value;
        let isValid = true;
        let errorMessage = '';
        
        // Clear any previous validation states
        document.querySelectorAll('.form-control').forEach(field => {
            field.classList.remove('is-invalid');
        });
        
        if (loginType === 'customer') {
            const emailOrPhone = document.getElementById('customerEmailOrPhone').value.trim();
            const password = document.getElementById('customerPassword').value;
            
            console.log('Customer login validation:', { emailOrPhone, password: password ? '[PROVIDED]' : '[EMPTY]', loginType });
            
            if (!emailOrPhone) {
                errorMessage = 'Please enter your email or mobile number';
                document.getElementById('customerEmailOrPhone').classList.add('is-invalid');
                isValid = false;
            } else if (!password) {
                errorMessage = 'Please enter your password';
                document.getElementById('customerPassword').classList.add('is-invalid');
                isValid = false;
            }
        } else {
            const loginId = document.getElementById('staffLoginId').value.trim();
            const password = document.getElementById('staffPassword').value;
            
            console.log('Staff login validation:', { loginId, password: password ? '[PROVIDED]' : '[EMPTY]', loginType });
            
            if (!loginId) {
                errorMessage = 'Please enter your login ID';
                document.getElementById('staffLoginId').classList.add('is-invalid');
                isValid = false;
            } else if (!password) {
                errorMessage = 'Please enter your password';
                document.getElementById('staffPassword').classList.add('is-invalid');
                isValid = false;
            }
        }
        
        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: errorMessage
            });
            return;
        }
        
        // Show loading
        const loginButton = document.getElementById('loginButton');
        const originalText = loginButton.innerHTML;
        loginButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
        loginButton.disabled = true;
        
        // Reset button state after a timeout in case of errors
        setTimeout(() => {
            loginButton.innerHTML = originalText;
            loginButton.disabled = false;
        }, 10000);
        
        // Submit the original form directly
        loginForm.submit();
    });
});

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

function showForgotPassword() {
    Swal.fire({
        title: 'Forgot Password',
        html: `
<div class="text-start">
                <p class="mb-3">Enter your email address to receive password reset instructions.</p>
                <div class="mb-3">
                    <label for="resetEmail" class="form-label">
                        Email Address
                    </label>
                    <input type="email" 
                           class="form-control form-control-apple" 
                           id="resetEmail" 
                           placeholder="Enter your email address">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Send Reset Link',
        customClass: {
            confirmButton: 'btn btn-apple-primary',
            cancelButton: 'btn btn-apple-glass'
        },
        preConfirm: () => {
            const email = document.getElementById('resetEmail').value;
            if (!email) {
                Swal.showValidationMessage('Please enter your email address');
                return false;
            }
            return email;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Handle password reset
            Swal.fire({
                icon: 'success',
                title: 'Reset Link Sent',
                text: 'If an account with that email exists, you will receive password reset instructions shortly.'
            });
        }
    });
}

function showContactSupport() {
    Swal.fire({
        title: 'Contact Support',
        html: `
            <div class="text-start">
                <div class="mb-3">
                    <h6><i class="fas fa-envelope text-primary me-2"></i>Email Support</h6>
                    <p class="text-muted">support@sampark.railway.gov.in</p>
                </div>
                <div class="mb-3">
                    <h6><i class="fas fa-phone text-primary me-2"></i>Phone Support</h6>
                    <p class="text-muted">1800-XXX-XXXX</p>
                </div>
                <div class="mb-3">
                    <h6><i class="fas fa-clock text-primary me-2"></i>Support Hours</h6>
                    <p class="text-muted">Monday to Friday: 9:00 AM - 6:00 PM</p>
                </div>
            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true
    });
}

// Auto-focus appropriate field based on login type
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        document.getElementById('customerEmailOrPhone').focus();
    }, 100);
});
</script>

<style>
.btn-check:checked + .btn-apple-glass {
    background: var(--apple-blue);
    color: white;
    transform: none;
}

.btn-check:checked + .btn-apple-glass:hover {
    background: var(--apple-blue);
    color: white;
}

.position-relative .btn-link {
    border: none;
    background: none;
    color: var(--apple-gray);
    padding: 0;
    width: 40px;
    height: 40px;
}

.position-relative .btn-link:hover {
    color: var(--apple-blue);
}

.form-check-input:checked {
    background-color: var(--apple-blue);
    border-color: var(--apple-blue);
}

.form-check-input:focus {
    border-color: var(--apple-blue);
    box-shadow: 0 0 0 0.25rem rgba(0, 136, 204, 0.25);
}

/* Login type switcher animation */
.btn-check + .btn-apple-glass {
    transition: all 0.3s ease;
}

/* Mobile improvements */
@media (max-width: 576px) {
    .card-body {
        padding: 1.5rem !important;
    }
    
    .display-3 {
        font-size: 1.5rem !important;
    }
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
