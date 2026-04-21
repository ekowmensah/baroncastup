<?php
/**
 * Fix Transaction 45 - Create Missing Revenue Share
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/autoloader.php';

$db = new \SmartCast\Core\Database();
$revenueShareModel = new \SmartCast\Models\RevenueShare();
$tenantBalanceModel = new \SmartCast\Models\TenantBalance();

echo "=== Fixing Transaction 45 ===\n";

try {
    $db->beginTransaction();
    
    // Get transaction 45 details
    $transaction = $db->selectOne("SELECT * FROM transactions WHERE id = 45");
    
    if (!$transaction) {
        throw new \Exception("Transaction 45 not found");
    }
    
    echo "Transaction 45 details:\n";
    echo "- Amount: $" . $transaction['amount'] . "\n";
    echo "- Tenant ID: " . $transaction['tenant_id'] . "\n";
    echo "- Event ID: " . $transaction['event_id'] . "\n";
    
    // Check if revenue share already exists
    $existingShare = $db->selectOne("SELECT * FROM revenue_shares WHERE transaction_id = 45");
    
    if ($existingShare) {
        echo "❌ Revenue share already exists for transaction 45\n";
        $db->rollback();
        exit(0);
    }
    
    // Create revenue share using the fixed method
    echo "Creating revenue share...\n";
    $revenueShare = $revenueShareModel->calculateAndCreateShare(
        $transaction['id'],
        $transaction['amount'],
        $transaction['tenant_id'],
        $transaction['event_id']
    );
    
    if ($revenueShare) {
        echo "✅ Revenue share created:\n";
        echo "- Platform Fee: $" . $revenueShare['amount'] . "\n";
        echo "- Tenant Earnings: $" . ($transaction['amount'] - $revenueShare['amount']) . "\n";
        
        // Update tenant balance
        $tenantEarnings = $transaction['amount'] - $revenueShare['amount'];
        $tenantBalanceModel->addEarnings($transaction['tenant_id'], $tenantEarnings);
        
        echo "✅ Tenant balance updated with $" . $tenantEarnings . "\n";
        
        $db->commit();
        echo "🎉 Transaction 45 fixed successfully!\n";
    } else {
        throw new \Exception("Failed to create revenue share");
    }
    
} catch (\Exception $e) {
    $db->rollback();
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
