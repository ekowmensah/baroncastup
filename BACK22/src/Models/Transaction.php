<?php

namespace SmartCast\Models;

/**
 * Transaction Model
 */
class Transaction extends BaseModel
{
    protected $table = 'transactions';
    protected $fillable = [
        'tenant_id', 'event_id', 'contestant_id', 'category_id', 'bundle_id', 'amount',
        'msisdn', 'status', 'provider', 'provider_reference', 'coupon_code',
        'referral_code', 'failure_reason'
    ];
    
    public function createTransaction($data)
    {
        // Generate unique transaction ID if not provided
        if (!isset($data['provider_reference'])) {
            $data['provider_reference'] = $this->generateTransactionId();
        }
        
        // Process coupon if provided
        if (!empty($data['coupon_code'])) {
            $couponModel = new Coupon();
            $couponResult = $couponModel->applyCoupon(
                $data['coupon_code'],
                null, // Transaction ID will be set after creation
                $data['tenant_id'],
                $data['event_id'] ?? null,
                $data['amount']
            );
            
            if ($couponResult['success']) {
                $data['amount'] = $couponResult['final_amount'];
            }
        }
        
        $transactionId = $this->create($data);
        
        // Process referral if provided
        if (!empty($data['referral_code']) && $transactionId) {
            $referralModel = new Referral();
            $referralModel->processReferral(
                $data['referral_code'],
                $transactionId,
                $data['tenant_id'],
                $data['amount']
            );
        }
        
        return $transactionId;
    }
    
    public function updateStatus($transactionId, $status, $failureReason = null)
    {
        $data = ['status' => $status];
        
        if ($failureReason) {
            $data['failure_reason'] = $failureReason;
        }
        
        return $this->update($transactionId, $data);
    }
    
    public function getTransactionsByEvent($eventId, $limit = null)
    {
        $sql = "
            SELECT t.*, c.name as contestant_name, c.contestant_code,
                   vb.name as bundle_name, vb.votes as bundle_votes
            FROM transactions t
            INNER JOIN contestants c ON t.contestant_id = c.id
            INNER JOIN vote_bundles vb ON t.bundle_id = vb.id
            WHERE t.event_id = :event_id
            ORDER BY t.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->select($sql, ['event_id' => $eventId]);
    }
    
    public function getSuccessfulTransactions($eventId = null, $tenantId = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'success'";
        $params = [];
        
        if ($eventId) {
            $sql .= " AND event_id = :event_id";
            $params['event_id'] = $eventId;
        }
        
        if ($tenantId) {
            $sql .= " AND tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->select($sql, $params);
    }
    
    public function getRevenueStats($tenantId, $startDate = null, $endDate = null)
    {
        $sql = "
            SELECT 
                COUNT(*) as total_transactions,
                COUNT(CASE WHEN status = 'success' THEN 1 END) as successful_transactions,
                COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_transactions,
                COALESCE(SUM(CASE WHEN status = 'success' THEN amount END), 0) as total_revenue,
                AVG(CASE WHEN status = 'success' THEN amount END) as avg_transaction_amount
            FROM {$this->table}
            WHERE tenant_id = :tenant_id
        ";
        
        $params = ['tenant_id' => $tenantId];
        
        if ($startDate && $endDate) {
            $sql .= " AND created_at BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }
        
        return $this->db->selectOne($sql, $params);
    }
    
    public function findByReference($reference)
    {
        return $this->findAll(['provider_reference' => $reference]);
    }
    
    private function generateTransactionId()
    {
        return 'TXN_' . time() . '_' . mt_rand(1000, 9999);
    }
    
    public function getTransactionsByMsisdn($msisdn, $eventId = null)
    {
        $conditions = ['msisdn' => $msisdn];
        
        if ($eventId) {
            $conditions['event_id'] = $eventId;
        }
        
        return $this->findAll($conditions, 'created_at DESC');
    }
}
