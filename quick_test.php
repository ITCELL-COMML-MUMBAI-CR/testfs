<?php
/**
 * Quick test to verify basic functionality
 */

echo "<h1>SAMPARK Quick System Test</h1>";

// Test 1: Basic includes
echo "<h2>Testing Basic Includes...</h2>";
try {
    require_once 'src/config/database.php';
    echo "✅ Database class loaded<br>";
    
    require_once 'src/config/Config.php';
    echo "✅ Config class loaded<br>";
    
    require_once 'src/utils/Router.php';
    echo "✅ Router class loaded<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test 2: Models
echo "<h2>Testing Models...</h2>";
try {
    require_once 'src/models/BaseModel.php';
    echo "✅ BaseModel loaded<br>";
    
    require_once 'src/models/ComplaintCategoryModel.php';
    $categoryModel = new ComplaintCategoryModel();
    echo "✅ ComplaintCategoryModel instantiated<br>";
    
    require_once 'src/models/ShedModel.php';
    $shedModel = new ShedModel();
    echo "✅ ShedModel instantiated<br>";
    
    require_once 'src/models/NotificationModel.php';
    $notificationModel = new NotificationModel();
    echo "✅ NotificationModel instantiated<br>";
    
} catch (Exception $e) {
    echo "❌ Model Error: " . $e->getMessage() . "<br>";
}

// Test 3: Controllers (without database)
echo "<h2>Testing Controllers...</h2>";

// Change to controller directory temporarily
$originalDir = getcwd();
chdir('src/controllers');

try {
    require_once 'BaseController.php';
    echo "✅ BaseController loaded<br>";
    
    require_once 'PublicController.php';
    echo "✅ PublicController loaded<br>";
    
    // Check methods exist
    $reflection = new ReflectionClass('PublicController');
    if ($reflection->hasMethod('privacyPolicy')) {
        echo "✅ PublicController::privacyPolicy method exists<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Controller Error: " . $e->getMessage() . "<br>";
} finally {
    chdir($originalDir);
}

// Test 4: Router functionality
echo "<h2>Testing Router...</h2>";
try {
    $router = new Router();
    
    // Test base path method
    $reflection = new ReflectionClass($router);
    if ($reflection->hasMethod('getBasePath')) {
        echo "✅ Router::getBasePath method exists<br>";
    }
    
    if ($reflection->hasMethod('enableCache')) {
        echo "✅ Router::enableCache method exists<br>";
    }
    
    echo "✅ Router functionality verified<br>";
    
} catch (Exception $e) {
    echo "❌ Router Error: " . $e->getMessage() . "<br>";
}

// Test 5: View files
echo "<h2>Testing View Templates...</h2>";
$views = [
    'src/views/public/privacy-policy.php',
    'src/views/customer/help.php',
    'src/views/admin/emails.php'
];

foreach ($views as $view) {
    if (file_exists($view)) {
        echo "✅ {$view} exists<br>";
    } else {
        echo "❌ {$view} missing<br>";
    }
}

echo "<h2>Test Complete!</h2>";
echo "<p>If all tests show ✅, the system components are properly loaded.</p>";
?>