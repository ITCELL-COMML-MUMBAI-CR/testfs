<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');

// Include Config for error handling
require_once '../src/config/Config.php';

// Set custom error handlers
set_error_handler('Config::errorHandler');
set_exception_handler('Config::exceptionHandler');
register_shutdown_function('Config::shutdownHandler');

echo "<h2>Debug Information</h2>";
echo "<strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "<br>";
echo "<strong>SCRIPT_NAME:</strong> " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "<strong>REQUEST_METHOD:</strong> " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "<strong>DOCUMENT_ROOT:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Test if files exist
$files_to_check = [
    '../src/config/database.php',
    '../src/config/Config.php',
    '../src/utils/Session.php',
    '../src/utils/Router.php',
    '../src/controllers/BaseController.php',
    '../src/config/routes.php'
];

echo "<h3>File Existence Check:</h3>";
foreach ($files_to_check as $file) {
    echo $file . ": " . (file_exists($file) ? "✅ EXISTS" : "❌ MISSING") . "<br>";
}

// Test database connection
echo "<h3>Database Connection Test:</h3>";
try {
    require_once '../src/config/database.php';
    $db = Database::getInstance();
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}
?>
