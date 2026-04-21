<?php

namespace SmartCast\Models;

/**
 * Payout Model
 */
class Payout extends BaseModel
{
    protected $table = 'payouts';
    protected $fillable = [
        'tenant_id', 'initiated_by', 'payout_id', 'amount', 'processing_fee', 'net_amount', 
        'payout_method', 'payout_method_id', 'payout_type', 'recipient_details',
        'status', 'provider_reference', 'failure_reason', 'processed_at',
        'approved_at', 'approved_by'
    ];
    
    const STATUS_PENDING = 'pending';           // Organizer requested, waiting for admin approval
    const STATUS_APPROVED = 'approved';         // Admin approved, ready for processing
    const STATUS_PROCESSING = 'processing';     // Currently being processed
    const STATUS_PAID = 'paid';                // Successfully paid out
    const STATUS_FAILED = 'failed';            // Processing failed
    const STATUS_REJECTED = 'rejected';        // Admin rejected the request
    const STATUS_CANCELLED = 'cancelled';      // Cancelled by organizer or admin
    
    // Legacy status for backward compatibility
    const STATUS_QUEUED = 'pending';
    const STATUS_SUCCESS = 'paid';
    
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_MOBILE_MONEY = 'mobile_money';
    const METHOD_PAYPAL = 'paypal';
    
    public function createPayout($tenantId, $amount, $method, $recipientDetails)
    {
        $balanceModel = new TenantBalance();
        
        // Check if tenant has sufficient balance
        if (!$balanceModel->canRequestPayout($tenantId, $amount)) {
            throw new \Exception('Insufficient balance for payout request');
        }
        
        // Check minimum payout amount
        $minimumAmount = $balanceModel->getMinimumPayoutAmount($tenantId);
        if ($amount < $minimumAmount) {
            throw new \Exception("Minimum payout amount is $minimumAmount");
        }
        
        // Generate unique payout ID
        $payoutId = $this->generatePayoutId();
        
        // Create payout record with pending status (awaiting admin approval)
        $id = $this->create([
            'tenant_id' => $tenantId,
            'payout_id' => $payoutId,
            'amount' => $amount,
            'payout_method' => $method,
            'recipient_details' => json_encode($recipientDetails),
            'status' => self::STATUS_PENDING
        ]);
        
        // Reserve the amount from available balance (move available → pending)
        $balanceModel->reserveForPayout($tenantId, $amount);
        
        return $id;
    }
    
    /**
     * Approve payout request (Super Admin only)
     */
    public function approvePayout($payoutId, $adminId, $notes = null)
    {
        $payout = $this->find($payoutId);
        
        if (!$payout || $payout['status'] !== self::STATUS_PENDING) {
            throw new \Exception('Invalid payout or payout not in pending status');
        }
        
        // Update status to approved
        $this->update($payoutId, [
            'status' => self::STATUS_APPROVED,
            'approved_by' => $adminId,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
        
        return true;
    }
    
    /**
     * Reject payout request (Super Admin only)
     */
    public function rejectPayout($payoutId, $adminId, $reason)
    {
        $payout = $this->find($payoutId);
        
        if (!$payout || $payout['status'] !== self::STATUS_PENDING) {
            throw new \Exception('Invalid payout or payout not in pending status');
        }
        
        // Update status to rejected
        $this->update($payoutId, [
            'status' => self::STATUS_REJECTED,
            'approved_by' => $adminId,
            'processed_at' => date('Y-m-d H:i:s'),
            'failure_reason' => $reason
        ]);
        
        // Restore balance to tenant (move from pending back to available)
        $balanceModel = new TenantBalance();
        $balanceModel->rejectPayout($payout['tenant_id'], $payout['amount']);
        
        return true;
    }
    
    /**
     * Process approved payout
     */
    public function processPayout($payoutId)
    {
        $payout = $this->find($payoutId);
        
        if (!$payout || $payout['status'] !== self::STATUS_APPROVED) {
            throw new \Exception('Invalid payout or payout not approved for processing');
        }
        
        // Update status to processing
        $this->update($payoutId, ['status' => self::STATUS_PROCESSING]);
        
        try {
            // Process based on method
            $result = $this->processPayoutByMethod($payout);
            
            if ($result['success']) {
                // Mark as paid and move balance from pending to paid
                $this->update($payoutId, [
                    'status' => self::STATUS_PAID,
                    'provider_reference' => $result['reference'] ?? null,
                    'processed_at' => date('Y-m-d H:i:s')
                ]);
                
                // Move money from pending to total_paid
                $balanceModel = new TenantBalance();
                $balanceModel->processPayout($payout['tenant_id'], $payout['amount']);
                
                return true;
            } else {
                // Mark as failed and restore balance
                $this->handlePayoutFailure($payoutId, $result['error'] ?? 'Unknown error');
                return false;
            }
            
        } catch (\Exception $e) {
            $this->handlePayoutFailure($payoutId, $e->getMessage());
            return false;
        }
    }
    
    private function processPayoutByMethod($payout)
    {
        $recipientDetails = json_decode($payout['recipient_details'], true);
        
        switch ($payout['payout_method']) {
            case self::METHOD_BANK_TRANSFER:
                return $this->processBankTransfer($payout['amount'], $recipientDetails);
                
            case self::METHOD_MOBILE_MONEY:
                return $this->processMobileMoney($payout['amount'], $recipientDetails);
                
            case self::METHOD_PAYPAL:
                return $this->processPayPal($payout['amount'], $recipientDetails);
                
            default:
                throw new \Exception('Unsupported payout method');
        }
    }
    
    private function processBankTransfer($amount, $details)
    {
        // Simulate bank transfer processing
        // In real implementation, integrate with banking API
        
        if (empty($details['account_number']) || empty($details['bank_code'])) {
            return ['success' => false, 'error' => 'Invalid bank details'];
        }
        
        // Simulate processing delay and success
        sleep(1);
        
        return [
            'success' => true,
            'reference' => 'BT_' . time() . '_' . mt_rand(1000, 9999)
        ];
    }
    
    private function processMobileMoney($amount, $details)
    {
        // Simulate mobile money processing
        // In real implementation, integrate with mobile money API
        
        if (empty($details['phone_number'])) {
            return ['success' => false, 'error' => 'Invalid phone number'];
        }
        
        return [
            'success' => true,
            'reference' => 'MM_' . time() . '_' . mt_rand(1000, 9999)
        ];
    }
    
    private function processPayPal($amount, $details)
    {
        // Simulate PayPal processing
        // In real implementation, integrate with PayPal API
        
        if (empty($details['email'])) {
            return ['success' => false, 'error' => 'Invalid PayPal email'];
        }
        
        return [
            'success' => true,
            'reference' => 'PP_' . time() . '_' . mt_rand(1000, 9999)
        ];
    }
    
    private function handlePayoutFailure($payoutId, $reason)
    {
        $payout = $this->find($payoutId);
        
        // Update payout status
        $this->update($payoutId, [
            'status' => self::STATUS_FAILED,
            'failure_reason' => $reason
        ]);
        
        // Restore balance (move from pending back to available)
        $balanceModel = new TenantBalance();
        $balanceModel->rejectPayout($payout['tenant_id'], $payout['amount']);
    }
    
    public function cancelPayout($payoutId, $reason = null)
    {
        $payout = $this->find($payoutId);
        
        if (!$payout || !in_array($payout['status'], [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_PROCESSING])) {
            throw new \Exception('Cannot cancel payout');
        }
        
        // Update status
        $this->update($payoutId, [
            'status' => self::STATUS_CANCELLED,
            'failure_reason' => $reason,
            'cancelled_at' => date('Y-m-d H:i:s')
        ]);
        
        // Restore balance if not already processed (move from pending back to available)
        if (in_array($payout['status'], [self::STATUS_PENDING, self::STATUS_APPROVED])) {
            $balanceModel = new TenantBalance();
            $balanceModel->rejectPayout($payout['tenant_id'], $payout['amount']);
        }
        
        return true;
    }
    
    /**
     * Reverse payout from processed back to approved (Super Admin only)
     */
    public function reverseProcessedToApproved($payoutId, $adminId, $reason)
    {
        $payout = $this->find($payoutId);
        
        if (!$payout || $payout['status'] !== self::STATUS_PAID) {
            throw new \Exception('Invalid payout or payout not in paid status');
        }
        
        // Update status back to approved
        $this->update($payoutId, [
            'status' => self::STATUS_APPROVED,
            'processed_at' => null,
            'provider_reference' => null,
            'failure_reason' => "Reversed by admin: $reason"
        ]);
        
        // Move balance from total_paid back to pending
        $balanceModel = new TenantBalance();
        $balanceModel->reverseProcessedPayout($payout['tenant_id'], $payout['amount']);
        
        return true;
    }
    
    /**
     * Reverse payout from approved back to pending (Super Admin only)
     */
    public function reverseApprovedToPending($payoutId, $adminId, $reason)
    {
        $payout = $this->find($payoutId);
        
        if (!$payout || $payout['status'] !== self::STATUS_APPROVED) {
            throw new \Exception('Invalid payout or payout not in approved status');
        }
        
        // Update status back to pending
        $this->update($payoutId, [
            'status' => self::STATUS_PENDING,
            'approved_by' => null,
            'approved_at' => null,
            'failure_reason' => "Reversed by admin: $reason"
        ]);
        
        // Balance stays in pending (no balance change needed)
        
        return true;
    }
    
    public function getPayoutsByTenant($tenantId, $status = null)
    {
        $conditions = ['tenant_id' => $tenantId];
        
        if ($status) {
            $conditions['status'] = $status;
        }
        
        return $this->findAll($conditions, 'created_at DESC');
    }
    
    /**
     * Get payout statistics
     */
    public function getPayoutStats($tenantId)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_count,
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total_paid,
                SUM(CASE WHEN status IN ('pending', 'approved', 'processing') THEN amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as awaiting_approval,
                AVG(CASE WHEN status = 'paid' THEN amount ELSE NULL END) as avg_amount
            FROM payouts 
            WHERE tenant_id = :tenant_id
        ";
        
        return $this->db->selectOne($sql, ['tenant_id' => $tenantId]);
    }
    
    /**
     * Get pending payouts for super admin approval
     */
    public function getPendingPayouts()
    {
        $sql = "
            SELECT 
                p.*,
                t.name as tenant_name,
                t.email as tenant_email,
                pm.method_name,
                pm.account_details,
                pm.method_type
            FROM payouts p
            INNER JOIN tenants t ON p.tenant_id = t.id
            LEFT JOIN payout_methods pm ON p.payout_method_id = pm.id
            WHERE p.status = 'pending'
            ORDER BY p.created_at ASC
        ";
        
        return $this->db->select($sql);
    }
    
    /**
     * Get approved payouts ready for processing
     */
    public function getApprovedPayouts()
    {
        $sql = "
            SELECT 
                p.*,
                t.name as tenant_name,
                t.email as tenant_email,
                pm.method_name,
                pm.account_details,
                pm.method_type
            FROM payouts p
            INNER JOIN tenants t ON p.tenant_id = t.id
            LEFT JOIN payout_methods pm ON p.payout_method_id = pm.id
            WHERE p.status = 'approved'
            ORDER BY p.approved_at ASC
        ";
        
        return $this->db->select($sql);
    }
    
    /**
     * Get payout statistics for super admin
     */
    public function getPayoutStatistics()
    {
        $sql = "
            SELECT 
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total_paid,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_approval,
                SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as approved_pending,
                SUM(CASE WHEN status = 'processing' THEN amount ELSE 0 END) as processing_amount,
                COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing_count,
                COUNT(CASE WHEN status = 'paid' AND MONTH(processed_at) = MONTH(NOW()) AND YEAR(processed_at) = YEAR(NOW()) THEN 1 END) as this_month_count,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                AVG(CASE WHEN status = 'paid' THEN amount ELSE NULL END) as avg_payout
            FROM payouts
        ";
        
        return $this->db->selectOne($sql);
    }
    
    /**
     * Get recent payouts for super admin
     */
    public function getRecentPayouts($limit = 50)
    {
        $sql = "
            SELECT 
                p.*,
                t.name as tenant_name,
                t.email as tenant_email,
                pm.method_name,
                pm.method_type
            FROM payouts p
            INNER JOIN tenants t ON p.tenant_id = t.id
            LEFT JOIN payout_methods pm ON p.payout_method_id = pm.id
            ORDER BY p.created_at DESC
            LIMIT :limit
        ";
        
        return $this->db->select($sql, ['limit' => $limit]);
    }
    
    /**
     * Get payout with full details for super admin
     */
    public function getPayoutWithDetails($payoutId)
    {
        $sql = "
            SELECT 
                p.*,
                t.name as tenant_name,
                t.email as tenant_email,
                t.phone as tenant_phone,
                pm.method_name,
                pm.method_type,
                pm.account_details,
                pm.is_verified as method_verified
            FROM payouts p
            INNER JOIN tenants t ON p.tenant_id = t.id
            LEFT JOIN payout_methods pm ON p.payout_method_id = pm.id
            WHERE p.id = :payout_id
        ";
        
        return $this->db->selectOne($sql, ['payout_id' => $payoutId]);
    }
    
    private function generatePayoutId()
    {
        return 'PO_' . date('Ymd') . '_' . strtoupper(uniqid());
    }
    
    public function retryFailedPayout($payoutId)
    {
        $payout = $this->find($payoutId);
        
        if (!$payout || $payout['status'] !== self::STATUS_FAILED) {
            throw new \Exception('Invalid payout for retry');
        }
        
        // Reset status to queued
        $this->update($payoutId, [
            'status' => self::STATUS_QUEUED,
            'failure_reason' => null
        ]);
        
        return true;
    }
}
