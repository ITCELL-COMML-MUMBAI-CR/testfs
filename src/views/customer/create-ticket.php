<?php
// Capture the content
ob_start();
?>

<!-- Create Support Ticket -->
<section class="py-apple-6">
    <div class="container-xl">
        
        <!-- Page Header -->
        <div class="row mb-4 text-center">
            <div class="col-12">
                <h1 class="display-3 mb-2">Create Support Ticket</h1>
                <p class="text-muted">Submit your freight support request with detailed information</p>
            </div>
        </div>
        
        <div class="row g-4 justify-content-center">
            
            <!-- Main Form -->
            <div class="col-12 col-lg-10 col-xl-8">
                
                <!-- Customer Details Card -->
                <div class="card-apple mb-4">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-user text-apple-blue me-2"></i>
                            Customer Information
                        </h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-apple">Name</label>
                                <input type="text" 
                                       class="form-control form-control-apple" 
                                       value="<?= htmlspecialchars($customer['name']) ?>" 
                                       readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple">Email</label>
                                <input type="email" 
                                       class="form-control form-control-apple" 
                                       value="<?= htmlspecialchars($customer['email']) ?>" 
                                       readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple">Mobile</label>
                                <input type="text" 
                                       class="form-control form-control-apple" 
                                       value="<?= htmlspecialchars($customer['mobile']) ?>" 
                                       readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-apple">Company</label>
                                <input type="text" 
                                       class="form-control form-control-apple" 
                                       value="<?= htmlspecialchars($customer['company_name']) ?>" 
                                       readonly>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <a href="<?= Config::getAppUrl() ?>/customer/profile" class="btn btn-apple-glass btn-sm">
                                <i class="fas fa-edit me-1"></i>Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Ticket Form Card -->
                <div class="card-apple">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-ticket-alt text-apple-blue me-2"></i>
                            Ticket Details
                        </h5>
                        
                        <form id="ticketForm" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                            
                            <!-- Issue Type Section -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="type" class="form-label-apple required">Issue Type</label>
                                    <select class="form-control form-control-apple" id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <?php 
                                        // Get all unique types from categories
                                        $allTypes = [];
                                        foreach ($categories as $categoryData) {
                                            foreach (array_keys($categoryData) as $type) {
                                                if (!in_array($type, $allTypes)) {
                                                    $allTypes[] = $type;
                                                }
                                            }
                                        }
                                        foreach ($allTypes as $type): ?>
                                            <option value="<?= htmlspecialchars($type) ?>">
                                                <?= htmlspecialchars($type) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="subtype" class="form-label-apple required">Issue Subtype</label>
                                    <select class="form-control form-control-apple" id="subtype" name="subtype" required disabled>
                                        <option value="">Select Subtype</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                    <input type="hidden" id="category_id" name="category_id">
                                </div>
                            </div>
                            
                            <!-- Reference Numbers Section -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="fnr_number" class="form-label-apple">FNR Number</label>
                                    <input type="text"
                                           class="form-control form-control-apple"
                                           id="fnr_number"
                                           name="fnr_number"
                                           placeholder="Enter 11-digit FNR number"
                                           pattern="[0-9]{11}"
                                           maxlength="11"
                                           title="FNR number must be exactly 11 digits">
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="e_indent_number" class="form-label-apple">e-Indent Number</label>
                                    <input type="text" 
                                           class="form-control form-control-apple" 
                                           id="e_indent_number" 
                                           name="e_indent_number" 
                                           placeholder="Enter e-Indent number">
                                </div>
                            </div>
                            
                            <!-- Location Section -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="division_filter" class="form-label-apple">Filter by Division</label>
                                    <select class="form-control form-control-apple" id="division_filter">
                                        <option value="">All Divisions</option>
                                        <?php foreach ($divisions as $div): ?>
                                            <option value="<?= htmlspecialchars($div['division']) ?>"
                                                    <?= ($customer['division'] ?? '') === $div['division'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($div['division']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="shed_id" class="form-label-apple required">Shed/Terminal</label>
                                    <select class="form-control form-control-apple" id="shed_id" name="shed_id" required>
                                        <option value="">Search and select shed...</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            
                            <!-- Wagon Details Section -->
                            <div class="row g-3 mb-4">
                                <div class="col-md-12">
                                    <label for="wagon_type" class="form-label-apple">Wagon Type</label>
                                    <select class="form-control form-control-apple" id="wagon_type" name="wagon_type">
                                        <option value="">Select Wagon Type (Optional)</option>
                                        <?php foreach ($wagon_types as $code => $label): ?>
                                            <option value="<?= htmlspecialchars($code) ?>"><?= htmlspecialchars($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            
                            
                            <!-- Description Section -->
                            <div class="mb-4">
                                <label for="description" class="form-label-apple required">Description</label>
                                <textarea class="form-control form-control-apple" 
                                          id="description" 
                                          name="description" 
                                          rows="6" 
                                          minlength="20" 
                                          maxlength="2000" 
                                          placeholder="Provide a detailed description of the issue including location, time, involved parties, and specific problems encountered..."
                                          required></textarea>
                                <div class="d-flex justify-content-between">
                                    <div class="invalid-feedback"></div>
                                    <small class="text-muted">
                                        <span id="charCount">0</span>/2000 characters (minimum 20)
                                    </small>
                                </div>
                            </div>
                            
                            <!-- File Upload Section -->
                            <div class="mb-4">
                                <label class="form-label-apple">Supporting Images</label>
                                
                                <!-- Hidden file input -->
                                <input type="file" class="d-none" id="fileInput" accept=".jpg,.jpeg,.png,.gif,.webp,.bmp,.heif,.heic,.tiff,.tif" multiple>
                                
                                <!-- Upload Zone -->
                                <div class="upload-zone border-2 border-dashed rounded p-4 text-center" id="uploadZone">
                                    <div class="upload-placeholder">
                                        <i class="fas fa-cloud-upload-alt text-muted mb-3" style="font-size: 3rem;"></i>
                                        <h5 class="mb-3">Upload Supporting Images</h5>
                                        <button type="button" class="btn btn-apple-primary btn-lg mb-3" onclick="selectFiles()">
                                            <i class="fas fa-folder-open me-2"></i>Browse Files
                                        </button>
                                        <p class="mb-2 text-muted">or drag and drop files here</p>
                                        <small class="text-muted">
                                            Maximum 3 files, Max 5MB each (auto-compressed)<br>
                                            Supported: Images only (JPG, PNG, GIF, WebP, BMP, HEIF, HEIC, TIFF)
                                        </small>
                                    </div>
                                    
                                    <!-- File Preview Container -->
                                    <div class="upload-preview mt-3" id="uploadPreview"></div>
                                    
                                    <!-- Compression Progress -->
                                    <div class="compression-progress d-none mt-3" id="compressionProgress">
                                        <div class="d-flex align-items-center">
                                            <div class="loader me-2" style="width: 20px; height: 20px;"></div>
                                            <span class="text-muted">Compressing files...</span>
                                        </div>
                                        <div class="progress mt-2" style="height: 4px;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                 role="progressbar" style="width: 0%" id="compressionBar"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- File Summary -->
                                <div class="file-summary mt-3" id="fileSummary" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">
                                            <span id="fileCount">0</span> files selected, 
                                            <span id="totalSize">0 MB</span> total
                                        </span>
                                        <button type="button" class="btn btn-link btn-sm text-danger" onclick="clearAllFiles()">
                                            <i class="fas fa-trash me-1"></i>Clear All
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-apple-primary btn-lg" id="submitButton">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Submit Support Ticket
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            
        </div>
    </div>
</section>

<script>
// Categories data from PHP
const categoriesData = <?= json_encode($categories) ?>;
// Shed data from PHP
const shedsData = <?= json_encode($sheds) ?>;
// Divisions data from PHP
const divisionsData = <?= json_encode($divisions) ?>;
// Customer division
const customerDivision = <?= json_encode($customer['division'] ?? '') ?>;

document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    setupFileUpload();
    setupValidation();
});

function initializeForm() {
    // Setup character counter
    const description = document.getElementById('description');
    const charCount = document.getElementById('charCount');

    description.addEventListener('input', function() {
        charCount.textContent = this.value.length;

        if (this.value.length < 20) {
            charCount.className = 'text-danger';
        } else if (this.value.length > 1800) {
            charCount.className = 'text-warning';
        } else {
            charCount.className = 'text-success';
        }
    });

    // Setup FNR number validation
    const fnrInput = document.getElementById('fnr_number');
    if (fnrInput) {
        fnrInput.addEventListener('input', function(e) {
            // Remove non-digit characters
            this.value = this.value.replace(/[^0-9]/g, '');

            // Validate length
            if (this.value.length > 0 && this.value.length !== 11) {
                this.setCustomValidity('FNR number must be exactly 11 digits');
            } else {
                this.setCustomValidity('');
            }
        });

        // Prevent paste of non-numeric content
        fnrInput.addEventListener('paste', function(e) {
            const pastedData = e.clipboardData.getData('text');
            if (!/^\d+$/.test(pastedData)) {
                e.preventDefault();
            }
        });
    }

    // Setup category cascading
    setupCategoryCascading();

    // Setup division filter first, then shed search
    setupDivisionFilter();
    setupShedSearch();

    // Setup wagon type search
    setupWagonTypeSearch();
}

function setupCategoryCascading() {
    const typeSelect = document.getElementById('type');
    const subtypeSelect = document.getElementById('subtype');
    const categoryIdInput = document.getElementById('category_id');
    
    typeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        subtypeSelect.innerHTML = '<option value="">Select Subtype</option>';
        
        if (selectedType) {
            // Find all subtypes for the selected type across all categories
            const subtypes = [];
            Object.keys(categoriesData).forEach(category => {
                if (categoriesData[category][selectedType]) {
                    categoriesData[category][selectedType].forEach(subtype => {
                        subtypes.push({
                            subtype: subtype.subtype,
                            category_id: subtype.category_id,
                            category: category
                        });
                    });
                }
            });
            
            // Remove duplicates based on subtype name
            const uniqueSubtypes = [];
            const seen = new Set();
            subtypes.forEach(subtype => {
                if (!seen.has(subtype.subtype)) {
                    seen.add(subtype.subtype);
                    uniqueSubtypes.push(subtype);
                }
            });
            
            uniqueSubtypes.forEach(subtype => {
                const option = document.createElement('option');
                option.value = subtype.subtype;
                option.textContent = subtype.subtype;
                option.dataset.categoryId = subtype.category_id;
                option.dataset.category = subtype.category;
                subtypeSelect.appendChild(option);
            });
            
            subtypeSelect.disabled = false;
        } else {
            subtypeSelect.disabled = true;
        }
    });
    
    subtypeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.dataset.categoryId) {
            categoryIdInput.value = selectedOption.dataset.categoryId;
        }
    });
}

function setupDivisionFilter() {
    // Division filter is already populated via PHP with selected option
    // No need to populate dynamically
}

function setupShedSearch() {
    const shedSelect = document.getElementById('shed_id');
    const divisionFilter = document.getElementById('division_filter');
    
    // Initialize Select2 for shed search
    if (typeof $ !== 'undefined' && $.fn.select2) {
        // Format shed data for Select2
        const shedOptions = shedsData.map(shed => ({
            id: shed.shed_id,
            text: `${shed.shed_code} - ${shed.name} (${shed.division})`,
            division: shed.division
        }));
        
        $(shedSelect).select2({
            theme: 'bootstrap-5',
            placeholder: 'Search sheds by code, name, or division...',
            allowClear: true,
            width: '100%',
            data: shedOptions,
            matcher: function(params, data) {
                // If no search term, return all data
                if (!params.term) {
                    // Apply division filter if selected
                    if (divisionFilter.value && data.division !== divisionFilter.value) {
                        return null;
                    }
                    return data;
                }

                // Apply division filter if selected
                if (divisionFilter.value && data.division !== divisionFilter.value) {
                    return null;
                }

                // Search in code and name
                const term = params.term.toLowerCase();
                if (data.text.toLowerCase().indexOf(term) > -1) {
                    return data;
                }

                return null;
            }
        });

        // Trigger initial filter based on customer's division
        if (customerDivision) {
            $(shedSelect).trigger('change.select2');
        }
    } else {
        // Fallback if Select2 is not available - populate regular select
        shedSelect.innerHTML = '<option value="">Select Shed/Terminal</option>';
        shedsData.forEach(shed => {
            const option = document.createElement('option');
            option.value = shed.shed_id;
            option.textContent = `${shed.shed_code} - ${shed.name} (${shed.division})`;
            option.dataset.division = shed.division;
            shedSelect.appendChild(option);
        });
    }
    
    // Handle division filter changes
    divisionFilter.addEventListener('change', function() {
        const selectedDivision = this.value;
        
        if (typeof $ !== 'undefined' && $.fn.select2) {
            // Clear and refresh the shed dropdown when division changes
            $(shedSelect).val(null).trigger('change');
        } else {
            // Filter regular select options
            const options = shedSelect.querySelectorAll('option');
            options.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                } else if (selectedDivision === '' || option.dataset.division === selectedDivision) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
            shedSelect.value = '';
        }
    });
}

function setupWagonTypeSearch() {
    const wagonTypeSelect = document.getElementById('wagon_type');

    // Initialize Select2 for wagon type search
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $(wagonTypeSelect).select2({
            theme: 'bootstrap-5',
            placeholder: 'Search wagon types...',
            allowClear: true,
            width: '100%'
        });
    }
}

// Global variables for file management
let selectedFiles = [];
let compressedFiles = [];

function setupFileUpload() {
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    
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

        // Quick validation to immediately alert about non-image files
        const nonImageFiles = files.filter(file => !file.type.startsWith('image/'));
        if (nonImageFiles.length > 0) {
            const fileNames = nonImageFiles.map(f => f.name).join(', ');
            window.SAMPARK.ui.showError('Invalid File Type',
                `Only image files are allowed. The following files are not supported: ${fileNames}`);
            return;
        }

        handleFileSelection(files);
    });
    
    // File input change handler
    fileInput.addEventListener('change', function() {
        const files = Array.from(this.files);

        // Quick validation to immediately alert about non-image files
        const nonImageFiles = files.filter(file => !file.type.startsWith('image/'));
        if (nonImageFiles.length > 0) {
            const fileNames = nonImageFiles.map(f => f.name).join(', ');
            window.SAMPARK.ui.showError('Invalid File Type',
                `Only image files are allowed. The following files are not supported: ${fileNames}`);

            // Clear the input
            this.value = '';
            return;
        }

        handleFileSelection(files);
    });
}

function selectFiles() {
    document.getElementById('fileInput').click();
}

function handleFileSelection(files) {
    // Validate file count
    if (selectedFiles.length + files.length > 3) {
        window.SAMPARK.ui.showError('File Limit', 'Maximum 3 files allowed');
        return;
    }
    
    // Validate each file
    const validFiles = [];
    files.forEach(file => {
        const validation = validateFile(file);
        if (validation.valid) {
            validFiles.push(file);
        } else {
            window.SAMPARK.ui.showError('Invalid File', `${file.name}: ${validation.errors.join(', ')}`);
        }
    });
    
    if (validFiles.length === 0) return;
    
    // Add to selected files
    selectedFiles = selectedFiles.concat(validFiles);
    
    // Show compression progress
    showCompressionProgress();
    
    // Compress files
    compressFiles(validFiles);
}

function validateFile(file) {
    const maxSize = 25 * 1024 * 1024; // 25MB
    const allowedTypes = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp',
        'image/heif', 'image/heic', 'image/tiff', 'image/tif'
    ];

    const errors = [];

    // Check if file type is an image
    if (!allowedTypes.includes(file.type)) {
        if (file.type.startsWith('application/') && (file.type.includes('pdf') || file.type.includes('word') || file.type.includes('excel') || file.type.includes('spreadsheet'))) {
            errors.push('Only image files are allowed. Documents (PDF, Word, Excel) are not supported.');
        } else if (file.type === 'text/plain') {
            errors.push('Only image files are allowed. Text files are not supported.');
        } else {
            errors.push('Only image files are allowed. Please select JPG, PNG, GIF, WebP, BMP, HEIF, HEIC, or TIFF files.');
        }
        return {
            valid: false,
            errors: errors
        };
    }

    if (file.size > maxSize) {
        errors.push('File size is too large. Please reduce the image size and try again.');
    }

    return {
        valid: errors.length === 0,
        errors: errors
    };
}

function showCompressionProgress() {
    const progressDiv = document.getElementById('compressionProgress');
    const progressBar = document.getElementById('compressionBar');
    const submitButton = document.getElementById('submitButton');
    
    progressDiv.classList.remove('d-none');
    progressBar.style.width = '0%';
    
    // Disable submit button during compression
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Compressing Files...';
}

function hideCompressionProgress() {
    const progressDiv = document.getElementById('compressionProgress');
    const submitButton = document.getElementById('submitButton');
    
    progressDiv.classList.add('d-none');
    
    // Re-enable submit button after compression
    submitButton.disabled = false;
    submitButton.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Support Ticket';
}

function updateCompressionProgress(percent) {
    const progressBar = document.getElementById('compressionBar');
    progressBar.style.width = percent + '%';
}

async function compressFiles(files) {
    const preview = document.getElementById('uploadPreview');
    let processedCount = 0;
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        
        // Create preview immediately
        createFilePreview(file, preview, 'compressing');
        
        try {
            // Compress file using FileCompressor
            const compressedFile = await compressFileAsync(file);
            compressedFiles.push(compressedFile);
            
            // Update preview status and show compressed size
            updateFilePreviewStatus(file.name, 'compressed');
            updateFilePreviewSize(file.name, compressedFile.size);
            
        } catch (error) {
            console.error('Compression failed for', file.name, error);
            // Use original file if compression fails
            compressedFiles.push(file);
            updateFilePreviewStatus(file.name, 'error');
        }
        
        processedCount++;
        updateCompressionProgress((processedCount / files.length) * 100);
    }
    
    // Hide progress and update summary
    hideCompressionProgress();
    updateFileSummary();
}

function compressFileAsync(file) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'compress');
        formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
        
        fetch(APP_URL + '/api/compress-file', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                try {
                    // Convert base64 data back to binary
                    const binaryString = atob(data.compressedData);
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

function createFilePreview(file, container, status = 'pending') {
    const previewDiv = document.createElement('div');
    previewDiv.className = 'file-preview mb-2';
    previewDiv.dataset.fileName = file.name;
    
    const fileIcon = getFileIcon(file.type);
    const fileSize = formatFileSize(file.size);
    
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
                <button type="button" class="btn btn-link btn-sm text-danger" onclick="removeFile('${file.name}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(previewDiv);
}

function updateFilePreviewStatus(fileName, status) {
    const statusElement = document.getElementById(`status-${fileName}`);
    if (statusElement) {
        statusElement.className = `badge badge-${getStatusBadgeClass(status)}`;
        statusElement.textContent = getStatusText(status);
    }
}

function updateFilePreviewSize(fileName, compressedSize) {
    const compressedSizeElement = document.getElementById(`compressed-size-${fileName}`);
    if (compressedSizeElement) {
        const compressedSizeText = formatFileSize(compressedSize);
        compressedSizeElement.innerHTML = `<span class="text-success">→ ${compressedSizeText}</span>`;
        compressedSizeElement.style.display = 'inline';
    }
}

function getFileIcon(mimeType) {
    if (mimeType.startsWith('image/')) return 'fas fa-image text-primary';
    if (mimeType === 'application/pdf') return 'fas fa-file-pdf text-danger';
    if (mimeType.includes('word')) return 'fas fa-file-word text-primary';
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'fas fa-file-excel text-success';
    if (mimeType === 'text/plain') return 'fas fa-file-alt text-muted';
    return 'fas fa-file text-muted';
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'compressing': return 'warning';
        case 'compressed': return 'success';
        case 'error': return 'danger';
        default: return 'secondary';
    }
}

function getStatusText(status) {
    switch (status) {
        case 'compressing': return 'Compressing...';
        case 'compressed': return 'Ready';
        case 'error': return 'Error';
        default: return 'Pending';
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function removeFile(fileName) {
    // Remove from selected files
    selectedFiles = selectedFiles.filter(file => file.name !== fileName);
    
    // Remove from compressed files
    compressedFiles = compressedFiles.filter(file => file.name !== fileName);
    
    // Remove preview
    const previewElement = document.querySelector(`[data-file-name="${fileName}"]`);
    if (previewElement) {
        previewElement.remove();
    }
    
    updateFileSummary();
}

function clearAllFiles() {
    selectedFiles = [];
    compressedFiles = [];
    
    const preview = document.getElementById('uploadPreview');
    preview.innerHTML = '';
    
    updateFileSummary();
}

function updateFileSummary() {
    const fileSummary = document.getElementById('fileSummary');
    const fileCount = document.getElementById('fileCount');
    const totalSize = document.getElementById('totalSize');
    
    if (selectedFiles.length === 0) {
        fileSummary.style.display = 'none';
        return;
    }
    
    fileSummary.style.display = 'block';
    fileCount.textContent = selectedFiles.length;
    
    // Calculate total original size
    const totalOriginalBytes = selectedFiles.reduce((sum, file) => sum + file.size, 0);
    
    // Calculate total compressed size
    const totalCompressedBytes = compressedFiles.reduce((sum, file) => sum + file.size, 0);
    
    // Show both sizes
    if (totalCompressedBytes > 0 && totalCompressedBytes !== totalOriginalBytes) {
        totalSize.innerHTML = `
            <span>${formatFileSize(totalOriginalBytes)}</span>
            <span class="text-success ms-1">→ ${formatFileSize(totalCompressedBytes)}</span>
        `;
    } else {
        totalSize.textContent = formatFileSize(totalOriginalBytes);
    }
}

function setupValidation() {
    const form = document.getElementById('ticketForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            submitTicket();
        }
    });
    
    // Real-time validation
    form.querySelectorAll('input, select, textarea').forEach(field => {
        field.addEventListener('blur', function() {
            validateField(this);
        });
    });
}

function validateForm() {
    const form = document.getElementById('ticketForm');
    let isValid = true;
    
    // Required field validation
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    // Description length validation
    const description = document.getElementById('description');
    if (description.value.trim().length < 20) {
        showFieldError(description, 'Description must be at least 20 characters');
        isValid = false;
    }
    
    return isValid;
}

function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let message = '';
    
    if (field.hasAttribute('required') && !value) {
        message = 'This field is required';
        isValid = false;
    }
    
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        showFieldError(field, message);
    }
    
    return isValid;
}

function showFieldError(field, message) {
    field.classList.remove('is-valid');
    field.classList.add('is-invalid');
    
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.textContent = message;
    }
}

function submitTicket() {
    const form = document.getElementById('ticketForm');
    const submitButton = document.getElementById('submitButton');
    
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Ticket...';
    submitButton.disabled = true;
    
    const formData = new FormData(form);
    
    // Add compressed files to form data in the correct format for PHP
    compressedFiles.forEach((file, index) => {
        formData.append(`evidence[]`, file);
    });
    
    fetch(APP_URL + '/customer/tickets/create', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        
        if (data.success) {
            window.SAMPARK.ui.showSuccess('Ticket Created', data.message)
                .then(() => {
                    window.location.href = data.redirect;
                });
        } else {
            if (data.errors) {
                // Show field-specific errors
                Object.keys(data.errors).forEach(field => {
                    const fieldElement = form.querySelector(`[name="${field}"]`);
                    if (fieldElement) {
                        showFieldError(fieldElement, data.errors[field][0]);
                    }
                });
            } else {
                window.SAMPARK.ui.showError('Submission Failed', data.message);
            }
        }
    })
    .catch(error => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
        window.SAMPARK.ui.showError('Error', 'Failed to create ticket. Please try again.');
    });
}

function showContactSupport() {
    window.SAMPARK.ui.showInfo('Contact Support', 
        'Email: support@sampark.railway.gov.in<br>' +
        'Phone: 1800-XXX-XXXX<br>' +
        'Hours: Mon-Fri 9:00 AM - 6:00 PM'
    );
}

function showSampleTicket() {
    Swal.fire({
        title: 'Sample Ticket Example',
        html: `
            <div class="text-start">
                <p><strong>Category:</strong> Service → Loading/Unloading → Delay in Loading</p>
                <p><strong>Location:</strong> XYZ Goods Shed, ABC Division</p>
                <p><strong>Wagon:</strong> Container Wagon - CONT12345</p>
                <p><strong>Date/Time:</strong> ${new Date().toLocaleDateString()} at 10:30 AM</p>
                <p><strong>Description:</strong> Container loading operation delayed by 3 hours due to crane breakdown at platform 2. Cargo consists of electronics goods requiring urgent dispatch. Multiple attempts to contact yard supervisor unsuccessful. Alternative arrangements needed urgently.</p>
                <p><strong>Reference:</strong> FNR: FNR123456789, e-Indent: IND789456</p>
            </div>
        `,
        customClass: {
            confirmButton: 'btn btn-apple-primary'
        },
        confirmButtonText: 'Got it!'
    });
}
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.upload-zone {
    border-color: #dee2e6 !important;
    transition: border-color 0.15s ease-in-out;
}

.upload-zone:hover,
.upload-zone.border-primary {
    border-color: var(--apple-blue) !important;
}

.file-preview {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    border: 1px solid #dee2e6;
    border-radius: var(--apple-radius-small);
    margin-bottom: 0.5rem;
}

.file-preview .file-icon {
    margin-right: 0.75rem;
}

.file-preview .file-info {
    flex: 1;
}

.checklist .form-check-input:checked {
    background-color: var(--apple-blue);
    border-color: var(--apple-blue);
}

.select2-container {
    width: 100% !important;
}

.select2-selection {
    border: 1px solid rgba(151, 151, 151, 0.3) !important;
    border-radius: var(--apple-radius-medium) !important;
    min-height: 44px !important;
}

/* Character counter styling */
#charCount.text-danger {
    font-weight: 600;
}

#charCount.text-success {
    color: #28a745 !important;
}

/* File Upload Improvements */
.file-preview {
    border: 1px solid #dee2e6;
    border-radius: var(--apple-radius-small);
    transition: all 0.2s ease;
}

.file-preview:hover {
    border-color: var(--apple-blue);
    box-shadow: 0 2px 8px rgba(0, 136, 204, 0.1);
}

.file-preview .file-icon {
    font-size: 1.5rem;
    width: 40px;
    text-align: center;
}

.file-preview .file-info {
    min-width: 0; /* Allow text to wrap */
}

.file-preview .file-info .fw-semibold {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
}

.file-preview .file-info .d-flex {
    flex-wrap: wrap;
    gap: 0.25rem;
}

.file-preview .text-success {
    font-weight: 500;
}

.file-preview .file-actions .btn {
    padding: 0.25rem 0.5rem;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.compression-progress {
    background-color: rgba(0, 136, 204, 0.05);
    border: 1px solid rgba(0, 136, 204, 0.2);
    border-radius: var(--apple-radius-small);
    padding: 1rem;
}

.file-summary {
    background-color: var(--apple-off-white);
    border: 1px solid #dee2e6;
    border-radius: var(--apple-radius-small);
    padding: 0.75rem 1rem;
}

.upload-zone {
    transition: all 0.3s ease;
}

.upload-zone:hover {
    border-color: var(--apple-blue) !important;
    background-color: rgba(0, 136, 204, 0.02);
}

.upload-zone.border-primary {
    border-color: var(--apple-blue) !important;
    background-color: rgba(0, 136, 204, 0.05);
}

/* Upload zone improvements */
.upload-zone .btn-apple-primary {
    border-radius: var(--apple-radius-medium);
    font-weight: 600;
    padding: 0.75rem 2rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 136, 204, 0.2);
}

.upload-zone .btn-apple-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 136, 204, 0.3);
}

.upload-zone .upload-placeholder h5 {
    color: var(--apple-blue);
    font-weight: 600;
}

/* Badge improvements */
.badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.375rem 0.75rem;
}

.badge-warning {
    background-color: #ffc107;
    color: #000;
}

.badge-success {
    background-color: #28a745;
}

.badge-danger {
    background-color: #dc3545;
}

.badge-secondary {
    background-color: #6c757d;
}

/* Progress bar improvements */
.progress {
    background-color: rgba(0, 136, 204, 0.1);
    border-radius: 2px;
}

.progress-bar {
    background-color: var(--apple-blue);
    border-radius: 2px;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .card-apple {
        margin-bottom: 1rem;
    }
    
    .upload-zone {
        padding: 1.5rem 1rem;
    }
    
    .file-preview {
        flex-direction: column;
        text-align: center;
    }
    
    .file-preview .file-icon {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
    
    .file-preview .file-info .fw-semibold {
        max-width: 150px;
    }
    
    .upload-zone .btn-apple-primary {
        padding: 0.5rem 1.5rem;
        font-size: 0.9rem;
    }
}
</style>

<?php
$content = ob_get_clean();
include '../src/views/layouts/app.php';
?>
