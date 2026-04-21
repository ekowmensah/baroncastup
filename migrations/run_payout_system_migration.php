<?php
/**
 * Payout System Migration Runner
 * 
 * This script ensures all payout system tables and enhancements are properly applied
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "Starting Payout System Migration...\n";
    
    // Read and execute the enhanced payout system migration
    $migrationFile = __DIR__ . '/../database/migrations/enhance_payout_system.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    $pdo->beginTransaction();
    
    foreach ($statements as $statement) {
        if (trim($statement)) {
            try {
                echo "Executing: " . substr($statement, 0, 50) . "...\n";
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Check if error is about table/column already existing
                if (strpos($e->getMessage(), 'already exists') !== false || 
                    strpos($e->getMessage(), 'Duplicate column') !== false ||
                    strpos($e->getMessage(), 'Duplicate key') !== false) {
                    echo "Skipping (already exists): " . substr($statement, 0, 50) . "...\n";
                    continue;
                } else {
                    throw $e;
                }
            }
        }
    }
    
    $pdo->commit();
    
    echo "\n✅ Payout System Migration Completed Successfully!\n";
    echo "\nNew Features Available:\n";
    echo "- Advanced payout method management\n";
    echo "- Automatic payout scheduling\n";
    echo "- Real-time revenue distribution\n";
    echo "- Dynamic fee calculation\n";
    echo "- Complete backend integration\n";
    echo "\nAccess the new payout system at: /organizer/payouts\n";
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollback();
    }
    echo "\n❌ Migration Failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
