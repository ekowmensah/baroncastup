<?php

require_once __DIR__ . '/includes/autoloader.php';

use SmartCast\Services\PaymentService;
use SmartCast\Core\Database;

echo "<h1>Payment Status Debug</h1>\n";

$reference = '68efe62bb7'; // The reference from your screenshot

try {
    $db = Database::getInstance();
    
    echo "<h2>1. Database Payment Transaction Check</h2>\n";
    
    // Check payment_transactions table
    $paymentTransaction = $db->selectOne(
        "SELECT * FROM payment_transactions WHERE reference = :ref1 OR gateway_reference = :ref2", 
        ['ref1' => $reference, 'ref2' => $reference]
    );
    
    if ($paymentTransaction) {
        echo "✅ Payment transaction found:<br>\n";
        echo "<pre>" . json_encode($paymentTransaction, JSON_PRETTY_PRINT) . "</pre>\n";
        
        // Check if there's a gateway response
        if (!empty($paymentTransaction['gateway_response'])) {
            $gatewayResponse = json_decode($paymentTransaction['gateway_response'], true);
            echo "<h3>Gateway Response:</h3>\n";
            echo "<pre>" . json_encode($gatewayResponse, JSON_PRETTY_PRINT) . "</pre>\n";
        }
    } else {
        echo "❌ No payment transaction found with reference: {$reference}<br>\n";
    }
    
    echo "<h2>2. Direct Paystack Verification</h2>\n";
    
    // Get Paystack gateway config
    $gateway = $db->selectOne("SELECT * FROM payment_gateways WHERE provider = 'paystack' AND is_active = 1");
    
    if ($gateway) {
        $config = json_decode($gateway['config'], true);
        echo "✅ Paystack gateway found<br>\n";
        
        // Try to verify directly with Paystack
        $paystackUrl = "https://api.paystack.co/transaction/verify/{$reference}";
        $headers = [
            'Authorization: Bearer ' . $config['secret_key'],
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $paystackUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        echo "<h3>Paystack API Response:</h3>\n";
        echo "HTTP Code: {$httpCode}<br>\n";
        
        if ($error) {
            echo "❌ cURL Error: {$error}<br>\n";
        } else {
            $paystackData = json_decode($response, true);
            echo "<pre>" . json_encode($paystackData, JSON_PRETTY_PRINT) . "</pre>\n";
            
            if ($paystackData && isset($paystackData['data'])) {
                $status = $paystackData['data']['status'] ?? 'unknown';
                echo "<p><strong>Paystack Status: {$status}</strong></p>\n";
                
                if ($status === 'success') {
                    echo "<p style='color: green;'>✅ Payment is successful on Paystack!</p>\n";
                    
                    // Try to process the vote
                    echo "<h3>Processing Vote...</h3>\n";
                    $paymentService = new PaymentService();
                    $result = $paymentService->verifyPaymentAndProcessVote($reference);
                    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>\n";
                    
                } elseif ($status === 'pending') {
                    echo "<p style='color: orange;'>⏳ Payment is still pending on Paystack</p>\n";
                } else {
                    echo "<p style='color: red;'>❌ Payment failed on Paystack</p>\n";
                }
            }
        }
    } else {
        echo "❌ No active Paystack gateway found<br>\n";
    }
    
    echo "<h2>3. Voting Transaction Check</h2>\n";
    
    // Check if vote was recorded
    $transaction = $db->selectOne(
        "SELECT * FROM transactions WHERE provider_reference = :reference", 
        ['reference' => $reference]
    );
    
    if ($transaction) {
        echo "✅ Voting transaction found:<br>\n";
        echo "<pre>" . json_encode($transaction, JSON_PRETTY_PRINT) . "</pre>\n";
        
        // Check if votes were cast
        $votes = $db->select(
            "SELECT * FROM votes WHERE transaction_id = :transaction_id", 
            ['transaction_id' => $transaction['id']]
        );
        
        if ($votes) {
            echo "✅ Votes cast: " . count($votes) . " vote records<br>\n";
            foreach ($votes as $vote) {
                echo "- Vote ID {$vote['id']}: {$vote['quantity']} votes for contestant {$vote['contestant_id']}<br>\n";
            }
        } else {
            echo "❌ No votes found for this transaction<br>\n";
        }
    } else {
        echo "❌ No voting transaction found<br>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}

echo "<hr>\n";
echo "<p><em>Debug completed at " . date('Y-m-d H:i:s') . "</em></p>\n";
