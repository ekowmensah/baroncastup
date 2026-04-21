<?php

namespace SmartCast\Models;

/**
 * Tenant Model
 */
class Tenant extends BaseModel
{
    protected $table = 'tenants';
    protected $fillable = [
        'name', 'email', 'phone', 'website', 'address', 'plan', 'active', 'verified',
        'ussd_code', 'ussd_enabled', 'ussd_welcome_message',
        'current_plan_id', 'subscription_status', 'trial_ends_at', 'subscription_ends_at'
    ];
    
    public function getActiveTenants()
    {
        return $this->findAll(['active' => 1], 'name ASC');
    }
    
    public function getTenantStats($tenantId)
    {
        $sql = "
            SELECT 
                t.name,
                t.plan,
                COUNT(DISTINCT e.id) as total_events,
                COUNT(DISTINCT CASE WHEN e.status = 'active' THEN e.id END) as active_events,
                COUNT(DISTINCT c.id) as total_contestants,
                COUNT(DISTINCT u.id) as total_users,
                COALESCE(SUM(v.quantity), 0) as total_votes
            FROM tenants t
            LEFT JOIN events e ON t.id = e.tenant_id
            LEFT JOIN contestants c ON e.id = c.event_id AND c.active = 1
            LEFT JOIN users u ON t.id = u.tenant_id AND u.active = 1
            LEFT JOIN votes v ON e.id = v.event_id
            WHERE t.id = :tenant_id
            GROUP BY t.id
        ";
        
        return $this->db->selectOne($sql, ['tenant_id' => $tenantId]);
    }
    
    public function updatePlan($tenantId, $plan)
    {
        return $this->update($tenantId, ['plan' => $plan]);
    }
    
    public function verify($tenantId)
    {
        return $this->update($tenantId, ['verified' => 1]);
    }
    
    public function suspend($tenantId)
    {
        return $this->update($tenantId, ['active' => 0]);
    }
    
    public function reactivate($tenantId)
    {
        return $this->update($tenantId, ['active' => 1]);
    }
    
    /**
     * Get all used USSD codes
     */
    public function getUsedUssdCodes()
    {
        $sql = "SELECT ussd_code FROM {$this->table} WHERE ussd_code IS NOT NULL";
        $results = $this->db->select($sql);
        
        return array_column($results, 'ussd_code');
    }
}
