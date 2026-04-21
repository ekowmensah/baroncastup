<?php
/**
 * Routing Debug Script
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/autoloader.php';

echo "<h1>SmartCast Routing Debug</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>";

echo "<h2>1. Current Request Info</h2>";
echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "</p>";
echo "<p><strong>REQUEST_METHOD:</strong> " . ($_SERVER['REQUEST_METHOD'] ?? 'NOT SET') . "</p>";
echo "<p><strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET') . "</p>";
echo "<p><strong>PATH_INFO:</strong> " . ($_SERVER['PATH_INFO'] ?? 'NOT SET') . "</p>";

echo "<h2>2. URL Parsing Test</h2>";
$testUrls = [
    '/smartcast/superadmin',
    '/smartcast/index.php/superadmin',
    '/smartcast/organizer',
    '/smartcast/index.php/organizer'
];

foreach ($testUrls as $testUrl) {
    echo "<h3>Testing: $testUrl</h3>";
    
    $path = parse_url($testUrl, PHP_URL_PATH);
    echo "<p>Parsed path: $path</p>";
    
    // Simulate Router logic
    $basePath = '/smartcast';
    if (strpos($path, $basePath . '/index.php') === 0) {
        $processedPath = substr($path, strlen($basePath . '/index.php'));
        echo "<p>Non-mod_rewrite path: $processedPath</p>";
    } elseif (strpos($path, $basePath) === 0) {
        $processedPath = substr($path, strlen($basePath));
        echo "<p>Mod_rewrite path: $processedPath</p>";
    } else {
        $processedPath = $path;
        echo "<p>No base path match: $processedPath</p>";
    }
    
    if (empty($processedPath)) {
        $processedPath = '/';
    }
    
    echo "<p><strong>Final processed path: $processedPath</strong></p>";
    echo "<hr>";
}

echo "<h2>3. Application Routes Test</h2>";
try {
    $app = new SmartCast\Core\Application();
    
    // Get router via reflection
    $reflection = new ReflectionClass($app);
    $routerProperty = $reflection->getProperty('router');
    $routerProperty->setAccessible(true);
    $router = $routerProperty->getValue($app);
    
    // Get routes via reflection
    $routesProperty = new ReflectionClass($router);
    $routesField = $routesProperty->getProperty('routes');
    $routesField->setAccessible(true);
    $routes = $routesField->getValue($router);
    
    echo "<p><strong>Total registered routes:</strong> " . count($routes) . "</p>";
    
    echo "<h3>SuperAdmin Routes:</h3>";
    $superadminRoutes = array_filter($routes, function($route) {
        return strpos($route['path'], '/superadmin') === 0;
    });
    
    if (empty($superadminRoutes)) {
        echo "<p style='color: red;'>❌ No superadmin routes found!</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Method</th><th>Path</th><th>Handler</th><th>Middleware</th></tr>";
        foreach ($superadminRoutes as $route) {
            echo "<tr>";
            echo "<td>{$route['method']}</td>";
            echo "<td>{$route['path']}</td>";
            echo "<td>{$route['handler']}</td>";
            echo "<td>" . ($route['middleware'] ?? 'none') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>All Routes (first 20):</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Method</th><th>Path</th><th>Handler</th></tr>";
    foreach (array_slice($routes, 0, 20) as $route) {
        echo "<tr>";
        echo "<td>{$route['method']}</td>";
        echo "<td>{$route['path']}</td>";
        echo "<td>{$route['handler']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error getting routes: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Manual Route Matching Test</h2>";
$testPath = '/superadmin';
echo "<p>Testing path: <strong>$testPath</strong></p>";

if (isset($routes)) {
    $matchingRoutes = [];
    foreach ($routes as $route) {
        if ($route['method'] === 'GET') {
            // Simple pattern matching
            $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $testPath)) {
                $matchingRoutes[] = $route;
            }
        }
    }
    
    if (empty($matchingRoutes)) {
        echo "<p style='color: red;'>❌ No matching routes found for $testPath</p>";
    } else {
        echo "<p style='color: green;'>✅ Found " . count($matchingRoutes) . " matching routes:</p>";
        foreach ($matchingRoutes as $route) {
            echo "<p>- {$route['method']} {$route['path']} → {$route['handler']}</p>";
        }
    }
}

echo "<h2>5. Test Links</h2>";
echo "<ul>";
echo "<li><a href='" . APP_URL . "/index.php/superadmin' target='_blank'>Test: " . APP_URL . "/index.php/superadmin</a></li>";
echo "<li><a href='" . APP_URL . "/superadmin' target='_blank'>Test: " . APP_URL . "/superadmin</a></li>";
echo "</ul>";

echo "<hr>";
echo "<p><small>Debug completed at " . date('Y-m-d H:i:s') . "</small></p>";
?>
