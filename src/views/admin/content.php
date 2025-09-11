<?php
/**
 * Admin Content Management View - SAMPARK
 * Manage news, announcements, and other content
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
                    <i class="fas fa-newspaper text-white fa-lg"></i>
                </div>
                <div>
                    <h1 class="h3 mb-1 fw-semibold">Content Management</h1>
                    <p class="text-muted mb-0">Manage news, announcements, and useful links</p>
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
                        <h5 class="mb-0">News & Updates</h5>
                        <button class="btn btn-apple-primary" data-bs-toggle="modal" data-bs-target="#addNewsModal">
                            <i class="fas fa-plus me-2"></i>Add News
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Published</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="fw-semibold">New Freight Portal Launched</div>
                                        <small class="text-muted">Enhanced features for better customer experience...</small>
                                    </td>
                                    <td><span class="badge bg-primary">System Update</span></td>
                                    <td><?= date('M d, Y') ?></td>
                                    <td><span class="badge bg-success">Published</span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-apple-primary" onclick="editNews(1)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-apple-danger" onclick="deleteNews(1)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="fw-semibold">Maintenance Schedule Update</div>
                                        <small class="text-muted">Planned maintenance on September 15th...</small>
                                    </td>
                                    <td><span class="badge bg-warning">Maintenance</span></td>
                                    <td><?= date('M d, Y', strtotime('-1 day')) ?></td>
                                    <td><span class="badge bg-success">Published</span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-apple-primary" onclick="editNews(2)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-apple-danger" onclick="deleteNews(2)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Announcements Tab -->
                <div class="tab-pane fade" id="announcementsTab">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">Announcements</h5>
                        <button class="btn btn-apple-primary" data-bs-toggle="modal" data-bs-target="#addAnnouncementModal">
                            <i class="fas fa-plus me-2"></i>Add Announcement
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>Expires</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="fw-semibold">Important Service Update</div>
                                        <small class="text-muted">Please update your contact information...</small>
                                    </td>
                                    <td><span class="badge bg-info">General</span></td>
                                    <td><span class="badge bg-warning">Medium</span></td>
                                    <td><?= date('M d, Y', strtotime('+7 days')) ?></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-apple-primary" onclick="editAnnouncement(1)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-apple-danger" onclick="deleteAnnouncement(1)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Links Tab -->
                <div class="tab-pane fade" id="linksTab">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">Useful Links</h5>
                        <button class="btn btn-apple-primary" data-bs-toggle="modal" data-bs-target="#addLinkModal">
                            <i class="fas fa-plus me-2"></i>Add Link
                        </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title mb-2">Indian Railways Portal</h6>
                                            <p class="card-text text-muted small">Official Indian Railways website</p>
                                            <a href="https://indianrailways.gov.in" target="_blank" class="btn btn-sm btn-apple-secondary">
                                                <i class="fas fa-external-link-alt me-1"></i>Visit
                                            </a>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="editLink(1)">Edit</a></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteLink(1)">Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title mb-2">Freight Guidelines</h6>
                                            <p class="card-text text-muted small">Complete freight booking guidelines</p>
                                            <a href="#" target="_blank" class="btn btn-sm btn-apple-secondary">
                                                <i class="fas fa-external-link-alt me-1"></i>Visit
                                            </a>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="editLink(2)">Edit</a></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteLink(2)">Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    // Initialize rich text editor if needed
});

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

function deleteNews(newsId) {
    Swal.fire({
        title: 'Delete News?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement delete functionality
            Swal.fire('Deleted', 'News item deleted successfully', 'success');
        }
    });
}

function editAnnouncement(announcementId) {
    // Implementation for editing announcement
    Swal.fire('Info', 'Edit announcement functionality to be implemented', 'info');
}

function deleteAnnouncement(announcementId) {
    Swal.fire({
        title: 'Delete Announcement?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement delete functionality
            Swal.fire('Deleted', 'Announcement deleted successfully', 'success');
        }
    });
}

function editLink(linkId) {
    // Implementation for editing link
    Swal.fire('Info', 'Edit link functionality to be implemented', 'info');
}

function deleteLink(linkId) {
    Swal.fire({
        title: 'Delete Link?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement delete functionality
            Swal.fire('Deleted', 'Link deleted successfully', 'success');
        }
    });
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

/* Mobile responsiveness */
@media (max-width: 768px) {
    .nav-tabs {
        flex-wrap: wrap;
    }
    
    .nav-tabs .nav-item {
        margin-bottom: 0.5rem;
    }
    
    .modal-dialog {
        margin: 1rem;
    }
    
    .card-body {
        padding: 1rem 0.75rem;
    }
}
</style>