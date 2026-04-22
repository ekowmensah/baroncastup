<?php
/**
 * SMS Integration Example
 * 
 * This file shows how to integrate SMS notifications into your voting system.
 * You can use this as a reference for implementing SMS in your existing code.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use SmartCast\Services\SmsService;
use SmartCast\Services\VoteCompletionService;

// Example 1: Direct SMS sending after vote completion
function sendSmsAfterVote($transactionId, $phone) {
    try {
        $smsService = new SmsService();
        
        // Prepare vote data (you would get this from your database)
        $voteData = [
            'phone' => $phone,
            'nominee_name' => 'John Doe',
            'event_name' => 'Best Artist 2024',
            'category_name' => 'Male Artist',
            'vote_count' => 5,
            'amount' => 5.00,
            'receipt_number' => 'SC241014000123ABC',
            'transaction_id' => $transactionId,
            'vote_id' => 456
        ];
        
        // Send SMS
        $result = $smsService->sendVoteConfirmationSms($voteData);
        
        if ($result['success']) {
            echo "SMS sent successfully!\n";
            return true;
        } else {
            echo "SMS failed: " . ($result['error'] ?? 'Unknown error') . "\n";
            return false;
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// Example 2: Using VoteCompletionService (recommended approach)
function processVoteWithSms($transactionId, $additionalData = []) {
    try {
        $completionService = new VoteCompletionService();
        
        // Process vote completion including SMS
        $result = $completionService->processVoteCompletion($transactionId, $additionalData);
        
        if ($result['success']) {
            echo "Vote processed successfully!\n";
            echo "SMS sent: " . ($result['sms_sent'] ? 'Yes' : 'No') . "\n";
            echo "Receipt: " . ($result['receipt_number'] ?? 'N/A') . "\n";
            return true;
        } else {
            echo "Vote processing failed: " . $result['error'] . "\n";
            return false;
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// Example 3: Integration with payment webhook
function handlePaymentWebhook() {
    // Get webhook data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate webhook (implement your own validation logic)
    if (!validateWebhook($input)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid webhook']);
        return;
    }
    
    // Extract transaction ID and phone from webhook
    $transactionId = $input['transaction_id'] ?? null;
    $phone = $input['phone'] ?? $input['customer_phone'] ?? null;
    
    if (!$transactionId || !$phone) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required data']);
        return;
    }
    
    // Process vote completion with SMS
    $completionService = new VoteCompletionService();
    $result = $completionService->processVoteCompletion($transactionId, ['phone' => $phone]);
    
    // Return response
    http_response_code($result['success'] ? 200 : 500);
    echo json_encode($result);
}

// Example 4: Bulk SMS for event reminders
function sendEventReminders($eventId) {
    try {
        $smsService = new SmsService();
        
        // Get subscribers for the event (implement your own logic)
        $subscribers = getEventSubscribers($eventId);
        
        $message = "Reminder: Voting for 'Best Artist 2024' is now live! Vote for your favorite nominees now. Event ends tomorrow. Vote now!";
        
        $phones = array_column($subscribers, 'phone');
        $results = $smsService->sendBulkSms($phones, $message);
        
        $successful = count(array_filter($results, function($r) { return $r['success']; }));
        $total = count($results);
        
        echo "Bulk SMS completed: {$successful}/{$total} successful\n";
        
        return $results;
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// Example 5: Custom SMS template
function sendCustomSms($phone, $templateData) {
    try {
        $smsService = new SmsService();
        
        // Custom message template
        $template = "Hello {name}! Your vote for {nominee} in {event} has been confirmed. Votes: {votes}. Thank you!";
        
        // Replace placeholders
        $message = str_replace(
            ['{name}', '{nominee}', '{event}', '{votes}'],
            [$templateData['name'], $templateData['nominee'], $templateData['event'], $templateData['votes']],
            $template
        );
        
        // Send SMS using active gateway
        $gateway = $smsService->getActiveGateway();
        if (!$gateway) {
            throw new Exception('No active SMS gateway');
        }
        
        $result = $smsService->sendSms($gateway, $phone, $message);
        
        return $result;
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// Helper functions (implement these based on your system)
function validateWebhook($data) {
    // Implement your webhook validation logic
    // Check signatures, API keys, etc.
    return true;
}

function getEventSubscribers($eventId) {
    // Implement logic to get subscribers for an event
    return [
        ['phone' => '233200000001', 'name' => 'John Doe'],
        ['phone' => '233200000002', 'name' => 'Jane Smith'],
        // ... more subscribers
    ];
}

// Usage examples:

// 1. Send SMS after successful payment
// sendSmsAfterVote(123, '233200000000');

// 2. Process complete vote with SMS
// processVoteWithSms(123, ['phone' => '233200000000']);

// 3. Handle webhook (call this from your webhook endpoint)
// handlePaymentWebhook();

// 4. Send event reminders
// sendEventReminders(1);

// 5. Send custom SMS
// sendCustomSms('233200000000', [
//     'name' => 'John',
//     'nominee' => 'Jane Doe',
//     'event' => 'Best Artist 2024',
//     'votes' => 5
// ]);

echo "SMS Integration examples loaded. Use the functions above in your application.\n";
?>
