<?php
/**
 * Redirect Test
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/autoloader.php';

echo "<h1>SmartCast Redirect Test</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>";

echo "<h2>URL Constants Test</h2>";
echo "<p><strong>APP_URL:</strong> " . APP_URL . "</p>";
echo "<p><strong>PUBLIC_URL:</strong> " . PUBLIC_URL . "</p>";
echo "<p><strong>ORGANIZER_URL:</strong> " . ORGANIZER_URL . "</p>";
echo "<p><strong>ADMIN_URL:</strong> " . ADMIN_URL . "</p>";
echo "<p><strong>SUPERADMIN_URL:</strong> " . SUPERADMIN_URL . "</p>";

echo "<h2>Redirect Function Test</h2>";

// Test the redirect logic without actually redirecting
function testRedirect($path) {
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    } else {
        return APP_URL . $path;
    }
}

$testPaths = [
    '/login' => 'Relative path',
    '/admin' => 'Relative path',
    SUPERADMIN_URL => 'Full URL constant',
    ORGANIZER_URL => 'Full URL constant',
    ADMIN_URL => 'Full URL constant',
    'http://example.com' => 'External URL'
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Input Path</th><th>Type</th><th>Result URL</th><th>Status</th></tr>";

foreach ($testPaths as $path => $type) {
    $result = testRedirect($path);
    $status = (strpos($result, 'smartcasthttp://') !== false) ? 
        '<span style="color: red;">❌ BROKEN</span>' : 
        '<span style="color: green;">✅ GOOD</span>';
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($path) . "</td>";
    echo "<td>$type</td>";
    echo "<td>" . htmlspecialchars($result) . "</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Login Role Redirect Test</h2>";
echo "<p>These are the URLs that would be used for different user roles:</p>";

$roles = [
    'platform_admin' => SUPERADMIN_URL,
    'owner' => ORGANIZER_URL,
    'manager' => ORGANIZER_URL,
    'staff' => ADMIN_URL
];

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>User Role</th><th>Redirect URL</th><th>Status</th></tr>";

foreach ($roles as $role => $url) {
    $status = (strpos($url, 'smartcasthttp://') !== false) ? 
        '<span style="color: red;">❌ BROKEN</span>' : 
        '<span style="color: green;">✅ GOOD</span>';
    
    echo "<tr>";
    echo "<td>$role</td>";
    echo "<td>" . htmlspecialchars($url) . "</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Test Login Links</h2>";
echo "<p>Try logging in with different user roles to test the redirect:</p>";
echo "<ul>";
echo "<li><a href='" . APP_URL . "/login' target='_blank'>Login Page</a></li>";
echo "<li><a href='" . APP_URL . "/index.php/login' target='_blank'>Login Page (Alternative)</a></li>";
echo "</ul>";

echo "<hr>";
echo "<p><small>Test completed at " . date('Y-m-d H:i:s') . "</small></p>";
?>
