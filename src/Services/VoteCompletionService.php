<?php

namespace SmartCast\Services;

use SmartCast\Models\Transaction;
use SmartCast\Models\Vote;
use SmartCast\Models\Event;
use SmartCast\Models\Contestant;
use SmartCast\Models\Category;
use SmartCast\Models\VoteReceipt;
use SmartCast\Services\SmsService;

/**
 * Service to handle vote completion tasks including SMS notifications
 */
class VoteCompletionService
{
    private $transactionModel;
    private $voteModel;
    private $eventModel;
    private $contestantModel;
    private $categoryModel;
    private $receiptModel;
    private $smsService;
    
    public function __construct()
    {
        $this->transactionModel = new Transaction();
        $this->voteModel = new Vote();
        $this->eventModel = new Event();
        $this->contestantModel = new Contestant();
        $this->categoryModel = new Category();
        $this->receiptModel = new VoteReceipt();
        $this->smsService = new SmsService();
    }
    
    /**
     * Process vote completion after successful payment
     */
    public function processVoteCompletion($transactionId, $additionalData = [])
    {
        try {
            error_log("VoteCompletionService: Starting vote completion for transaction {$transactionId}");
            
            // Get transaction
            $transaction = $this->transactionModel->find($transactionId);
            if (!$transaction) {
                throw new \Exception('Transaction not found');
            }
            
            error_log("VoteCompletionService: Transaction found - Status: {$transaction['status']}, Provider: {$transaction['provider']}");
            
            if ($transaction['status'] !== 'success') {
                throw new \Exception('Transaction status is not success: ' . $transaction['status']);
            }
            
            // Get associated vote
            $vote = $this->voteModel->getByTransactionId($transactionId);
            if (!$vote) {
                error_log("VoteCompletionService: Vote record not found for transaction {$transactionId}");
                throw new \Exception('Vote record not found for transaction');
            }
            
            error_log("VoteCompletionService: Vote found - ID: {$vote['id']}, Quantity: " . ($vote['quantity'] ?? $vote['vote_count'] ?? 'N/A'));
            
            // Get related entities
            $event = $this->eventModel->find($vote['event_id']);
            $contestant = $this->contestantModel->find($vote['contestant_id']);
            $category = $this->categoryModel->find($vote['category_id']);
            
            error_log("VoteCompletionService: Related entities - Event: " . ($event['name'] ?? 'N/A') . ", Contestant: " . ($contestant['name'] ?? 'N/A'));
            
            // Generate or get receipt
            $receipt = $this->getOrCreateReceipt($transaction, $vote);
            
            // Prepare SMS data
            $smsData = [
                'phone' => $this->extractPhoneNumber($transaction, $additionalData),
                'nominee_name' => $contestant['name'] ?? 'Unknown Contestant',
                'event_name' => $event['name'] ?? 'Unknown Event',
                'category_name' => $category['name'] ?? 'Unknown Category',
                'vote_count' => $vote['quantity'] ?? $vote['vote_count'] ?? 1,
                'amount' => $transaction['amount'] ?? 0,
                'receipt_number' => $receipt['short_code'] ?? $transaction['reference'] ?? $transaction['id'],
                'transaction_id' => $transaction['id'],
                'vote_id' => $vote['id']
            ];
            
            error_log("VoteCompletionService: Prepared SMS data - Phone: {$smsData['phone']}, Votes: {$smsData['vote_count']}");
            
            // Send SMS notification
            $smsResult = $this->smsService->sendVoteConfirmationSms($smsData);
            
            error_log("VoteCompletionService: SMS result - " . json_encode($smsResult));
            
            // Log the completion
            $this->logVoteCompletion($transaction, $vote, $smsResult);
            
            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'vote_id' => $vote['id'],
                'receipt_number' => $receipt['short_code'] ?? null,
                'sms_sent' => $smsResult['success'] ?? false,
                'sms_details' => $smsResult
            ];
            
        } catch (\Exception $e) {
            error_log("Vote Completion Service Error: " . $e->getMessage());
            error_log("Vote Completion Service Error Trace: " . $e->getTraceAsString());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId
            ];
        }
    }
    
    /**
     * Extract phone number from transaction or additional data
     */
    private function extractPhoneNumber($transaction, $additionalData = [])
    {
        // Try to get phone from various sources
        $phone = null;
        
        // From additional data (webhook payload)
        if (!empty($additionalData['phone'])) {
            $phone = $additionalData['phone'];
        }
        
        // From transaction msisdn field (USSD votes)
        if (!$phone && !empty($transaction['msisdn'])) {
            $phone = $transaction['msisdn'];
        }
        
        // From transaction phone field (web votes)
        if (!$phone && !empty($transaction['phone'])) {
            $phone = $transaction['phone'];
        }
        
        // From transaction metadata
        if (!$phone && !empty($transaction['metadata'])) {
            $metadata = is_string($transaction['metadata']) ? 
                json_decode($transaction['metadata'], true) : 
                $transaction['metadata'];
                
            if (isset($metadata['phone']) || isset($metadata['msisdn'])) {
                $phone = $metadata['phone'] ?? $metadata['msisdn'];
            }
        }
        
        // From payment gateway response
        if (!$phone && !empty($transaction['gateway_response'])) {
            $response = is_string($transaction['gateway_response']) ? 
                json_decode($transaction['gateway_response'], true) : 
                $transaction['gateway_response'];
                
            if (isset($response['phone']) || isset($response['mobile']) || isset($response['msisdn'])) {
                $phone = $response['phone'] ?? $response['mobile'] ?? $response['msisdn'];
            }
        }
        
        if (!$phone) {
            error_log("VoteCompletionService: Phone number not found. Transaction data: " . json_encode($transaction));
            error_log("VoteCompletionService: Additional data: " . json_encode($additionalData));
            throw new \Exception('Phone number not found in transaction data');
        }
        
        error_log("VoteCompletionService: Extracted phone number: {$phone}");
        return $phone;
    }
    
    /**
     * Get existing receipt or create new one
     */
    private function getOrCreateReceipt($transaction, $vote)
    {
        // Try to find existing receipt
        $receipt = $this->receiptModel->getByTransactionId($transaction['id']);
        
        if (!$receipt) {
            // Create new receipt
            $receiptData = [
                'transaction_id' => $transaction['id'],
                'vote_id' => $vote['id'],
                'event_id' => $vote['event_id'],
                'contestant_id' => $vote['contestant_id'],
                'category_id' => $vote['category_id'],
                'receipt_number' => $this->generateReceiptNumber($transaction),
                'amount' => $transaction['amount'],
                'vote_count' => $vote['vote_count'],
                'status' => 'issued',
                'issued_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $receiptId = $this->receiptModel->create($receiptData);
            $receipt = $this->receiptModel->find($receiptId);
        }
        
        return $receipt;
    }
    
    /**
     * Generate unique receipt number
     */
    private function generateReceiptNumber($transaction)
    {
        $prefix = 'SC'; // SmartCast
        $timestamp = date('ymd');
        $transactionId = str_pad($transaction['id'], 6, '0', STR_PAD_LEFT);
        $random = strtoupper(substr(md5(uniqid()), 0, 3));
        
        return $prefix . $timestamp . $transactionId . $random;
    }
    
    /**
     * Log vote completion for audit purposes
     */
    private function logVoteCompletion($transaction, $vote, $smsResult)
    {
        try {
            $logData = [
                'action' => 'vote_completion',
                'entity_type' => 'vote',
                'entity_id' => $vote['id'],
                'details' => json_encode([
                    'transaction_id' => $transaction['id'],
                    'vote_id' => $vote['id'],
                    'event_id' => $vote['event_id'],
                    'contestant_id' => $vote['contestant_id'],
                    'amount' => $transaction['amount'],
                    'vote_count' => $vote['vote_count'] ?? $vote['quantity'] ?? 1,
                    'sms_sent' => $smsResult['success'] ?? false,
                    'sms_error' => $smsResult['error'] ?? null
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'system',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'system',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // If AuditLog model exists, use it
            if (class_exists('\SmartCast\Models\AuditLog')) {
                $auditModel = new \SmartCast\Models\AuditLog();
                $auditModel->create($logData);
            } else {
                // Fallback to error log
                error_log("Vote Completion: " . json_encode($logData));
            }
            
        } catch (\Exception $e) {
            error_log("Failed to log vote completion: " . $e->getMessage());
        }
    }
    
    /**
     * Process multiple vote completions (batch processing)
     */
    public function processBatchCompletions($transactionIds)
    {
        $results = [];
        
        foreach ($transactionIds as $transactionId) {
            $results[] = $this->processVoteCompletion($transactionId);
            
            // Small delay to avoid overwhelming SMS gateway
            usleep(100000); // 0.1 second
        }
        
        return $results;
    }
    
    /**
     * Retry failed SMS for completed votes
     */
    public function retryFailedSms($transactionId)
    {
        try {
            $transaction = $this->transactionModel->find($transactionId);
            if (!$transaction) {
                throw new \Exception('Transaction not found');
            }
            
            // Get SMS logs for this transaction
            $smsLogs = $this->smsService->getSmsLog()->getByTransactionId($transactionId);
            $failedSms = array_filter($smsLogs, function($log) {
                return $log['status'] === 'failed';
            });
            
            if (empty($failedSms)) {
                throw new \Exception('No failed SMS found for this transaction');
            }
            
            $results = [];
            foreach ($failedSms as $smsLog) {
                $results[] = $this->smsService->retryFailedSms($smsLog['id']);
            }
            
            return [
                'success' => true,
                'retry_count' => count($results),
                'results' => $results
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get completion statistics
     */
    public function getCompletionStats($dateFrom = null, $dateTo = null)
    {
        try {
            $whereClause = "WHERE t.status = 'success'";
            $params = [];
            
            if ($dateFrom) {
                $whereClause .= " AND DATE(t.created_at) >= :date_from";
                $params['date_from'] = $dateFrom;
            }
            
            if ($dateTo) {
                $whereClause .= " AND DATE(t.created_at) <= :date_to";
                $params['date_to'] = $dateTo;
            }
            
            $sql = "
                SELECT 
                    COUNT(DISTINCT t.id) as total_transactions,
                    COUNT(DISTINCT v.id) as total_votes,
                    COUNT(DISTINCT sl.id) as total_sms_sent,
                    SUM(CASE WHEN sl.status = 'sent' THEN 1 ELSE 0 END) as successful_sms,
                    SUM(CASE WHEN sl.status = 'failed' THEN 1 ELSE 0 END) as failed_sms,
                    ROUND(
                        (SUM(CASE WHEN sl.status = 'sent' THEN 1 ELSE 0 END) * 100.0 / 
                         NULLIF(COUNT(DISTINCT sl.id), 0)), 2
                    ) as sms_success_rate
                FROM transactions t
                LEFT JOIN votes v ON t.id = v.transaction_id
                LEFT JOIN sms_logs sl ON t.id = sl.transaction_id
                {$whereClause}
            ";
            
            return $this->transactionModel->getDatabase()->selectOne($sql, $params);
            
        } catch (\Exception $e) {
            error_log("Error getting completion stats: " . $e->getMessage());
            return null;
        }
    }
}
