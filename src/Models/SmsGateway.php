<?php

namespace SmartCast\Models;

/**
 * SMS Gateway Model
 */
class SmsGateway extends BaseModel
{
    protected $table = 'sms_gateways';
    
    protected $fillable = [
        'name',
        'type',
        'api_key',
        'client_id',
        'client_secret',
        'sender_id',
        'base_url',
        'test_phone',
        'is_active',
        'priority',
        'config',
        'created_at',
        'updated_at'
    ];
    
    /**
     * Get active gateway with highest priority
     */
    public function getActiveGateway()
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE is_active = 1 
            ORDER BY priority ASC, created_at ASC 
            LIMIT 1
        ";
        
        return $this->db->selectOne($sql);
    }
    
    /**
     * Get gateway by type
     */
    public function getGatewayByType($type)
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE type = :type AND is_active = 1 
            ORDER BY priority ASC 
            LIMIT 1
        ";
        
        return $this->db->selectOne($sql, ['type' => $type]);
    }
    
    /**
     * Get all active gateways
     */
    public function getActiveGateways()
    {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE is_active = 1 
            ORDER BY priority ASC, name ASC
        ";
        
        return $this->db->select($sql);
    }
    
    /**
     * Test gateway configuration
     */
    public function testConfiguration($id)
    {
        $gateway = $this->find($id);
        if (!$gateway) {
            return ['success' => false, 'message' => 'Gateway not found'];
        }
        
        // Basic validation
        $required = ['api_key', 'sender_id'];
        if ($gateway['type'] === 'hubtel') {
            $required[] = 'client_id';
            $required[] = 'client_secret';
        }
        
        foreach ($required as $field) {
            if (empty($gateway[$field])) {
                return [
                    'success' => false, 
                    'message' => "Missing required field: {$field}"
                ];
            }
        }
        
        return ['success' => true, 'message' => 'Configuration valid'];
    }
    
    /**
     * Update gateway priority
     */
    public function updatePriority($id, $priority)
    {
        return $this->update($id, ['priority' => $priority]);
    }
    
    /**
     * Toggle gateway status
     */
    public function toggleStatus($id)
    {
        $gateway = $this->find($id);
        if (!$gateway) {
            return false;
        }
        
        return $this->update($id, ['is_active' => !$gateway['is_active']]);
    }
    
    /**
     * Get gateway statistics
     */
    public function getGatewayStats($gatewayId = null, $dateFrom = null, $dateTo = null)
    {
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($gatewayId) {
            $whereClause .= " AND sl.gateway_id = :gateway_id";
            $params['gateway_id'] = $gatewayId;
        }
        
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
                sg.id,
                sg.name,
                sg.type,
                COUNT(sl.id) as total_sent,
                SUM(CASE WHEN sl.status = 'sent' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN sl.status = 'failed' THEN 1 ELSE 0 END) as failed,
                ROUND(
                    (SUM(CASE WHEN sl.status = 'sent' THEN 1 ELSE 0 END) * 100.0 / 
                     NULLIF(COUNT(sl.id), 0)), 2
                ) as success_rate
            FROM {$this->table} sg
            LEFT JOIN sms_logs sl ON sg.id = sl.gateway_id
            {$whereClause}
            GROUP BY sg.id, sg.name, sg.type
            ORDER BY sg.priority ASC
        ";
        
        return $this->db->select($sql, $params);
    }
}
