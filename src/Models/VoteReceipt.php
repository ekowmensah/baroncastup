<?php

namespace SmartCast\Models;

/**
 * Vote Receipt Model
 */
class VoteReceipt extends BaseModel
{
    protected $table = 'vote_receipts';
    protected $fillable = [
        'transaction_id', 'short_code', 'public_hash'
    ];
    
    public function generateReceipt($transactionId)
    {
        try {
            error_log("VoteReceipt: Starting receipt generation for transaction: " . $transactionId);
            
            // Ensure table exists
            $this->ensureTableExists();
            
            // Check if receipt already exists for this transaction
            $existingReceipt = $this->getReceiptByTransaction($transactionId);
            if ($existingReceipt) {
                error_log("VoteReceipt: Receipt already exists for transaction: " . $transactionId);
                return [
                    'id' => $existingReceipt['id'],
                    'short_code' => $existingReceipt['short_code'],
                    'public_hash' => $existingReceipt['public_hash']
                ];
            }
            
            // Generate unique short code
            error_log("VoteReceipt: Generating short code...");
            $shortCode = $this->generateShortCode();
            error_log("VoteReceipt: Generated short code: " . $shortCode);
            
            // Generate public hash for verification
            error_log("VoteReceipt: Generating public hash...");
            $publicHash = $this->generatePublicHash($transactionId, $shortCode);
            error_log("VoteReceipt: Generated public hash: " . substr($publicHash, 0, 20) . "...");
            
            // Create receipt record
            error_log("VoteReceipt: Creating receipt record...");
            $receiptId = $this->create([
                'transaction_id' => $transactionId,
                'short_code' => $shortCode,
                'public_hash' => $publicHash
            ]);
            error_log("VoteReceipt: Receipt created with ID: " . $receiptId);
            
            return [
                'id' => $receiptId,
                'short_code' => $shortCode,
                'public_hash' => $publicHash
            ];
            
        } catch (\Exception $e) {
            error_log("VoteReceipt: Error generating receipt: " . $e->getMessage());
            error_log("VoteReceipt: Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
    
    private function generateShortCode()
    {
        $attempts = 0;
        $maxAttempts = 10;
        
        do {
            // Generate 8-character alphanumeric code
            $shortCode = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
            
            // Check if code already exists
            $existing = $this->findAll(['short_code' => $shortCode], null, 1);
            
            if (empty($existing)) {
                return $shortCode;
            }
            
            $attempts++;
        } while ($attempts < $maxAttempts);
        
        // Fallback: use timestamp-based code
        return strtoupper(substr(uniqid(), -8));
    }
    
    public function generatePublicHash($transactionId, $shortCode)
    {
        // Use deterministic data for consistent hash generation
        $data = $transactionId . '|' . $shortCode . '|' . 'SMARTCAST_RECEIPT_SALT';
        return hash('sha256', $data);
    }
    
    public function verifyReceipt($shortCode, $publicHash = null)
    {
        $conditions = ['short_code' => $shortCode];
        
        if ($publicHash) {
            $conditions['public_hash'] = $publicHash;
        }
        
        $receipt = $this->findAll($conditions, null, 1);
        
        if (empty($receipt)) {
            return [
                'valid' => false,
                'error' => 'Receipt not found'
            ];
        }
        
        $receipt = $receipt[0];
        
        // Get transaction details
        $transactionModel = new Transaction();
        $transaction = $transactionModel->find($receipt['transaction_id']);
        
        if (!$transaction) {
            return [
                'valid' => false,
                'error' => 'Associated transaction not found'
            ];
        }
        
        // Get additional details
        $eventModel = new Event();
        $contestantModel = new Contestant();
        $bundleModel = new VoteBundle();
        
        $event = $eventModel->find($transaction['event_id']);
        $contestant = $contestantModel->find($transaction['contestant_id']);
        $bundle = $bundleModel->find($transaction['bundle_id']);
        
        return [
            'valid' => true,
            'receipt' => $receipt,
            'transaction' => $transaction,
            'event' => $event,
            'contestant' => $contestant,
            'bundle' => $bundle
        ];
    }
    
    public function getReceiptByTransaction($transactionId)
    {
        $receipt = $this->findAll(['transaction_id' => $transactionId], null, 1);
        
        return !empty($receipt) ? $receipt[0] : null;
    }
    
    /**
     * Alias for getReceiptByTransaction for consistency with other models
     */
    public function getByTransactionId($transactionId)
    {
        return $this->getReceiptByTransaction($transactionId);
    }
    
    /**
     * Find receipt by short code
     */
    public function findByShortCode($shortCode)
    {
        $receipt = $this->findAll(['short_code' => $shortCode], null, 1);
        
        return !empty($receipt) ? $receipt[0] : null;
    }
    
    public function getReceiptDetails($shortCode)
    {
        $verification = $this->verifyReceipt($shortCode);
        
        if (!$verification['valid']) {
            return $verification;
        }
        
        $receipt = $verification['receipt'];
        $transaction = $verification['transaction'];
        $event = $verification['event'];
        $contestant = $verification['contestant'];
        $bundle = $verification['bundle'];
        
        return [
            'valid' => true,
            'receipt_code' => $receipt['short_code'],
            'transaction_id' => $transaction['id'],
            'event_name' => $event['name'],
            'contestant_name' => $contestant['name'],
            'votes_purchased' => $bundle['votes'],
            'amount_paid' => $transaction['amount'],
            'transaction_date' => $transaction['created_at'],
            'transaction_status' => $transaction['status'],
            'msisdn' => $transaction['msisdn'] ? substr($transaction['msisdn'], 0, 3) . '****' . substr($transaction['msisdn'], -2) : 'N/A'
        ];
    }
    
    public function generateReceiptPDF($shortCode)
    {
        $details = $this->getReceiptDetails($shortCode);
        
        if (!$details['valid']) {
            throw new \Exception('Invalid receipt code');
        }
        
        // This would integrate with a PDF library like TCPDF or FPDF
        // For now, return HTML that can be converted to PDF
        
        $html = $this->generateReceiptHTML($details);
        
        return $html;
    }
    
    private function generateReceiptHTML($details)
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Vote Receipt - ' . $details['receipt_code'] . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .receipt { max-width: 400px; margin: 0 auto; border: 1px solid #ccc; padding: 20px; }
                .header { text-align: center; margin-bottom: 20px; }
                .logo { font-size: 24px; font-weight: bold; color: #007bff; }
                .receipt-code { font-size: 18px; font-weight: bold; margin: 10px 0; }
                .details { margin: 15px 0; }
                .detail-row { display: flex; justify-content: space-between; margin: 5px 0; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                .qr-placeholder { width: 100px; height: 100px; border: 1px solid #ccc; margin: 10px auto; }
            </style>
        </head>
        <body>
            <div class="receipt">
                <div class="header">
                    <div class="logo">SmartCast</div>
                    <div>Vote Receipt</div>
                    <div class="receipt-code">' . $details['receipt_code'] . '</div>
                </div>
                
                <div class="details">
                    <div class="detail-row">
                        <span>Event:</span>
                        <span>' . htmlspecialchars($details['event_name']) . '</span>
                    </div>
                    <div class="detail-row">
                        <span>Contestant:</span>
                        <span>' . htmlspecialchars($details['contestant_name']) . '</span>
                    </div>
                    <div class="detail-row">
                        <span>Votes:</span>
                        <span>' . $details['votes_purchased'] . '</span>
                    </div>
                    <div class="detail-row">
                        <span>Amount:</span>
                        <span>$' . number_format($details['amount_paid'], 2) . '</span>
                    </div>
                    <div class="detail-row">
                        <span>Date:</span>
                        <span>' . date('M j, Y H:i', strtotime($details['transaction_date'])) . '</span>
                    </div>
                    <div class="detail-row">
                        <span>Status:</span>
                        <span>' . ucfirst($details['transaction_status']) . '</span>
                    </div>
                    <div class="detail-row">
                        <span>Phone:</span>
                        <span>' . $details['msisdn'] . '</span>
                    </div>
                </div>
                
                <div class="qr-placeholder">
                    <!-- QR Code would go here -->
                    <div style="text-align: center; line-height: 100px; color: #999;">QR Code</div>
                </div>
                
                <div class="footer">
                    <p>Thank you for voting!</p>
                    <p>Keep this receipt for your records.</p>
                    <p>Transaction ID: ' . $details['transaction_id'] . '</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    public function getReceiptStats($tenantId = null, $eventId = null)
    {
        $sql = "
            SELECT 
                COUNT(vr.id) as total_receipts,
                COUNT(CASE WHEN t.status = 'success' THEN 1 END) as successful_receipts,
                COUNT(CASE WHEN t.status = 'failed' THEN 1 END) as failed_receipts,
                MIN(vr.created_at) as first_receipt,
                MAX(vr.created_at) as last_receipt
            FROM {$this->table} vr
            INNER JOIN transactions t ON vr.transaction_id = t.id
        ";
        
        $params = [];
        $conditions = [];
        
        if ($tenantId) {
            $conditions[] = "t.tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        if ($eventId) {
            $conditions[] = "t.event_id = :event_id";
            $params['event_id'] = $eventId;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        return $this->db->selectOne($sql, $params);
    }
    
    public function searchReceipts($criteria)
    {
        $sql = "
            SELECT vr.*, t.amount, t.status, t.msisdn, t.created_at as transaction_date,
                   e.name as event_name, c.name as contestant_name
            FROM {$this->table} vr
            INNER JOIN transactions t ON vr.transaction_id = t.id
            INNER JOIN events e ON t.event_id = e.id
            INNER JOIN contestants c ON t.contestant_id = c.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (isset($criteria['tenant_id'])) {
            $sql .= " AND t.tenant_id = :tenant_id";
            $params['tenant_id'] = $criteria['tenant_id'];
        }
        
        if (isset($criteria['event_id'])) {
            $sql .= " AND t.event_id = :event_id";
            $params['event_id'] = $criteria['event_id'];
        }
        
        if (isset($criteria['short_code'])) {
            $sql .= " AND vr.short_code LIKE :short_code";
            $params['short_code'] = '%' . $criteria['short_code'] . '%';
        }
        
        if (isset($criteria['msisdn'])) {
            $sql .= " AND t.msisdn = :msisdn";
            $params['msisdn'] = $criteria['msisdn'];
        }
        
        if (isset($criteria['status'])) {
            $sql .= " AND t.status = :status";
            $params['status'] = $criteria['status'];
        }
        
        if (isset($criteria['date_from'])) {
            $sql .= " AND vr.created_at >= :date_from";
            $params['date_from'] = $criteria['date_from'];
        }
        
        if (isset($criteria['date_to'])) {
            $sql .= " AND vr.created_at <= :date_to";
            $params['date_to'] = $criteria['date_to'];
        }
        
        $sql .= " ORDER BY vr.created_at DESC";
        
        if (isset($criteria['limit'])) {
            $sql .= " LIMIT " . (int)$criteria['limit'];
        }
        
        return $this->db->select($sql, $params);
    }
    
    public function bulkVerifyReceipts($shortCodes)
    {
        $results = [];
        
        foreach ($shortCodes as $shortCode) {
            $results[$shortCode] = $this->verifyReceipt($shortCode);
        }
        
        return $results;
    }
    
    public function getReceiptsByDateRange($startDate, $endDate, $tenantId = null)
    {
        $sql = "
            SELECT vr.*, t.amount, e.name as event_name, c.name as contestant_name
            FROM {$this->table} vr
            INNER JOIN transactions t ON vr.transaction_id = t.id
            INNER JOIN events e ON t.event_id = e.id
            INNER JOIN contestants c ON t.contestant_id = c.id
            WHERE vr.created_at BETWEEN :start_date AND :end_date
        ";
        
        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        
        if ($tenantId) {
            $sql .= " AND t.tenant_id = :tenant_id";
            $params['tenant_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY vr.created_at DESC";
        
        return $this->db->select($sql, $params);
    }
    
    /**
     * Ensure the vote_receipts table exists
     */
    private function ensureTableExists()
    {
        try {
            // Try to query the table to see if it exists
            $this->db->selectOne("SELECT 1 FROM {$this->table} LIMIT 1");
        } catch (\Exception $e) {
            // Table doesn't exist, create it
            error_log("VoteReceipt: Table doesn't exist, creating it...");
            $this->createTable();
        }
    }
    
    /**
     * Create the vote_receipts table
     */
    private function createTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `{$this->table}` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `transaction_id` int(11) NOT NULL,
              `short_code` varchar(8) NOT NULL,
              `public_hash` varchar(255) NOT NULL,
              `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `unique_transaction` (`transaction_id`),
              UNIQUE KEY `unique_short_code` (`short_code`),
              KEY `idx_short_code` (`short_code`),
              KEY `idx_transaction_id` (`transaction_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $this->db->query($sql);
        error_log("VoteReceipt: Table created successfully");
    }
}
