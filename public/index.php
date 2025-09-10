<?php
/**
 * SAMPARK - Support and Mediation Portal for All Rail Cargo
 * Main Entry Point - All requests are routed through this file
 */

// Start output buffering
ob_start();

// Set error reporting
define('ENVIRONMENT', 'development');
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Include autoloader and core files
require_once '../src/config/database.php';
require_once '../src/config/Config.php';
require_once '../src/utils/Session.php';
require_once '../src/utils/ActivityLogger.php';
require_once '../src/utils/Router.php';
require_once '../src/controllers/BaseController.php';

// Set custom error handlers
set_error_handler('Config::errorHandler');
set_exception_handler('Config::exceptionHandler');
register_shutdown_function('Config::shutdownHandler');

// Configure PHP to log errors to our error.log file
ini_set('log_errors', 1);
ini_set('error_log', '../logs/error.log');

// Initialize router
$router = new Router();

// Define routes
include '../src/config/routes.php';

// Handle the request
try {
    $router->dispatch();
} catch (Exception $e) {
    // Our exception handler will handle this
    Config::exceptionHandler($e);
}

// End output buffering
ob_end_flush();
?>
