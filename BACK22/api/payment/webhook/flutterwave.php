<?php

/**
 * Flutterwave Webhook Handler
 * Processes payment notifications from Flutterwave
 */

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config/config.php';

use SmartCast\Core\Database;
use SmartCast\Services\Gateways\FlutterwaveGateway;
use SmartCast\Controllers\VoteController;

// Set headers
header('Content-Type: application/json');

// Log webhook receipt
error_log("=== FLUTTERWAVE WEBHOOK RECEIVED ===");
error_log("Time: " . date('Y-m-d H:i:s'));
error_log("Method: " . $_SERVER['REQUEST_METHOD']);

try {
    // Get raw POST data
    $rawPayload = file_get_contents('php://input');
    error_log("Raw Payload: " . $rawPayload);
    
    // Get signature from header
    $signature = $_SERVER['HTTP_X_FLUTTERWAVE_SIGNATURE'] ?? $_SERVER['HTTP_VERIF_HASH'] ?? '';
    error_log("Signature: " . $signature);
    
    if (empty($rawPayload)) {
        throw new \Exception('Empty payload received');
    }
    
    // Get Flutterwave configuration
    $db = Database::getInstance();
    $gatewayQuery = "SELECT * FROM payment_gateways WHERE provider = 'flutterwave' AND is_active = 1";
    $gateway = $db->selectOne($gatewayQuery);
    
    if (!$gateway) {
        throw new \Exception('Flutterwave gateway not configured');
    }
    
    $config = json_decode($gateway['config'], true);
    $flutterwaveGateway = new FlutterwaveGateway($config);
    
    // Verify webhook signature
    if (!empty($signature)) {
        $isValid = $flutterwaveGateway->verifyWebhookSignature($rawPayload, $signature);
        if (!$isValid) {
            error_log("Invalid webhook signature");
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid signature']);
            exit;
        }
        error_log("Webhook signature verified ✓");
    }
    
    // Process webhook
    $webhookResult = $flutterwaveGateway->processWebhook($rawPayload);
    error_log("Webhook Result: " . json_encode($webhookResult));
    
    if (!$webhookResult['success']) {
        throw new \Exception($webhookResult['message'] ?? 'Webhook processing failed');
    }
    
    // Get payment transaction by reference
    $reference = $webhookResult['reference'] ?? '';
    if (empty($reference)) {
        throw new \Exception('No reference in webhook data');
    }
    
    $paymentQuery = "SELECT * FROM payment_transactions WHERE reference = :reference";
    $paymentTransaction = $db->selectOne($paymentQuery, ['reference' => $reference]);
    
    if (!$paymentTransaction) {
        error_log("Payment transaction not found for reference: " . $reference);
        // Still return success to Flutterwave to avoid retries
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Transaction not found but acknowledged']);
        exit;
    }
    
    error_log("Found payment transaction ID: " . $paymentTransaction['id']);
    
    // Update payment transaction status
    $status = $webhookResult['status'];
    $updateQuery = "UPDATE payment_transactions SET 
                    status = :status,
                    gateway_response = :gateway_response,
                    webhook_verified = 1,
                    updated_at = NOW()
                    WHERE id = :id";
    
    $db->execute($updateQuery, [
        'id' => $paymentTransaction['id'],
        'status' => $status,
        'gateway_response' => json_encode($webhookResult)
    ]);
    
    error_log("Payment transaction updated to status: " . $status);
    
    // If payment successful, process the vote
    if ($status === 'success') {
        error_log("Processing vote for successful payment...");
        
        // Get voting transaction
        $votingTransactionId = $paymentTransaction['related_id'];
        if ($votingTransactionId) {
            $votingQuery = "SELECT * FROM transactions WHERE id = :id";
            $votingTransaction = $db->selectOne($votingQuery, ['id' => $votingTransactionId]);
            
            if ($votingTransaction) {
                // Update voting transaction status
                $updateVoteQuery = "UPDATE transactions SET status = 'success', updated_at = NOW() WHERE id = :id";
                $db->execute($updateVoteQuery, ['id' => $votingTransactionId]);
                
                error_log("Voting transaction updated to success");
                
                // Process the vote using VoteController
                $voteController = new VoteController();
                $voteResult = $voteController->processVoteFromWebhook($votingTransaction);
                
                error_log("Vote processing result: " . json_encode($voteResult));
            }
        }
    }
    
    // Return success response to Flutterwave
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Webhook processed successfully',
        'reference' => $reference,
        'status' => $status
    ]);
    
    error_log("=== FLUTTERWAVE WEBHOOK PROCESSED SUCCESSFULLY ===");
    
} catch (\Exception $e) {
    error_log("Flutterwave Webhook Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
