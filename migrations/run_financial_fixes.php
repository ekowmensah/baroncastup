<?php
/**
 * Financial System Fix Migration Runner
 * 
 * This script runs all necessary migrations to fix financial statistics and balances
 */

echo "=== SmartCast Financial System Fix ===\n\n";

$migrations = [
    'fix_revenue_calculations.php' => 'Revenue Calculations & Missing Tables',
    'fix_tenant_balances.php' => 'Tenant Balance Corrections'
];

foreach ($migrations as $file => $description) {
    echo "Running: $description\n";
    echo str_repeat('-', 50) . "\n";
    
    $migrationPath = __DIR__ . '/' . $file;
    
    if (!file_exists($migrationPath)) {
        echo "❌ Migration file not found: $file\n\n";
        continue;
    }
    
    // Capture output
    ob_start();
    $exitCode = 0;
    
    try {
        include $migrationPath;
    } catch (Exception $e) {
        $exitCode = 1;
        echo "❌ Migration failed: " . $e->getMessage() . "\n";
    }
    
    $output = ob_get_clean();
    echo $output;
    
    if ($exitCode === 0) {
        echo "✅ Migration completed successfully\n\n";
    } else {
        echo "❌ Migration failed\n\n";
        break;
    }
}

echo "=== Financial System Fix Complete ===\n";
echo "\nPlease verify the following pages now show correct data:\n";
echo "1. /organizer/financial/overview\n";
echo "2. /organizer/financial/revenue\n";
echo "3. /organizer/financial/transactions\n";
echo "4. /organizer/payouts\n";
echo "\nTest the following functionality:\n";
echo "1. Request a payout\n";
echo "2. Cancel a payout\n";
echo "3. Verify balance calculations are accurate\n";
?>
