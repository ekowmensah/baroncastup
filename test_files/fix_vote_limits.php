<?php
/**
 * Fix Vote Quantity Column Limits
 * 
 * This script fixes the database column constraints that limit vote quantities to 100
 * Run this once to update the database schema
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "🔧 Fixing vote quantity column limits...\n\n";
    
    // Check current column definitions
    echo "📊 Current column definitions:\n";
    $result = $connection->query("DESCRIBE votes");
    while ($row = $result->fetch()) {
        if ($row['Field'] === 'quantity') {
            echo "votes.quantity: {$row['Type']} (Current)\n";
        }
    }
    
    $result = $connection->query("DESCRIBE leaderboard_cache");
    while ($row = $result->fetch()) {
        if ($row['Field'] === 'total_votes') {
            echo "leaderboard_cache.total_votes: {$row['Type']} (Current)\n";
        }
    }
    
    echo "\n🚀 Updating column definitions...\n";
    
    // Update votes.quantity column
    $connection->exec("ALTER TABLE votes MODIFY COLUMN quantity INT UNSIGNED NOT NULL DEFAULT 1");
    echo "✅ Updated votes.quantity to INT UNSIGNED\n";
    
    // Update leaderboard_cache.total_votes column
    $connection->exec("ALTER TABLE leaderboard_cache MODIFY COLUMN total_votes INT UNSIGNED NOT NULL DEFAULT 0");
    echo "✅ Updated leaderboard_cache.total_votes to INT UNSIGNED\n";
    
    // Verify changes
    echo "\n📊 New column definitions:\n";
    $result = $connection->query("DESCRIBE votes");
    while ($row = $result->fetch()) {
        if ($row['Field'] === 'quantity') {
            echo "votes.quantity: {$row['Type']} (Updated)\n";
        }
    }
    
    $result = $connection->query("DESCRIBE leaderboard_cache");
    while ($row = $result->fetch()) {
        if ($row['Field'] === 'total_votes') {
            echo "leaderboard_cache.total_votes: {$row['Type']} (Updated)\n";
        }
    }
    
    // Show recent votes to verify
    echo "\n📋 Recent votes (showing quantity values):\n";
    $result = $connection->query("
        SELECT id, transaction_id, contestant_id, quantity, created_at 
        FROM votes 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    
    while ($row = $result->fetch()) {
        echo "Vote ID {$row['id']}: {$row['quantity']} votes for contestant {$row['contestant_id']}\n";
    }
    
    echo "\n🎉 Database schema updated successfully!\n";
    echo "📝 Vote quantities can now support up to 4,294,967,295 votes (INT UNSIGNED max)\n";
    echo "🔄 Try voting with 1,800 votes again - it should work now!\n";
    
} catch (Exception $e) {
    echo "❌ Error updating database: " . $e->getMessage() . "\n";
    echo "📋 You may need to run this SQL manually:\n";
    echo "   ALTER TABLE votes MODIFY COLUMN quantity INT UNSIGNED NOT NULL DEFAULT 1;\n";
    echo "   ALTER TABLE leaderboard_cache MODIFY COLUMN total_votes INT UNSIGNED NOT NULL DEFAULT 0;\n";
}
?>
