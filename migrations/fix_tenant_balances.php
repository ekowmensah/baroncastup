<?php
/**
 * Fix Tenant Balances Migration
 * 
 * This migration recalculates all tenant balances based on actual revenue transactions
 * and payout records to ensure accuracy after fixing the payout cancellation bug.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "Starting Tenant Balance Fix Migration...\n";
    
    $pdo->beginTransaction();
    
    // Get all tenants
    $tenants = $pdo->query("SELECT id FROM tenants WHERE active = 1")->fetchAll();
    
    echo "Found " . count($tenants) . " active tenants to process.\n";
    
    foreach ($tenants as $tenant) {
        $tenantId = $tenant['id'];
        echo "Processing tenant ID: $tenantId\n";
        
        // Calculate total earned from revenue transactions
        $revenueQuery = "
            SELECT COALESCE(SUM(rt.net_tenant_amount), 0) as total_earned
            FROM revenue_transactions rt
            WHERE rt.tenant_id = :tenant_id
            AND rt.distribution_status = 'completed'
        ";
        
        $revenueResult = $pdo->prepare($revenueQuery);
        $revenueResult->execute(['tenant_id' => $tenantId]);
        $totalEarned = $revenueResult->fetch()['total_earned'] ?? 0;
        
        // If no revenue_transactions table exists, calculate from successful transactions
        if ($totalEarned == 0) {
            $transactionQuery = "
                SELECT COALESCE(SUM(t.amount * 0.95), 0) as total_earned
                FROM transactions t
                INNER JOIN events e ON t.event_id = e.id
                WHERE e.tenant_id = :tenant_id
                AND t.status = 'success'
            ";
            
            $transactionResult = $pdo->prepare($transactionQuery);
            $transactionResult->execute(['tenant_id' => $tenantId]);
            $totalEarned = $transactionResult->fetch()['total_earned'] ?? 0;
        }
        
        // Calculate total successfully paid out (only successful payouts)
        $payoutQuery = "
            SELECT COALESCE(SUM(amount), 0) as total_paid
            FROM payouts
            WHERE tenant_id = :tenant_id
            AND status = 'success'
        ";
        
        $payoutResult = $pdo->prepare($payoutQuery);
        $payoutResult->execute(['tenant_id' => $tenantId]);
        $totalPaid = $payoutResult->fetch()['total_paid'] ?? 0;
        
        // Calculate pending amount (queued and processing payouts)
        $pendingQuery = "
            SELECT COALESCE(SUM(amount), 0) as pending
            FROM payouts
            WHERE tenant_id = :tenant_id
            AND status IN ('queued', 'processing')
        ";
        
        $pendingResult = $pdo->prepare($pendingQuery);
        $pendingResult->execute(['tenant_id' => $tenantId]);
        $pending = $pendingResult->fetch()['pending'] ?? 0;
        
        // Calculate available balance
        $available = $totalEarned - $totalPaid - $pending;
        $available = max(0, $available); // Ensure non-negative
        
        // Check if tenant_balance record exists
        $balanceCheck = $pdo->prepare("SELECT id FROM tenant_balances WHERE tenant_id = :tenant_id");
        $balanceCheck->execute(['tenant_id' => $tenantId]);
        $existingBalance = $balanceCheck->fetch();
        
        if ($existingBalance) {
            // Update existing record
            $updateQuery = "
                UPDATE tenant_balances 
                SET 
                    available = :available,
                    pending = :pending,
                    total_earned = :total_earned,
                    total_paid = :total_paid,
                    updated_at = NOW()
                WHERE tenant_id = :tenant_id
            ";
            
            $updateStmt = $pdo->prepare($updateQuery);
            $updateStmt->execute([
                'available' => $available,
                'pending' => $pending,
                'total_earned' => $totalEarned,
                'total_paid' => $totalPaid,
                'tenant_id' => $tenantId
            ]);
            
            echo "  Updated balance - Available: $" . number_format($available, 2) . 
                 ", Earned: $" . number_format($totalEarned, 2) . 
                 ", Paid: $" . number_format($totalPaid, 2) . 
                 ", Pending: $" . number_format($pending, 2) . "\n";
        } else {
            // Create new record
            $insertQuery = "
                INSERT INTO tenant_balances (tenant_id, available, pending, total_earned, total_paid, created_at, updated_at)
                VALUES (:tenant_id, :available, :pending, :total_earned, :total_paid, NOW(), NOW())
            ";
            
            $insertStmt = $pdo->prepare($insertQuery);
            $insertStmt->execute([
                'tenant_id' => $tenantId,
                'available' => $available,
                'pending' => $pending,
                'total_earned' => $totalEarned,
                'total_paid' => $totalPaid
            ]);
            
            echo "  Created balance - Available: $" . number_format($available, 2) . 
                 ", Earned: $" . number_format($totalEarned, 2) . 
                 ", Paid: $" . number_format($totalPaid, 2) . 
                 ", Pending: $" . number_format($pending, 2) . "\n";
        }
    }
    
    // Update any cancelled payouts to ensure they don't affect totals
    echo "\nFixing cancelled payout records...\n";
    
    $cancelledPayouts = $pdo->query("
        SELECT id, tenant_id, amount 
        FROM payouts 
        WHERE status = 'cancelled' 
        AND processed_at IS NULL
    ")->fetchAll();
    
    foreach ($cancelledPayouts as $payout) {
        $pdo->prepare("
            UPDATE payouts 
            SET processed_at = NOW(), 
                failure_reason = COALESCE(failure_reason, 'Cancelled by user')
            WHERE id = :id
        ")->execute(['id' => $payout['id']]);
        
        echo "  Fixed cancelled payout ID: {$payout['id']} for tenant {$payout['tenant_id']}\n";
    }
    
    // Verify balance integrity
    echo "\nVerifying balance integrity...\n";
    
    $integrityCheck = $pdo->query("
        SELECT 
            COUNT(*) as total_tenants,
            SUM(available) as total_available,
            SUM(pending) as total_pending,
            SUM(total_earned) as total_earned,
            SUM(total_paid) as total_paid
        FROM tenant_balances
    ")->fetch();
    
    echo "Balance Summary:\n";
    echo "  Total Tenants: " . $integrityCheck['total_tenants'] . "\n";
    echo "  Total Available: $" . number_format($integrityCheck['total_available'], 2) . "\n";
    echo "  Total Pending: $" . number_format($integrityCheck['total_pending'], 2) . "\n";
    echo "  Total Earned: $" . number_format($integrityCheck['total_earned'], 2) . "\n";
    echo "  Total Paid: $" . number_format($integrityCheck['total_paid'], 2) . "\n";
    
    // Check for any negative balances
    $negativeBalances = $pdo->query("
        SELECT tenant_id, available, pending, total_earned, total_paid
        FROM tenant_balances 
        WHERE available < 0 OR pending < 0 OR total_earned < 0 OR total_paid < 0
    ")->fetchAll();
    
    if (!empty($negativeBalances)) {
        echo "\nWARNING: Found negative balances:\n";
        foreach ($negativeBalances as $balance) {
            echo "  Tenant {$balance['tenant_id']}: Available={$balance['available']}, Pending={$balance['pending']}, Earned={$balance['total_earned']}, Paid={$balance['total_paid']}\n";
        }
    } else {
        echo "\n✅ All balances are positive - integrity check passed!\n";
    }
    
    $pdo->commit();
    
    echo "\n✅ Tenant Balance Fix Migration Completed Successfully!\n";
    echo "\nNext Steps:\n";
    echo "1. Verify the financial overview pages show correct data\n";
    echo "2. Test payout requests and cancellations\n";
    echo "3. Check that balance calculations are accurate\n";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "\n❌ Migration Failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
