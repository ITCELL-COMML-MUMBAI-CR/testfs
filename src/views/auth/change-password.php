<?php
// Capture the content
ob_start();
?>

<section class="py-apple-6">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">

                <!-- Change Password Card -->
                <div class="card-apple">
                    <div class="card-header text-center">
                        <h3 class="mb-0">
                            <i class="fas fa-key me-2 text-apple-blue"></i>
                            Change Password
                        </h3>
                        <p class="text-muted mb-0 mt-2">You must change your password to continue</p>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Password Requirements:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Minimum 8 characters</li>
                                <li>At least one uppercase letter</li>
                                <li>At least one lowercase letter</li>
                                <li>At least one number</li>
                                <li>At least one special character (!@#$%^&*)</li>
                            </ul>
                        </div>

                        <form id="changePasswordForm" action="<?= Config::getAppUrl() ?>/change-password" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                            <!-- Current Password -->
                            <div class="mb-4">
                                <label for="current_password" class="form-label-apple">
                                    Current Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-apple" id="current_password" name="current_password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Current password is required.</div>
                            </div>

                            <!-- New Password -->
                            <div class="mb-4">
                                <label for="new_password" class="form-label-apple">
                                    New Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-apple" id="new_password" name="new_password" required minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Must be at least 8 characters with uppercase, lowercase, number, and special character.</div>
                                <div class="invalid-feedback">Please enter a valid new password.</div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label-apple">
                                    Confirm New Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control form-control-apple" id="confirm_password" name="confirm_password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Password confirmation is required.</div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-apple-primary btn-lg">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security Notice -->
                <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        For your security, you cannot access the system until you change your password.
                    </small>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('changePasswordForm');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');

    // Password visibility toggles
    function setupPasswordToggle(inputId, toggleId) {
        const input = document.getElementById(inputId);
        const toggle = document.getElementById(toggleId);

        toggle.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }

    setupPasswordToggle('current_password', 'toggleCurrentPassword');
    setupPasswordToggle('new_password', 'toggleNewPassword');
    setupPasswordToggle('confirm_password', 'toggleConfirmPassword');

    // Form validation
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }

        // Check if passwords match
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Passwords don't match");
            event.preventDefault();
            event.stopPropagation();
        } else {
            confirmPassword.setCustomValidity("");
        }

        // Password strength validation
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passwordRegex.test(newPassword.value)) {
            newPassword.setCustomValidity("Password must contain at least 8 characters with uppercase, lowercase, number, and special character");
            event.preventDefault();
            event.stopPropagation();
        } else {
            newPassword.setCustomValidity("");
        }

        form.classList.add('was-validated');
    });

    // Real-time password confirmation validation
    confirmPassword.addEventListener('input', function() {
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Passwords don't match");
        } else {
            confirmPassword.setCustomValidity("");
        }
    });

    // Real-time password strength validation
    newPassword.addEventListener('input', function() {
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passwordRegex.test(newPassword.value)) {
            newPassword.setCustomValidity("Password must contain at least 8 characters with uppercase, lowercase, number, and special character");
        } else {
            newPassword.setCustomValidity("");
        }
    });
});
</script>

<style>
.card-apple {
    border: none;
    border-radius: var(--apple-radius);
    background: var(--apple-white);
    backdrop-filter: blur(20px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
}

.card-header {
    background: var(--apple-off-white);
    border-bottom: 1px solid rgba(151, 151, 151, 0.1);
    border-radius: var(--apple-radius) var(--apple-radius) 0 0;
    padding: 2rem 2rem 1.5rem;
}

.alert-info {
    background-color: rgba(0, 122, 255, 0.05);
    border: 1px solid rgba(0, 122, 255, 0.1);
    color: var(--apple-blue);
}

.btn-apple-primary {
    background: var(--apple-blue);
    border: none;
    border-radius: var(--apple-radius-small);
    color: white;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
}

.btn-apple-primary:hover {
    background: var(--apple-blue-dark);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 122, 255, 0.3);
}

.form-control-apple {
    border: 1px solid rgba(151, 151, 151, 0.3);
    border-radius: var(--apple-radius-small);
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control-apple:focus {
    border-color: var(--apple-blue);
    box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
    outline: none;
}

.form-label-apple {
    font-weight: 500;
    color: var(--apple-black);
    margin-bottom: 0.5rem;
}

.btn-outline-secondary {
    border-color: rgba(151, 151, 151, 0.3);
    color: rgba(151, 151, 151, 0.8);
}

.btn-outline-secondary:hover {
    background-color: rgba(151, 151, 151, 0.1);
    border-color: rgba(151, 151, 151, 0.5);
}

:root {
    --apple-blue: #007AFF;
    --apple-blue-dark: #0056CC;
    --apple-white: #FFFFFF;
    --apple-off-white: #F8F9FA;
    --apple-black: #1D1D1F;
    --apple-radius: 12px;
    --apple-radius-small: 8px;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .card-header {
        padding: 1.5rem 1rem 1rem;
    }

    .card-body {
        padding: 1.5rem 1rem;
    }
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>