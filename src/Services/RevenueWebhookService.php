<?php

namespace SmartCast\Services;

use SmartCast\Models\WebhookEndpoint;
use SmartCast\Models\WebhookEvent;

/**
 * Revenue Webhook Service
 * Sends real-time notifications when revenue is distributed
 */
class RevenueWebhookService
{
    private $webhookEndpointModel;
    private $webhookEventModel;
    
    public function __construct()
    {
        $this->webhookEndpointModel = new WebhookEndpoint();
        $this->webhookEventModel = new WebhookEvent();
    }
    
    /**
     * Send revenue notification to tenant
     */
    public function sendRevenueEarned($tenantId, $transactionData, $revenueBreakdown)
    {
        $payload = [
            'event' => 'revenue.earned',
            'timestamp' => date('c'),
            'data' => [
                'tenant_id' => $tenantId,
                'transaction_id' => $transactionData['id'],
                'event_name' => $transactionData['event_name'] ?? 'Unknown Event',
                'contestant_name' => $transactionData['contestant_name'] ?? 'Unknown Contestant',
                'gross_amount' => $revenueBreakdown['total_amount'],
                'platform_fee' => $revenueBreakdown['platform_fee'],
                'net_earnings' => $revenueBreakdown['tenant_amount'],
                'vote_count' => $transactionData['vote_count'] ?? 1,
                'voter_msisdn' => $transactionData['msisdn'] ?? null
            ]
        ];
        
        return $this->sendWebhook($tenantId, $payload);
    }
    
    /**
     * Send payout notification to tenant
     */
    public function sendPayoutProcessed($tenantId, $payoutData)
    {
        $payload = [
            'event' => 'payout.processed',
            'timestamp' => date('c'),
            'data' => [
                'tenant_id' => $tenantId,
                'payout_id' => $payoutData['payout_id'],
                'amount' => $payoutData['amount'],
                'method' => $payoutData['payout_method'],
                'status' => $payoutData['status'],
                'processed_at' => $payoutData['processed_at']
            ]
        ];
        
        return $this->sendWebhook($tenantId, $payload);
    }
    
    /**
     * Send platform revenue notification to super admin
     */
    public function sendPlatformRevenue($transactionData, $revenueBreakdown)
    {
        $payload = [
            'event' => 'platform.revenue',
            'timestamp' => date('c'),
            'data' => [
                'transaction_id' => $transactionData['id'],
                'tenant_id' => $transactionData['tenant_id'],
                'platform_fee' => $revenueBreakdown['platform_fee'],
                'fee_percentage' => $this->calculateFeePercentage($revenueBreakdown),
                'total_transaction_amount' => $revenueBreakdown['total_amount'],
                'event_name' => $transactionData['event_name'] ?? 'Unknown Event'
            ]
        ];
        
        // Send to platform webhook endpoints (tenant_id = null for platform)
        return $this->sendWebhook(null, $payload);
    }
    
    /**
     * Send webhook to all active endpoints for a tenant
     */
    private function sendWebhook($tenantId, $payload)
    {
        try {
            $endpoints = $this->webhookEndpointModel->getActiveEndpoints($tenantId);
            
            if (empty($endpoints)) {
                error_log("No webhook endpoints found for tenant: " . ($tenantId ?? 'platform'));
                return false;
            }
            
            $results = [];
            
            foreach ($endpoints as $endpoint) {
                $eventId = $this->webhookEventModel->create([
                    'endpoint_id' => $endpoint['id'],
                    'event' => $payload['event'],
                    'payload' => json_encode($payload),
                    'status' => 'queued',
                    'attempts' => 0
                ]);
                
                // Send webhook asynchronously
                $result = $this->sendWebhookRequest($endpoint, $payload);
                
                // Update event status
                $this->webhookEventModel->update($eventId, [
                    'status' => $result['success'] ? 'sent' : 'failed',
                    'attempts' => 1
                ]);
                
                $results[] = $result;
                
                error_log("Webhook sent to {$endpoint['url']}: " . ($result['success'] ? 'SUCCESS' : 'FAILED'));
            }
            
            return $results;
            
        } catch (\Exception $e) {
            error_log("Webhook sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send HTTP request to webhook endpoint
     */
    private function sendWebhookRequest($endpoint, $payload)
    {
        try {
            $headers = [
                'Content-Type: application/json',
                'User-Agent: SmartCast-Webhooks/1.0'
            ];
            
            // Add signature if secret is configured
            if (!empty($endpoint['secret'])) {
                $signature = hash_hmac('sha256', json_encode($payload), $endpoint['secret']);
                $headers[] = 'X-SmartCast-Signature: sha256=' . $signature;
            }
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $endpoint['url'],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false // For development - enable in production
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new \Exception("cURL error: " . $error);
            }
            
            $success = $httpCode >= 200 && $httpCode < 300;
            
            return [
                'success' => $success,
                'http_code' => $httpCode,
                'response' => $response,
                'error' => $success ? null : "HTTP $httpCode"
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'http_code' => 0,
                'response' => null,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Calculate fee percentage from revenue breakdown
     */
    private function calculateFeePercentage($revenueBreakdown)
    {
        if ($revenueBreakdown['total_amount'] <= 0) {
            return 0;
        }
        
        return round(($revenueBreakdown['platform_fee'] / $revenueBreakdown['total_amount']) * 100, 2);
    }
    
    /**
     * Test webhook endpoint
     */
    public function testWebhook($tenantId, $endpointUrl)
    {
        $testPayload = [
            'event' => 'webhook.test',
            'timestamp' => date('c'),
            'data' => [
                'message' => 'This is a test webhook from SmartCast',
                'tenant_id' => $tenantId,
                'test_timestamp' => time()
            ]
        ];
        
        $endpoint = ['url' => $endpointUrl, 'secret' => null];
        return $this->sendWebhookRequest($endpoint, $testPayload);
    }
}
