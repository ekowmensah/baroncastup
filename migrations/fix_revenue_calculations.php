<?php
/**
 * Fix Revenue Calculations Migration
 * 
 * This migration ensures that all revenue calculations are accurate by:
 * 1. Creating missing revenue_transactions records
 * 2. Updating tenant balances based on actual revenue
 * 3. Fixing any inconsistencies in financial data
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "Starting Revenue Calculations Fix Migration...\n";
    
    $pdo->beginTransaction();
    
    // Check if revenue_transactions table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'revenue_transactions'")->fetch();
    
    if (!$tableCheck) {
        echo "Creating revenue_transactions table...\n";
        
        $createTable = "
            CREATE TABLE `revenue_transactions` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `transaction_id` int(11) NOT NULL,
                `tenant_id` int(11) NOT NULL,
                `event_id` int(11) NOT NULL,
                `gross_amount` decimal(10,2) NOT NULL,
                `platform_fee` decimal(10,2) NOT NULL,
                `processing_fee` decimal(10,2) DEFAULT 0.00,
                `referrer_commission` decimal(10,2) DEFAULT 0.00,
                `net_tenant_amount` decimal(10,2) NOT NULL,
                `fee_rule_snapshot` JSON DEFAULT NULL,
                `distribution_status` enum('pending','completed','failed') DEFAULT 'completed',
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
                FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
                INDEX `idx_tenant_date` (`tenant_id`, `created_at`),
                INDEX `idx_event_date` (`event_id`, `created_at`),
                INDEX `idx_distribution_status` (`distribution_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ";
        
        $pdo->exec($createTable);
        echo "✅ revenue_transactions table created.\n";
    }
    
    // Get all successful transactions that don't have revenue_transactions records
    $missingRevenueQuery = "
        SELECT 
            t.id as transaction_id,
            t.amount as gross_amount,
            e.tenant_id,
            e.id as event_id,
            t.created_at
        FROM transactions t
        INNER JOIN events e ON t.event_id = e.id
        LEFT JOIN revenue_transactions rt ON t.id = rt.transaction_id
        WHERE t.status = 'success'
        AND rt.id IS NULL
        ORDER BY t.created_at ASC
    ";
    
    $missingTransactions = $pdo->query($missingRevenueQuery)->fetchAll();
    
    echo "Found " . count($missingTransactions) . " transactions missing revenue records.\n";
    
    foreach ($missingTransactions as $transaction) {
        $grossAmount = $transaction['gross_amount'];
        $platformFeeRate = 0.05; // 5% platform fee
        $platformFee = round($grossAmount * $platformFeeRate, 2);
        $netTenantAmount = $grossAmount - $platformFee;
        
        // Insert revenue transaction record
        $insertRevenue = "
            INSERT INTO revenue_transactions 
            (transaction_id, tenant_id, event_id, gross_amount, platform_fee, net_tenant_amount, distribution_status, created_at)
            VALUES 
            (:transaction_id, :tenant_id, :event_id, :gross_amount, :platform_fee, :net_tenant_amount, 'completed', :created_at)
        ";
        
        $stmt = $pdo->prepare($insertRevenue);
        $stmt->execute([
            'transaction_id' => $transaction['transaction_id'],
            'tenant_id' => $transaction['tenant_id'],
            'event_id' => $transaction['event_id'],
            'gross_amount' => $grossAmount,
            'platform_fee' => $platformFee,
            'net_tenant_amount' => $netTenantAmount,
            'created_at' => $transaction['created_at']
        ]);
        
        echo "  Created revenue record for transaction {$transaction['transaction_id']} - Net: $" . number_format($netTenantAmount, 2) . "\n";
    }
    
    // Update revenue_shares table if it exists to match revenue_transactions
    $revenueSharesCheck = $pdo->query("SHOW TABLES LIKE 'revenue_shares'")->fetch();
    
    if ($revenueSharesCheck) {
        echo "\nSyncing revenue_shares with revenue_transactions...\n";
        
        // Check what columns exist in revenue_shares table
        $columnsResult = $pdo->query("DESCRIBE revenue_shares")->fetchAll();
        $columns = array_column($columnsResult, 'Field');
        
        echo "Revenue_shares columns: " . implode(', ', $columns) . "\n";
        
        // Build sync query based on available columns
        $selectFields = ['rt.transaction_id', 'rt.platform_fee'];
        $insertFields = ['transaction_id', 'amount'];
        
        if (in_array('percentage', $columns)) {
            $selectFields[] = 'ROUND((rt.platform_fee / rt.gross_amount) * 100, 2)';
            $insertFields[] = 'percentage';
        }
        
        if (in_array('created_at', $columns)) {
            $selectFields[] = 'rt.created_at';
            $insertFields[] = 'created_at';
        }
        
        $syncQuery = "
            INSERT IGNORE INTO revenue_shares (" . implode(', ', $insertFields) . ")
            SELECT " . implode(', ', $selectFields) . "
            FROM revenue_transactions rt
            LEFT JOIN revenue_shares rs ON rt.transaction_id = rs.transaction_id
            WHERE rs.id IS NULL
        ";
        
        try {
            $syncResult = $pdo->exec($syncQuery);
            echo "✅ Synced $syncResult revenue_shares records.\n";
        } catch (Exception $e) {
            echo "⚠️  Warning: Could not sync revenue_shares: " . $e->getMessage() . "\n";
            echo "This is not critical - continuing with migration...\n";
        }
    }
    
    // Now run the tenant balance fix
    echo "\nRecalculating tenant balances...\n";
    
    $tenants = $pdo->query("SELECT id FROM tenants WHERE active = 1")->fetchAll();
    
    foreach ($tenants as $tenant) {
        $tenantId = $tenant['id'];
        
        // Calculate total earned from revenue transactions
        $revenueQuery = "
            SELECT COALESCE(SUM(net_tenant_amount), 0) as total_earned
            FROM revenue_transactions
            WHERE tenant_id = :tenant_id
            AND distribution_status = 'completed'
        ";
        
        $revenueResult = $pdo->prepare($revenueQuery);
        $revenueResult->execute(['tenant_id' => $tenantId]);
        $totalEarned = $revenueResult->fetch()['total_earned'] ?? 0;
        
        // Calculate total successfully paid out
        $payoutQuery = "
            SELECT COALESCE(SUM(amount), 0) as total_paid
            FROM payouts
            WHERE tenant_id = :tenant_id
            AND status = 'success'
        ";
        
        $payoutResult = $pdo->prepare($payoutQuery);
        $payoutResult->execute(['tenant_id' => $tenantId]);
        $totalPaid = $payoutResult->fetch()['total_paid'] ?? 0;
        
        // Calculate pending amount
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
        $available = max(0, $available);
        
        // Update or create tenant balance
        $upsertBalance = "
            INSERT INTO tenant_balances (tenant_id, available, pending, total_earned, total_paid, created_at, updated_at)
            VALUES (:tenant_id, :available, :pending, :total_earned, :total_paid, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                available = VALUES(available),
                pending = VALUES(pending),
                total_earned = VALUES(total_earned),
                total_paid = VALUES(total_paid),
                updated_at = NOW()
        ";
        
        $upsertStmt = $pdo->prepare($upsertBalance);
        $upsertStmt->execute([
            'tenant_id' => $tenantId,
            'available' => $available,
            'pending' => $pending,
            'total_earned' => $totalEarned,
            'total_paid' => $totalPaid
        ]);
        
        if ($totalEarned > 0) {
            echo "  Tenant $tenantId: Earned=$" . number_format($totalEarned, 2) . 
                 ", Available=$" . number_format($available, 2) . 
                 ", Paid=$" . number_format($totalPaid, 2) . "\n";
        }
    }
    
    // Create platform_revenue table if it doesn't exist
    $platformRevenueCheck = $pdo->query("SHOW TABLES LIKE 'platform_revenue'")->fetch();
    
    if (!$platformRevenueCheck) {
        echo "\nCreating platform_revenue table...\n";
        
        $createPlatformRevenue = "
            CREATE TABLE `platform_revenue` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `date` date NOT NULL,
                `total_gross_revenue` decimal(12,2) DEFAULT 0.00,
                `total_platform_fees` decimal(12,2) DEFAULT 0.00,
                `total_processing_fees` decimal(12,2) DEFAULT 0.00,
                `total_net_revenue` decimal(12,2) DEFAULT 0.00,
                `transaction_count` int(11) DEFAULT 0,
                `active_tenants` int(11) DEFAULT 0,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `unique_date` (`date`),
                INDEX `idx_date` (`date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ";
        
        $pdo->exec($createPlatformRevenue);
        echo "✅ platform_revenue table created.\n";
        
        // Populate platform revenue data
        echo "Populating platform revenue data...\n";
        
        $populateRevenue = "
            INSERT INTO platform_revenue (date, total_gross_revenue, total_platform_fees, total_net_revenue, transaction_count, active_tenants)
            SELECT 
                DATE(rt.created_at) as date,
                SUM(rt.gross_amount) as total_gross_revenue,
                SUM(rt.platform_fee) as total_platform_fees,
                SUM(rt.net_tenant_amount) as total_net_revenue,
                COUNT(*) as transaction_count,
                COUNT(DISTINCT rt.tenant_id) as active_tenants
            FROM revenue_transactions rt
            WHERE rt.distribution_status = 'completed'
            GROUP BY DATE(rt.created_at)
            ORDER BY date ASC
        ";
        
        try {
            $populateResult = $pdo->exec($populateRevenue);
            echo "✅ Populated $populateResult days of platform revenue data.\n";
        } catch (Exception $e) {
            echo "⚠️  Warning: Could not populate platform revenue data: " . $e->getMessage() . "\n";
            echo "This is not critical - continuing with migration...\n";
        }
    }
    
    // Final verification
    echo "\nRunning final verification...\n";
    
    $verification = $pdo->query("
        SELECT 
            COUNT(DISTINCT tb.tenant_id) as active_tenants,
            SUM(tb.total_earned) as total_tenant_earnings,
            SUM(tb.available) as total_available,
            SUM(tb.total_paid) as total_paid_out,
            (SELECT SUM(platform_fee) FROM revenue_transactions WHERE distribution_status = 'completed') as total_platform_fees
        FROM tenant_balances tb
        INNER JOIN tenants t ON tb.tenant_id = t.id
        WHERE t.active = 1
    ")->fetch();
    
    echo "Verification Results:\n";
    echo "  Active Tenants: " . $verification['active_tenants'] . "\n";
    echo "  Total Tenant Earnings: $" . number_format($verification['total_tenant_earnings'], 2) . "\n";
    echo "  Total Available: $" . number_format($verification['total_available'], 2) . "\n";
    echo "  Total Paid Out: $" . number_format($verification['total_paid_out'], 2) . "\n";
    echo "  Total Platform Fees: $" . number_format($verification['total_platform_fees'], 2) . "\n";
    
    $pdo->commit();
    
    echo "\n✅ Revenue Calculations Fix Migration Completed Successfully!\n";
    echo "\nThe financial statistics should now show accurate data on:\n";
    echo "- organizer/financial/overview\n";
    echo "- organizer/financial/revenue\n";
    echo "- organizer/financial/transactions\n";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "\n❌ Migration Failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
