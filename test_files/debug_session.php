<?php
/**
 * Session Debug Script
 */

// Start session first
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/autoloader.php';

echo "<h1>SmartCast Session Debug</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>";

echo "<h2>1. Raw Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>2. Session Configuration</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Name:</strong> " . session_name() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>Session Cookie Params:</strong></p>";
echo "<pre>";
print_r(session_get_cookie_params());
echo "</pre>";

echo "<h2>3. SmartCast Session Class Test</h2>";
try {
    $session = new SmartCast\Core\Session();
    
    echo "<p><strong>Is Logged In:</strong> " . ($session->isLoggedIn() ? 'YES' : 'NO') . "</p>";
    
    if ($session->isLoggedIn()) {
        echo "<p><strong>User ID:</strong> " . $session->getUserId() . "</p>";
        echo "<p><strong>User Role:</strong> " . $session->getUserRole() . "</p>";
        echo "<p><strong>User Email:</strong> " . $session->get('user_email') . "</p>";
        echo "<p><strong>Tenant ID:</strong> " . $session->getTenantId() . "</p>";
    } else {
        echo "<p style='color: red;'>Session shows not logged in</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Session error: " . $e->getMessage() . "</p>";
}

echo "<h2>4. Manual Session Check</h2>";
echo "<p><strong>user_id in session:</strong> " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . "</p>";
echo "<p><strong>user_role in session:</strong> " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'NOT SET') . "</p>";
echo "<p><strong>user_email in session:</strong> " . (isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'NOT SET') . "</p>";
echo "<p><strong>tenant_id in session:</strong> " . (isset($_SESSION['tenant_id']) ? $_SESSION['tenant_id'] : 'NOT SET') . "</p>";

echo "<h2>5. Test Dashboard Access</h2>";
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'platform_admin') {
    echo "<p style='color: green;'>✓ You should have access to Super Admin Dashboard</p>";
    echo "<p><a href='" . SUPERADMIN_URL . "' target='_blank'>Try Super Admin Dashboard</a></p>";
} elseif (isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['owner', 'manager'])) {
    echo "<p style='color: green;'>✓ You should have access to Organizer Dashboard</p>";
    echo "<p><a href='" . ORGANIZER_URL . "' target='_blank'>Try Organizer Dashboard</a></p>";
} else {
    echo "<p style='color: red;'>✗ No dashboard access detected</p>";
}

echo "<h2>6. Test Controller Access</h2>";
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'platform_admin') {
    try {
        echo "<p>Attempting to create SuperAdminController...</p>";
        $controller = new SmartCast\Controllers\SuperAdminController();
        echo "<p style='color: green;'>✓ SuperAdminController created successfully!</p>";
        echo "<p>This means the dashboard should work.</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ SuperAdminController failed: " . $e->getMessage() . "</p>";
    }
}

echo "<h2>7. Session Constants</h2>";
echo "<p><strong>SESSION_NAME:</strong> " . (defined('SESSION_NAME') ? SESSION_NAME : 'NOT DEFINED') . "</p>";
echo "<p><strong>SESSION_LIFETIME:</strong> " . (defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 'NOT DEFINED') . "</p>";

echo "<h2>8. Quick Actions</h2>";
echo "<ul>";
echo "<li><a href='" . APP_URL . "/login' target='_blank'>Go to Login Page</a></li>";
echo "<li><a href='" . APP_URL . "/logout' target='_blank'>Logout</a></li>";
echo "<li><a href='" . SUPERADMIN_URL . "' target='_blank'>Try Super Admin Dashboard</a></li>";
echo "<li><a href='" . APP_URL . "/index.php/superadmin' target='_blank'>Try Super Admin (Alternative URL)</a></li>";
echo "</ul>";

echo "<hr>";
echo "<p><small>Debug completed at " . date('Y-m-d H:i:s') . "</small></p>";
?>
