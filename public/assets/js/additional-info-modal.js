/**
 * Additional Information Modal JavaScript
 * Handles file uploads, compression, and form submission
 */

class AdditionalInfoModal {
    constructor() {
        this.modal = null;
        this.ticketId = null;
        this.existingFiles = [];
        this.removedFiles = [];
        this.selectedFiles = [];
        this.compressedFiles = [];
        this.isSubmitting = false;
        this.maxFiles = 3;

        this.init();
    }

    init() {
        // Initialize modal elements
        this.modal = document.getElementById('additionalInfoModal');
        this.fileInput = document.getElementById('fileInput');
        this.uploadZone = document.getElementById('uploadZone');
        this.uploadPreview = document.getElementById('uploadPreview');
        this.compressionProgress = document.getElementById('compressionProgress');
        this.compressionBar = document.getElementById('compressionBar');
        this.submitBtn = document.getElementById('submitInfoBtn');

        this.setupEventListeners();
    }

    setupEventListeners() {
        // File input change
        this.fileInput.addEventListener('change', (e) => {
            // Validate slot availability before processing
            const currentExistingFiles = this.existingFiles.length - this.removedFiles.length;
            const currentNewFiles = this.compressedFiles.length;
            const availableSlots = this.maxFiles - currentExistingFiles - currentNewFiles;

            if (e.target.files.length > availableSlots) {
                window.SAMPARK.ui.showError('Too Many Files', `You can only select ${availableSlots} file(s). Total limit is ${this.maxFiles} files per ticket.`);
                e.target.value = '';
                return;
            }

            this.handleFileSelection(Array.from(e.target.files));
            // Clear the input so the same files can be selected again if needed
            e.target.value = '';
        });

        // Drag and drop
        this.uploadZone.addEventListener('dragover', this.handleDragOver.bind(this));
        this.uploadZone.addEventListener('dragleave', this.handleDragLeave.bind(this));
        this.uploadZone.addEventListener('drop', this.handleDrop.bind(this));

        // Upload zone click
        this.uploadZone.addEventListener('click', (e) => {
            if (e.target.tagName !== 'INPUT') {
                // Check available slots and update input accordingly
                const currentExistingFiles = this.existingFiles.length - this.removedFiles.length;
                const currentNewFiles = this.compressedFiles.length;
                const availableSlots = this.maxFiles - currentExistingFiles - currentNewFiles;

                if (availableSlots <= 0) {
                    window.SAMPARK.ui.showError('No Slots Available', 'You have reached the maximum limit of 3 files. Please remove existing files to add new ones.');
                    return;
                }

                // Set file input to multiple only if more than 1 slot available
                if (availableSlots === 1) {
                    this.fileInput.removeAttribute('multiple');
                } else {
                    this.fileInput.setAttribute('multiple', 'multiple');
                }

                this.fileInput.click();
            }
        });

        // Submit button
        this.submitBtn.addEventListener('click', this.handleSubmit.bind(this));

        // Event delegation for dynamic buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.remove-file-btn')) {
                const button = e.target.closest('.remove-file-btn');
                const fileName = button.getAttribute('data-file-name');
                this.removeNewFile(fileName);
            } else if (e.target.closest('.remove-existing-file-btn')) {
                const button = e.target.closest('.remove-existing-file-btn');
                const fileId = button.getAttribute('data-file-id');
                this.removeExistingFile(fileId);
            } else if (e.target.closest('.view-file-btn')) {
                const button = e.target.closest('.view-file-btn');
                const filePath = button.getAttribute('data-file-path');
                const fileName = button.getAttribute('data-file-name');
                this.viewFile(filePath, fileName);
            }
        });

        // Modal close cleanup
        this.modal.addEventListener('hidden.bs.modal', () => {
            this.cleanup();
        });
    }

    async show(ticketId) {
        try {
            this.ticketId = ticketId;
            document.getElementById('ticketId').value = ticketId;

            // Show loading state
            this.showLoading();

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
        const { ticket, existingFiles, latestRevert, availableSlots } = data;

        // Store existing files
        this.existingFiles = existingFiles || [];

        // Update modal title with ticket ID
        document.getElementById('additionalInfoModalLabel').innerHTML = `
            <i class="fas fa-plus-circle me-2"></i>
            Provide Additional Information - Ticket #${ticket.complaint_id}
        `;

        // Show revert message if available
        if (latestRevert) {
            const revertSection = document.getElementById('revertMessageSection');
            const revertMessage = document.getElementById('revertMessage');
            revertMessage.innerHTML = this.escapeHtml(latestRevert.remarks);
            revertSection.style.display = 'block';
        }

        // Show existing files if any
        if (this.existingFiles.length > 0) {
            this.displayExistingFiles();
        }

        // Update available slots
        this.updateAvailableSlots();

        this.hideLoading();
    }

    displayExistingFiles() {
        const existingSection = document.getElementById('existingFilesSection');
        const existingContainer = document.getElementById('existingFilesContainer');
        const currentFileCount = document.getElementById('currentFileCount');

        if (this.existingFiles.length === 0) {
            existingSection.style.display = 'none';
            return;
        }

        existingSection.style.display = 'block';
        currentFileCount.textContent = this.existingFiles.length;

        existingContainer.innerHTML = '';

        this.existingFiles.forEach(file => {
            if (!this.removedFiles.includes(file.id)) {
                const fileElement = this.createExistingFilePreview(file);
                existingContainer.appendChild(fileElement);
            }
        });
    }

    createExistingFilePreview(file) {
        const div = document.createElement('div');
        div.className = 'existing-file-preview mb-2';
        div.dataset.fileId = file.id;

        const fileIcon = this.getFileIcon(file.fileType || this.getFileTypeFromExtension(file.extension));
        const fileSize = this.formatFileSize(file.fileSize);

        div.innerHTML = `
            <div class="d-flex align-items-center p-2 border rounded bg-light">
                <div class="file-icon me-3">
                    <i class="${fileIcon} text-muted"></i>
                </div>
                <div class="file-info flex-grow-1">
                    <div class="fw-semibold">${this.escapeHtml(file.originalName)}</div>
                    <div class="text-muted small">${fileSize}</div>
                </div>
                <div class="file-actions">
                    <button type="button" class="btn btn-link btn-sm text-primary me-2 view-file-btn"
                            data-file-path="${file.filePath}" data-file-name="${this.escapeHtml(file.originalName)}">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-link btn-sm text-danger remove-existing-file-btn"
                            data-file-id="${file.id}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;

        return div;
    }

    handleDragOver(e) {
        e.preventDefault();
        this.uploadZone.classList.add('drag-over');
    }

    handleDragLeave(e) {
        e.preventDefault();
        this.uploadZone.classList.remove('drag-over');
    }

    handleDrop(e) {
        e.preventDefault();
        this.uploadZone.classList.remove('drag-over');

        // Check available slots before processing dropped files
        const currentExistingFiles = this.existingFiles.length - this.removedFiles.length;
        const currentNewFiles = this.compressedFiles.length;
        const availableSlots = this.maxFiles - currentExistingFiles - currentNewFiles;

        if (availableSlots <= 0) {
            window.SAMPARK.ui.showError('No Slots Available', 'You have reached the maximum limit of 3 files. Please remove existing files to add new ones.');
            return;
        }

        const files = Array.from(e.dataTransfer.files);
        this.handleFileSelection(files);
    }

    async handleFileSelection(files) {
        // Calculate available slots
        const currentExistingFiles = this.existingFiles.length - this.removedFiles.length;
        const currentNewFiles = this.compressedFiles.length;
        const availableSlots = this.maxFiles - currentExistingFiles - currentNewFiles;

        if (files.length > availableSlots) {
            window.SAMPARK.ui.showError('Too Many Files', `You can only add ${availableSlots} more file(s). Total limit is ${this.maxFiles} files per ticket.`);
            return;
        }

        // Validate files
        const validFiles = [];
        for (const file of files) {
            const validation = this.validateFile(file);
            if (validation.valid) {
                validFiles.push(file);
            } else {
                window.SAMPARK.ui.showError('Invalid File', `${file.name}: ${validation.errors.join(', ')}`);
                return;
            }
        }

        if (validFiles.length === 0) return;

        // Add to selected files
        this.selectedFiles = this.selectedFiles.concat(validFiles);

        // Show compression progress
        this.showCompressionProgress();

        // Compress files
        await this.compressFiles(validFiles);
    }

    validateFile(file) {
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

    async compressFiles(files) {
        let processedCount = 0;

        for (const file of files) {
            // Create preview immediately
            this.createFilePreview(file, 'compressing');

            try {
                const compressedFile = await this.compressFile(file);
                this.compressedFiles.push(compressedFile);

                // Update preview status
                this.updateFilePreviewStatus(file.name, 'ready');
                this.updateFilePreviewSize(file.name, compressedFile.size);

            } catch (error) {
                console.error('Compression failed for', file.name, error);
                // Use original file if compression fails
                this.compressedFiles.push(file);
                this.updateFilePreviewStatus(file.name, 'ready');
            }

            processedCount++;
            this.updateCompressionProgress((processedCount / files.length) * 100);
        }

        // Hide progress
        this.hideCompressionProgress();
        this.updateAvailableSlots();
    }

    async compressFile(file) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('action', 'compress');
            formData.append('csrf_token', CSRF_TOKEN);

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

    createFilePreview(file, status = 'pending') {
        const previewDiv = document.createElement('div');
        previewDiv.className = 'file-preview mb-2';
        previewDiv.dataset.fileName = file.name;

        const fileIcon = this.getFileIcon(file.type);
        const fileSize = this.formatFileSize(file.size);

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
                    <span class="badge badge-${this.getStatusBadgeClass(status)}" id="status-${file.name}">
                        ${this.getStatusText(status)}
                    </span>
                </div>
                <div class="file-actions">
                    <button type="button" class="btn btn-link btn-sm text-danger remove-file-btn"
                            data-file-name="${file.name}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;

        this.uploadPreview.appendChild(previewDiv);
    }

    updateFilePreviewStatus(fileName, status) {
        const statusElement = document.getElementById(`status-${fileName}`);
        if (statusElement) {
            statusElement.className = `badge badge-${this.getStatusBadgeClass(status)}`;
            statusElement.textContent = this.getStatusText(status);
        }
    }

    updateFilePreviewSize(fileName, compressedSize) {
        const compressedSizeElement = document.getElementById(`compressed-size-${fileName}`);
        if (compressedSizeElement) {
            const compressedSizeText = this.formatFileSize(compressedSize);
            compressedSizeElement.innerHTML = `<span class="text-success">→ ${compressedSizeText}</span>`;
            compressedSizeElement.style.display = 'inline';
        }
    }

    removeNewFile(fileName) {
        // Remove from selected files
        this.selectedFiles = this.selectedFiles.filter(file => file.name !== fileName);

        // Remove from compressed files
        this.compressedFiles = this.compressedFiles.filter(file => file.name !== fileName);

        // Remove preview element
        const previewElement = this.uploadPreview.querySelector(`[data-file-name="${fileName}"]`);
        if (previewElement) {
            previewElement.remove();
        }

        this.updateAvailableSlots();
    }

    removeExistingFile(fileId) {
        // Add to removed files array
        this.removedFiles.push(parseInt(fileId));

        // Hide from UI
        const fileElement = document.querySelector(`[data-file-id="${fileId}"]`);
        if (fileElement) {
            fileElement.remove();
        }

        this.updateAvailableSlots();
        this.displayExistingFiles();
    }

    updateAvailableSlots() {
        const currentExistingFiles = this.existingFiles.length - this.removedFiles.length;
        const currentNewFiles = this.compressedFiles.length;
        const availableSlots = this.maxFiles - currentExistingFiles - currentNewFiles;

        document.getElementById('availableSlots').textContent = availableSlots;
        document.getElementById('currentFileCount').textContent = currentExistingFiles;

        // Show/hide upload section and warning
        const uploadSection = document.getElementById('uploadSection');
        const fileLimitWarning = document.getElementById('fileLimitWarning');

        if (availableSlots <= 0) {
            uploadSection.style.display = 'none';
            fileLimitWarning.style.display = 'block';
        } else {
            uploadSection.style.display = 'block';
            fileLimitWarning.style.display = 'none';
        }

        // Update file input multiple attribute based on available slots
        if (this.fileInput) {
            if (availableSlots === 1) {
                this.fileInput.removeAttribute('multiple');
            } else if (availableSlots > 1) {
                this.fileInput.setAttribute('multiple', 'multiple');
            }
        }
    }

    viewFile(filePath, fileName) {
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

    async handleSubmit() {
        if (this.isSubmitting) return;

        const additionalInfo = document.getElementById('additionalInfoText').value.trim();

        if (!additionalInfo) {
            window.SAMPARK.ui.showError('Missing Information', 'Please provide the additional information');
            return;
        }

        // Check if compression is in progress
        if (!this.compressionProgress.classList.contains('d-none')) {
            window.SAMPARK.ui.showError('Please Wait', 'Please wait for file compression to complete');
            return;
        }

        // Show confirmation dialog if files will be deleted
        if (this.removedFiles.length > 0) {
            const result = await Swal.fire({
                title: 'Confirm File Deletion',
                html: `
                    <p>You have marked ${this.removedFiles.length} existing file(s) for deletion.</p>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>This action cannot be undone!</strong><br>
                        The selected files will be permanently deleted when you submit.
                    </div>
                    <p>Do you want to continue?</p>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete and Submit',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d'
            });

            if (!result.isConfirmed) {
                return;
            }
        }

        try {
            this.isSubmitting = true;
            this.submitBtn.disabled = true;
            this.submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';

            const formData = new FormData();
            formData.append('csrf_token', CSRF_TOKEN);
            formData.append('additional_info', additionalInfo);

            // Add compressed files
            this.compressedFiles.forEach((file, index) => {
                formData.append(`supporting_files[]`, file);
            });

            // Add removed files
            if (this.removedFiles.length > 0) {
                formData.append('removed_files', JSON.stringify(this.removedFiles));
            }

            const response = await fetch(`${APP_URL}/customer/tickets/${this.ticketId}/provide-info`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                window.SAMPARK.ui.showSuccess('Information Submitted', data.message);

                // Close modal
                const bsModal = bootstrap.Modal.getInstance(this.modal);
                bsModal.hide();

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

    showCompressionProgress() {
        this.compressionProgress.classList.remove('d-none');
    }

    hideCompressionProgress() {
        this.compressionProgress.classList.add('d-none');
    }

    updateCompressionProgress(percent) {
        this.compressionBar.style.width = percent + '%';
    }

    showLoading() {
        // Add loading state to modal body
    }

    hideLoading() {
        // Remove loading state
    }

    cleanup() {
        // Reset all state
        this.ticketId = null;
        this.existingFiles = [];
        this.removedFiles = [];
        this.selectedFiles = [];
        this.compressedFiles = [];
        this.isSubmitting = false;

        // Clear form
        document.getElementById('additionalInfoForm').reset();
        document.getElementById('ticketId').value = '';
        document.getElementById('removedFiles').value = '';
        document.getElementById('additionalInfoText').value = '';

        // Clear containers
        document.getElementById('existingFilesContainer').innerHTML = '';
        this.uploadPreview.innerHTML = '';

        // Hide sections
        document.getElementById('revertMessageSection').style.display = 'none';
        document.getElementById('existingFilesSection').style.display = 'none';
        document.getElementById('fileLimitWarning').style.display = 'none';
        document.getElementById('uploadSection').style.display = 'block';

        // Reset file input
        this.fileInput.value = '';

        // Hide compression progress
        this.hideCompressionProgress();

        // Reset button
        this.submitBtn.disabled = false;
        this.submitBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Information';
    }

    // Utility functions
    getFileIcon(mimeType) {
        if (mimeType.startsWith('image/')) return 'fas fa-image text-primary';
        if (mimeType === 'application/pdf') return 'fas fa-file-pdf text-danger';
        if (mimeType.includes('word')) return 'fas fa-file-word text-primary';
        if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'fas fa-file-excel text-success';
        if (mimeType === 'text/plain') return 'fas fa-file-alt text-muted';
        return 'fas fa-file text-muted';
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

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    getStatusBadgeClass(status) {
        switch (status) {
            case 'compressing': return 'warning';
            case 'ready': return 'success';
            case 'error': return 'danger';
            default: return 'secondary';
        }
    }

    getStatusText(status) {
        switch (status) {
            case 'compressing': return 'Compressing...';
            case 'ready': return 'Ready';
            case 'error': return 'Error';
            default: return 'Pending';
        }
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    unescapeHtml(text) {
        const map = {
            '&amp;': '&',
            '&lt;': '<',
            '&gt;': '>',
            '&quot;': '"',
            '&#039;': "'"
        };
        return text.replace(/&amp;|&lt;|&gt;|&quot;|&#039;/g, function(m) { return map[m]; });
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