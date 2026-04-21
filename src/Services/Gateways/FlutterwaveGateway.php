<?php

namespace SmartCast\Services\Gateways;

/**
 * Flutterwave Payment Gateway
 * Supports multi-country mobile money, cards, bank transfers, and USSD
 * 
 * Supported Countries: Ghana, Nigeria, Kenya, Uganda, Rwanda, Tanzania, Zambia, South Africa
 * Payment Methods: Mobile Money, Cards, Bank Transfer, USSD
 */
class FlutterwaveGateway implements PaymentGatewayInterface
{
    private $clientId;
    private $clientSecret;
    private $encryptionKey;
    private $webhookSecret;
    private $apiUrl;
    private $accessToken;
    private $tokenExpiry;
    
    // Supported networks per country
    private $mobileMoneyNetworks = [
        'GH' => ['MTN', 'VODAFONE', 'AIRTELTIGO'],
        'NG' => ['MTN', 'AIRTEL', 'GLO', '9MOBILE'],
        'KE' => ['MPESA', 'AIRTEL'],
        'UG' => ['MTN', 'AIRTEL'],
        'RW' => ['MTN', 'AIRTEL'],
        'TZ' => ['MPESA', 'TIGO', 'AIRTEL'],
        'ZM' => ['MTN', 'AIRTEL'],
        'ZA' => ['VODACOM']
    ];
    
    // Currency mapping
    private $currencies = [
        'GH' => 'GHS',
        'NG' => 'NGN',
        'KE' => 'KES',
        'UG' => 'UGX',
        'RW' => 'RWF',
        'TZ' => 'TZS',
        'ZM' => 'ZMW',
        'ZA' => 'ZAR'
    ];
    
    public function __construct($config)
    {
        $this->clientId = $config['client_id'] ?? '';
        $this->clientSecret = $config['client_secret'] ?? '';
        $this->encryptionKey = $config['encryption_key'] ?? '';
        $this->webhookSecret = $config['webhook_secret'] ?? '';
        $this->apiUrl = $config['api_url'] ?? 'https://api.flutterwave.com';
        
        // Use sandbox for testing
        if (!empty($config['sandbox']) && $config['sandbox'] === true) {
            $this->apiUrl = 'https://developersandbox-api.flutterwave.com';
        }
    }
    
    
    /**
     * Initialize payment (mobile money, card, bank transfer, USSD)
     * All payment methods use Flutterwave Standard API
     */
    public function initializeMobileMoneyPayment($paymentData)
    {
        try {
            $paymentMethod = $paymentData['flutterwave_payment_method'] ?? 'mobilemoney';
            
            // All payment methods use Flutterwave Standard (hosted payment page)
            // This includes mobile money, card, bank transfer, and USSD
            return $this->initializeStandardPayment($paymentData, $paymentMethod);
            
        } catch (\Exception $e) {
            error_log("Flutterwave Payment Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Initialize standard payment (card, bank transfer, USSD)
     * Uses Flutterwave Standard (hosted payment page)
     */
    private function initializeStandardPayment($paymentData, $paymentMethod)
    {
        try {
            $url = $this->apiUrl . '/v3/payments';
            
            $data = [
                'tx_ref' => $paymentData['reference'],
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'],
                'redirect_url' => $paymentData['callback_url'] ?? APP_URL . '/payment/callback',
                // Don't specify payment_options - let Flutterwave show all available options for the country
                'customer' => [
                    'email' => $paymentData['email'] ?? 'customer@smartcast.com',
                    'phonenumber' => $paymentData['phone'],
                    'name' => $paymentData['name'] ?? 'SmartCast Voter'
                ],
                'customizations' => [
                    'title' => 'SmartCast Voting',
                    'description' => $paymentData['description'] ?? 'Vote Payment',
                    'logo' => APP_URL . '/assets/images/logo.png'
                ],
                'meta' => $paymentData['metadata'] ?? []
            ];
            
            // Make API request (requireAuth = true will add Authorization header)
            $response = $this->makeRequest('POST', $url, $data, [], true);
            
            // Response structure: { status: "success", message: "...", data: { link: "..." } }
            if (isset($response['link'])) {
                return [
                    'success' => true,
                    'reference' => $paymentData['reference'],
                    'payment_url' => $response['link'],
                    'gateway_reference' => $paymentData['reference'],
                    'status' => 'pending',
                    'message' => 'Redirect to complete payment'
                ];
            }
            
            // Log the full response for debugging
            error_log("Flutterwave Standard Payment Response: " . json_encode($response));
            throw new \Exception($response['message'] ?? 'Failed to initialize payment');
            
        } catch (\Exception $e) {
            error_log("Flutterwave Standard Payment Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create customer
     */
    private function createCustomer($paymentData)
    {
        $url = $this->apiUrl . '/customers';
        
        // Extract country code from phone
        $countryCode = $this->extractCountryCode($paymentData['phone']);
        $phoneNumber = $this->extractPhoneNumber($paymentData['phone']);
        
        $data = [
            'email' => $paymentData['email'] ?? 'customer@smartcast.com',
            'name' => [
                'first' => $paymentData['name'] ?? 'SmartCast',
                'last' => 'User'
            ],
            'phone' => [
                'country_code' => $countryCode,
                'number' => $phoneNumber
            ]
        ];
        
        return $this->makeRequest('POST', $url, $data);
    }
    
    /**
     * Create mobile money payment method
     */
    private function createMobileMoneyPaymentMethod($paymentData, $customerId)
    {
        $url = $this->apiUrl . '/payment-methods';
        
        $countryCode = $this->extractCountryCode($paymentData['phone']);
        $phoneNumber = $this->extractPhoneNumber($paymentData['phone']);
        $network = strtoupper($paymentData['network'] ?? 'MTN');
        
        $data = [
            'type' => 'mobile_money',
            'mobile_money' => [
                'country_code' => $countryCode,
                'network' => $network,
                'phone_number' => $phoneNumber
            ]
        ];
        
        return $this->makeRequest('POST', $url, $data);
    }
    
    /**
     * Create charge
     */
    private function createCharge($paymentData, $customerId, $paymentMethodId)
    {
        $url = $this->apiUrl . '/charges';
        
        // Get currency - prefer explicit country over phone extraction
        $currency = $paymentData['currency'] ?? 'GHS';
        if (!empty($paymentData['country'])) {
            // Use explicitly provided country
            $currency = $this->getCurrencyByCountry($paymentData['country']);
        } elseif (!empty($paymentData['phone'])) {
            // Fallback to extracting from phone
            $countryCode = $this->extractCountryCode($paymentData['phone']);
            $currency = $this->getCurrencyByCountry($countryCode);
        }
        
        $data = [
            'customer_id' => $customerId,
            'payment_method_id' => $paymentMethodId,
            'amount' => (float) $paymentData['amount'],
            'currency' => $currency,
            'reference' => $paymentData['reference'],
            'description' => $paymentData['description'] ?? 'SmartCast Voting Payment',
            'meta' => $paymentData['metadata'] ?? []
        ];
        
        return $this->makeRequest('POST', $url, $data);
    }
    
    /**
     * Verify payment status
     */
    public function verifyPayment($reference)
    {
        try {
            // Get charge by reference
            $url = $this->apiUrl . '/charges?reference=' . urlencode($reference);
            
            $response = $this->makeRequest('GET', $url);
            
            if (isset($response['data']) && is_array($response['data']) && count($response['data']) > 0) {
                $charge = $response['data'][0];
                
                return [
                    'success' => true,
                    'status' => $charge['status'] ?? 'pending',
                    'amount' => $charge['amount'] ?? 0,
                    'currency' => $charge['currency'] ?? '',
                    'reference' => $charge['reference'] ?? $reference,
                    'charge_id' => $charge['id'] ?? '',
                    'paid_at' => $charge['paid_at'] ?? null,
                    'data' => $charge
                ];
            }
            
            return [
                'success' => false,
                'status' => 'not_found',
                'message' => 'Payment not found'
            ];
            
        } catch (\Exception $e) {
            error_log("Flutterwave Verify Error: " . $e->getMessage());
            return [
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($payload, $signature)
    {
        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Process webhook
     */
    public function processWebhook($payload, $signature = null)
    {
        try {
            $data = json_decode($payload, true);
            
            if (!isset($data['event']) || !isset($data['data'])) {
                throw new \Exception('Invalid webhook payload');
            }
            
            $event = $data['event'];
            $chargeData = $data['data'];
            
            // Handle different event types
            switch ($event) {
                case 'charge.completed':
                case 'charge.success':
                    return [
                        'success' => true,
                        'status' => 'success',
                        'reference' => $chargeData['reference'] ?? '',
                        'amount' => $chargeData['amount'] ?? 0,
                        'currency' => $chargeData['currency'] ?? '',
                        'charge_id' => $chargeData['id'] ?? '',
                        'event' => $event
                    ];
                    
                case 'charge.failed':
                    return [
                        'success' => false,
                        'status' => 'failed',
                        'reference' => $chargeData['reference'] ?? '',
                        'message' => $chargeData['processor_response'] ?? 'Payment failed',
                        'event' => $event
                    ];
                    
                default:
                    return [
                        'success' => false,
                        'status' => 'unknown_event',
                        'event' => $event
                    ];
            }
            
        } catch (\Exception $e) {
            error_log("Flutterwave Webhook Error: " . $e->getMessage());
            return [
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get supported countries
     */
    public function getSupportedCountries()
    {
        return array_keys($this->mobileMoneyNetworks);
    }
    
    /**
     * Get supported networks for a country
     */
    public function getSupportedNetworks($countryCode)
    {
        return $this->mobileMoneyNetworks[$countryCode] ?? [];
    }
    
    /**
     * Get currency by country code
     */
    private function getCurrencyByCountry($countryCode)
    {
        return $this->currencies[$countryCode] ?? 'GHS';
    }
    
    /**
     * Extract country code from phone number
     */
    private function extractCountryCode($phone)
    {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Country code mapping
        $countryCodeMap = [
            '233' => 'GH',  // Ghana
            '234' => 'NG',  // Nigeria
            '254' => 'KE',  // Kenya
            '256' => 'UG',  // Uganda
            '250' => 'RW',  // Rwanda
            '255' => 'TZ',  // Tanzania
            '260' => 'ZM',  // Zambia
            '27' => 'ZA'    // South Africa
        ];
        
        foreach ($countryCodeMap as $code => $country) {
            if (strpos($phone, $code) === 0) {
                return $code;
            }
        }
        
        // Default to Ghana
        return '233';
    }
    
    /**
     * Extract phone number without country code
     */
    private function extractPhoneNumber($phone)
    {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Remove country codes
        $countryCodes = ['233', '234', '254', '256', '250', '255', '260', '27'];
        
        foreach ($countryCodes as $code) {
            if (strpos($phone, $code) === 0) {
                return substr($phone, strlen($code));
            }
        }
        
        // Remove leading zero if present
        return ltrim($phone, '0');
    }
    
    /**
     * Make HTTP request to Flutterwave API
     */
    private function makeRequest($method, $url, $data = [], $headers = [], $requireAuth = true)
    {
        $ch = curl_init();
        
        // Use client secret as Bearer token (Flutterwave doesn't use OAuth)
        if ($requireAuth) {
            $headers[] = 'Authorization: Bearer ' . $this->clientSecret;
        }
        
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'X-Trace-Id: ' . uniqid('smartcast_', true);
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("cURL Error: " . $error);
        }
        
        $result = json_decode($response, true);
        
        // Log for debugging
        error_log("Flutterwave API Request: $method $url");
        error_log("Flutterwave API Response: " . $response);
        
        if ($httpCode >= 400) {
            throw new \Exception($result['message'] ?? 'API request failed with code ' . $httpCode);
        }
        
        return $result['data'] ?? $result;
    }
    
    /**
     * Get gateway name
     */
    public function getName()
    {
        return 'Flutterwave';
    }
    
    /**
     * Check if gateway is available
     */
    public function isAvailable()
    {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }
}
