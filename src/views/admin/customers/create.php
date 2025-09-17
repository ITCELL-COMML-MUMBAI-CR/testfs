<?php
// Capture the content
ob_start();
?>

<!-- Admin Create Customer -->
<section class="py-apple-6">
    <div class="container-xl">

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div>
                        <nav aria-label="breadcrumb" class="mb-2">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="<?= Config::getAppUrl() ?>/admin/dashboard" class="text-decoration-none">
                                        <i class="fas fa-home"></i> Admin
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="<?= Config::getAppUrl() ?>/admin/customers" class="text-decoration-none">
                                        Customers
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Add New Customer</li>
                            </ol>
                        </nav>
                        <h1 class="display-3 mb-2">Add New Customer</h1>
                        <p class="text-muted mb-0">Create a new customer account in the system</p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <a href="<?= Config::getAppUrl() ?>/admin/customers" class="btn btn-apple-glass">
                            <i class="fas fa-arrow-left me-2"></i>Back to Customers
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Customer Form -->
        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                <div class="card-apple">
                    <div class="card-body">
                        <form id="createCustomerForm" method="POST" action="<?= Config::getAppUrl() ?>/admin/customers/create">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                            <!-- Personal Information -->
                            <div class="mb-4">
                                <h5 class="fw-bold text-apple-black mb-3">
                                    <i class="fas fa-user me-2 text-apple-blue"></i>
                                    Personal Information
                                </h5>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label-apple">
                                            Full Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-apple" id="name" name="name" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label-apple">
                                            Email Address <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" class="form-control form-control-apple" id="email" name="email" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="mobile" class="form-label-apple">
                                            Mobile Number <span class="text-danger">*</span>
                                        </label>
                                        <input type="tel" class="form-control form-control-apple" id="mobile" name="mobile" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="customer_type" class="form-label-apple">
                                            Customer Type
                                        </label>
                                        <select class="form-control form-control-apple" id="customer_type" name="customer_type">
                                            <option value="individual">Individual</option>
                                            <option value="corporate">Corporate</option>
                                            <option value="government">Government</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Organization Information -->
                            <div class="mb-4">
                                <h5 class="fw-bold text-apple-black mb-3">
                                    <i class="fas fa-building me-2 text-apple-blue"></i>
                                    Organization Information
                                </h5>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="company_name" class="form-label-apple">
                                            Company Name
                                        </label>
                                        <input type="text" class="form-control form-control-apple" id="company_name" name="company_name">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="designation" class="form-label-apple">
                                            Designation
                                        </label>
                                        <input type="text" class="form-control form-control-apple" id="designation" name="designation">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="gstin" class="form-label-apple">
                                            GSTIN
                                        </label>
                                        <input type="text" class="form-control form-control-apple" id="gstin" name="gstin" placeholder="e.g., 22AAAAA0000A1Z5">
                                    </div>
                                </div>
                            </div>

                            <!-- Location Information -->
                            <div class="mb-4">
                                <h5 class="fw-bold text-apple-black mb-3">
                                    <i class="fas fa-map-marker-alt me-2 text-apple-blue"></i>
                                    Location Information
                                </h5>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="division" class="form-label-apple">
                                            Division <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-control form-control-apple" id="division" name="division" required>
                                            <option value="">Select Division</option>
                                            <?php foreach ($divisions as $division): ?>
                                                <option value="<?= htmlspecialchars($division['division']) ?>">
                                                    <?= htmlspecialchars($division['division']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="zone" class="form-label-apple">
                                            Zone
                                        </label>
                                        <input type="text" class="form-control form-control-apple" id="zone" name="zone">
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                                <a href="<?= Config::getAppUrl() ?>/admin/customers" class="btn btn-apple-glass">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-apple-primary">
                                    <i class="fas fa-user-plus me-2"></i>Create Customer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="row justify-content-center mt-4">
            <div class="col-xl-8 col-lg-10">
                <div class="card-apple-glass">
                    <div class="card-body py-3">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-1">
                                    <i class="fas fa-info-circle text-apple-blue me-2"></i>
                                    Customer Creation Guidelines
                                </h6>
                                <div class="d-flex flex-wrap gap-3 mt-2">
                                    <small><i class="fas fa-check text-success me-1"></i>Email must be unique</small>
                                    <small><i class="fas fa-check text-success me-1"></i>Mobile number is required</small>
                                    <small><i class="fas fa-check text-success me-1"></i>Customer ID will be auto-generated</small>
                                    <small><i class="fas fa-check text-success me-1"></i>Account will be active immediately</small>
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
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createCustomerForm');
    const customerTypeSelect = document.getElementById('customer_type');
    const companyNameField = document.getElementById('company_name');
    const designationField = document.getElementById('designation');

    // Show/hide company fields based on customer type
    function toggleCompanyFields() {
        const isIndividual = customerTypeSelect.value === 'individual';
        const companyRow = companyNameField.closest('.row');

        if (isIndividual) {
            companyNameField.required = false;
            designationField.required = false;
        } else {
            companyNameField.required = true;
            designationField.required = false; // Designation is optional even for corporate
        }
    }

    customerTypeSelect.addEventListener('change', toggleCompanyFields);
    toggleCompanyFields(); // Initialize

    // Form validation
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        // Email validation
        const emailField = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailField.value && !emailRegex.test(emailField.value)) {
            emailField.classList.add('is-invalid');
            isValid = false;
        }

        // Mobile validation (basic check for 10 digits)
        const mobileField = document.getElementById('mobile');
        const mobileRegex = /^[0-9]{10}$/;
        if (mobileField.value && !mobileRegex.test(mobileField.value.replace(/\D/g, ''))) {
            mobileField.classList.add('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                title: 'Validation Error',
                text: 'Please correct the highlighted fields',
                icon: 'error'
            });
        }
    });

    // Remove validation errors on input
    form.addEventListener('input', function(e) {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
        }
    });

    // Format mobile number
    document.getElementById('mobile').addEventListener('input', function(e) {
        // Remove non-digits
        let value = e.target.value.replace(/\D/g, '');
        // Limit to 10 digits
        if (value.length > 10) {
            value = value.slice(0, 10);
        }
        e.target.value = value;
    });

    // Format GSTIN
    document.getElementById('gstin').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });
});
</script>

<style>
/* Form styling */
.form-control-apple:focus {
    border-color: var(--apple-blue);
    box-shadow: 0 0 0 0.2rem rgba(0, 122, 255, 0.25);
}

.form-control.is-invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 1.4 1.4 1.4-1.4'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.form-control.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

/* Section dividers */
.border-top {
    border-top: 1px solid rgba(151, 151, 151, 0.1) !important;
}

/* Help section styling */
.card-apple-glass {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .display-3 {
        font-size: 1.75rem;
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