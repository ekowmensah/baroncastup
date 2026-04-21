<?php
/**
 * Debug Revenue Distribution
 * Check what's happening with the latest transactions
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/autoloader.php';

$db = new \SmartCast\Core\Database();

echo "=== Revenue Distribution Debug ===\n";
echo "Checking at: " . date('Y-m-d H:i:s') . "\n\n";

// 1. Check latest transactions
echo "1. Latest Transactions:\n";
echo str_repeat("-", 80) . "\n";
$transactions = $db->select("
    SELECT t.id, t.amount, t.status, t.tenant_id, t.event_id, t.created_at,
           e.name as event_name, tenant.name as tenant_name
    FROM transactions t
    LEFT JOIN events e ON t.event_id = e.id
    LEFT JOIN tenants tenant ON t.tenant_id = tenant.id
    ORDER BY t.created_at DESC 
    LIMIT 5
");

foreach ($transactions as $t) {
    printf("ID: %d | Amount: $%.2f | Status: %s | Tenant: %s | Event: %s | Date: %s\n",
        $t['id'], $t['amount'], $t['status'], 
        $t['tenant_name'] ?? 'Unknown', $t['event_name'] ?? 'Unknown', $t['created_at']
    );
}

// 2. Check revenue shares for latest transactions
echo "\n2. Revenue Shares for Latest Transactions:\n";
echo str_repeat("-", 80) . "\n";
$revenueShares = $db->select("
    SELECT rs.*, t.amount as transaction_amount, fr.rule_type, fr.percentage_rate
    FROM revenue_shares rs
    LEFT JOIN transactions t ON rs.transaction_id = t.id
    LEFT JOIN fee_rules fr ON rs.fee_rule_id = fr.id
    ORDER BY rs.created_at DESC 
    LIMIT 5
");

if (empty($revenueShares)) {
    echo "❌ No revenue shares found!\n";
} else {
    foreach ($revenueShares as $rs) {
        printf("Transaction ID: %d | Platform Fee: $%.2f | Transaction: $%.2f | Rule: %s %.1f%%\n",
            $rs['transaction_id'], $rs['amount'], $rs['transaction_amount'],
            $rs['rule_type'] ?? 'N/A', $rs['percentage_rate'] ?? 0
        );
    }
}

// 3. Check fee rules
echo "\n3. Active Fee Rules:\n";
echo str_repeat("-", 80) . "\n";
$feeRules = $db->select("
    SELECT id, tenant_id, event_id, rule_type, percentage_rate, active, created_at
    FROM fee_rules 
    WHERE active = 1 
    ORDER BY tenant_id, event_id
");

foreach ($feeRules as $rule) {
    printf("ID: %d | Tenant: %s | Event: %s | Type: %s | Rate: %.1f%% | Created: %s\n",
        $rule['id'], 
        $rule['tenant_id'] ?? 'Global',
        $rule['event_id'] ?? 'All Events',
        $rule['rule_type'], 
        $rule['percentage_rate'],
        $rule['created_at']
    );
}

// 4. Test fee rule lookup for latest transaction
echo "\n4. Fee Rule Test for Latest Transaction:\n";
echo str_repeat("-", 80) . "\n";
if (!empty($transactions)) {
    $latestTransaction = $transactions[0];
    
    echo "Testing fee rule lookup for:\n";
    echo "- Tenant ID: " . $latestTransaction['tenant_id'] . "\n";
    echo "- Event ID: " . $latestTransaction['event_id'] . "\n";
    
    // Simulate the fee rule lookup with debugging
    $feeRuleModel = new \SmartCast\Models\FeeRule();
    
    echo "Debugging fee rule lookup step by step:\n";
    
    // 1. Check for event-specific rule
    echo "1. Checking event-specific rule (tenant_id=2, event_id=23):\n";
    $eventRule = $db->select("SELECT * FROM fee_rules WHERE tenant_id = 2 AND event_id = 23 AND active = 1");
    echo "   Result: " . (empty($eventRule) ? "None found" : "Found " . count($eventRule)) . "\n";
    
    // 2. Check for tenant-specific rule
    echo "2. Checking tenant-specific rule (tenant_id=2, event_id=NULL):\n";
    $tenantRule = $db->select("SELECT * FROM fee_rules WHERE tenant_id = 2 AND event_id IS NULL AND active = 1");
    echo "   Result: " . (empty($tenantRule) ? "None found" : "Found " . count($tenantRule)) . "\n";
    if (!empty($tenantRule)) {
        printf("   Rule: ID=%d, Rate=%.1f%%\n", $tenantRule[0]['id'], $tenantRule[0]['percentage_rate']);
    }
    
    // 3. Check for global rule
    echo "3. Checking global rule (tenant_id=NULL, event_id=NULL):\n";
    $globalRule = $db->select("SELECT * FROM fee_rules WHERE tenant_id IS NULL AND event_id IS NULL AND active = 1");
    echo "   Result: " . (empty($globalRule) ? "None found" : "Found " . count($globalRule)) . "\n";
    if (!empty($globalRule)) {
        printf("   Rule: ID=%d, Rate=%.1f%%\n", $globalRule[0]['id'], $globalRule[0]['percentage_rate']);
    }
    
    // Now test the actual method
    echo "4. Testing FeeRule::getApplicableFeeRule() method:\n";
    $applicableRule = $feeRuleModel->getApplicableFeeRule(
        $latestTransaction['tenant_id'], 
        $latestTransaction['event_id']
    );
    
    if ($applicableRule) {
        echo "✅ Found applicable fee rule:\n";
        printf("   Rule ID: %d | Type: %s | Rate: %.1f%%\n", 
            $applicableRule['id'], $applicableRule['rule_type'], $applicableRule['percentage_rate']);
        
        // Calculate what the fee should be
        $expectedFee = $latestTransaction['amount'] * ($applicableRule['percentage_rate'] / 100);
        printf("   Expected platform fee: $%.2f\n", $expectedFee);
    } else {
        echo "❌ No applicable fee rule found by method!\n";
        echo "   This suggests an issue with the FeeRule::getApplicableFeeRule() method\n";
    }
}

echo "\n=== Debug Complete ===\n";
