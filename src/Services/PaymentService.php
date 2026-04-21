<?php

namespace SmartCast\Services;

use SmartCast\Core\Database;
use SmartCast\Services\Gateways\PaystackGateway;
use SmartCast\Services\Gateways\HubtelGateway;
use SmartCast\Services\Gateways\FlutterwaveGateway;
use SmartCast\Models\Transaction;

/**
 * Payment Service
 * Manages payment processing through various gateways
 */
class PaymentService
{
    private $db;
    private $gateways = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadGateways();
    }
    
    /**
     * Initialize a mobile money payment
     * 
     * @param array $paymentData Payment information
     * @return array Payment result
     */
    public function initializeMobileMoneyPayment($paymentData)
    {
        try {
            // Validate required fields
            $required = ['amount', 'description', 'metadata'];
            
            // Phone is only required for mobile money payments
            $flutterwaveMethod = $paymentData['flutterwave_payment_method'] ?? '';
            if ($flutterwaveMethod === 'mobilemoney' || empty($flutterwaveMethod)) {
                $required[] = 'phone';
            }
            
            foreach ($required as $field) {
                if (empty($paymentData[$field])) {
                    throw new \Exception("Missing required field: {$field}");
                }
            }
            
            // Determine gateway based on payment method and country/network
            $gatewayProvider = 'hubtel'; // Default to Hubtel for Ghana
            
            // If Flutterwave payment method is explicitly specified, use Flutterwave
            if (!empty($paymentData['flutterwave_payment_method'])) {
                $gatewayProvider = 'flutterwave';
            }
            // If country is specified and not Ghana, use Flutterwave
            elseif (!empty($paymentData['country']) && $paymentData['country'] !== 'GH') {
                $gatewayProvider = 'flutterwave';
            }
            // If network is specified for international payment, use Flutterwave
            elseif (!empty($paymentData['network']) && !in_array(strtoupper($paymentData['network']), ['MTN', 'VODAFONE', 'TELECEL', 'AIRTELTIGO'])) {
                $gatewayProvider = 'flutterwave';
            }
            
            // Get appropriate gateway
            $gateway = $this->getGatewayByProvider($gatewayProvider);
            if (!$gateway) {
                // Log the missing gateway
                error_log("Payment gateway not found: {$gatewayProvider}");
                
                // If Flutterwave is not configured, throw specific error
                if ($gatewayProvider === 'flutterwave') {
                    throw new \Exception('Flutterwave payment gateway is not configured. Please configure it in SuperAdmin settings.');
                }
                
                // Fallback to any available gateway for other cases
                $gateway = $this->getGatewayByProvider('hubtel');
                if (!$gateway) {
                    throw new \Exception('No payment gateway configured');
                }
            }
            
            // Generate unique reference
            $reference = $this->generatePaymentReference();
            
            // Create payment transaction record
            $paymentTransactionId = $this->createPaymentTransaction([
                'reference' => $reference,
                'gateway_id' => $gateway['id'],
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'] ?? 'GHS',
                'payment_method' => 'mobile_money',
                'phone_number' => $paymentData['phone'],
                'email' => $paymentData['email'] ?? null,
                'customer_name' => $paymentData['customer_name'] ?? null,
                'description' => $paymentData['description'],
                'metadata' => json_encode($paymentData['metadata']),
                'tenant_id' => $paymentData['tenant_id'] ?? null,
                'related_type' => $paymentData['related_type'] ?? 'vote',
                'related_id' => $paymentData['related_id'] ?? null,
                'gateway_provider' => $gateway['provider'],
                'otp_verified' => $paymentData['otp_verified'] ?? 0,
                'otp_verification_id' => $paymentData['otp_verification_id'] ?? null
            ]);
            
            // Initialize payment with gateway
            $gatewayService = $this->getGatewayService($gateway);
            $gatewayData = [
                'amount' => $paymentData['amount'],
                'phone' => $paymentData['phone'] ?? '',
                'reference' => $reference,
                'currency' => $paymentData['currency'] ?? 'GHS',
                'email' => $paymentData['email'] ?? 'voter@smartcast.com',
                'customer_name' => $paymentData['customer_name'] ?? 'SmartCast Voter',
                'name' => $paymentData['name'] ?? $paymentData['customer_name'] ?? 'SmartCast Voter',
                'description' => $paymentData['description'],
                'callback_url' => $paymentData['callback_url'] ?? null,
                'metadata' => $paymentData['metadata'],
                // Flutterwave-specific parameters
                'flutterwave_payment_method' => $paymentData['flutterwave_payment_method'] ?? null,
                'country' => $paymentData['country'] ?? null,
                'network' => $paymentData['network'] ?? null
            ];
            
            $result = $gatewayService->initializeMobileMoneyPayment($gatewayData);
            
            if ($result['success']) {
                // Update payment transaction with gateway response
                $this->updatePaymentTransaction($paymentTransactionId, [
                    'gateway_reference' => $result['gateway_reference'],
                    'gateway_response' => json_encode($result)
                ]);
                
                return [
                    'success' => true,
                    'payment_transaction_id' => $paymentTransactionId,
                    'reference' => $reference,
                    'gateway_reference' => $result['gateway_reference'],
                    'payment_url' => $result['payment_url'] ?? null,
                    'access_code' => $result['access_code'] ?? null,
                    'provider' => $result['provider'] ?? null,
                    'charge_status' => $result['charge_status'] ?? 'pending',
                    'requires_otp' => $result['requires_otp'] ?? false,
                    'requires_approval' => $result['requires_approval'] ?? false,
                    'message' => $result['message']
                ];
            } else {
                // Update payment transaction as failed
                $this->updatePaymentTransaction($paymentTransactionId, [
                    'status' => 'failed',
                    'gateway_response' => json_encode($result)
                ]);
                
                return [
                    'success' => false,
                    'message' => $result['message'],
                    'error_code' => $result['error_code'] ?? 'PAYMENT_FAILED'
                ];
            }
            
        } catch (\Exception $e) {
            error_log("Payment initialization error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'INITIALIZATION_ERROR'
            ];
        }
    }
    
    /**
     * Initialize a card payment
     * 
     * @param array $paymentData Payment data
     * @return array Payment response
     */
    public function initializeCardPayment($paymentData)
    {
        try {
            // Validate required fields
            $required = ['amount', 'description', 'metadata'];
            foreach ($required as $field) {
                if (empty($paymentData[$field])) {
                    throw new \Exception("Missing required field: {$field}");
                }
            }
            
            // Get Hubtel gateway
            $gateway = $this->getGatewayByProvider('hubtel');
            if (!$gateway) {
                throw new \Exception('Hubtel gateway not configured');
            }
            
            // Generate unique reference
            $reference = $this->generatePaymentReference();
            
            // Create payment transaction record
            $paymentTransactionId = $this->createPaymentTransaction([
                'reference' => $reference,
                'gateway_id' => $gateway['id'],
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'] ?? 'GHS',
                'payment_method' => 'card',
                'phone_number' => $paymentData['phone'] ?? null,
                'email' => $paymentData['email'] ?? 'voter@smartcast.com',
                'customer_name' => $paymentData['customer_name'] ?? 'SmartCast Voter',
                'description' => $paymentData['description'],
                'metadata' => json_encode($paymentData['metadata']),
                'tenant_id' => $paymentData['tenant_id'] ?? null,
                'related_type' => $paymentData['related_type'] ?? 'vote',
                'related_id' => $paymentData['related_id'] ?? null,
                'gateway_provider' => 'hubtel'
            ]);
            
            // Initialize card payment with Hubtel
            $gatewayService = $this->getGatewayService($gateway);
            $gatewayData = [
                'amount' => $paymentData['amount'],
                'reference' => $reference,
                'description' => $paymentData['description'],
                'callback_url' => $paymentData['callback_url'] ?? null,
                'return_url' => $paymentData['return_url'] ?? null,
                'cancellation_url' => $paymentData['cancellation_url'] ?? null,
                'phone' => $paymentData['phone'] ?? '',
                'email' => $paymentData['email'] ?? 'voter@smartcast.com',
                'customer_name' => $paymentData['customer_name'] ?? 'SmartCast Voter'
            ];
            
            $result = $gatewayService->initializeCardPayment($gatewayData);
            
            if ($result['success']) {
                // Update payment transaction with gateway reference
                $this->updatePaymentTransaction($paymentTransactionId, [
                    'gateway_reference' => $result['gateway_reference'],
                    'status' => 'pending'
                ]);
                
                return [
                    'success' => true,
                    'payment_transaction_id' => $paymentTransactionId,
                    'reference' => $reference,
                    'gateway_reference' => $result['gateway_reference'],
                    'payment_url' => $result['payment_url'],
                    'checkout_id' => $result['checkout_id'] ?? null,
                    'provider' => 'card',
                    'charge_status' => 'pending',
                    'requires_approval' => true,
                    'message' => $result['message']
                ];
            }
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Card payment initialization error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'CARD_INITIALIZATION_ERROR'
            ];
        }
    }
    
    /**
     * Verify a payment by voting transaction ID and process vote if successful
     * 
     * @param int $votingTransactionId Voting transaction ID
     * @return array Verification result
     */
    public function verifyPaymentByVotingTransactionId($votingTransactionId)
    {
        try {
            // Debug logging
            error_log("PaymentService: Looking up payment for voting transaction ID: " . $votingTransactionId);
            
            // Get payment transaction by voting transaction ID
            $paymentTransaction = $this->getPaymentTransactionByVotingTransactionId($votingTransactionId);
            
            if (!$paymentTransaction) {
                error_log("PaymentService: Payment transaction NOT FOUND for voting transaction ID: " . $votingTransactionId);
                throw new \Exception('Payment transaction not found');
            }
            
            error_log("PaymentService: Found payment transaction ID: " . $paymentTransaction['id'] . ", gateway_reference: " . $paymentTransaction['gateway_reference']);
            
            // Get gateway and verify payment
            $gateway = $this->getGatewayById($paymentTransaction['gateway_id']);
            $gatewayService = $this->getGatewayService($gateway);
            
            $verificationResult = $gatewayService->verifyPayment($paymentTransaction['gateway_reference']);
            
            if ($verificationResult['success']) {
                $status = $verificationResult['status'];
                
                // Only update payment transaction if status is not pending
                if ($status !== 'pending') {
                    $this->updatePaymentTransaction($paymentTransaction['id'], [
                        'status' => $status,
                        'gateway_response' => json_encode($verificationResult),
                        'webhook_verified' => 1
                    ]);
                }
                
                if ($status === 'success') {
                    // Process the vote
                    $voteResult = $this->processVoteFromPayment($paymentTransaction);
                    
                    return [
                        'success' => true,
                        'status' => 'success',
                        'message' => 'Payment verified and vote processed successfully',
                        'payment_verified' => true,
                        'vote_processed' => $voteResult['success'] ?? false,
                        'vote_details' => $voteResult
                    ];
                } elseif ($status === 'pending') {
                    // Payment still pending - don't treat as failure
                    return [
                        'success' => true,
                        'status' => 'pending',
                        'message' => $verificationResult['message'] ?? 'Payment is still pending approval',
                        'payment_verified' => false,
                        'vote_processed' => false
                    ];
                } else {
                    // Other statuses (failed, expired, etc.)
                    return [
                        'success' => true,
                        'status' => $status,
                        'message' => $verificationResult['message'] ?? 'Payment verification completed',
                        'payment_verified' => true,
                        'vote_processed' => false
                    ];
                }
            } else {
                // Verification failed - treat as error, not pending
                return [
                    'success' => false,
                    'status' => 'error',
                    'message' => $verificationResult['message'],
                    'error_code' => $verificationResult['error_code']
                ];
            }
            
        } catch (\Exception $e) {
            error_log("Payment verification error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'VERIFICATION_ERROR'
            ];
        }
    }
    
    /**
     * Verify a payment and process vote if successful
     * 
     * @param string $reference Payment reference
     * @return array Verification result
     */
    public function verifyPaymentAndProcessVote($reference)
    {
        try {
            // Get payment transaction
            $paymentTransaction = $this->getPaymentTransactionByReference($reference);
            if (!$paymentTransaction) {
                throw new \Exception('Payment transaction not found');
            }
            
            // Get gateway and verify payment
            $gateway = $this->getGatewayById($paymentTransaction['gateway_id']);
            $gatewayService = $this->getGatewayService($gateway);
            
            $verificationResult = $gatewayService->verifyPayment($paymentTransaction['gateway_reference']);
            
            if ($verificationResult['success']) {
                $status = $verificationResult['status'];
                
                // Update payment transaction
                $this->updatePaymentTransaction($paymentTransaction['id'], [
                    'status' => $status,
                    'gateway_response' => json_encode($verificationResult),
                    'webhook_verified' => 1
                ]);
                
                if ($status === 'success') {
                    // Process the vote
                    $voteResult = $this->processVoteFromPayment($paymentTransaction);
                    
                    return [
                        'success' => true,
                        'status' => 'success',
                        'message' => 'Payment verified and vote processed successfully',
                        'payment_verified' => true,
                        'vote_processed' => $voteResult['success'] ?? false,
                        'vote_details' => $voteResult
                    ];
                } else {
                    return [
                        'success' => true,
                        'status' => $status,
                        'message' => 'Payment verification completed',
                        'payment_verified' => true,
                        'vote_processed' => false
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => $verificationResult['message'],
                    'error_code' => $verificationResult['error_code']
                ];
            }
            
        } catch (\Exception $e) {
            error_log("Payment verification error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'VERIFICATION_ERROR'
            ];
        }
    }
    
    /**
     * Process webhook from payment gateway
     * 
     * @param string $provider Gateway provider
     * @param array $payload Webhook payload
     * @param string $signature Webhook signature
     * @return array Processing result
     */
    public function processWebhook($provider, $payload, $signature = null)
    {
        try {
            // Log webhook
            $this->logWebhook($provider, $payload, $signature);
            
            // Get gateway by provider
            $gateway = $this->getGatewayByProvider($provider);
            if (!$gateway) {
                throw new \Exception("No gateway found for provider: {$provider}");
            }
            
            // Process webhook
            $gatewayService = $this->getGatewayService($gateway);
            $result = $gatewayService->processWebhook($payload, $signature);
            
            // If payment was confirmed, process the vote
            if ($result['success'] && $result['action'] === 'payment_confirmed') {
                $reference = $result['reference'];
                $paymentTransaction = $this->getPaymentTransactionByGatewayReference($reference);
                
                if ($paymentTransaction) {
                    $voteResult = $this->processVoteFromPayment($paymentTransaction);
                    $result['vote_processed'] = $voteResult['success'] ?? false;
                    $result['vote_details'] = $voteResult;
                }
            }
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("Webhook processing error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'WEBHOOK_ERROR'
            ];
        }
    }
    
    /**
     * Process vote from successful payment
     */
    private function processVoteFromPayment($paymentTransaction)
    {
        try {
            $metadata = json_decode($paymentTransaction['metadata'], true);
            
            if (!$metadata || !isset($metadata['transaction_id'])) {
                throw new \Exception('Invalid payment metadata for vote processing');
            }
            
            // Create voting transaction record
            $transactionModel = new Transaction();
            $transactionData = [
                'tenant_id' => $paymentTransaction['tenant_id'],
                'event_id' => $metadata['event_id'],
                'contestant_id' => $metadata['contestant_id'],
                'category_id' => $metadata['category_id'] ?? null,
                'bundle_id' => $metadata['bundle_id'] ?? null,
                'amount' => $paymentTransaction['amount'],
                'msisdn' => $paymentTransaction['phone_number'],
                'status' => 'success',
                'provider' => 'paystack',
                'provider_reference' => $paymentTransaction['gateway_reference']
            ];
            
            $transactionId = $transactionModel->createTransaction($transactionData);
            
            // Create revenue transaction for financial tracking
            $this->createRevenueTransaction(
                $transactionId,
                $paymentTransaction['tenant_id'],
                $metadata['event_id'],
                $paymentTransaction['amount']
            );
            
            // Cast the votes
            $voteModel = new \SmartCast\Models\Vote();
            $voteId = $voteModel->castVote(
                $transactionId,
                $transactionData['tenant_id'],
                $transactionData['event_id'],
                $transactionData['contestant_id'],
                $transactionData['category_id'],
                $metadata['votes'] ?? 1
            );
            
            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'vote_id' => $voteId,
                'votes_cast' => $metadata['votes'] ?? 1
            ];
            
        } catch (\Exception $e) {
            error_log("Vote processing error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'VOTE_PROCESSING_ERROR'
            ];
        }
    }
    
    /**
     * Create revenue transaction record for financial tracking
     */
    private function createRevenueTransaction($transactionId, $tenantId, $eventId, $grossAmount)
    {
        try {
            $revenueModel = new \SmartCast\Models\RevenueTransaction();
            return $revenueModel->createRevenueTransaction(
                $transactionId,
                $tenantId,
                $eventId,
                $grossAmount
            );
        } catch (\Exception $e) {
            error_log("Revenue transaction creation error: " . $e->getMessage());
            // Don't fail the whole process if revenue tracking fails
            return false;
        }
    }
    
    /**
     * Load available payment gateways
     */
    private function loadGateways()
    {
        $sql = "SELECT * FROM payment_gateways WHERE is_active = 1 ORDER BY priority ASC";
        $this->gateways = $this->db->select($sql);
    }
    
    /**
     * Get active gateway for payment method
     */
    private function getActiveGateway($paymentMethod)
    {
        foreach ($this->gateways as $gateway) {
            $supportedMethods = json_decode($gateway['supported_methods'], true);
            if (in_array($paymentMethod, $supportedMethods)) {
                return $gateway;
            }
        }
        return null;
    }
    
    /**
     * Get gateway by ID
     */
    private function getGatewayById($gatewayId)
    {
        $sql = "SELECT * FROM payment_gateways WHERE id = :id";
        return $this->db->selectOne($sql, ['id' => $gatewayId]);
    }
    
    /**
     * Get gateway by provider
     */
    private function getGatewayByProvider($provider)
    {
        $sql = "SELECT * FROM payment_gateways WHERE provider = :provider AND is_active = 1";
        return $this->db->selectOne($sql, ['provider' => $provider]);
    }
    
    /**
     * Get gateway service instance
     */
    private function getGatewayService($gateway)
    {
        $config = json_decode($gateway['config'], true);
        
        switch ($gateway['provider']) {
            case 'paystack':
                return new PaystackGateway($config);
            case 'hubtel':
                return new HubtelGateway($config);
            case 'flutterwave':
                return new FlutterwaveGateway($config);
            default:
                throw new \Exception("Unsupported gateway provider: {$gateway['provider']}");
        }
    }
    
    /**
     * Create payment transaction record
     */
    private function createPaymentTransaction($data)
    {
        $sql = "INSERT INTO payment_transactions (
            reference, gateway_id, amount, currency, payment_method, 
            phone_number, email, customer_name, description, metadata, 
            tenant_id, related_type, related_id, gateway_provider,
            otp_verified, otp_verification_id, created_at
        ) VALUES (
            :reference, :gateway_id, :amount, :currency, :payment_method,
            :phone_number, :email, :customer_name, :description, :metadata,
            :tenant_id, :related_type, :related_id, :gateway_provider,
            :otp_verified, :otp_verification_id, NOW()
        )";
        
        // Set defaults for new fields
        $data['gateway_provider'] = $data['gateway_provider'] ?? null;
        $data['otp_verified'] = $data['otp_verified'] ?? 0;
        $data['otp_verification_id'] = $data['otp_verification_id'] ?? null;
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update payment transaction
     */
    private function updatePaymentTransaction($id, $data)
    {
        $setParts = [];
        $params = ['id' => $id];
        
        foreach ($data as $key => $value) {
            $setParts[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }
        
        $sql = "UPDATE payment_transactions SET " . implode(', ', $setParts) . ", updated_at = NOW() WHERE id = :id";
        return $this->db->query($sql, $params);
    }
    
    /**
     * Get payment transaction by reference
     */
    private function getPaymentTransactionByReference($reference)
    {
        $sql = "SELECT * FROM payment_transactions WHERE reference = :reference";
        return $this->db->selectOne($sql, ['reference' => $reference]);
    }
    
    /**
     * Get payment transaction by gateway reference
     */
    private function getPaymentTransactionByGatewayReference($gatewayReference)
    {
        $sql = "SELECT * FROM payment_transactions WHERE gateway_reference = :gateway_reference";
        return $this->db->selectOne($sql, ['gateway_reference' => $gatewayReference]);
    }
    
    /**
     * Get payment transaction by voting transaction ID
     * First tries related_id column, then falls back to metadata JSON extraction
     */
    private function getPaymentTransactionByVotingTransactionId($votingTransactionId)
    {
        error_log("PaymentService: Searching for payment transaction with voting_transaction_id: " . $votingTransactionId);
        
        // First try: Use related_id column (faster and more reliable)
        // Cast both sides to ensure type matching (related_id might be stored as string or int)
        $sql = "SELECT * FROM payment_transactions WHERE related_type = 'vote' AND CAST(related_id AS CHAR) = CAST(:transaction_id AS CHAR) ORDER BY id DESC LIMIT 1";
        $result = $this->db->selectOne($sql, ['transaction_id' => $votingTransactionId]);
        
        if ($result) {
            error_log("PaymentService: Found via related_id - Payment Transaction ID: " . $result['id']);
            return $result;
        }
        
        error_log("PaymentService: Not found via related_id, trying JSON_EXTRACT...");
        
        // Fallback: Try JSON_EXTRACT from metadata (for older records)
        $sql = "SELECT * FROM payment_transactions WHERE JSON_EXTRACT(metadata, '$.transaction_id') = :transaction_id ORDER BY id DESC LIMIT 1";
        $result = $this->db->selectOne($sql, ['transaction_id' => $votingTransactionId]);
        
        if ($result) {
            error_log("PaymentService: Found via JSON_EXTRACT - Payment Transaction ID: " . $result['id']);
        } else {
            error_log("PaymentService: NOT FOUND via either method for voting_transaction_id: " . $votingTransactionId);
        }
        
        return $result;
    }
    
    /**
     * Log webhook for debugging
     */
    private function logWebhook($provider, $payload, $signature)
    {
        $sql = "INSERT INTO payment_webhook_logs (
            gateway_provider, payload, signature, ip_address, created_at
        ) VALUES (
            :provider, :payload, :signature, :ip_address, NOW()
        )";
        
        $this->db->query($sql, [
            'provider' => $provider,
            'payload' => json_encode($payload),
            'signature' => $signature,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
    
    /**
     * Generate unique payment reference
     */
    private function generatePaymentReference()
    {
        return strtolower(substr(uniqid() . bin2hex(random_bytes(4)), 0, 10));
    }
    
    /**
     * Format phone number
     */
    public function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle Ghana phone numbers
        if (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            // Convert 0XXXXXXXXX to 233XXXXXXXXX
            $phone = '233' . substr($phone, 1);
        } elseif (strlen($phone) === 9) {
            // Convert XXXXXXXXX to 233XXXXXXXXX
            $phone = '233' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Detect mobile money provider
     */
    public function detectMobileMoneyProvider($phone)
    {
        $phone = $this->formatPhoneNumber($phone);
        $prefix = substr($phone, -9, 3);
        
        $providers = [
            'mtn' => ['024', '025', '053', '054', '055', '059'],
            'vod' => ['020', '050'],
            'tgo' => ['026', '027', '056', '057']
        ];
        
        foreach ($providers as $code => $prefixes) {
            if (in_array($prefix, $prefixes)) {
                return $code;
            }
        }
        
        return 'mtn'; // Default
    }
}
