<?php

/**
 * Compress a file to be under a specified size limit
 * 
 * @param string $inputFile Path to the input file
 * @param int $maxSizeKB Maximum size in kilobytes (default: 2048 KB = 2 MB)
 * @param string $outputFile Optional output file path. If not provided, will use temp file
 * @return string|false Path to compressed file on success, false on failure
 */
function compressFile($inputFile, $maxSizeKB = 2048, $outputFile = null) {
    // Validate input file
    if (!file_exists($inputFile)) {
        trigger_error("Input file does not exist: $inputFile", E_USER_WARNING);
        return false;
    }
    
    if (!is_readable($inputFile)) {
        trigger_error("Input file is not readable: $inputFile", E_USER_WARNING);
        return false;
    }
    
    // Convert KB to bytes
    $maxSizeBytes = $maxSizeKB * 1024;
    
    // Get original file size
    $originalSize = filesize($inputFile);
    
    // If file is already under the limit, just copy it
    if ($originalSize <= $maxSizeBytes) {
        if ($outputFile === null) {
            return $inputFile;
        }
        copy($inputFile, $outputFile);
        return $outputFile;
    }
    
    // Determine file type
    $mimeType = mime_content_type($inputFile);
    $fileInfo = pathinfo($inputFile);
    $extension = strtolower($fileInfo['extension'] ?? '');
    
    // Set output file if not provided
    if ($outputFile === null) {
        $outputFile = sys_get_temp_dir() . '/' . uniqid('compressed_') . '.' . $extension;
    }
    
    // Handle different file types
    if (strpos($mimeType, 'image/') === 0) {
        return compressImage($inputFile, $outputFile, $maxSizeBytes, $mimeType);
    } else {
        return compressWithZip($inputFile, $outputFile, $maxSizeBytes);
    }
}

/**
 * Compress an image file
 * 
 * @param string $inputFile Input image file path
 * @param string $outputFile Output file path
 * @param int $maxSizeBytes Maximum size in bytes
 * @param string $mimeType MIME type of the image
 * @return string|false Compressed file path or false on failure
 */
function compressImage($inputFile, $outputFile, $maxSizeBytes, $mimeType) {
    // Check if GD library is available
    if (!extension_loaded('gd')) {
        // Fallback to ZIP compression if GD is not available
        return compressWithZip($inputFile, $outputFile, $maxSizeBytes);
    }
    
    // Load image based on type
    $image = null;
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $image = @imagecreatefromjpeg($inputFile);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($inputFile);
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($inputFile);
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $image = @imagecreatefromwebp($inputFile);
            }
            break;
        case 'image/bmp':
            if (function_exists('imagecreatefrombmp')) {
                $image = @imagecreatefrombmp($inputFile);
            }
            break;
    }
    
    if (!$image) {
        // If image loading failed, try ZIP compression
        return compressWithZip($inputFile, $outputFile, $maxSizeBytes);
    }
    
    // Get original dimensions
    $width = imagesx($image);
    $height = imagesy($image);
    
    // Try different quality/size combinations
    $quality = 90;
    $scale = 1.0;
    $attempts = 0;
    $maxAttempts = 10;
    
    while ($attempts < $maxAttempts) {
        // Calculate new dimensions
        $newWidth = (int)($width * $scale);
        $newHeight = (int)($height * $scale);
        
        // Create resized image
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagecolortransparent($resized, imagecolorallocate($resized, 0, 0, 0));
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        
        // Resample the image
        imagecopyresampled($resized, $image, 0, 0, 0, 0, 
                          $newWidth, $newHeight, $width, $height);
        
        // Save with appropriate format
        $tempFile = sys_get_temp_dir() . '/' . uniqid('img_temp_') . '.' . pathinfo($outputFile, PATHINFO_EXTENSION);
        
        if ($mimeType === 'image/png') {
            // For PNG, use imagepng with compression level
            $compressionLevel = 9 - (int)(($quality - 10) / 10); // Convert quality to PNG compression (0-9)
            $compressionLevel = max(0, min(9, $compressionLevel));
            imagepng($resized, $tempFile, $compressionLevel);
        } elseif ($mimeType === 'image/gif') {
            imagegif($resized, $tempFile);
        } elseif ($mimeType === 'image/webp' && function_exists('imagewebp')) {
            imagewebp($resized, $tempFile, $quality);
        } else {
            // Default to JPEG for other formats
            imagejpeg($resized, $tempFile, $quality);
        }
        imagedestroy($resized);
        
        // Check file size
        $currentSize = filesize($tempFile);
        
        if ($currentSize <= $maxSizeBytes) {
            // Success! Move to output file
            rename($tempFile, $outputFile);
            imagedestroy($image);
            return $outputFile;
        }
        
        // Clean up temp file
        @unlink($tempFile);
        
        // Adjust parameters for next attempt
        if ($quality > 30) {
            $quality -= 10;
        } else {
            $scale *= 0.9;
            $quality = 90; // Reset quality when scaling down
        }
        
        $attempts++;
    }
    
    imagedestroy($image);
    
    // If we couldn't compress enough with image manipulation, try ZIP
    return compressWithZip($inputFile, $outputFile, $maxSizeBytes);
}

/**
 * Compress a file using ZIP compression
 * 
 * @param string $inputFile Input file path
 * @param string $outputFile Output file path
 * @param int $maxSizeBytes Maximum size in bytes
 * @return string|false Compressed file path or false on failure
 */
function compressWithZip($inputFile, $outputFile, $maxSizeBytes) {
    if (!class_exists('ZipArchive')) {
        trigger_error("ZipArchive class is not available", E_USER_WARNING);
        return false;
    }
    
    // Change output extension to .zip
    $pathInfo = pathinfo($outputFile);
    $zipFile = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.zip';
    
    $zip = new ZipArchive();
    
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        trigger_error("Cannot create ZIP file: $zipFile", E_USER_WARNING);
        return false;
    }
    
    // Try different compression levels
    $compressionLevels = [
        ZipArchive::CM_DEFLATE => [9, 6, 3, 1], // Best to worst compression
        ZipArchive::CM_BZIP2 => [9, 6, 3, 1],   // BZIP2 if available
        ZipArchive::CM_STORE => [0]             // No compression as last resort
    ];
    
    $originalName = basename($inputFile);
    
    foreach ($compressionLevels as $method => $levels) {
        foreach ($levels as $level) {
            // Clear the archive
            $zip->close();
            $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            
            // Add file with current compression settings
            $zip->addFile($inputFile, $originalName);
            
            if (method_exists($zip, 'setCompressionName')) {
                $zip->setCompressionName($originalName, $method);
                if ($method !== ZipArchive::CM_STORE) {
                    $zip->setCompressionIndex(0, $method, $level);
                }
            }
            
            $zip->close();
            
            // Check resulting size
            if (filesize($zipFile) <= $maxSizeBytes) {
                return $zipFile;
            }
        }
    }
    
    // If even ZIP couldn't compress enough, try splitting the file
    // This would require a more complex implementation
    // For now, return the ZIP even if it's over the limit
    trigger_error("Could not compress file to under {$maxSizeBytes} bytes. Minimum achieved: " . filesize($zipFile), E_USER_NOTICE);
    return $zipFile;
}

/**
 * Helper function to format file size in human-readable format
 * 
 * @param int $bytes Size in bytes
 * @return string Formatted size string
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

// Example usage:
/*
// Compress to default 2MB
$compressed = compressFile('/path/to/large_file.jpg');

// Compress to 500KB
$compressed = compressFile('/path/to/document.pdf', 500);

// Compress with specific output path
$compressed = compressFile('/path/to/video.mp4', 1024, '/path/to/output.zip');

if ($compressed) {
    echo "File compressed successfully to: " . $compressed . "\n";
    echo "New size: " . formatFileSize(filesize($compressed)) . "\n";
} else {
    echo "Compression failed\n";
}
*/

?>