<?php

require_once __DIR__ . '/includes/autoloader.php';

use SmartCast\Services\MoMoPaymentService;

echo "<h1>Mobile Money Flow Fix</h1>\n";

try {
    echo "<h2>Testing Mobile Money Payment Initialization</h2>\n";
    
    $momoService = new MoMoPaymentService();
    
    // Test payment data
    $paymentData = [
        'amount' => 3.00,
        'phone' => '0545644749',
        'description' => 'Test vote for JOHN DOE - 3 vote(s)',
        'currency' => 'GHS',
        'metadata' => [
            'event_id' => 43,
            'contestant_id' => 213,
            'category_id' => 166,
            'votes' => 3,
            'bundle_id' => 31
        ],
        'tenant_id' => 22
    ];
    
    echo "Initializing payment...<br>\n";
    $result = $momoService->initiatePayment($paymentData);
    
    echo "<h3>Payment Initialization Result:</h3>\n";
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>\n";
    
    if ($result['success']) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
        echo "<h3>✅ Payment Initialized Successfully!</h3>\n";
        echo "<p><strong>Reference:</strong> {$result['payment_reference']}</p>\n";
        echo "<p><strong>Provider:</strong> {$result['provider']}</p>\n";
        
        if (isset($result['payment_url'])) {
            echo "<p><strong>Payment URL:</strong> <a href='{$result['payment_url']}' target='_blank' style='color: #007bff;'>{$result['payment_url']}</a></p>\n";
            echo "<p style='color: #28a745;'><strong>Next Step:</strong> User should be redirected to this URL to complete mobile money payment</p>\n";
        } else {
            echo "<p style='color: #dc3545;'><strong>Issue:</strong> No payment URL generated - this is why user got stuck!</p>\n";
        }
        
        echo "</div>\n";
        
        // Show what the frontend should do
        echo "<h3>Frontend Implementation:</h3>\n";
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>\n";
        echo "<pre>\n";
        echo "// Instead of polling for status immediately:\n";
        echo "if (result.success && result.payment_url) {\n";
        echo "    // Redirect user to Paystack for mobile money verification\n";
        echo "    window.location.href = result.payment_url;\n";
        echo "} else {\n";
        echo "    // Show error message\n";
        echo "    showError(result.message);\n";
        echo "}\n";
        echo "</pre>\n";
        echo "</div>\n";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
        echo "<h3>❌ Payment Initialization Failed</h3>\n";
        echo "<p><strong>Error:</strong> {$result['message']}</p>\n";
        echo "<p><strong>Error Code:</strong> " . ($result['error_code'] ?? 'Unknown') . "</p>\n";
        echo "</div>\n";
    }
    
    echo "<h2>Mobile Money Channel Verification</h2>\n";
    
    // Check if the issue is in the Paystack gateway configuration
    echo "<p>The previous payment showed 'card' channel instead of 'mobile_money'.</p>\n";
    echo "<p>This suggests the mobile money initialization needs to be fixed.</p>\n";
    
    echo "<h3>Expected Flow:</h3>\n";
    echo "<ol>\n";
    echo "<li>User selects mobile money payment</li>\n";
    echo "<li>System calls Paystack with channels: ['mobile_money']</li>\n";
    echo "<li>Paystack returns checkout URL</li>\n";
    echo "<li>User redirected to Paystack mobile money page</li>\n";
    echo "<li>User enters mobile money PIN/OTP</li>\n";
    echo "<li>Payment completes, webhook processes vote</li>\n";
    echo "</ol>\n";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<h3>❌ Error</h3>\n";
    echo "<p>" . $e->getMessage() . "</p>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
    echo "</div>\n";
}

echo "<hr>\n";
echo "<h2>Recommendations:</h2>\n";
echo "<ul>\n";
echo "<li><strong>Fix Frontend:</strong> Redirect user to payment_url instead of polling status</li>\n";
echo "<li><strong>Verify Channels:</strong> Ensure Paystack receives channels: ['mobile_money']</li>\n";
echo "<li><strong>Test Flow:</strong> Complete end-to-end mobile money payment</li>\n";
echo "</ul>\n";
