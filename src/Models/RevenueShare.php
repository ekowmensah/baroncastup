<?php

namespace SmartCast\Models;

/**
 * Revenue Share Model
 */
class RevenueShare extends BaseModel
{
    protected $table = 'revenue_shares';
    protected $fillable = [
        'transaction_id', 'tenant_id', 'amount', 'fee_rule_id'
    ];
    
    public function calculateAndCreateShare($transactionId, $transactionAmount, $tenantId, $eventId = null)
    {
        $feeRuleModel = new FeeRule();
        
        // Get applicable fee rule (now with event ID for event-specific rules)
        $feeRule = $feeRuleModel->getApplicableFeeRule($tenantId, $eventId);
        
        if (!$feeRule) {
            error_log("No applicable fee rule found for tenant: $tenantId, event: $eventId");
            return null;
        }
        
        error_log("Using fee rule: " . print_r($feeRule, true));
        
        // Calculate share amount
        $shareAmount = $this->calculateShareAmount($transactionAmount, $feeRule);
        
        error_log("Calculated platform fee: $shareAmount for transaction amount: $transactionAmount");
        
        // Create revenue share record
        $revenueShareId = $this->create([
            'transaction_id' => $transactionId,
            'tenant_id' => $tenantId,
            'amount' => $shareAmount,
            'fee_rule_id' => $feeRule['id']
        ]);
        
        if ($revenueShareId) {
            // Return the created record with the calculated amount
            return [
                'id' => $revenueShareId,
                'transaction_id' => $transactionId,
                'tenant_id' => $tenantId,
                'amount' => $shareAmount,
                'fee_rule_id' => $feeRule['id']
            ];
        }
        
        return null;
    }
    
    private function calculateShareAmount($transactionAmount, $feeRule)
    {
        switch ($feeRule['rule_type']) {
            case 'percentage':
                return $transactionAmount * ($feeRule['percentage_rate'] / 100);
                
            case 'fixed':
                return $feeRule['fixed_amount'];
                
            case 'blend':
                $percentageAmount = $transactionAmount * ($feeRule['percentage_rate'] / 100);
                return $percentageAmount + $feeRule['fixed_amount'];
                
            default:
                return 0;
        }
    }
    
    public function getTenantRevenue($tenantId, $startDate = null, $endDate = null)
    {
        $sql = "
            SELECT 
                COUNT(*) as transaction_count,
                SUM(rs.amount) as total_revenue,
                AVG(rs.amount) as avg_revenue_per_transaction,
                MIN(rs.created_at) as first_transaction,
                MAX(rs.created_at) as last_transaction
            FROM {$this->table} rs
            WHERE rs.tenant_id = :tenant_id
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($startDate && $endDate) {
            $sql .= " AND rs.created_at BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        
        return $this->db->selectOne($sql, $params);
    }
    
    public function getRevenueByPeriod($tenantId, $period = 'day', $limit = 30)
    {
        switch($period) {
            case 'hour':
                $dateFormat = '%Y-%m-%d %H:00:00';
                break;
            case 'day':
                $dateFormat = '%Y-%m-%d';
                break;
            case 'week':
                $dateFormat = '%Y-%u';
                break;
            case 'month':
                $dateFormat = '%Y-%m';
                break;
            default:
                $dateFormat = '%Y-%m-%d';
                break;
        }
        
        $sql = "
            SELECT 
                DATE_FORMAT(rs.created_at, '{$dateFormat}') as period,
                COUNT(*) as transaction_count,
                SUM(rs.amount) as revenue
            FROM {$this->table} rs
            WHERE rs.tenant_id = :tenant_id
            GROUP BY period
            ORDER BY period DESC
            LIMIT {$limit}
        ";
        
        return $this->db->select($sql, ['tenant_id' => $tenantId]);
    }
    
    public function getTopRevenueEvents($tenantId, $limit = 10)
    {
        $sql = "
            SELECT 
                e.id,
                e.name,
                e.code,
                COUNT(rs.id) as transaction_count,
                SUM(rs.amount) as total_revenue
            FROM {$this->table} rs
            INNER JOIN transactions t ON rs.transaction_id = t.id
            INNER JOIN events e ON t.event_id = e.id
            WHERE rs.tenant_id = :tenant_id
            GROUP BY e.id
            ORDER BY total_revenue DESC
            LIMIT {$limit}
        ";
        
        return $this->db->select($sql, ['tenant_id' => $tenantId]);
    }
    
    public function getPlatformRevenue($startDate = null, $endDate = null)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_transactions,
                SUM(rs.amount) as total_revenue,
                COUNT(DISTINCT rs.tenant_id) as active_tenants,
                AVG(rs.amount) as avg_revenue_per_transaction
            FROM {$this->table} rs
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($startDate && $endDate) {
            $sql .= " AND rs.created_at BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        
        return $this->db->selectOne($sql, $params);
    }
    
    public function getRevenueSharesByTransaction($transactionId)
    {
        $sql = "
            SELECT rs.*, fr.rule_type, fr.percentage_rate, fr.fixed_amount
            FROM {$this->table} rs
            LEFT JOIN fee_rules fr ON rs.fee_rule_id = fr.id
            WHERE rs.transaction_id = :transaction_id
        ";
        
        return $this->db->select($sql, ['transaction_id' => $transactionId]);
    }
    
    public function updateTenantBalance($tenantId)
    {
        $balanceModel = new TenantBalance();
        
        // Calculate total earned
        $revenue = $this->getTenantRevenue($tenantId);
        $totalEarned = $revenue['total_revenue'] ?? 0;
        
        // Update tenant balance
        return $balanceModel->updateBalance($tenantId, $totalEarned);
    }
}
