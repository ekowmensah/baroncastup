<?php
/**
 * Route Testing Script
 * Test if routing is working properly
 */

// Include configuration and autoloader
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/autoloader.php';

echo "<h1>SmartCast Route Testing</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>";

// Test 1: Check if mod_rewrite is enabled
echo "<h2>1. Apache mod_rewrite Check</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "<p style='color: green;'>✓ mod_rewrite is enabled</p>";
    } else {
        echo "<p style='color: red;'>✗ mod_rewrite is NOT enabled - Please enable it in Apache</p>";
    }
} else {
    echo "<p style='color: orange;'>? Cannot check mod_rewrite status (not running on Apache or function not available)</p>";
}

// Test 2: Check .htaccess file
echo "<h2>2. .htaccess File Check</h2>";
if (file_exists(__DIR__ . '/.htaccess')) {
    echo "<p style='color: green;'>✓ .htaccess file exists</p>";
    echo "<pre>" . htmlspecialchars(file_get_contents(__DIR__ . '/.htaccess')) . "</pre>";
} else {
    echo "<p style='color: red;'>✗ .htaccess file is missing</p>";
}

// Test 3: Router initialization
echo "<h2>3. Router Initialization Test</h2>";
try {
    $app = new SmartCast\Core\Application();
    echo "<p style='color: green;'>✓ Application initialized successfully</p>";
    
    // Get router via reflection to check routes
    $reflection = new ReflectionClass($app);
    $routerProperty = $reflection->getProperty('router');
    $routerProperty->setAccessible(true);
    $router = $routerProperty->getValue($app);
    
    $routesProperty = new ReflectionClass($router);
    $routesField = $routesProperty->getProperty('routes');
    $routesField->setAccessible(true);
    $routes = $routesField->getValue($router);
    
    echo "<p style='color: green;'>✓ Found " . count($routes) . " registered routes</p>";
    
    echo "<h3>Registered Routes:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Method</th><th>Path</th><th>Handler</th></tr>";
    foreach ($routes as $route) {
        echo "<tr>";
        echo "<td>{$route['method']}</td>";
        echo "<td>{$route['path']}</td>";
        echo "<td>{$route['handler']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Application initialization failed: " . $e->getMessage() . "</p>";
}

// Test 4: URL parsing
echo "<h2>4. URL Parsing Test</h2>";
$testUrls = [
    '/smartcast/',
    '/smartcast/login',
    '/smartcast/register',
    '/smartcast/events',
    '/smartcast/api/events'
];

foreach ($testUrls as $testUrl) {
    $path = parse_url($testUrl, PHP_URL_PATH);
    $basePath = '/smartcast';
    if (strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }
    if (empty($path)) {
        $path = '/';
    }
    
    echo "<p><strong>URL:</strong> $testUrl → <strong>Parsed Path:</strong> $path</p>";
}

// Test 5: Direct controller test
echo "<h2>5. Controller Test</h2>";
try {
    $homeController = new SmartCast\Controllers\HomeController();
    echo "<p style='color: green;'>✓ HomeController can be instantiated</p>";
    
    $authController = new SmartCast\Controllers\AuthController();
    echo "<p style='color: green;'>✓ AuthController can be instantiated</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Controller instantiation failed: " . $e->getMessage() . "</p>";
}

// Test 6: Database connection
echo "<h2>6. Database Connection Test</h2>";
try {
    $db = new SmartCast\Core\Database();
    $connection = $db->getConnection();
    $stmt = $connection->query("SELECT 1");
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test URLs
echo "<h2>7. Test URLs</h2>";
echo "<p>Try these URLs to test routing:</p>";
echo "<ul>";
echo "<li><a href='" . APP_URL . "/' target='_blank'>" . APP_URL . "/</a></li>";
echo "<li><a href='" . APP_URL . "/login' target='_blank'>" . APP_URL . "/login</a></li>";
echo "<li><a href='" . APP_URL . "/register' target='_blank'>" . APP_URL . "/register</a></li>";
echo "<li><a href='" . APP_URL . "/events' target='_blank'>" . APP_URL . "/events</a></li>";
echo "<li><a href='" . APP_URL . "/api/events' target='_blank'>" . APP_URL . "/api/events</a></li>";
echo "</ul>";

// Instructions
echo "<h2>8. Troubleshooting Instructions</h2>";
echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
echo "<h3>If routes are still showing 404:</h3>";
echo "<ol>";
echo "<li><strong>Enable mod_rewrite in Apache:</strong>";
echo "<ul><li>Uncomment <code>LoadModule rewrite_module modules/mod_rewrite.so</code> in httpd.conf</li>";
echo "<li>Change <code>AllowOverride None</code> to <code>AllowOverride All</code> for your directory</li>";
echo "<li>Restart Apache</li></ul></li>";

echo "<li><strong>Alternative: Use index.php in URLs:</strong>";
echo "<ul><li>Try: <code>" . APP_URL . "/index.php/login</code></li>";
echo "<li>Try: <code>" . APP_URL . "/index.php/register</code></li></ul></li>";

echo "<li><strong>Check Apache error logs:</strong>";
echo "<ul><li>Look for .htaccess errors in Apache error log</li>";
echo "<li>Check if directory has proper permissions</li></ul></li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><small>Generated on " . date('Y-m-d H:i:s') . "</small></p>";
?>
