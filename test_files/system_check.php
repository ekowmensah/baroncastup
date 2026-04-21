<?php
/**
 * SmartCast System Check
 * Run this file to check system health and configuration
 */

// Include configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/autoloader.php';

// Start output buffering for clean display
ob_start();

echo "<h1>SmartCast System Health Check</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .info { color: blue; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .status-ok { background-color: #d4edda; }
    .status-error { background-color: #f8d7da; }
    .status-warning { background-color: #fff3cd; }
</style>\n";

$checks = [];

// 1. PHP Version Check
$checks['PHP Version'] = [
    'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'OK' : 'ERROR',
    'message' => 'PHP ' . PHP_VERSION . (version_compare(PHP_VERSION, '7.4.0', '>=') ? ' (Compatible)' : ' (Requires 7.4+)'),
    'required' => true
];

// 2. Required PHP Extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl', 'curl'];
foreach ($required_extensions as $ext) {
    $checks["PHP Extension: $ext"] = [
        'status' => extension_loaded($ext) ? 'OK' : 'ERROR',
        'message' => extension_loaded($ext) ? 'Loaded' : 'Missing - Please install php-' . $ext,
        'required' => true
    ];
}

// 3. Configuration Check
$checks['Configuration File'] = [
    'status' => file_exists(__DIR__ . '/config/config.php') ? 'OK' : 'ERROR',
    'message' => file_exists(__DIR__ . '/config/config.php') ? 'Found' : 'Missing config/config.php',
    'required' => true
];

// 4. Database Connection
try {
    $db = new SmartCast\Core\Database();
    $connection = $db->getConnection();
    $checks['Database Connection'] = [
        'status' => 'OK',
        'message' => 'Connected to ' . DB_HOST . '/' . DB_NAME,
        'required' => true
    ];
    
    // Test basic query
    $stmt = $connection->query("SELECT 1");
    $checks['Database Query'] = [
        'status' => 'OK',
        'message' => 'Basic queries working',
        'required' => true
    ];
    
} catch (Exception $e) {
    $checks['Database Connection'] = [
        'status' => 'ERROR',
        'message' => 'Failed: ' . $e->getMessage(),
        'required' => true
    ];
}

// 5. Database Tables Check
if (isset($connection)) {
    try {
        $tables = [
            'users', 'tenants', 'events', 'contestants', 'categories', 'votes', 
            'transactions', 'vote_bundles', 'audit_logs', 'tenant_settings'
        ];
        
        $existing_tables = [];
        $stmt = $connection->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $existing_tables[] = $row[0];
        }
        
        foreach ($tables as $table) {
            $checks["Table: $table"] = [
                'status' => in_array($table, $existing_tables) ? 'OK' : 'WARNING',
                'message' => in_array($table, $existing_tables) ? 'Exists' : 'Missing - Run database migration',
                'required' => false
            ];
        }
        
    } catch (Exception $e) {
        $checks['Database Tables'] = [
            'status' => 'ERROR',
            'message' => 'Cannot check tables: ' . $e->getMessage(),
            'required' => false
        ];
    }
}

// 6. Directory Permissions
$directories = [
    'public/uploads' => __DIR__ . '/public/uploads',
    'views' => __DIR__ . '/views',
    'src' => __DIR__ . '/src'
];

foreach ($directories as $name => $path) {
    if (file_exists($path)) {
        $writable = is_writable($path);
        $checks["Directory: $name"] = [
            'status' => $writable ? 'OK' : 'WARNING',
            'message' => $writable ? 'Writable' : 'Not writable - Check permissions',
            'required' => $name === 'public/uploads'
        ];
    } else {
        $checks["Directory: $name"] = [
            'status' => 'ERROR',
            'message' => 'Missing directory',
            'required' => true
        ];
    }
}

// 7. Core Files Check
$core_files = [
    'index.php' => __DIR__ . '/index.php',
    'autoloader.php' => __DIR__ . '/includes/autoloader.php',
    'Application.php' => __DIR__ . '/src/Core/Application.php',
    'Router.php' => __DIR__ . '/src/Core/Router.php',
    'Database.php' => __DIR__ . '/src/Core/Database.php'
];

foreach ($core_files as $name => $path) {
    $checks["Core File: $name"] = [
        'status' => file_exists($path) ? 'OK' : 'ERROR',
        'message' => file_exists($path) ? 'Found' : 'Missing',
        'required' => true
    ];
}

// 8. CSS and JS Files
$assets = [
    'public.css' => __DIR__ . '/public/css/public.css',
    'organizer.css' => __DIR__ . '/public/css/organizer.css',
    'admin.css' => __DIR__ . '/public/css/admin.css',
    'public.js' => __DIR__ . '/public/js/public.js'
];

foreach ($assets as $name => $path) {
    $checks["Asset: $name"] = [
        'status' => file_exists($path) ? 'OK' : 'WARNING',
        'message' => file_exists($path) ? 'Found' : 'Missing',
        'required' => false
    ];
}

// 9. Security Check
$checks['Debug Mode'] = [
    'status' => APP_DEBUG ? 'WARNING' : 'OK',
    'message' => APP_DEBUG ? 'Enabled - Disable in production' : 'Disabled',
    'required' => false
];

$checks['Default Secrets'] = [
    'status' => (JWT_SECRET === 'your-jwt-secret-key-change-this') ? 'ERROR' : 'OK',
    'message' => (JWT_SECRET === 'your-jwt-secret-key-change-this') ? 'Using default secrets - Change immediately' : 'Custom secrets configured',
    'required' => true
];

// Display Results
echo "<h2>System Check Results</h2>\n";
echo "<table>\n";
echo "<tr><th>Component</th><th>Status</th><th>Message</th><th>Required</th></tr>\n";

$total_checks = count($checks);
$passed_checks = 0;
$critical_errors = 0;

foreach ($checks as $component => $check) {
    $status_class = '';
    switch ($check['status']) {
        case 'OK':
            $status_class = 'status-ok';
            $passed_checks++;
            break;
        case 'ERROR':
            $status_class = 'status-error';
            if ($check['required']) $critical_errors++;
            break;
        case 'WARNING':
            $status_class = 'status-warning';
            break;
    }
    
    echo "<tr class='$status_class'>";
    echo "<td>$component</td>";
    echo "<td>{$check['status']}</td>";
    echo "<td>{$check['message']}</td>";
    echo "<td>" . ($check['required'] ? 'Yes' : 'No') . "</td>";
    echo "</tr>\n";
}

echo "</table>\n";

// Summary
echo "<h2>Summary</h2>\n";
echo "<p><strong>Total Checks:</strong> $total_checks</p>\n";
echo "<p><strong>Passed:</strong> <span class='success'>$passed_checks</span></p>\n";
echo "<p><strong>Critical Errors:</strong> <span class='error'>$critical_errors</span></p>\n";

if ($critical_errors === 0) {
    echo "<p class='success'><strong>✓ System is ready to run!</strong></p>\n";
} else {
    echo "<p class='error'><strong>✗ Critical errors found. Please fix before running the system.</strong></p>\n";
}

// Recommendations
echo "<h2>Recommendations</h2>\n";
echo "<ul>\n";

if (APP_DEBUG) {
    echo "<li>Disable debug mode in production by setting APP_DEBUG to false</li>\n";
}

if (JWT_SECRET === 'your-jwt-secret-key-change-this') {
    echo "<li><strong>CRITICAL:</strong> Change default security keys in config.php</li>\n";
}

if (!file_exists(__DIR__ . '/public/uploads')) {
    echo "<li>Create uploads directory: mkdir public/uploads && chmod 755 public/uploads</li>\n";
}

echo "<li>Ensure database tables are created by importing smartcast.sql</li>\n";
echo "<li>Configure web server to point document root to the project directory</li>\n";
echo "<li>Set up SSL certificate for production use</li>\n";
echo "</ul>\n";

// System Information
echo "<h2>System Information</h2>\n";
echo "<table>\n";
echo "<tr><th>Item</th><th>Value</th></tr>\n";
echo "<tr><td>PHP Version</td><td>" . PHP_VERSION . "</td></tr>\n";
echo "<tr><td>Server Software</td><td>" . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</td></tr>\n";
echo "<tr><td>Document Root</td><td>" . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</td></tr>\n";
echo "<tr><td>App URL</td><td>" . APP_URL . "</td></tr>\n";
echo "<tr><td>Database Host</td><td>" . DB_HOST . "</td></tr>\n";
echo "<tr><td>Database Name</td><td>" . DB_NAME . "</td></tr>\n";
echo "<tr><td>Current Time</td><td>" . date('Y-m-d H:i:s') . "</td></tr>\n";
echo "</table>\n";

// URLs to test
echo "<h2>URLs to Test</h2>\n";
echo "<ul>\n";
echo "<li><a href='" . APP_URL . "' target='_blank'>Homepage: " . APP_URL . "</a></li>\n";
echo "<li><a href='" . APP_URL . "/login' target='_blank'>Login: " . APP_URL . "/login</a></li>\n";
echo "<li><a href='" . APP_URL . "/register' target='_blank'>Register: " . APP_URL . "/register</a></li>\n";
echo "<li><a href='" . APP_URL . "/events' target='_blank'>Events: " . APP_URL . "/events</a></li>\n";
echo "<li><a href='" . APP_URL . "/api/events' target='_blank'>API: " . APP_URL . "/api/events</a></li>\n";
echo "</ul>\n";

echo "<hr>\n";
echo "<p><small>Generated on " . date('Y-m-d H:i:s') . " | SmartCast System Check v1.0</small></p>\n";

// End output buffering and display
$content = ob_get_clean();
echo $content;
?>
