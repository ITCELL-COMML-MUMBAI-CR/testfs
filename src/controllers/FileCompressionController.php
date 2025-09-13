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
        $maxSizeKB = 5120; // 5MB limit (5120 KB)
        
        // Validate file type using FileCompressor's supported types
        if (!FileCompressor::isTypeSupported($file['type'])) {
            $this->json([
                'success' => false,
                'message' => 'File type not supported: ' . $file['type']
            ], 400);
            return;
        }
        
        // Validate file size (max 50MB before compression)
        if ($file['size'] > 50 * 1024 * 1024) {
            $this->json([
                'success' => false,
                'message' => 'File too large (max 50MB before compression)'
            ], 400);
            return;
        }
        
        try {
            // Compress the file using the new FileCompressor class
            $compressedPath = FileCompressor::compressFile($file['tmp_name'], $maxSizeKB);
            
            if (!$compressedPath) {
                // For very large images that can't be compressed to 5MB, try with higher fallback sizes
                $fallbackSizes = [6144, 7168, 8192, 10240]; // 6MB, 7MB, 8MB, 10MB fallbacks
                foreach ($fallbackSizes as $fallbackSize) {
                    $compressedPath = FileCompressor::compressFile($file['tmp_name'], $fallbackSize);
                    if ($compressedPath) {
                        error_log("File compressed to {$fallbackSize}KB instead of {$maxSizeKB}KB");
                        break;
                    }
                }
                
                if (!$compressedPath) {
                    $this->json([
                        'success' => false,
                        'message' => 'File cannot be compressed to acceptable size. Please try a smaller image or different format.'
                    ], 422);
                    return;
                }
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
            
            // Calculate compression statistics
            $originalSize = $file['size'];
            $compressedSize = strlen($compressedData);
            $compressionRatio = FileCompressor::getCompressionRatio($originalSize, $compressedSize);
            
            // Clean up temporary file if it's not the original
            if ($compressedPath !== $file['tmp_name']) {
                @unlink($compressedPath);
            }
            
            // Return compressed file data
            $this->json([
                'success' => true,
                'message' => 'File compressed successfully',
                'compressedData' => base64_encode($compressedData), // Keep consistent with frontend
                'compressed_data' => base64_encode($compressedData), // Backward compatibility
                'originalSize' => $originalSize,
                'compressedSize' => $compressedSize,
                'compressionRatio' => $compressionRatio,
                'originalSizeFormatted' => FileCompressor::formatFileSize($originalSize),
                'compressedSizeFormatted' => FileCompressor::formatFileSize($compressedSize)
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
