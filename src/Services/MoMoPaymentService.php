<?php

namespace SmartCast\Services;

/**
 * Mobile Money Payment Service
 * Production implementation using Paystack mobile money integration
 */
class MoMoPaymentService
{
    private $paymentService;
    
    public function __construct()
    {
        $this->paymentService = new PaymentService();
    }
    
    /**
     * Initiate a mobile money payment
     * 
     * @param array $paymentData
     * @return array Payment response
     */
    public function initiatePayment($paymentData)
    {
        // Validate required fields
        $required = ['amount', 'phone', 'description'];
        foreach ($required as $field) {
            if (empty($paymentData[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }
        
        // Format phone number
        $phone = $this->paymentService->formatPhoneNumber($paymentData['phone']);
        
        // Generate a valid email if none provided
        $email = $paymentData['email'] ?? null;
        if (empty($email)) {
            // Create a valid email from phone number
            $hash = substr(md5($phone), 0, 8);
            $email = "voter{$hash}@smartcastgh.com";
        }
        
        // Prepare payment data for PaymentService
        $paymentServiceData = [
            'amount' => $paymentData['amount'],
            'phone' => $phone,
            'currency' => $paymentData['currency'] ?? 'GHS',
            'description' => $paymentData['description'],
            'email' => $email,
            'customer_name' => $paymentData['customer_name'] ?? null,
            'callback_url' => $paymentData['callback_url'] ?? null,
            'metadata' => $paymentData['metadata'] ?? [],
            'tenant_id' => $paymentData['tenant_id'] ?? null,
            'related_type' => 'vote',
            'related_id' => $paymentData['voting_transaction_id'] ?? $paymentData['contestant_id'] ?? null
        ];
        
        // Initialize payment through PaymentService
        $result = $this->paymentService->initializeMobileMoneyPayment($paymentServiceData);
        
        if ($result['success']) {
            return [
                'success' => true,
                'transaction_id' => $result['reference'],
                'payment_reference' => $result['gateway_reference'],
                'payment_url' => $result['payment_url'] ?? null,
                'access_code' => $result['access_code'] ?? null,
                'provider' => $result['provider'] ?? null,
                'requires_approval' => $result['requires_approval'] ?? false,
                'status' => 'pending',
                'message' => $result['message'],
                'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes'))
            ];
        } else {
            return [
                'success' => false,
                'message' => $result['message'],
                'error_code' => $result['error_code'] ?? 'PAYMENT_FAILED'
            ];
        }
    }
    
    /**
     * Check payment status
     * 
     * @param string $transactionId Voting transaction ID
     * @return array Status response
     */
    public function checkPaymentStatus($transactionId)
    {
        // Validate transaction ID
        if (empty($transactionId)) {
            throw new \Exception("Transaction ID is required");
        }
        
        try {
            // Verify payment by voting transaction ID and process vote if successful
            $result = $this->paymentService->verifyPaymentByVotingTransactionId($transactionId);
            
            if ($result['success']) {
                return [
                    'transaction_id' => $transactionId,
                    'status' => $result['status'],
                    'message' => $result['message'],
                    'payment_verified' => $result['payment_verified'] ?? false,
                    'vote_processed' => $result['vote_processed'] ?? false,
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            } else {
                return [
                    'transaction_id' => $transactionId,
                    'status' => 'failed',
                    'message' => $result['message'],
                    'error_code' => $result['error_code'] ?? 'VERIFICATION_FAILED',
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }
        } catch (\Exception $e) {
            error_log("Payment status check error: " . $e->getMessage());
            return [
                'transaction_id' => $transactionId,
                'status' => 'error',
                'message' => 'Payment service temporarily unavailable',
                'error_code' => 'SERVICE_UNAVAILABLE',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
    
    /**
     * Verify payment callback
     * 
     * @param array $callbackData
     * @return array Verification result
     */
    public function verifyCallback($callbackData)
    {
        // This method is now handled by the webhook system
        // Redirect to the payment verification method
        $transactionId = $callbackData['reference'] ?? $callbackData['transaction_id'] ?? '';
        
        if (empty($transactionId)) {
            return [
                'valid' => false,
                'message' => 'Missing transaction reference',
                'error_code' => 'MISSING_REFERENCE'
            ];
        }
        
        try {
            $result = $this->paymentService->verifyPaymentAndProcessVote($transactionId);
            
            return [
                'valid' => $result['success'],
                'transaction_id' => $transactionId,
                'status' => $result['status'] ?? 'unknown',
                'message' => $result['message'] ?? 'Callback processed',
                'payment_verified' => $result['payment_verified'] ?? false,
                'vote_processed' => $result['vote_processed'] ?? false
            ];
        } catch (\Exception $e) {
            error_log("Callback verification error: " . $e->getMessage());
            return [
                'valid' => false,
                'message' => $e->getMessage(),
                'error_code' => 'CALLBACK_ERROR'
            ];
        }
    }
    
    /**
     * Get supported mobile networks
     * 
     * @return array List of supported networks
     */
    public function getSupportedNetworks()
    {
        return [
            'mtn' => [
                'name' => 'MTN Mobile Money',
                'prefixes' => ['024', '025', '053', '054', '055', '059'],
                'logo' => '/assets/images/mtn-logo.png'
            ],
            'vod' => [
                'name' => 'Vodafone Cash',
                'prefixes' => ['020', '050'],
                'logo' => '/assets/images/vodafone-logo.png'
            ],
            'tgo' => [
                'name' => 'AirtelTigo Money',
                'prefixes' => ['026', '027', '056', '057'],
                'logo' => '/assets/images/airteltigo-logo.png'
            ]
        ];
    }
    
    /**
     * Detect network from phone number
     * 
     * @param string $phone
     * @return string|null Network code
     */
    public function detectNetwork($phone)
    {
        return $this->paymentService->detectMobileMoneyProvider($phone);
    }
    
    /**
     * Format phone number to standard format
     * 
     * @param string $phone
     * @return string Formatted phone number
     */
    public function formatPhoneNumber($phone)
    {
        return $this->paymentService->formatPhoneNumber($phone);
    }
    
}
