<?php
/**
 * Admin Sheds Management View - SAMPARK
 * Manage railway sheds and locations
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
                    <i class="fas fa-warehouse text-white fa-lg"></i>
                </div>
                <div>
                    <h1 class="h3 mb-1 fw-semibold">Shed Management</h1>
                    <p class="text-muted mb-0">Manage railway sheds, zones, and divisions</p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <button class="btn btn-apple-secondary" onclick="importSheds()">
                    <i class="fas fa-upload me-2"></i>Import
                </button>
                <button class="btn btn-apple-primary" data-bs-toggle="modal" data-bs-target="#addShedModal">
                    <i class="fas fa-plus me-2"></i>Add Shed
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card card-apple text-center border-start border-primary border-4">
                <div class="card-body">
                    <div class="text-primary mb-2">
                        <i class="fas fa-warehouse fa-2x"></i>
                    </div>
                    <h3 class="mb-1"><?= count($sheds ?? []) ?></h3>
                    <p class="text-muted mb-0">Total Sheds</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-apple text-center border-start border-success border-4">
                <div class="card-body">
                    <div class="text-success mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h3 class="mb-1"><?= count(array_filter($sheds ?? [], fn($s) => $s['is_active'] ?? true)) ?></h3>
                    <p class="text-muted mb-0">Active Sheds</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-apple text-center border-start border-info border-4">
                <div class="card-body">
                    <div class="text-info mb-2">
                        <i class="fas fa-map-marked-alt fa-2x"></i>
                    </div>
                    <h3 class="mb-1"><?= count(array_unique(array_column($sheds ?? [], 'division'))) ?></h3>
                    <p class="text-muted mb-0">Divisions</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-apple text-center border-start border-warning border-4">
                <div class="card-body">
                    <div class="text-warning mb-2">
                        <i class="fas fa-globe fa-2x"></i>
                    </div>
                    <h3 class="mb-1"><?= count(array_unique(array_column($sheds ?? [], 'zone'))) ?></h3>
                    <p class="text-muted mb-0">Zones</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="card card-apple mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Search sheds..." 
                               id="searchSheds" onkeyup="filterSheds()">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-control-apple" id="filterZone" onchange="filterSheds()">
                        <option value="">All Zones</option>
                        <?php foreach (array_unique(array_column($sheds ?? [], 'zone')) as $zone): ?>
                        <option value="<?= htmlspecialchars($zone) ?>"><?= htmlspecialchars($zone) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control-apple" id="filterDivision" onchange="filterSheds()">
                        <option value="">All Divisions</option>
                        <?php foreach (array_unique(array_column($sheds ?? [], 'division')) as $division): ?>
                        <option value="<?= htmlspecialchars($division) ?>"><?= htmlspecialchars($division) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control-apple" id="filterStatus" onchange="filterSheds()">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-apple-secondary w-100" onclick="clearFilters()">
                        <i class="fas fa-times me-1"></i>Clear
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sheds Table -->
    <div class="card card-apple">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Railway Sheds
                <span class="badge bg-primary ms-2" id="shedsCount"><?= count($sheds ?? []) ?></span>
            </h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-apple-secondary" onclick="exportSheds()">
                    <i class="fas fa-download me-1"></i>Export
                </button>
                <button class="btn btn-sm btn-apple-primary" onclick="refreshSheds()">
                    <i class="fas fa-sync-alt me-1"></i>Refresh
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="shedsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">Shed Code</th>
                            <th class="border-0">Shed Name</th>
                            <th class="border-0">Division</th>
                            <th class="border-0">Zone</th>
                            <th class="border-0">Location</th>
                            <th class="border-0">Status</th>
                            <th class="border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sheds)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-warehouse fa-3x mb-3"></i>
                                    <h5>No sheds found</h5>
                                    <p>Click "Add Shed" to register your first railway shed.</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($sheds as $shed): ?>
                            <tr class="shed-row" 
                                data-zone="<?= htmlspecialchars($shed['zone'] ?? '') ?>"
                                data-division="<?= htmlspecialchars($shed['division'] ?? '') ?>"
                                data-status="<?= ($shed['is_active'] ?? true) ? 'active' : 'inactive' ?>">
                                <td>
                                    <span class="fw-semibold text-primary"><?= htmlspecialchars($shed['shed_code'] ?? '') ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($shed['name'] ?? '') ?></div>
                                    <?php if ($shed['description'] ?? ''): ?>
                                    <small class="text-muted"><?= htmlspecialchars($shed['description']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($shed['division'] ?? '') ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($shed['zone'] ?? '') ?></span>
                                </td>
                                <td>
                                    <?php if ($shed['city'] ?? ''): ?>
                                    <div><?= htmlspecialchars($shed['city']) ?></div>
                                    <?php endif; ?>
                                    <?php if ($shed['state'] ?? ''): ?>
                                    <small class="text-muted"><?= htmlspecialchars($shed['state']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               <?= ($shed['is_active'] ?? true) ? 'checked' : '' ?>
                                               onchange="toggleShedStatus(<?= $shed['shed_id'] ?? 0 ?>)">
                                        <label class="form-check-label small">
                                            <?= ($shed['is_active'] ?? true) ? 'Active' : 'Inactive' ?>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-apple-primary" 
                                                onclick="viewShedDetails(<?= $shed['shed_id'] ?? 0 ?>)" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-apple-secondary" 
                                                onclick="editShed(<?= $shed['shed_id'] ?? 0 ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-apple-danger" 
                                                onclick="deleteShed(<?= $shed['shed_id'] ?? 0 ?>)" title="Delete">
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

<!-- Add Shed Modal -->
<div class="modal fade" id="addShedModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Shed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addShedForm" onsubmit="saveShed(event)">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-apple">Shed Code *</label>
                            <input type="text" class="form-control-apple" name="shed_code" required 
                                   placeholder="e.g., DLI, NDLS">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">Shed Name *</label>
                            <input type="text" class="form-control-apple" name="name" required 
                                   placeholder="e.g., Delhi Shed">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">Division *</label>
                            <input type="text" class="form-control-apple" name="division" required 
                                   placeholder="e.g., Delhi Division">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">Zone *</label>
                            <select class="form-control-apple" name="zone" required>
                                <option value="">Select Zone</option>
                                <option value="Northern Railway">Northern Railway</option>
                                <option value="Southern Railway">Southern Railway</option>
                                <option value="Eastern Railway">Eastern Railway</option>
                                <option value="Western Railway">Western Railway</option>
                                <option value="Central Railway">Central Railway</option>
                                <option value="North Eastern Railway">North Eastern Railway</option>
                                <option value="South Eastern Railway">South Eastern Railway</option>
                                <option value="South Central Railway">South Central Railway</option>
                                <option value="East Central Railway">East Central Railway</option>
                                <option value="West Central Railway">West Central Railway</option>
                                <option value="North Central Railway">North Central Railway</option>
                                <option value="South East Central Railway">South East Central Railway</option>
                                <option value="North Western Railway">North Western Railway</option>
                                <option value="South Western Railway">South Western Railway</option>
                                <option value="East Coast Railway">East Coast Railway</option>
                                <option value="Northeast Frontier Railway">Northeast Frontier Railway</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">City</label>
                            <input type="text" class="form-control-apple" name="city" 
                                   placeholder="e.g., New Delhi">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">State</label>
                            <input type="text" class="form-control-apple" name="state" 
                                   placeholder="e.g., Delhi">
                        </div>
                        <div class="col-12">
                            <label class="form-label-apple">Description</label>
                            <textarea class="form-control-apple" name="description" rows="3" 
                                      placeholder="Brief description of the shed..."></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" checked>
                                <label class="form-check-label">
                                    Active (visible in system)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-apple-primary">
                        <i class="fas fa-save me-2"></i>Save Shed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Shed Modal -->
<div class="modal fade" id="editShedModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Shed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editShedForm" onsubmit="updateShed(event)">
                <input type="hidden" name="shed_id" id="editShedId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-apple">Shed Code *</label>
                            <input type="text" class="form-control-apple" name="shed_code" id="editShedCode" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">Shed Name *</label>
                            <input type="text" class="form-control-apple" name="name" id="editShedName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">Division *</label>
                            <input type="text" class="form-control-apple" name="division" id="editShedDivision" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">Zone *</label>
                            <select class="form-control-apple" name="zone" id="editShedZone" required>
                                <option value="Northern Railway">Northern Railway</option>
                                <option value="Southern Railway">Southern Railway</option>
                                <option value="Eastern Railway">Eastern Railway</option>
                                <option value="Western Railway">Western Railway</option>
                                <option value="Central Railway">Central Railway</option>
                                <option value="North Eastern Railway">North Eastern Railway</option>
                                <option value="South Eastern Railway">South Eastern Railway</option>
                                <option value="South Central Railway">South Central Railway</option>
                                <option value="East Central Railway">East Central Railway</option>
                                <option value="West Central Railway">West Central Railway</option>
                                <option value="North Central Railway">North Central Railway</option>
                                <option value="South East Central Railway">South East Central Railway</option>
                                <option value="North Western Railway">North Western Railway</option>
                                <option value="South Western Railway">South Western Railway</option>
                                <option value="East Coast Railway">East Coast Railway</option>
                                <option value="Northeast Frontier Railway">Northeast Frontier Railway</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">City</label>
                            <input type="text" class="form-control-apple" name="city" id="editShedCity">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">State</label>
                            <input type="text" class="form-control-apple" name="state" id="editShedState">
                        </div>
                        <div class="col-12">
                            <label class="form-label-apple">Description</label>
                            <textarea class="form-control-apple" name="description" id="editShedDescription" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="editShedActive">
                                <label class="form-check-label">
                                    Active (visible in system)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-apple-primary">
                        <i class="fas fa-save me-2"></i>Update Shed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Shed Details Modal -->
<div class="modal fade" id="shedDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Shed Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="shedDetailsContent">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
// Sheds management JavaScript
let allSheds = <?= json_encode($sheds ?? []) ?>;

async function saveShed(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/admin/sheds`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            Swal.fire('Success', 'Shed added successfully', 'success').then(() => {
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
        Swal.fire('Error', 'Failed to add shed', 'error');
    }
}

async function editShed(shedId) {
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/api/sheds/${shedId}`);
        const shed = await response.json();
        hideLoading();
        
        if (shed) {
            document.getElementById('editShedId').value = shed.shed_id;
            document.getElementById('editShedCode').value = shed.shed_code;
            document.getElementById('editShedName').value = shed.name;
            document.getElementById('editShedDivision').value = shed.division;
            document.getElementById('editShedZone').value = shed.zone;
            document.getElementById('editShedCity').value = shed.city || '';
            document.getElementById('editShedState').value = shed.state || '';
            document.getElementById('editShedDescription').value = shed.description || '';
            document.getElementById('editShedActive').checked = shed.is_active;
            
            new bootstrap.Modal(document.getElementById('editShedModal')).show();
        }
    } catch (error) {
        hideLoading();
        Swal.fire('Error', 'Failed to load shed data', 'error');
    }
}

async function updateShed(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('csrf_token', CSRF_TOKEN);
    const shedId = formData.get('shed_id');
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/admin/sheds/${shedId}/edit`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            Swal.fire('Success', 'Shed updated successfully', 'success').then(() => {
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
        Swal.fire('Error', 'Failed to update shed', 'error');
    }
}

async function deleteShed(shedId) {
    const result = await Swal.fire({
        title: 'Delete Shed?',
        text: 'This action cannot be undone. All associated data will be affected.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel'
    });
    
    if (result.isConfirmed) {
        try {
            showLoading();
            const response = await fetch(`${APP_URL}/admin/sheds/${shedId}/delete`, {
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
                Swal.fire('Deleted', 'Shed deleted successfully', 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', apiResult.message, 'error');
            }
        } catch (error) {
            hideLoading();
            Swal.fire('Error', 'Failed to delete shed', 'error');
        }
    }
}

async function toggleShedStatus(shedId) {
    try {
        const response = await fetch(`${APP_URL}/admin/sheds/${shedId}/toggle`, {
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
        Swal.fire('Error', 'Failed to update shed status', 'error');
        location.reload();
    }
}

async function viewShedDetails(shedId) {
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/api/sheds/${shedId}/details`);
        const details = await response.json();
        hideLoading();
        
        if (details) {
            const detailsHtml = `
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><td class="fw-semibold">Shed Code:</td><td>${details.shed_code}</td></tr>
                            <tr><td class="fw-semibold">Shed Name:</td><td>${details.name}</td></tr>
                            <tr><td class="fw-semibold">Division:</td><td>${details.division}</td></tr>
                            <tr><td class="fw-semibold">Zone:</td><td>${details.zone}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><td class="fw-semibold">City:</td><td>${details.city || 'N/A'}</td></tr>
                            <tr><td class="fw-semibold">State:</td><td>${details.state || 'N/A'}</td></tr>
                            <tr><td class="fw-semibold">Status:</td><td><span class="badge bg-${details.is_active ? 'success' : 'danger'}">${details.is_active ? 'Active' : 'Inactive'}</span></td></tr>
                            <tr><td class="fw-semibold">Created:</td><td>${new Date(details.created_at).toLocaleDateString()}</td></tr>
                        </table>
                    </div>
                </div>
                ${details.description ? `<div class="row"><div class="col-12"><strong>Description:</strong><br>${details.description}</div></div>` : ''}
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Statistics</h6>
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="bg-light p-3 rounded">
                                    <h5 class="text-primary">${details.ticket_count || 0}</h5>
                                    <small>Total Tickets</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light p-3 rounded">
                                    <h5 class="text-success">${details.resolved_tickets || 0}</h5>
                                    <small>Resolved</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light p-3 rounded">
                                    <h5 class="text-warning">${details.pending_tickets || 0}</h5>
                                    <small>Pending</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('shedDetailsContent').innerHTML = detailsHtml;
            new bootstrap.Modal(document.getElementById('shedDetailsModal')).show();
        }
    } catch (error) {
        hideLoading();
        Swal.fire('Error', 'Failed to load shed details', 'error');
    }
}

function filterSheds() {
    const searchTerm = document.getElementById('searchSheds').value.toLowerCase();
    const zoneFilter = document.getElementById('filterZone').value;
    const divisionFilter = document.getElementById('filterDivision').value;
    const statusFilter = document.getElementById('filterStatus').value;
    
    const rows = document.querySelectorAll('.shed-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const shedCode = row.cells[0].textContent.toLowerCase();
        const shedName = row.cells[1].textContent.toLowerCase();
        const zone = row.dataset.zone;
        const division = row.dataset.division;
        const status = row.dataset.status;
        
        const matchesSearch = shedCode.includes(searchTerm) || shedName.includes(searchTerm);
        const matchesZone = !zoneFilter || zone === zoneFilter;
        const matchesDivision = !divisionFilter || division === divisionFilter;
        const matchesStatus = !statusFilter || status === statusFilter;
        
        if (matchesSearch && matchesZone && matchesDivision && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('shedsCount').textContent = visibleCount;
}

function clearFilters() {
    document.getElementById('searchSheds').value = '';
    document.getElementById('filterZone').value = '';
    document.getElementById('filterDivision').value = '';
    document.getElementById('filterStatus').value = '';
    filterSheds();
}

function exportSheds() {
    window.location.href = `${APP_URL}/admin/sheds/export`;
}

function importSheds() {
    Swal.fire({
        title: 'Import Sheds',
        html: `
            <div class="text-start">
                <p>Upload a CSV file with the following columns:</p>
                <ul>
                    <li>shed_code (required)</li>
                    <li>name (required)</li>
                    <li>division (required)</li>
                    <li>zone (required)</li>
                    <li>city (optional)</li>
                    <li>state (optional)</li>
                    <li>description (optional)</li>
                </ul>
                <input type="file" id="importFile" class="form-control mt-3" accept=".csv">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Upload',
        cancelButtonText: 'Cancel',
        preConfirm: () => {
            const file = document.getElementById('importFile').files[0];
            if (!file) {
                Swal.showValidationMessage('Please select a CSV file');
                return false;
            }
            return file;
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('file', result.value);
            formData.append('csrf_token', CSRF_TOKEN);
            
            try {
                showLoading();
                const response = await fetch(`${APP_URL}/admin/sheds/import`, {
                    method: 'POST',
                    body: formData
                });
                
                const importResult = await response.json();
                hideLoading();
                
                if (importResult.success) {
                    Swal.fire('Success', `Imported ${importResult.imported_count} sheds successfully`, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', importResult.message, 'error');
                }
            } catch (error) {
                hideLoading();
                Swal.fire('Error', 'Failed to import sheds', 'error');
            }
        }
    });
}

function refreshSheds() {
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
/* Sheds page specific styles */
.shed-row {
    transition: all 0.2s ease;
}

.shed-row:hover {
    background-color: rgba(var(--apple-primary-rgb), 0.04);
}

.card.border-start {
    background: linear-gradient(135deg, rgba(var(--bs-primary), 0.05) 0%, transparent 100%);
}

.form-switch .form-check-input:checked {
    background-color: var(--apple-primary);
    border-color: var(--apple-primary);
}

/* Statistics cards with gradients */
.border-primary {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, transparent 100%);
}

.border-success {
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, transparent 100%);
}

.border-info {
    background: linear-gradient(135deg, rgba(13, 202, 240, 0.1) 0%, transparent 100%);
}

.border-warning {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, transparent 100%);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    
    .modal-dialog.modal-lg {
        margin: 1rem;
    }
}
</style>