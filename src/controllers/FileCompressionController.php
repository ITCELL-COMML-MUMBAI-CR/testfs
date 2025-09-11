<?php
/**
 * File Compression Controller for SAMPARK
 * Handles file compression using FileCompressor utility
 */

require_once 'BaseController.php';
require_once __DIR__ . '/../utils/FileCompressor.php';

class FileCompressionController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }
    
    public function compressFile() {
        $this->validateCSRF();
        
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->json([
                'success' => false,
                'message' => 'No file uploaded or upload error'
            ], 400);
            return;
        }
        
        $file = $_FILES['file'];
        $maxSizeKB = 2048; // 2MB limit (2048 KB)
        
        // Validate file type
        $allowedTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp',
            'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        if (!in_array($file['type'], $allowedTypes)) {
            $this->json([
                'success' => false,
                'message' => 'File type not supported'
            ], 400);
            return;
        }
        
        // Validate file size (max 20MB before compression)
        if ($file['size'] > 20 * 1024 * 1024) {
            $this->json([
                'success' => false,
                'message' => 'File too large (max 20MB before compression)'
            ], 400);
            return;
        }
        
        try {
            // Compress the file
            $compressedPath = compressFile($file['tmp_name'], $maxSizeKB);
            
            if (!$compressedPath) {
                $this->json([
                    'success' => false,
                    'message' => 'File compression failed'
                ], 500);
                return;
            }
            
            // Read the compressed file
            $compressedData = file_get_contents($compressedPath);
            
            if ($compressedData === false) {
                $this->json([
                    'success' => false,
                    'message' => 'Could not read compressed file'
                ], 500);
                return;
            }
            
            // Clean up temporary file
            unlink($compressedPath);
            
            // Return compressed file data
            $this->json([
                'success' => true,
                'message' => 'File compressed successfully',
                'compressed_data' => base64_encode($compressedData),
                'originalSize' => $file['size'],
                'compressedSize' => strlen($compressedData),
                'compressionRatio' => round((1 - strlen($compressedData) / $file['size']) * 100, 2)
            ]);
            
        } catch (Exception $e) {
            error_log("File compression error: " . $e->getMessage());
            
            $this->json([
                'success' => false,
                'message' => 'File compression failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
