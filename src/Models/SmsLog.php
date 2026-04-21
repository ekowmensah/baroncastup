<?php

namespace SmartCast\Models;

/**
 * SMS Log Model
 */
class SmsLog extends BaseModel
{
    protected $table = 'sms_logs';
    
    protected $fillable = [
        'phone',
        'message',
        'gateway_id',
        'gateway_type',
        'status',
        'response',
        'vote_id',
        'transaction_id',
        'retry_count',
        'last_retry_at',
        'created_at'
    ];
    
    /**
     * Get SMS statistics
     */
    public function getStatistics($dateFrom = null, $dateTo = null)
    {
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($dateFrom) {
            $whereClause .= " AND DATE(created_at) >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $whereClause .= " AND DATE(created_at) <= :date_to";
            $params['date_to'] = $dateTo;
        }
        
        $sql = "
            SELECT 
                COUNT(*) as total_sms,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                ROUND(
                    (SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) * 100.0 / 
                     NULLIF(COUNT(*), 0)), 2
                ) as success_rate,
                COUNT(DISTINCT gateway_id) as gateways_used,
                COUNT(DISTINCT phone) as unique_recipients
            FROM {$this->table}
            {$whereClause}
        ";
        
        $result = $this->db->selectOne($sql, $params);
        return $result;
    }
    
    /**
     * Get daily SMS statistics
     */
    public function getDailyStats($days = 30)
    {
        $sql = "
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM {$this->table}
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ";
        
        return $this->db->select($sql, ['days' => $days]);
    }
    
    /**
     * Get failed SMS for retry
     */
    public function getFailedSms($limit = 100)
    {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE status = 'failed' 
            AND (retry_count IS NULL OR retry_count < 3)
            ORDER BY created_at ASC
            LIMIT :limit
        ";
        
        return $this->db->select($sql, ['limit' => $limit]);
    }
    
    /**
     * Get SMS logs with pagination
     */
    public function getPaginatedLogs($page = 1, $perPage = 50, $filters = [])
    {
        $offset = ($page - 1) * $perPage;
        $whereClause = "WHERE 1=1";
        $params = ['limit' => $perPage, 'offset' => $offset];
        
        if (!empty($filters['status'])) {
            $whereClause .= " AND status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['gateway_type'])) {
            $whereClause .= " AND gateway_type = :gateway_type";
            $params['gateway_type'] = $filters['gateway_type'];
        }
        
        if (!empty($filters['phone'])) {
            $whereClause .= " AND phone LIKE :phone";
            $params['phone'] = '%' . $filters['phone'] . '%';
        }
        
        if (!empty($filters['date_from'])) {
            $whereClause .= " AND DATE(created_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereClause .= " AND DATE(created_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$whereClause}";
        $countParams = array_diff_key($params, ['limit' => '', 'offset' => '']);
        $totalResult = $this->db->selectOne($countSql, $countParams);
        $total = $totalResult['total'];
        
        // Get paginated results
        $sql = "
            SELECT sl.*, sg.name as gateway_name
            FROM {$this->table} sl
            LEFT JOIN sms_gateways sg ON sl.gateway_id = sg.id
            {$whereClause}
            ORDER BY sl.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $logs = $this->db->select($sql, $params);
        
        return [
            'data' => $logs,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Get SMS logs for specific vote
     */
    public function getByVoteId($voteId)
    {
        $sql = "
            SELECT sl.*, sg.name as gateway_name
            FROM {$this->table} sl
            LEFT JOIN sms_gateways sg ON sl.gateway_id = sg.id
            WHERE sl.vote_id = :vote_id
            ORDER BY sl.created_at DESC
        ";
        
        return $this->db->select($sql, ['vote_id' => $voteId]);
    }
    
    /**
     * Get SMS logs for specific transaction
     */
    public function getByTransactionId($transactionId)
    {
        $sql = "
            SELECT sl.*, sg.name as gateway_name
            FROM {$this->table} sl
            LEFT JOIN sms_gateways sg ON sl.gateway_id = sg.id
            WHERE sl.transaction_id = :transaction_id
            ORDER BY sl.created_at DESC
        ";
        
        return $this->db->select($sql, ['transaction_id' => $transactionId]);
    }
    
    /**
     * Clean old logs (older than specified days)
     */
    public function cleanOldLogs($days = 90)
    {
        $sql = "
            DELETE FROM {$this->table}
            WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
        ";
        
        return $this->db->execute($sql, ['days' => $days]);
    }
    
    /**
     * Get gateway performance comparison
     */
    public function getGatewayComparison($dateFrom = null, $dateTo = null)
    {
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($dateFrom) {
            $whereClause .= " AND DATE(sl.created_at) >= :date_from";
            $params['date_from'] = $dateFrom;
        }
        
        if ($dateTo) {
            $whereClause .= " AND DATE(sl.created_at) <= :date_to";
            $params['date_to'] = $dateTo;
        }
        
        $sql = "
            SELECT 
                sl.gateway_type,
                sg.name as gateway_name,
                COUNT(*) as total_sent,
                SUM(CASE WHEN sl.status = 'sent' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN sl.status = 'failed' THEN 1 ELSE 0 END) as failed,
                ROUND(
                    (SUM(CASE WHEN sl.status = 'sent' THEN 1 ELSE 0 END) * 100.0 / 
                     NULLIF(COUNT(*), 0)), 2
                ) as success_rate,
                AVG(
                    CASE WHEN sl.status = 'sent' 
                    THEN TIMESTAMPDIFF(SECOND, sl.created_at, sl.created_at) 
                    ELSE NULL END
                ) as avg_response_time
            FROM {$this->table} sl
            LEFT JOIN sms_gateways sg ON sl.gateway_id = sg.id
            {$whereClause}
            GROUP BY sl.gateway_type, sg.name
            ORDER BY success_rate DESC, total_sent DESC
        ";
        
        return $this->db->select($sql, $params);
    }
}
