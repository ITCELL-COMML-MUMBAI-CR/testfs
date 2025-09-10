<?php
// Capture the content
ob_start();
?>

<!-- Admin Edit Customer -->
<section class="py-apple-6">
    <div class="container-xl">
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?= Config::APP_URL ?>/admin/customers">Customers</a></li>
                                <li class="breadcrumb-item"><a href="<?= Config::APP_URL ?>/admin/customers/<?= $customer['customer_id'] ?>"><?= htmlspecialchars($customer['name']) ?></a></li>
                                <li class="breadcrumb-item active" aria-current="page">Edit</li>
                            </ol>
                        </nav>
                        <h1 class="display-3 mb-2">Edit Customer</h1>
                        <p class="text-muted mb-0">Update customer information and settings</p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <a href="<?= Config::APP_URL ?>/admin/customers/<?= $customer['customer_id'] ?>" class="btn btn-apple-glass me-2">
                            <i class="fas fa-eye me-2"></i>View Details
                        </a>
                        <a href="<?= Config::APP_URL ?>/admin/customers" class="btn btn-apple-glass">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card-apple">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Customer Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="editCustomerForm" method="POST" action="<?= Config::APP_URL ?>/admin/customers/<?= $customer['customer_id'] ?>/update">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            
                            <div class="row g-3">
                                <!-- Customer ID (Read-only) -->
                                <div class="col-md-6">
                                    <label for="customer_id" class="form-label-apple">Customer ID</label>
                                    <input type="text" class="form-control form-control-apple" id="customer_id" 
                                           value="<?= htmlspecialchars($customer['customer_id']) ?>" readonly>
                                    <div class="form-text">Customer ID cannot be changed</div>
                                </div>
                                
                                <!-- Status -->
                                <div class="col-md-6">
                                    <label for="status" class="form-label-apple">Status <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-apple" id="status" name="status" required>
                                        <?php foreach ($status_options as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= $customer['status'] === $value ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Full Name -->
                                <div class="col-md-6">
                                    <label for="name" class="form-label-apple">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-apple" id="name" name="name" 
                                           value="<?= htmlspecialchars($customer['name']) ?>" required maxlength="100">
                                </div>
                                
                                <!-- Customer Type -->
                                <div class="col-md-6">
                                    <label for="customer_type" class="form-label-apple">Customer Type <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-apple" id="customer_type" name="customer_type" required>
                                        <?php foreach ($customer_types as $value => $label): ?>
                                            <option value="<?= $value ?>" <?= $customer['customer_type'] === $value ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Email -->
                                <div class="col-md-6">
                                    <label for="email" class="form-label-apple">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control form-control-apple" id="email" name="email" 
                                           value="<?= htmlspecialchars($customer['email']) ?>" required maxlength="100">
                                </div>
                                
                                <!-- Mobile -->
                                <div class="col-md-6">
                                    <label for="mobile" class="form-label-apple">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control form-control-apple" id="mobile" name="mobile" 
                                           value="<?= htmlspecialchars($customer['mobile']) ?>" required maxlength="15">
                                </div>
                                
                                <!-- Company Name -->
                                <div class="col-md-6">
                                    <label for="company_name" class="form-label-apple">Company Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-apple" id="company_name" name="company_name" 
                                           value="<?= htmlspecialchars($customer['company_name']) ?>" required maxlength="150">
                                </div>
                                
                                <!-- Designation -->
                                <div class="col-md-6">
                                    <label for="designation" class="form-label-apple">Designation</label>
                                    <input type="text" class="form-control form-control-apple" id="designation" name="designation" 
                                           value="<?= htmlspecialchars($customer['designation']) ?>" maxlength="100">
                                </div>
                                
                                <!-- GSTIN -->
                                <div class="col-md-6">
                                    <label for="gstin" class="form-label-apple">GSTIN</label>
                                    <input type="text" class="form-control form-control-apple" id="gstin" name="gstin" 
                                           value="<?= htmlspecialchars($customer['gstin']) ?>" maxlength="15">
                                </div>
                                
                                <!-- Division -->
                                <div class="col-md-6">
                                    <label for="division" class="form-label-apple">Division <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-apple" id="division" name="division" required>
                                        <option value="">Select Division</option>
                                        <?php foreach ($divisions as $division): ?>
                                            <option value="<?= htmlspecialchars($division['division']) ?>" 
                                                    <?= $customer['division'] === $division['division'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($division['division']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Zone -->
                                <div class="col-md-6">
                                    <label for="zone" class="form-label-apple">Zone <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-apple" id="zone" name="zone" 
                                           value="<?= htmlspecialchars($customer['zone']) ?>" required maxlength="50">
                                </div>
                                
                                <!-- Registration Date (Read-only) -->
                                <div class="col-md-6">
                                    <label for="created_at" class="form-label-apple">Registration Date</label>
                                    <input type="text" class="form-control form-control-apple" id="created_at" 
                                           value="<?= date('M d, Y H:i', strtotime($customer['created_at'])) ?>" readonly>
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?= Config::APP_URL ?>/admin/customers/<?= $customer['customer_id'] ?>" class="btn btn-apple-glass">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-apple-primary" id="saveBtn">
                                            <i class="fas fa-save me-2"></i>Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editCustomerForm');
    const saveBtn = document.getElementById('saveBtn');
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Disable submit button
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        
        // Get form data
        const formData = new FormData(form);
        
        // Convert to JSON
        const jsonData = {};
        for (let [key, value] of formData.entries()) {
            jsonData[key] = value;
        }
        
        // Send request
        fetch(form.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify(jsonData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = `${APP_URL}/admin/customers/${jsonData.customer_id || '<?= $customer['customer_id'] ?>'}`;
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to update customer',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred while updating the customer',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        })
        .finally(() => {
            // Re-enable submit button
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save Changes';
        });
    });
    
    // Real-time validation
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', validateField);
        field.addEventListener('input', validateField);
    });
    
    function validateField(e) {
        const field = e.target;
        const value = field.value.trim();
        
        // Remove existing validation classes
        field.classList.remove('is-valid', 'is-invalid');
        
        if (field.hasAttribute('required') && !value) {
            field.classList.add('is-invalid');
            return false;
        }
        
        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                field.classList.add('is-invalid');
                return false;
            }
        }
        
        // Mobile validation
        if (field.type === 'tel' && value) {
            const mobileRegex = /^[0-9]{10}$/;
            if (!mobileRegex.test(value)) {
                field.classList.add('is-invalid');
                return false;
            }
        }
        
        field.classList.add('is-valid');
        return true;
    }
    
    // Auto-populate zone based on division
    const divisionSelect = document.getElementById('division');
    const zoneInput = document.getElementById('zone');
    
    divisionSelect.addEventListener('change', function() {
        if (this.value) {
            // You can implement AJAX call to get zone based on division
            // For now, we'll just clear the zone field
            zoneInput.value = '';
        }
    });
});
</script>

<style>
/* Form styling */
.form-label-apple {
    font-weight: 600;
    color: var(--apple-black);
    margin-bottom: 0.5rem;
}

.form-control-apple {
    border: 1px solid rgba(151, 151, 151, 0.2);
    border-radius: var(--apple-radius-small);
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.form-control-apple:focus {
    border-color: var(--apple-blue);
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-control-apple:readonly {
    background-color: var(--apple-off-white);
    color: var(--apple-gray);
}

/* Validation styling */
.is-valid {
    border-color: #28a745 !important;
}

.is-invalid {
    border-color: #dc3545 !important;
}

/* Card styling */
.card-apple {
    border: 1px solid rgba(151, 151, 151, 0.1);
    border-radius: var(--apple-radius);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.card-header {
    background-color: var(--apple-off-white);
    border-bottom: 1px solid rgba(151, 151, 151, 0.1);
    padding: 1rem 1.5rem;
}

.card-body {
    padding: 1.5rem;
}

/* Button styling */
.btn {
    border-radius: var(--apple-radius-small);
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    transition: all 0.2s ease;
}

.btn-apple-primary {
    background-color: var(--apple-blue);
    border-color: var(--apple-blue);
    color: white;
}

.btn-apple-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

.btn-apple-glass {
    background-color: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(151, 151, 151, 0.2);
    color: var(--apple-black);
}

.btn-apple-glass:hover {
    background-color: rgba(255, 255, 255, 0.9);
    border-color: var(--apple-blue);
    color: var(--apple-blue);
}

/* Mobile responsive */
@media (max-width: 768px) {
    .display-3 {
        font-size: 1.75rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
