<?php

require_once __DIR__ . '/../../includes/autoloader.php';

use SmartCast\Services\PaymentService;

// Don't set JSON header initially - will be set conditionally

try {
    // Get the request method and provider
    $method = $_SERVER['REQUEST_METHOD'];
    $provider = $_GET['provider'] ?? 'paystack';
    
    // Determine provider from URL path if not in query string
    if (strpos($_SERVER['REQUEST_URI'], '/webhook/hubtel') !== false) {
        $provider = 'hubtel';
    } elseif (strpos($_SERVER['REQUEST_URI'], '/webhook/paystack') !== false) {
        $provider = 'paystack';
    }
    
    if ($method === 'GET') {
        // Handle GET redirect from Paystack (callback)
        if (isset($_GET['trxref']) || isset($_GET['reference'])) {
            $reference = $_GET['reference'] ?? $_GET['trxref'];
            
            // Verify payment and process vote
            try {
                $paymentService = new PaymentService();
                $verificationResult = $paymentService->verifyPaymentAndProcessVote($reference);
                
                // Set HTML content type for popup
                header('Content-Type: text/html; charset=utf-8');
                
                // Generate popup close script
                $data = [
                    'success' => $verificationResult['success'],
                    'status' => $verificationResult['success'] ? 'success' : 'failed',
                    'message' => $verificationResult['success'] ? 'Payment completed successfully!' : 'Payment verification failed',
                    'receipt_number' => $verificationResult['receipt_number'] ?? null,
                    'amount' => $verificationResult['amount'] ?? null,
                    'votes_cast' => $verificationResult['votes_cast'] ?? null
                ];
                
                echo generatePopupCloseScript($data);
                exit;
                
            } catch (\Exception $e) {
                error_log("Payment verification error: " . $e->getMessage());
                header('Content-Type: text/html; charset=utf-8');
                echo generatePopupCloseScript([
                    'success' => false,
                    'status' => 'error',
                    'message' => 'Payment verification error: ' . $e->getMessage()
                ]);
                exit;
            }
        } else {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'Missing payment reference']);
            exit;
        }
    }
    
    if ($method !== 'POST') {
        header('Content-Type: application/json');
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    
    // Set JSON header for POST webhook processing
    header('Content-Type: application/json');
    
    // Get the raw POST data
    $input = file_get_contents('php://input');
    $payload = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON payload']);
        exit;
    }
    
    // Get signature from headers
    $signature = null;
    if ($provider === 'paystack') {
        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? null;
    }
    
    // Log the webhook for debugging
    error_log("Webhook received - Provider: {$provider}, Event: " . ($payload['event'] ?? 'unknown'));
    error_log("Webhook payload: " . $input);
    
    // Process the webhook
    $paymentService = new PaymentService();
    $result = $paymentService->processWebhook($provider, $payload, $signature);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => $result['message'],
            'action' => $result['action'] ?? 'processed'
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => $result['message'],
            'error_code' => $result['error_code'] ?? 'WEBHOOK_ERROR'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Webhook processing exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error',
        'error_code' => 'INTERNAL_ERROR'
    ]);
}

/**
 * Generate script to close popup and communicate with parent window
 */
function generatePopupCloseScript($data)
{
    $jsonData = json_encode($data);
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <title>Payment Complete</title>
    </head>
    <body>
        <div style='text-align: center; padding: 20px; font-family: Arial, sans-serif;'>
            <h3>" . ($data['success'] ? '✅ Payment Successful!' : '❌ Payment Failed') . "</h3>
            <p>" . htmlspecialchars($data['message']) . "</p>
            <p><small>This window will close automatically...</small></p>
        </div>
        <script>
            // Send result to parent window
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage({
                    type: 'PAYMENT_COMPLETE',
                    data: {$jsonData}
                }, '*');
            }
            
            // Close popup after a short delay
            setTimeout(function() {
                window.close();
            }, 2000);
        </script>
    </body>
    </html>";
}
