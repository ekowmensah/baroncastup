<?php

namespace SmartCast\Controllers\SuperAdmin;

use SmartCast\Controllers\BaseController;
use SmartCast\Models\Payout;
use SmartCast\Models\TenantBalance;

/**
 * Super Admin Payout Controller
 * Handles payout approval and management for super admin
 */
class PayoutController extends BaseController
{
    private $payoutModel;
    private $balanceModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->payoutModel = new Payout();
        $this->balanceModel = new TenantBalance();
        
        // Ensure user is super admin
        if (!$this->session->isSuperAdmin()) {
            $this->redirect(APP_URL, 'Access denied', 'error');
        }
    }
    
    /**
     * Payout management dashboard
     */
    public function index()
    {
        try {
            $stats = $this->payoutModel->getPayoutStatistics();
            $pendingPayouts = $this->payoutModel->getPendingPayouts();
            $approvedPayouts = $this->payoutModel->getApprovedPayouts();
            $recentPayouts = $this->payoutModel->getRecentPayouts(20);
            
            $this->view('superadmin/payouts/index', [
                'title' => 'Payout Management',
                'breadcrumbs' => [
                    ['title' => 'Super Admin', 'url' => SUPERADMIN_URL],
                    ['title' => 'Payouts']
                ],
                'stats' => $stats,
                'pending_payouts' => $pendingPayouts,
                'approved_payouts' => $approvedPayouts,
                'recent_payouts' => $recentPayouts
            ]);
            
        } catch (\Exception $e) {
            error_log('Super admin payout dashboard error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL, 'Error loading payout dashboard', 'error');
        }
    }
    
    /**
     * Pending payouts for approval
     */
    public function pending()
    {
        try {
            $pendingPayouts = $this->payoutModel->getPendingPayouts();
            
            $this->view('superadmin/payouts/pending', [
                'title' => 'Pending Payouts',
                'breadcrumbs' => [
                    ['title' => 'Super Admin', 'url' => SUPERADMIN_URL],
                    ['title' => 'Payouts', 'url' => SUPERADMIN_URL . '/payouts'],
                    ['title' => 'Pending Approval']
                ],
                'payouts' => $pendingPayouts
            ]);
            
        } catch (\Exception $e) {
            error_log('Pending payouts error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/payouts', 'Error loading pending payouts', 'error');
        }
    }
    
    /**
     * Approve payout
     */
    public function approve($payoutId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processApproval($payoutId);
        }
        
        try {
            $payout = $this->payoutModel->getPayoutWithDetails($payoutId);
            
            if (!$payout || $payout['status'] !== 'pending') {
                $this->redirect(SUPERADMIN_URL . '/payouts/pending', 'Invalid payout request', 'error');
                return;
            }
            
            // Get additional required data
            $tenant = $this->getTenantDetails($payout['tenant_id']);
            $balance = $this->balanceModel->getBalance($payout['tenant_id']);
            $payout_method = $this->getPayoutMethodDetails($payout['payout_method_id'] ?? null);
            $tenant_stats = $this->getTenantStats($payout['tenant_id']);
            $audit_logs = $this->getPayoutAuditLogs($payoutId);
            
            $this->view('superadmin/payouts/approve', [
                'title' => 'Approve Payout',
                'breadcrumbs' => [
                    ['title' => 'Super Admin', 'url' => SUPERADMIN_URL],
                    ['title' => 'Payouts', 'url' => SUPERADMIN_URL . '/payouts'],
                    ['title' => 'Pending', 'url' => SUPERADMIN_URL . '/payouts/pending'],
                    ['title' => 'Approve']
                ],
                'payout' => $payout,
                'tenant' => $tenant,
                'balance' => $balance,
                'payout_method' => $payout_method,
                'tenant_stats' => $tenant_stats,
                'audit_logs' => $audit_logs
            ]);
            
        } catch (\Exception $e) {
            error_log('Approve payout form error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/payouts/pending', 'Error loading payout details', 'error');
        }
    }
    
    /**
     * Process payout approval
     */
    public function processApproval($payoutId)
    {
        $this->validateCsrf(SUPERADMIN_URL . '/payouts/approve/' . $payoutId);
        
        try {
            $action = $_POST['action'] ?? '';
            $notes = trim($_POST['notes'] ?? '');
            $reason = trim($_POST['reason'] ?? '');
            $adminId = $this->session->getUserId();
            
            if ($action === 'approve') {
                $this->payoutModel->approvePayout($payoutId, $adminId, $notes);
                $this->redirect(SUPERADMIN_URL . '/payouts/pending', 
                    'Payout approved successfully', 
                    'success');
                    
            } elseif ($action === 'reject') {
                if (empty($reason)) {
                    $this->redirect(SUPERADMIN_URL . '/payouts/approve/' . $payoutId, 
                        'Rejection reason is required', 
                        'error');
                    return;
                }
                
                $this->payoutModel->rejectPayout($payoutId, $adminId, $reason);
                $this->redirect(SUPERADMIN_URL . '/payouts/pending', 
                    'Payout rejected successfully', 
                    'success');
                    
            } else {
                throw new \Exception('Invalid action');
            }
            
        } catch (\Exception $e) {
            error_log('Process payout approval error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/payouts/approve/' . $payoutId, 
                'Error processing payout: ' . $e->getMessage(), 
                'error');
        }
    }
    
    /**
     * Process approved payouts
     */
    public function process($payoutId)
    {
        $this->validateCsrf(SUPERADMIN_URL . '/payouts');
        
        try {
            $result = $this->payoutModel->processPayout($payoutId);
            
            if ($result) {
                $this->redirect(SUPERADMIN_URL . '/payouts', 
                    'Payout processed successfully', 
                    'success');
            } else {
                $this->redirect(SUPERADMIN_URL . '/payouts', 
                    'Payout processing failed', 
                    'error');
            }
            
        } catch (\Exception $e) {
            error_log('Process payout error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/payouts', 
                'Error processing payout: ' . $e->getMessage(), 
                'error');
        }
    }
    
    /**
     * Bulk approve payouts
     */
    public function bulkApprove()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(SUPERADMIN_URL . '/payouts/pending', 'Invalid request', 'error');
            return;
        }
        
        $this->validateCsrf(SUPERADMIN_URL . '/payouts');
        
        try {
            $payoutIds = $_POST['payout_ids'] ?? [];
            $notes = trim($_POST['bulk_notes'] ?? '');
            $adminId = $this->session->getUserId();
            
            if (empty($payoutIds)) {
                $this->redirect(SUPERADMIN_URL . '/payouts/pending', 
                    'No payouts selected', 
                    'error');
                return;
            }
            
            $approved = 0;
            foreach ($payoutIds as $payoutId) {
                try {
                    $this->payoutModel->approvePayout($payoutId, $adminId, $notes);
                    $approved++;
                } catch (\Exception $e) {
                    error_log('Bulk approve error for payout ' . $payoutId . ': ' . $e->getMessage());
                }
            }
            
            $this->redirect(SUPERADMIN_URL . '/payouts/pending', 
                "Successfully approved {$approved} payout(s)", 
                'success');
                
        } catch (\Exception $e) {
            error_log('Bulk approve error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/payouts/pending', 
                'Error processing bulk approval', 
                'error');
        }
    }
    
    /**
     * Payout details
     */
    public function details($payoutId)
    {
        try {
            $payout = $this->payoutModel->getPayoutWithDetails($payoutId);
            
            if (!$payout) {
                $this->redirect(SUPERADMIN_URL . '/payouts', 'Payout not found', 'error');
                return;
            }
            
            // Get additional data
            $tenant = $this->getTenantDetails($payout['tenant_id']);
            $balance = $this->balanceModel->getBalance($payout['tenant_id']);
            $payout_method = $this->getPayoutMethodDetails($payout['payout_method_id'] ?? null);
            $tenant_stats = $this->getTenantStats($payout['tenant_id']);
            $audit_logs = $this->getPayoutAuditLogs($payoutId);
            
            $this->view('superadmin/payouts/details', [
                'title' => 'Payout Details',
                'breadcrumbs' => [
                    ['title' => 'Super Admin', 'url' => SUPERADMIN_URL],
                    ['title' => 'Payouts', 'url' => SUPERADMIN_URL . '/payouts'],
                    ['title' => 'Details']
                ],
                'payout' => $payout,
                'tenant' => $tenant,
                'balance' => $balance,
                'payout_method' => $payout_method,
                'tenant_stats' => $tenant_stats,
                'audit_logs' => $audit_logs
            ]);
            
        } catch (\Exception $e) {
            error_log('Payout details error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/payouts', 'Error loading payout details', 'error');
        }
    }
    
    /**
     * API: Get payout statistics
     */
    public function apiStats()
    {
        try {
            $stats = $this->payoutModel->getPayoutStatistics();
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            error_log('API stats error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Failed to load statistics'
            ]);
        }
    }
    
    /**
     * Reject payout form
     */
    public function reject($payoutId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processRejection($payoutId);
        }
        
        try {
            $payout = $this->payoutModel->getPayoutWithDetails($payoutId);
            
            if (!$payout || $payout['status'] !== 'pending') {
                $this->redirect(SUPERADMIN_URL . '/payouts', 'Payout not found or already processed', 'error');
                return;
            }
            
            $this->view('superadmin/payouts/reject', [
                'title' => 'Reject Payout',
                'breadcrumbs' => [
                    ['title' => 'Super Admin', 'url' => SUPERADMIN_URL],
                    ['title' => 'Payouts', 'url' => SUPERADMIN_URL . '/payouts'],
                    ['title' => 'Reject Payout']
                ],
                'payout' => $payout
            ]);
            
        } catch (\Exception $e) {
            error_log('Reject payout form error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/payouts', 'Error loading payout details', 'error');
        }
    }
    
    /**
     * Process payout rejection
     */
    public function processRejection($payoutId)
    {
        $this->validateCsrf(SUPERADMIN_URL . '/payouts/reject/' . $payoutId);
        
        try {
            $reason = trim($_POST['reason'] ?? '');
            $adminId = $this->session->getUserId();
            
            if (empty($reason)) {
                $this->redirect(SUPERADMIN_URL . '/payouts/reject/' . $payoutId, 
                    'Please provide a reason for rejection', 'error');
                return;
            }
            
            $this->payoutModel->rejectPayout($payoutId, $adminId, $reason);
            $this->redirect(SUPERADMIN_URL . '/payouts', 
                'Payout rejected successfully', 'success');
                
        } catch (\Exception $e) {
            error_log('Process payout rejection error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/payouts/reject/' . $payoutId, 
                'Error processing rejection: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Reverse processed payout back to approved
     */
    public function reverseProcessed($payoutId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processReverseProcessed($payoutId);
        }
        
        try {
            $payout = $this->payoutModel->getPayoutWithDetails($payoutId);
            
            if (!$payout || $payout['status'] !== 'paid') {
                $this->redirect(SUPERADMIN_URL . '/payouts', 'Payout not found or not in paid status', 'error');
                return;
            }
            
            $this->view('superadmin/payouts/reverse-processed', [
                'title' => 'Reverse Processed Payout',
                'breadcrumbs' => [
                    ['title' => 'Super Admin', 'url' => SUPERADMIN_URL],
                    ['title' => 'Payouts', 'url' => SUPERADMIN_URL . '/payouts'],
                    ['title' => 'Reverse Processed']
                ],
                'payout' => $payout
            ]);
            
        } catch (\Exception $e) {
            error_log('Reverse processed payout form error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/payouts', 'Error loading payout details', 'error');
        }
    }
    
    /**
     * Process reversal from processed to approved
     */
    public function processReverseProcessed($payoutId)
    {
        $this->validateCsrf(SUPERADMIN_URL . '/payouts/reverse-processed/' . $payoutId);
        
        try {
            $reason = trim($_POST['reason'] ?? '');
            $adminId = $this->session->getUserId();
            
            if (empty($reason)) {
                $this->redirect(SUPERADMIN_URL . '/payouts/reverse-processed/' . $payoutId, 
                    'Please provide a reason for reversal', 'error');
                return;
            }
            
            $this->payoutModel->reverseProcessedToApproved($payoutId, $adminId, $reason);
            $this->redirect(SUPERADMIN_URL . '/payouts', 
                'Payout reversed from processed to approved successfully', 'success');
                
        } catch (\Exception $e) {
            error_log('Process reverse processed error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/payouts/reverse-processed/' . $payoutId, 
                'Error processing reversal: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Reverse approved payout back to pending
     */
    public function reverseApproved($payoutId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processReverseApproved($payoutId);
        }
        
        try {
            $payout = $this->payoutModel->getPayoutWithDetails($payoutId);
            
            if (!$payout || $payout['status'] !== 'approved') {
                $this->redirect(SUPERADMIN_URL . '/payouts', 'Payout not found or not in approved status', 'error');
                return;
            }
            
            $this->view('superadmin/payouts/reverse-approved', [
                'title' => 'Reverse Approved Payout',
                'breadcrumbs' => [
                    ['title' => 'Super Admin', 'url' => SUPERADMIN_URL],
                    ['title' => 'Payouts', 'url' => SUPERADMIN_URL . '/payouts'],
                    ['title' => 'Reverse Approved']
                ],
                'payout' => $payout
            ]);
            
        } catch (\Exception $e) {
            error_log('Reverse approved payout form error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/payouts', 'Error loading payout details', 'error');
        }
    }
    
    /**
     * Process reversal from approved to pending
     */
    public function processReverseApproved($payoutId)
    {
        $this->validateCsrf(SUPERADMIN_URL . '/payouts/reverse-approved/' . $payoutId);
        
        try {
            $reason = trim($_POST['reason'] ?? '');
            $adminId = $this->session->getUserId();
            
            if (empty($reason)) {
                $this->redirect(SUPERADMIN_URL . '/payouts/reverse-approved/' . $payoutId, 
                    'Please provide a reason for reversal', 'error');
                return;
            }
            
            $this->payoutModel->reverseApprovedToPending($payoutId, $adminId, $reason);
            $this->redirect(SUPERADMIN_URL . '/payouts', 
                'Payout reversed from approved to pending successfully', 'success');
                
        } catch (\Exception $e) {
            error_log('Process reverse approved error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/payouts/reverse-approved/' . $payoutId, 
                'Error processing reversal: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Recalculate processing fees for a payout
     */
    public function recalculateFees($payoutId)
    {
        try {
            $payoutService = new \SmartCast\Services\PayoutService();
            $result = $payoutService->recalculateProcessingFee($payoutId);
            
            if ($result['success']) {
                $message = sprintf(
                    'Processing fees recalculated successfully! Fee: $%.2f, Net: $%.2f (Method: %s)', 
                    $result['processing_fee'], 
                    $result['net_amount'],
                    $result['method_type'] ?? 'unknown'
                );
                $this->redirect(SUPERADMIN_URL . '/payouts/details/' . $payoutId, $message, 'success');
            } else {
                $this->redirect(SUPERADMIN_URL . '/payouts/details/' . $payoutId, 
                    'Error recalculating fees: ' . $result['error'], 'error');
            }
            
        } catch (\Exception $e) {
            error_log('Recalculate fees error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/payouts/details/' . $payoutId, 
                'Error recalculating fees: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Debug payout methods for a tenant (only available in debug mode)
     */
    public function debugPayoutMethods($payoutId)
    {
        // Only allow in debug mode
        $debugEnabled = defined('APP_DEBUG') ? APP_DEBUG : (getenv('APP_DEBUG') === 'true');
        if (!$debugEnabled) {
            $this->redirect(SUPERADMIN_URL . '/payouts', 'Debug mode is not enabled', 'error');
            return;
        }
        
        try {
            $payout = $this->payoutModel->find($payoutId);
            if (!$payout) {
                echo "Payout not found";
                return;
            }
            
            $payoutMethodModel = new \SmartCast\Models\PayoutMethod();
            
            echo "<h3>Debug Info for Payout ID: " . htmlspecialchars($payoutId) . "</h3>";
            echo "<h4>Payout Data:</h4>";
            echo "<pre>" . htmlspecialchars(json_encode($payout, JSON_PRETTY_PRINT)) . "</pre>";
            
            echo "<h4>All Payout Methods for Tenant {$payout['tenant_id']}:</h4>";
            $allMethods = $payoutMethodModel->getMethodsByTenant($payout['tenant_id'], false);
            echo "<pre>" . htmlspecialchars(json_encode($allMethods, JSON_PRETTY_PRINT)) . "</pre>";
            
            echo "<h4>Default Payout Method:</h4>";
            $defaultMethod = $payoutMethodModel->getDefaultMethod($payout['tenant_id']);
            echo "<pre>" . htmlspecialchars(json_encode($defaultMethod, JSON_PRETTY_PRINT)) . "</pre>";
            
            if (!empty($payout['payout_method_id'])) {
                echo "<h4>Payout Method by ID ({$payout['payout_method_id']}):</h4>";
                $methodById = $payoutMethodModel->find($payout['payout_method_id']);
                echo "<pre>" . htmlspecialchars(json_encode($methodById, JSON_PRETTY_PRINT)) . "</pre>";
            }
            
            // Test fee calculation
            $payoutService = new \SmartCast\Services\PayoutService();
            echo "<h4>Fee Calculation Test:</h4>";
            if ($defaultMethod) {
                $testFee = $payoutService->recalculateProcessingFee($payoutId);
                echo "<pre>" . htmlspecialchars(json_encode($testFee, JSON_PRETTY_PRINT)) . "</pre>";
            } else {
                echo "No default method found for fee calculation";
            }
            
        } catch (\Exception $e) {
            echo "Error: " . htmlspecialchars($e->getMessage());
        }
        exit;
    }
    
    /**
     * Download payout receipt
     */
    public function downloadReceipt($payoutId)
    {
        try {
            $payout = $this->payoutModel->getPayoutWithDetails($payoutId);
            
            if (!$payout || $payout['status'] !== 'paid') {
                $this->redirect(SUPERADMIN_URL . '/payouts', 'Receipt not available', 'error');
                return;
            }
            
            // Generate and download receipt
            $this->generatePayoutReceipt($payout);
            
        } catch (\Exception $e) {
            error_log('Download receipt error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/payouts', 'Error downloading receipt', 'error');
        }
    }
    
    /**
     * Payout history
     */
    public function history()
    {
        try {
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = 25;
            $offset = ($page - 1) * $perPage;
            $statusFilter = $_GET['status'] ?? '';
            $search = trim($_GET['search'] ?? '');
            
            $db = new \SmartCast\Core\Database();
            
            // Build WHERE clause
            $where = '1=1';
            $params = [];
            
            if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'processing', 'paid', 'failed', 'rejected', 'cancelled'])) {
                $where .= ' AND p.status = :status';
                $params['status'] = $statusFilter;
            }
            
            if ($search) {
                $where .= ' AND (p.payout_id LIKE :search OR t.name LIKE :search2)';
                $params['search'] = "%{$search}%";
                $params['search2'] = "%{$search}%";
            }
            
            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM payouts p 
                         INNER JOIN tenants t ON p.tenant_id = t.id 
                         WHERE {$where}";
            $total = $db->selectOne($countSql, $params)['total'] ?? 0;
            $totalPages = max(1, ceil($total / $perPage));
            
            // Get paginated payouts with tenant details
            $sql = "SELECT p.*, t.name as tenant_name, t.email as tenant_email
                    FROM payouts p 
                    INNER JOIN tenants t ON p.tenant_id = t.id 
                    WHERE {$where}
                    ORDER BY p.created_at DESC 
                    LIMIT {$perPage} OFFSET {$offset}";
            $payouts = $db->select($sql, $params);
            
            $this->view('superadmin/payouts/history', [
                'title' => 'Payout History',
                'breadcrumbs' => [
                    ['title' => 'Super Admin', 'url' => SUPERADMIN_URL],
                    ['title' => 'Payouts', 'url' => SUPERADMIN_URL . '/payouts'],
                    ['title' => 'History']
                ],
                'payouts' => $payouts,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $total,
                'per_page' => $perPage,
                'status_filter' => $statusFilter,
                'search' => $search
            ]);
            
        } catch (\Exception $e) {
            error_log('Payout history error: ' . $e->getMessage());
            $this->redirect(SUPERADMIN_URL . '/payouts', 'Error loading payout history', 'error');
        }
    }
    
    /**
     * Generate payout receipt
     */
    private function generatePayoutReceipt($payout)
    {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="payout-receipt-' . $payout['payout_id'] . '.txt"');
        
        echo "============================================\n";
        echo "         SMARTCAST PAYOUT RECEIPT           \n";
        echo "============================================\n\n";
        echo "Payout ID:      " . $payout['payout_id'] . "\n";
        echo "Organizer:      " . $payout['tenant_name'] . "\n";
        echo "--------------------------------------------\n";
        echo "Amount:          GHS " . number_format($payout['amount'], 2) . "\n";
        echo "Processing Fee:  GHS " . number_format($payout['processing_fee'] ?? 0, 2) . "\n";
        echo "Net Amount:      GHS " . number_format($payout['net_amount'] ?? $payout['amount'], 2) . "\n";
        echo "--------------------------------------------\n";
        echo "Method:          " . ucfirst(str_replace('_', ' ', $payout['payout_method'])) . "\n";
        echo "Status:          " . ucfirst($payout['status']) . "\n";
        if (!empty($payout['provider_reference'])) {
            echo "Reference:       " . $payout['provider_reference'] . "\n";
        }
        echo "Requested:       " . ($payout['created_at'] ? date('F j, Y g:i A', strtotime($payout['created_at'])) : 'N/A') . "\n";
        if (!empty($payout['approved_at'])) {
            echo "Approved:        " . date('F j, Y g:i A', strtotime($payout['approved_at'])) . "\n";
        }
        echo "Processed:       " . ($payout['processed_at'] ? date('F j, Y g:i A', strtotime($payout['processed_at'])) : 'N/A') . "\n";
        echo "\n============================================\n";
        echo "This receipt confirms the successful payout.\n";
        echo "Generated on: " . date('F j, Y g:i A') . "\n";
        echo "============================================\n";
    }
    
    /**
     * Get tenant details
     */
    private function getTenantDetails($tenantId)
    {
        try {
            $db = new \SmartCast\Core\Database();
            return $db->selectOne("
                SELECT t.*, sp.name as plan_name 
                FROM tenants t 
                LEFT JOIN subscription_plans sp ON t.current_plan_id = sp.id 
                WHERE t.id = :tenant_id
            ", ['tenant_id' => $tenantId]);
        } catch (\Exception $e) {
            error_log('Get tenant details error: ' . $e->getMessage());
            return [
                'id' => $tenantId,
                'name' => 'Unknown Tenant',
                'email' => 'unknown@example.com',
                'phone' => '',
                'verified' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'plan_name' => 'Unknown Plan'
            ];
        }
    }
    
    /**
     * Get payout method details
     */
    private function getPayoutMethodDetails($payoutMethodId)
    {
        if (!$payoutMethodId) {
            return null;
        }
        
        try {
            $db = new \SmartCast\Core\Database();
            return $db->selectOne("SELECT * FROM payout_methods WHERE id = :id", ['id' => $payoutMethodId]);
        } catch (\Exception $e) {
            error_log('Get payout method details error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get tenant statistics
     */
    private function getTenantStats($tenantId)
    {
        try {
            $db = new \SmartCast\Core\Database();
            
            // Get total events
            $totalEvents = $db->selectOne("SELECT COUNT(*) as count FROM events WHERE tenant_id = :tenant_id", ['tenant_id' => $tenantId])['count'] ?? 0;
            
            // Get total revenue
            $totalRevenue = $db->selectOne("
                SELECT COALESCE(SUM(net_tenant_amount), 0) as total 
                FROM revenue_transactions 
                WHERE tenant_id = :tenant_id
            ", ['tenant_id' => $tenantId])['total'] ?? 0;
            
            // Get previous payouts count
            $previousPayouts = $db->selectOne("
                SELECT COUNT(*) as count 
                FROM payouts 
                WHERE tenant_id = :tenant_id AND status = 'paid'
            ", ['tenant_id' => $tenantId])['count'] ?? 0;
            
            return [
                'total_events' => $totalEvents,
                'total_revenue' => $totalRevenue,
                'previous_payouts' => $previousPayouts
            ];
        } catch (\Exception $e) {
            error_log('Get tenant stats error: ' . $e->getMessage());
            return [
                'total_events' => 0,
                'total_revenue' => 0,
                'previous_payouts' => 0
            ];
        }
    }
    
    /**
     * Get payout audit logs
     */
    private function getPayoutAuditLogs($payoutId)
    {
        try {
            $db = new \SmartCast\Core\Database();
            return $db->select("
                SELECT pal.*, u.name as performed_by_name 
                FROM payout_audit_log pal 
                LEFT JOIN users u ON pal.performed_by = u.id 
                WHERE pal.payout_id = :payout_id
                ORDER BY pal.created_at DESC
            ", ['payout_id' => $payoutId]);
        } catch (\Exception $e) {
            error_log('Get payout audit logs error: ' . $e->getMessage());
            return [];
        }
    }
}
