<?php

namespace SmartCast\Models;

/**
 * Webhook Endpoint Model
 */
class WebhookEndpoint extends BaseModel
{
    protected $table = 'webhook_endpoints';
    protected $fillable = [
        'tenant_id', 'url', 'secret', 'active'
    ];
    
    public function createEndpoint($tenantId, $url, $secret = null)
    {
        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception('Invalid webhook URL');
        }
        
        // Generate secret if not provided
        if (!$secret) {
            $secret = $this->generateSecret();
        }
        
        return $this->create([
            'tenant_id' => $tenantId,
            'url' => $url,
            'secret' => $secret,
            'active' => 1
        ]);
    }
    
    private function generateSecret()
    {
        return bin2hex(random_bytes(32));
    }
    
    public function getActiveEndpoints($tenantId)
    {
        return $this->findAll([
            'tenant_id' => $tenantId,
            'active' => 1
        ]);
    }
    
    public function testEndpoint($endpointId)
    {
        $endpoint = $this->find($endpointId);
        if (!$endpoint) {
            throw new \Exception('Endpoint not found');
        }
        
        $testPayload = [
            'event' => 'webhook.test',
            'timestamp' => time(),
            'data' => [
                'message' => 'This is a test webhook from SmartCast',
                'endpoint_id' => $endpointId
            ]
        ];
        
        return $this->sendWebhook($endpoint, 'webhook.test', $testPayload);
    }
    
    public function sendWebhook($endpoint, $eventType, $payload)
    {
        $webhookData = [
            'event' => $eventType,
            'timestamp' => time(),
            'data' => $payload
        ];
        
        // Generate signature
        $signature = $this->generateSignature(json_encode($webhookData), $endpoint['secret']);
        
        // Prepare headers
        $headers = [
            'Content-Type: application/json',
            'X-SmartCast-Signature: ' . $signature,
            'X-SmartCast-Event: ' . $eventType,
            'User-Agent: SmartCast-Webhook/1.0'
        ];
        
        // Send HTTP request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint['url']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        $success = ($httpCode >= 200 && $httpCode < 300);
        
        // Log the webhook event
        $webhookEventModel = new WebhookEvent();
        $webhookEventModel->logEvent($endpoint['id'], $eventType, $webhookData, $success ? 'sent' : 'failed');
        
        return [
            'success' => $success,
            'http_code' => $httpCode,
            'response' => $response,
            'error' => $error
        ];
    }
    
    private function generateSignature($payload, $secret)
    {
        return hash_hmac('sha256', $payload, $secret);
    }
    
    public function verifySignature($payload, $signature, $secret)
    {
        $expectedSignature = $this->generateSignature($payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }
    
    public function activateEndpoint($endpointId)
    {
        return $this->update($endpointId, ['active' => 1]);
    }
    
    public function deactivateEndpoint($endpointId)
    {
        return $this->update($endpointId, ['active' => 0]);
    }
    
    public function updateEndpointUrl($endpointId, $newUrl)
    {
        if (!filter_var($newUrl, FILTER_VALIDATE_URL)) {
            throw new \Exception('Invalid webhook URL');
        }
        
        return $this->update($endpointId, ['url' => $newUrl]);
    }
    
    public function regenerateSecret($endpointId)
    {
        $newSecret = $this->generateSecret();
        $this->update($endpointId, ['secret' => $newSecret]);
        
        return $newSecret;
    }
    
    public function getEndpointStats($endpointId)
    {
        $webhookEventModel = new WebhookEvent();
        
        $sql = "
            SELECT 
                COUNT(*) as total_events,
                COUNT(CASE WHEN status = 'sent' THEN 1 END) as successful_events,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_events,
                MIN(created_at) as first_event,
                MAX(created_at) as last_event
            FROM webhook_events
            WHERE endpoint_id = :endpoint_id
        ";
        
        return $this->db->selectOne($sql, ['endpoint_id' => $endpointId]);
    }
    
    public function getEndpointsByTenant($tenantId)
    {
        $sql = "
            SELECT we.*, 
                   COUNT(wev.id) as total_events,
                   COUNT(CASE WHEN wev.status = 'sent' THEN 1 END) as successful_events
            FROM {$this->table} we
            LEFT JOIN webhook_events wev ON we.id = wev.endpoint_id
            WHERE we.tenant_id = :tenant_id
            GROUP BY we.id
            ORDER BY we.created_at DESC
        ";
        
        return $this->db->select($sql, ['tenant_id' => $tenantId]);
    }
}
