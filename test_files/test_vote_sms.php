<?php
/**
 * Test Vote SMS Integration
 * This script tests the SMS notification after a successful vote
 */

// Load configuration first
require_once __DIR__ . '/config/config.php';

// Then load autoloader
require_once __DIR__ . '/includes/autoloader.php';

use SmartCast\Services\VoteCompletionService;
use SmartCast\Models\Transaction;

// Test configuration
$testTransactionId = 109; // Set this to a real transaction ID from your database

if (!$testTransactionId) {
    echo "❌ Please set a test transaction ID in the script\n";
    echo "   Find a successful transaction ID from your database:\n";
    echo "   SELECT id, msisdn, amount, status FROM transactions WHERE status = 'success' ORDER BY created_at DESC LIMIT 5;\n";
    exit(1);
}

try {
    echo "=== Vote SMS Integration Test ===\n\n";
    
    // Initialize services
    $voteCompletionService = new VoteCompletionService();
    $transactionModel = new Transaction();
    
    // Get the transaction
    $transaction = $transactionModel->find($testTransactionId);
    
    if (!$transaction) {
        echo "❌ Transaction not found: $testTransactionId\n";
        exit(1);
    }
    
    echo "📋 Transaction Details:\n";
    echo "   ID: {$transaction['id']}\n";
    echo "   Status: {$transaction['status']}\n";
    echo "   Amount: GH₵{$transaction['amount']}\n";
    echo "   Phone: {$transaction['msisdn']}\n";
    echo "   Created: {$transaction['created_at']}\n\n";
    
    if ($transaction['status'] !== 'success') {
        echo "⚠️  Warning: Transaction status is '{$transaction['status']}', not 'success'\n";
        echo "   SMS will still be attempted...\n\n";
    }
    
    // Test the vote completion service
    echo "🧪 Testing Vote Completion Service...\n";
    
    $result = $voteCompletionService->processVoteCompletion($testTransactionId, [
        'phone' => $transaction['msisdn'] // Explicitly pass the phone number
    ]);
    
    echo "\n📊 Results:\n";
    echo "   Success: " . ($result['success'] ? '✅ Yes' : '❌ No') . "\n";
    
    if ($result['success']) {
        echo "   Transaction ID: {$result['transaction_id']}\n";
        echo "   Vote ID: {$result['vote_id']}\n";
        echo "   Receipt Number: " . ($result['receipt_number'] ?? 'N/A') . "\n";
        echo "   SMS Sent: " . ($result['sms_sent'] ? '✅ Yes' : '❌ No') . "\n";
        
        if (isset($result['sms_details'])) {
            echo "   SMS Details:\n";
            echo "     Success: " . ($result['sms_details']['success'] ? 'Yes' : 'No') . "\n";
            if (isset($result['sms_details']['error'])) {
                echo "     Error: {$result['sms_details']['error']}\n";
            }
        }
    } else {
        echo "   Error: {$result['error']}\n";
    }
    
    echo "\n=== Test Complete ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
