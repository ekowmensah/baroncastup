<?php
/**
 * Direct SuperAdmin Test
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/autoloader.php';

echo "<h1>Direct SuperAdmin Dashboard Test</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>";

// Check session
echo "<h2>Session Check</h2>";
echo "<p><strong>User Role:</strong> " . ($_SESSION['user_role'] ?? 'NOT SET') . "</p>";
echo "<p><strong>User ID:</strong> " . ($_SESSION['user_id'] ?? 'NOT SET') . "</p>";

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'platform_admin') {
    echo "<p style='color: red;'>❌ Not authorized for SuperAdmin</p>";
    echo "<p><a href='" . APP_URL . "/login'>Please login as platform_admin</a></p>";
    exit;
}

echo "<p style='color: green;'>✅ Authorized as platform_admin</p>";

echo "<h2>Direct Controller Test</h2>";
try {
    // Directly instantiate and call the controller
    $controller = new SmartCast\Controllers\SuperAdminController();
    echo "<p style='color: green;'>✅ SuperAdminController created</p>";
    
    echo "<h2>Dashboard Output</h2>";
    echo "<div style='border: 2px solid #ccc; padding: 20px; margin: 20px 0;'>";
    
    // Call the dashboard method directly
    ob_start();
    $controller->dashboard();
    $output = ob_get_clean();
    
    echo $output;
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>Routing Test</h2>";
echo "<p>If the dashboard rendered above, the controller works fine.</p>";
echo "<p>The 404 issue is likely in the routing system.</p>";

echo "<h3>Test These URLs:</h3>";
echo "<ul>";
echo "<li><a href='" . APP_URL . "/index.php/superadmin' target='_blank'>With index.php: " . APP_URL . "/index.php/superadmin</a></li>";
echo "<li><a href='" . APP_URL . "/superadmin' target='_blank'>Clean URL: " . APP_URL . "/superadmin</a></li>";
echo "</ul>";

echo "<hr>";
echo "<p><small>Test completed at " . date('Y-m-d H:i:s') . "</small></p>";
?>
