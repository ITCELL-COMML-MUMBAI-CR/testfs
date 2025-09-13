<?php
/**
 * File Controller for SAMPARK
 * Handles secure file serving and evidence management
 */

require_once 'BaseController.php';

class FileController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    /**
     * Serve evidence files securely
     */
    public function serveEvidence($filename) {
        try {
            $user = $this->getCurrentUser();
            
            // Sanitize filename
            $filename = basename($filename);
            
            // Get evidence details from database
            $evidence = $this->db->fetch(
                "SELECT e.*, c.customer_id, c.assigned_to_user_id, c.division 
                 FROM evidence e
                 LEFT JOIN complaints c ON e.complaint_id = c.complaint_id
                 WHERE e.file_name_1 = ? OR e.file_name_2 = ? OR e.file_name_3 = ?",
                [$filename, $filename, $filename]
            );
            
            if (!$evidence) {
                http_response_code(404);
                exit('File not found');
            }
            
            // Check access permissions
            $hasAccess = false;
            
            switch ($user['role']) {
                case 'customer':
                    $hasAccess = ($evidence['customer_id'] === $user['customer_id']);
                    break;
                    
                case 'controller':
                    $hasAccess = ($evidence['assigned_to_user_id'] == $user['id']);
                    break;
                    
                case 'controller_nodal':
                    $hasAccess = ($evidence['division'] === $user['division']);
                    break;
                    
                case 'admin':
                case 'superadmin':
                    $hasAccess = true;
                    break;
            }
            
            if (!$hasAccess) {
                http_response_code(403);
                exit('Access denied');
            }
            
            // Determine which file column contains the requested file and build file path
            $filePath = null;
            $fileType = null;
            $originalName = null;
            
            if ($evidence['file_name_1'] === $filename) {
                $filePath = Config::getUploadPath() . $evidence['file_path_1'];
                $fileType = $evidence['file_type_1'];
                $originalName = $evidence['file_name_1'];
            } elseif ($evidence['file_name_2'] === $filename) {
                $filePath = Config::getUploadPath() . $evidence['file_path_2'];
                $fileType = $evidence['file_type_2'];
                $originalName = $evidence['file_name_2'];
            } elseif ($evidence['file_name_3'] === $filename) {
                $filePath = Config::getUploadPath() . $evidence['file_path_3'];
                $fileType = $evidence['file_type_3'];
                $originalName = $evidence['file_name_3'];
            }
            
            if (!$filePath || !file_exists($filePath)) {
                http_response_code(404);
                exit('File not found on disk');
            }
            
            // Log file access
            $this->logActivity('file_accessed', [
                'file_name' => $filename,
                'complaint_id' => $evidence['complaint_id'],
                'original_name' => $originalName
            ]);
            
            // Set security headers
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            
            // Determine if file should be displayed inline or downloaded
            $disposition = $this->shouldDisplayInline($fileType) ? 'inline' : 'attachment';
            
            // Set appropriate headers
            $mimeType = $this->getMimeType($fileType, $filePath);
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($filePath));
            header('Content-Disposition: ' . $disposition . '; filename="' . $originalName . '"');
            header('Cache-Control: private, max-age=3600');
            
            // Output file
            $this->outputFile($filePath);
            
        } catch (Exception $e) {
            error_log("File serving error: " . $e->getMessage());
            http_response_code(500);
            exit('Server error');
        }
    }
    
    /**
     * Download evidence as ZIP for a ticket
     */
    public function downloadTicketEvidence($ticketId) {
        try {
            $user = $this->getCurrentUser();
            
            // Verify ticket access
            $ticket = $this->getTicketWithAccess($ticketId, $user);
            
            if (!$ticket) {
                http_response_code(403);
                exit('Access denied');
            }
            
            // Get all evidence for the ticket
            $evidence = $this->db->fetchAll(
                "SELECT * FROM evidence WHERE complaint_id = ? ORDER BY uploaded_at ASC",
                [$ticketId]
            );
            
            if (empty($evidence)) {
                http_response_code(404);
                exit('No evidence found');
            }
            
            // Create temporary ZIP file
            $zipFilename = 'evidence_' . $ticketId . '_' . date('Y-m-d') . '.zip';
            $tempZipPath = sys_get_temp_dir() . '/' . $zipFilename;
            
            $zip = new ZipArchive();
            if ($zip->open($tempZipPath, ZipArchive::CREATE) !== TRUE) {
                http_response_code(500);
                exit('Failed to create ZIP file');
            }
            
            $uploadPath = Config::getUploadPath();
            $fileCount = 0;
            
            foreach ($evidence as $file) {
                // Check all three file columns
                for ($i = 1; $i <= 3; $i++) {
                    $fileNameField = "file_name_$i";
                    $filePathField = "file_path_$i";
                    
                    if (!empty($file[$fileNameField])) {
                        $filePath = $uploadPath . $file[$filePathField];
                        
                        if (file_exists($filePath)) {
                            $zip->addFile($filePath, $file[$fileNameField]);
                            $fileCount++;
                        }
                    }
                }
            }
            
            $zip->close();
            
            // Log download
            $this->logActivity('evidence_download', [
                'complaint_id' => $ticketId,
                'file_count' => $fileCount,
                'zip_filename' => $zipFilename
            ]);
            
            // Set download headers
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
            header('Content-Length: ' . filesize($tempZipPath));
            
            // Output ZIP file
            readfile($tempZipPath);
            
            // Clean up
            unlink($tempZipPath);
            exit;
            
        } catch (Exception $e) {
            error_log("Evidence download error: " . $e->getMessage());
            http_response_code(500);
            exit('Server error');
        }
    }
    
    /**
     * Serve user avatar/profile images
     */
    public function serveAvatar($userId) {
        try {
            $user = $this->getCurrentUser();
            
            // Only allow users to access their own avatar or admins to access any
            if ($user['id'] != $userId && !in_array($user['role'], ['admin', 'superadmin'])) {
                http_response_code(403);
                exit('Access denied');
            }
            
            $avatarPath = Config::getUploadPath() . 'avatars/' . $userId . '.jpg';
            
            if (!file_exists($avatarPath)) {
                // Serve default avatar
                $avatarPath = '../public/assets/images/default-avatar.png';
            }
            
            if (!file_exists($avatarPath)) {
                http_response_code(404);
                exit('Avatar not found');
            }
            
            $mimeType = mime_content_type($avatarPath);
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($avatarPath));
            header('Cache-Control: public, max-age=3600');
            
            readfile($avatarPath);
            exit;
            
        } catch (Exception $e) {
            error_log("Avatar serving error: " . $e->getMessage());
            http_response_code(500);
            exit('Server error');
        }
    }
    
    /**
     * Upload user avatar
     */
    public function uploadAvatar() {
        $this->validateCSRF();
        
        try {
            $user = $this->getCurrentUser();
            
            if (empty($_FILES['avatar'])) {
                $this->json(['success' => false, 'message' => 'No file uploaded'], 400);
                return;
            }
            
            $file = $_FILES['avatar'];
            
            // Validate file
            $validation = $this->validateImageUpload($file);
            if (!$validation['success']) {
                $this->json(['success' => false, 'message' => $validation['message']], 400);
                return;
            }
            
            // Create avatars directory if it doesn't exist
            $avatarDir = Config::getUploadPath() . 'avatars/';
            if (!is_dir($avatarDir)) {
                mkdir($avatarDir, 0755, true);
            }
            
            // Process and save image
            $avatarPath = $avatarDir . $user['id'] . '.jpg';
            
            if ($this->processAndSaveAvatar($file['tmp_name'], $avatarPath)) {
                $this->logActivity('avatar_uploaded', ['user_id' => $user['id']]);
                
                $this->json([
                    'success' => true,
                    'message' => 'Avatar uploaded successfully',
                    'avatar_url' => Config::getAppUrl() . '/uploads/avatars/' . $user['id'] . '.jpg?t=' . time()
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to process image'], 500);
            }
            
        } catch (Exception $e) {
            error_log("Avatar upload error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Upload failed'], 500);
        }
    }
    
    /**
     * Delete user avatar
     */
    public function deleteAvatar() {
        $this->validateCSRF();
        
        try {
            $user = $this->getCurrentUser();
            $avatarPath = Config::getUploadPath() . 'avatars/' . $user['id'] . '.jpg';
            
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
                
                $this->logActivity('avatar_deleted', ['user_id' => $user['id']]);
                
                $this->json([
                    'success' => true,
                    'message' => 'Avatar deleted successfully'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'No avatar found to delete'
                ], 404);
            }
            
        } catch (Exception $e) {
            error_log("Avatar deletion error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Deletion failed'], 500);
        }
    }
    
    /**
     * Clean up orphaned files
     */
    public function cleanupOrphanedFiles() {
        $this->requireRole(['admin', 'superadmin']);
        
        try {
            $cleanupResults = [
                'evidence_cleaned' => 0,
                'avatars_cleaned' => 0,
                'total_space_freed' => 0
            ];
            
            // Clean up evidence files
            $evidenceDir = Config::getUploadPath();
            $evidenceFiles = glob($evidenceDir . '*');
            
            foreach ($evidenceFiles as $filePath) {
                $filename = basename($filePath);
                
                // Check if file exists in database
                $exists = $this->db->fetch(
                    "SELECT id FROM evidence WHERE file_name = ?",
                    [$filename]
                );
                
                if (!$exists) {
                    $fileSize = filesize($filePath);
                    unlink($filePath);
                    $cleanupResults['evidence_cleaned']++;
                    $cleanupResults['total_space_freed'] += $fileSize;
                }
            }
            
            // Clean up avatar files
            $avatarDir = Config::getUploadPath() . 'avatars/';
            $avatarFiles = glob($avatarDir . '*.jpg');
            
            foreach ($avatarFiles as $filePath) {
                $filename = basename($filePath, '.jpg');
                
                // Check if user exists
                $exists = $this->db->fetch(
                    "SELECT id FROM users WHERE id = ? UNION SELECT customer_id FROM customers WHERE customer_id = ?",
                    [$filename, $filename]
                );
                
                if (!$exists) {
                    $fileSize = filesize($filePath);
                    unlink($filePath);
                    $cleanupResults['avatars_cleaned']++;
                    $cleanupResults['total_space_freed'] += $fileSize;
                }
            }
            
            $this->logActivity('file_cleanup', $cleanupResults);
            
            $this->json([
                'success' => true,
                'message' => 'File cleanup completed',
                'results' => $cleanupResults
            ]);
            
        } catch (Exception $e) {
            error_log("File cleanup error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Cleanup failed'], 500);
        }
    }
    
    /**
     * Get file storage statistics
     */
    public function getStorageStats() {
        $this->requireRole(['admin', 'superadmin']);
        
        try {
            $stats = [
                'evidence_files' => 0,
                'evidence_size' => 0,
                'avatar_files' => 0,
                'avatar_size' => 0,
                'total_files' => 0,
                'total_size' => 0,
                'upload_limit' => Config::getMaxUploadSize(),
                'available_space' => disk_free_space(Config::getUploadPath())
            ];
            
            // Count evidence files
            $evidenceDir = Config::getUploadPath();
            if (is_dir($evidenceDir)) {
                $evidenceFiles = glob($evidenceDir . '*');
                $stats['evidence_files'] = count($evidenceFiles);
                
                foreach ($evidenceFiles as $file) {
                    $stats['evidence_size'] += filesize($file);
                }
            }
            
            // Count avatar files
            $avatarDir = Config::getUploadPath() . 'avatars/';
            if (is_dir($avatarDir)) {
                $avatarFiles = glob($avatarDir . '*');
                $stats['avatar_files'] = count($avatarFiles);
                
                foreach ($avatarFiles as $file) {
                    $stats['avatar_size'] += filesize($file);
                }
            }
            
            $stats['total_files'] = $stats['evidence_files'] + $stats['avatar_files'];
            $stats['total_size'] = $stats['evidence_size'] + $stats['avatar_size'];
            
            $this->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            error_log("Storage stats error: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Failed to get storage stats'], 500);
        }
    }
    
    // Helper methods
    
    private function getTicketWithAccess($ticketId, $user) {
        $sql = "SELECT * FROM complaints WHERE complaint_id = ?";
        $params = [$ticketId];
        
        // Add access control based on user role
        switch ($user['role']) {
            case 'customer':
                $sql .= " AND customer_id = ?";
                $params[] = $user['customer_id'];
                break;
                
            case 'controller':
                $sql .= " AND assigned_to_user_id = ?";
                $params[] = $user['id'];
                break;
                
            case 'controller_nodal':
                $sql .= " AND division = ?";
                $params[] = $user['division'];
                break;
                
            case 'admin':
            case 'superadmin':
                // No additional restrictions
                break;
                
            default:
                return null;
        }
        
        return $this->db->fetch($sql, $params);
    }
    
    private function shouldDisplayInline($fileType) {
        $inlineTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];
        return in_array($fileType, $inlineTypes);
    }
    
    private function getMimeType($storedType, $filePath) {
        // Use stored type if valid, otherwise detect from file
        $detectedType = mime_content_type($filePath);
        
        // Security: Only allow safe mime types
        $allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif',
            'application/pdf', 'text/plain',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        if (in_array($storedType, $allowedTypes)) {
            return $storedType;
        } elseif (in_array($detectedType, $allowedTypes)) {
            return $detectedType;
        } else {
            return 'application/octet-stream';
        }
    }
    
    private function outputFile($filePath) {
        $fileSize = filesize($filePath);
        $chunkSize = 8192; // 8KB chunks
        
        if ($fileSize > $chunkSize) {
            $handle = fopen($filePath, 'rb');
            
            while (!feof($handle)) {
                echo fread($handle, $chunkSize);
                ob_flush();
                flush();
            }
            
            fclose($handle);
        } else {
            readfile($filePath);
        }
        
        exit;
    }
    
    private function validateImageUpload($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Upload error: ' . $file['error']];
        }
        
        // Check file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'File too large. Maximum size is 5MB.'];
        }
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, and GIF are allowed.'];
        }
        
        // Check image dimensions
        $imageInfo = getimagesize($file['tmp_name']);
        if (!$imageInfo) {
            return ['success' => false, 'message' => 'Invalid image file.'];
        }
        
        // Check minimum dimensions
        if ($imageInfo[0] < 50 || $imageInfo[1] < 50) {
            return ['success' => false, 'message' => 'Image too small. Minimum size is 50x50 pixels.'];
        }
        
        // Check maximum dimensions
        if ($imageInfo[0] > 2000 || $imageInfo[1] > 2000) {
            return ['success' => false, 'message' => 'Image too large. Maximum size is 2000x2000 pixels.'];
        }
        
        return ['success' => true];
    }
    
    private function processAndSaveAvatar($sourcePath, $destPath) {
        try {
            // Get image info
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return false;
            }
            
            // Create image resource based on type
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($sourcePath);
                    break;
                default:
                    return false;
            }
            
            if (!$sourceImage) {
                return false;
            }
            
            // Calculate dimensions for square crop
            $sourceWidth = $imageInfo[0];
            $sourceHeight = $imageInfo[1];
            $size = min($sourceWidth, $sourceHeight);
            
            // Calculate crop position (center crop)
            $cropX = ($sourceWidth - $size) / 2;
            $cropY = ($sourceHeight - $size) / 2;
            
            // Create target image (200x200)
            $targetSize = 200;
            $targetImage = imagecreatetruecolor($targetSize, $targetSize);
            
            // Copy and resize
            imagecopyresampled(
                $targetImage, $sourceImage,
                0, 0, $cropX, $cropY,
                $targetSize, $targetSize, $size, $size
            );
            
            // Save as JPEG
            $result = imagejpeg($targetImage, $destPath, 85);
            
            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($targetImage);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Avatar processing error: " . $e->getMessage());
            return false;
        }
    }
}
