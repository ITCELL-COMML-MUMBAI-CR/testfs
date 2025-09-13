<?php
/**
 * Admin Content Management View - SAMPARK
 * Manage news, announcements, and other content
 */
ob_start();
?>

<div class="container-xl py-4 content-management-page">
    <!-- Page Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <div class="d-flex align-items-center">
                <div class="bg-apple-blue rounded-3 p-3 me-3">
                    <i class="fas fa-newspaper text-dark fa-lg"></i>
                </div>
                <div>
                    <h1 class="h3 mb-1 fw-semibold">Content Management</h1>
                    <p class="text-muted mb-0">Manage news, announcements, and useful links with advanced search and filtering</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Type Tabs -->
    <div class="card card-apple mb-4">
        <div class="card-header border-0">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#newsTab" type="button">
                        <i class="fas fa-newspaper me-2"></i>News & Updates
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#announcementsTab" type="button">
                        <i class="fas fa-bullhorn me-2"></i>Announcements
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#linksTab" type="button">
                        <i class="fas fa-link me-2"></i>Useful Links
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <!-- News Tab -->
                <div class="tab-pane fade show active" id="newsTab">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-0">News & Updates</h5>
                            <small class="text-muted">Manage news articles and system updates</small>
                        </div>
                        <div>
                            <button class="btn btn-apple-secondary me-2" onclick="refreshContentTable('newsTable')" title="Refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button class="btn btn-apple-primary" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                                <i class="fas fa-plus me-2"></i>Add News
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="newsTable" class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>Published</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($news as $item): ?>
                                <tr data-priority="<?= $item['priority'] ?>" class="priority-<?= $item['priority'] ?>">
                                    <td>
                                        <div class="fw-semibold content-preview"><?= htmlspecialchars($item['title']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($item['short_description'] ?: substr(strip_tags($item['content']), 0, 100) . '...') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $item['type'] === 'news' ? 'primary' : ($item['type'] === 'announcement' ? 'info' : ($item['type'] === 'alert' ? 'warning' : 'secondary')) ?>">
                                            <?= ucfirst($item['type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $item['priority'] === 'urgent' ? 'danger' : ($item['priority'] === 'high' ? 'warning' : ($item['priority'] === 'medium' ? 'info' : 'secondary')) ?>">
                                            <?= ucfirst($item['priority']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($item['publish_date'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $item['is_active'] ? 'success' : 'secondary' ?> status-indicator status-<?= $item['is_active'] ? 'active' : 'inactive' ?>">
                                            <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($item['created_by_name'] ?: 'Unknown') ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm action-buttons">
                                            <button class="btn btn-apple-primary" onclick="editNews(<?= $item['id'] ?>)" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-apple-danger" onclick="deleteNews(<?= $item['id'] ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Announcements Tab -->
                <div class="tab-pane fade" id="announcementsTab">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-0">Announcements</h5>
                            <small class="text-muted">Manage system announcements and alerts</small>
                        </div>
                        <div>
                            <button class="btn btn-apple-secondary me-2" onclick="refreshContentTable('announcementsTable')" title="Refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button class="btn btn-apple-primary" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                                <i class="fas fa-plus me-2"></i>Add Announcement
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="announcementsTable" class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Priority</th>
                                    <th>Published</th>
                                    <th>Expires</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($announcements as $item): ?>
                                <tr data-priority="<?= $item['priority'] ?>" class="priority-<?= $item['priority'] ?>">
                                    <td>
                                        <div class="fw-semibold content-preview"><?= htmlspecialchars($item['title']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($item['short_description'] ?: substr(strip_tags($item['content']), 0, 100) . '...') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $item['priority'] === 'urgent' ? 'danger' : ($item['priority'] === 'high' ? 'warning' : ($item['priority'] === 'medium' ? 'info' : 'secondary')) ?>">
                                            <?= ucfirst($item['priority']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($item['publish_date'])) ?></td>
                                    <td>
                                        <?php if ($item['expire_date']): ?>
                                            <?= date('M d, Y', strtotime($item['expire_date'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $item['is_active'] ? 'success' : 'secondary' ?> status-indicator status-<?= $item['is_active'] ? 'active' : 'inactive' ?>">
                                            <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($item['created_by_name'] ?: 'Unknown') ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm action-buttons">
                                            <button class="btn btn-apple-primary" onclick="editAnnouncement(<?= $item['id'] ?>)" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-apple-danger" onclick="deleteAnnouncement(<?= $item['id'] ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Links Tab -->
                <div class="tab-pane fade" id="linksTab">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-0">Useful Links</h5>
                            <small class="text-muted">Manage quick access links for users</small>
                        </div>
                        <div>
                            <button class="btn btn-apple-secondary me-2" onclick="refreshContentTable('linksTable')" title="Refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button class="btn btn-apple-primary" data-bs-toggle="modal" data-bs-target="#addLinkModal">
                                <i class="fas fa-plus me-2"></i>Add Link
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="linksTable" class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>URL</th>
                                    <th>Icon</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($quick_links as $link): ?>
                                <tr data-priority="normal">
                                    <td>
                                        <div class="fw-semibold content-preview"><?= htmlspecialchars($link['title']) ?></div>
                                    </td>
                                    <td>
                                        <div class="text-muted small"><?= htmlspecialchars($link['description'] ?: 'No description') ?></div>
                                    </td>
                                    <td>
                                        <a href="<?= htmlspecialchars($link['url']) ?>" target="<?= $link['target'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($link['url']) ?>
                                            <i class="fas fa-external-link-alt ms-1"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($link['icon']): ?>
                                            <i class="<?= htmlspecialchars($link['icon']) ?>"></i>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= $link['sort_order'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $link['is_active'] ? 'success' : 'secondary' ?> status-indicator status-<?= $link['is_active'] ? 'active' : 'inactive' ?>">
                                            <?= $link['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($link['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm action-buttons">
                                            <button class="btn btn-apple-primary" onclick="editLink(<?= $link['id'] ?>)" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-apple-danger" onclick="deleteLink(<?= $link['id'] ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add News Modal -->
<div class="modal fade" id="addNewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add News Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addNewsForm" onsubmit="saveNews(event)">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label-apple">Title *</label>
                            <input type="text" class="form-control-apple" name="title" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-apple">Category *</label>
                            <select class="form-control-apple" name="category" required>
                                <option value="">Select Category</option>
                                <option value="system_update">System Update</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="announcement">Announcement</option>
                                <option value="general">General</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label-apple">Summary</label>
                            <input type="text" class="form-control-apple" name="summary" 
                                   placeholder="Brief summary for news listing...">
                        </div>
                        <div class="col-12">
                            <label class="form-label-apple">Content *</label>
                            <textarea class="form-control-apple" name="content" rows="8" required 
                                      placeholder="Full news content..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">Publish Date</label>
                            <input type="datetime-local" class="form-control-apple" name="publish_date" 
                                   value="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">Status</label>
                            <select class="form-control-apple" name="status">
                                <option value="draft">Draft</option>
                                <option value="published" selected>Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-apple-primary">
                        <i class="fas fa-save me-2"></i>Save News
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Announcement Modal -->
<div class="modal fade" id="addAnnouncementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAnnouncementForm" onsubmit="saveAnnouncement(event)">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label-apple">Title *</label>
                            <input type="text" class="form-control-apple" name="title" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">Type</label>
                            <select class="form-control-apple" name="type">
                                <option value="general">General</option>
                                <option value="urgent">Urgent</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="service">Service</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">Priority</label>
                            <select class="form-control-apple" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label-apple">Message *</label>
                            <textarea class="form-control-apple" name="message" rows="5" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">Start Date</label>
                            <input type="datetime-local" class="form-control-apple" name="start_date" 
                                   value="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">Expire Date</label>
                            <input type="datetime-local" class="form-control-apple" name="expire_date">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_marquee">
                                <label class="form-check-label">
                                    Show in marquee (homepage banner)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-apple-primary">
                        <i class="fas fa-save me-2"></i>Save Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Link Modal -->
<div class="modal fade" id="addLinkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Useful Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addLinkForm" onsubmit="saveLink(event)">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label-apple">Title *</label>
                            <input type="text" class="form-control-apple" name="title" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label-apple">URL *</label>
                            <input type="url" class="form-control-apple" name="url" required 
                                   placeholder="https://example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label-apple">Description</label>
                            <textarea class="form-control-apple" name="description" rows="3" 
                                      placeholder="Brief description of the link..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">Category</label>
                            <select class="form-control-apple" name="category">
                                <option value="official">Official</option>
                                <option value="guidelines">Guidelines</option>
                                <option value="forms">Forms</option>
                                <option value="external">External</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-apple">Order</label>
                            <input type="number" class="form-control-apple" name="sort_order" value="0" 
                                   placeholder="Display order (0 = first)">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" checked>
                                <label class="form-check-label">
                                    Active (visible to users)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-apple-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-apple-primary">
                        <i class="fas fa-save me-2"></i>Save Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Content management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables for content management
    initializeContentDataTables();
});

// Initialize DataTables for all content types
function initializeContentDataTables() {
    // News Table
    if ($.fn.DataTable.isDataTable('#newsTable')) {
        $('#newsTable').DataTable().destroy();
    }
    $('#newsTable').DataTable({
        processing: true,
        responsive: true,
        searching: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[3, 'desc']], // Order by published date
        language: {
            search: 'Search news:',
            lengthMenu: 'Show _MENU_ news items',
            info: 'Showing _START_ to _END_ of _TOTAL_ news items',
            infoEmpty: 'Showing 0 to 0 of 0 news items',
            infoFiltered: '(filtered from _MAX_ total news items)',
            zeroRecords: 'No matching news found',
            emptyTable: 'No news items found'
        },
        columnDefs: [
            { orderable: false, targets: [6] } // Actions column
        ]
    });

    // Announcements Table
    if ($.fn.DataTable.isDataTable('#announcementsTable')) {
        $('#announcementsTable').DataTable().destroy();
    }
    $('#announcementsTable').DataTable({
        processing: true,
        responsive: true,
        searching: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[2, 'desc']], // Order by published date
        language: {
            search: 'Search announcements:',
            lengthMenu: 'Show _MENU_ announcements',
            info: 'Showing _START_ to _END_ of _TOTAL_ announcements',
            infoEmpty: 'Showing 0 to 0 of 0 announcements',
            infoFiltered: '(filtered from _MAX_ total announcements)',
            zeroRecords: 'No matching announcements found',
            emptyTable: 'No announcements found'
        },
        columnDefs: [
            { orderable: false, targets: [6] } // Actions column
        ]
    });

    // Links Table
    if ($.fn.DataTable.isDataTable('#linksTable')) {
        $('#linksTable').DataTable().destroy();
    }
    $('#linksTable').DataTable({
        processing: true,
        responsive: true,
        searching: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[4, 'asc']], // Order by sort order
        language: {
            search: 'Search links:',
            lengthMenu: 'Show _MENU_ links',
            info: 'Showing _START_ to _END_ of _TOTAL_ links',
            infoEmpty: 'Showing 0 to 0 of 0 links',
            infoFiltered: '(filtered from _MAX_ total links)',
            zeroRecords: 'No matching links found',
            emptyTable: 'No links found'
        },
        columnDefs: [
            { orderable: false, targets: [7] } // Actions column
        ]
    });
}

// Refresh content table
function refreshContentTable(tableId) {
    const $btn = $(`button[onclick="refreshContentTable('${tableId}')"]`);
    const $icon = $btn.find('i');
    
    // Add spinning animation
    $icon.addClass('spinning');
    $btn.prop('disabled', true);
    
    // Simulate refresh (since we're using server-side data, we'll just reload the page)
    setTimeout(() => {
        location.reload();
    }, 500);
}

// Enhanced content management functions
async function saveNews(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/admin/content/news`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            Swal.fire('Success', 'News item added successfully', 'success').then(() => {
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
        Swal.fire('Error', 'Failed to add news item', 'error');
    }
}

async function saveAnnouncement(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/admin/content/announcements`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            Swal.fire('Success', 'Announcement added successfully', 'success').then(() => {
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
        Swal.fire('Error', 'Failed to add announcement', 'error');
    }
}

async function saveLink(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    formData.append('csrf_token', CSRF_TOKEN);
    
    try {
        showLoading();
        const response = await fetch(`${APP_URL}/admin/content/links`, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            Swal.fire('Success', 'Link added successfully', 'success').then(() => {
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
        Swal.fire('Error', 'Failed to add link', 'error');
    }
}

function editNews(newsId) {
    // Implementation for editing news
    Swal.fire('Info', 'Edit news functionality to be implemented', 'info');
}

async function deleteNews(newsId) {
    const result = await Swal.fire({
        title: 'Delete News?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Delete'
    });
    
    if (result.isConfirmed) {
        try {
            const response = await fetch(`${APP_URL}/admin/content/news/${newsId}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire('Deleted', data.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to delete news item', 'error');
        }
    }
}

function editAnnouncement(announcementId) {
    // Implementation for editing announcement
    Swal.fire('Info', 'Edit announcement functionality to be implemented', 'info');
}

async function deleteAnnouncement(announcementId) {
    const result = await Swal.fire({
        title: 'Delete Announcement?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Delete'
    });
    
    if (result.isConfirmed) {
        try {
            const response = await fetch(`${APP_URL}/admin/content/announcements/${announcementId}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire('Deleted', data.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to delete announcement', 'error');
        }
    }
}

function editLink(linkId) {
    // Implementation for editing link
    Swal.fire('Info', 'Edit link functionality to be implemented', 'info');
}

async function deleteLink(linkId) {
    const result = await Swal.fire({
        title: 'Delete Link?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Delete'
    });
    
    if (result.isConfirmed) {
        try {
            const response = await fetch(`${APP_URL}/admin/content/links/${linkId}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire('Deleted', data.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to delete link', 'error');
        }
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
/* Content management styles */
.nav-tabs .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    color: var(--bs-body-color);
}

.nav-tabs .nav-link.active {
    background: none;
    border-color: var(--apple-primary);
    color: var(--apple-primary);
    font-weight: 600;
}

.nav-tabs .nav-link:hover {
    border-color: var(--apple-primary);
    color: var(--apple-primary);
}

/* Card enhancements for links */
.card {
    transition: all 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: var(--apple-shadow-medium);
}

/* Table responsive improvements */
.table-responsive {
    border-radius: var(--apple-radius-medium);
}

/* Badge styling */
.badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}

/* Modal enhancements */
.modal-lg .modal-dialog {
    max-width: 900px;
}

/* Form styling */
textarea.form-control-apple {
    resize: vertical;
}

/* Button group styling */
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

/* Content management page specific styles */
.content-management-page {
    background-color: #f8f9fa;
    min-height: 100vh;
}

/* Card enhancements */
.card-apple {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    background: #ffffff;
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-radius: 8px 8px 0 0 !important;
    padding: 1rem 1.5rem;
}

.card-header .nav-tabs {
    border: none;
    margin: 0;
}

.card-header .nav-tabs .nav-link {
    background: transparent;
    border: 1px solid transparent;
    color: #6c757d;
    border-radius: 6px;
    margin-right: 0.5rem;
    padding: 0.75rem 1.25rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.card-header .nav-tabs .nav-link:hover {
    background: #e9ecef;
    color: #495057;
    border-color: #dee2e6;
}

.card-header .nav-tabs .nav-link.active {
    background: #ffffff;
    color: #007bff;
    border-color: #007bff;
    box-shadow: 0 1px 3px rgba(0, 123, 255, 0.2);
}

/* Content area styling */
.card-body {
    padding: 2rem;
    background: #ffffff;
}

/* Table enhancements */
.table {
    margin-bottom: 0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table thead th {
    background: #f8f9fa;
    border: none;
    font-weight: 600;
    color: #495057;
    padding: 1.25rem 1rem;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
}

.table tbody td {
    vertical-align: middle;
    padding: 1.25rem 1rem;
    border-top: 1px solid #f1f3f4;
    font-size: 0.95rem;
    line-height: 1.4;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

/* Badge styling */
.badge {
    font-size: 0.75em;
    padding: 0.4em 0.8em;
    border-radius: 6px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Button styling */
.btn-group-sm .btn {
    padding: 0.4rem 0.6rem;
    margin: 0 2px;
    border-radius: 6px;
    font-size: 0.8rem;
    transition: all 0.2s ease;
}

.btn-group-sm .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-apple-primary {
    background: #007bff;
    border: 1px solid #007bff;
    color: white;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.btn-apple-primary:hover {
    background: #0056b3;
    border-color: #0056b3;
    color: white;
}

.btn-apple-secondary {
    background: #6c757d;
    border: 1px solid #6c757d;
    color: white;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.btn-apple-secondary:hover {
    background: #545b62;
    border-color: #545b62;
    color: white;
}

.btn-apple-danger {
    background: #dc3545;
    border: 1px solid #dc3545;
    color: white;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn-apple-danger:hover {
    background: #c82333;
    border-color: #c82333;
    color: white;
}

/* DataTable enhancements */
.dataTables_wrapper {
    padding: 0;
    margin-top: 1.5rem;
}

.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_processing,
.dataTables_wrapper .dataTables_paginate {
    color: #495057;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
}

.dataTables_wrapper .dataTables_filter {
    float: right;
    text-align: right;
}

.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #ced4da;
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    background: #ffffff;
    width: 250px;
}

.dataTables_wrapper .dataTables_filter input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    background: #ffffff;
}

.dataTables_wrapper .dataTables_length select {
    border: 1px solid #ced4da;
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
    font-size: 0.9rem;
    background: #ffffff;
    transition: all 0.2s ease;
}

.dataTables_wrapper .dataTables_length select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    background: #ffffff;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    margin: 0 2px;
    padding: 0.5rem 0.75rem;
    background: #ffffff;
    color: #495057;
    transition: all 0.2s ease;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #007bff;
    color: white;
    border-color: #007bff;
    transform: translateY(-1px);
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #007bff;
    color: white;
    border-color: #007bff;
    box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
}

/* Page header styling */
.page-header {
    margin-bottom: 2rem;
}

.page-header h5 {
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.page-header small {
    color: #6c757d;
    font-size: 0.9rem;
}

/* Action buttons container */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

/* Table responsive improvements */
.table-responsive {
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Content type indicators */
.content-type-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

/* Priority indicators */
.priority-high { color: #dc3545; }
.priority-medium { color: #ffc107; }
.priority-low { color: #6c757d; }
.priority-urgent { color: #dc3545; font-weight: bold; }

/* Refresh button animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.btn .fa-sync-alt.spinning {
    animation: spin 1s linear infinite;
}

/* Enhanced visual elements */
.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.status-indicator::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}

.status-active::before { background: #28a745; }
.status-inactive::before { background: #6c757d; }

/* Content preview styling */
.content-preview {
    max-width: 400px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
}

/* Enhanced table row styling */
.table tbody tr {
    border-left: 2px solid transparent;
    transition: border-left-color 0.2s ease;
}

.table tbody tr:hover {
    border-left-color: #007bff;
}

.table tbody tr.priority-high {
    border-left-color: #ffc107;
}

.table tbody tr.priority-urgent {
    border-left-color: #dc3545;
}

/* Action button enhancements */
.action-buttons .btn {
    transition: all 0.2s ease;
}

.action-buttons .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Search and filter enhancements */
.dataTables_wrapper .dataTables_filter {
    position: relative;
}

/* Loading states */
.table-loading {
    position: relative;
}

.table-loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

/* Enhanced card header with icons */
.card-header .nav-tabs .nav-link i {
    margin-right: 0.5rem;
    font-size: 0.9rem;
}

/* Priority color coding for entire rows */
.table tbody tr[data-priority="urgent"] {
    background: rgba(220, 53, 69, 0.02);
}

.table tbody tr[data-priority="high"] {
    background: rgba(255, 193, 7, 0.02);
}

/* Enhanced pagination */
.dataTables_wrapper .dataTables_paginate {
    margin-top: 1.5rem;
    text-align: center;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    min-width: 40px;
    text-align: center;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .nav-tabs {
        flex-wrap: wrap;
    }
    
    .nav-tabs .nav-item {
        margin-bottom: 0.5rem;
        flex: 1;
        min-width: 0;
    }
    
    .nav-tabs .nav-link {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
        text-align: center;
    }
    
    .modal-dialog {
        margin: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.2rem 0.4rem;
        font-size: 0.75rem;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .dataTables_wrapper .dataTables_filter {
        float: none;
        text-align: left;
        margin-bottom: 1rem;
    }
    
    .dataTables_wrapper .dataTables_length {
        margin-bottom: 1rem;
    }
    
    .page-header {
        text-align: center;
    }
    
    .page-header .action-buttons {
        justify-content: center;
        margin-top: 1rem;
    }
}

@media (max-width: 576px) {
    .card-header {
        padding: 1rem;
    }
    
    .card-body {
        padding: 0.75rem;
    }
    
    .table thead th,
    .table tbody td {
        padding: 0.5rem 0.25rem;
        font-size: 0.8rem;
    }
    
    .badge {
        font-size: 0.65em;
        padding: 0.3em 0.6em;
    }
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>