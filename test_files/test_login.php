<?php
/**
 * Login Test Page
 */

// Include configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/autoloader.php';

echo "<h1>SmartCast Login Test</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>";

// Test database connection
echo "<h2>1. Database Connection Test</h2>";
try {
    $db = new SmartCast\Core\Database();
    $connection = $db->getConnection();
    echo "<p style='color: green;'>✓ Database connected successfully</p>";
    
    // Check if users table exists
    $stmt = $connection->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✓ Users table exists</p>";
        
        // Check if there are any users
        $stmt = $connection->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "<p>Total users in database: <strong>{$result['count']}</strong></p>";
        
        if ($result['count'] > 0) {
            // Show sample users (without passwords)
            $stmt = $connection->query("SELECT id, email, role, active, created_at FROM users LIMIT 5");
            $users = $stmt->fetchAll();
            
            echo "<h3>Sample Users:</h3>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>Email</th><th>Role</th><th>Active</th><th>Created</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>{$user['id']}</td>";
                echo "<td>{$user['email']}</td>";
                echo "<td>{$user['role']}</td>";
                echo "<td>" . ($user['active'] ? 'Yes' : 'No') . "</td>";
                echo "<td>{$user['created_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: red;'>✗ Users table does not exist</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test User model
echo "<h2>2. User Model Test</h2>";
try {
    $userModel = new SmartCast\Models\User();
    echo "<p style='color: green;'>✓ User model instantiated successfully</p>";
    
    // Test findByEmail method
    if (isset($users) && !empty($users)) {
        $testEmail = $users[0]['email'];
        $user = $userModel->findByEmail($testEmail);
        if ($user) {
            echo "<p style='color: green;'>✓ findByEmail() method works</p>";
            echo "<p>Found user: {$user['email']} (Role: {$user['role']})</p>";
        } else {
            echo "<p style='color: red;'>✗ findByEmail() method failed</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ User model test failed: " . $e->getMessage() . "</p>";
}

// Test AuthController
echo "<h2>3. AuthController Test</h2>";
try {
    $authController = new SmartCast\Controllers\AuthController();
    echo "<p style='color: green;'>✓ AuthController instantiated successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ AuthController test failed: " . $e->getMessage() . "</p>";
}

// Test Session
echo "<h2>4. Session Test</h2>";
try {
    $session = new SmartCast\Core\Session();
    echo "<p style='color: green;'>✓ Session class works</p>";
    echo "<p>Session ID: " . session_id() . "</p>";
    echo "<p>Is logged in: " . ($session->isLoggedIn() ? 'Yes' : 'No') . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Session test failed: " . $e->getMessage() . "</p>";
}

// Create test user if none exist
echo "<h2>5. Test User Creation</h2>";
if (isset($result) && $result['count'] == 0) {
    try {
        $userModel = new SmartCast\Models\User();
        $tenantModel = new SmartCast\Models\Tenant();
        
        // Create test tenant
        $tenantId = $tenantModel->create([
            'name' => 'Test Organization',
            'email' => 'test@smartcast.com',
            'plan' => 'basic',
            'active' => 1,
            'verified' => 1
        ]);
        
        // Create test user
        $userId = $userModel->createUser([
            'tenant_id' => $tenantId,
            'email' => 'admin@smartcast.com',
            'password' => 'password123',
            'role' => 'owner'
        ]);
        
        echo "<p style='color: green;'>✓ Test user created successfully</p>";
        echo "<p><strong>Test Login Credentials:</strong></p>";
        echo "<p>Email: admin@smartcast.com</p>";
        echo "<p>Password: password123</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Test user creation failed: " . $e->getMessage() . "</p>";
    }
}

// Test URLs
echo "<h2>6. Test URLs</h2>";
echo "<p>Try these URLs to test the login system:</p>";
echo "<ul>";
echo "<li><a href='" . APP_URL . "/login' target='_blank'>Login Page</a></li>";
echo "<li><a href='" . APP_URL . "/register' target='_blank'>Register Page</a></li>";
echo "<li><a href='" . APP_URL . "/index.php/login' target='_blank'>Login Page (Alternative)</a></li>";
echo "<li><a href='" . APP_URL . "/index.php/register' target='_blank'>Register Page (Alternative)</a></li>";
echo "</ul>";

echo "<h2>7. Quick Login Test Form</h2>";
if (isset($users) && !empty($users)) {
    echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
    echo "<h3>Quick Test Login</h3>";
    echo "<form method='POST' action='" . APP_URL . "/login' target='_blank'>";
    echo "<p><label>Email: <input type='email' name='email' value='{$users[0]['email']}' style='margin-left: 10px;'></label></p>";
    echo "<p><label>Password: <input type='password' name='password' placeholder='Enter password' style='margin-left: 10px;'></label></p>";
    echo "<p><button type='submit' style='padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px;'>Test Login</button></p>";
    echo "</form>";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>Test completed at " . date('Y-m-d H:i:s') . "</small></p>";
?>
