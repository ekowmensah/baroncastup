<?php

namespace SmartCast\Controllers\Api;

use SmartCast\Controllers\BaseController;
use SmartCast\Models\Event;
use SmartCast\Models\Contestant;
use SmartCast\Models\VoteBundle;
use SmartCast\Models\Transaction;
use SmartCast\Models\Vote;
use SmartCast\Models\VoteReceipt;
use SmartCast\Models\VoteLedger;
use SmartCast\Models\RateLimit;
use SmartCast\Models\RiskBlock;

/**
 * API Vote Controller
 */
class VoteController extends BaseController
{
    private $eventModel;
    private $contestantModel;
    private $bundleModel;
    private $transactionModel;
    private $voteModel;
    private $receiptModel;
    private $ledgerModel;
    private $rateLimitModel;
    private $riskBlockModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->eventModel = new Event();
        $this->contestantModel = new Contestant();
        $this->bundleModel = new VoteBundle();
        $this->transactionModel = new Transaction();
        $this->voteModel = new Vote();
        $this->receiptModel = new VoteReceipt();
        $this->ledgerModel = new VoteLedger();
        $this->rateLimitModel = new RateLimit();
        $this->riskBlockModel = new RiskBlock();
        
        // Set JSON headers
        header('Content-Type: application/json');
    }
    
    public function vote()
    {
        try {
            // Rate limiting check
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $rateCheck = $this->rateLimitModel->checkRateLimit("vote_api_{$ipAddress}", 10, 300); // 10 votes per 5 minutes
            
            if (!$rateCheck['allowed']) {
                return $this->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded. Please try again later.',
                    'retry_after' => $rateCheck['reset_time'] - time()
                ], 429);
            }
            
            // Risk blocking check
            if ($this->riskBlockModel->isIpBlocked($ipAddress)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                return $this->json([
                    'success' => false,
                    'message' => 'Invalid JSON data'
                ], 400);
            }
            
            // Validate required fields
            $required = ['event_id', 'contestant_id', 'bundle_id', 'msisdn'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->json([
                        'success' => false,
                        'message' => "Field '{$field}' is required"
                    ], 400);
                }
            }
            
            // Check if MSISDN is blocked
            if ($this->riskBlockModel->isMsisdnBlocked($data['msisdn'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'This phone number is blocked'
                ], 403);
            }
            
            // Validate event
            $event = $this->eventModel->find($data['event_id']);
            if (!$event || !$this->eventModel->canVote($data['event_id'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Voting is not available for this event'
                ], 400);
            }
            
            // Validate contestant
            $contestant = $this->contestantModel->find($data['contestant_id']);
            if (!$contestant || $contestant['event_id'] != $data['event_id'] || !$contestant['active']) {
                return $this->json([
                    'success' => false,
                    'message' => 'Invalid contestant'
                ], 400);
            }
            
            // Validate bundle
            $bundle = $this->bundleModel->find($data['bundle_id']);
            if (!$bundle || $bundle['event_id'] != $data['event_id'] || !$bundle['active']) {
                return $this->json([
                    'success' => false,
                    'message' => 'Invalid vote bundle'
                ], 400);
            }
            
            // Process the vote
            $this->transactionModel->db->beginTransaction();
            
            try {
                // Create transaction
                $transactionData = [
                    'tenant_id' => $event['tenant_id'],
                    'event_id' => $data['event_id'],
                    'contestant_id' => $data['contestant_id'],
                    'bundle_id' => $data['bundle_id'],
                    'amount' => $bundle['price'],
                    'msisdn' => $data['msisdn'],
                    'status' => 'success',
                    'provider' => 'api',
                    'coupon_code' => $data['coupon_code'] ?? null,
                    'referral_code' => $data['referral_code'] ?? null
                ];
                
                $transactionId = $this->transactionModel->createTransaction($transactionData);
                
                // Cast votes
                $voteId = $this->voteModel->castVote(
                    $transactionId,
                    $event['tenant_id'],
                    $data['event_id'],
                    $data['contestant_id'],
                    $bundle['votes']
                );
                
                // Create ledger entry
                $this->ledgerModel->createLedgerEntry(
                    $voteId,
                    $transactionId,
                    $event['tenant_id'],
                    $data['event_id'],
                    $data['contestant_id'],
                    $bundle['votes']
                );
                
                // Generate receipt
                $receipt = $this->receiptModel->generateReceipt($transactionId);
                
                $this->transactionModel->db->commit();
                
                return $this->json([
                    'success' => true,
                    'message' => 'Vote cast successfully',
                    'data' => [
                        'transaction_id' => $transactionId,
                        'vote_id' => $voteId,
                        'receipt_code' => $receipt['short_code'],
                        'votes_cast' => $bundle['votes'],
                        'amount_charged' => $bundle['price'],
                        'contestant' => [
                            'id' => $contestant['id'],
                            'name' => $contestant['name'],
                            'code' => $contestant['contestant_code']
                        ],
                        'timestamp' => date('Y-m-d H:i:s')
                    ]
                ]);
                
            } catch (\Exception $e) {
                $this->transactionModel->db->rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Vote processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function verifyReceipt()
    {
        try {
            $receiptCode = $_GET['receipt_code'] ?? '';
            
            if (empty($receiptCode)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Receipt code is required'
                ], 400);
            }
            
            $verification = $this->receiptModel->verifyReceipt($receiptCode);
            
            if ($verification['valid']) {
                $details = $this->receiptModel->getReceiptDetails($receiptCode);
                
                return $this->json([
                    'success' => true,
                    'data' => $details
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => $verification['error']
                ], 404);
            }
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Receipt verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getBundles()
    {
        try {
            $eventId = $_GET['event_id'] ?? '';
            
            if (empty($eventId)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Event ID is required'
                ], 400);
            }
            
            $event = $this->eventModel->find($eventId);
            if (!$event || $event['visibility'] !== 'public') {
                return $this->json([
                    'success' => false,
                    'message' => 'Event not found or not public'
                ], 404);
            }
            
            $bundles = $this->bundleModel->getBundlesByEvent($eventId);
            
            return $this->json([
                'success' => true,
                'data' => $bundles
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to fetch vote bundles',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getContestants()
    {
        try {
            $eventId = $_GET['event_id'] ?? '';
            
            if (empty($eventId)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Event ID is required'
                ], 400);
            }
            
            $event = $this->eventModel->find($eventId);
            if (!$event || $event['visibility'] !== 'public') {
                return $this->json([
                    'success' => false,
                    'message' => 'Event not found or not public'
                ], 404);
            }
            
            $contestants = $this->contestantModel->getContestantsByEvent($eventId);
            
            return $this->json([
                'success' => true,
                'data' => $contestants
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to fetch contestants',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
