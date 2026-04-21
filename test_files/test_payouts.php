<?php
/**
 * Test Payouts - Create Sample Payout Data
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/autoloader.php';

$db = new \SmartCast\Core\Database();
$payoutModel = new \SmartCast\Models\Payout();

echo "=== Creating Sample Payout Data ===\n";

try {
    $db->beginTransaction();
    
    // Sample payout data for tenant 2 (Organizer Two)
    $samplePayouts = [
        [
            'tenant_id' => 2,
            'payout_id' => 'PO_20251010_001',
            'amount' => 50.00,
            'payout_method' => 'bank_transfer',
            'recipient_details' => json_encode([
                'bank_name' => 'Test Bank',
                'account_number' => '1234567890',
                'routing_number' => '123456789',
                'account_holder' => 'Organizer Two'
            ]),
            'status' => 'success',
            'provider_reference' => 'BT_1728567890_1234',
            'processed_at' => '2025-10-08 14:30:00',
            'created_at' => '2025-10-08 10:15:00'
        ],
        [
            'tenant_id' => 2,
            'payout_id' => 'PO_20251010_002',
            'amount' => 25.00,
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
            'tenant_id' => 2,
            'payout_id' => 'PO_20251010_003',
            'amount' => 15.00,
            'payout_method' => 'paypal',
            'recipient_details' => json_encode([
                'email' => 'organizer@example.com'
            ]),
            'status' => 'failed',
            'provider_reference' => null,
            'processed_at' => null,
            'failure_reason' => 'Invalid PayPal email address',
            'created_at' => '2025-10-07 09:20:00'
        ]
    ];
    
    foreach ($samplePayouts as $payout) {
        // Check if payout already exists
        $existing = $db->selectOne("SELECT id FROM payouts WHERE payout_id = ?", [$payout['payout_id']]);
        
        if (!$existing) {
            $db->query("
                INSERT INTO payouts (
                    tenant_id, payout_id, amount, payout_method, recipient_details,
                    status, provider_reference, processed_at, failure_reason, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $payout['tenant_id'],
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
        } else {
            echo "⚠️  Payout already exists: " . $payout['payout_id'] . "\n";
        }
    }
    
    $db->commit();
    
    echo "\n=== Sample Payouts Created Successfully ===\n";
    echo "You can now visit /organizer/financial/payouts to see the real data!\n";
    
} catch (\Exception $e) {
    $db->rollback();
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
