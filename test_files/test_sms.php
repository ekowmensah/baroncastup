<?php
/**
 * SMS Testing Script
 * Use this to test SMS functionality directly
 */

// Load configuration first
require_once __DIR__ . '/config/config.php';

// Then load autoloader
require_once __DIR__ . '/includes/autoloader.php';

use SmartCast\Services\SmsService;
use SmartCast\Models\SmsGateway;

// Test configuration
$testPhone = '233200000000'; // Replace with your test phone number
$testMessage = 'Test SMS from SmartCast voting system. Time: ' . date('Y-m-d H:i:s');

try {
    echo "=== SMS Gateway Test ===\n\n";
    
    // Initialize services
    $smsService = new SmsService();
    $smsGateway = new SmsGateway();
    
    // Get all gateways
    $gateways = $smsGateway->findAll();
    
    if (empty($gateways)) {
        echo "❌ No SMS gateways configured. Please add a gateway first.\n";
        exit(1);
    }
    
    echo "Found " . count($gateways) . " gateway(s):\n";
    foreach ($gateways as $gateway) {
        echo "- {$gateway['name']} ({$gateway['type']}) - " . 
             ($gateway['is_active'] ? 'Active' : 'Inactive') . "\n";
    }
    echo "\n";
    
    // Test each active gateway
    foreach ($gateways as $gateway) {
        if (!$gateway['is_active']) {
            echo "⏭️  Skipping inactive gateway: {$gateway['name']}\n";
            continue;
        }
        
        echo "🧪 Testing gateway: {$gateway['name']} ({$gateway['type']})\n";
        echo "   API Key: " . substr($gateway['api_key'], 0, 10) . "...\n";
        echo "   Sender ID: {$gateway['sender_id']}\n";
        echo "   Phone: $testPhone\n";
        echo "   Message: $testMessage\n\n";
        
        // Test the gateway
        $result = $smsService->sendSms($gateway, $testPhone, $testMessage);
        
        if ($result['success']) {
            echo "✅ SMS sent successfully!\n";
            echo "   HTTP Code: {$result['http_code']}\n";
            echo "   Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "❌ SMS failed!\n";
            echo "   HTTP Code: {$result['http_code']}\n";
            echo "   Error: " . ($result['error'] ?? 'Unknown error') . "\n";
            echo "   Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
            echo "   Raw Response: {$result['raw_response']}\n";
        }
        
        echo "\n" . str_repeat("-", 50) . "\n\n";
    }
    
    echo "=== Test Complete ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
