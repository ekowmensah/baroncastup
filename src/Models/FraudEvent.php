<?php

namespace SmartCast\Models;

/**
 * Fraud Event Model
 */
class FraudEvent extends BaseModel
{
    protected $table = 'fraud_events';
    protected $fillable = [
        'tenant_id', 'event_type', 'details', 'ip_address'
    ];
    
    const EVENT_SUSPICIOUS_VOTING = 'suspicious_voting';
    const EVENT_RAPID_REQUESTS = 'rapid_requests';
    const EVENT_DUPLICATE_VOTES = 'duplicate_votes';
    const EVENT_INVALID_PAYMENT = 'invalid_payment';
    const EVENT_BOT_ACTIVITY = 'bot_activity';
    const EVENT_UNUSUAL_PATTERN = 'unusual_pattern';
    
    public function logSuspiciousVoting($tenantId, $details, $ipAddress = null)
    {
        return $this->logFraudEvent(
            $tenantId,
            self::EVENT_SUSPICIOUS_VOTING,
            $details,
            $ipAddress
        );
    }
    
    public function logRapidRequests($tenantId, $details, $ipAddress = null)
    {
        return $this->logFraudEvent(
            $tenantId,
            self::EVENT_RAPID_REQUESTS,
            $details,
            $ipAddress
        );
    }
    
    public function logDuplicateVotes($tenantId, $details, $ipAddress = null)
    {
        return $this->logFraudEvent(
            $tenantId,
            self::EVENT_DUPLICATE_VOTES,
            $details,
            $ipAddress
        );
    }
    
    public function logInvalidPayment($tenantId, $details, $ipAddress = null)
    {
        return $this->logFraudEvent(
            $tenantId,
            self::EVENT_INVALID_PAYMENT,
            $details,
            $ipAddress
        );
    }
    
    public function logBotActivity($tenantId, $details, $ipAddress = null)
    {
        return $this->logFraudEvent(
            $tenantId,
            self::EVENT_BOT_ACTIVITY,
            $details,
            $ipAddress
        );
    }
    
    private function logFraudEvent($tenantId, $eventType, $details, $ipAddress = null)
    {
        return $this->create([
            'tenant_id' => $tenantId,
            'event_type' => $eventType,
            'details' => is_array($details) ? json_encode($details) : $details,
            'ip_address' => $ipAddress ?: ($_SERVER['REMOTE_ADDR'] ?? null)
        ]);
    }
    
    public function getFraudEvents($tenantId = null, $eventType = null, $limit = 100)
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($tenantId) {
            $sql .= " AND tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        if ($eventType) {
            $sql .= " AND event_type = :event_type";
            $params['event_type'] = $eventType;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT {$limit}";
        
        return $this->db->select($sql, $params);
    }
    
    public function getFraudStats($tenantId = null, $hours = 24)
    {
        $since = date('Y-m-d H:i:s', time() - ($hours * 3600));
        
        $sql = "
            SELECT 
                event_type,
                COUNT(*) as event_count,
                COUNT(DISTINCT ip_address) as unique_ips
            FROM {$this->table} 
            WHERE created_at >= :since
        ";
        
        $params = ['since' => $since];
        
        if ($tenantId) {
            $sql .= " AND tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        $sql .= " GROUP BY event_type ORDER BY event_count DESC";
        
        return $this->db->select($sql, $params);
    }
    
    public function detectSuspiciousActivity($tenantId, $ipAddress, $msisdn = null)
    {
        $suspiciousEvents = [];
        
        // Check for rapid requests from same IP
        $rapidRequests = $this->checkRapidRequests($ipAddress);
        if ($rapidRequests) {
            $suspiciousEvents[] = [
                'type' => 'rapid_requests',
                'severity' => 'medium',
                'details' => $rapidRequests
            ];
        }
        
        // Check for duplicate voting patterns
        if ($msisdn) {
            $duplicateVotes = $this->checkDuplicateVotes($msisdn);
            if ($duplicateVotes) {
                $suspiciousEvents[] = [
                    'type' => 'duplicate_votes',
                    'severity' => 'high',
                    'details' => $duplicateVotes
                ];
            }
        }
        
        // Check for bot-like behavior
        $botActivity = $this->checkBotActivity($ipAddress);
        if ($botActivity) {
            $suspiciousEvents[] = [
                'type' => 'bot_activity',
                'severity' => 'high',
                'details' => $botActivity
            ];
        }
        
        return $suspiciousEvents;
    }
    
    private function checkRapidRequests($ipAddress, $windowMinutes = 5, $threshold = 50)
    {
        $since = date('Y-m-d H:i:s', time() - ($windowMinutes * 60));
        
        $sql = "
            SELECT COUNT(*) as request_count 
            FROM {$this->table} 
            WHERE ip_address = :ip_address 
            AND created_at >= :since
        ";
        
        $result = $this->db->selectOne($sql, [
            'ip_address' => $ipAddress,
            'since' => $since
        ]);
        
        $count = $result['request_count'] ?? 0;
        
        if ($count >= $threshold) {
            return [
                'ip_address' => $ipAddress,
                'request_count' => $count,
                'window_minutes' => $windowMinutes,
                'threshold' => $threshold
            ];
        }
        
        return false;
    }
    
    private function checkDuplicateVotes($msisdn, $windowMinutes = 10, $threshold = 20)
    {
        // This would need to check the votes table
        // Implementation depends on vote tracking requirements
        return false;
    }
    
    private function checkBotActivity($ipAddress)
    {
        // Check for patterns that indicate bot activity
        // This is a simplified implementation
        $recentEvents = $this->getFraudEvents(null, null, 100);
        
        $ipEvents = array_filter($recentEvents, function($event) use ($ipAddress) {
            return $event['ip_address'] === $ipAddress;
        });
        
        if (count($ipEvents) > 10) {
            return [
                'ip_address' => $ipAddress,
                'event_count' => count($ipEvents),
                'pattern' => 'high_frequency_activity'
            ];
        }
        
        return false;
    }
}
