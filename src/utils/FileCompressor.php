<?php

/**
 * Advanced File Compression Utility
 * Optimized for speed and supports multiple file types
 * 
 * Supported file types:
 * - Images: JPEG, PNG, GIF, WebP, BMP, HEIF, HEIC, TIFF
 */

class FileCompressor {
    private const MAX_MEMORY_USAGE = '512M';
    private const COMPRESSION_QUALITY_STEPS = [85, 70, 55, 40, 25, 15, 10];
    private const SCALE_STEPS = [0.9, 0.8, 0.7, 0.6, 0.5, 0.4, 0.3, 0.2];
    
    private static $supportedTypes = [
        // Images only
        'image/jpeg' => 'compressImage',
        'image/jpg' => 'compressImage',
        'image/png' => 'compressImage',
        'image/gif' => 'compressImage',
        'image/webp' => 'compressImage',
        'image/bmp' => 'compressImage',
        'image/heif' => 'compressImage',
        'image/heic' => 'compressImage',
        'image/tiff' => 'compressImage',
        'image/tif' => 'compressImage'
    ];
    
    public static function compressFile($inputFile, $maxSizeKB = 5120, $outputFile = null) {
        // Validate input
        if (!self::validateInput($inputFile)) {
            return false;
        }
        
        // Set memory limit for large files
        $oldMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', self::MAX_MEMORY_USAGE);
        
        try {
            $result = self::performCompression($inputFile, $maxSizeKB, $outputFile);
        } finally {
            // Always restore memory limit
            ini_set('memory_limit', $oldMemoryLimit);
        }
        
        return $result;
    }
    
    private static function validateInput($inputFile) {
        if (!file_exists($inputFile)) {
            trigger_error("Input file does not exist: $inputFile", E_USER_WARNING);
            return false;
        }
        
        if (!is_readable($inputFile)) {
            trigger_error("Input file is not readable: $inputFile", E_USER_WARNING);
            return false;
        }
        
        return true;
    }
    
    private static function performCompression($inputFile, $maxSizeKB, $outputFile) {
        $maxSizeBytes = $maxSizeKB * 1024;
        $originalSize = filesize($inputFile);
        
        // If already under limit, just copy or return original
        if ($originalSize <= $maxSizeBytes) {
            if ($outputFile === null) {
                return $inputFile;
            }
            copy($inputFile, $outputFile);
            return $outputFile;
        }
        
        // Determine file type and compression method
        $mimeType = mime_content_type($inputFile);
        $compressionMethod = self::$supportedTypes[$mimeType] ?? null;
        
        if (!$compressionMethod) {
            trigger_error("Unsupported file type: $mimeType", E_USER_WARNING);
            return false;
        }
        
        // Set output file if not provided
        if ($outputFile === null) {
            $extension = pathinfo($inputFile, PATHINFO_EXTENSION);
            $outputFile = sys_get_temp_dir() . '/' . uniqid('compressed_') . '.' . $extension;
        }
        
        // Perform compression based on file type
        return self::$compressionMethod($inputFile, $outputFile, $maxSizeBytes, $mimeType);
    }
    
    private static function compressImage($inputFile, $outputFile, $maxSizeBytes, $mimeType) {
        if (!extension_loaded('gd')) {
            trigger_error("GD extension is required for image compression", E_USER_WARNING);
            return false;
        }
        
        // Load image efficiently
        $image = self::createImageResource($inputFile, $mimeType);
        if (!$image) {
            trigger_error("Failed to load image: $inputFile", E_USER_WARNING);
            return false;
        }
        
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Fast compression with binary search approach
        $result = self::binarySearchCompress($image, $inputFile, $outputFile, $maxSizeBytes, $mimeType, $width, $height);
        
        imagedestroy($image);
        return $result;
    }
    
    private static function createImageResource($inputFile, $mimeType) {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return @imagecreatefromjpeg($inputFile);
            case 'image/png':
                return @imagecreatefrompng($inputFile);
            case 'image/gif':
                return @imagecreatefromgif($inputFile);
            case 'image/webp':
                return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($inputFile) : false;
            case 'image/bmp':
                return function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($inputFile) : false;
            case 'image/heif':
            case 'image/heic':
                // HEIF/HEIC support requires PHP 7.1+ and proper imagick extension
                if (extension_loaded('imagick')) {
                    try {
                        $imagick = new \Imagick($inputFile);
                        $imagick->setImageFormat('jpeg');
                        $tempJpeg = tempnam(sys_get_temp_dir(), 'heif_convert_') . '.jpg';
                        $imagick->writeImage($tempJpeg);
                        $imagick->destroy();
                        return @imagecreatefromjpeg($tempJpeg);
                    } catch (Exception $e) {
                        return false;
                    }
                }
                return false;
            case 'image/tiff':
            case 'image/tif':
                // TIFF support requires imagick
                if (extension_loaded('imagick')) {
                    try {
                        $imagick = new \Imagick($inputFile);
                        $imagick->setImageFormat('jpeg');
                        $tempJpeg = tempnam(sys_get_temp_dir(), 'tiff_convert_') . '.jpg';
                        $imagick->writeImage($tempJpeg);
                        $imagick->destroy();
                        return @imagecreatefromjpeg($tempJpeg);
                    } catch (Exception $e) {
                        return false;
                    }
                }
                return false;
            default:
                return false;
        }
    }
    
    private static function binarySearchCompress($originalImage, $inputFile, $outputFile, $maxSizeBytes, $mimeType, $originalWidth, $originalHeight) {
        $bestResult = null;
        $tempFiles = [];
        $bestSize = PHP_INT_MAX;
        $bestFile = null;
        
        // Try different scale and quality combinations efficiently
        foreach (self::SCALE_STEPS as $scale) {
            $newWidth = (int)($originalWidth * $scale);
            $newHeight = (int)($originalHeight * $scale);
            
            // Skip if image becomes too small
            if ($newWidth < 50 || $newHeight < 50) {
                continue;
            }
            
            // Create resized image once for this scale
            $resizedImage = self::createResizedImage($originalImage, $newWidth, $newHeight, $mimeType);
            if (!$resizedImage) continue;
            
            // Try different quality levels for this scale
            foreach (self::COMPRESSION_QUALITY_STEPS as $quality) {
                $tempFile = tempnam(sys_get_temp_dir(), 'img_compress_');
                $tempFiles[] = $tempFile;
                
                if (self::saveImageWithQuality($resizedImage, $tempFile, $quality, $mimeType)) {
                    $size = filesize($tempFile);
                    
                    if ($size <= $maxSizeBytes) {
                        // Found acceptable compression
                        if (copy($tempFile, $outputFile)) {
                            imagedestroy($resizedImage);
                            self::cleanupTempFiles($tempFiles);
                            return $outputFile;
                        }
                    } elseif ($size < $bestSize) {
                        // Keep track of best result even if over limit
                        $bestSize = $size;
                        if ($bestFile) @unlink($bestFile);
                        $bestFile = tempnam(sys_get_temp_dir(), 'best_compress_');
                        copy($tempFile, $bestFile);
                    }
                }
            }
            
            imagedestroy($resizedImage);
        }
        
        self::cleanupTempFiles($tempFiles);
        
        // If we have a best result but it's still over limit, use it as last resort
        if ($bestFile && file_exists($bestFile)) {
            if (copy($bestFile, $outputFile)) {
                @unlink($bestFile);
                error_log("Image compressed to {$bestSize} bytes (target was {$maxSizeBytes} bytes)");
                return $outputFile;
            }
            @unlink($bestFile);
        }
        
        trigger_error("Could not compress image to under {$maxSizeBytes} bytes", E_USER_WARNING);
        return false;
    }
    
    private static function createResizedImage($originalImage, $newWidth, $newHeight, $mimeType) {
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagecolortransparent($resizedImage, imagecolorallocate($resizedImage, 0, 0, 0));
            imagealphablending($resizedImage, false);
            imagesavealpha($resizedImage, true);
        }
        
        // High quality resampling
        if (imagecopyresampled($resizedImage, $originalImage, 0, 0, 0, 0, $newWidth, $newHeight, imagesx($originalImage), imagesy($originalImage))) {
            return $resizedImage;
        }
        
        imagedestroy($resizedImage);
        return false;
    }
    
    private static function saveImageWithQuality($image, $outputFile, $quality, $mimeType) {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagejpeg($image, $outputFile, $quality);
            case 'image/png':
                // PNG compression level (0-9, inverted from quality)
                $compressionLevel = max(0, min(9, (int)((100 - $quality) / 10)));
                return imagepng($image, $outputFile, $compressionLevel);
            case 'image/webp':
                return function_exists('imagewebp') ? imagewebp($image, $outputFile, $quality) : false;
            case 'image/gif':
                return imagegif($image, $outputFile);
            default:
                return imagejpeg($image, $outputFile, $quality); // Fallback to JPEG
        }
    }
    
    
    
    
    
    
    private static function cleanupTempFiles($tempFiles) {
        foreach ($tempFiles as $file) {
            @unlink($file);
        }
    }
    
    /**
     * Get list of supported MIME types
     */
    public static function getSupportedTypes() {
        return array_keys(self::$supportedTypes);
    }
    
    /**
     * Check if a file type is supported
     */
    public static function isTypeSupported($mimeType) {
        return isset(self::$supportedTypes[$mimeType]);
    }
    
    /**
     * Format file size in human-readable format
     */
    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Get compression ratio as percentage
     */
    public static function getCompressionRatio($originalSize, $compressedSize) {
        if ($originalSize == 0) return 0;
        return round((($originalSize - $compressedSize) / $originalSize) * 100, 2);
    }
}

// Wrapper functions for backward compatibility
function compressFile($inputFile, $maxSizeKB = 5120, $outputFile = null) {
    return FileCompressor::compressFile($inputFile, $maxSizeKB, $outputFile);
}

function formatFileSize($bytes) {
    return FileCompressor::formatFileSize($bytes);
}

// Example usage:
/*
// Compress various file types to default 5MB
$compressed = FileCompressor::compressFile('/path/to/image.jpg');
$compressed = FileCompressor::compressFile('/path/to/document.pdf', 3072); // 3MB
$compressed = FileCompressor::compressFile('/path/to/text.txt', 1024); // 1MB

// Check supported types
$supportedTypes = FileCompressor::getSupportedTypes();

if ($compressed) {
    $originalSize = filesize('/path/to/original');
    $compressedSize = filesize($compressed);
    $ratio = FileCompressor::getCompressionRatio($originalSize, $compressedSize);
    
    echo "File compressed successfully!\n";
    echo "Original: " . FileCompressor::formatFileSize($originalSize) . "\n";
    echo "Compressed: " . FileCompressor::formatFileSize($compressedSize) . "\n";
    echo "Compression: {$ratio}%\n";
}
*/

?>