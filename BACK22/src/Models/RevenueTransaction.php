<?php

namespace SmartCast\Models;

/**
 * Revenue Transaction Model
 */
class RevenueTransaction extends BaseModel
{
    protected $table = 'revenue_transactions';
    protected $fillable = [
        'transaction_id', 'tenant_id', 'event_id', 'gross_amount', 'platform_fee',
        'processing_fee', 'referrer_commission', 'net_tenant_amount', 'fee_rule_snapshot',
        'distribution_status'
    ];
    
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    
    /**
     * Get tenant's actual fee rules from their subscription plan
     */
    private function getTenantFeeRules($tenantId)
    {
        $sql = "
            SELECT 
                fr.percentage_rate as platform_fee_percentage,
                fr.id as fee_rule_id,
                fr.name as fee_rule_name
            FROM tenants t
            LEFT JOIN subscription_plans sp ON t.current_plan_id = sp.id
            LEFT JOIN fee_rules fr ON sp.fee_rule_id = fr.id
            WHERE t.id = :tenant_id
            LIMIT 1
        ";
        
        $result = $this->db->selectOne($sql, ['tenant_id' => $tenantId]);
        
        if ($result && $result['platform_fee_percentage']) {
            return [
                'platform_fee_percentage' => floatval($result['platform_fee_percentage']),
                'processing_fee_percentage' => 0.0,  // Hubtel charges post-payment
                'processing_fee_fixed' => 0.00,      // Hubtel charges post-payment
                'referrer_commission_percentage' => 0.0,
                'fee_rule_id' => $result['fee_rule_id'],
                'fee_rule_name' => $result['fee_rule_name']
            ];
        }
        
        // Fallback to global default (35%)
        return [
            'platform_fee_percentage' => 35.0,
            'processing_fee_percentage' => 0.0,  // Hubtel charges post-payment
            'processing_fee_fixed' => 0.00,      // Hubtel charges post-payment
            'referrer_commission_percentage' => 0.0,
            'fee_rule_id' => null,
            'fee_rule_name' => 'Global Default (35%)'
        ];
    }
    
    public function createRevenueTransaction($transactionId, $tenantId, $eventId, $grossAmount, $feeRules = null)
    {
        // If no fee rules provided, get tenant's actual fee rules from their plan
        if ($feeRules === null) {
            $feeRules = $this->getTenantFeeRules($tenantId);
        }
        
        // Calculate fees based on rules
        $feeCalculation = $this->calculateFees($grossAmount, $feeRules);
        
        return $this->create([
            'transaction_id' => $transactionId,
            'tenant_id' => $tenantId,
            'event_id' => $eventId,
            'gross_amount' => $grossAmount,
            'platform_fee' => $feeCalculation['platform_fee'],
            'processing_fee' => $feeCalculation['processing_fee'],
            'referrer_commission' => $feeCalculation['referrer_commission'],
            'net_tenant_amount' => $feeCalculation['net_tenant_amount'],
            'fee_rule_snapshot' => json_encode($feeCalculation['rules_applied']),
            'distribution_status' => self::STATUS_COMPLETED
        ]);
    }
    
    public function calculateFees($grossAmount, $feeRules = null)
    {
        // Default fee structure if no rules provided
        $defaultRules = [
            'platform_fee_percentage' => 5.0, // 5%
            'processing_fee_percentage' => 0.0, // 0% - Hubtel charges post-payment
            'processing_fee_fixed' => 0.00, // GH₵0.00 - Hubtel charges post-payment
            'referrer_commission_percentage' => 0.0 // 0% (if no referrer)
        ];
        
        $rules = $feeRules ?? $defaultRules;
        
        // Calculate and round fees first
        $platformFee = round(($grossAmount * $rules['platform_fee_percentage']) / 100, 2);
        $processingFee = round((($grossAmount * $rules['processing_fee_percentage']) / 100) + $rules['processing_fee_fixed'], 2);
        $referrerCommission = round(($grossAmount * $rules['referrer_commission_percentage']) / 100, 2);
        
        // Calculate net amount from rounded fees to ensure accuracy
        $netTenantAmount = round($grossAmount - $platformFee - $processingFee - $referrerCommission, 2);
        
        return [
            'platform_fee' => $platformFee,
            'processing_fee' => $processingFee,
            'referrer_commission' => $referrerCommission,
            'net_tenant_amount' => $netTenantAmount,
            'rules_applied' => $rules
        ];
    }
    
    public function getRevenueByTenant($tenantId, $startDate = null, $endDate = null)
    {
        $sql = "
            SELECT 
                SUM(gross_amount) as total_gross,
                SUM(platform_fee) as total_platform_fee,
                SUM(processing_fee) as total_processing_fee,
                SUM(referrer_commission) as total_referrer_commission,
                SUM(net_tenant_amount) as total_net_amount,
                COUNT(*) as transaction_count
            FROM {$this->table}
            WHERE tenant_id = :tenant_id
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($startDate) {
            $sql .= " AND created_at >= :start_date";
            $params['start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND created_at <= :end_date";
            $params['end_date'] = $endDate;
        }
        
        return $this->db->selectOne($sql, $params);
    }
    
    public function getRevenueByEvent($eventId)
    {
        $sql = "
            SELECT 
                rt.*,
                t.name as tenant_name,
                e.name as event_name
            FROM {$this->table} rt
            INNER JOIN tenants t ON rt.tenant_id = t.id
            INNER JOIN events e ON rt.event_id = e.id
            WHERE rt.event_id = :event_id
            ORDER BY rt.created_at DESC
        ";
        
        return $this->db->select($sql, ['event_id' => $eventId]);
    }
    
    public function getPlatformRevenue($startDate = null, $endDate = null)
    {
        $sql = "
            SELECT 
                SUM(platform_fee) as total_platform_fee,
                SUM(processing_fee) as total_processing_fee,
                SUM(gross_amount) as total_gross_amount,
                COUNT(*) as transaction_count,
                COUNT(DISTINCT tenant_id) as unique_tenants,
                COUNT(DISTINCT event_id) as unique_events
            FROM {$this->table}
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($startDate) {
            $sql .= " AND created_at >= :start_date";
            $params['start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND created_at <= :end_date";
            $params['end_date'] = $endDate;
        }
        
        return $this->db->selectOne($sql, $params);
    }
    
    public function getTopEarningTenants($limit = 10, $startDate = null, $endDate = null)
    {
        $sql = "
            SELECT 
                rt.tenant_id,
                t.name as tenant_name,
                t.email as tenant_email,
                SUM(rt.net_tenant_amount) as total_earnings,
                SUM(rt.gross_amount) as total_gross,
                COUNT(*) as transaction_count
            FROM {$this->table} rt
            INNER JOIN tenants t ON rt.tenant_id = t.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if ($startDate) {
            $sql .= " AND rt.created_at >= :start_date";
            $params['start_date'] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND rt.created_at <= :end_date";
            $params['end_date'] = $endDate;
        }
        
        $sql .= "
            GROUP BY rt.tenant_id, t.name, t.email
            ORDER BY total_earnings DESC
            LIMIT :limit
        ";
        
        $params['limit'] = $limit;
        
        return $this->db->select($sql, $params);
    }
    
    public function getRevenueAnalytics($tenantId = null, $period = 'month')
    {
        $dateFormat = $period === 'day' ? '%Y-%m-%d' : 
                     ($period === 'week' ? '%Y-%u' : '%Y-%m');
        
        $sql = "
            SELECT 
                DATE_FORMAT(created_at, '{$dateFormat}') as period,
                SUM(gross_amount) as gross_amount,
                SUM(platform_fee) as platform_fee,
                SUM(processing_fee) as processing_fee,
                SUM(net_tenant_amount) as net_amount,
                COUNT(*) as transaction_count
            FROM {$this->table}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 " . strtoupper($period) . ")
        ";
        
        $params = [];
        
        if ($tenantId) {
            $sql .= " AND tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        $sql .= "
            GROUP BY period
            ORDER BY period ASC
        ";
        
        return $this->db->select($sql, $params);
    }
    
    public function getFailedDistributions()
    {
        return $this->findAll(['distribution_status' => self::STATUS_FAILED], 'created_at DESC');
    }
    
    public function retryFailedDistribution($id)
    {
        $revenueTransaction = $this->find($id);
        
        if (!$revenueTransaction || $revenueTransaction['distribution_status'] !== self::STATUS_FAILED) {
            throw new \Exception('Invalid revenue transaction for retry');
        }
        
        try {
            // Update tenant balance
            $balanceModel = new TenantBalance();
            $balanceModel->addEarnings($revenueTransaction['tenant_id'], $revenueTransaction['net_tenant_amount']);
            
            // Mark as completed
            $this->update($id, ['distribution_status' => self::STATUS_COMPLETED]);
            
            return true;
        } catch (\Exception $e) {
            error_log('Failed to retry revenue distribution: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getRevenueBreakdown($tenantId, $eventId = null)
    {
        $sql = "
            SELECT 
                DATE(created_at) as date,
                SUM(gross_amount) as daily_gross,
                SUM(net_tenant_amount) as daily_net,
                COUNT(*) as daily_transactions
            FROM {$this->table}
            WHERE tenant_id = :tenant_id
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($eventId) {
            $sql .= " AND event_id = :event_id";
            $params['event_id'] = $eventId;
        }
        
        $sql .= "
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ";
        
        return $this->db->select($sql, $params);
    }
    
    public function updateDistributionStatus($id, $status)
    {
        $validStatuses = [self::STATUS_PENDING, self::STATUS_COMPLETED, self::STATUS_FAILED];
        
        if (!in_array($status, $validStatuses)) {
            throw new \Exception('Invalid distribution status');
        }
        
        return $this->update($id, ['distribution_status' => $status]);
    }
}
