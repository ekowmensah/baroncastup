<?php

/**
 * Basic Payment Example
 * 
 * This example demonstrates how to initialize a simple mobile money payment
 * using the Payment Gateway Module.
 */

// Include autoloader (adjust path as needed)
require_once __DIR__ . '/../../../vendor/autoload.php';
// OR manual autoload:
// require_once __DIR__ . '/../src/Gateways/GatewayInterface.php';
// require_once __DIR__ . '/../src/Gateways/HubtelGateway.php';

use PaymentGateway\Gateways\HubtelGateway;
use PaymentGateway\Gateways\PaystackGateway;

// Load configuration
$config = require __DIR__ . '/../config/payment_config.php';

// ============================================
// Example 1: Hubtel Mobile Money Payment
// ============================================
echo "=== Hubtel Mobile Money Payment ===\n\n";

// Initialize Hubtel gateway
$hubtelGateway = new HubtelGateway($config['hubtel']);

// Prepare payment data
$paymentData = [
    'amount' => 10.00,                    // Amount in GHS
    'phone' => '0545644749',              // Customer phone number
    'reference' => 'PAY_' . time(),       // Unique reference
    'description' => 'Payment for service',
    'customer_name' => 'John Doe',
    'email' => 'john@example.com',
    'callback_url' => 'https://yourdomain.com/payment/callback',
    'metadata' => [
        'order_id' => '12345',
        'customer_id' => '67890'
    ]
];

// Initialize payment
$result = $hubtelGateway->initializeMobileMoneyPayment($paymentData);

// Handle response
if ($result['success']) {
    echo "✅ Payment initiated successfully!\n";
    echo "Reference: " . $result['client_reference'] . "\n";
    echo "Gateway Reference: " . $result['gateway_reference'] . "\n";
    echo "Amount: GHS " . $result['amount'] . "\n";
    echo "Channel: " . $result['channel'] . "\n";
    echo "Status: " . $result['status'] . "\n";
    echo "Message: " . $result['message'] . "\n\n";
    
    // Save reference for later verification
    $paymentReference = $result['client_reference'];
    
    echo "👉 User should now approve payment on their phone via USSD (*170#)\n\n";
    
    // Wait a bit and verify payment
    echo "Waiting 10 seconds before verification...\n";
    sleep(10);
    
    // Verify payment
    echo "\n=== Verifying Payment ===\n\n";
    $verification = $hubtelGateway->verifyPayment($paymentReference);
    
    if ($verification['success']) {
        echo "Payment Status: " . $verification['status'] . "\n";
        if ($verification['status'] === 'success') {
            echo "✅ Payment completed successfully!\n";
            echo "Amount: GHS " . $verification['amount'] . "\n";
            echo "Paid at: " . $verification['paid_at'] . "\n";
        } else {
            echo "⏳ Payment still pending approval\n";
        }
    } else {
        echo "❌ Verification failed: " . $verification['message'] . "\n";
    }
} else {
    echo "❌ Payment initialization failed!\n";
    echo "Error: " . $result['message'] . "\n";
    echo "Error Code: " . ($result['error_code'] ?? 'N/A') . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n\n";

// ============================================
// Example 2: Paystack Mobile Money Payment
// ============================================
echo "=== Paystack Mobile Money Payment ===\n\n";

// Initialize Paystack gateway
$paystackGateway = new PaystackGateway($config['paystack']);

// Prepare payment data
$paystackData = [
    'amount' => 10.00,                    // Amount in GHS
    'phone' => '0545644749',              // Customer phone number
    'reference' => 'PAY_' . time(),       // Unique reference
    'description' => 'Payment for service',
    'email' => 'john@example.com',        // Required by Paystack
    'callback_url' => 'https://yourdomain.com/payment/callback',
    'metadata' => [
        'order_id' => '12345',
        'customer_id' => '67890'
    ]
];

// Initialize payment
$paystackResult = $paystackGateway->initializeMobileMoneyPayment($paystackData);

// Handle response
if ($paystackResult['success']) {
    echo "✅ Payment initialized successfully!\n";
    echo "Reference: " . $paystackResult['gateway_reference'] . "\n";
    echo "Payment URL: " . $paystackResult['payment_url'] . "\n";
    echo "Status: " . $paystackResult['status'] . "\n";
    echo "Message: " . $paystackResult['message'] . "\n\n";
    
    echo "👉 Redirect user to: " . $paystackResult['payment_url'] . "\n";
    echo "   User will complete payment on Paystack's secure page\n\n";
} else {
    echo "❌ Payment initialization failed!\n";
    echo "Error: " . $paystackResult['message'] . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n\n";

// ============================================
// Example 3: Error Handling
// ============================================
echo "=== Error Handling Example ===\n\n";

try {
    // Missing required field
    $invalidData = [
        'amount' => 10.00,
        // 'phone' => '0545644749', // Missing phone
        'reference' => 'PAY_' . time(),
    ];
    
    $result = $hubtelGateway->initializeMobileMoneyPayment($invalidData);
    
} catch (\Exception $e) {
    echo "❌ Exception caught: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n\n";

// ============================================
// Example 4: Get Supported Networks
// ============================================
echo "=== Supported Networks ===\n\n";

$networks = $hubtelGateway->getSupportedNetworks();

foreach ($networks as $code => $network) {
    echo "Network: " . $network['name'] . "\n";
    echo "Code: " . $code . "\n";
    echo "Prefixes: " . implode(', ', $network['prefixes']) . "\n";
    echo "\n";
}

echo "\n" . str_repeat("=", 50) . "\n\n";

// ============================================
// Tips and Best Practices
// ============================================
echo "=== Tips and Best Practices ===\n\n";

echo "1. Always generate unique references for each payment\n";
echo "2. Store payment references in your database\n";
echo "3. Implement webhook handlers for real-time updates\n";
echo "4. Verify payments before delivering services\n";
echo "5. Handle errors gracefully and log them\n";
echo "6. Use test mode during development\n";
echo "7. Implement timeout handling for pending payments\n";
echo "8. Provide clear feedback to users\n";
echo "9. Monitor payment gateway logs\n";
echo "10. Have a fallback gateway configured\n\n";

// ============================================
// Next Steps
// ============================================
echo "=== Next Steps ===\n\n";

echo "1. Set up webhook endpoints (see webhook_handler.php example)\n";
echo "2. Implement payment verification polling\n";
echo "3. Add OTP verification (see with_otp.php example)\n";
echo "4. Configure multi-gateway failover (see multi_gateway.php example)\n";
echo "5. Test with real credentials in test mode\n";
echo "6. Deploy to production with live credentials\n\n";

echo "✅ Example completed!\n";
