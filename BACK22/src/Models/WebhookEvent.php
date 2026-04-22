<?php

namespace SmartCast\Models;

/**
 * Webhook Event Model
 */
class WebhookEvent extends BaseModel
{
    protected $table = 'webhook_events';
    protected $fillable = [
        'endpoint_id', 'event', 'payload', 'status', 'attempts'
    ];
    
    const STATUS_QUEUED = 'queued';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    
    // Event types
    const EVENT_VOTE_CAST = 'vote.cast';
    const EVENT_TRANSACTION_SUCCESS = 'transaction.success';
    const EVENT_TRANSACTION_FAILED = 'transaction.failed';
    const EVENT_EVENT_CREATED = 'event.created';
    const EVENT_EVENT_UPDATED = 'event.updated';
    const EVENT_EVENT_STATUS_CHANGED = 'event.status_changed';
    const EVENT_CONTESTANT_CREATED = 'contestant.created';
    const EVENT_CONTESTANT_UPDATED = 'contestant.updated';
    const EVENT_PAYOUT_COMPLETED = 'payout.completed';
    const EVENT_PAYOUT_FAILED = 'payout.failed';
    
    public function logEvent($endpointId, $eventType, $payload, $status = self::STATUS_QUEUED)
    {
        return $this->create([
            'endpoint_id' => $endpointId,
            'event' => $eventType,
            'payload' => is_array($payload) ? json_encode($payload) : $payload,
            'status' => $status,
            'attempts' => $status === self::STATUS_SENT ? 1 : 0
        ]);
    }
    
    public function queueWebhook($tenantId, $eventType, $payload)
    {
        $endpointModel = new WebhookEndpoint();
        $endpoints = $endpointModel->getActiveEndpoints($tenantId);
        
        $queuedEvents = [];
        
        foreach ($endpoints as $endpoint) {
            $eventId = $this->logEvent($endpoint['id'], $eventType, $payload, self::STATUS_QUEUED);
            $queuedEvents[] = $eventId;
        }
        
        return $queuedEvents;
    }
    
    public function processQueuedEvents($limit = 50)
    {
        $queuedEvents = $this->findAll(['status' => self::STATUS_QUEUED], 'created_at ASC', $limit);
        
        $results = [];
        
        foreach ($queuedEvents as $event) {
            $result = $this->processEvent($event['id']);
            $results[] = [
                'event_id' => $event['id'],
                'success' => $result['success'],
                'attempts' => $result['attempts']
            ];
        }
        
        return $results;
    }
    
    public function processEvent($eventId)
    {
        $event = $this->find($eventId);
        if (!$event) {
            return ['success' => false, 'error' => 'Event not found'];
        }
        
        // Get endpoint
        $endpointModel = new WebhookEndpoint();
        $endpoint = $endpointModel->find($event['endpoint_id']);
        
        if (!$endpoint || !$endpoint['active']) {
            $this->update($eventId, ['status' => self::STATUS_FAILED]);
            return ['success' => false, 'error' => 'Endpoint not active'];
        }
        
        // Increment attempts
        $attempts = $event['attempts'] + 1;
        $this->update($eventId, ['attempts' => $attempts]);
        
        // Send webhook
        $payload = json_decode($event['payload'], true);
        $result = $endpointModel->sendWebhook($endpoint, $event['event'], $payload);
        
        if ($result['success']) {
            $this->update($eventId, ['status' => self::STATUS_SENT]);
            return ['success' => true, 'attempts' => $attempts];
        } else {
            // Check if we should retry
            if ($attempts >= 3) {
                $this->update($eventId, ['status' => self::STATUS_FAILED]);
            }
            
            return [
                'success' => false,
                'attempts' => $attempts,
                'error' => $result['error'] ?? 'HTTP ' . $result['http_code']
            ];
        }
    }
    
    public function retryFailedEvents($endpointId = null, $limit = 20)
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE status = :status 
            AND attempts < 3
        ";
        
        $params = ['status' => self::STATUS_FAILED];
        
        if ($endpointId) {
            $sql .= " AND endpoint_id = :endpoint_id";
            $params['endpoint_id'] = $endpointId;
        }
        
        $sql .= " ORDER BY created_at ASC LIMIT {$limit}";
        
        $failedEvents = $this->db->select($sql, $params);
        
        $results = [];
        
        foreach ($failedEvents as $event) {
            // Reset to queued status
            $this->update($event['id'], ['status' => self::STATUS_QUEUED]);
            
            // Process the event
            $result = $this->processEvent($event['id']);
            $results[] = [
                'event_id' => $event['id'],
                'success' => $result['success']
            ];
        }
        
        return $results;
    }
    
    public function getEventsByEndpoint($endpointId, $limit = 100)
    {
        return $this->findAll(['endpoint_id' => $endpointId], 'created_at DESC', $limit);
    }
    
    public function getEventStats($tenantId = null, $endpointId = null)
    {
        $sql = "
            SELECT 
                we.status,
                we.event,
                COUNT(*) as event_count,
                AVG(we.attempts) as avg_attempts
            FROM {$this->table} we
        ";
        
        $params = [];
        $conditions = [];
        
        if ($tenantId || $endpointId) {
            $sql .= " INNER JOIN webhook_endpoints wep ON we.endpoint_id = wep.id";
            
            if ($tenantId) {
                $conditions[] = "wep.tenant_id = :tenant_id";
                $params['tenant_id'] = $tenantId;
            }
            
            if ($endpointId) {
                $conditions[] = "we.endpoint_id = :endpoint_id";
                $params['endpoint_id'] = $endpointId;
            }
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sql .= " GROUP BY we.status, we.event ORDER BY event_count DESC";
        
        return $this->db->select($sql, $params);
    }
    
    public function getRecentEvents($tenantId, $limit = 50)
    {
        $sql = "
            SELECT we.*, wep.url as endpoint_url
            FROM {$this->table} we
            INNER JOIN webhook_endpoints wep ON we.endpoint_id = wep.id
            WHERE wep.tenant_id = :tenant_id
            ORDER BY we.created_at DESC
            LIMIT {$limit}
        ";
        
        return $this->db->select($sql, ['tenant_id' => $tenantId]);
    }
    
    public function cleanupOldEvents($olderThanDays = 30)
    {
        $cutoffDate = date('Y-m-d H:i:s', time() - ($olderThanDays * 24 * 3600));
        
        return $this->db->delete(
            $this->table,
            'created_at < :cutoff_date AND status = :status',
            [
                'cutoff_date' => $cutoffDate,
                'status' => self::STATUS_SENT
            ]
        );
    }
    
    public function getFailureRate($endpointId, $hours = 24)
    {
        $since = date('Y-m-d H:i:s', time() - ($hours * 3600));
        
        $sql = "
            SELECT 
                COUNT(*) as total_events,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_events,
                COUNT(CASE WHEN status = 'sent' THEN 1 END) as successful_events
            FROM {$this->table}
            WHERE endpoint_id = :endpoint_id
            AND created_at >= :since
        ";
        
        $result = $this->db->selectOne($sql, [
            'endpoint_id' => $endpointId,
            'since' => $since
        ]);
        
        $totalEvents = $result['total_events'] ?? 0;
        $failedEvents = $result['failed_events'] ?? 0;
        
        return [
            'total_events' => $totalEvents,
            'failed_events' => $failedEvents,
            'successful_events' => $result['successful_events'] ?? 0,
            'failure_rate' => $totalEvents > 0 ? ($failedEvents / $totalEvents) * 100 : 0
        ];
    }
    
    // Webhook trigger methods for different events
    
    public function triggerVoteCast($tenantId, $voteData)
    {
        return $this->queueWebhook($tenantId, self::EVENT_VOTE_CAST, $voteData);
    }
    
    public function triggerTransactionSuccess($tenantId, $transactionData)
    {
        return $this->queueWebhook($tenantId, self::EVENT_TRANSACTION_SUCCESS, $transactionData);
    }
    
    public function triggerTransactionFailed($tenantId, $transactionData)
    {
        return $this->queueWebhook($tenantId, self::EVENT_TRANSACTION_FAILED, $transactionData);
    }
    
    public function triggerEventCreated($tenantId, $eventData)
    {
        return $this->queueWebhook($tenantId, self::EVENT_EVENT_CREATED, $eventData);
    }
    
    public function triggerEventUpdated($tenantId, $eventData)
    {
        return $this->queueWebhook($tenantId, self::EVENT_EVENT_UPDATED, $eventData);
    }
    
    public function triggerEventStatusChanged($tenantId, $eventData)
    {
        return $this->queueWebhook($tenantId, self::EVENT_EVENT_STATUS_CHANGED, $eventData);
    }
    
    public function triggerContestantCreated($tenantId, $contestantData)
    {
        return $this->queueWebhook($tenantId, self::EVENT_CONTESTANT_CREATED, $contestantData);
    }
    
    public function triggerContestantUpdated($tenantId, $contestantData)
    {
        return $this->queueWebhook($tenantId, self::EVENT_CONTESTANT_UPDATED, $contestantData);
    }
    
    public function triggerPayoutCompleted($tenantId, $payoutData)
    {
        return $this->queueWebhook($tenantId, self::EVENT_PAYOUT_COMPLETED, $payoutData);
    }
    
    public function triggerPayoutFailed($tenantId, $payoutData)
    {
        return $this->queueWebhook($tenantId, self::EVENT_PAYOUT_FAILED, $payoutData);
    }
    
    public function getEventTypeStats($tenantId)
    {
        $sql = "
            SELECT 
                we.event,
                COUNT(*) as total_count,
                COUNT(CASE WHEN we.status = 'sent' THEN 1 END) as successful_count,
                COUNT(CASE WHEN we.status = 'failed' THEN 1 END) as failed_count
            FROM {$this->table} we
            INNER JOIN webhook_endpoints wep ON we.endpoint_id = wep.id
            WHERE wep.tenant_id = :tenant_id
            GROUP BY we.event
            ORDER BY total_count DESC
        ";
        
        return $this->db->select($sql, ['tenant_id' => $tenantId]);
    }
}
