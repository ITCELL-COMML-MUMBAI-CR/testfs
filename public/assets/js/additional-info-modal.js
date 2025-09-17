/**
 * Additional Information Modal JavaScript
 * Simple file upload handling for additional files
 */

// Global variables
let selectedAdditionalFiles = [];
let compressedAdditionalFiles = [];
let isCompressing = false;

// Initialize when DOM loads
document.addEventListener('DOMContentLoaded', function() {
    initializeAdditionalInfoModal();
});

function initializeAdditionalInfoModal() {
    const fileInput = document.getElementById('fileInput');

    if (!fileInput) return;

    // Set file input to accept multiple files (max 2)
    fileInput.setAttribute('multiple', 'multiple');

    // File input change handler
    fileInput.addEventListener('change', function() {
        handleAdditionalFileSelection(Array.from(this.files));
        this.value = ''; // Clear input to allow re-selection
    });
}

function selectAdditionalFiles() {
    if (selectedAdditionalFiles.length >= 2) {
        window.SAMPARK.ui.showError('File Limit', 'Maximum 2 additional files allowed');
        return;
    }
    document.getElementById('fileInput').click();
}

function handleAdditionalFileSelection(files) {
    // Validate file count
    if (selectedAdditionalFiles.length + files.length > 2) {
        window.SAMPARK.ui.showError('File Limit', 'Maximum 2 additional files allowed');
        return;
    }

    // Validate each file
    const validFiles = [];
    files.forEach(file => {
        const validation = validateAdditionalFile(file);
        if (validation.valid) {
            validFiles.push(file);
        } else {
            window.SAMPARK.ui.showError('Invalid File', `${file.name}: ${validation.errors.join(', ')}`);
        }
    });

    if (validFiles.length === 0) return;

    // Add to selected files
    selectedAdditionalFiles = selectedAdditionalFiles.concat(validFiles);

    // Show compression progress
    showCompressionProgress();

    // Compress files
    compressAdditionalFiles(validFiles);
}

function validateAdditionalFile(file) {
    const maxSize = 50 * 1024 * 1024; // 50MB
    const allowedTypes = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp',
        'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    const errors = [];

    if (file.size > maxSize) {
        errors.push('File too large (max 50MB before compression)');
    }

    if (!allowedTypes.includes(file.type)) {
        errors.push('File type not supported');
    }

    return {
        valid: errors.length === 0,
        errors: errors
    };
}

function showCompressionProgress() {
    const progressDiv = document.getElementById('compressionProgress');
    const progressBar = document.getElementById('compressionBar');
    const submitButton = document.getElementById('submitInfoBtn');

    if (progressDiv && progressBar) {
        progressDiv.classList.remove('d-none');
        progressBar.style.width = '0%';
    }

    isCompressing = true;

    // Disable submit button during compression
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Compressing Files...';
    }
}

function hideCompressionProgress() {
    const progressDiv = document.getElementById('compressionProgress');
    const submitButton = document.getElementById('submitInfoBtn');

    if (progressDiv) {
        progressDiv.classList.add('d-none');
    }

    isCompressing = false;

    // Re-enable submit button after compression
    if (submitButton) {
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Information';
    }

    updateFileDisplay();
}

function updateCompressionProgress(percent) {
    const progressBar = document.getElementById('compressionBar');
    if (progressBar) {
        progressBar.style.width = percent + '%';
    }
}

async function compressAdditionalFiles(files) {
    const preview = document.getElementById('uploadPreview');
    let processedCount = 0;

    for (let i = 0; i < files.length; i++) {
        const file = files[i];

        // Create preview immediately
        createFilePreview(file, 'compressing');

        try {
            const compressedFile = await compressFile(file);
            compressedAdditionalFiles.push(compressedFile);
            updateFilePreviewStatus(file.name, 'ready');
        } catch (error) {
            console.error('Compression failed for', file.name, error);
            // Use original file if compression fails
            compressedAdditionalFiles.push(file);
            updateFilePreviewStatus(file.name, 'ready');
        }

        processedCount++;
        updateCompressionProgress((processedCount / files.length) * 100);
    }

    hideCompressionProgress();
}

async function compressFile(file) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'compress');
        formData.append('csrf_token', CSRF_TOKEN || '');

        fetch(`${APP_URL}/api/compress-file`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                try {
                    const binaryString = atob(data.compressedData);
                    const bytes = new Uint8Array(binaryString.length);
                    for (let i = 0; i < binaryString.length; i++) {
                        bytes[i] = binaryString.charCodeAt(i);
                    }

                    const compressedFile = new File([bytes], file.name, {
                        type: file.type,
                        lastModified: Date.now()
                    });
                    resolve(compressedFile);
                } catch (error) {
                    reject(new Error('Failed to decode compressed data: ' + error.message));
                }
            } else {
                reject(new Error(data.message || 'Compression failed'));
            }
        })
        .catch(error => {
            reject(error);
        });
    });
}

function createFilePreview(file, status = 'pending') {
    const preview = document.getElementById('uploadPreview');
    if (!preview) return;

    const fileDiv = document.createElement('div');
    fileDiv.className = 'file-preview mb-2 p-3 border rounded';
    fileDiv.dataset.fileName = file.name;

    const fileIcon = getFileIcon(file.type);
    const fileSize = formatFileSize(file.size);

    fileDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="file-icon me-3">
                <i class="${fileIcon} text-muted fa-2x"></i>
            </div>
            <div class="file-info flex-grow-1">
                <div class="fw-semibold">${escapeHtml(file.name)}</div>
                <div class="text-muted small">${fileSize}</div>
            </div>
            <div class="file-status me-2">
                <span class="badge bg-${getStatusBadgeClass(status)}" id="status-${file.name}">
                    ${getStatusText(status)}
                </span>
            </div>
            <div class="file-actions">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeAdditionalFile('${escapeHtml(file.name)}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;

    preview.appendChild(fileDiv);
}

function updateFilePreviewStatus(fileName, status) {
    const statusElement = document.getElementById(`status-${fileName}`);
    if (statusElement) {
        statusElement.className = `badge bg-${getStatusBadgeClass(status)}`;
        statusElement.textContent = getStatusText(status);
    }
}

function removeAdditionalFile(fileName) {
    // Remove from selected files
    selectedAdditionalFiles = selectedAdditionalFiles.filter(file => file.name !== fileName);

    // Remove from compressed files
    compressedAdditionalFiles = compressedAdditionalFiles.filter(file => file.name !== fileName);

    // Remove preview element
    const previewElement = document.querySelector(`[data-file-name="${fileName}"]`);
    if (previewElement) {
        previewElement.remove();
    }

    updateFileDisplay();
}

function clearAllFiles() {
    selectedAdditionalFiles = [];
    compressedAdditionalFiles = [];

    const preview = document.getElementById('uploadPreview');
    if (preview) {
        preview.innerHTML = '';
    }

    updateFileDisplay();
}

function updateFileDisplay() {
    const fileCount = selectedAdditionalFiles.length;
    const fileCountElement = document.getElementById('fileCount');
    const fileSummary = document.getElementById('fileSummary');
    const fileLimitWarning = document.getElementById('fileLimitWarning');
    const uploadZone = document.getElementById('uploadZone');

    if (fileCountElement) {
        fileCountElement.textContent = fileCount;
    }

    if (fileSummary) {
        fileSummary.style.display = fileCount > 0 ? 'block' : 'none';
    }

    // Show/hide warning and upload zone based on file limit
    if (fileCount >= 2) {
        if (fileLimitWarning) {
            fileLimitWarning.style.display = 'block';
            fileLimitWarning.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>You have reached the maximum limit of 2 additional files.';
        }
        if (uploadZone) {
            uploadZone.querySelector('.upload-placeholder').style.display = 'none';
        }
    } else {
        if (fileLimitWarning) {
            fileLimitWarning.style.display = 'none';
        }
        if (uploadZone) {
            uploadZone.querySelector('.upload-placeholder').style.display = 'block';
        }
    }
}

// Utility functions
function getFileIcon(mimeType) {
    if (mimeType.startsWith('image/')) return 'fas fa-image text-primary';
    if (mimeType === 'application/pdf') return 'fas fa-file-pdf text-danger';
    if (mimeType.includes('word')) return 'fas fa-file-word text-primary';
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'fas fa-file-excel text-success';
    if (mimeType === 'text/plain') return 'fas fa-file-alt text-muted';
    return 'fas fa-file text-muted';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'compressing': return 'warning';
        case 'ready': return 'success';
        case 'error': return 'danger';
        default: return 'secondary';
    }
}

function getStatusText(status) {
    switch (status) {
        case 'compressing': return 'Compressing...';
        case 'ready': return 'Ready';
        case 'error': return 'Error';
        default: return 'Pending';
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Additional Info Modal specific functions
class AdditionalInfoModal {
    constructor() {
        this.modal = null;
        this.ticketId = null;
        this.existingFiles = [];
        this.isSubmitting = false;

        this.init();
    }

    init() {
        this.modal = document.getElementById('additionalInfoModal');
        this.submitBtn = document.getElementById('submitInfoBtn');

        this.setupEventListeners();
    }

    setupEventListeners() {
        if (this.submitBtn) {
            this.submitBtn.addEventListener('click', this.handleSubmit.bind(this));
        }

        // Modal close cleanup
        if (this.modal) {
            this.modal.addEventListener('hidden.bs.modal', () => {
                this.cleanup();
            });
        }
    }

    async show(ticketId) {
        try {
            this.ticketId = ticketId;
            document.getElementById('ticketId').value = ticketId;

            // Load ticket data and existing files
            const response = await fetch(`${APP_URL}/api/tickets/${ticketId}/additional-info-modal`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to load ticket data');
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Failed to load ticket data');
            }

            // Populate modal with data
            this.populateModal(data);

            // Show modal
            const bsModal = new bootstrap.Modal(this.modal);
            bsModal.show();

        } catch (error) {
            console.error('Error loading additional info modal:', error);
            window.SAMPARK.ui.showError('Error', 'Failed to load modal: ' + error.message);
        }
    }

    populateModal(data) {
        const { ticket, existingFiles, latestRevert } = data;

        // Store existing files
        this.existingFiles = existingFiles || [];

        // Update modal title
        document.getElementById('additionalInfoModalLabel').innerHTML = `
            <i class="fas fa-plus-circle me-2"></i>
            Provide Additional Information - Ticket #${ticket.complaint_id}
        `;

        // Show revert message if available
        if (latestRevert) {
            const revertSection = document.getElementById('revertMessageSection');
            const revertMessage = document.getElementById('revertMessage');
            if (revertMessage && revertSection) {
                revertMessage.innerHTML = escapeHtml(latestRevert.remarks);
                revertSection.style.display = 'block';
            }
        }

        // Show existing files if any
        if (this.existingFiles.length > 0) {
            this.displayExistingFiles();
        }
    }

    displayExistingFiles() {
        const existingSection = document.getElementById('existingFilesSection');
        const existingContainer = document.getElementById('existingFilesContainer');
        const currentFileCount = document.getElementById('currentFileCount');

        if (!existingSection || !existingContainer || !currentFileCount) return;

        if (this.existingFiles.length === 0) {
            existingSection.style.display = 'none';
            return;
        }

        existingSection.style.display = 'block';
        currentFileCount.textContent = this.existingFiles.length;

        existingContainer.innerHTML = '';

        this.existingFiles.forEach(file => {
            const fileElement = this.createExistingFilePreview(file);
            existingContainer.appendChild(fileElement);
        });
    }

    createExistingFilePreview(file) {
        const div = document.createElement('div');
        div.className = 'existing-file-preview mb-2';

        const fileIcon = getFileIcon(file.fileType || this.getFileTypeFromExtension(file.extension));
        const fileSize = formatFileSize(file.fileSize);

        div.innerHTML = `
            <div class="d-flex align-items-center p-2 border rounded bg-light">
                <div class="file-icon me-3">
                    <i class="${fileIcon} text-muted"></i>
                </div>
                <div class="file-info flex-grow-1">
                    <div class="fw-semibold">${escapeHtml(file.originalName)}</div>
                    <div class="text-muted small">${fileSize}</div>
                </div>
                <div class="file-actions">
                    <button type="button" class="btn btn-link btn-sm text-primary view-file-btn"
                            onclick="viewFile('${file.filePath}', '${escapeHtml(file.originalName)}')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        `;

        return div;
    }

    getFileTypeFromExtension(extension) {
        const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        if (imageTypes.includes(extension)) return 'image/' + extension;
        if (extension === 'pdf') return 'application/pdf';
        if (extension === 'doc' || extension === 'docx') return 'application/msword';
        if (extension === 'xls' || extension === 'xlsx') return 'application/vnd.ms-excel';
        if (extension === 'txt') return 'text/plain';
        return 'application/octet-stream';
    }

    async handleSubmit() {
        if (this.isSubmitting) return;

        const additionalInfo = document.getElementById('additionalInfoText').value.trim();

        if (!additionalInfo) {
            window.SAMPARK.ui.showError('Missing Information', 'Please provide the additional information');
            return;
        }

        // Check if compression is in progress
        if (isCompressing) {
            window.SAMPARK.ui.showError('Please Wait', 'Please wait for file compression to complete');
            return;
        }

        try {
            this.isSubmitting = true;
            this.submitBtn.disabled = true;
            this.submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';

            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN || '');
            formData.append('additional_info', additionalInfo);

            // Add compressed additional files
            compressedAdditionalFiles.forEach((file, index) => {
                formData.append(`additional_files[]`, file);
            });

            const response = await fetch(`${APP_URL}/customer/tickets/${this.ticketId}/provide-info`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Show enhanced success message with view link and info provided
                let successMessage = `<p>${data.message}</p>`;
                if (data.details) {
                    successMessage += `<p><strong>Information Provided:</strong><br>"${data.details.info_provided}"</p>`;
                    successMessage += `<div class="mt-3">
                        <a href="${data.details.view_url}" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>View Ticket
                        </a>
                    </div>`;
                }

                window.SAMPARK.ui.showSuccess('Information Submitted Successfully', successMessage);

                // Close modal
                const bsModal = bootstrap.Modal.getInstance(this.modal);
                if (bsModal) {
                    bsModal.hide();
                }

                // Reload page after delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Submission failed');
            }

        } catch (error) {
            console.error('Submit error:', error);
            window.SAMPARK.ui.showError('Submission Failed', error.message);
        } finally {
            this.isSubmitting = false;
            this.submitBtn.disabled = false;
            this.submitBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Information';
        }
    }

    cleanup() {
        // Reset all state
        this.ticketId = null;
        this.existingFiles = [];
        this.isSubmitting = false;

        // Clear form
        const form = document.getElementById('additionalInfoForm');
        if (form) form.reset();

        const ticketIdInput = document.getElementById('ticketId');
        const additionalInfoText = document.getElementById('additionalInfoText');

        if (ticketIdInput) ticketIdInput.value = '';
        if (additionalInfoText) additionalInfoText.value = '';

        // Clear file selections
        selectedAdditionalFiles = [];
        compressedAdditionalFiles = [];

        // Clear containers
        const existingContainer = document.getElementById('existingFilesContainer');
        const uploadPreview = document.getElementById('uploadPreview');

        if (existingContainer) existingContainer.innerHTML = '';
        if (uploadPreview) uploadPreview.innerHTML = '';

        // Hide sections
        const revertSection = document.getElementById('revertMessageSection');
        const existingSection = document.getElementById('existingFilesSection');
        const fileLimitWarning = document.getElementById('fileLimitWarning');

        if (revertSection) revertSection.style.display = 'none';
        if (existingSection) existingSection.style.display = 'none';
        if (fileLimitWarning) fileLimitWarning.style.display = 'none';

        // Reset file input
        const fileInput = document.getElementById('fileInput');
        if (fileInput) fileInput.value = '';

        // Hide compression progress
        hideCompressionProgress();

        updateFileDisplay();
    }
}

function viewFile(filePath, fileName) {
    const extension = fileName.split('.').pop().toLowerCase();
    const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    if (imageTypes.includes(extension)) {
        Swal.fire({
            title: fileName,
            imageUrl: filePath,
            imageAlt: fileName,
            showCloseButton: true,
            showConfirmButton: false,
            width: '80%',
            padding: '1rem'
        });
    } else {
        window.open(filePath, '_blank');
    }
}

// Global instance
window.additionalInfoModal = new AdditionalInfoModal();

// Global function to show modal
window.showAdditionalInfoModal = function(ticketId) {
    window.additionalInfoModal.show(ticketId);
};

// Backward compatibility
window.provideAdditionalInfo = function(ticketId) {
    window.additionalInfoModal.show(ticketId);
};