<?php

namespace SmartCast\Models;

/**
 * Risk Block Model
 */
class RiskBlock extends BaseModel
{
    protected $table = 'risk_blocks';
    protected $fillable = [
        'tenant_id', 'block_type', 'block_value', 'reason', 'active'
    ];
    
    const BLOCK_TYPE_IP = 'ip';
    const BLOCK_TYPE_MSISDN = 'msisdn';
    const BLOCK_TYPE_DEVICE = 'device';
    
    public function blockIp($ipAddress, $reason = null, $tenantId = null)
    {
        return $this->create([
            'tenant_id' => $tenantId,
            'block_type' => self::BLOCK_TYPE_IP,
            'block_value' => $ipAddress,
            'reason' => $reason,
            'active' => 1
        ]);
    }
    
    public function blockMsisdn($msisdn, $reason = null, $tenantId = null)
    {
        return $this->create([
            'tenant_id' => $tenantId,
            'block_type' => self::BLOCK_TYPE_MSISDN,
            'block_value' => $msisdn,
            'reason' => $reason,
            'active' => 1
        ]);
    }
    
    public function blockDevice($deviceId, $reason = null, $tenantId = null)
    {
        return $this->create([
            'tenant_id' => $tenantId,
            'block_type' => self::BLOCK_TYPE_DEVICE,
            'block_value' => $deviceId,
            'reason' => $reason,
            'active' => 1
        ]);
    }
    
    public function isBlocked($type, $value, $tenantId = null)
    {
        $conditions = [
            'block_type' => $type,
            'block_value' => $value,
            'active' => 1
        ];
        
        if ($tenantId) {
            $conditions['tenant_id'] = $tenantId;
        }
        
        $block = $this->findAll($conditions, null, 1);
        return !empty($block);
    }
    
    public function isIpBlocked($ipAddress, $tenantId = null)
    {
        return $this->isBlocked(self::BLOCK_TYPE_IP, $ipAddress, $tenantId);
    }
    
    public function isMsisdnBlocked($msisdn, $tenantId = null)
    {
        return $this->isBlocked(self::BLOCK_TYPE_MSISDN, $msisdn, $tenantId);
    }
    
    public function isDeviceBlocked($deviceId, $tenantId = null)
    {
        return $this->isBlocked(self::BLOCK_TYPE_DEVICE, $deviceId, $tenantId);
    }
    
    public function unblock($type, $value, $tenantId = null)
    {
        $sql = "
            UPDATE {$this->table} 
            SET active = 0 
            WHERE block_type = :type 
            AND block_value = :value 
            AND active = 1
        ";
        
        $params = [
            'type' => $type,
            'value' => $value
        ];
        
        if ($tenantId) {
            $sql .= " AND tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        return $this->db->query($sql, $params);
    }
    
    public function getActiveBlocks($tenantId = null)
    {
        $conditions = ['active' => 1];
        
        if ($tenantId) {
            $conditions['tenant_id'] = $tenantId;
        }
        
        return $this->findAll($conditions, 'created_at DESC');
    }
    
    public function getBlockHistory($type = null, $value = null, $tenantId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($type) {
            $sql .= " AND block_type = :type";
            $params['type'] = $type;
        }
        
        if ($value) {
            $sql .= " AND block_value = :value";
            $params['value'] = $value;
        }
        
        if ($tenantId) {
            $sql .= " AND tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->select($sql, $params);
    }
    
    public function checkMultipleBlocks($checks, $tenantId = null)
    {
        $results = [];
        
        foreach ($checks as $type => $value) {
            $results[$type] = $this->isBlocked($type, $value, $tenantId);
        }
        
        return $results;
    }
}
