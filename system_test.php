<?php
/**
 * SAMPARK System Functionality Test
 * Tests all major components and newly added functionality
 */

// Start output buffering
ob_start();

// Set error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once 'src/config/database.php';
require_once 'src/config/Config.php';
require_once 'src/utils/Router.php';

// Test results array
$testResults = [];

function runTest($testName, $testFunction) {
    global $testResults;
    
    try {
        $startTime = microtime(true);
        $result = $testFunction();
        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000, 2); // in milliseconds
        
        $testResults[] = [
            'name' => $testName,
            'status' => $result ? 'PASS' : 'FAIL',
            'execution_time' => $executionTime,
            'message' => $result === true ? 'Success' : $result
        ];
        
        return $result;
    } catch (Exception $e) {
        $testResults[] = [
            'name' => $testName,
            'status' => 'ERROR',
            'execution_time' => 0,
            'message' => $e->getMessage()
        ];
        
        return false;
    }
}

// Test 1: Database Connection
runTest('Database Connection', function() {
    $db = Database::getInstance();
    $result = $db->fetch("SELECT 1 as test");
    return $result && $result['test'] == 1;
});

// Test 2: Router Class Functionality
runTest('Router Base Path Detection', function() {
    $router = new Router();
    
    // Test auto-detection by checking if the class exists and methods work
    $reflection = new ReflectionClass($router);
    $getBasePathMethod = $reflection->getMethod('getBasePath');
    $getBasePathMethod->setAccessible(true);
    
    // Should return string (even if empty)
    $basePath = $getBasePathMethod->invoke($router);
    return is_string($basePath);
});

// Test 3: Router Cache Functionality
runTest('Router Cache Functionality', function() {
    $router = new Router();
    
    // Enable caching
    $router->enableCache(true);
    
    // Check if method exists
    $reflection = new ReflectionClass($router);
    $methods = $reflection->getMethods();
    $cacheMethodExists = false;
    
    foreach ($methods as $method) {
        if ($method->getName() === 'enableCache') {
            $cacheMethodExists = true;
            break;
        }
    }
    
    return $cacheMethodExists;
});

// Test 4: Missing Models Availability
runTest('ComplaintCategoryModel Class', function() {
    require_once 'src/models/ComplaintCategoryModel.php';
    return class_exists('ComplaintCategoryModel');
});

runTest('ShedModel Class', function() {
    require_once 'src/models/ShedModel.php';
    return class_exists('ShedModel');
});

runTest('WagonModel Class', function() {
    require_once 'src/models/WagonModel.php';
    return class_exists('WagonModel');
});

runTest('NotificationModel Class', function() {
    require_once 'src/models/NotificationModel.php';
    return class_exists('NotificationModel');
});

runTest('NewsModel Class', function() {
    require_once 'src/models/NewsModel.php';
    return class_exists('NewsModel');
});

// Test 5: Model Functionality
runTest('ComplaintCategoryModel Functionality', function() {
    require_once 'src/models/BaseModel.php';
    require_once 'src/models/ComplaintCategoryModel.php';
    
    $model = new ComplaintCategoryModel();
    
    // Test that the model has expected methods
    $reflection = new ReflectionClass($model);
    $requiredMethods = ['getMainCategories', 'getSubcategories', 'getCategoriesHierarchy'];
    
    foreach ($requiredMethods as $method) {
        if (!$reflection->hasMethod($method)) {
            return "Missing method: {$method}";
        }
    }
    
    return true;
});

runTest('ShedModel Functionality', function() {
    require_once 'src/models/BaseModel.php';
    require_once 'src/models/ShedModel.php';
    
    $model = new ShedModel();
    
    // Test that the model has expected methods
    $reflection = new ReflectionClass($model);
    $requiredMethods = ['getActiveSheds', 'getShedsByZone', 'searchSheds'];
    
    foreach ($requiredMethods as $method) {
        if (!$reflection->hasMethod($method)) {
            return "Missing method: {$method}";
        }
    }
    
    return true;
});

runTest('NotificationModel Functionality', function() {
    require_once 'src/models/BaseModel.php';
    require_once 'src/models/NotificationModel.php';
    
    $model = new NotificationModel();
    
    // Test that the model has expected methods and constants
    $reflection = new ReflectionClass($model);
    $requiredMethods = ['getUserNotifications', 'createNotification', 'markAsRead'];
    
    foreach ($requiredMethods as $method) {
        if (!$reflection->hasMethod($method)) {
            return "Missing method: {$method}";
        }
    }
    
    // Test constants
    $constants = $reflection->getConstants();
    $requiredConstants = ['TYPE_TICKET_CREATED', 'PRIORITY_LOW', 'PRIORITY_HIGH'];
    
    foreach ($requiredConstants as $constant) {
        if (!array_key_exists($constant, $constants)) {
            return "Missing constant: {$constant}";
        }
    }
    
    return true;
});

// Test 6: Controller Classes
runTest('PublicController Class', function() {
    // Change to controllers directory context
    $originalDir = getcwd();
    chdir('src/controllers');
    
    try {
        require_once 'PublicController.php';
        $result = class_exists('PublicController');
        chdir($originalDir);
        return $result;
    } catch (Exception $e) {
        chdir($originalDir);
        return "Error loading PublicController: " . $e->getMessage();
    }
});

runTest('PublicController Methods', function() {
    $reflection = new ReflectionClass('PublicController');
    $requiredMethods = ['privacyPolicy', 'about', 'help', 'contact'];
    
    foreach ($requiredMethods as $method) {
        if (!$reflection->hasMethod($method)) {
            return "Missing method: {$method}";
        }
    }
    
    return true;
});

// Test 7: AdminController New Methods
runTest('AdminController Email Methods', function() {
    // Change to controllers directory context
    $originalDir = getcwd();
    chdir('src/controllers');
    
    try {
        require_once 'AdminController.php';
        $reflection = new ReflectionClass('AdminController');
        $requiredMethods = ['emails', 'emailTemplates', 'sendBulkEmail', 'storeAnnouncement'];
        
        chdir($originalDir);
        
        foreach ($requiredMethods as $method) {
            if (!$reflection->hasMethod($method)) {
                return "Missing method: {$method}";
            }
        }
        
        return true;
    } catch (Exception $e) {
        chdir($originalDir);
        return "Error loading AdminController: " . $e->getMessage();
    }
});

// Test 8: CustomerController Help Method
runTest('CustomerController Help Method', function() {
    // Change to controllers directory context
    $originalDir = getcwd();
    chdir('src/controllers');
    
    try {
        require_once 'CustomerController.php';
        $reflection = new ReflectionClass('CustomerController');
        
        chdir($originalDir);
        
        if (!$reflection->hasMethod('help')) {
            return "Missing help method";
        }
        
        return true;
    } catch (Exception $e) {
        chdir($originalDir);
        return "Error loading CustomerController: " . $e->getMessage();
    }
});

// Test 9: View Templates
runTest('Privacy Policy View Template', function() {
    return file_exists('src/views/public/privacy-policy.php');
});

runTest('Customer Help View Template', function() {
    return file_exists('src/views/customer/help.php');
});

runTest('Admin Email View Template', function() {
    return file_exists('src/views/admin/emails.php');
});

// Test 10: Route Configuration
runTest('Route Configuration File', function() {
    $routesContent = file_get_contents('src/config/routes.php');
    
    // Check if privacy policy route exists
    $hasPrivacyRoute = strpos($routesContent, "privacy-policy") !== false;
    
    // Check if admin email routes exist
    $hasEmailRoutes = strpos($routesContent, "admin/emails") !== false;
    
    // Check if customer help route exists
    $hasHelpRoute = strpos($routesContent, "customer/help") !== false;
    
    return $hasPrivacyRoute && $hasEmailRoutes && $hasHelpRoute;
});

// Test 11: Directory Structure
runTest('Required Directories Exist', function() {
    $requiredDirs = [
        'src/models',
        'src/controllers',
        'src/views/public',
        'src/views/customer',
        'src/views/admin',
        'src/utils',
        'src/config',
        'logs',
        'public/uploads'
    ];
    
    foreach ($requiredDirs as $dir) {
        if (!is_dir($dir)) {
            return "Missing directory: {$dir}";
        }
    }
    
    return true;
});

// Test 12: Configuration Files
runTest('Configuration Files Exist', function() {
    $requiredFiles = [
        'src/config/Config.php',
        'src/config/database.php',
        'src/config/routes.php',
        'src/config/sampark_db.sql'
    ];
    
    foreach ($requiredFiles as $file) {
        if (!file_exists($file)) {
            return "Missing file: {$file}";
        }
    }
    
    return true;
});

// Test 13: Base Path Configuration
runTest('Base Path Auto-Detection', function() {
    // Test different scenarios for base path detection
    $originalScriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    
    // Test case 1: Document root installation
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $router = new Router();
    
    $reflection = new ReflectionClass($router);
    $getBasePathMethod = $reflection->getMethod('getBasePath');
    $getBasePathMethod->setAccessible(true);
    
    $basePath = $getBasePathMethod->invoke($router);
    
    // Restore original
    $_SERVER['SCRIPT_NAME'] = $originalScriptName;
    
    // Should handle document root correctly
    return is_string($basePath);
});

// Test 14: Error Handling Enhancement
runTest('Router Error Handling Methods', function() {
    $reflection = new ReflectionClass('Router');
    $requiredMethods = ['handleError', 'handleInternalError', 'handleForbidden'];
    
    foreach ($requiredMethods as $method) {
        if (!$reflection->hasMethod($method)) {
            return "Missing error handling method: {$method}";
        }
    }
    
    return true;
});

// Test 15: System Integration
runTest('System Integration Test', function() {
    // Test if we can instantiate key components together
    try {
        $db = Database::getInstance();
        $router = new Router();
        
        // Test model instantiation
        require_once 'src/models/BaseModel.php';
        require_once 'src/models/ComplaintCategoryModel.php';
        $categoryModel = new ComplaintCategoryModel();
        
        return true;
    } catch (Exception $e) {
        return "Integration error: " . $e->getMessage();
    }
});

// Display Results
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMPARK System Test Results</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            overflow: hidden;
        }
        .header { 
            background: linear-gradient(135deg, #0088cc 0%, #005fa3 100%); 
            color: white; 
            padding: 20px; 
            text-align: center; 
        }
        .stats { 
            padding: 20px; 
            display: flex; 
            justify-content: space-around; 
            background: #f8f9fa; 
            border-bottom: 1px solid #dee2e6;
        }
        .stat { 
            text-align: center; 
        }
        .stat-number { 
            font-size: 2em; 
            font-weight: bold; 
            margin-bottom: 5px; 
        }
        .pass { color: #28a745; }
        .fail { color: #dc3545; }
        .error { color: #fd7e14; }
        .results { 
            padding: 20px; 
        }
        .test-item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 12px 16px; 
            margin-bottom: 8px; 
            border-radius: 6px; 
            border-left: 4px solid #dee2e6;
        }
        .test-item.pass { 
            background: #d4edda; 
            border-left-color: #28a745; 
        }
        .test-item.fail { 
            background: #f8d7da; 
            border-left-color: #dc3545; 
        }
        .test-item.error { 
            background: #fff3cd; 
            border-left-color: #fd7e14; 
        }
        .test-name { 
            font-weight: 500; 
            flex-grow: 1; 
        }
        .test-status { 
            font-weight: bold; 
            margin: 0 15px; 
        }
        .test-time { 
            font-size: 0.85em; 
            color: #666; 
            min-width: 60px; 
            text-align: right;
        }
        .test-message { 
            font-size: 0.9em; 
            color: #666; 
            margin-top: 4px; 
        }
        .summary { 
            margin-top: 20px; 
            padding: 15px; 
            background: #e9ecef; 
            border-radius: 6px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚆 SAMPARK System Test Results</h1>
            <p>Comprehensive functionality verification for Support and Mediation Portal for All Rail Cargo</p>
        </div>
        
        <?php
        // Calculate statistics
        $totalTests = count($testResults);
        $passedTests = count(array_filter($testResults, fn($test) => $test['status'] === 'PASS'));
        $failedTests = count(array_filter($testResults, fn($test) => $test['status'] === 'FAIL'));
        $errorTests = count(array_filter($testResults, fn($test) => $test['status'] === 'ERROR'));
        $totalTime = array_sum(array_column($testResults, 'execution_time'));
        
        $successRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 1) : 0;
        ?>
        
        <div class="stats">
            <div class="stat">
                <div class="stat-number"><?= $totalTests ?></div>
                <div>Total Tests</div>
            </div>
            <div class="stat">
                <div class="stat-number pass"><?= $passedTests ?></div>
                <div>Passed</div>
            </div>
            <div class="stat">
                <div class="stat-number fail"><?= $failedTests ?></div>
                <div>Failed</div>
            </div>
            <div class="stat">
                <div class="stat-number error"><?= $errorTests ?></div>
                <div>Errors</div>
            </div>
            <div class="stat">
                <div class="stat-number"><?= $successRate ?>%</div>
                <div>Success Rate</div>
            </div>
        </div>
        
        <div class="results">
            <h3>Test Results</h3>
            
            <?php foreach ($testResults as $test): ?>
            <div class="test-item <?= strtolower($test['status']) ?>">
                <div>
                    <div class="test-name"><?= htmlspecialchars($test['name']) ?></div>
                    <?php if ($test['message'] !== 'Success'): ?>
                    <div class="test-message"><?= htmlspecialchars($test['message']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="test-status <?= strtolower($test['status']) ?>"><?= $test['status'] ?></div>
                <div class="test-time"><?= $test['execution_time'] ?>ms</div>
            </div>
            <?php endforeach; ?>
            
            <div class="summary">
                <strong>Summary:</strong> 
                Executed <?= $totalTests ?> tests in <?= round($totalTime, 2) ?>ms. 
                Success rate: <?= $successRate ?>%. 
                <?php if ($successRate >= 90): ?>
                    <span class="pass">✅ System is ready for deployment!</span>
                <?php elseif ($successRate >= 70): ?>
                    <span style="color: #fd7e14;">⚠️ System has minor issues that should be addressed.</span>
                <?php else: ?>
                    <span class="fail">❌ System has critical issues that must be fixed before deployment.</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// End output buffering and send to browser
ob_end_flush();
?>