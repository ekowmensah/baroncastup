<?php

// Load configuration first
require_once __DIR__ . '/config/config.php';

// Then load autoloader
require_once __DIR__ . '/includes/autoloader.php';

use SmartCast\Services\MoMoPaymentService;

// Test the updated MoMoPaymentService with real Paystack integration
echo "<h1>Paystack Mobile Money Integration Test</h1>\n";

try {
    $momoService = new MoMoPaymentService();
    
    echo "<h2>1. Testing Network Detection</h2>\n";
    $testPhones = [
        '0545644749' => 'MTN',
        '0201234567' => 'Vodafone', 
        '0261234567' => 'AirtelTigo'
    ];
    
    foreach ($testPhones as $phone => $expected) {
        $formatted = $momoService->formatPhoneNumber($phone);
        $network = $momoService->detectNetwork($phone);
        echo "Phone: {$phone} → Formatted: {$formatted} → Network: {$network} (Expected: {$expected})<br>\n";
    }
    
    echo "<h2>2. Testing Supported Networks</h2>\n";
    $networks = $momoService->getSupportedNetworks();
    foreach ($networks as $code => $info) {
        echo "Code: {$code} → Name: {$info['name']} → Prefixes: " . implode(', ', $info['prefixes']) . "<br>\n";
    }
    
    echo "<h2>3. Testing Payment Initialization (Demo Data)</h2>\n";
    
    // Test payment initialization with demo data
    $paymentData = [
        'amount' => 5.00,
        'phone' => '0545644749',
        'description' => 'Test vote for contestant',
        'currency' => 'GHS',
        'email' => 'test@example.com',
        'customer_name' => 'Test Voter',
        'tenant_id' => 22,
        'contestant_id' => 214,
        'metadata' => [
            'event_id' => 43,
            'contestant_id' => 214,
            'category_id' => 166,
            'votes' => 5,
            'bundle_id' => 28
        ]
    ];
    
    echo "Attempting to initialize payment...<br>\n";
    echo "Payment Data: " . json_encode($paymentData, JSON_PRETTY_PRINT) . "<br>\n";
    
    $result = $momoService->initiatePayment($paymentData);
    
    echo "<h3>Payment Initialization Result:</h3>\n";
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>\n";
    
    if ($result['success']) {
        echo "<p style='color: green;'>✅ Payment initialization successful!</p>\n";
        echo "<p>Payment URL: <a href='{$result['payment_url']}' target='_blank'>{$result['payment_url']}</a></p>\n";
        echo "<p>Reference: {$result['payment_reference']}</p>\n";
        echo "<p>Provider: {$result['provider']}</p>\n";
        
        // Test payment status check
        echo "<h2>4. Testing Payment Status Check</h2>\n";
        $statusResult = $momoService->checkPaymentStatus($result['transaction_id']);
        echo "<pre>" . json_encode($statusResult, JSON_PRETTY_PRINT) . "</pre>\n";
    } else {
        echo "<p style='color: red;'>❌ Payment initialization failed: {$result['message']}</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>\n";
    echo "<p>Stack trace:</p>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}

echo "<hr>\n";
echo "<p><strong>Integration Status:</strong> " . (class_exists('SmartCast\Services\PaymentService') ? '✅ PaymentService loaded' : '❌ PaymentService not found') . "</p>\n";
echo "<p><strong>Gateway Status:</strong> " . (class_exists('SmartCast\Services\Gateways\PaystackGateway') ? '✅ PaystackGateway loaded' : '❌ PaystackGateway not found') . "</p>\n";
echo "<p><strong>Database:</strong> " . (class_exists('SmartCast\Core\Database') ? '✅ Database available' : '❌ Database not available') . "</p>\n";
