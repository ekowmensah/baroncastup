<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

$db = new Database();

echo "=== CHECKING FINANCIAL DATA ===\n\n";

// Get a tenant ID
$tenants = $db->query("SELECT id, name FROM tenants WHERE active = 1 LIMIT 1")->fetchAll();
if (empty($tenants)) {
    echo "No active tenants found\n";
    exit;
}

$tenantId = $tenants[0]['id'];
$tenantName = $tenants[0]['name'];

echo "Checking tenant: $tenantName (ID: $tenantId)\n\n";

// Check revenue_transactions
echo "1. REVENUE TRANSACTIONS:\n";
$revenueCheck = $db->query("
    SELECT 
        COUNT(*) as count,
        SUM(gross_amount) as total_gross,
        SUM(platform_fee) as total_fee,
        SUM(net_tenant_amount) as total_net
    FROM revenue_transactions
    WHERE tenant_id = $tenantId
    AND distribution_status = 'completed'
")->fetch();
print_r($revenueCheck);

// Check tenant_balances
echo "\n2. TENANT BALANCE TABLE:\n";
$balanceCheck = $db->query("
    SELECT * FROM tenant_balances WHERE tenant_id = $tenantId
")->fetch();
print_r($balanceCheck);

// Check successful transactions
echo "\n3. SUCCESSFUL TRANSACTIONS:\n";
$transCheck = $db->query("
    SELECT 
        COUNT(*) as count,
        SUM(amount) as total_amount
    FROM transactions t
    INNER JOIN events e ON t.event_id = e.id
    WHERE e.tenant_id = $tenantId
    AND t.status = 'success'
")->fetch();
print_r($transCheck);

// Check votes
echo "\n4. VOTES DATA:\n";
$votesCheck = $db->query("
    SELECT 
        COUNT(*) as vote_count,
        SUM(v.quantity) as total_votes
    FROM votes v
    INNER JOIN contestants c ON v.contestant_id = c.id
    INNER JOIN events e ON c.event_id = e.id
    WHERE e.tenant_id = $tenantId
")->fetch();
print_r($votesCheck);

// Check recent votes with transactions
echo "\n5. RECENT VOTES (with transaction info):\n";
$recentVotes = $db->query("
    SELECT 
        c.name as contestant_name,
        e.name as event_name,
        v.quantity,
        t.amount,
        t.status,
        v.created_at
    FROM votes v
    INNER JOIN contestants c ON v.contestant_id = c.id
    INNER JOIN events e ON c.event_id = e.id
    LEFT JOIN transactions t ON v.transaction_id = t.id
    WHERE e.tenant_id = $tenantId
    ORDER BY v.created_at DESC
    LIMIT 5
")->fetchAll();
foreach ($recentVotes as $vote) {
    echo "  - {$vote['contestant_name']} ({$vote['event_name']}): {$vote['quantity']} votes, Amount: {$vote['amount']}, Status: {$vote['status']}\n";
}

// Check if revenue_transactions table exists
echo "\n6. CHECKING TABLE EXISTENCE:\n";
$tables = $db->query("SHOW TABLES LIKE 'revenue_transactions'")->fetch();
echo "revenue_transactions table exists: " . ($tables ? "YES" : "NO") . "\n";

?>
