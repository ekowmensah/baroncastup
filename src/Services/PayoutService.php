<?php

namespace SmartCast\Services;

use SmartCast\Models\Payout;
use SmartCast\Models\PayoutMethod;
use SmartCast\Models\PayoutSchedule;
use SmartCast\Models\TenantBalance;
use SmartCast\Models\RevenueTransaction;

/**
 * Payout Service
 * Handles all payout-related operations and calculations
 */
class PayoutService
{
    private $payoutModel;
    private $payoutMethodModel;
    private $payoutScheduleModel;
    private $balanceModel;
    private $revenueModel;
    
    public function __construct()
    {
        $this->payoutModel = new Payout();
        $this->payoutMethodModel = new PayoutMethod();
        $this->payoutScheduleModel = new PayoutSchedule();
        $this->balanceModel = new TenantBalance();
        $this->revenueModel = new RevenueTransaction();
    }
    
    /**
     * Process revenue from a successful transaction
     */
    public function processTransactionRevenue($transactionId, $tenantId, $eventId, $grossAmount, $feeRules = null)
    {
        try {
            // Create revenue transaction record
            $revenueTransactionId = $this->revenueModel->createRevenueTransaction(
                $transactionId, 
                $tenantId, 
                $eventId, 
                $grossAmount, 
                $feeRules
            );
            
            // Get the revenue breakdown
            $revenueTransaction = $this->revenueModel->find($revenueTransactionId);
            
            // Update tenant balance
            $this->balanceModel->addEarnings($tenantId, $revenueTransaction['net_tenant_amount']);
            
            // Check for instant payout eligibility
            $this->checkInstantPayoutEligibility($tenantId);
            
            return [
                'success' => true,
                'revenue_transaction_id' => $revenueTransactionId,
                'net_amount' => $revenueTransaction['net_tenant_amount'],
                'platform_fee' => $revenueTransaction['platform_fee'],
                'processing_fee' => $revenueTransaction['processing_fee']
            ];
            
        } catch (\Exception $e) {
            error_log('Revenue processing failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Request a manual payout
     */
    public function requestPayout($tenantId, $amount, $payoutMethodId = null)
    {
        try {
            // Get payout method
            $payoutMethod = $payoutMethodId 
                ? $this->payoutMethodModel->find($payoutMethodId)
                : $this->payoutMethodModel->getDefaultMethod($tenantId);
            
            if (!$payoutMethod) {
                throw new \Exception('No payout method available');
            }
            
            // Debug logging
            error_log("Payout method found: " . json_encode($payoutMethod));
            
            // Check payout eligibility
            $schedule = $this->payoutScheduleModel->getScheduleByTenant($tenantId);
            $eligibility = $this->payoutScheduleModel->canRequestPayout($tenantId, $amount);
            
            if (!$eligibility['allowed']) {
                throw new \Exception($eligibility['reason']);
            }
            
            // Check balance
            if (!$this->balanceModel->canRequestPayout($tenantId, $amount)) {
                throw new \Exception('Insufficient balance for payout request');
            }
            
            // Calculate processing fee
            $processingFee = $this->calculateProcessingFee($amount, $payoutMethod['method_type']);
            $netAmount = $amount - $processingFee;
            
            // Create payout record
            $payoutId = $this->payoutModel->create([
                'tenant_id' => $tenantId,
                'payout_id' => $this->generatePayoutId(),
                'amount' => $amount,
                'processing_fee' => $processingFee,
                'net_amount' => $netAmount,
                'payout_method' => $payoutMethod['method_type'],
                'payout_method_id' => $payoutMethod['id'],
                'payout_type' => 'manual',
                'recipient_details' => $payoutMethod['account_details'],
                'status' => Payout::STATUS_QUEUED
            ]);
            
            // Reserve the amount from available balance (move to pending, not paid)
            $this->balanceModel->reserveForPayout($tenantId, $amount);
            
            return [
                'success' => true,
                'payout_id' => $payoutId,
                'amount' => $amount,
                'processing_fee' => $processingFee,
                'net_amount' => $netAmount
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Process automatic payouts
     */
    public function processAutomaticPayouts()
    {
        $results = [
            'processed' => 0,
            'failed' => 0,
            'total_amount' => 0,
            'errors' => []
        ];
        
        try {
            // Get tenants eligible for automatic payout
            $eligibleTenants = $this->payoutScheduleModel->getTenantsForAutoPayout();
            
            foreach ($eligibleTenants as $tenant) {
                try {
                    $result = $this->processAutomaticPayout($tenant);
                    
                    if ($result['success']) {
                        $results['processed']++;
                        $results['total_amount'] += $result['amount'];
                    } else {
                        $results['failed']++;
                        $results['errors'][] = "Tenant {$tenant['tenant_id']}: " . $result['error'];
                    }
                    
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Tenant {$tenant['tenant_id']}: " . $e->getMessage();
                }
            }
            
        } catch (\Exception $e) {
            $results['errors'][] = 'General error: ' . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Process instant payouts
     */
    public function processInstantPayouts()
    {
        $results = [
            'processed' => 0,
            'failed' => 0,
            'total_amount' => 0,
            'errors' => []
        ];
        
        try {
            // Get tenants eligible for instant payout
            $eligibleTenants = $this->payoutScheduleModel->getTenantsForInstantPayout();
            
            foreach ($eligibleTenants as $tenant) {
                try {
                    $result = $this->processInstantPayout($tenant);
                    
                    if ($result['success']) {
                        $results['processed']++;
                        $results['total_amount'] += $result['amount'];
                    } else {
                        $results['failed']++;
                        $results['errors'][] = "Tenant {$tenant['tenant_id']}: " . $result['error'];
                    }
                    
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Tenant {$tenant['tenant_id']}: " . $e->getMessage();
                }
            }
            
        } catch (\Exception $e) {
            $results['errors'][] = 'General error: ' . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Get payout dashboard data
     */
    public function getPayoutDashboard($tenantId)
    {
        $balance = $this->balanceModel->getBalance($tenantId);
        $schedule = $this->payoutScheduleModel->getScheduleByTenant($tenantId);
        // Get recent payouts excluding cancelled ones
        $allPayouts = $this->payoutModel->getPayoutsByTenant($tenantId, null);
        $recentPayouts = array_filter($allPayouts, function($payout) {
            return $payout['status'] !== 'cancelled';
        });
        $payoutMethods = $this->payoutMethodModel->getMethodsByTenant($tenantId);
        $revenueStats = $this->revenueModel->getRevenueByTenant($tenantId);
        
        return [
            'balance' => $balance,
            'schedule' => $schedule,
            'recent_payouts' => array_slice($recentPayouts, 0, 10),
            'payout_methods' => $payoutMethods,
            'revenue_stats' => $revenueStats,
            'can_request_payout' => $this->balanceModel->canRequestPayout($tenantId, $schedule['minimum_amount'])
        ];
    }
    
    /**
     * Get platform payout analytics
     */
    public function getPlatformAnalytics($startDate = null, $endDate = null)
    {
        $platformRevenue = $this->revenueModel->getPlatformRevenue($startDate, $endDate);
        $payoutStats = $this->payoutModel->getPayoutStats();
        $topEarners = $this->revenueModel->getTopEarningTenants(10, $startDate, $endDate);
        $scheduleStats = $this->payoutScheduleModel->getScheduleStats();
        
        return [
            'platform_revenue' => $platformRevenue,
            'payout_stats' => $payoutStats,
            'top_earners' => $topEarners,
            'schedule_stats' => $scheduleStats
        ];
    }
    
    private function processAutomaticPayout($tenant)
    {
        // Get default payout method
        $payoutMethod = $this->payoutMethodModel->getDefaultMethod($tenant['tenant_id']);
        
        if (!$payoutMethod || !$payoutMethod['is_verified']) {
            return [
                'success' => false,
                'error' => 'No verified payout method available'
            ];
        }
        
        // Calculate payout amount (use available balance)
        $amount = $tenant['available'];
        $processingFee = $this->calculateProcessingFee($amount, $payoutMethod['method_type']);
        $netAmount = $amount - $processingFee;
        
        // Create payout record
        $payoutId = $this->payoutModel->create([
            'tenant_id' => $tenant['tenant_id'],
            'payout_id' => $this->generatePayoutId(),
            'amount' => $amount,
            'processing_fee' => $processingFee,
            'net_amount' => $netAmount,
            'payout_method' => $payoutMethod['method_type'],
            'payout_method_id' => $payoutMethod['id'],
            'payout_type' => 'automatic',
            'recipient_details' => $payoutMethod['account_details'],
            'status' => Payout::STATUS_QUEUED
        ]);
        
        // Process the payout
        $processed = $this->payoutModel->processPayout($payoutId);
        
        if ($processed) {
            // Update next payout date
            $this->payoutScheduleModel->updateNextPayoutDate($tenant['tenant_id']);
        }
        
        return [
            'success' => $processed,
            'amount' => $amount,
            'payout_id' => $payoutId
        ];
    }
    
    private function processInstantPayout($tenant)
    {
        // Similar to automatic payout but for instant threshold
        return $this->processAutomaticPayout($tenant);
    }
    
    private function checkInstantPayoutEligibility($tenantId)
    {
        $schedule = $this->payoutScheduleModel->getScheduleByTenant($tenantId);
        $balance = $this->balanceModel->getBalance($tenantId);
        
        if ($schedule['auto_payout_enabled'] && 
            $balance['available'] >= $schedule['instant_payout_threshold']) {
            
            // Trigger instant payout
            $this->processInstantPayout([
                'tenant_id' => $tenantId,
                'available' => $balance['available']
            ]);
        }
    }
    
    private function calculateProcessingFee($amount, $methodType)
    {
        // Processing fees by method type
        $feeStructure = [
            'bank_transfer' => ['percentage' => 1.0, 'fixed' => 0.50],
            'mobile_money' => ['percentage' => 1.5, 'fixed' => 0.25],
            'paypal' => ['percentage' => 2.9, 'fixed' => 0.30],
            'stripe' => ['percentage' => 2.9, 'fixed' => 0.30]
        ];
        
        // Default to bank_transfer if method type is null or not found
        $methodType = $methodType ?? 'bank_transfer';
        $fees = $feeStructure[$methodType] ?? $feeStructure['bank_transfer'];
        
        return round(($amount * $fees['percentage'] / 100) + $fees['fixed'], 2);
    }
    
    private function generatePayoutId()
    {
        return 'PO_' . date('Ymd') . '_' . strtoupper(uniqid());
    }
    
    /**
     * Recalculate processing fee for existing payout
     */
    public function recalculateProcessingFee($payoutId)
    {
        try {
            $payout = $this->payoutModel->find($payoutId);
            
            if (!$payout) {
                throw new \Exception('Payout not found');
            }
            
            // Get payout method
            $payoutMethod = null;
            if (!empty($payout['payout_method_id'])) {
                $payoutMethod = $this->payoutMethodModel->find($payout['payout_method_id']);
            }
            
            if (!$payoutMethod) {
                // Try to get default method for tenant
                $payoutMethod = $this->payoutMethodModel->getDefaultMethod($payout['tenant_id']);
            }
            
            if (!$payoutMethod) {
                // Try to get any active method for tenant
                $methods = $this->payoutMethodModel->getMethodsByTenant($payout['tenant_id']);
                if (!empty($methods)) {
                    $payoutMethod = $methods[0];
                }
            }
            
            if (!$payoutMethod) {
                throw new \Exception('No payout method available for fee calculation');
            }
            
            // Recalculate processing fee
            $methodType = $payoutMethod['method_type'] ?? 'bank_transfer';
            
            $processingFee = $this->calculateProcessingFee($payout['amount'], $methodType);
            $netAmount = $payout['amount'] - $processingFee;
            
            // Update payout with correct fees
            $this->payoutModel->update($payoutId, [
                'processing_fee' => $processingFee,
                'net_amount' => $netAmount,
                'payout_method' => $methodType
            ]);
            
            return [
                'success' => true,
                'processing_fee' => $processingFee,
                'net_amount' => $netAmount,
                'method_type' => $methodType
            ];
            
        } catch (\Exception $e) {
            error_log('Recalculate processing fee error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Retry failed payout
     */
    public function retryPayout($payoutId)
    {
        try {
            return $this->payoutModel->retryFailedPayout($payoutId);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Cancel pending payout
     */
    public function cancelPayout($payoutId, $reason = null)
    {
        try {
            return $this->payoutModel->cancelPayout($payoutId, $reason);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
}
