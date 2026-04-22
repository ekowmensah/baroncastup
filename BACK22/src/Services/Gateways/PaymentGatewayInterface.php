<?php

namespace SmartCast\Services\Gateways;

/**
 * Payment Gateway Interface
 * All payment gateways must implement this interface
 */
interface PaymentGatewayInterface
{
    /**
     * Initialize a mobile money payment
     * 
     * @param array $paymentData Payment information
     * @return array Payment result with success status and details
     */
    public function initializeMobileMoneyPayment($paymentData);
    
    /**
     * Verify payment status
     * 
     * @param string $reference Payment reference
     * @return array Verification result
     */
    public function verifyPayment($reference);
    
    /**
     * Process webhook notification
     * 
     * @param array $payload Webhook payload
     * @param string|null $signature Webhook signature for verification
     * @return array Processing result
     */
    public function processWebhook($payload, $signature = null);
    
    /**
     * Get gateway name
     * 
     * @return string Gateway name
     */
    public function getName();
    
    /**
     * Check if gateway is available
     * 
     * @return bool True if gateway is properly configured
     */
    public function isAvailable();
}
