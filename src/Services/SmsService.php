<?php

namespace SmartCast\Services;

use SmartCast\Models\SmsLog;
use SmartCast\Models\SmsGateway;

class SmsService
{
    private $smsLog;
    private $smsGateway;
    private $config;
    
    public function __construct()
    {
        $this->smsLog = new SmsLog();
        $this->smsGateway = new SmsGateway();
        $this->config = $this->loadConfig();
    }
    
    /**
     * Send SMS after successful vote
     */
    public function sendVoteConfirmationSms($voteData)
    {
        try {
            // Get active gateway
            $gateway = $this->getActiveGateway();
            if (!$gateway) {
                throw new \Exception('No active SMS gateway configured');
            }
            
            // Format SMS message
            $message = $this->formatVoteConfirmationMessage($voteData);
            
            // Send SMS based on gateway type
            $result = $this->sendSmsViaGateway($gateway, $voteData['phone'], $message);
            
            // Log SMS attempt
            $this->logSms([
                'phone' => $voteData['phone'],
                'message' => $message,
                'gateway_id' => $gateway['id'],
                'gateway_type' => $gateway['type'],
                'status' => $result['success'] ? 'sent' : 'failed',
                'response' => json_encode($result),
                'vote_id' => $voteData['vote_id'] ?? null,
                'transaction_id' => $voteData['transaction_id'] ?? null
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("SMS Service Error: " . $e->getMessage());
            
            // Log failed attempt
            $this->logSms([
                'phone' => $voteData['phone'] ?? 'unknown',
                'message' => $message ?? 'Message generation failed',
                'gateway_id' => $gateway['id'] ?? null,
                'gateway_type' => $gateway['type'] ?? 'unknown',
                'status' => 'failed',
                'response' => json_encode(['error' => $e->getMessage()]),
                'vote_id' => $voteData['vote_id'] ?? null,
                'transaction_id' => $voteData['transaction_id'] ?? null
            ]);
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Send SMS directly (for testing and manual sending)
     */
    public function sendSms($gateway, $phone, $message, $voteData = null)
    {
        try {
            $result = $this->sendSmsViaGateway($gateway, $phone, $message);
            
            // Log the SMS attempt
            $this->logSms([
                'phone' => $phone,
                'message' => $message,
                'gateway_id' => $gateway['id'],
                'gateway_type' => $gateway['type'],
                'status' => $result['success'] ? 'sent' : 'failed',
                'response' => json_encode($result['response']),
                'vote_id' => $voteData['vote_id'] ?? null,
                'transaction_id' => $voteData['transaction_id'] ?? null
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            error_log("SMS Service Error: " . $e->getMessage());
            
            // Log failed attempt
            $this->logSms([
                'phone' => $phone,
                'message' => $message,
                'gateway_id' => $gateway['id'] ?? null,
                'gateway_type' => $gateway['type'] ?? 'unknown',
                'status' => 'failed',
                'response' => json_encode(['error' => $e->getMessage()]),
                'vote_id' => $voteData['vote_id'] ?? null,
                'transaction_id' => $voteData['transaction_id'] ?? null
            ]);
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Send SMS via appropriate gateway (internal method)
     */
    private function sendSmsViaGateway($gateway, $phone, $message)
    {
        switch ($gateway['type']) {
            case 'mnotify':
                return $this->sendViaMNotify($gateway, $phone, $message);
                
            case 'hubtel':
                return $this->sendViaHubtel($gateway, $phone, $message);
                
            default:
                throw new \Exception('Unsupported gateway type: ' . $gateway['type']);
        }
    }
    
    /**
     * Send SMS via mNotify gateway
     */
    private function sendViaMNotify($gateway, $phone, $message)
    {
        // mNotify API endpoint for sending SMS
        $url = 'https://api.mnotify.com/api/sms/quick?key=' . $gateway['api_key'];
        
        $data = [
            'recipient' => [$this->formatPhoneNumber($phone)],
            'sender' => $gateway['sender_id'],
            'message' => $message,
            'is_schedule' => false,
            'schedule_date' => ''
        ];
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        return $this->makeHttpRequest($url, $data, $headers, 'POST');
    }
    
    /**
     * Send SMS via Hubtel gateway
     */
    private function sendViaHubtel($gateway, $phone, $message)
    {
        // Hubtel SMS API endpoint
        $url = 'https://sms.hubtel.com/v1/messages/send';
        
        $data = [
            'From' => $gateway['sender_id'],
            'To' => $this->formatPhoneNumber($phone),
            'Content' => $message,
            'ClientReference' => 'smartcast_' . time(),
            'RegisteredDelivery' => true
        ];
        
        $headers = [
            'Authorization: Basic ' . base64_encode($gateway['client_id'] . ':' . $gateway['client_secret']),
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        return $this->makeHttpRequest($url, $data, $headers, 'POST');
    }
    
    /**
     * Make HTTP request to SMS gateway
     */
    private function makeHttpRequest($url, $data, $headers, $method = 'POST')
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_VERBOSE => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('cURL Error: ' . $error);
        }
        
        $responseData = json_decode($response, true);
        
        // Log the response for debugging
        error_log("SMS API Response - HTTP Code: $httpCode, Response: " . $response);
        
        return [
            'success' => $this->isSuccessResponse($httpCode, $responseData),
            'http_code' => $httpCode,
            'response' => $responseData,
            'raw_response' => $response
        ];
    }
    
    /**
     * Determine if response indicates success
     */
    private function isSuccessResponse($httpCode, $responseData)
    {
        // HTTP success codes
        if ($httpCode < 200 || $httpCode >= 300) {
            return false;
        }
        
        // Check response data for success indicators
        if (is_array($responseData)) {
            // mNotify success indicators
            if (isset($responseData['status']) && $responseData['status'] === 'success') {
                return true;
            }
            
            // Hubtel success indicators
            if (isset($responseData['Status']) && $responseData['Status'] === 0) {
                return true;
            }
            
            // Generic success indicators
            if (isset($responseData['success']) && $responseData['success'] === true) {
                return true;
            }
        }
        
        // Default to HTTP code success
        return true;
    }
    
    /**
     * Format vote confirmation message
     */
    private function formatVoteConfirmationMessage($voteData)
    {
        $template = $this->config['vote_confirmation_template'] ?? $this->getDefaultTemplate();
        
        $placeholders = [
            '{nominee_name}' => $voteData['nominee_name'],
            '{event_name}' => $voteData['event_name'],
            '{category_name}' => $voteData['category_name'],
            '{vote_count}' => $voteData['vote_count'],
            '{receipt_number}' => $voteData['receipt_number'],
            '{amount}' => 'GH₵' . number_format($voteData['amount'], 2),
            '{date}' => date('M j, Y H:i')
        ];
        
        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }
    
    /**
     * Get default SMS template
     */
    private function getDefaultTemplate()
    {
        return "Thank you for voting!\n\n" .
               "Nominee: {nominee_name}\n" .
               "Event: {event_name}\n" .
               "Category: {category_name}\n" .
               "Votes: {vote_count}\n" .
               "Amount: {amount}\n" .
               "Receipt: {receipt_number}\n\n" .
               "Thank you for your participation!";
    }
    
    /**
     * Format phone number for international format
     */
    private function formatPhoneNumber($phone)
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Handle Ghana phone numbers
        if (strlen($phone) == 10 && substr($phone, 0, 1) == '0') {
            return '+233' . substr($phone, 1);
        }
        
        // If already in international format
        if (strlen($phone) == 12 && substr($phone, 0, 3) == '+233') {
            return $phone;
        }
        
        // Default: assume it needs Ghana country code
        if (strlen($phone) == 9) {
            return '+233' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Get active SMS gateway
     */
    public function getActiveGateway()
    {
        return $this->smsGateway->getActiveGateway();
    }
    
    /**
     * Log SMS attempt
     */
    private function logSms($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->smsLog->create($data);
    }
    
    /**
     * Load SMS configuration
     */
    private function loadConfig()
    {
        // This could be loaded from database or config file
        return [
            'vote_confirmation_template' => null, // Will use default if null
            'retry_attempts' => 3,
            'retry_delay' => 5, // seconds
            'max_message_length' => 320
        ];
    }
    
    /**
     * Send bulk SMS (for future use)
     */
    public function sendBulkSms($recipients, $message, $gatewayType = null)
    {
        $gateway = $gatewayType ? 
            $this->smsGateway->getGatewayByType($gatewayType) : 
            $this->getActiveGateway();
            
        if (!$gateway) {
            throw new \Exception('No suitable gateway found');
        }
        
        $results = [];
        
        foreach ($recipients as $phone) {
            try {
                $result = $this->sendSms($gateway, $phone, $message);
                $results[] = [
                    'phone' => $phone,
                    'success' => $result['success'],
                    'response' => $result
                ];
                
                // Log each SMS
                $this->logSms([
                    'phone' => $phone,
                    'message' => $message,
                    'gateway_id' => $gateway['id'],
                    'gateway_type' => $gateway['type'],
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'response' => json_encode($result)
                ]);
                
                // Small delay between messages
                usleep(100000); // 0.1 second
                
            } catch (\Exception $e) {
                $results[] = [
                    'phone' => $phone,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Test gateway connection
     */
    public function testGateway($gatewayId, $testPhone = null)
    {
        $gateway = $this->smsGateway->find($gatewayId);
        if (!$gateway) {
            throw new \Exception('Gateway not found');
        }
        
        $testMessage = "Test message from SmartCast voting system. Time: " . date('Y-m-d H:i:s');
        $phone = $testPhone ?? $gateway['test_phone'] ?? '233545644749';
        
        return $this->sendSms($gateway, $phone, $testMessage);
    }
    
    /**
     * Get SMS statistics
     */
    public function getStatistics($dateFrom = null, $dateTo = null)
    {
        return $this->smsLog->getStatistics($dateFrom, $dateTo);
    }
    
    /**
     * Retry failed SMS
     */
    public function retryFailedSms($smsLogId)
    {
        $smsRecord = $this->smsLog->find($smsLogId);
        if (!$smsRecord || $smsRecord['status'] !== 'failed') {
            throw new \Exception('SMS record not found or not in failed state');
        }
        
        $gateway = $this->smsGateway->find($smsRecord['gateway_id']);
        if (!$gateway) {
            throw new \Exception('Gateway not found');
        }
        
        $result = $this->sendSms($gateway, $smsRecord['phone'], $smsRecord['message']);
        
        // Update the log record
        $this->smsLog->update($smsLogId, [
            'status' => $result['success'] ? 'sent' : 'failed',
            'response' => json_encode($result),
            'retry_count' => ($smsRecord['retry_count'] ?? 0) + 1,
            'last_retry_at' => date('Y-m-d H:i:s')
        ]);
        
        return $result;
    }
}
