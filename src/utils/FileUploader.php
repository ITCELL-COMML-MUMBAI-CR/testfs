<?php
/**
 * File Upload Utility Class for SAMPARK
 * Handles secure file uploads with compression and validation
 */

class FileUploader {
    
    private $allowedTypes;
    private $maxFileSize;
    private $maxFiles;
    private $uploadPath;
    
    public function __construct() {
        $this->allowedTypes = Config::ALLOWED_FILE_TYPES;
        $this->maxFileSize = Config::MAX_FILE_SIZE;
        $this->maxFiles = Config::MAX_FILES_PER_TICKET;
        $this->uploadPath = Config::getUploadPath();
        
        // Ensure upload directory exists
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
    
    /**
     * Upload evidence files for a complaint
     */
    public function uploadEvidence($complaintId, $files, $uploaderType, $uploaderId) {
        $uploadedFiles = [];
        $errors = [];
        
        try {
            $db = Database::getInstance();
            
            // Check if evidence already exists for this complaint
            $existingEvidence = $db->fetch(
                "SELECT id, file_name_1, file_name_2, file_name_3 FROM evidence WHERE complaint_id = ?",
                [$complaintId]
            );
            
            $fileData = [];
            $fileIndex = 1;
            
            // Handle both array format and single file format
            if (isset($files['name']) && is_array($files['name'])) {
                // Standard PHP file upload format
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $files['name'][$i],
                            'type' => $files['type'][$i],
                            'tmp_name' => $files['tmp_name'][$i],
                            'size' => $files['size'][$i],
                            'error' => $files['error'][$i]
                        ];
                        
                        $result = $this->processFile($file, $complaintId, $fileIndex);
                        
                        if ($result['success']) {
                            $fileData["file_name_$fileIndex"] = $result['filename'];
                            $fileData["file_type_$fileIndex"] = $result['file_type'];
                            $fileData["file_path_$fileIndex"] = $result['file_path'];
                            $fileData["compressed_size_$fileIndex"] = $result['compressed_size'];
                            $uploadedFiles[] = $result;
                            $fileIndex++;
                        } else {
                            $errors[] = "File {$file['name']}: " . $result['error'];
                        }
                    }
                }
            } else {
                // Single file or different format - handle as single file
                if (isset($files['name']) && $files['error'] === UPLOAD_ERR_OK) {
                    $result = $this->processFile($files, $complaintId, 1);
                    
                    if ($result['success']) {
                        $fileData["file_name_1"] = $result['filename'];
                        $fileData["file_type_1"] = $result['file_type'];
                        $fileData["file_path_1"] = $result['file_path'];
                        $fileData["compressed_size_1"] = $result['compressed_size'];
                        $uploadedFiles[] = $result;
                    } else {
                        $errors[] = "File {$files['name']}: " . $result['error'];
                    }
                }
            }
            
            // If we have files to save and no errors
            if (!empty($fileData) && empty($errors)) {
                if ($existingEvidence) {
                    // Update existing evidence record
                    $updateFields = [];
                    $params = [];
                    
                    foreach ($fileData as $field => $value) {
                        $updateFields[] = "$field = ?";
                        $params[] = $value;
                    }
                    
                    $params[] = $complaintId;
                    
                    $sql = "UPDATE evidence SET " . implode(', ', $updateFields) . " WHERE complaint_id = ?";
                    $db->query($sql, $params);
                } else {
                    // Insert new evidence record
                    $fileData['complaint_id'] = $complaintId;
                    $fileData['uploaded_by_type'] = $uploaderType;
                    $fileData['uploaded_by_id'] = $uploaderId;
                    
                    $fields = array_keys($fileData);
                    $placeholders = array_fill(0, count($fields), '?');
                    $values = array_values($fileData);
                    
                    $sql = "INSERT INTO evidence (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $db->query($sql, $values);
                }
            }
            
            return [
                'success' => empty($errors),
                'files' => $uploadedFiles,
                'errors' => $errors
            ];
            
        } catch (Exception $e) {
            error_log("File upload error: " . $e->getMessage());
            return [
                'success' => false,
                'files' => [],
                'errors' => ['Failed to upload files. Please try again.']
            ];
        }
    }

    /**
     * Upload additional evidence files for a complaint (max 2 files)
     */
    public function uploadAdditionalEvidence($complaintId, $files, $uploaderType, $uploaderId) {
        $uploadedFiles = [];
        $errors = [];

        try {
            $db = Database::getInstance();

            // Check if evidence exists for this complaint
            $existingEvidence = $db->fetch(
                "SELECT id, additional_file_name_1, additional_file_name_2 FROM evidence WHERE complaint_id = ?",
                [$complaintId]
            );

            if (!$existingEvidence) {
                return [
                    'success' => false,
                    'files' => [],
                    'errors' => ['No evidence record found for this ticket']
                ];
            }

            // Check if additional files already exist
            $existingAdditionalFiles = 0;
            if (!empty($existingEvidence['additional_file_name_1'])) $existingAdditionalFiles++;
            if (!empty($existingEvidence['additional_file_name_2'])) $existingAdditionalFiles++;

            $fileData = [];
            $fileIndex = $existingAdditionalFiles + 1; // Start from next available slot

            // Handle both array format and single file format
            if (isset($files['name']) && is_array($files['name'])) {
                // Standard PHP file upload format
                for ($i = 0; $i < count($files['name']) && $fileIndex <= 2; $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $files['name'][$i],
                            'type' => $files['type'][$i],
                            'tmp_name' => $files['tmp_name'][$i],
                            'size' => $files['size'][$i],
                            'error' => $files['error'][$i]
                        ];

                        $result = $this->processAdditionalFile($file, $complaintId, $fileIndex);

                        if ($result['success']) {
                            $fileData["additional_file_name_$fileIndex"] = $result['filename'];
                            $fileData["additional_file_type_$fileIndex"] = $result['file_type'];
                            $fileData["additional_file_path_$fileIndex"] = $result['file_path'];
                            $fileData["additional_compressed_size_$fileIndex"] = $result['compressed_size'];
                            $uploadedFiles[] = $result;
                            $fileIndex++;
                        } else {
                            $errors[] = "File {$file['name']}: " . $result['error'];
                        }
                    }
                }
            } else {
                // Single file format
                if (isset($files['name']) && $files['error'] === UPLOAD_ERR_OK && $fileIndex <= 2) {
                    $result = $this->processAdditionalFile($files, $complaintId, $fileIndex);

                    if ($result['success']) {
                        $fileData["additional_file_name_$fileIndex"] = $result['filename'];
                        $fileData["additional_file_type_$fileIndex"] = $result['file_type'];
                        $fileData["additional_file_path_$fileIndex"] = $result['file_path'];
                        $fileData["additional_compressed_size_$fileIndex"] = $result['compressed_size'];
                        $uploadedFiles[] = $result;
                    } else {
                        $errors[] = "File {$files['name']}: " . $result['error'];
                    }
                }
            }

            // If we have files to save and no errors
            if (!empty($fileData) && empty($errors)) {
                // Update existing evidence record with additional files
                $updateFields = [];
                $params = [];

                foreach ($fileData as $field => $value) {
                    $updateFields[] = "$field = ?";
                    $params[] = $value;
                }

                // Also update the additional files uploaded timestamp
                $updateFields[] = "additional_files_uploaded_at = NOW()";
                $params[] = $complaintId;

                $sql = "UPDATE evidence SET " . implode(', ', $updateFields) . " WHERE complaint_id = ?";
                $db->query($sql, $params);
            }

            return [
                'success' => empty($errors),
                'files' => $uploadedFiles,
                'errors' => $errors
            ];

        } catch (Exception $e) {
            error_log("Additional file upload error: " . $e->getMessage());
            return [
                'success' => false,
                'files' => [],
                'errors' => ['Failed to upload additional files. Please try again.']
            ];
        }
    }
    
    /**
     * Process additional file with proper naming
     */
    private function processAdditionalFile($file, $complaintId, $fileNumber) {
        // Validate file
        $validation = $this->validateFile($file);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => implode(', ', $validation['errors'])
            ];
        }

        // Generate unique filename for additional files
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $complaintId . '_additional_file_' . $fileNumber . '.' . $extension;
        $filePath = $this->uploadPath . $filename;

        // Move uploaded file to temp location first
        $tempPath = $this->uploadPath . 'temp_' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
            return [
                'success' => false,
                'error' => 'Failed to save file'
            ];
        }

        // Compress the file if it's an image
        $mimeType = mime_content_type($tempPath);
        if (strpos($mimeType, 'image/') === 0) {
            // Use FileCompressor to compress the image
            require_once __DIR__ . '/FileCompressor.php';
            $compressedPath = compressFile($tempPath, 5120); // 5MB limit

            if ($compressedPath && file_exists($compressedPath)) {
                // Move compressed file to final location
                if (rename($compressedPath, $filePath)) {
                    // Clean up temp file
                    @unlink($tempPath);
                } else {
                    // If rename failed, copy and clean up
                    copy($compressedPath, $filePath);
                    @unlink($tempPath);
                    @unlink($compressedPath);
                }
            } else {
                // If compression failed, use original file
                rename($tempPath, $filePath);
            }
        } else {
            // For non-image files, just move to final location
            rename($tempPath, $filePath);
        }

        // Get final file size
        $finalSize = filesize($filePath);

        return [
            'success' => true,
            'filename' => $filename,
            'file_path' => $filename, // Store relative path
            'file_type' => $extension,
            'original_size' => $file['size'],
            'compressed_size' => $finalSize,
            'original_name' => $file['name']
        ];
    }

    /**
     * Process individual file
     */
    private function processFile($file, $complaintId, $fileNumber) {
        // Validate file
        $validation = $this->validateFile($file);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => implode(', ', $validation['errors'])
            ];
        }
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $complaintId . '_file' . $fileNumber . '.' . $extension;
        $filePath = $this->uploadPath . $filename;
        
        // Move uploaded file to temp location first
        $tempPath = $this->uploadPath . 'temp_' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
            return [
                'success' => false,
                'error' => 'Failed to save file'
            ];
        }
        
        // Compress the file if it's an image
        $mimeType = mime_content_type($tempPath);
        if (strpos($mimeType, 'image/') === 0) {
            // Use FileCompressor to compress the image
            require_once __DIR__ . '/FileCompressor.php';
            $compressedPath = compressFile($tempPath, 5120); // 5MB limit
            
            if ($compressedPath && file_exists($compressedPath)) {
                // Move compressed file to final location
                if (rename($compressedPath, $filePath)) {
                    // Clean up temp file
                    @unlink($tempPath);
                } else {
                    // If rename failed, copy and clean up
                    copy($compressedPath, $filePath);
                    @unlink($tempPath);
                    @unlink($compressedPath);
                }
            } else {
                // If compression failed, use original file
                rename($tempPath, $filePath);
            }
        } else {
            // For non-image files, just move to final location
            rename($tempPath, $filePath);
        }
        
        // Get final file size
        $finalSize = filesize($filePath);
        
        return [
            'success' => true,
            'filename' => $filename,
            'file_path' => $filename, // Store relative path
            'file_type' => $extension,
            'original_size' => $file['size'],
            'compressed_size' => $finalSize
        ];
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file) {
        $errors = [];
        
        // Check file size - images only up to 25MB for compression to 5MB
        $maxAllowedSize = 25 * 1024 * 1024; // 25MB (will be compressed to 5MB by FileCompressor)
        if ($file['size'] > $maxAllowedSize) {
            $errors[] = 'File size is too large. Please reduce the image size and try again.';
        }
        
        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedTypes)) {
            $errors[] = 'Only image files are allowed. Please upload JPG, PNG, GIF, WebP, BMP, HEIF, HEIC, or TIFF files.';
        }
        
        // Check MIME type for security - images only
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'heif' => 'image/heif',
            'heic' => 'image/heic',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff'
        ];
        
        if (isset($allowedMimes[$extension])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if ($mimeType !== $allowedMimes[$extension]) {
                $errors[] = 'This file appears to be corrupted or is not a valid image file. Please try another image.';
            }
        }
        
        // Check for malicious content
        if ($this->containsMaliciousContent($file['tmp_name'])) {
            $errors[] = 'This file is not allowed for security reasons. Please upload a clean image file.';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    
    /**
     * Check for malicious content in uploaded files
     */
    private function containsMaliciousContent($filePath) {
        // Read first few bytes to check for suspicious patterns
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return true; // Assume malicious if can't read
        }
        
        $header = fread($handle, 1024);
        fclose($handle);
        
        // Check for PHP tags
        if (strpos($header, '<?php') !== false || strpos($header, '<?=') !== false) {
            return true;
        }
        
        // Check for script tags
        if (stripos($header, '<script') !== false) {
            return true;
        }
        
        // Check for suspicious executable signatures
        $suspiciousSignatures = [
            "\x4D\x5A", // PE executable
            "\x7F\x45\x4C\x46", // ELF executable
            "\xCA\xFE\xBA\xBE", // Java class file
        ];
        
        foreach ($suspiciousSignatures as $signature) {
            if (strpos($header, $signature) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get file URL for display
     */
    public function getFileUrl($filename) {
        return Config::getPublicUploadPath() . $filename;
    }
    
    /**
     * Delete file from storage
     */
    public function deleteFile($filename) {
        $filePath = $this->uploadPath . $filename;
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return true; // File doesn't exist, consider it deleted
    }
    
    /**
     * Get file information
     */
    public function getFileInfo($filename) {
        $filePath = $this->uploadPath . $filename;
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        return [
            'name' => $filename,
            'size' => filesize($filePath),
            'type' => mime_content_type($filePath),
            'modified' => filemtime($filePath),
            'url' => $this->getFileUrl($filename)
        ];
    }
    
    /**
     * Clean up old files (for maintenance)
     */
    public function cleanupOldFiles($days = 365) {
        $cutoffTime = time() - ($days * 24 * 60 * 60);
        $deletedFiles = 0;
        
        try {
            $db = Database::getInstance();
            
            // Get old file records
            $sql = "SELECT file_name FROM evidence 
                    WHERE uploaded_at < ? AND complaint_id IN (
                        SELECT complaint_id FROM complaints WHERE status = 'closed'
                    )";
            
            $oldFiles = $db->fetchAll($sql, [date('Y-m-d H:i:s', $cutoffTime)]);
            
            foreach ($oldFiles as $file) {
                if ($this->deleteFile($file['file_name'])) {
                    $deletedFiles++;
                }
            }
            
            // Clean up database records
            if ($deletedFiles > 0) {
                $db->query(
                    "DELETE FROM evidence WHERE uploaded_at < ? AND complaint_id IN (
                        SELECT complaint_id FROM complaints WHERE status = 'closed'
                    )",
                    [date('Y-m-d H:i:s', $cutoffTime)]
                );
            }
            
            return $deletedFiles;
            
        } catch (Exception $e) {
            error_log("File cleanup error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Upload single evidence file for a ticket
     */
    public function uploadSingleEvidence($file, $complaintId) {
        // Validate file
        $validation = $this->validateFile($file);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => implode(', ', $validation['errors'])
            ];
        }
        
        try {
            // Process the file (includes compression)
            $result = $this->processFile($file, $complaintId, 1);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'original_name' => $file['name'],
                    'file_name' => $result['filename'],
                    'file_size' => $result['compressed_size'],
                    'file_type' => $result['file_type']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $result['error']
                ];
            }
            
        } catch (Exception $e) {
            error_log("File upload error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to process file upload'
            ];
        }
    }
    
}
