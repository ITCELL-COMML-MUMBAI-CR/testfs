<?php
/**
 * Admin Categories Management View - SAMPARK
 * Manage complaint categories and subcategories
 */
ob_start();
?>

<div class="container-xl py-4">
    <!-- Page Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <div class="d-flex align-items-center">
                <div class="bg-apple-blue rounded-3 p-3 me-3">
                    <i class="fas fa-tags text-dark fa-lg"></i>
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
    <div class="row g-4 mb-4 justify-content-center">
        <div class="col-md-3">
            <div class="card card-apple text-center">
                <div class="card-body">
                    <div class="text-primary mb-2">
                        <i class="fas fa-layer-group fa-2x"></i>
                    </div>
                    <h3 class="mb-1"><?= count(array_unique(array_column($categories ?? [], 'category'))) ?></h3>
                    <p class="text-muted mb-0">Total Categories</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-apple text-center">
                <div class="card-body">
                    <div class="text-success mb-2">
                        <i class="fas fa-tags fa-2x"></i>
                    </div>
                    <h3 class="mb-1"><?= count(array_unique(array_column($categories ?? [], 'type'))) ?></h3>
                    <p class="text-muted mb-0">Total Types</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-apple text-center">
                <div class="card-body">
                    <div class="text-info mb-2">
                        <i class="fas fa-sitemap fa-2x"></i>
                    </div>
                    <h3 class="mb-1"><?= count(array_filter(array_column($categories ?? [], 'subtype'))) ?></h3>
                    <p class="text-muted mb-0">Total Subtypes</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories List -->
    <div class="card card-apple">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Categories & Types
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="categoriesTable" class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">Category</th>
                            <th class="border-0">Type</th>
                            <th class="border-0">Subtype</th>
                            <th class="border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
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
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= ucfirst($category['type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($category['subtype']): ?>
                                        <span class="badge bg-primary"><?= htmlspecialchars($category['subtype']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">No subtype</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-apple-primary" 
                                                onclick="editCategory(<?= $category['category_id'] ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
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
                        <label class="form-label-apple">Category *</label>
                        <div class="d-flex gap-2">
                            <select class="form-control-apple" id="categorySelect" name="category_select" onchange="handleCategorySelection()">
                                <option value="">Select existing category</option>
                                <!-- Categories will be loaded here -->
                            </select>
                            <input type="text" class="form-control-apple" id="categoryInput" name="category" placeholder="Or enter new category" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Type *</label>
                        <div class="d-flex gap-2">
                            <select class="form-control-apple" id="typeSelect" name="type_select" onchange="handleTypeSelection()">
                                <option value="">Select existing type</option>
                                <!-- Types will be loaded here -->
                            </select>
                            <input type="text" class="form-control-apple" id="typeInput" name="type" placeholder="Or enter new type" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Subtype *</label>
                        <input type="text" class="form-control-apple" name="subtype" placeholder="Enter subtype" required>
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
                        <label class="form-label-apple">Category *</label>
                        <div class="d-flex gap-2">
                            <select class="form-control-apple" id="editCategorySelect" name="category_select" onchange="handleEditCategorySelection()">
                                <option value="">Select existing category</option>
                                <!-- Categories will be loaded here -->
                            </select>
                            <input type="text" class="form-control-apple" name="category" id="editCategoryName" placeholder="Or enter new category" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Type *</label>
                        <div class="d-flex gap-2">
                            <select class="form-control-apple" id="editTypeSelect" name="type_select" onchange="handleEditTypeSelection()">
                                <option value="">Select existing type</option>
                                <!-- Types will be loaded here -->
                            </select>
                            <input type="text" class="form-control-apple" name="type" id="editCategoryType" placeholder="Or enter new type" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-apple">Subtype *</label>
                        <input type="text" class="form-control-apple" name="subtype" id="editCategorySubtype" placeholder="Enter subtype" required>
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


<!-- DataTables Buttons Extension -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
// Category management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    initializeCategoriesTable();

    // Load existing categories and types for dropdowns
    loadExistingCategoriesAndTypes();
});

function initializeCategoriesTable() {
    $('#categoriesTable').DataTable({
        "responsive": true,
        "pageLength": 25,
        "order": [[0, "asc"]],
        "dom": 'Bfrtip',
        "buttons": [
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Categories List',
                exportOptions: {
                    columns: [0, 1, 2]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Categories List',
                exportOptions: {
                    columns: [0, 1, 2]
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-info btn-sm',
                title: 'Categories List',
                exportOptions: {
                    columns: [0, 1, 2]
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-secondary btn-sm',
                title: 'Categories List',
                exportOptions: {
                    columns: [0, 1, 2]
                }
            }
        ],
        "language": {
            "search": "Search categories:",
            "lengthMenu": "Show _MENU_ entries per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ categories",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "columnDefs": [
            {
                "targets": -1,
                "orderable": false
            }
        ]
    });
}

async function loadExistingCategoriesAndTypes() {
    try {
        const response = await fetch(`${APP_URL}/api/categories/distinct`);
        const data = await response.json();

        if (data.success) {
            // Populate category dropdowns
            const categorySelects = document.querySelectorAll('#categorySelect, #editCategorySelect');
            categorySelects.forEach(select => {
                select.innerHTML = '<option value="">Select existing category</option>';
                data.categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat;
                    option.textContent = cat;
                    select.appendChild(option);
                });
            });

            // Populate type dropdowns
            const typeSelects = document.querySelectorAll('#typeSelect, #editTypeSelect');
            typeSelects.forEach(select => {
                select.innerHTML = '<option value="">Select existing type</option>';
                data.types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type;
                    option.textContent = type;
                    select.appendChild(option);
                });
            });
        }
    } catch (error) {
        console.error('Failed to load categories and types:', error);
    }
}

function handleCategorySelection() {
    const select = document.getElementById('categorySelect');
    const input = document.getElementById('categoryInput');
    if (select.value) {
        input.value = select.value;
        input.readOnly = true;
    } else {
        input.value = '';
        input.readOnly = false;
    }
}

function handleTypeSelection() {
    const select = document.getElementById('typeSelect');
    const input = document.getElementById('typeInput');
    if (select.value) {
        input.value = select.value;
        input.readOnly = true;
    } else {
        input.value = '';
        input.readOnly = false;
    }
}

function handleEditCategorySelection() {
    const select = document.getElementById('editCategorySelect');
    const input = document.getElementById('editCategoryName');
    if (select.value) {
        input.value = select.value;
        input.readOnly = true;
    } else {
        input.readOnly = false;
    }
}

function handleEditTypeSelection() {
    const select = document.getElementById('editTypeSelect');
    const input = document.getElementById('editCategoryType');
    if (select.value) {
        input.value = select.value;
        input.readOnly = true;
    } else {
        input.readOnly = false;
    }
}

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
                document.getElementById('addCategoryForm').reset();
                bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')).hide();
                refreshCategoriesTable();
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
            document.getElementById('editCategorySubtype').value = category.subtype || '';

            // Reset the dropdowns
            document.getElementById('editCategorySelect').value = '';
            document.getElementById('editTypeSelect').value = '';

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
                bootstrap.Modal.getInstance(document.getElementById('editCategoryModal')).hide();
                refreshCategoriesTable();
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
            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN);

            const response = await fetch(`${APP_URL}/admin/categories/${categoryId}/delete`, {
                method: 'POST',
                body: formData
            });
            
            const apiResult = await response.json();
            hideLoading();
            
            if (apiResult.success) {
                Swal.fire('Deleted', 'Category deleted successfully', 'success').then(() => {
                    refreshCategoriesTable();
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




function refreshCategories() {
    refreshCategoriesTable();
}

async function refreshCategoriesTable() {
    try {
        showLoading();

        // Destroy existing DataTable
        if ($.fn.DataTable.isDataTable('#categoriesTable')) {
            $('#categoriesTable').DataTable().destroy();
        }

        const response = await fetch(`${APP_URL}/api/categories/table-data`);
        const data = await response.json();
        hideLoading();

        if (data.success) {
            // Update the table body with new data
            const tbody = document.querySelector('#categoriesTable tbody');
            tbody.innerHTML = data.html;

            // Reinitialize DataTable
            initializeCategoriesTable();

            // Reload dropdowns with fresh data
            loadExistingCategoriesAndTypes();
        } else {
            // Fallback to full page reload
            location.reload();
        }
    } catch (error) {
        hideLoading();
        // Fallback to full page reload
        location.reload();
    }
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
    background-color: #28a745;
    border-color: #28a745;
}

/* Button group styling */
.btn-group .btn {
    border: none !important;
}

/* Table enhancements */
.table-hover tbody tr:hover {
    background-color: rgba(var(--apple-primary-rgb), 0.04);
}

#categoriesTable {
    margin: 20px;
}

#categoriesTable th {
    padding: 15px 12px;
    font-weight: 600;
}

#categoriesTable td {
    padding: 12px;
    vertical-align: middle;
}

/* Badge styling */
.badge {
    font-size: 0.75em;
}

/* Modal enhancements */
.modal-dialog {
    transition: all 0.3s ease;
}

.modal-body .d-flex {
    gap: 8px;
}

.modal-body .d-flex .form-control-apple {
    flex: 1;
    min-width: 0;
}

.modal-body .mb-3 {
    margin-bottom: 1rem;
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


<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>