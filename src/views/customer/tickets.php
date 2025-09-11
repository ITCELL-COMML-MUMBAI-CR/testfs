
<?php
// Capture the content
ob_start();
?>

<!-- Customer Tickets -->
<section class="py-apple-6">
    <div class="container-xl">
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div>
                        <h1 class="display-3 mb-2">My Support Tickets</h1>
                        <p class="text-muted mb-0">View and manage your freight support requests</p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <a href="<?= Config::getAppUrl() ?>/customer/tickets/create" class="btn btn-apple-primary">
                            <i class="fas fa-plus me-2"></i>Create New Ticket
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tickets Table -->
        <div class="card-apple">
            <div class="card-body">
                <?php if (!empty($tickets['data'])): ?>                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Support Tickets</h5>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="customerTicketsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated by DataTables AJAX -->
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-ticket-alt text-muted" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-muted mb-2">No tickets found</h5>
                        <p class="text-muted mb-4">You don't have any support tickets matching your current filters.</p>
                        <a href="<?= Config::getAppUrl() ?>/customer/tickets/create" class="btn btn-apple-primary">
                            <i class="fas fa-plus me-2"></i>Create Your First Ticket
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Help Section -->
        <div class="card-apple-glass mt-4">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-1">
                            <i class="fas fa-info-circle text-apple-blue me-2"></i>
                            Understanding Ticket Status
                        </h6>
                        <div class="d-flex flex-wrap gap-3 mt-2">
                            <small><span class="badge status-pending me-1">Pending</span> Under review</small>
                            <small><span class="badge status-awaiting-feedback me-1">Awaiting Feedback</span> Your response needed</small>
                            <small><span class="badge status-awaiting-info me-1">Awaiting Info</span> Additional details required</small>
                            <small><span class="badge status-awaiting-approval me-1">Awaiting Approval</span> Solution pending approval</small>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-2 mt-md-0">
                        <a href="<?= Config::getAppUrl() ?>/help" class="btn btn-apple-glass btn-sm">
                            <i class="fas fa-question-circle me-1"></i>More Help
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Background refresh scripts
document.addEventListener('DOMContentLoaded', function() {
    // Initialize customer tickets table with auto-refresh
    if (typeof initializeCustomerTicketsTable === 'function') {
        const customerTable = initializeCustomerTicketsTable('customerTicketsTable');
        console.log('Customer tickets table initialized with background refresh');
    } else {
        console.warn('DataTable configuration not loaded - fallback to basic table');
        initializeBasicTable();
    }
    
    // Update filter behavior for DataTables
    document.querySelectorAll('#filterForm select, #filterForm input').forEach(field => {
        field.addEventListener('change', function() {
            if (window.backgroundRefreshManager) {
                // Force immediate refresh when user changes filters
                window.backgroundRefreshManager.forceRefresh();
            }
        });
    });
});

function forceRefresh() {
    if (window.backgroundRefreshManager) {
        window.backgroundRefreshManager.forceRefresh();
        
        // Show brief confirmation
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Refreshing...';
        button.disabled = true;
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 2000);
    }
}

function initializeBasicTable() {
    // Fallback initialization if DataTables config is not available
    console.log('Using basic table without auto-refresh');
}

function copyTicketId(ticketId) {
    if (window.SAMPARK && window.SAMPARK.utils) {
        window.SAMPARK.utils.copyToClipboard('#' + ticketId)
            .then(() => {
                window.SAMPARK.ui.showToast('Ticket ID copied to clipboard', 'success');
            })
            .catch(err => {
                window.SAMPARK.ui.showToast('Failed to copy ticket ID', 'error');
            });
    } else {
        // Fallback copy method
        navigator.clipboard.writeText('#' + ticketId).then(() => {
            alert('Ticket ID copied to clipboard');
        });
    }
}

window.provideFeedback = function(ticketId) {
    Swal.fire({
        title: 'Provide Feedback',
        html: `
            <div class="text-start">
                <p class="mb-3">Please rate the resolution provided for your ticket and share your feedback.</p>
                
                <div class="mb-3">
                    <label class="form-label">Rating</label>
                    <div class="rating-buttons">
                        <input type="radio" name="rating" value="excellent" id="excellent">
                        <label for="excellent" class="btn btn-outline-success">
                            <i class="fas fa-smile me-1"></i>Excellent
                        </label>
                        
                        <input type="radio" name="rating" value="satisfactory" id="satisfactory">
                        <label for="satisfactory" class="btn btn-outline-warning">
                            <i class="fas fa-meh me-1"></i>Satisfactory
                        </label>
                        
                        <input type="radio" name="rating" value="unsatisfactory" id="unsatisfactory">
                        <label for="unsatisfactory" class="btn btn-outline-danger">
                            <i class="fas fa-frown me-1"></i>Unsatisfactory
                        </label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="feedbackRemarks" class="form-label">Additional Comments (Optional)</label>
                    <textarea class="form-control" id="feedbackRemarks" rows="3" 
                              placeholder="Share your experience or suggestions..."></textarea>
                    <small class="text-muted">Required if rating is unsatisfactory</small>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Submit Feedback',
        customClass: {
            confirmButton: 'btn btn-apple-primary',
            cancelButton: 'btn btn-apple-glass'
        },
        width: '600px',
        didOpen: () => {
            // Add click handlers for radio button labels
            document.querySelectorAll('.rating-buttons label').forEach(label => {
                label.addEventListener('click', function() {
                    // Remove active class from all labels
                    document.querySelectorAll('.rating-buttons label').forEach(l => l.classList.remove('active'));
                    // Add active class to clicked label
                    this.classList.add('active');
                });
            });
        },
        preConfirm: () => {
            const rating = document.querySelector('input[name="rating"]:checked');
            const remarks = document.getElementById('feedbackRemarks').value.trim();
            
            if (!rating) {
                Swal.showValidationMessage('Please select a rating');
                return false;
            }
            
            if (rating.value === 'unsatisfactory' && !remarks) {
                Swal.showValidationMessage('Please provide comments for unsatisfactory rating');
                return false;
            }
            
            return {
                rating: rating.value,
                remarks: remarks
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitFeedback(ticketId, result.value);
        }
    });
}

function submitFeedback(ticketId, feedback) {
    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('rating', feedback.rating);
    formData.append('remarks', feedback.remarks);
    
    fetch(APP_URL + '/customer/tickets/' + ticketId + '/feedback', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.SAMPARK.ui.showSuccess('Feedback Submitted', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            window.SAMPARK.ui.showError('Submission Failed', data.message);
        }
    })
    .catch(error => {
        window.SAMPARK.ui.showError('Error', 'Failed to submit feedback. Please try again.');
    });
}

function shareTicket(ticketId) {
    const shareUrl = window.location.origin + APP_URL + '/customer/tickets/' + ticketId;
    
    Swal.fire({
        title: 'Share Ticket',
        html: `
            <div class="text-start">
                <p class="mb-3">Share this ticket with colleagues or support team:</p>
                <div class="input-group">
                    <input type="text" class="form-control" value="${shareUrl}" id="shareUrl" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyShareUrl()">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <div class="mt-3">
                    <strong>Ticket ID:</strong> #${ticketId}
                </div>
            </div>
        `,
        showConfirmButton: false,
        showCloseButton: true
    });
}

function copyShareUrl() {
    const shareUrl = document.getElementById('shareUrl');
    shareUrl.select();
    document.execCommand('copy');
    window.SAMPARK.ui.showToast('Share URL copied to clipboard', 'success');
}

function printTicket(ticketId) {
    window.open(APP_URL + '/customer/tickets/' + ticketId + '?print=1', '_blank');
}

function exportTickets(format) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('export', format);
    window.open(currentUrl.toString(), '_blank');
}

// Real-time updates are now handled by the background refresh manager
// The old startRealTimeUpdates function is replaced by the new system

// Submit feedback updated to work with DataTables refresh
function submitFeedbackUpdated(ticketId, feedback) {
    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('rating', feedback.rating);
    formData.append('remarks', feedback.remarks);
    
    fetch(APP_URL + '/customer/tickets/' + ticketId + '/feedback', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.SAMPARK.ui.showSuccess('Feedback Submitted', data.message);
            
            // Force refresh instead of page reload
            if (window.backgroundRefreshManager) {
                window.backgroundRefreshManager.forceRefresh();
            } else {
                setTimeout(() => location.reload(), 1500);
            }
        } else {
            window.SAMPARK.ui.showError('Submission Failed', data.message);
        }
    })
    .catch(error => {
        window.SAMPARK.ui.showError('Error', 'Failed to submit feedback. Please try again.');
    });
}

// Global function for datatable
window.provideAdditionalInfo = function(ticketId) {
    showProvideInfoDialog(ticketId);
}

function showProvideInfoDialog(ticketId) {
    Swal.fire({
        title: 'Provide Additional Information',
        html: `
            <div class="text-start">
                <p class="mb-3">Please provide the additional information requested for ticket #${ticketId}.</p>
                
                <div class="mb-3">
                    <label for="additionalInfoText" class="form-label">Additional Information</label>
                    <textarea class="form-control" id="additionalInfoText" rows="5" 
                              placeholder="Provide the requested information, clarifications, or additional details..."></textarea>
                </div>
                
                <!-- File Upload Section -->
                <div class="mb-3">
                    <label class="form-label">Supporting Documents (Optional)</label>
                    <input type="file" class="d-none" id="infoFileInput" accept=".jpg,.jpeg,.png,.gif,.webp,.bmp,.pdf,.doc,.docx,.txt,.xls,.xlsx" multiple>
                    
                    <div class="upload-zone border-2 border-dashed rounded p-3 text-center" id="infoUploadZone">
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt text-muted mb-2" style="font-size: 2rem;"></i>
                            <p class="mb-2">Click to select files or drag and drop</p>
                            <button type="button" class="btn btn-outline-primary btn-sm mb-2">
                                <i class="fas fa-folder-open me-1"></i>Browse Files
                            </button>
                            <small class="text-muted d-block">Maximum 3 files, 2MB each (auto-compressed)</small>
                        </div>
                        
                        <div class="upload-preview mt-3" id="infoUploadPreview"></div>
                        
                        <div class="compression-progress d-none mt-3" id="infoCompressionProgress">
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="loader me-2" style="width: 20px; height: 20px;"></div>
                                <span class="text-muted">Compressing files...</span>
                            </div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 0%" id="infoCompressionBar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Submit Information',
        customClass: {
            confirmButton: 'btn btn-info',
            cancelButton: 'btn btn-secondary'
        },
        width: '700px',
        didOpen: () => {
            setupInfoFileUpload();
        },
        preConfirm: () => {
            const additionalInfo = document.getElementById('additionalInfoText').value.trim();
            
            if (!additionalInfo) {
                Swal.showValidationMessage('Please provide the additional information');
                return false;
            }
            
            // Check if compression is in progress
            if (!document.getElementById('infoCompressionProgress').classList.contains('d-none')) {
                Swal.showValidationMessage('Please wait for file compression to complete');
                return false;
            }
            
            return {
                additionalInfo: additionalInfo,
                files: window.infoCompressedFiles || []
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitAdditionalInfoWithFiles(ticketId, result.value);
        }
        // Cleanup
        window.infoSelectedFiles = [];
        window.infoCompressedFiles = [];
    });
}

// File upload functionality for info dialog (using same system as create-ticket)
window.infoSelectedFiles = [];
window.infoCompressedFiles = [];

function setupInfoFileUpload() {
    const uploadZone = document.getElementById('infoUploadZone');
    const fileInput = document.getElementById('infoFileInput');
    
    // Reset state
    window.infoSelectedFiles = [];
    window.infoCompressedFiles = [];
    
    // Drag and drop handlers
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('border-primary');
    });
    
    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('border-primary');
    });
    
    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('border-primary');
        
        const files = Array.from(e.dataTransfer.files);
        handleInfoFileSelection(files);
    });
    
    // File input change handler
    fileInput.addEventListener('change', function() {
        handleInfoFileSelection(Array.from(this.files));
    });
    
    // Click handler for the entire zone
    uploadZone.addEventListener('click', function(e) {
        // Prevent double triggering
        if (e.target.tagName !== 'INPUT') {
            fileInput.click();
        }
    });
}

function handleInfoFileSelection(files) {
    // Validate file count
    if (window.infoSelectedFiles.length + files.length > 3) {
        Swal.showValidationMessage('Maximum 3 files allowed');
        return;
    }
    
    // Validate each file
    const validFiles = [];
    files.forEach(file => {
        const validation = validateInfoFile(file);
        if (validation.valid) {
            validFiles.push(file);
        } else {
            Swal.showValidationMessage(`${file.name}: ${validation.errors.join(', ')}`);
            return;
        }
    });
    
    if (validFiles.length === 0) return;
    
    // Add to selected files
    window.infoSelectedFiles = window.infoSelectedFiles.concat(validFiles);
    
    // Show compression progress
    showInfoCompressionProgress();
    
    // Compress files
    compressInfoFiles(validFiles);
}

function validateInfoFile(file) {
    const maxSize = 20 * 1024 * 1024; // 20MB (will be compressed to 2MB)
    const allowedTypes = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp',
        'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];
    
    const errors = [];
    
    if (file.size > maxSize) {
        errors.push('File too large (max 20MB before compression)');
    }
    
    if (!allowedTypes.includes(file.type)) {
        errors.push('File type not supported');
    }
    
    return {
        valid: errors.length === 0,
        errors: errors
    };
}

function showInfoCompressionProgress() {
    document.getElementById('infoCompressionProgress').classList.remove('d-none');
}

function hideInfoCompressionProgress() {
    document.getElementById('infoCompressionProgress').classList.add('d-none');
}

function updateInfoCompressionProgress(percent) {
    const progressBar = document.getElementById('infoCompressionBar');
    progressBar.style.width = percent + '%';
}

async function compressInfoFiles(files) {
    const preview = document.getElementById('infoUploadPreview');
    let processedCount = 0;
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        
        // Create preview immediately
        createInfoFilePreview(file, preview, 'compressing');
        
        try {
            // Compress file using the same method as create-ticket
            const compressedFile = await compressFileAsyncInfo(file);
            window.infoCompressedFiles.push(compressedFile);
            
            // Update preview status and show compressed size
            updateInfoFilePreviewStatus(file.name, 'compressed');
            updateInfoFilePreviewSize(file.name, compressedFile.size);
            
        } catch (error) {
            console.error('Compression failed for', file.name, error);
            // Use original file if compression fails (fallback)
            window.infoCompressedFiles.push(file);
            updateInfoFilePreviewStatus(file.name, 'ready');
        }
        
        processedCount++;
        updateInfoCompressionProgress((processedCount / files.length) * 100);
    }
    
    // Hide progress
    hideInfoCompressionProgress();
}

function createInfoFilePreview(file, container, status = 'pending') {
    const previewDiv = document.createElement('div');
    previewDiv.className = 'file-preview mb-2';
    previewDiv.dataset.fileName = file.name;
    
    const fileIcon = getInfoFileIcon(file.type);
    const fileSize = formatInfoFileSize(file.size);
    
    previewDiv.innerHTML = `
        <div class="d-flex align-items-center p-2 border rounded">
            <div class="file-icon me-3">
                <i class="${fileIcon} text-muted"></i>
            </div>
            <div class="file-info flex-grow-1">
                <div class="fw-semibold">${file.name}</div>
                <div class="text-muted small d-flex align-items-center">
                    <span id="original-size-${file.name}">${fileSize}</span>
                    <span id="compressed-size-${file.name}" class="ms-2" style="display: none;"></span>
                </div>
            </div>
            <div class="file-status me-2">
                <span class="badge badge-${getStatusBadgeClass(status)}" id="status-${file.name}">
                    ${getStatusText(status)}
                </span>
            </div>
            <div class="file-actions">
                <button type="button" class="btn btn-link btn-sm text-danger" onclick="removeInfoFile('${file.name}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(previewDiv);
}

function updateInfoFilePreviewStatus(fileName, status) {
    const statusElement = document.getElementById(`status-${fileName}`);
    if (statusElement) {
        switch(status) {
            case 'compressed':
                statusElement.className = 'badge badge-success';
                statusElement.textContent = 'Ready';
                break;
            case 'error':
                statusElement.className = 'badge badge-danger';
                statusElement.textContent = 'Error';
                break;
            case 'ready':
                statusElement.className = 'badge badge-success';
                statusElement.textContent = 'Ready';
                break;
        }
    }
}

function getInfoFileIcon(mimeType) {
    if (mimeType.startsWith('image/')) return 'fas fa-image text-primary';
    if (mimeType === 'application/pdf') return 'fas fa-file-pdf text-danger';
    if (mimeType.includes('word')) return 'fas fa-file-word text-primary';
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'fas fa-file-excel text-success';
    if (mimeType === 'text/plain') return 'fas fa-file-alt text-muted';
    return 'fas fa-file text-muted';
}

function formatInfoFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function compressFileAsync(file) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'compress');
        formData.append('csrf_token', CSRF_TOKEN);
        
        fetch(APP_URL + '/api/compress-file', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.compressed_data) {
                // Convert base64 back to file
                const binaryString = atob(data.compressed_data);
                const bytes = new Uint8Array(binaryString.length);
                for (let i = 0; i < binaryString.length; i++) {
                    bytes[i] = binaryString.charCodeAt(i);
                }
                
                // Create a new File object from the compressed data
                const compressedFile = new File([bytes], file.name, {
                    type: file.type,
                    lastModified: Date.now()
                });
                resolve(compressedFile);
            } else {
                reject(new Error(data.message || 'Compression failed'));
            }
        })
        .catch(error => {
            reject(error);
        });
    });
}

// Additional utility functions (same as create-ticket)
function updateInfoFilePreviewSize(fileName, compressedSize) {
    const compressedSizeElement = document.getElementById(`compressed-size-${fileName}`);
    if (compressedSizeElement) {
        const compressedSizeText = formatInfoFileSize(compressedSize);
        compressedSizeElement.innerHTML = `<span class="text-success">→ ${compressedSizeText}</span>`;
        compressedSizeElement.style.display = 'inline';
    }
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'compressing': return 'warning';
        case 'compressed': return 'success';
        case 'error': return 'danger';
        case 'ready': return 'success';
        default: return 'secondary';
    }
}

function getStatusText(status) {
    switch (status) {
        case 'compressing': return 'Compressing...';
        case 'compressed': return 'Ready';
        case 'error': return 'Error';
        case 'ready': return 'Ready';
        default: return 'Pending';
    }
}

function removeInfoFile(fileName) {
    // Remove from selected files
    window.infoSelectedFiles = window.infoSelectedFiles.filter(file => file.name !== fileName);
    
    // Remove from compressed files
    window.infoCompressedFiles = window.infoCompressedFiles.filter(file => file.name !== fileName);
    
    // Remove preview
    const previewElement = document.querySelector(`[data-file-name="${fileName}"]`);
    if (previewElement) {
        previewElement.remove();
    }
}

function compressFileAsyncInfo(file) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'compress');
        formData.append('csrf_token', CSRF_TOKEN);
        
        fetch(APP_URL + '/api/compress-file', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.compressed_data) {
                // Convert base64 back to file
                const binaryString = atob(data.compressed_data);
                const bytes = new Uint8Array(binaryString.length);
                for (let i = 0; i < binaryString.length; i++) {
                    bytes[i] = binaryString.charCodeAt(i);
                }
                
                // Create a new File object from the compressed data
                const compressedFile = new File([bytes], file.name, {
                    type: file.type,
                    lastModified: Date.now()
                });
                resolve(compressedFile);
            } else {
                reject(new Error(data.message || 'Compression failed'));
            }
        })
        .catch(error => {
            reject(error);
        });
    });
}

function submitAdditionalInfoWithFiles(ticketId, data) {
    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    formData.append('additional_info', data.additionalInfo);
    
    // Add compressed files
    data.files.forEach((file, index) => {
        formData.append(`supporting_files[]`, file);
    });
    
    fetch(APP_URL + '/customer/tickets/' + ticketId + '/provide-info', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(responseData => {
        if (responseData.success) {
            window.SAMPARK.ui.showSuccess('Information Submitted', responseData.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            window.SAMPARK.ui.showError('Submission Failed', responseData.message);
        }
    })
    .catch(error => {
        window.SAMPARK.ui.showError('Error', 'Failed to submit information. Please try again.');
    });
}
</script>

<style>
/* Table enhancements */
.table th {
    background-color: var(--apple-off-white);
    border-bottom: 2px solid rgba(151, 151, 151, 0.1);
    font-weight: 600;
    font-size: 0.875rem;
}

.table td {
    border-color: rgba(151, 151, 151, 0.05);
    padding: 1rem 0.75rem;
}

.table tbody tr:hover {
    background-color: rgba(238, 238, 238, 0.3);
}

/* Status badges */
.badge {
    font-size: 0.75rem;
    padding: 0.375em 0.75em;
    border-radius: var(--apple-radius-small);
}

/* Rating buttons */
.rating-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.rating-buttons input[type="radio"] {
    display: none;
}

.rating-buttons label {
    cursor: pointer;
    transition: all 0.2s ease;
}

.rating-buttons input[type="radio"]:checked + label,
.rating-buttons label.active {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.rating-buttons label.active.btn-outline-success {
    background-color: #28a745;
    color: white;
    border-color: #28a745;
}

.rating-buttons label.active.btn-outline-warning {
    background-color: #ffc107;
    color: #212529;
    border-color: #ffc107;
}

.rating-buttons label.active.btn-outline-danger {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
}

/* Pagination */
.pagination .page-link {
    border: 1px solid rgba(151, 151, 151, 0.2);
    color: var(--apple-black);
    background: var(--apple-white);
    border-radius: var(--apple-radius-small);
    margin: 0 2px;
}

.pagination .page-link:hover {
    background: var(--apple-off-white);
    border-color: var(--apple-blue);
    color: var(--apple-blue);
}

.pagination .page-item.active .page-link {
    background: var(--apple-blue);
    border-color: var(--apple-blue);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.8rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }
    
    .rating-buttons {
        flex-direction: column;
    }
    
    .rating-buttons label {
        text-align: center;
    }
}

/* Auto-close warning animation */
@keyframes urgent-pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.text-danger {
    animation: urgent-pulse 2s infinite;
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>  