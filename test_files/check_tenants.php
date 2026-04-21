<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/autoloader.php';

$db = new \SmartCast\Core\Database();

echo "=== Available Tenants ===\n";
$tenants = $db->select("SELECT id, name, email FROM tenants ORDER BY id");

foreach ($tenants as $tenant) {
    echo "ID: {$tenant['id']} | Name: {$tenant['name']} | Email: {$tenant['email']}\n";
}

echo "\n=== Tenant Balances ===\n";
$balances = $db->select("SELECT * FROM tenant_balances ORDER BY tenant_id");

foreach ($balances as $balance) {
    echo "Tenant ID: {$balance['tenant_id']} | Available: $" . number_format($balance['available'], 2) . 
         " | Total Earned: $" . number_format($balance['total_earned'], 2) . 
         " | Total Paid: $" . number_format($balance['total_paid'], 2) . "\n";
}
?>
