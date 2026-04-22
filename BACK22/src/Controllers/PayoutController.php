<?php

namespace SmartCast\Controllers;

use SmartCast\Services\PayoutService;
use SmartCast\Models\PayoutMethod;
use SmartCast\Models\PayoutSchedule;
use SmartCast\Models\Payout;
use SmartCast\Models\TenantBalance;

/**
 * Payout Controller
 * Handles all payout-related operations for organizers
 */
class PayoutController extends BaseController
{
    private $payoutService;
    private $payoutMethodModel;
    private $payoutScheduleModel;
    private $payoutModel;
    private $balanceModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->payoutService = new PayoutService();
        $this->payoutMethodModel = new PayoutMethod();
        $this->payoutScheduleModel = new PayoutSchedule();
        $this->payoutModel = new Payout();
        $this->balanceModel = new TenantBalance();
    }
    
    /**
     * Payout dashboard
     */
    public function dashboard()
    {
        $tenantId = $this->session->getTenantId();
        
        try {
            $dashboardData = $this->payoutService->getPayoutDashboard($tenantId);
            
            $this->view('organizer/payouts/dashboard', [
                'title' => 'Payouts Dashboard',
                'breadcrumbs' => [
                    ['title' => 'Payouts', 'url' => ORGANIZER_URL . '/payouts'],
                    ['title' => 'Dashboard']
                ],
                'balance' => $dashboardData['balance'],
                'schedule' => $dashboardData['schedule'],
                'recent_payouts' => $dashboardData['recent_payouts'],
                'payout_methods' => $dashboardData['payout_methods'],
                'revenue_stats' => $dashboardData['revenue_stats'],
                'can_request_payout' => $dashboardData['can_request_payout']
            ]);
            
        } catch (\Exception $e) {
            error_log('Payout dashboard error: ' . $e->getMessage());
            $this->redirect(ORGANIZER_URL, 'Error loading payout dashboard', 'error');
        }
    }
    
    /**
     * Request payout form
     */
    public function requestPayout()
    {
        $tenantId = $this->session->getTenantId();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processPayoutRequest();
        }
        
        try {
            $balance = $this->balanceModel->getBalance($tenantId);
            $schedule = $this->payoutScheduleModel->getScheduleByTenant($tenantId);
            $payoutMethods = $this->payoutMethodModel->getMethodsByTenant($tenantId);
            
            $this->view('organizer/payouts/request', [
                'title' => 'Request Payout',
                'breadcrumbs' => [
                    ['title' => 'Payouts', 'url' => ORGANIZER_URL . '/payouts'],
                    ['title' => 'Request Payout']
                ],
                'balance' => $balance,
                'schedule' => $schedule,
                'payout_methods' => $payoutMethods
            ]);
            
        } catch (\Exception $e) {
            error_log('Request payout form error: ' . $e->getMessage());
            $this->redirect(ORGANIZER_URL . '/payouts', 'Error loading payout request form', 'error');
        }
    }
    
    /**
     * Process payout request
     */
    private function processPayoutRequest()
    {
        $tenantId = $this->session->getTenantId();
        
        try {
            $amount = floatval($_POST['amount'] ?? 0);
            $payoutMethodId = intval($_POST['payout_method_id'] ?? 0);
            
            if ($amount <= 0) {
                $this->redirect(ORGANIZER_URL . '/payouts/request', 'Invalid payout amount', 'error');
                return;
            }
            
            $result = $this->payoutService->requestPayout($tenantId, $amount, $payoutMethodId);
            
            if ($result['success']) {
                $this->redirect(ORGANIZER_URL . '/payouts', 
                    'Payout request submitted successfully! Your request for $' . number_format($result['amount'], 2) . ' is now pending admin approval. You will be notified once it\'s reviewed.', 
                    'success');
            } else {
                $this->redirect(ORGANIZER_URL . '/payouts/request', 
                    'Payout request failed: ' . $result['error'], 
                    'error');
            }
            
        } catch (\Exception $e) {
            error_log('Process payout request error: ' . $e->getMessage());
            $this->redirect(ORGANIZER_URL . '/payouts/request', 
                'An error occurred while processing your payout request', 
                'error');
        }
    }
    
    /**
     * Payout details
     */
    public function details($payoutId)
    {
        $tenantId = $this->session->getTenantId();
        
        try {
            $payout = $this->payoutModel->getPayoutWithDetails($payoutId);
            
            // Verify payout belongs to current tenant
            if (!$payout || $payout['tenant_id'] != $tenantId) {
                if ($this->isApiRequest()) {
                    return $this->json(['success' => false, 'error' => 'Payout not found'], 404);
                }
                $this->redirect(ORGANIZER_URL . '/payouts/history', 'Payout not found', 'error');
                return;
            }
            
            // Return JSON for API requests
            if ($this->isApiRequest()) {
                return $this->json(['success' => true, 'payout' => $payout]);
            }
            
            // Otherwise render view (if needed in future)
            $this->view('organizer/payouts/details', [
                'title' => 'Payout Details',
                'breadcrumbs' => [
                    ['title' => 'Payouts', 'url' => ORGANIZER_URL . '/payouts'],
                    ['title' => 'History', 'url' => ORGANIZER_URL . '/payouts/history'],
                    ['title' => 'Details']
                ],
                'payout' => $payout
            ]);
            
        } catch (\Exception $e) {
            error_log('Payout details error: ' . $e->getMessage());
            if ($this->isApiRequest()) {
                return $this->json(['success' => false, 'error' => 'Error loading payout details'], 500);
            }
            $this->redirect(ORGANIZER_URL . '/payouts/history', 'Error loading payout details', 'error');
        }
    }
    
    /**
     * Payout history
     */
    public function history()
    {
        $tenantId = $this->session->getTenantId();
        
        try {
            $payouts = $this->payoutModel->getPayoutsByTenant($tenantId);
            
            $this->view('organizer/payouts/history', [
                'title' => 'Payout History',
                'breadcrumbs' => [
                    ['title' => 'Payouts', 'url' => ORGANIZER_URL . '/payouts'],
                    ['title' => 'History']
                ],
                'payouts' => $payouts
            ]);
            
        } catch (\Exception $e) {
            error_log('Payout history error: ' . $e->getMessage());
            $this->redirect(ORGANIZER_URL . '/payouts', 'Error loading payout history', 'error');
        }
    }
    
    /**
     * Payout methods management
     */
    public function methods()
    {
        $tenantId = $this->session->getTenantId();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processMethodAction();
        }
        
        try {
            $methods = $this->payoutMethodModel->getMethodsByTenant($tenantId, false);
            
            $this->view('organizer/payouts/methods', [
                'title' => 'Payout Methods',
                'breadcrumbs' => [
                    ['title' => 'Payouts', 'url' => ORGANIZER_URL . '/payouts'],
                    ['title' => 'Methods']
                ],
                'methods' => $methods
            ]);
            
        } catch (\Exception $e) {
            error_log('Payout methods error: ' . $e->getMessage());
            $this->redirect(ORGANIZER_URL . '/payouts', 'Error loading payout methods', 'error');
        }
    }
    
    /**
     * Add payout method
     */
    public function addMethod()
    {
        $tenantId = $this->session->getTenantId();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processAddMethod();
        }
        
        $this->view('organizer/payouts/add-method', [
            'title' => 'Add Payout Method',
            'breadcrumbs' => [
                ['title' => 'Payouts', 'url' => ORGANIZER_URL . '/payouts'],
                ['title' => 'Methods', 'url' => ORGANIZER_URL . '/payouts/methods'],
                ['title' => 'Add Method']
            ]
        ]);
    }
    
    /**
     * Process add method
     */
    private function processAddMethod()
    {
        $tenantId = $this->session->getTenantId();
        
        try {
            $methodType = $_POST['method_type'] ?? '';
            $methodName = trim($_POST['method_name'] ?? '');
            
            // Build account details based on method type
            $accountDetails = [];
            
            switch ($methodType) {
                case PayoutMethod::TYPE_BANK_TRANSFER:
                    $accountDetails = [
                        'account_number' => $_POST['account_number'] ?? '',
                        'bank_name' => $_POST['bank_name'] ?? '',
                        'account_name' => $_POST['account_name'] ?? '',
                        'bank_code' => $_POST['bank_code'] ?? '',
                        'routing_number' => $_POST['routing_number'] ?? ''
                    ];
                    break;
                    
                case PayoutMethod::TYPE_MOBILE_MONEY:
                    $accountDetails = [
                        'phone_number' => $_POST['phone_number'] ?? '',
                        'provider' => $_POST['provider'] ?? '',
                        'account_name' => $_POST['account_name'] ?? ''
                    ];
                    break;
                    
                case PayoutMethod::TYPE_PAYPAL:
                    $accountDetails = [
                        'email' => $_POST['email'] ?? ''
                    ];
                    break;
                    
                case PayoutMethod::TYPE_STRIPE:
                    $accountDetails = [
                        'account_id' => $_POST['account_id'] ?? ''
                    ];
                    break;
            }
            
            $methodId = $this->payoutMethodModel->createMethod($tenantId, $methodType, $methodName, $accountDetails);
            
            $this->redirect(ORGANIZER_URL . '/payouts/methods', 
                'Payout method added successfully', 
                'success');
                
        } catch (\Exception $e) {
            error_log('Add payout method error: ' . $e->getMessage());
            $this->redirect(ORGANIZER_URL . '/payouts/add-method', 
                'Error adding payout method: ' . $e->getMessage(), 
                'error');
        }
    }
    
    /**
     * Payout settings
     */
    public function settings()
    {
        $tenantId = $this->session->getTenantId();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processSettingsUpdate();
        }
        
        try {
            $schedule = $this->payoutScheduleModel->getScheduleByTenant($tenantId);
            
            $this->view('organizer/payouts/settings', [
                'title' => 'Payout Settings',
                'breadcrumbs' => [
                    ['title' => 'Payouts', 'url' => ORGANIZER_URL . '/payouts'],
                    ['title' => 'Settings']
                ],
                'schedule' => $schedule
            ]);
            
        } catch (\Exception $e) {
            error_log('Payout settings error: ' . $e->getMessage());
            $this->redirect(ORGANIZER_URL . '/payouts', 'Error loading payout settings', 'error');
        }
    }
    
    /**
     * Process settings update
     */
    private function processSettingsUpdate()
    {
        $tenantId = $this->session->getTenantId();
        
        try {
            $frequency = $_POST['frequency'] ?? PayoutSchedule::FREQUENCY_MONTHLY;
            $minimumAmount = floatval($_POST['minimum_amount'] ?? 10.00);
            $autoPayoutEnabled = isset($_POST['auto_payout_enabled']);
            $instantThreshold = floatval($_POST['instant_payout_threshold'] ?? 1000.00);
            $payoutDay = intval($_POST['payout_day'] ?? 1);
            
            $this->payoutScheduleModel->updateSchedule(
                $tenantId, 
                $frequency, 
                $minimumAmount, 
                $autoPayoutEnabled, 
                $instantThreshold, 
                $payoutDay
            );
            
            $this->redirect(ORGANIZER_URL . '/payouts/settings', 
                'Payout settings updated successfully', 
                'success');
                
        } catch (\Exception $e) {
            error_log('Update payout settings error: ' . $e->getMessage());
            $this->redirect(ORGANIZER_URL . '/payouts/settings', 
                'Error updating payout settings: ' . $e->getMessage(), 
                'error');
        }
    }
    
    /**
     * Process method actions (set default, deactivate, etc.)
     */
    private function processMethodAction()
    {
        $tenantId = $this->session->getTenantId();
        
        try {
            $action = $_POST['action'] ?? '';
            $methodId = intval($_POST['method_id'] ?? 0);
            
            switch ($action) {
                case 'set_default':
                    $this->payoutMethodModel->setDefaultMethod($tenantId, $methodId);
                    $message = 'Default payout method updated';
                    break;
                    
                case 'deactivate':
                    $this->payoutMethodModel->deactivateMethod($methodId);
                    $message = 'Payout method deactivated';
                    break;
                    
                default:
                    throw new \Exception('Invalid action');
            }
            
            $this->redirect(ORGANIZER_URL . '/payouts/methods', $message, 'success');
            
        } catch (\Exception $e) {
            error_log('Method action error: ' . $e->getMessage());
            $this->redirect(ORGANIZER_URL . '/payouts/methods', 
                'Error processing action: ' . $e->getMessage(), 
                'error');
        }
    }
    
    /**
     * Cancel payout
     */
    public function cancelPayout($payoutId)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->redirect(ORGANIZER_URL . '/payouts/history', 'Invalid request method', 'error');
                return;
            }

            $this->validateCsrf(ORGANIZER_URL . '/payouts/history');

            $tenantId = $this->session->getTenantId();
            $payout = $this->payoutModel->find($payoutId);

            if (!$payout || intval($payout['tenant_id']) !== intval($tenantId)) {
                $this->redirect(ORGANIZER_URL . '/payouts/history', 'Payout not found', 'error');
                return;
            }

            $result = $this->payoutService->cancelPayout($payoutId, 'Cancelled by organizer');
            
            if ($result) {
                $this->redirect(ORGANIZER_URL . '/payouts/history', 
                    'Payout cancelled successfully', 
                    'success');
            } else {
                $this->redirect(ORGANIZER_URL . '/payouts/history', 
                    'Unable to cancel payout', 
                    'error');
            }
            
        } catch (\Exception $e) {
            error_log('Cancel payout error: ' . $e->getMessage());
            $this->redirect(ORGANIZER_URL . '/payouts/history', 
                'Error cancelling payout', 
                'error');
        }
    }
    
    /**
     * API: Get balance
     */
    public function apiGetBalance()
    {
        $tenantId = $this->session->getTenantId();
        
        try {
            $balance = $this->balanceModel->getBalance($tenantId);
            return $this->json(['success' => true, 'balance' => $balance]);
            
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API: Calculate payout fees
     */
    public function apiCalculateFees()
    {
        try {
            $amount = floatval($_POST['amount'] ?? 0);
            $methodType = $_POST['method_type'] ?? 'bank_transfer';
            
            if ($amount <= 0) {
                return $this->json(['success' => false, 'error' => 'Invalid amount'], 400);
            }
            
            // Calculate processing fee
            $feeStructure = [
                'bank_transfer' => ['percentage' => 1.0, 'fixed' => 0.50],
                'mobile_money' => ['percentage' => 1.5, 'fixed' => 0.25],
                'paypal' => ['percentage' => 2.9, 'fixed' => 0.30],
                'stripe' => ['percentage' => 2.9, 'fixed' => 0.30]
            ];
            
            $fees = $feeStructure[$methodType] ?? $feeStructure['bank_transfer'];
            $processingFee = round(($amount * $fees['percentage'] / 100) + $fees['fixed'], 2);
            $netAmount = $amount - $processingFee;
            
            return $this->json([
                'success' => true,
                'amount' => $amount,
                'processing_fee' => $processingFee,
                'net_amount' => $netAmount,
                'fee_percentage' => $fees['percentage'],
                'fee_fixed' => $fees['fixed']
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Download payout receipt
     */
    public function downloadReceipt($payoutId)
    {
        try {
            $tenantId = $this->session->getTenantId();
            $payout = $this->payoutModel->getPayoutWithDetails($payoutId);
            
            // Verify payout belongs to current tenant and is paid
            if (!$payout || $payout['tenant_id'] != $tenantId || $payout['status'] !== 'paid') {
                $this->redirect(ORGANIZER_URL . '/payouts', 'Receipt not available', 'error');
                return;
            }
            
            // Generate and download receipt
            $this->generatePayoutReceipt($payout);
            
        } catch (\Exception $e) {
            error_log('Download receipt error: ' . $e->getMessage());
            $this->redirect(ORGANIZER_URL . '/payouts', 'Error downloading receipt', 'error');
        }
    }
    
    /**
     * Generate payout receipt
     */
    private function generatePayoutReceipt($payout)
    {
        $recipientDetails = [];
        if (!empty($payout['recipient_details'])) {
            $decoded = json_decode($payout['recipient_details'], true);
            if (is_array($decoded)) {
                $recipientDetails = $decoded;
            }
        }

        $methodLabel = ucwords(str_replace('_', ' ', $payout['payout_method'] ?? 'N/A'));
        $methodLines = $this->formatReceiptMethodDetails($payout['payout_method'] ?? '', $recipientDetails);
        $requestedAt = $payout['created_at'] ?? ($payout['requested_at'] ?? null);
        $processedAt = $payout['processed_at'] ?? null;

        // Set headers for text receipt download
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="payout-receipt-' . $payout['payout_id'] . '.txt"');
        
        echo "BARONCAST PAYOUT RECEIPT\n";
        echo "========================\n\n";
        echo "Payout ID: " . $payout['payout_id'] . "\n";
        echo "Organization: " . $payout['tenant_name'] . "\n";
        echo "Amount: GHS " . number_format((float)($payout['amount'] ?? 0), 2) . "\n";
        echo "Processing Fee: GHS " . number_format((float)($payout['processing_fee'] ?? 0), 2) . "\n";
        echo "Net Amount: GHS " . number_format((float)($payout['net_amount'] ?? (($payout['amount'] ?? 0) - ($payout['processing_fee'] ?? 0))), 2) . "\n";
        echo "Method: " . $methodLabel . "\n";
        foreach ($methodLines as $line) {
            echo "  - " . $line . "\n";
        }
        echo "Status: " . ucfirst($payout['status']) . "\n";
        if (!empty($payout['provider_reference'])) {
            echo "Provider Reference: " . $payout['provider_reference'] . "\n";
        }
        echo "Requested: " . ($requestedAt ? date('F j, Y g:i A', strtotime($requestedAt)) : 'N/A') . "\n";
        echo "Processed: " . ($processedAt ? date('F j, Y g:i A', strtotime($processedAt)) : 'N/A') . "\n";
        echo "\nThis receipt confirms the successful payout processing.\n";
        echo "Generated on: " . date('F j, Y g:i A') . "\n";
        echo "\nThank you for using BaronCast!\n";
    }

    private function formatReceiptMethodDetails($methodType, array $details)
    {
        switch ($methodType) {
            case 'bank_transfer':
                return [
                    'Account: ' . (!empty($details['account_number']) ? ('****' . substr((string)$details['account_number'], -4)) : 'N/A'),
                    'Bank: ' . ($details['bank_name'] ?? 'N/A'),
                    'Account Name: ' . ($details['account_name'] ?? 'N/A')
                ];

            case 'mobile_money':
                return [
                    'Phone: ' . (!empty($details['phone_number']) ? ('****' . substr((string)$details['phone_number'], -4)) : 'N/A'),
                    'Provider: ' . ($details['provider'] ?? 'N/A'),
                    'Account Name: ' . ($details['account_name'] ?? 'N/A')
                ];

            case 'paypal':
                return [
                    'Email: ' . ($details['email'] ?? 'N/A')
                ];

            case 'stripe':
                return [
                    'Account ID: ' . (!empty($details['account_id']) ? ('****' . substr((string)$details['account_id'], -4)) : 'N/A')
                ];

            default:
                return ['Details: Payment method configured'];
        }
    }
}
