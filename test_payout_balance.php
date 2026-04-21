<?php
/**
 * Test script to verify tenant balance deduction after payout processing
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/src/Models/TenantBalance.php';
require_once __DIR__ . '/src/Models/Payout.php';

use SmartCast\Core\Database;
use SmartCast\Models\TenantBalance;
use SmartCast\Models\Payout;

echo "=== Testing Tenant Balance Deduction After Payout ===\n\n";

$db = new Database();
$balanceModel = new TenantBalance();
$payoutModel = new Payout();

// Test with tenant ID 44 which has paid payouts
$testTenantId = 44;

try {
    // Get current balance
    $balanceBefore = $balanceModel->getBalance($testTenantId);
    echo "Balance BEFORE recalculation:\n";
    echo "  Available: " . number_format($balanceBefore['available'], 2) . "\n";
    echo "  Pending: " . number_format($balanceBefore['pending'], 2) . "\n";
    echo "  Total Paid: " . number_format($balanceBefore['total_paid'], 2) . "\n";
    echo "  Total Earned: " . number_format($balanceBefore['total_earned'], 2) . "\n\n";
    
    // Check paid payouts in database
    $paidPayouts = $db->select(
        "SELECT id, payout_id, amount, status, processed_at 
         FROM payouts 
         WHERE tenant_id = :tenant_id AND status = 'paid'
         ORDER BY processed_at DESC 
         LIMIT 5",
        ['tenant_id' => $testTenantId]
    );
    
    echo "Paid payouts in database:\n";
    $totalPaidAmount = 0;
    foreach ($paidPayouts as $payout) {
        echo "  - {$payout['payout_id']}: " . number_format($payout['amount'], 2) . 
             " (Status: {$payout['status']}, Processed: {$payout['processed_at']})\n";
        $totalPaidAmount += $payout['amount'];
    }
    echo "  Total paid amount: " . number_format($totalPaidAmount, 2) . "\n\n";
    
    // Recalculate balance using the fixed method
    echo "Recalculating balance...\n";
    $result = $balanceModel->recalculateBalance($testTenantId);
    
    if ($result) {
        echo "Balance recalculated successfully!\n\n";
        
        // Get balance after recalculation
        $balanceAfter = $balanceModel->getBalance($testTenantId);
        echo "Balance AFTER recalculation:\n";
        echo "  Available: " . number_format($balanceAfter['available'], 2) . "\n";
        echo "  Pending: " . number_format($balanceAfter['pending'], 2) . "\n";
        echo "  Total Paid: " . number_format($balanceAfter['total_paid'], 2) . "\n";
        echo "  Total Earned: " . number_format($balanceAfter['total_earned'], 2) . "\n\n";
        
        // Check if total_paid matches the sum of paid payouts
        if (abs($balanceAfter['total_paid'] - $totalPaidAmount) < 0.01) {
            echo "  ✅ Total Paid matches sum of paid payouts!\n";
        } else {
            echo "  ❌ Total Paid does NOT match sum of paid payouts!\n";
            echo "     Balance total_paid: " . number_format($balanceAfter['total_paid'], 2) . "\n";
            echo "     Sum of payouts: " . number_format($totalPaidAmount, 2) . "\n";
        }
        
    } else {
        echo "❌ Failed to recalculate balance!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
