<?php
/**
 * Basic Test - Check if core components work
 */

echo "<h1>SmartCast Basic Test</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>";

// Test 1: Configuration
echo "<h2>1. Configuration Test</h2>";
if (file_exists(__DIR__ . '/config/config.php')) {
    try {
        require_once __DIR__ . '/config/config.php';
        echo "<p style='color: green;'>✓ Configuration loaded successfully</p>";
        echo "<p>App URL: " . APP_URL . "</p>";
        echo "<p>Debug Mode: " . (APP_DEBUG ? 'ON' : 'OFF') . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Configuration error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Configuration file missing</p>";
}

// Test 2: Autoloader
echo "<h2>2. Autoloader Test</h2>";
if (file_exists(__DIR__ . '/includes/autoloader.php')) {
    try {
        require_once __DIR__ . '/includes/autoloader.php';
        echo "<p style='color: green;'>✓ Autoloader loaded successfully</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Autoloader error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Autoloader file missing</p>";
}

// Test 3: Class Loading
echo "<h2>3. Class Loading Test</h2>";
$classes_to_test = [
    'SmartCast\Core\Application',
    'SmartCast\Core\Router',
    'SmartCast\Core\Database',
    'SmartCast\Controllers\HomeController',
    'SmartCast\Controllers\AuthController'
];

foreach ($classes_to_test as $class) {
    if (class_exists($class)) {
        echo "<p style='color: green;'>✓ $class loaded</p>";
    } else {
        echo "<p style='color: red;'>✗ $class not found</p>";
    }
}

// Test 4: Database Connection
echo "<h2>4. Database Connection Test</h2>";
if (class_exists('SmartCast\Core\Database')) {
    try {
        $db = new SmartCast\Core\Database();
        $connection = $db->getConnection();
        $stmt = $connection->query("SELECT 1 as test");
        $result = $stmt->fetch();
        if ($result['test'] == 1) {
            echo "<p style='color: green;'>✓ Database connection successful</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Database class not available</p>";
}

// Test 5: Router Test
echo "<h2>5. Router Test</h2>";
if (class_exists('SmartCast\Core\Router')) {
    try {
        $router = new SmartCast\Core\Router();
        $router->get('/test', function() { echo 'Test route works!'; });
        echo "<p style='color: green;'>✓ Router can be instantiated</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Router error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Router class not available</p>";
}

// Test 6: Application Test
echo "<h2>6. Application Test</h2>";
if (class_exists('SmartCast\Core\Application')) {
    try {
        // Don't actually run the application, just test instantiation
        echo "<p style='color: green;'>✓ Application class is available</p>";
        echo "<p><strong>Note:</strong> Application not started to avoid conflicts</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Application error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Application class not available</p>";
}

// Test URLs
echo "<h2>7. Test These URLs</h2>";
echo "<p>If the above tests pass, try these URLs:</p>";
echo "<ul>";
echo "<li><a href='" . (defined('APP_URL') ? APP_URL : 'http://localhost/smartcast') . "/index.php' target='_blank'>Direct Index</a></li>";
echo "<li><a href='" . (defined('APP_URL') ? APP_URL : 'http://localhost/smartcast') . "/index.php/login' target='_blank'>Login (with index.php)</a></li>";
echo "<li><a href='" . (defined('APP_URL') ? APP_URL : 'http://localhost/smartcast') . "/login' target='_blank'>Login (clean URL)</a></li>";
echo "</ul>";

echo "<hr>";
echo "<p><small>Test completed at " . date('Y-m-d H:i:s') . "</small></p>";
?>
