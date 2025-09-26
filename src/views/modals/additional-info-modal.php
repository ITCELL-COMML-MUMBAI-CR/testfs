<!-- Additional Information Modal -->
<div class="modal fade" id="additionalInfoModal" tabindex="-1" aria-labelledby="additionalInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="additionalInfoModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>
                    Provide Additional Information
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="additionalInfoForm" enctype="multipart/form-data">
                    <input type="hidden" id="ticketId" name="ticket_id" value="">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" id="removedFiles" name="removed_files" value="">

                    <!-- Revert Message Display -->
                    <div id="revertMessageSection" class="mb-4" style="display: none;">
                        <label class="form-label">Message from SAMPARK TEAM:</label>
                        <div class="alert alert-warning">
                            <div class="small mb-2"><strong>Reason for requesting additional information:</strong></div>
                            <div id="revertMessage"></div>
                        </div>
                    </div>

                    <!-- Additional Information Text -->
                    <div class="mb-4">
                        <label for="additionalInfoText" class="form-label">
                            Additional Information <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="additionalInfoText" name="additional_info" rows="5"
                                  placeholder="Provide the requested information, clarifications, or additional details..." required></textarea>
                        <div class="form-text">Please provide detailed information as requested by the SAMPARK team.</div>
                    </div>

                    <!-- Existing Files Section -->
                    <div id="existingFilesSection" class="mb-4" style="display: none;">
                        <label class="form-label">Current Supporting Images (<span id="currentFileCount">0</span> files uploaded during ticket creation)</label>
                        <div id="existingFilesContainer" class="border rounded p-3 bg-light">
                            <!-- Existing files will be populated here -->
                        </div>
                    </div>

                    <!-- File Upload Section -->
                    <div id="uploadSection" class="mb-4">
                        <label class="form-label">
                            Add Additional Supporting Images (Maximum 2 files)
                        </label>
                        <input type="file" class="d-none" id="fileInput"
                               accept=".jpg,.jpeg,.png,.gif,.webp,.bmp,.heif,.heic,.tiff,.tif"
                               name="supporting_files[]">

                        <div class="upload-zone border-2 border-dashed rounded p-4 text-center" id="uploadZone">
                            <div class="upload-placeholder">
                                <i class="fas fa-cloud-upload-alt text-muted mb-3" style="font-size: 2.5rem;"></i>
                                <p class="mb-2">Upload Additional Supporting Images</p>
                                <button type="button" class="btn btn-outline-primary btn-sm mb-2" onclick="selectAdditionalFiles()">
                                    <i class="fas fa-folder-open me-1"></i>Browse Files
                                </button>
                                <small class="text-muted d-block">Maximum 2 additional files, 50MB each (auto-compressed)</small>
                                <small class="text-muted">Supported: Images, PDF, Word, Excel, Text files</small>
                            </div>

                            <div class="upload-preview mt-3" id="uploadPreview"></div>

                            <div class="compression-progress d-none mt-3" id="compressionProgress">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    <span class="text-muted">Compressing files...</span>
                                </div>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                         role="progressbar" style="width: 0%" id="compressionBar"></div>
                                </div>
                            </div>

                            <!-- File Summary -->
                            <div class="file-summary mt-3" id="fileSummary" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <span id="fileCount">0</span> files selected
                                    </span>
                                    <button type="button" class="btn btn-link btn-sm text-danger" onclick="clearAllFiles()">
                                        <i class="fas fa-trash me-1"></i>Clear All
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- File Limit Warning -->
                    <div id="fileLimitWarning" class="alert alert-warning mb-4" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        You have reached the maximum limit of 2 additional files.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="submitInfoBtn">
                    <i class="fas fa-paper-plane me-1"></i>Submit Information
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.upload-zone {
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-zone:hover {
    border-color: #007bff !important;
    background-color: rgba(0, 123, 255, 0.05);
}

.upload-zone.drag-over {
    border-color: #007bff !important;
    background-color: rgba(0, 123, 255, 0.1);
    transform: scale(1.02);
}

.file-preview {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background: white;
    transition: all 0.2s ease;
}

.file-preview:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.existing-file-preview {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background: #f8f9fa;
    transition: all 0.2s ease;
}

.existing-file-preview:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.file-icon i {
    font-size: 1.25rem;
}

.badge.badge-success {
    background-color: #28a745;
    color: white;
}

.badge.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge.badge-danger {
    background-color: #dc3545;
    color: white;
}

.badge.badge-secondary {
    background-color: #6c757d;
    color: white;
}

@media (max-width: 576px) {
    .modal-dialog {
        margin: 0.5rem;
    }

    .upload-zone {
        padding: 2rem 1rem;
    }

    .file-preview .d-flex {
        flex-direction: column;
        text-align: center;
    }

    .file-actions {
        margin-top: 0.5rem;
    }
}
</style>