<?php
/**
 * Dashboard Routes Test
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/autoloader.php';

echo "<h1>SmartCast Dashboard Routes Test</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>";

// Test 1: Check if controllers exist
echo "<h2>1. Controller Existence Test</h2>";
$controllers = [
    'OrganizerController' => 'SmartCast\\Controllers\\OrganizerController',
    'SuperAdminController' => 'SmartCast\\Controllers\\SuperAdminController',
    'AdminController' => 'SmartCast\\Controllers\\AdminController'
];

foreach ($controllers as $name => $class) {
    if (class_exists($class)) {
        echo "<p style='color: green;'>✓ $name exists</p>";
        
        // Test if dashboard method exists
        if (method_exists($class, 'dashboard')) {
            echo "<p style='color: green;'>  ✓ dashboard() method exists</p>";
        } else {
            echo "<p style='color: red;'>  ✗ dashboard() method missing</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ $name missing</p>";
    }
}

// Test 2: Check session and authentication
echo "<h2>2. Session Test</h2>";
try {
    $session = new SmartCast\Core\Session();
    echo "<p>Session Status: " . ($session->isLoggedIn() ? 'Logged In' : 'Not Logged In') . "</p>";
    
    if ($session->isLoggedIn()) {
        echo "<p>User ID: " . $session->getUserId() . "</p>";
        echo "<p>User Role: " . $session->getUserRole() . "</p>";
        echo "<p>Tenant ID: " . $session->getTenantId() . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Not logged in - this might cause 404 errors for protected routes</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Session error: " . $e->getMessage() . "</p>";
}

// Test 3: Test controller instantiation
echo "<h2>3. Controller Instantiation Test</h2>";

if (!isset($session) || !$session->isLoggedIn()) {
    echo "<p style='color: orange;'>⚠️ Skipping controller tests - not logged in</p>";
    echo "<p>Controllers require authentication and will redirect to login</p>";
} else {
    foreach ($controllers as $name => $class) {
        try {
            $controller = new $class();
            echo "<p style='color: green;'>✓ $name instantiated successfully</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ $name failed: " . $e->getMessage() . "</p>";
        }
    }
}

// Test 4: Check required models
echo "<h2>4. Required Models Test</h2>";
$models = [
    'Event' => 'SmartCast\\Models\\Event',
    'TenantBalance' => 'SmartCast\\Models\\TenantBalance',
    'Contestant' => 'SmartCast\\Models\\Contestant',
    'Vote' => 'SmartCast\\Models\\Vote',
    'Transaction' => 'SmartCast\\Models\\Transaction'
];

foreach ($models as $name => $class) {
    if (class_exists($class)) {
        echo "<p style='color: green;'>✓ $name model exists</p>";
        
        try {
            $model = new $class();
            echo "<p style='color: green;'>  ✓ Can instantiate</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>  ✗ Instantiation failed: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ $name model missing</p>";
    }
}

// Test 5: Check views
echo "<h2>5. View Files Test</h2>";
$views = [
    'Organizer Dashboard' => __DIR__ . '/views/organizer/dashboard.php',
    'Organizer Layout' => __DIR__ . '/views/layout/organizer_layout.php',
    'Super Admin Layout' => __DIR__ . '/views/layout/superadmin_layout.php',
    'Admin Layout' => __DIR__ . '/views/layout/admin_layout.php'
];

foreach ($views as $name => $path) {
    if (file_exists($path)) {
        echo "<p style='color: green;'>✓ $name exists</p>";
    } else {
        echo "<p style='color: red;'>✗ $name missing: $path</p>";
    }
}

// Test 6: Manual route test
echo "<h2>6. Manual Route Test</h2>";
echo "<p>Try these URLs to test the routes:</p>";
echo "<ul>";
echo "<li><a href='" . APP_URL . "/organizer' target='_blank'>Organizer Dashboard</a></li>";
echo "<li><a href='" . APP_URL . "/superadmin' target='_blank'>Super Admin Dashboard</a></li>";
echo "<li><a href='" . APP_URL . "/admin' target='_blank'>Admin Dashboard</a></li>";
echo "<li><a href='" . APP_URL . "/index.php/organizer' target='_blank'>Organizer (Alternative)</a></li>";
echo "<li><a href='" . APP_URL . "/index.php/superadmin' target='_blank'>Super Admin (Alternative)</a></li>";
echo "</ul>";

// Test 7: Create test user for login
echo "<h2>7. Test User Creation</h2>";
if (!$session->isLoggedIn()) {
    try {
        $userModel = new SmartCast\Models\User();
        $tenantModel = new SmartCast\Models\Tenant();
        
        // Check if test users exist
        $testUsers = [
            'owner@test.com' => 'owner',
            'admin@test.com' => 'platform_admin'
        ];
        
        foreach ($testUsers as $email => $role) {
            $user = $userModel->findByEmail($email);
            if (!$user) {
                // Create tenant if needed
                $tenant = $tenantModel->findAll(['email' => $email], null, 1);
                if (empty($tenant)) {
                    $tenantId = $tenantModel->create([
                        'name' => 'Test ' . ucfirst($role),
                        'email' => $email,
                        'plan' => 'basic',
                        'active' => 1,
                        'verified' => 1
                    ]);
                } else {
                    $tenantId = $tenant[0]['id'];
                }
                
                // Create user
                $userModel->createUser([
                    'tenant_id' => $tenantId,
                    'email' => $email,
                    'password' => 'password123',
                    'role' => $role
                ]);
                
                echo "<p style='color: green;'>✓ Created test user: $email (password: password123)</p>";
            } else {
                echo "<p style='color: blue;'>ℹ️ Test user exists: $email</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Test user creation failed: " . $e->getMessage() . "</p>";
    }
}

echo "<h2>8. Troubleshooting Steps</h2>";
echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
echo "<h3>If you're getting 404 errors:</h3>";
echo "<ol>";
echo "<li><strong>Login first:</strong> Use one of the test accounts created above</li>";
echo "<li><strong>Check authentication:</strong> Dashboard routes require login</li>";
echo "<li><strong>Try alternative URLs:</strong> Use /index.php/ format if mod_rewrite isn't working</li>";
echo "<li><strong>Check error logs:</strong> Look for PHP errors in your server logs</li>";
echo "</ol>";
echo "</div>";

echo "<hr>";
echo "<p><small>Test completed at " . date('Y-m-d H:i:s') . "</small></p>";
?>
