<?php

namespace SmartCast\Controllers;

use SmartCast\Models\Tenant;
use SmartCast\Models\UssdSession;
use SmartCast\Models\Event;
use SmartCast\Helpers\UssdHelper;

/**
 * USSD Controller
 * Handles incoming USSD requests from Hubtel with multi-tenant support
 */
class UssdController extends BaseController
{
    private $ussdSession;
    private $tenantModel;
    private $eventModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->ussdSession = new UssdSession();
        $this->tenantModel = new Tenant();
        $this->eventModel = new Event();
    }
    
    /**
     * Handle incoming USSD request from Hubtel
     * 
     * Hubtel sends POST/GET with:
     * - sessionId: Unique session identifier
     * - serviceCode: USSD code dialed (e.g., *920*01#)
     * - phoneNumber: User's phone number
     * - text: User input (empty for first request)
     */
    public function handleRequest()
    {
        try {
            // Log raw input for debugging
            $rawInput = file_get_contents('php://input');
            error_log("USSD Raw Input: " . $rawInput);
            
            // Hubtel sends JSON data
            $input = json_decode($rawInput, true);
            
            // If JSON parsing failed, try form data (for testing)
            if (!$input) {
                $input = $_POST ?: $_GET;
                error_log("USSD: Using form data - " . json_encode($input));
            } else {
                error_log("USSD: Parsed JSON - " . json_encode($input));
            }
            
            // Check if this is a Service Fulfillment request (has OrderId)
            if (isset($input['OrderId'])) {
                error_log("USSD: Detected Service Fulfillment request");
                return $this->handleServiceFulfillment();
            }
            
            // Otherwise, it's a Service Interaction request (normal USSD flow)
            // Get Hubtel USSD parameters (Hubtel uses capitalized keys)
            $sessionId = $input['SessionId'] ?? $input['sessionId'] ?? null;
            $serviceCode = $input['ServiceCode'] ?? $input['serviceCode'] ?? null;
            $phoneNumber = $input['Mobile'] ?? $input['phoneNumber'] ?? null;
            $text = $input['Message'] ?? $input['text'] ?? '';
            $type = $input['Type'] ?? $input['type'] ?? 'Initiation';
            
            // Log parsed parameters
            error_log("USSD Parsed - Type: {$type}, Session: {$sessionId}, Code: {$serviceCode}, Phone: {$phoneNumber}, Text: '{$text}'");
            
            // Validate required parameters
            if (!$sessionId || !$serviceCode || !$phoneNumber) {
                error_log("USSD Error: Missing required parameters");
                return $this->ussdResponse('Invalid USSD request. Please try again.', true, $sessionId);
            }
            
            // Extract tenant from service code
            $tenant = $this->getTenantFromServiceCode($serviceCode);
            
            if (!$tenant) {
                error_log("USSD Error: No tenant found for service code: {$serviceCode}");
                return $this->ussdResponse('Service not available. Please contact support.', true, $sessionId);
            }
            
            if (!$tenant['ussd_enabled']) {
                error_log("USSD Error: USSD not enabled for tenant: {$tenant['id']}");
                return $this->ussdResponse('USSD voting is currently disabled for this service.', true, $sessionId);
            }
            
            // Check if session exists
            $session = $this->ussdSession->getSession($sessionId);
            
            if (!$session) {
                // New session - show welcome and event selection
                return $this->handleNewSession($sessionId, $phoneNumber, $serviceCode, $tenant);
            }
            
            // Existing session - process user input
            $response = $this->ussdSession->processUssdInput($sessionId, $text);
            
            // Check if this is an AddToCart response (for payment)
            if (isset($response['add_to_cart']) && $response['add_to_cart']) {
                return $this->ussdAddToCartResponse($response, $sessionId);
            }
            
            return $this->ussdResponse($response['message'], $response['end'], $sessionId);
            
        } catch (\Exception $e) {
            error_log("USSD Error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return $this->ussdResponse('An error occurred. Please try again later.', true, $sessionId ?? null);
        }
    }
    
    /**
     * Handle new USSD session
     * Simplified flow: Welcome → Enter nominee shortcode → Vote
     */
    private function handleNewSession($sessionId, $phoneNumber, $serviceCode, $tenant)
    {
        // Create new session - go directly to shortcode entry
        $this->ussdSession->createSession(
            $sessionId,
            $phoneNumber,
            UssdSession::STATE_ENTER_SHORTCODE,
            [
                'service_code' => $serviceCode,
                'ussd_code' => $tenant['ussd_code'] // Store the USSD code, not specific tenant
            ]
        );
        
        // Update session with service code
        $this->ussdSession->updateSessionColumns($sessionId, [
            'service_code' => $serviceCode
        ]);
        
        // Build welcome message
        $welcomeMessage = "Welcome to SmartCast Voting!\n\nEnter nominee code to vote:";
        
        // Use custom welcome message if any tenant has one configured
        // (For shared codes, we use a generic message or the first tenant's message)
        if (!empty($tenant['ussd_welcome_message'])) {
            $customMessage = $tenant['ussd_welcome_message'];
            // Ensure it ends with prompt for nominee code
            if (stripos($customMessage, 'code') === false) {
                $welcomeMessage = $customMessage . "\n\nEnter nominee code:";
            } else {
                $welcomeMessage = $customMessage;
            }
        }
        
        error_log("USSD: New session created for USSD code {$tenant['ussd_code']}");
        
        return $this->ussdResponse($welcomeMessage, false, $sessionId);
    }
    
    /**
     * Extract tenant from service code
     * 
     * Examples:
     * *711*734# → any tenant with ussd_code = '734' (can be multiple)
     * *711*01# → any tenant with ussd_code = '01'
     * *711# → null (base code without tenant)
     * 
     * Note: For shared USSD codes, this returns the first enabled tenant.
     * The actual tenant will be determined when user enters nominee shortcode.
     */
    private function getTenantFromServiceCode($serviceCode)
    {
        // Use UssdHelper to extract tenant code dynamically
        $tenantCode = UssdHelper::extractTenantCode($serviceCode);
        
        if ($tenantCode) {
            error_log("USSD: Extracted USSD code: {$tenantCode} from service code: {$serviceCode}");
            
            // Find any enabled tenant with this USSD code
            // Multiple tenants can share the same code
            $tenants = $this->tenantModel->findAll([
                'ussd_code' => $tenantCode,
                'ussd_enabled' => 1
            ], null, 1);
            
            if (!empty($tenants)) {
                $tenant = $tenants[0];
                error_log("USSD: Found USSD code {$tenantCode} (can be shared by multiple tenants)");
                return $tenant;
            }
            
            error_log("USSD: No enabled tenant found with USSD code: {$tenantCode}");
        } else {
            error_log("USSD: Could not extract USSD code from service code: {$serviceCode}");
        }
        
        return null;
    }
    
    /**
     * Format USSD response for Hubtel
     * 
     * @param string $message Message to display to user
     * @param bool $end Whether to end the session
     * @return void
     */
    private function ussdResponse($message, $end = false, $sessionId = null)
    {
        // Hubtel Programmable Services API Response Format
        // Documentation: https://developers.hubtel.com/documentations/programmable-services
        
        $type = $end ? 'release' : 'response';  // lowercase as per Hubtel docs
        
        $response = [
            'SessionId' => $sessionId ?? $_POST['SessionId'] ?? $_GET['SessionId'] ?? '',
            'Type' => $type,
            'Message' => $message,
            'Label' => $end ? 'Goodbye' : 'Menu',
            'DataType' => $end ? 'display' : 'input',
            'FieldType' => 'text'
        ];
        
        // Add ClientState for continuation
        if (!$end) {
            $response['ClientState'] = '';
        }
        
        // Log response
        error_log("USSD Response ({$type}): " . substr($message, 0, 100) . (strlen($message) > 100 ? '...' : ''));
        error_log("USSD Response JSON: " . json_encode($response));
        
        // Set content type to JSON
        header('Content-Type: application/json; charset=utf-8');
        
        // Output JSON response and exit
        echo json_encode($response);
        exit;
    }
    
    /**
     * Format AddToCart response for Hubtel payment collection
     * Per Hubtel Programmable Services API documentation
     */
    private function ussdAddToCartResponse($data, $sessionId)
    {
        $response = [
            'SessionId' => $sessionId,
            'Type' => 'AddToCart',
            'Message' => $data['message'] ?? 'Please wait for payment prompt or Dial *170# to Approve',
            'Label' => 'Payment',
            'DataType' => 'display',
            'FieldType' => 'text',
            'Item' => $data['item']
        ];
        
        // Log response
        error_log("USSD AddToCart Response: " . json_encode($response));
        
        // Set content type to JSON
        header('Content-Type: application/json; charset=utf-8');
        
        // Output JSON response and exit
        echo json_encode($response);
        exit;
    }
    
    /**
     * Handle Service Fulfillment callback from Hubtel
     * Called after user completes payment
     */
    public function handleServiceFulfillment()
    {
        try {
            // Get JSON payload from Hubtel
            $rawInput = file_get_contents('php://input');
            error_log("USSD Service Fulfillment Input: " . $rawInput);
            
            $payload = json_decode($rawInput, true);
            
            if (!$payload) {
                error_log("USSD Fulfillment Error: Invalid JSON");
                return $this->json(['success' => false, 'message' => 'Invalid payload'], 400);
            }
            
            $sessionId = $payload['SessionId'] ?? null;
            $orderId = $payload['OrderId'] ?? null;
            $orderInfo = $payload['OrderInfo'] ?? null;
            
            if (!$sessionId || !$orderId || !$orderInfo) {
                error_log("USSD Fulfillment Error: Missing required fields");
                return $this->json(['success' => false, 'message' => 'Missing required fields'], 400);
            }
            
            // Check payment status
            $paymentStatus = $orderInfo['Status'] ?? null;
            $paymentInfo = $orderInfo['Payment'] ?? null;
            
            if ($paymentStatus !== 'Paid' || !$paymentInfo || !$paymentInfo['IsSuccessful']) {
                error_log("USSD Fulfillment: Payment not successful - Status: {$paymentStatus}");
                return $this->json(['success' => false, 'message' => 'Payment not successful'], 400);
            }
            
            // Get session data
            $session = $this->ussdSession->getSession($sessionId);
            if (!$session) {
                error_log("USSD Fulfillment Error: Session not found - {$sessionId}");
                return $this->json(['success' => false, 'message' => 'Session not found'], 404);
            }
            
            $sessionData = $session['data'];
            $transactionId = $sessionData['transaction_id'] ?? null;
            $voteCount = $sessionData['vote_count'] ?? null;
            
            if (!$transactionId) {
                error_log("USSD Fulfillment Error: Transaction ID not found in session");
                return $this->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }
            
            // Process the vote
            $result = $this->processVoteFulfillment($transactionId, $orderId, $orderInfo, $voteCount);
            
            if ($result['success']) {
                error_log("USSD Fulfillment: Vote processed successfully - Transaction: {$transactionId}");
                
                // Optional: Try to send callback to Hubtel (not required by API)
                // This may fail if Hubtel doesn't use this endpoint
                $callbackSent = $this->sendFulfillmentCallback($sessionId, $orderId, 'success');
                if (!$callbackSent) {
                    error_log("USSD Fulfillment: Callback to Hubtel failed, but service was completed successfully");
                }
                
                // Return success response to Hubtel (this is what they actually need)
                return $this->json([
                    'success' => true,
                    'message' => 'Vote processed successfully',
                    'transaction_id' => $transactionId,
                    'votes_cast' => $result['votes_cast']
                ]);
            } else {
                error_log("USSD Fulfillment Error: Vote processing failed - " . $result['message']);
                
                // Optional: Try to send failure callback
                $this->sendFulfillmentCallback($sessionId, $orderId, 'failed', $result['message']);
                
                // Return error response to Hubtel
                return $this->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
            
        } catch (\Exception $e) {
            error_log("USSD Fulfillment Exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            return $this->json([
                'success' => false,
                'message' => 'Fulfillment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Process vote after successful payment
     */
    private function processVoteFulfillment($transactionId, $orderId, $orderInfo, $voteCountFromSession = null)
    {
        try {
            $transactionModel = new \SmartCast\Models\Transaction();
            $voteModel = new \SmartCast\Models\Vote();
            
            // Get transaction
            $transaction = $transactionModel->find($transactionId);
            if (!$transaction) {
                throw new \Exception('Transaction not found');
            }
            
            // Update transaction with payment info
            $transactionModel->update($transactionId, [
                'status' => 'success',
                'provider_reference' => $orderId,
                'payment_details' => json_encode($orderInfo)
            ]);
            
            // Get vote count - same logic as web voting
            $bundleModel = new \SmartCast\Models\VoteBundle();
            $bundle = $bundleModel->find($transaction['bundle_id']);
            
            if (!$bundle) {
                throw new \Exception('Bundle not found');
            }
            
            // Check if transaction amount matches bundle price
            if (abs($transaction['amount'] - $bundle['price']) < 0.01) {
                // Amount matches bundle price - this is a regular bundle purchase
                $voteCount = $bundle['votes'];
                error_log("USSD: Bundle purchase - using bundle votes: {$voteCount}");
            } else {
                // Amount doesn't match bundle price - this is a custom vote using bundle as reference
                // Prioritize session vote count, then calculate from amount
                if ($voteCountFromSession) {
                    $voteCount = $voteCountFromSession;
                    error_log("USSD: Custom vote - using session vote count: {$voteCount}");
                } else {
                    $eventModel = new \SmartCast\Models\Event();
                    $event = $eventModel->find($transaction['event_id']);
                    $votePrice = $event['vote_price'] ?? 0.50;
                    $voteCount = (int) ($transaction['amount'] / $votePrice);
                    error_log("USSD: Custom vote - calculated from amount: {$voteCount} (amount: {$transaction['amount']}, price: {$votePrice})");
                }
            }
            
            // Create revenue transaction for financial tracking
            $revenueModel = new \SmartCast\Models\RevenueTransaction();
            $revenueModel->createRevenueTransaction(
                $transactionId,
                $transaction['tenant_id'],
                $transaction['event_id'],
                $transaction['amount']
            );
            
            // Cast the votes
            $voteId = $voteModel->castVote(
                $transactionId,
                $transaction['tenant_id'],
                $transaction['event_id'],
                $transaction['contestant_id'],
                $transaction['category_id'],
                $voteCount
            );
            
            error_log("USSD: Vote cast successfully - Vote ID: {$voteId}, Count: {$voteCount}");
            
            // ✅ SEND SMS NOTIFICATION - Same as web voting
            error_log("USSD: Sending SMS notification for transaction: {$transactionId}");
            try {
                $voteCompletionService = new \SmartCast\Services\VoteCompletionService();
                $smsResult = $voteCompletionService->processVoteCompletion($transactionId, [
                    'phone' => $transaction['msisdn'] ?? $transaction['phone'] ?? null
                ]);
                error_log("USSD: SMS notification result: " . json_encode($smsResult));
            } catch (\Exception $e) {
                error_log("USSD: SMS notification failed: " . $e->getMessage());
                error_log("USSD: SMS error trace: " . $e->getTraceAsString());
                // Don't throw - SMS failure shouldn't break the vote process
            }
            
            return [
                'success' => true,
                'vote_id' => $voteId,
                'votes_cast' => $voteCount
            ];
            
        } catch (\Exception $e) {
            error_log("USSD Vote Processing Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send fulfillment callback to Hubtel
     * Per Hubtel documentation: https://gs-callback.hubtel.com/callback
     */
    private function sendFulfillmentCallback($sessionId, $orderId, $status, $metadata = null)
    {
        try {
            $callbackUrl = 'https://gs-callback.hubtel.com/callback';
            
            $payload = [
                'SessionId' => $sessionId,
                'OrderId' => $orderId,
                'ServiceStatus' => $status, // 'success' or 'failed'
                'MetaData' => $metadata
            ];
            
            error_log("USSD: Sending fulfillment callback to Hubtel - " . json_encode($payload));
            
            $ch = curl_init($callbackUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlInfo = curl_getinfo($ch);
            curl_close($ch);
            
            if ($curlError) {
                error_log("USSD: Fulfillment callback CURL error - " . $curlError);
                error_log("USSD: CURL info - " . json_encode($curlInfo));
            }
            
            error_log("USSD: Fulfillment callback response - HTTP {$httpCode}: {$response}");
            
            if ($httpCode >= 200 && $httpCode < 300) {
                error_log("USSD: Fulfillment callback sent successfully");
                return true;
            } else {
                error_log("USSD: Fulfillment callback failed with HTTP {$httpCode}");
                return false;
            }
            
        } catch (\Exception $e) {
            error_log("USSD: Fulfillment callback failed - " . $e->getMessage());
            return false;
        }
    }
}
