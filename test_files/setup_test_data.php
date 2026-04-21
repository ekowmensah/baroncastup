<?php
/**
 * Setup Test Data for Payouts Page
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/autoloader.php';

$db = new \SmartCast\Core\Database();

echo "=== Setting Up Test Data ===\n";

try {
    echo "Starting database transaction...\n";
    $db->beginTransaction();
    
    // 1. Create a test tenant
    echo "1. Creating test tenant...\n";
    $db->query("
        INSERT INTO tenants (name, email, phone, website, plan, active, verified, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ", [
        'Test Organizer ' . date('His'),
        'test' . date('His') . '@organizer.com',
        '+233501234567',
        'https://test-organizer.com',
        'basic',
        1,
        1,
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s')
    ]);
    
    $tenantId = $db->getConnection()->lastInsertId();
    echo "✅ Created tenant with ID: $tenantId\n";
    
    // 2. Create tenant balance
    echo "2. Creating tenant balance...\n";
    $db->query("
        INSERT INTO tenant_balances (tenant_id, available, pending, total_earned, total_paid, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ", [
        $tenantId,
        125.50,  // Available balance
        0.00,    // Pending
        200.00,  // Total earned
        74.50,   // Total paid out
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s')
    ]);
    
    echo "✅ Created tenant balance\n";
    
    // 2.5. Create a fee rule for this tenant
    echo "2.5. Creating fee rule...\n";
    $db->query("
        INSERT INTO fee_rules (tenant_id, event_id, rule_type, percentage_rate, fixed_amount, active, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ", [
        $tenantId,
        null, // Tenant-wide rule
        'percentage',
        15.0, // 15% fee
        null,
        1,
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s')
    ]);
    
    $feeRuleId = $db->getConnection()->lastInsertId();
    echo "✅ Created fee rule with ID: $feeRuleId\n";
    
    // 3. Create sample payouts
    echo "3. Creating sample payouts...\n";
    $samplePayouts = [
        [
            'payout_id' => 'PO_20251010_001',
            'amount' => 50.00,
            'payout_method' => 'bank_transfer',
            'recipient_details' => json_encode([
                'bank_name' => 'Test Bank',
                'account_number' => '1234567890',
                'routing_number' => '123456789',
                'account_holder' => 'Test Organizer'
            ]),
            'status' => 'success',
            'provider_reference' => 'BT_1728567890_1234',
            'processed_at' => '2025-10-08 14:30:00',
            'created_at' => '2025-10-08 10:15:00'
        ],
        [
            'payout_id' => 'PO_20251010_002',
            'amount' => 24.50,
            'payout_method' => 'mobile_money',
            'recipient_details' => json_encode([
                'phone_number' => '+233501234567',
                'network' => 'MTN'
            ]),
            'status' => 'processing',
            'provider_reference' => null,
            'processed_at' => null,
            'created_at' => '2025-10-09 16:45:00'
        ],
        [
            'payout_id' => 'PO_20251010_003',
            'amount' => 15.00,
            'payout_method' => 'paypal',
            'recipient_details' => json_encode([
                'email' => 'test@organizer.com'
            ]),
            'status' => 'failed',
            'provider_reference' => null,
            'processed_at' => null,
            'failure_reason' => 'Invalid PayPal email address',
            'created_at' => '2025-10-07 09:20:00'
        ]
    ];
    
    foreach ($samplePayouts as $payout) {
        $db->query("
            INSERT INTO payouts (
                tenant_id, payout_id, amount, payout_method, recipient_details,
                status, provider_reference, processed_at, failure_reason, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $tenantId,
            $payout['payout_id'],
            $payout['amount'],
            $payout['payout_method'],
            $payout['recipient_details'],
            $payout['status'],
            $payout['provider_reference'] ?? null,
            $payout['processed_at'] ?? null,
            $payout['failure_reason'] ?? null,
            $payout['created_at'],
            date('Y-m-d H:i:s')
        ]);
        
        echo "✅ Created payout: " . $payout['payout_id'] . " ($" . $payout['amount'] . " - " . $payout['status'] . ")\n";
    }
    
    // 4. Create some sample transactions and revenue shares for context
    echo "4. Creating sample transactions...\n";
    
    // Create an event first
    $db->query("
        INSERT INTO events (tenant_id, name, description, status, vote_price, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ", [
        $tenantId,
        'Test Event',
        'Sample event for testing',
        'active',
        1.00,
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s')
    ]);
    
    $eventId = $db->getConnection()->lastInsertId();
    
    // Create sample transactions
    for ($i = 1; $i <= 3; $i++) {
        $db->query("
            INSERT INTO transactions (tenant_id, event_id, amount, status, msisdn, provider, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $tenantId,
            $eventId,
            10.00 * $i,
            'success',
            '+233501234567',
            'momo',
            date('Y-m-d H:i:s', strtotime("-$i days")),
            date('Y-m-d H:i:s')
        ]);
        
        $transactionId = $db->getConnection()->lastInsertId();
        
        // Create revenue share for each transaction
        $platformFee = (10.00 * $i) * 0.15; // 15% fee
        $db->query("
            INSERT INTO revenue_shares (transaction_id, tenant_id, amount, fee_rule_id, created_at)
            VALUES (?, ?, ?, ?, ?)
        ", [
            $transactionId,
            $tenantId,
            $platformFee,
            $feeRuleId, // Use the fee rule we just created
            date('Y-m-d H:i:s', strtotime("-$i days"))
        ]);
    }
    
    echo "✅ Created sample transactions and revenue shares\n";
    
    // 5. Create a user for this tenant
    echo "5. Creating user for tenant...\n";
    $db->query("
        INSERT INTO users (tenant_id, email, password_hash, role, active, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ", [
        $tenantId,
        'test' . date('His') . '@organizer.com',
        password_hash('password123', PASSWORD_DEFAULT),
        'owner',
        1,
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s')
    ]);
    
    $userId = $db->getConnection()->lastInsertId();
    echo "✅ Created user with ID: $userId\n";
    
    $db->commit();
    
    echo "\n=== Test Data Setup Complete ===\n";
    echo "Tenant ID: $tenantId\n";
    echo "User ID: $userId\n";
    echo "Login: test" . date('His') . "@organizer.com / password123\n";
    echo "Available Balance: GH₵125.50\n";
    echo "Total Payouts: 3 (Success: 1, Processing: 1, Failed: 1)\n";
    echo "\nYou can now:\n";
    echo "1. Login with test@organizer.com / password123\n";
    echo "2. Visit /organizer/financial/payouts to see the real data!\n";
    
} catch (\Exception $e) {
    $db->rollback();
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}