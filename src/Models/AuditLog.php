<?php

namespace SmartCast\Models;

/**
 * Audit Log Model
 */
class AuditLog extends BaseModel
{
    protected $table = 'audit_logs';
    protected $fillable = [
        'user_id', 'action', 'details', 'ip_address'
    ];
    protected $timestamps = true; // Will only add created_at since updated_at doesn't exist

    /**
     * Generic audit logger used by controllers.
     * Accepts flexible payloads and only persists columns that actually exist.
     */
    public function log(array $data)
    {
        $columns = $this->getTableColumns();
        $columnSet = array_flip($columns);
        $payload = [];

        foreach ($data as $key => $value) {
            if (isset($columnSet[$key])) {
                $payload[$key] = $value;
            }
        }

        if (isset($columnSet['details'])) {
            if (!array_key_exists('details', $payload)) {
                $payload['details'] = '[]';
            } elseif (is_array($payload['details'])) {
                $payload['details'] = json_encode($payload['details']);
            }
        }

        if (isset($columnSet['ip_address']) && empty($payload['ip_address'])) {
            $payload['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? null;
        }

        if ($this->timestamps) {
            if (isset($columnSet['created_at']) && !isset($payload['created_at'])) {
                $payload['created_at'] = date('Y-m-d H:i:s');
            }
            if (isset($columnSet['updated_at']) && !isset($payload['updated_at'])) {
                $payload['updated_at'] = date('Y-m-d H:i:s');
            }
        }

        return $this->db->insert($this->table, $payload);
    }
    
    public function logLogin($userId, $email)
    {
        return $this->log([
            'user_id' => $userId,
            'action' => 'login',
            'details' => json_encode(['email' => $email, 'success' => true]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
    
    public function logFailedLogin($email, $ipAddress)
    {
        return $this->log([
            'user_id' => null,
            'action' => 'login_failed',
            'details' => json_encode(['email' => $email, 'ip' => $ipAddress]),
            'ip_address' => $ipAddress
        ]);
    }
    
    public function logLogout($userId)
    {
        try {
            return $this->log([
                'user_id' => $userId,
                'action' => 'logout',
                'details' => '[]',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        } catch (\Exception $e) {
            // If user doesn't exist (foreign key constraint), log without user_id
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                return $this->log([
                    'user_id' => null,
                    'action' => 'logout',
                    'details' => json_encode(['attempted_user_id' => $userId, 'error' => 'User not found']),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
                ]);
            }
            throw $e;
        }
    }
    
    public function logEventCreated($userId, $eventId, $eventName)
    {
        return $this->log([
            'user_id' => $userId,
            'action' => 'event_created',
            'details' => json_encode(['event_id' => $eventId, 'name' => $eventName]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
    
    public function logEventUpdated($userId, $eventId, $eventName)
    {
        return $this->log([
            'user_id' => $userId,
            'action' => 'event_updated',
            'details' => json_encode(['event_id' => $eventId, 'name' => $eventName]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
    
    public function logContestantCreated($userId, $contestantId, $contestantName)
    {
        return $this->log([
            'user_id' => $userId,
            'action' => 'contestant_created',
            'details' => json_encode(['contestant_id' => $contestantId, 'name' => $contestantName]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
    
    public function logVoteCast($transactionId, $eventId, $contestantId, $amount)
    {
        return $this->log([
            'user_id' => null,
            'action' => 'vote_cast',
            'details' => json_encode([
                'transaction_id' => $transactionId,
                'event_id' => $eventId,
                'contestant_id' => $contestantId,
                'amount' => $amount
            ]),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
    
    public function getRecentLogs($tenantId = null, $limit = 50)
    {
        $sql = "
            SELECT al.*, u.email as user_email
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
        ";
        
        $params = [];
        
        if ($tenantId) {
            $sql .= " WHERE u.tenant_id = :tenant_id OR u.tenant_id IS NULL";
            $params['tenant_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY al.created_at DESC LIMIT {$limit}";
        
        return $this->db->select($sql, $params);
    }
}
