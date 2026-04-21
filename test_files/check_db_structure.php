<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/autoloader.php';

$db = new \SmartCast\Core\Database();

echo "=== Database Structure Check ===\n";

// Check if tables exist
$tables = ['tenants', 'tenant_balances', 'payouts', 'transactions', 'revenue_shares'];

foreach ($tables as $table) {
    try {
        $result = $db->select("SELECT COUNT(*) as count FROM $table");
        echo "$table: " . $result[0]['count'] . " records\n";
    } catch (\Exception $e) {
        echo "$table: ERROR - " . $e->getMessage() . "\n";
    }
}

echo "\n=== Recent Transactions (any tenant) ===\n";
try {
    $transactions = $db->select("SELECT id, tenant_id, amount, status, created_at FROM transactions ORDER BY created_at DESC LIMIT 3");
    foreach ($transactions as $t) {
        echo "ID: {$t['id']} | Tenant: {$t['tenant_id']} | Amount: $" . number_format($t['amount'], 2) . " | Status: {$t['status']} | Date: {$t['created_at']}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
