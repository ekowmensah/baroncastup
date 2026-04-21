<?php

namespace SmartCast\Services\Gateways;

use SmartCast\Core\Database;

/**
 * Paystack Payment Gateway Service
 * Handles mobile money, card, and bank transfer payments via Paystack API
 */
class PaystackGateway
{
    private $config;
    private $db;
    private $baseUrl;
    
    public function __construct($config)
    {
        $this->config = $config;
        $this->db = Database::getInstance();
        $this->baseUrl = $config['base_url'] ?? 'https://api.paystack.co';
    }
    
    /**
     * Initialize a payment transaction
     * 
     * @param array $data Payment data
     * @return array Payment response
     */
    public function initializePayment($data)
    {
        $required = ['amount', 'email', 'reference'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }
        
        // Prepare payment data for Paystack
        $paymentData = [
            'amount' => $data['amount'] * 100, // Convert to kobo
            'currency' => $data['currency'] ?? 'GHS',
            'email' => $data['email'],
            'reference' => $data['reference'],
            'callback_url' => $data['callback_url'] ?? null,
            'metadata' => $data['metadata'] ?? []
        ];
        
        // Add channels if specified (for mobile money)
        if (!empty($data['channels'])) {
            $paymentData['channels'] = $data['channels'];
        }
        
        // Add mobile money specific data
        if (!empty($data['mobile_money'])) {
            $paymentData['mobile_money'] = $data['mobile_money'];
        }
        
        try {
            $response = $this->makeApiCall('POST', '/transaction/initialize', $paymentData);
            
            if ($response['status'] === true) {
                return [
                    'success' => true,
                    'gateway_reference' => $response['data']['reference'],
                    'access_code' => $response['data']['access_code'],
                    'payment_url' => $response['data']['authorization_url'],
                    'status' => 'pending',
                    'message' => 'Payment initialized successfully',
                    'raw_response' => $response
                ];
            } else {
                throw new \Exception($response['message'] ?? 'Payment initialization failed');
            }
        } catch (\Exception $e) {
            error_log("Paystack initialization error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'INITIALIZATION_FAILED'
            ];
        }
    }
    
    /**
     * Initialize mobile money payment specifically
     * 
     * @param array $data Payment data with phone number
     * @return array Payment response
     */
    public function initializeMobileMoneyPayment($data)
    {
        $required = ['amount', 'phone', 'reference'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }
        
        // Format phone number
        $phone = $this->formatPhoneNumber($data['phone']);
        $provider = $this->detectMobileMoneyProvider($phone);
        
        // Prepare mobile money payment data
        $paymentData = [
            'amount' => $data['amount'] * 100, // Convert to kobo
            'currency' => $data['currency'] ?? 'GHS',
            'email' => $data['email'] ?? $this->generateEmailFromPhone($phone),
            'reference' => $data['reference'],
            'channels' => ['mobile_money'],
            'mobile_money' => [
                'phone' => $phone,
                'provider' => $provider
            ],
            'callback_url' => $data['callback_url'] ?? null,
            'metadata' => array_merge($data['metadata'] ?? [], [
                'payment_method' => 'mobile_money',
                'phone_number' => $phone,
                'provider' => $provider
            ])
        ];
        
        try {
            $response = $this->makeApiCall('POST', '/transaction/initialize', $paymentData);
            
            if ($response['status'] === true) {
                return [
                    'success' => true,
                    'gateway_reference' => $response['data']['reference'],
                    'access_code' => $response['data']['access_code'],
                    'payment_url' => $response['data']['authorization_url'],
                    'provider' => $provider,
                    'charge_status' => 'redirect_to_checkout',
                    'requires_otp' => false,
                    'status' => 'pending',
                    'message' => 'Redirecting to secure payment page for mobile money verification.',
                    'raw_response' => $response
                ];
            } else {
                throw new \Exception($response['message'] ?? 'Mobile money payment initialization failed');
            }
        } catch (\Exception $e) {
            error_log("Paystack mobile money error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'MOBILE_MONEY_FAILED'
            ];
        }
    }
    
    /**
     * Verify a payment transaction
     * 
     * @param string $reference Payment reference
     * @return array Verification result
     */
    public function verifyPayment($reference)
    {
        try {
            $response = $this->makeApiCall('GET', "/transaction/verify/{$reference}");
            
            if ($response['status'] === true) {
                $data = $response['data'];
                
                return [
                    'success' => true,
                    'reference' => $data['reference'],
                    'amount' => $data['amount'] / 100, // Convert from kobo
                    'currency' => $data['currency'],
                    'status' => $this->mapPaystackStatus($data['status']),
                    'gateway_response' => $data['gateway_response'] ?? null,
                    'paid_at' => $data['paid_at'] ?? null,
                    'channel' => $data['channel'] ?? null,
                    'fees' => isset($data['fees']) ? $data['fees'] / 100 : 0,
                    'customer' => $data['customer'] ?? null,
                    'metadata' => $data['metadata'] ?? null,
                    'raw_response' => $response
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response['message'] ?? 'Payment verification failed',
                    'error_code' => 'VERIFICATION_FAILED'
                ];
            }
        } catch (\Exception $e) {
            error_log("Paystack verification error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'VERIFICATION_ERROR'
            ];
        }
    }
    
    /**
     * Process webhook notification
     * 
     * @param array $payload Webhook payload
     * @param string $signature Webhook signature
     * @return array Processing result
     */
    public function processWebhook($payload, $signature = null)
    {
        // Verify webhook signature if provided
        if ($signature && !empty($this->config['webhook_secret'])) {
            $expectedSignature = hash_hmac('sha512', json_encode($payload), $this->config['webhook_secret']);
            if (!hash_equals($expectedSignature, $signature)) {
                return [
                    'success' => false,
                    'message' => 'Invalid webhook signature',
                    'error_code' => 'INVALID_SIGNATURE'
                ];
            }
        }
        
        try {
            $event = $payload['event'] ?? '';
            $data = $payload['data'] ?? [];
            
            switch ($event) {
                case 'charge.success':
                    return $this->handleSuccessfulCharge($data);
                    
                case 'charge.failed':
                    return $this->handleFailedCharge($data);
                    
                default:
                    return [
                        'success' => true,
                        'message' => "Event '{$event}' processed (no action required)",
                        'action' => 'ignored'
                    ];
            }
        } catch (\Exception $e) {
            error_log("Paystack webhook error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'WEBHOOK_PROCESSING_ERROR'
            ];
        }
    }
    
    /**
     * Handle successful charge webhook
     */
    private function handleSuccessfulCharge($data)
    {
        $reference = $data['reference'] ?? '';
        
        if (empty($reference)) {
            throw new \Exception('Missing reference in webhook data');
        }
        
        // Update payment transaction status
        $sql = "UPDATE payment_transactions 
                SET status = 'success', 
                    gateway_response = :gateway_response,
                    webhook_verified = 1,
                    updated_at = NOW()
                WHERE gateway_reference = :reference";
        
        $this->db->query($sql, [
            'reference' => $reference,
            'gateway_response' => json_encode($data)
        ]);
        
        return [
            'success' => true,
            'message' => 'Payment marked as successful',
            'reference' => $reference,
            'action' => 'payment_confirmed'
        ];
    }
    
    /**
     * Handle failed charge webhook
     */
    private function handleFailedCharge($data)
    {
        $reference = $data['reference'] ?? '';
        
        if (empty($reference)) {
            throw new \Exception('Missing reference in webhook data');
        }
        
        // Update payment transaction status
        $sql = "UPDATE payment_transactions 
                SET status = 'failed', 
                    gateway_response = :gateway_response,
                    webhook_verified = 1,
                    updated_at = NOW()
                WHERE gateway_reference = :reference";
        
        $this->db->query($sql, [
            'reference' => $reference,
            'gateway_response' => json_encode($data)
        ]);
        
        return [
            'success' => true,
            'message' => 'Payment marked as failed',
            'reference' => $reference,
            'action' => 'payment_failed'
        ];
    }
    
    /**
     * Make API call to Paystack
     */
    private function makeApiCall($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Authorization: Bearer ' . $this->config['secret_key'],
            'Content-Type: application/json',
            'Cache-Control: no-cache'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("cURL error: {$error}");
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $message = $decodedResponse['message'] ?? "HTTP {$httpCode} error";
            throw new \Exception($message);
        }
        
        return $decodedResponse;
    }
    
    /**
     * Format phone number for Ghana
     */
    private function formatPhoneNumber($phone)
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
     * Detect mobile money provider from phone number
     */
    private function detectMobileMoneyProvider($phone)
    {
        $phone = $this->formatPhoneNumber($phone);
        $prefix = substr($phone, -9, 3); // Get first 3 digits after country code
        
        $providers = [
            'mtn' => ['024', '025', '053', '054', '055', '059'],
            'vod' => ['020', '050'], // Vodafone
            'tgo' => ['026', '027', '056', '057'] // AirtelTigo
        ];
        
        foreach ($providers as $code => $prefixes) {
            if (in_array($prefix, $prefixes)) {
                return $code;
            }
        }
        
        return 'mtn'; // Default to MTN
    }
    
    /**
     * Generate email from phone number for Paystack requirement
     */
    private function generateEmailFromPhone($phone)
    {
        // Create a valid email format that Paystack will accept
        // Use a hash to make it shorter and more realistic
        $hash = substr(md5($phone), 0, 8);
        return "voter{$hash}@gmail.com";
    }
    
    /**
     * Map Paystack status to internal status
     */
    private function mapPaystackStatus($paystackStatus)
    {
        $statusMap = [
            'success' => 'success',
            'failed' => 'failed',
            'abandoned' => 'cancelled',
            'pending' => 'pending'
        ];
        
        return $statusMap[$paystackStatus] ?? 'pending';
    }
    
    /**
     * Get supported networks
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
}
