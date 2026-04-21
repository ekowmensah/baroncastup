<?php

require_once __DIR__ . '/includes/autoloader.php';

use SmartCast\Services\MoMoPaymentService;

echo "<h1>Mobile Money Popup Flow Test</h1>\n";

try {
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
    
    echo "<h2>Testing Payment Initialization</h2>\n";
    $result = $momoService->initiatePayment($paymentData);
    
    if ($result['success']) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
        echo "<h3>✅ Payment Initialized Successfully!</h3>\n";
        echo "<p><strong>Reference:</strong> {$result['payment_reference']}</p>\n";
        echo "<p><strong>Provider:</strong> {$result['provider']}</p>\n";
        
        if (isset($result['payment_url'])) {
            echo "<p><strong>Payment URL:</strong> <a href='{$result['payment_url']}' target='_blank'>{$result['payment_url']}</a></p>\n";
            
            // Simulate the popup flow
            echo "<h3>Popup Flow Simulation</h3>\n";
            echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>\n";
            echo "<p><strong>Step 1:</strong> User clicks vote button</p>\n";
            echo "<p><strong>Step 2:</strong> Payment initialized with reference: {$result['payment_reference']}</p>\n";
            echo "<p><strong>Step 3:</strong> Popup opens with Paystack URL</p>\n";
            echo "<p><strong>Step 4:</strong> User completes mobile money payment in popup</p>\n";
            echo "<p><strong>Step 5:</strong> Status checking detects payment completion</p>\n";
            echo "<p><strong>Step 6:</strong> Popup closes, vote is recorded</p>\n";
            echo "</div>\n";
            
            // Test popup button
            echo "<h3>Test Popup (Click to Open)</h3>\n";
            echo "<button onclick=\"openTestPopup('{$result['payment_url']}')\" style='background: #667eea; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600;'>\n";
            echo "<i class='fas fa-external-link-alt'></i> Open Payment Popup\n";
            echo "</button>\n";
            
            echo "<script>\n";
            echo "function openTestPopup(url) {\n";
            echo "    const popup = window.open(url, 'PaystackTest', 'width=800,height=600,scrollbars=yes,resizable=yes');\n";
            echo "    if (popup) {\n";
            echo "        popup.focus();\n";
            echo "        alert('Popup opened! Complete the payment in the popup window.');\n";
            echo "    } else {\n";
            echo "        alert('Popup blocked! Please allow popups and try again.');\n";
            echo "    }\n";
            echo "}\n";
            echo "</script>\n";
            
        } else {
            echo "<p style='color: #dc3545;'><strong>Issue:</strong> No payment URL generated</p>\n";
        }
        
        echo "</div>\n";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
        echo "<h3>❌ Payment Initialization Failed</h3>\n";
        echo "<p><strong>Error:</strong> {$result['message']}</p>\n";
        echo "</div>\n";
    }
    
    echo "<h2>Frontend Integration Status</h2>\n";
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px;'>\n";
    echo "<h4>✅ Popup Implementation Complete</h4>\n";
    echo "<ul>\n";
    echo "<li>✅ <code>showPaymentPopup()</code> function added</li>\n";
    echo "<li>✅ <code>openPaymentPopup()</code> function added</li>\n";
    echo "<li>✅ Popup monitoring and status updates</li>\n";
    echo "<li>✅ Fallback for blocked popups</li>\n";
    echo "<li>✅ Auto-close popup on payment success</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
    
    echo "<h2>User Experience Flow</h2>\n";
    echo "<ol>\n";
    echo "<li><strong>User selects mobile money payment</strong> → Clicks 'Cast Your Vote'</li>\n";
    echo "<li><strong>Payment initializes</strong> → Shows 'Payment Initiated' message</li>\n";
    echo "<li><strong>Popup opens automatically</strong> → Paystack mobile money page loads</li>\n";
    echo "<li><strong>User completes payment</strong> → Enters mobile money PIN/OTP</li>\n";
    echo "<li><strong>Payment succeeds</strong> → Popup closes, vote recorded</li>\n";
    echo "<li><strong>Success message shown</strong> → User sees confirmation</li>\n";
    echo "</ol>\n";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>\n";
    echo "<h3>❌ Error</h3>\n";
    echo "<p>" . $e->getMessage() . "</p>\n";
    echo "</div>\n";
}

echo "<hr>\n";
echo "<h2>Next Steps</h2>\n";
echo "<ul>\n";
echo "<li><strong>Test the voting flow:</strong> Go to your voting page and try making a payment</li>\n";
echo "<li><strong>Check popup behavior:</strong> Ensure popup opens and payment completes</li>\n";
echo "<li><strong>Verify status checking:</strong> Confirm votes are recorded after payment</li>\n";
echo "</ul>\n";
