<?php
/**
 * Migration: Apply Platform Fees to Old Events
 * 
 * This migration processes all historical transactions that don't have
 * revenue shares and applies the appropriate platform fees based on
 * tenant plans and fee rules.
 * 
 * Run this script once to migrate old data.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/autoloader.php';

class OldEventsRevenueMigration
{
    private $db;
    private $processed = 0;
    private $errors = 0;
    private $totalRevenue = 0;
    private $totalPlatformFees = 0;

    public function __construct()
    {
        $this->db = new \SmartCast\Core\Database();
        echo "=== Old Events Revenue Migration ===\n";
        echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";
    }

    public function run()
    {
        try {
            // Start transaction for safety
            $this->db->beginTransaction();
            
            echo "Step 1: Finding transactions without revenue shares...\n";
            $transactionsToProcess = $this->findTransactionsWithoutRevenueShares();
            
            if (empty($transactionsToProcess)) {
                echo "✅ No transactions need migration. All transactions already have revenue shares.\n";
                $this->db->rollback();
                return;
            }
            
            echo "Found " . count($transactionsToProcess) . " transactions to process.\n\n";
            
            echo "Step 2: Processing transactions and creating revenue shares...\n";
            $this->processTransactions($transactionsToProcess);
            
            echo "\nStep 3: Updating tenant balances...\n";
            $this->updateTenantBalances();
            
            // Commit all changes
            $this->db->commit();
            
            $this->printSummary();
            
        } catch (\Exception $e) {
            $this->db->rollback();
            echo "❌ Migration failed: " . $e->getMessage() . "\n";
            echo "All changes have been rolled back.\n";
            throw $e;
        }
    }

    private function findTransactionsWithoutRevenueShares()
    {
        $sql = "
            SELECT 
                t.id,
                t.amount,
                t.event_id,
                t.created_at,
                e.tenant_id,
                tenant.plan as tenant_plan,
                tenant.name as tenant_name
            FROM transactions t
            INNER JOIN events e ON t.event_id = e.id
            INNER JOIN tenants tenant ON e.tenant_id = tenant.id
            LEFT JOIN revenue_shares rs ON t.id = rs.transaction_id
            WHERE t.status = 'success' 
            AND rs.id IS NULL
            AND t.amount > 0
            ORDER BY t.created_at ASC
        ";
        
        return $this->db->select($sql);
    }

    private function processTransactions($transactions)
    {
        foreach ($transactions as $transaction) {
            try {
                $this->processTransaction($transaction);
                $this->processed++;
                
                if ($this->processed % 100 == 0) {
                    echo "Processed {$this->processed} transactions...\n";
                }
                
            } catch (\Exception $e) {
                $this->errors++;
                echo "❌ Error processing transaction {$transaction['id']}: " . $e->getMessage() . "\n";
            }
        }
    }

    private function processTransaction($transaction)
    {
        // Get fee rule for this tenant
        $feeRule = $this->getFeeRuleForTenant($transaction['tenant_id'], $transaction['tenant_plan']);
        
        // Calculate platform fee
        $transactionAmount = floatval($transaction['amount']);
        $platformFeeRate = $feeRule['platform_fee_percentage'] / 100;
        $platformFeeAmount = $transactionAmount * $platformFeeRate;
        $tenantEarnings = $transactionAmount - $platformFeeAmount;
        
        // Create revenue share record
        $this->createRevenueShare($transaction, $platformFeeAmount, $tenantEarnings, $feeRule);
        
        // Update running totals
        $this->totalRevenue += $transactionAmount;
        $this->totalPlatformFees += $platformFeeAmount;
    }

    private function getFeeRuleForTenant($tenantId, $tenantPlan)
    {
        // First, check if there's a specific fee rule for this tenant
        $specificRule = $this->db->selectOne(
            "SELECT *, percentage_rate as platform_fee_percentage FROM fee_rules WHERE tenant_id = ? AND active = 1 ORDER BY created_at DESC LIMIT 1",
            [$tenantId]
        );
        
        if ($specificRule) {
            return $specificRule;
        }
        
        // If no specific rule, use global rule
        $globalRule = $this->db->selectOne(
            "SELECT *, percentage_rate as platform_fee_percentage FROM fee_rules WHERE tenant_id IS NULL AND active = 1 ORDER BY created_at DESC LIMIT 1"
        );
        
        if ($globalRule) {
            return $globalRule;
        }
        
        // Fallback to plan-based defaults
        $defaultFees = [
            'free' => 15.0,
            'basic' => 12.0,
            'premium' => 10.0,
            'enterprise' => 8.0
        ];
        
        return [
            'id' => null,
            'tenant_id' => null,
            'platform_fee_percentage' => $defaultFees[$tenantPlan] ?? 12.0,
            'percentage_rate' => $defaultFees[$tenantPlan] ?? 12.0,
            'description' => "Default fee for {$tenantPlan} plan (migration)"
        ];
    }

    private function createRevenueShare($transaction, $platformFeeAmount, $tenantEarnings, $feeRule)
    {
        // The actual revenue_shares table only has: id, transaction_id, tenant_id, amount, fee_rule_id, created_at
        $sql = "
            INSERT INTO revenue_shares (
                transaction_id,
                tenant_id,
                amount,
                fee_rule_id,
                created_at
            ) VALUES (?, ?, ?, ?, ?)
        ";
        
        $this->db->query($sql, [
            $transaction['id'],
            $transaction['tenant_id'],
            $platformFeeAmount, // Store platform fee amount in the amount field
            $feeRule['id'],
            $transaction['created_at'] // Use original transaction date
        ]);
    }

    private function updateTenantBalances()
    {
        // Calculate tenant earnings from transactions and platform fees
        $sql = "
            SELECT 
                rs.tenant_id,
                SUM(tr.amount - rs.amount) as total_earnings,
                COUNT(*) as transaction_count,
                tenant.name as tenant_name
            FROM revenue_shares rs
            INNER JOIN transactions tr ON rs.transaction_id = tr.id
            INNER JOIN tenants tenant ON rs.tenant_id = tenant.id
            WHERE rs.created_at >= CURDATE()  -- Only process today's revenue shares (newly created)
            GROUP BY rs.tenant_id, tenant.name
        ";
        
        $tenantEarnings = $this->db->select($sql);
        
        foreach ($tenantEarnings as $earning) {
            $this->updateTenantBalance($earning);
        }
    }

    private function updateTenantBalance($earning)
    {
        // Check if tenant balance record exists
        $existingBalance = $this->db->selectOne(
            "SELECT * FROM tenant_balances WHERE tenant_id = ?",
            [$earning['tenant_id']]
        );
        
        if ($existingBalance) {
            // Update existing balance - using actual column names: available, total_earned
            $sql = "
                UPDATE tenant_balances 
                SET available = available + ?,
                    total_earned = total_earned + ?,
                    updated_at = ?
                WHERE tenant_id = ?
            ";
            
            $this->db->query($sql, [
                $earning['total_earnings'],
                $earning['total_earnings'],
                date('Y-m-d H:i:s'),
                $earning['tenant_id']
            ]);
            
        } else {
            // Create new balance record - using actual column names
            $sql = "
                INSERT INTO tenant_balances (
                    tenant_id,
                    available,
                    pending,
                    total_earned,
                    total_paid,
                    created_at,
                    updated_at
                ) VALUES (?, ?, 0, ?, 0, ?, ?)
            ";
            
            $this->db->query($sql, [
                $earning['tenant_id'],
                $earning['total_earnings'],
                $earning['total_earnings'],
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
        }
        
        echo "✅ Updated balance for {$earning['tenant_name']}: +$" . number_format($earning['total_earnings'], 2) . 
             " ({$earning['transaction_count']} transactions)\n";
    }

    private function printSummary()
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "MIGRATION COMPLETED SUCCESSFULLY\n";
        echo str_repeat("=", 50) . "\n";
        echo "Processed Transactions: " . number_format($this->processed) . "\n";
        echo "Errors: " . number_format($this->errors) . "\n";
        echo "Total Revenue Processed: $" . number_format($this->totalRevenue, 2) . "\n";
        echo "Total Platform Fees: $" . number_format($this->totalPlatformFees, 2) . "\n";
        echo "Total Tenant Earnings: $" . number_format($this->totalRevenue - $this->totalPlatformFees, 2) . "\n";
        echo "Average Platform Fee Rate: " . number_format(($this->totalPlatformFees / $this->totalRevenue) * 100, 2) . "%\n";
        echo "Completed at: " . date('Y-m-d H:i:s') . "\n";
        echo str_repeat("=", 50) . "\n";
    }
}

// Run the migration
try {
    $migration = new OldEventsRevenueMigration();
    $migration->run();
    echo "\n🎉 Migration completed successfully!\n";
} catch (\Exception $e) {
    echo "\n💥 Migration failed with error: " . $e->getMessage() . "\n";
    exit(1);
}
