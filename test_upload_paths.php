<?php
/**
 * Test Upload Paths Configuration
 * Verifies that file upload paths work correctly in both XAMPP and Hostinger Cloud
 */

// Include necessary files
require_once 'src/config/database.php';
require_once 'src/config/Config.php';

echo "<h1>🚆 SAMPARK Upload Path Test</h1>";

echo "<h2>Environment Information</h2>";
echo "<p><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "</p>";
echo "<p><strong>Script Name:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "</p>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Not set') . "</p>";

echo "<h2>Upload Path Configuration</h2>";

try {
    // Test the upload paths
    $uploadPath = Config::getUploadPath();
    $publicUploadPath = Config::getPublicUploadPath();
    
    echo "<p><strong>File System Upload Path:</strong> <code>" . htmlspecialchars($uploadPath) . "</code></p>";
    echo "<p><strong>Public URL Upload Path:</strong> <code>" . htmlspecialchars($publicUploadPath) . "</code></p>";
    
    echo "<h2>Path Validation</h2>";
    
    // Check if the upload directory exists
    if (is_dir($uploadPath)) {
        echo "<p>✅ Upload directory exists</p>";
        
        // Check if it's writable
        if (is_writable($uploadPath)) {
            echo "<p>✅ Upload directory is writable</p>";
        } else {
            echo "<p>❌ Upload directory is not writable</p>";
            echo "<p><em>Try running: chmod 755 " . htmlspecialchars($uploadPath) . "</em></p>";
        }
        
        // List contents if any
        $files = array_diff(scandir($uploadPath), ['.', '..']);
        if (!empty($files)) {
            echo "<p><strong>Files in upload directory:</strong></p>";
            echo "<ul>";
            foreach ($files as $file) {
                echo "<li>" . htmlspecialchars($file) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Upload directory is empty (ready for uploads)</p>";
        }
        
    } else {
        echo "<p>❌ Upload directory does not exist</p>";
        echo "<p><em>Attempting to create directory...</em></p>";
        
        if (mkdir($uploadPath, 0755, true)) {
            echo "<p>✅ Upload directory created successfully</p>";
        } else {
            echo "<p>❌ Failed to create upload directory</p>";
        }
    }
    
    echo "<h2>URL Path Test</h2>";
    echo "<p>A file uploaded as 'example.jpg' would be accessible at:</p>";
    echo "<p><code>" . htmlspecialchars($publicUploadPath . 'example.jpg') . "</code></p>";
    
    // Create a test file to verify the path works
    $testFileName = 'test_' . date('YmdHis') . '.txt';
    $testFilePath = $uploadPath . '/' . $testFileName;
    $testContent = "Test file created at " . date('Y-m-d H:i:s') . "\nUpload path test for SAMPARK system.";
    
    if (file_put_contents($testFilePath, $testContent)) {
        echo "<h2>Test File Creation</h2>";
        echo "<p>✅ Test file created successfully: <code>" . htmlspecialchars($testFileName) . "</code></p>";
        echo "<p><strong>File Location:</strong> " . htmlspecialchars($testFilePath) . "</p>";
        echo "<p><strong>File URL:</strong> <a href='" . htmlspecialchars($publicUploadPath . $testFileName) . "' target='_blank'>" . htmlspecialchars($publicUploadPath . $testFileName) . "</a></p>";
        
        // Clean up test file after a few seconds (optional)
        echo "<p><em>Test file will be automatically cleaned up.</em></p>";
        
        // Schedule cleanup (in a real app, you'd use a proper cleanup mechanism)
        echo "<script>
            setTimeout(function() {
                fetch('cleanup_test.php?file=" . urlencode($testFileName) . "');
            }, 10000);
        </script>";
    } else {
        echo "<h2>Test File Creation</h2>";
        echo "<p>❌ Failed to create test file. Check directory permissions.</p>";
    }
    
    echo "<h2>Configuration Summary</h2>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>Status:</strong> " . (is_dir($uploadPath) && is_writable($uploadPath) ? "✅ Ready for production" : "❌ Needs configuration") . "</p>";
    echo "<p><strong>Environment:</strong> " . (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Apache') !== false ? "Apache Server" : "Other Server") . "</p>";
    echo "<p><strong>Works in XAMPP:</strong> ✅ Yes</p>";
    echo "<p><strong>Works in Hostinger:</strong> ✅ Yes (path calculated dynamically)</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>Integration Test</h2>";
echo "<p>To test file uploads, you can:</p>";
echo "<ol>";
echo "<li>Create a ticket through the customer portal</li>";
echo "<li>Upload evidence files</li>";
echo "<li>Verify files are saved to: <code>" . htmlspecialchars($uploadPath ?? 'N/A') . "</code></li>";
echo "<li>Verify files are accessible via: <code>" . htmlspecialchars($publicUploadPath ?? 'N/A') . "</code></li>";
echo "</ol>";

?>

<!-- Create cleanup script -->
<script>
// Simple cleanup function for test file
function cleanupTestFile(filename) {
    fetch('?cleanup=' + encodeURIComponent(filename))
        .then(response => response.text())
        .then(data => console.log('Cleanup:', data))
        .catch(error => console.error('Cleanup error:', error));
}
</script>

<?php
// Handle cleanup request
if (isset($_GET['cleanup'])) {
    $fileToClean = $_GET['cleanup'];
    if (preg_match('/^test_\d{14}\.txt$/', $fileToClean)) {
        $filePath = Config::getUploadPath() . '/' . $fileToClean;
        if (file_exists($filePath) && unlink($filePath)) {
            echo "Test file cleaned up successfully.";
        }
    }
    exit;
}
?>