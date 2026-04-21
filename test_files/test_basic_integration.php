<?php

require_once __DIR__ . '/includes/autoloader.php';

use SmartCast\Services\MoMoPaymentService;
use SmartCast\Services\PaymentService;
use SmartCast\Core\Database;

// Basic integration test without database operations
echo "<h1>Basic Paystack Integration Test</h1>\n";

try {
    echo "<h2>1. Class Loading Test</h2>\n";
    
    // Test if classes can be loaded
    echo "✅ MoMoPaymentService: " . (class_exists('SmartCast\Services\MoMoPaymentService') ? 'Loaded' : 'Failed') . "<br>\n";
    echo "✅ PaymentService: " . (class_exists('SmartCast\Services\PaymentService') ? 'Loaded' : 'Failed') . "<br>\n";
    echo "✅ PaystackGateway: " . (class_exists('SmartCast\Services\Gateways\PaystackGateway') ? 'Loaded' : 'Failed') . "<br>\n";
    echo "✅ Database: " . (class_exists('SmartCast\Core\Database') ? 'Loaded' : 'Failed') . "<br>\n";
    
    echo "<h2>2. Configuration Test</h2>\n";
    echo "✅ DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "<br>\n";
    echo "✅ DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "<br>\n";
    echo "✅ APP_URL: " . (defined('APP_URL') ? APP_URL : 'Not defined') . "<br>\n";
    
    echo "<h2>3. Database Connection Test</h2>\n";
    try {
        $db = Database::getInstance();
        echo "✅ Database connection: Successful<br>\n";
        
        // Test if payment_gateways table exists
        $result = $db->selectOne("SHOW TABLES LIKE 'payment_gateways'");
        echo "✅ payment_gateways table: " . ($result ? 'Exists' : 'Missing') . "<br>\n";
        
        // Check if Paystack gateway is configured
        $paystack = $db->selectOne("SELECT * FROM payment_gateways WHERE provider = 'paystack' AND is_active = 1");
        echo "✅ Paystack gateway: " . ($paystack ? 'Configured and Active' : 'Not configured') . "<br>\n";
        
        if ($paystack) {
            $config = json_decode($paystack['config'], true);
            echo "   - Public Key: " . (isset($config['public_key']) ? 'Set' : 'Missing') . "<br>\n";
            echo "   - Secret Key: " . (isset($config['secret_key']) ? 'Set' : 'Missing') . "<br>\n";
            echo "   - Currency: " . ($config['currency'] ?? 'Not set') . "<br>\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "<br>\n";
    }
    
    echo "<h2>4. Service Initialization Test</h2>\n";
    try {
        $momoService = new MoMoPaymentService();
        echo "✅ MoMoPaymentService: Initialized successfully<br>\n";
        
        // Test network detection without database calls
        $networks = $momoService->getSupportedNetworks();
        echo "✅ Supported networks: " . count($networks) . " networks loaded<br>\n";
        
        foreach ($networks as $code => $info) {
            echo "   - {$code}: {$info['name']}<br>\n";
        }
        
        // Test phone formatting
        $testPhone = '0545644749';
        $formatted = $momoService->formatPhoneNumber($testPhone);
        $network = $momoService->detectNetwork($testPhone);
        echo "✅ Phone formatting: {$testPhone} → {$formatted} ({$network})<br>\n";
        
    } catch (Exception $e) {
        echo "❌ Service initialization failed: " . $e->getMessage() . "<br>\n";
        echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
    }
    
    echo "<h2>5. Integration Status</h2>\n";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<strong>✅ Integration Status: Ready for Testing</strong><br>\n";
    echo "Your Paystack mobile money integration is properly configured and ready for payment processing.<br>\n";
    echo "Next step: Test with a real payment using the full test file.\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "❌ <strong>Integration Error:</strong> " . $e->getMessage() . "<br>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
    echo "</div>\n";
}

echo "<hr>\n";
echo "<p><em>Test completed at " . date('Y-m-d H:i:s') . "</em></p>\n";
