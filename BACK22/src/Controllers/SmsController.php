<?php

namespace SmartCast\Controllers;

use SmartCast\Services\SmsService;
use SmartCast\Models\SmsGateway;
use SmartCast\Models\SmsLog;
use SmartCast\Models\Transaction;
use SmartCast\Models\Vote;
use SmartCast\Models\Event;
use SmartCast\Models\Contestant;
use SmartCast\Models\Category;

class SmsController extends BaseController
{
    private $smsService;
    private $smsGateway;
    private $smsLog;
    private $transactionModel;
    private $voteModel;
    private $eventModel;
    private $contestantModel;
    private $categoryModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->smsService = new SmsService();
        $this->smsGateway = new SmsGateway();
        $this->smsLog = new SmsLog();
        $this->transactionModel = new Transaction();
        $this->voteModel = new Vote();
        $this->eventModel = new Event();
        $this->contestantModel = new Contestant();
        $this->categoryModel = new Category();
    }
    
    /**
     * Send SMS after successful vote/payment
     * This method should be called from payment webhook or vote completion
     */
    public function sendVoteConfirmation()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $input = $_POST;
            }
            
            // Validate required data
            $requiredFields = ['transaction_id', 'phone'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    $this->json([
                        'success' => false,
                        'message' => "Missing required field: {$field}"
                    ], 400);
                    return;
                }
            }
            
            // Get transaction details
            $transaction = $this->transactionModel->findById($input['transaction_id']);
            if (!$transaction) {
                $this->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
                return;
            }
            
            // Only send SMS for successful transactions
            if ($transaction['status'] !== 'success') {
                $this->json([
                    'success' => false,
                    'message' => 'Transaction not successful'
                ], 400);
                return;
            }
            
            // Get vote details
            $vote = $this->voteModel->getByTransactionId($transaction['id']);
            if (!$vote) {
                $this->json([
                    'success' => false,
                    'message' => 'Vote record not found'
                ], 404);
                return;
            }
            
            // Prepare SMS data
            $smsData = $this->prepareSmsData($transaction, $vote, $input['phone']);
            
            // Send SMS
            $result = $this->smsService->sendVoteConfirmationSms($smsData);
            
            $this->json([
                'success' => $result['success'],
                'message' => $result['success'] ? 'SMS sent successfully' : 'Failed to send SMS',
                'details' => $result
            ]);
            
        } catch (\Exception $e) {
            error_log("SMS Controller Error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Prepare SMS data from transaction and vote information
     */
    private function prepareSmsData($transaction, $vote, $phone)
    {
        // Get related data
        $contestant = $this->contestantModel->findById($vote['contestant_id']);
        $event = $this->eventModel->findById($vote['event_id']);
        $category = $this->categoryModel->findById($vote['category_id']);
        
        return [
            'phone' => $phone,
            'nominee_name' => $contestant['name'] ?? 'Unknown Contestant',
            'event_name' => $event['name'] ?? 'Unknown Event',
            'category_name' => $category['name'] ?? 'Unknown Category',
            'vote_count' => $vote['vote_count'] ?? 1,
            'amount' => $transaction['amount'] ?? 0,
            'receipt_number' => $transaction['reference'] ?? $transaction['id'],
            'transaction_id' => $transaction['id'],
            'vote_id' => $vote['id']
        ];
    }
    
    /**
     * Manual SMS sending (for admin use)
     */
    public function sendManualSms()
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $phone = $_POST['phone'] ?? '';
                $message = $_POST['message'] ?? '';
                $gatewayType = $_POST['gateway_type'] ?? null;
                
                if (empty($phone) || empty($message)) {
                    $this->json([
                        'success' => false,
                        'message' => 'Phone and message are required'
                    ], 400);
                    return;
                }
                
                // Get gateway
                $gateway = $gatewayType ? 
                    $this->smsGateway->getGatewayByType($gatewayType) : 
                    $this->smsGateway->getActiveGateway();
                    
                if (!$gateway) {
                    $this->json([
                        'success' => false,
                        'message' => 'No active gateway found'
                    ], 400);
                    return;
                }
                
                // Send SMS
                $result = $this->smsService->sendSms($gateway, $phone, $message);
                
                $this->json([
                    'success' => $result['success'],
                    'message' => $result['success'] ? 'SMS sent successfully' : 'Failed to send SMS',
                    'details' => $result
                ]);
                
            } catch (\Exception $e) {
                $this->json([
                    'success' => false,
                    'message' => 'Error sending SMS: ' . $e->getMessage()
                ], 500);
            }
        }
        
        // Show manual SMS form
        $gateways = $this->smsGateway->getActiveGateways();
        $this->view('superadmin/sms/manual-send', [
            'gateways' => $gateways
        ]);
    }
    
    /**
     * Test gateway connection
     */
    public function testGateway($gatewayId)
    {
        $this->requireAuth();
        
        try {
            $testPhone = $_POST['test_phone'] ?? null;
            $result = $this->smsService->testGateway($gatewayId, $testPhone);
            
            $this->json([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Gateway test successful' : 'Gateway test failed',
                'details' => $result
            ]);
            
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get SMS statistics
     */
    public function getStatistics()
    {
        $this->requireAuth();
        
        try {
            $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
            $dateTo = $_GET['date_to'] ?? date('Y-m-d');
            
            $stats = $this->smsService->getStatistics($dateFrom, $dateTo);
            $dailyStats = $this->smsLog->getDailyStats(30);
            $gatewayComparison = $this->smsLog->getGatewayComparison($dateFrom, $dateTo);
            
            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'overview' => $stats,
                    'daily_stats' => $dailyStats,
                    'gateway_comparison' => $gatewayComparison
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error fetching statistics: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get SMS logs with pagination
     */
    public function getLogs()
    {
        $this->requireAuth();
        
        try {
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 50);
            $filters = [
                'status' => $_GET['status'] ?? null,
                'gateway_type' => $_GET['gateway_type'] ?? null,
                'phone' => $_GET['phone'] ?? null,
                'date_from' => $_GET['date_from'] ?? null,
                'date_to' => $_GET['date_to'] ?? null
            ];
            
            // Remove empty filters
            $filters = array_filter($filters);
            
            $result = $this->smsLog->getPaginatedLogs($page, $perPage, $filters);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error fetching logs: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Retry failed SMS
     */
    public function retrySms($smsId)
    {
        $this->requireAuth();
        
        try {
            $result = $this->smsService->retryFailedSms($smsId);
            
            $this->jsonResponse([
                'success' => $result['success'],
                'message' => $result['success'] ? 'SMS retry successful' : 'SMS retry failed',
                'details' => $result
            ]);
            
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Retry failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk SMS sending
     */
    public function sendBulkSms()
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $phones = $_POST['phones'] ?? [];
                $message = $_POST['message'] ?? '';
                $gatewayType = $_POST['gateway_type'] ?? null;
                
                if (empty($phones) || empty($message)) {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => 'Phone numbers and message are required'
                    ], 400);
                    return;
                }
                
                // Parse phone numbers (could be comma-separated or array)
                if (is_string($phones)) {
                    $phones = array_map('trim', explode(',', $phones));
                }
                
                $results = $this->smsService->sendBulkSms($phones, $message, $gatewayType);
                
                $successful = count(array_filter($results, function($r) { return $r['success']; }));
                $total = count($results);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => "Bulk SMS completed: {$successful}/{$total} successful",
                    'results' => $results,
                    'summary' => [
                        'total' => $total,
                        'successful' => $successful,
                        'failed' => $total - $successful
                    ]
                ]);
                
            } catch (\Exception $e) {
                $this->json([
                    'success' => false,
                    'message' => 'Bulk SMS failed: ' . $e->getMessage()
                ], 500);
            }
        }
        
        // Show bulk SMS form
        $gateways = $this->smsGateway->getActiveGateways();
        $this->view('superadmin/sms/bulk-send', [
            'gateways' => $gateways
        ]);
    }
}
