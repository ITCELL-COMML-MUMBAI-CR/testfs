<?php
/**
 * Admin Categories Management View - SAMPARK
 * Manage complaint categories and subcategories
 */
$is_logged_in = true;
$user_role = $user['role'] ?? 'admin';
$user_name = $user['name'] ?? 'Administrator';
?>

<div class="container-xl py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <div class="d-flex align-items-center">
                <div class="bg-apple-blue rounded-3 p-3 me-3">
                    <i class="fas fa-tags text-white fa-lg"></i>
                </div>
                <div>
                    <h1 class="h3 mb-1 fw-semibold">Category Management</h1>
                    <p class="text-muted mb-0">Manage complaint categories and subcategories</p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <button class="btn btn-apple-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-plus me-2"></i>Add Category
            </button>
        </div>
    </div>

    <!-- Categories Overview -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-apple text-center">
                <div class="card-body">
                    <div class="text-primary mb-2">
                        <i class="fas fa-layer-group fa-2x"></i>
                    </div>
                    <h3 class="mb-1"><?= count($categories ?? []) ?></h3>
                    <p class="text-muted mb-0">Total Categories</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-apple text-center">
                <div class="card-body">
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h3 class="mb-1"><?= array_sum(array_column($categories ?? [], 'active_count')) ?></h3>
                    <p class="text-muted mb-0">Active Categories</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-apple text-center">
                <div class="card-body">
                    <div class="text-info mb-2">
                        <i class="fas fa-sitemap fa-2x"></i>
                    </div>
                    <h3 class="mb-1"><?= array_sum(array_column($categories ?? [], 'subtype_count')) ?></h3>
                    <p class="text-muted mb-0">Total Subtypes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-apple text-center">
                <div class="card-body">
                    <div class="text-warning mb-2">
                        <i class="fas fa-ticket-alt fa-2x"></i>
                    </div>
                    <h3 class="mb-1"><?= array_sum(array_column($categories ?? [], 'ticket_count')) ?></h3>
                    <p class="text-muted mb-0">Total Tickets</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories List -->
    <div class="card card-apple">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Categories & Subtypes
            </h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-apple-secondary" onclick="exportCategories()">
                    <i class="fas fa-download me-1"></i>Export
                </button>
                <button class="btn btn-sm btn-apple-primary" onclick="refreshCategories()">
                    <i class="fas fa-sync-alt me-1"></i>Refresh
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">Category</th>
                            <th class="border-0">Type</th>
                            <th class="border-0">Subtypes</th>
                            <th class="border-0">Tickets</th>
                            <th class="border-0">Status</th>
                            <th class="border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-tags fa-3x mb-3"></i>
                                    <h5>No categories found</h5>
                                    <p>Click "Add Category" to create your first complaint category.</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($category['category']) ?></div>
                                    <?php if ($category['description']): ?>
                                    <small class="text-muted"><?= htmlspecialchars($category['description']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= ucfirst($category['type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($category['subtype_count'] > 0): ?>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="viewSubtypes(<?= $category['category_id'] ?>)">
                                            <i class="fas fa-sitemap me-1"></i>
                                            <?= $category['subtype_count'] ?> subtypes
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">No subtypes</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($category['ticket_count'] > 0): ?>
                                        <a href="<?= Config::APP_URL ?>/admin/tickets?category=<?= $category['category_id'] ?>" 
                                           class="text-decoration-none">
                                            <span class="badge bg-info"><?= $category['ticket_count'] ?> tickets</span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No tickets</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               <?= ($category['is_active'] ?? true) ? 'checked' : '' ?>
                                               onchange="toggleCategoryStatus(<?= $category['category_id'] ?>)">
                                        <label class="form-check-label small">
                                            <?= ($category['is_active'] ?? true) ? 'Active' : 'Inactive' ?>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-apple-primary" 
                                                onclick="editCategory(<?= $category['category_id'] ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-apple-secondary" 
                                                onclick="addSubtype(<?= $category['category_id'] ?>)" title="Add Subtype">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button class="btn btn-sm btn-apple-danger" 
                                                onclick="deleteCategory(<?= $category['category_id'] ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCategoryForm" onsubmit="saveCategory(event)">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-apple">Category Name *</label>
                        <input type="text" class="form-control-apple" name="category" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Type *</label>
                        <select class="form-control-apple" name="type" required>
                            <option value="">Select Type</option>
                            <option value="complaint">Complaint</option>
                            <option value="query">Query</option>
                            <option value="suggestion">Suggestion</option>
                            <option value="appreciation">Appreciation</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Description</label>
                        <textarea class="form-control-apple" name="description" rows="3" 
                                  placeholder="Brief description of this category..."></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" checked>
                            <label class="form-check-label">
                                Active (visible to customers)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-apple-primary">
                        <i class="fas fa-save me-2"></i>Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCategoryForm" onsubmit="updateCategory(event)">
                <input type="hidden" name="category_id" id="editCategoryId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-apple">Category Name *</label>
                        <input type="text" class="form-control-apple" name="category" id="editCategoryName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Type *</label>
                        <select class="form-control-apple" name="type" id="editCategoryType" required>
                            <option value="complaint">Complaint</option>
                            <option value="query">Query</option>
                            <option value="suggestion">Suggestion</option>
                            <option value="appreciation">Appreciation</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Description</label>
                        <textarea class="form-control-apple" name="description" id="editCategoryDescription" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editCategoryActive">
                            <label class="form-check-label">
                                Active (visible to customers)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-apple-primary">
                        <i class="fas fa-save me-2"></i>Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Subtype Modal -->
<div class="modal fade" id="addSubtypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Subtype</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSubtypeForm" onsubmit="saveSubtype(event)">
                <input type="hidden" name="category_id" id="subtypeCategoryId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-apple">Parent Category</label>
                        <input type="text" class="form-control-apple" id="subtypeCategoryName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Subtype Name *</label>
                        <input type="text" class="form-control-apple" name="subtype" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Description</label>
                        <textarea class="form-control-apple" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-apple-primary">
                        <i class="fas fa-save me-2"></i>Save Subtype
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Subtypes Modal -->
<div class="modal fade" id="subtypesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Subtypes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="subtypesContent">
                    <!-- Subtypes will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Category management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize page
});

async function saveCategory(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/admin/categories`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            Swal.fire('Success', 'Category added successfully', 'success').then(() => {
                location.reload();
            });
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
        Swal.fire('Error', 'Failed to add category', 'error');
    }
}

async function editCategory(categoryId) {
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/api/categories/${categoryId}`);
        const category = await response.json();
        hideLoading();
        
        if (category) {
            document.getElementById('editCategoryId').value = category.category_id;
            document.getElementById('editCategoryName').value = category.category;
            document.getElementById('editCategoryType').value = category.type;
            document.getElementById('editCategoryDescription').value = category.description || '';
            document.getElementById('editCategoryActive').checked = category.is_active;
            
            new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
        }
    } catch (error) {
        hideLoading();
        Swal.fire('Error', 'Failed to load category data', 'error');
    }
}

async function updateCategory(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('csrf_token', CSRF_TOKEN);
    const categoryId = formData.get('category_id');
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/admin/categories/${categoryId}/edit`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            Swal.fire('Success', 'Category updated successfully', 'success').then(() => {
                location.reload();
            });
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
        Swal.fire('Error', 'Failed to update category', 'error');
    }
}

async function deleteCategory(categoryId) {
    const result = await Swal.fire({
        title: 'Delete Category?',
        text: 'This action cannot be undone. All associated subtypes will also be deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel'
    });
    
    if (result.isConfirmed) {
        try {
            showLoading();
            const response = await fetch(`${APP_URL}/admin/categories/${categoryId}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    csrf_token: CSRF_TOKEN
                })
            });
            
            const apiResult = await response.json();
            hideLoading();
            
            if (apiResult.success) {
                Swal.fire('Deleted', 'Category deleted successfully', 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', apiResult.message, 'error');
            }
        } catch (error) {
            hideLoading();
            Swal.fire('Error', 'Failed to delete category', 'error');
        }
    }
}

async function toggleCategoryStatus(categoryId) {
    try {
        const response = await fetch(`${APP_URL}/admin/categories/${categoryId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                csrf_token: CSRF_TOKEN
            })
        });
        
        const result = await response.json();
        
        if (!result.success) {
            Swal.fire('Error', result.message || 'Failed to update status', 'error');
            // Revert the toggle
            location.reload();
        } else {
            Swal.fire({
                title: 'Status Updated',
                text: result.message,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    } catch (error) {
        Swal.fire('Error', 'Failed to update category status', 'error');
        location.reload();
    }
}

function addSubtype(categoryId) {
    // Get category name for display
    const categoryRow = event.target.closest('tr');
    const categoryName = categoryRow.querySelector('.fw-semibold').textContent;
    
    document.getElementById('subtypeCategoryId').value = categoryId;
    document.getElementById('subtypeCategoryName').value = categoryName;
    
    new bootstrap.Modal(document.getElementById('addSubtypeModal')).show();
}

async function saveSubtype(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/admin/categories/subtypes`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            Swal.fire('Success', 'Subtype added successfully', 'success').then(() => {
                bootstrap.Modal.getInstance(document.getElementById('addSubtypeModal')).hide();
                location.reload();
            });
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
        Swal.fire('Error', 'Failed to add subtype', 'error');
    }
}

async function viewSubtypes(categoryId) {
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/api/categories/${categoryId}/subtypes`);
        const subtypes = await response.json();
        hideLoading();
        
        if (subtypes) {
            let subtypesHtml = `
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Subtype Name</th>
                                <th>Description</th>
                                <th>Tickets</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            subtypes.forEach(subtype => {
                subtypesHtml += `
                    <tr>
                        <td class="fw-semibold">${subtype.subtype}</td>
                        <td><small class="text-muted">${subtype.description || 'No description'}</small></td>
                        <td><span class="badge bg-info">${subtype.ticket_count || 0}</span></td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="deleteSubtype(${subtype.subtype_id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            subtypesHtml += '</tbody></table></div>';
            
            document.getElementById('subtypesContent').innerHTML = subtypesHtml;
            new bootstrap.Modal(document.getElementById('subtypesModal')).show();
        }
    } catch (error) {
        hideLoading();
        Swal.fire('Error', 'Failed to load subtypes', 'error');
    }
}

async function deleteSubtype(subtypeId) {
    const result = await Swal.fire({
        title: 'Delete Subtype?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Delete'
    });
    
    if (result.isConfirmed) {
        try {
            showLoading();
            const response = await fetch(`${APP_URL}/admin/categories/subtypes/${subtypeId}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    csrf_token: CSRF_TOKEN
                })
            });
            
            const apiResult = await response.json();
            hideLoading();
            
            if (apiResult.success) {
                Swal.fire('Deleted', 'Subtype deleted successfully', 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', apiResult.message, 'error');
            }
        } catch (error) {
            hideLoading();
            Swal.fire('Error', 'Failed to delete subtype', 'error');
        }
    }
}

function exportCategories() {
    window.location.href = `${APP_URL}/admin/categories/export`;
}

function refreshCategories() {
    location.reload();
}

// Utility functions
function showLoading() {
    document.getElementById('loadingOverlay')?.classList.remove('d-none');
}

function hideLoading() {
    document.getElementById('loadingOverlay')?.classList.add('d-none');
}
</script>

<style>
/* Categories page specific styles */
.card-apple {
    transition: all 0.2s ease;
}

.card-apple:hover {
    transform: translateY(-2px);
    box-shadow: var(--apple-shadow-medium);
}

/* Switch styling */
.form-switch .form-check-input:checked {
    background-color: var(--apple-primary);
    border-color: var(--apple-primary);
}

/* Button group styling */
.btn-group .btn {
    border: none !important;
}

/* Table enhancements */
.table-hover tbody tr:hover {
    background-color: rgba(var(--apple-primary-rgb), 0.04);
}

/* Badge styling */
.badge {
    font-size: 0.75em;
}

/* Modal enhancements */
.modal-dialog {
    transition: all 0.3s ease;
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .card-body {
        padding: 1rem 0.75rem;
    }
}
</style>