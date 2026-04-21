<?php

namespace SmartCast\Models;

/**
 * Rate Limit Model
 */
class RateLimit extends BaseModel
{
    protected $table = 'rate_limits';
    protected $fillable = ['key'];
    
    public function checkRateLimit($key, $maxRequests = 100, $windowSeconds = 3600)
    {
        $windowStart = date('Y-m-d H:i:s', time() - $windowSeconds);
        
        // Count requests in the current window
        $sql = "
            SELECT COUNT(*) as request_count 
            FROM {$this->table} 
            WHERE `key` = :key 
            AND created_at >= :window_start
        ";
        
        $result = $this->db->selectOne($sql, [
            'key' => $key,
            'window_start' => $windowStart
        ]);
        
        $currentCount = $result['request_count'] ?? 0;
        
        if ($currentCount >= $maxRequests) {
            return [
                'allowed' => false,
                'current_count' => $currentCount,
                'limit' => $maxRequests,
                'reset_time' => time() + $windowSeconds
            ];
        }
        
        // Record this request
        $this->create(['key' => $key]);
        
        return [
            'allowed' => true,
            'current_count' => $currentCount + 1,
            'limit' => $maxRequests,
            'remaining' => $maxRequests - ($currentCount + 1)
        ];
    }
    
    public function recordRequest($key)
    {
        return $this->create(['key' => $key]);
    }
    
    public function cleanupOldRecords($olderThanHours = 24)
    {
        $cutoffTime = date('Y-m-d H:i:s', time() - ($olderThanHours * 3600));
        
        $sql = "DELETE FROM {$this->table} WHERE created_at < :cutoff_time";
        return $this->db->query($sql, ['cutoff_time' => $cutoffTime]);
    }
    
    public function getRateLimitStats($key, $windowSeconds = 3600)
    {
        $windowStart = date('Y-m-d H:i:s', time() - $windowSeconds);
        
        $sql = "
            SELECT 
                COUNT(*) as total_requests,
                MIN(created_at) as first_request,
                MAX(created_at) as last_request
            FROM {$this->table} 
            WHERE `key` = :key 
            AND created_at >= :window_start
        ";
        
        return $this->db->selectOne($sql, [
            'key' => $key,
            'window_start' => $windowStart
        ]);
    }
    
    public function blockKey($key, $durationSeconds = 3600)
    {
        // Create multiple entries to effectively block the key
        $blockUntil = time() + $durationSeconds;
        
        for ($i = 0; $i < 1000; $i++) { // Block with 1000 fake requests
            $this->create([
                'key' => "BLOCKED_{$key}_{$blockUntil}",
            ]);
        }
    }
    
    public function isBlocked($key)
    {
        $sql = "
            SELECT COUNT(*) as block_count 
            FROM {$this->table} 
            WHERE `key` LIKE :pattern 
            AND created_at >= :window_start
        ";
        
        $result = $this->db->selectOne($sql, [
            'pattern' => "BLOCKED_{$key}_%",
            'window_start' => date('Y-m-d H:i:s', time() - 3600)
        ]);
        
        return ($result['block_count'] ?? 0) > 0;
    }
}
