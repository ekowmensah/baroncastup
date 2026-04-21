<?php
/**
 * Revenue Migration Verification Script
 * 
 * This script checks the current state of revenue distribution
 * and helps verify if migration is needed or was successful.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/autoloader.php';

class RevenueMigrationVerifier
{
    private $db;

    public function __construct()
    {
        $this->db = new \SmartCast\Core\Database();
        echo "=== Revenue Migration Verification ===\n";
        echo "Checking at: " . date('Y-m-d H:i:s') . "\n\n";
    }

    public function verify()
    {
        echo "1. Checking transaction and revenue share counts...\n";
        $this->checkTransactionCounts();
        
        echo "\n2. Checking transactions without revenue shares...\n";
        $this->checkMissingRevenueShares();
        
        echo "\n3. Checking tenant balances...\n";
        $this->checkTenantBalances();
        
        echo "\n4. Checking fee rules...\n";
        $this->checkFeeRules();
        
        echo "\n5. Revenue distribution summary...\n";
        $this->revenueDistributionSummary();
    }

    private function checkTransactionCounts()
    {
        // Total successful transactions
        $totalTransactions = $this->db->selectOne(
            "SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount 
             FROM transactions WHERE status = 'success' AND amount > 0"
        );
        
        // Transactions with revenue shares
        $transactionsWithShares = $this->db->selectOne(
            "SELECT COUNT(DISTINCT t.id) as count, COALESCE(SUM(t.amount), 0) as total_amount
             FROM transactions t 
             INNER JOIN revenue_shares rs ON t.id = rs.transaction_id 
             WHERE t.status = 'success' AND t.amount > 0"
        );
        
        // Transactions without revenue shares
        $transactionsWithoutShares = $this->db->selectOne(
            "SELECT COUNT(*) as count, COALESCE(SUM(t.amount), 0) as total_amount
             FROM transactions t 
             LEFT JOIN revenue_shares rs ON t.id = rs.transaction_id 
             WHERE t.status = 'success' AND t.amount > 0 AND rs.id IS NULL"
        );
        
        echo "Total successful transactions: " . number_format($totalTransactions['count']) . 
             " (Total: $" . number_format($totalTransactions['total_amount'], 2) . ")\n";
        echo "Transactions with revenue shares: " . number_format($transactionsWithShares['count']) . 
             " (Total: $" . number_format($transactionsWithShares['total_amount'], 2) . ")\n";
        echo "Transactions WITHOUT revenue shares: " . number_format($transactionsWithoutShares['count']) . 
             " (Total: $" . number_format($transactionsWithoutShares['total_amount'], 2) . ")\n";
        
        if ($transactionsWithoutShares['count'] > 0) {
            echo "⚠️  Migration needed: {$transactionsWithoutShares['count']} transactions missing revenue shares\n";
        } else {
            echo "✅ All transactions have revenue shares\n";
        }
    }

    private function checkMissingRevenueShares()
    {
        $sql = "
            SELECT 
                e.tenant_id,
                t.name as tenant_name,
                COUNT(*) as missing_count,
                SUM(tr.amount) as missing_amount,
                MIN(tr.created_at) as oldest_transaction,
                MAX(tr.created_at) as newest_transaction
            FROM transactions tr
            INNER JOIN events e ON tr.event_id = e.id
            INNER JOIN tenants t ON e.tenant_id = t.id
            LEFT JOIN revenue_shares rs ON tr.id = rs.transaction_id
            WHERE tr.status = 'success' 
            AND tr.amount > 0
            AND rs.id IS NULL
            GROUP BY e.tenant_id, t.name
            ORDER BY missing_amount DESC
        ";
        
        $missingByTenant = $this->db->select($sql);
        
        if (empty($missingByTenant)) {
            echo "✅ No missing revenue shares found\n";
            return;
        }
        
        echo "Missing revenue shares by tenant:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-20s %-10s %-15s %-12s %-12s\n", "Tenant", "Count", "Amount", "Oldest", "Newest");
        echo str_repeat("-", 80) . "\n";
        
        foreach ($missingByTenant as $tenant) {
            printf("%-20s %-10s $%-14s %-12s %-12s\n",
                substr($tenant['tenant_name'], 0, 19),
                number_format($tenant['missing_count']),
                number_format($tenant['missing_amount'], 2),
                date('Y-m-d', strtotime($tenant['oldest_transaction'])),
                date('Y-m-d', strtotime($tenant['newest_transaction']))
            );
        }
    }

    private function checkTenantBalances()
    {
        $sql = "
            SELECT 
                t.name as tenant_name,
                t.plan,
                COALESCE(tb.available, 0) as available_balance,
                COALESCE(tb.total_earned, 0) as total_earnings,
                COALESCE(tb.total_paid, 0) as total_payouts,
                COALESCE(SUM(tr.amount - rs.amount), 0) as calculated_earnings
            FROM tenants t
            LEFT JOIN tenant_balances tb ON t.id = tb.tenant_id
            LEFT JOIN revenue_shares rs ON t.id = rs.tenant_id
            LEFT JOIN transactions tr ON rs.transaction_id = tr.id
            GROUP BY t.id, t.name, t.plan, tb.available, tb.total_earned, tb.total_paid
            HAVING calculated_earnings > 0 OR total_earnings > 0
            ORDER BY calculated_earnings DESC
        ";
        
        $balances = $this->db->select($sql);
        
        if (empty($balances)) {
            echo "No tenant balances found\n";
            return;
        }
        
        echo "Tenant balance verification:\n";
        echo str_repeat("-", 90) . "\n";
        printf("%-20s %-10s %-15s %-15s %-15s %-10s\n", 
               "Tenant", "Plan", "Available", "Total Earnings", "Calculated", "Match");
        echo str_repeat("-", 90) . "\n";
        
        foreach ($balances as $balance) {
            $match = abs($balance['total_earnings'] - $balance['calculated_earnings']) < 0.01 ? "✅" : "❌";
            
            printf("%-20s %-10s $%-14s $%-14s $%-14s %-10s\n",
                substr($balance['tenant_name'], 0, 19),
                $balance['plan'],
                number_format($balance['available_balance'], 2),
                number_format($balance['total_earnings'], 2),
                number_format($balance['calculated_earnings'], 2),
                $match
            );
        }
    }

    private function checkFeeRules()
    {
        $feeRules = $this->db->select("SELECT * FROM fee_rules WHERE active = 1 ORDER BY tenant_id, created_at DESC");
        
        echo "Active fee rules:\n";
        echo str_repeat("-", 60) . "\n";
        printf("%-15s %-20s %-10s %-15s\n", "Tenant ID", "Rule Type", "Fee %", "Created");
        echo str_repeat("-", 60) . "\n";
        
        foreach ($feeRules as $rule) {
            printf("%-15s %-20s %-10s %-15s\n",
                $rule['tenant_id'] ?? 'Global',
                substr($rule['rule_type'] ?? 'N/A', 0, 19),
                $rule['percentage_rate'] . '%',
                date('Y-m-d', strtotime($rule['created_at']))
            );
        }
        
        if (empty($feeRules)) {
            echo "⚠️  No fee rules found. Migration will use default plan-based fees.\n";
        }
    }

    private function revenueDistributionSummary()
    {
        $summary = $this->db->selectOne("
            SELECT 
                COUNT(DISTINCT rs.transaction_id) as transactions_with_shares,
                COALESCE(SUM(tr.amount), 0) as total_transaction_amount,
                COALESCE(SUM(rs.amount), 0) as total_platform_fees,
                COALESCE(SUM(tr.amount - rs.amount), 0) as total_tenant_earnings,
                COUNT(DISTINCT rs.tenant_id) as tenants_with_earnings
            FROM revenue_shares rs
            INNER JOIN transactions tr ON rs.transaction_id = tr.id
        ");
        
        if ($summary['transactions_with_shares'] == 0) {
            echo "No revenue shares found in the system.\n";
            return;
        }
        
        $avgFeeRate = $summary['total_transaction_amount'] > 0 ? 
                     ($summary['total_platform_fees'] / $summary['total_transaction_amount']) * 100 : 0;
        
        echo "Revenue Distribution Summary:\n";
        echo str_repeat("-", 40) . "\n";
        echo "Transactions processed: " . number_format($summary['transactions_with_shares']) . "\n";
        echo "Total transaction amount: $" . number_format($summary['total_transaction_amount'], 2) . "\n";
        echo "Total platform fees: $" . number_format($summary['total_platform_fees'], 2) . "\n";
        echo "Total tenant earnings: $" . number_format($summary['total_tenant_earnings'], 2) . "\n";
        echo "Average platform fee rate: " . number_format($avgFeeRate, 2) . "%\n";
        echo "Tenants with earnings: " . number_format($summary['tenants_with_earnings']) . "\n";
    }
}

// Run the verification
try {
    $verifier = new RevenueMigrationVerifier();
    $verifier->verify();
    echo "\n✅ Verification completed!\n";
} catch (\Exception $e) {
    echo "\n❌ Verification failed: " . $e->getMessage() . "\n";
    exit(1);
}
