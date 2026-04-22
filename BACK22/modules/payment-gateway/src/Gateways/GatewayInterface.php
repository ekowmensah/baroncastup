<?php

namespace PaymentGateway\Gateways;

/**
 * Payment Gateway Interface
 * All payment gateways must implement this interface
 */
interface GatewayInterface
{
    /**
     * Initialize a mobile money payment
     * 
     * @param array $data Payment data including amount, phone, reference
     * @return array Payment response with success status and details
     */
    public function initializeMobileMoneyPayment($data);
    
    /**
     * Verify a payment transaction
     * 
     * @param string $reference Transaction reference
     * @return array Verification result with payment status
     */
    public function verifyPayment($reference);
    
    /**
     * Process webhook notification from gateway
     * 
     * @param array $payload Webhook payload
     * @param string|null $signature Webhook signature for verification
     * @return array Processing result
     */
    public function processWebhook($payload, $signature = null);
    
    /**
     * Get supported networks/channels
     * 
     * @return array List of supported payment networks
     */
    public function getSupportedNetworks();
}
