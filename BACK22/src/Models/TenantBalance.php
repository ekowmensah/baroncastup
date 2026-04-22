<?php

namespace SmartCast\Models;

/**
 * Tenant Balance Model
 */
class TenantBalance extends BaseModel
{
    protected $table = 'tenant_balances';
    protected $fillable = [
        'tenant_id', 'available', 'pending', 'total_earned', 'total_paid'
    ];
    
    public function getBalance($tenantId)
    {
        $balance = $this->findAll(['tenant_id' => $tenantId], null, 1);
        
        if (empty($balance)) {
            // Create initial balance record
            $this->create([
                'tenant_id' => $tenantId,
                'available' => 0.00,
                'pending' => 0.00,
                'total_earned' => 0.00,
                'total_paid' => 0.00
            ]);
            
            return $this->getBalance($tenantId);
        }
        
        $balanceData = $balance[0];
        
        // Get pending breakdown by payout status
        $pendingBreakdown = $this->getPendingBreakdown($tenantId);
        
        // Merge the breakdown into balance data
        return array_merge($balanceData, $pendingBreakdown);
    }
    
    private function getPendingBreakdown($tenantId)
    {
        try {
            $db = new \SmartCast\Core\Database();
            
            // Get pending amounts by status
            $pendingApproval = $db->query("
                SELECT COALESCE(SUM(amount), 0) as total 
                FROM payouts 
                WHERE tenant_id = " . intval($tenantId) . " AND status = 'pending'
            ")->fetch()['total'] ?? 0;
            
            $approvedPending = $db->query("
                SELECT COALESCE(SUM(amount), 0) as total 
                FROM payouts 
                WHERE tenant_id = " . intval($tenantId) . " AND status = 'approved'
            ")->fetch()['total'] ?? 0;
            
            $processing = $db->query("
                SELECT COALESCE(SUM(amount), 0) as total 
                FROM payouts 
                WHERE tenant_id = " . intval($tenantId) . " AND status = 'processing'
            ")->fetch()['total'] ?? 0;
            
            return [
                'pending_approval' => floatval($pendingApproval),
                'approved_pending' => floatval($approvedPending),
                'processing' => floatval($processing)
            ];
            
        } catch (\Exception $e) {
            error_log('Get pending breakdown error: ' . $e->getMessage());
            return [
                'pending_approval' => 0,
                'approved_pending' => 0,
                'processing' => 0
            ];
        }
    }
    
    public function updateBalance($tenantId, $totalEarned, $totalPaid = null)
    {
        $currentBalance = $this->getBalance($tenantId);
        
        $newAvailable = $totalEarned - ($totalPaid ?? $currentBalance['total_paid']);
        
        $updateData = [
            'available' => max(0, $newAvailable),
            'total_earned' => $totalEarned
        ];
        
        if ($totalPaid !== null) {
            $updateData['total_paid'] = $totalPaid;
        }
        
        return $this->update($currentBalance['id'], $updateData);
    }
    
    public function addEarnings($tenantId, $amount)
    {
        $balance = $this->getBalance($tenantId);
        
        return $this->update($balance['id'], [
            'available' => $balance['available'] + $amount,
            'total_earned' => $balance['total_earned'] + $amount
        ]);
    }
    
    public function addToBalance($tenantId, $amount)
    {
        $balance = $this->getBalance($tenantId);
        
        return $this->update($balance['id'], [
            'available' => $balance['available'] + $amount
        ]);
    }
    
    public function reversePayout($tenantId, $amount)
    {
        $balance = $this->getBalance($tenantId);
        
        return $this->update($balance['id'], [
            'available' => $balance['available'] + $amount,
            'total_paid' => max(0, $balance['total_paid'] - $amount)
        ]);
    }
    
    public function addPendingAmount($tenantId, $amount)
    {
        $balance = $this->getBalance($tenantId);
        
        return $this->update($balance['id'], [
            'pending' => $balance['pending'] + $amount
        ]);
    }
    
    public function movePendingToAvailable($tenantId, $amount = null)
    {
        $balance = $this->getBalance($tenantId);
        
        $amountToMove = $amount ?? $balance['pending'];
        $amountToMove = min($amountToMove, $balance['pending']);
        
        return $this->update($balance['id'], [
            'available' => $balance['available'] + $amountToMove,
            'pending' => $balance['pending'] - $amountToMove,
            'total_earned' => $balance['total_earned'] + $amountToMove
        ]);
    }
    
    public function reserveForPayout($tenantId, $amount)
    {
        $balance = $this->getBalance($tenantId);
        
        if ($balance['available'] < $amount) {
            throw new \Exception('Insufficient balance for payout');
        }
        
        return $this->update($balance['id'], [
            'available' => $balance['available'] - $amount,
            'pending' => $balance['pending'] + $amount
        ]);
    }
    
    public function processPayout($tenantId, $amount)
    {
        $balance = $this->getBalance($tenantId);
        
        if ($balance['pending'] < $amount) {
            throw new \Exception('Insufficient pending balance for payout processing');
        }
        
        return $this->update($balance['id'], [
            'pending' => $balance['pending'] - $amount,
            'total_paid' => $balance['total_paid'] + $amount
        ]);
    }
    
    public function rejectPayout($tenantId, $amount)
    {
        $balance = $this->getBalance($tenantId);
        
        if ($balance['pending'] < $amount) {
            throw new \Exception('Insufficient pending balance for payout rejection');
        }
        
        return $this->update($balance['id'], [
            'pending' => $balance['pending'] - $amount,
            'available' => $balance['available'] + $amount
        ]);
    }
    
    public function reverseProcessedPayout($tenantId, $amount)
    {
        $balance = $this->getBalance($tenantId);
        
        if ($balance['total_paid'] < $amount) {
            throw new \Exception('Insufficient paid balance for payout reversal');
        }
        
        return $this->update($balance['id'], [
            'total_paid' => $balance['total_paid'] - $amount,
            'pending' => $balance['pending'] + $amount
        ]);
    }
    
    public function getBalanceHistory($tenantId, $limit = 50)
    {
        // This would require a separate balance_history table in a real implementation
        // For now, we'll return the current balance
        return [$this->getBalance($tenantId)];
    }
    
    public function getAllBalances($activeOnly = true)
    {
        $sql = "
            SELECT tb.*, t.name as tenant_name, t.email as tenant_email
            FROM {$this->table} tb
            INNER JOIN tenants t ON tb.tenant_id = t.id
        ";
        
        if ($activeOnly) {
            $sql .= " WHERE t.active = 1";
        }
        
        $sql .= " ORDER BY tb.available DESC";
        
        return $this->db->select($sql);
    }
    
    public function getTotalPlatformBalance()
    {
        $sql = "
            SELECT 
                SUM(available) as total_available,
                SUM(pending) as total_pending,
                SUM(total_earned) as total_earned,
                SUM(total_paid) as total_paid,
                COUNT(*) as tenant_count
            FROM {$this->table}
        ";
        
        return $this->db->selectOne($sql);
    }
    
    public function getBalanceStats($tenantId)
    {
        $balance = $this->getBalance($tenantId);
        
        // Calculate additional stats
        $stats = [
            'current_balance' => $balance,
            'payout_percentage' => $balance['total_earned'] > 0 
                ? ($balance['total_paid'] / $balance['total_earned']) * 100 
                : 0,
            'pending_percentage' => $balance['total_earned'] > 0 
                ? ($balance['pending'] / $balance['total_earned']) * 100 
                : 0
        ];
        
        return $stats;
    }
    
    public function canRequestPayout($tenantId, $amount)
    {
        $balance = $this->getBalance($tenantId);
        return $balance['available'] >= $amount;
    }
    
    public function getMinimumPayoutAmount($tenantId)
    {
        // This could be configurable per tenant or globally
        return 10.00; // Minimum GH₵10 payout
    }
    
    /**
     * Recalculate balance from actual revenue transactions
     * This ensures the balance is accurate based on real data
     */
    public function recalculateBalance($tenantId)
    {
        try {
            // Calculate total earned from revenue transactions
            $revenueQuery = "
                SELECT COALESCE(SUM(net_tenant_amount), 0) as total_earned
                FROM revenue_transactions
                WHERE tenant_id = :tenant_id
                AND distribution_status = 'completed'
            ";
            
            $revenueResult = $this->db->selectOne($revenueQuery, ['tenant_id' => $tenantId]);
            $totalEarned = $revenueResult['total_earned'] ?? 0;
            
            // Calculate total successfully paid out
            $payoutQuery = "
                SELECT COALESCE(SUM(amount), 0) as total_paid
                FROM payouts
                WHERE tenant_id = :tenant_id
                AND status = 'paid'
            ";
            
            $payoutResult = $this->db->selectOne($payoutQuery, ['tenant_id' => $tenantId]);
            $totalPaid = $payoutResult['total_paid'] ?? 0;
            
            // Calculate pending amount (queued or processing payouts)
            $pendingQuery = "
                SELECT COALESCE(SUM(amount), 0) as pending
                FROM payouts
                WHERE tenant_id = :tenant_id
                AND status IN ('pending', 'approved', 'processing')
            ";
            
            $pendingResult = $this->db->selectOne($pendingQuery, ['tenant_id' => $tenantId]);
            $pending = $pendingResult['pending'] ?? 0;
            
            // Calculate available balance
            $available = $totalEarned - $totalPaid - $pending;
            $available = max(0, $available);
            
            // Get current balance record
            $currentBalance = $this->findAll(['tenant_id' => $tenantId], null, 1);
            
            if (empty($currentBalance)) {
                // Create new balance record
                return $this->create([
                    'tenant_id' => $tenantId,
                    'available' => $available,
                    'pending' => $pending,
                    'total_earned' => $totalEarned,
                    'total_paid' => $totalPaid
                ]);
            } else {
                // Update existing balance record
                return $this->update($currentBalance[0]['id'], [
                    'available' => $available,
                    'pending' => $pending,
                    'total_earned' => $totalEarned,
                    'total_paid' => $totalPaid
                ]);
            }
            
        } catch (\Exception $e) {
            error_log('Recalculate balance error: ' . $e->getMessage());
            return false;
        }
    }
}
