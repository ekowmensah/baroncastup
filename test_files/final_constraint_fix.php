<?php
/**
 * Final Fix for Constraint Issues
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "🔧 Final Constraint Fix\n\n";
    
    // Step 1: Check current constraints
    echo "1. Checking current constraints...\n";
    $stmt = $connection->query("SHOW INDEX FROM leaderboard_cache");
    $indexes = $stmt->fetchAll();
    
    foreach ($indexes as $index) {
        echo "   - {$index['Key_name']}: {$index['Column_name']}\n";
    }
    
    // Step 2: Drop foreign key constraints temporarily
    echo "2. Dropping foreign key constraints...\n";
    try {
        $connection->exec("ALTER TABLE leaderboard_cache DROP FOREIGN KEY leaderboard_cache_ibfk_1");
        echo "   ✅ Dropped FK constraint 1\n";
    } catch (Exception $e) {
        echo "   ⚠️ FK1: " . $e->getMessage() . "\n";
    }
    
    try {
        $connection->exec("ALTER TABLE leaderboard_cache DROP FOREIGN KEY leaderboard_cache_ibfk_2");
        echo "   ✅ Dropped FK constraint 2\n";
    } catch (Exception $e) {
        echo "   ⚠️ FK2: " . $e->getMessage() . "\n";
    }
    
    // Step 3: Drop the problematic unique constraint
    echo "3. Dropping unique constraint...\n";
    try {
        $connection->exec("ALTER TABLE leaderboard_cache DROP INDEX unique_event_contestant");
        echo "   ✅ Dropped unique constraint\n";
    } catch (Exception $e) {
        echo "   ⚠️ Unique constraint: " . $e->getMessage() . "\n";
    }
    
    // Step 4: Clear data
    echo "4. Clearing data...\n";
    $connection->exec("DELETE FROM leaderboard_cache");
    echo "   ✅ Cleared cache\n";
    
    // Step 5: Add new unique constraint
    echo "5. Adding category-aware unique constraint...\n";
    try {
        $connection->exec("ALTER TABLE leaderboard_cache ADD UNIQUE KEY unique_event_contestant_category (event_id, contestant_id, category_id)");
        echo "   ✅ Added new constraint\n";
    } catch (Exception $e) {
        echo "   ⚠️ New constraint: " . $e->getMessage() . "\n";
    }
    
    // Step 6: Re-add foreign keys
    echo "6. Re-adding foreign key constraints...\n";
    try {
        $connection->exec("ALTER TABLE leaderboard_cache ADD CONSTRAINT leaderboard_cache_ibfk_1 FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE");
        echo "   ✅ Added FK constraint 1\n";
    } catch (Exception $e) {
        echo "   ⚠️ FK1: " . $e->getMessage() . "\n";
    }
    
    try {
        $connection->exec("ALTER TABLE leaderboard_cache ADD CONSTRAINT leaderboard_cache_ibfk_2 FOREIGN KEY (contestant_id) REFERENCES contestants (id) ON DELETE CASCADE");
        echo "   ✅ Added FK constraint 2\n";
    } catch (Exception $e) {
        echo "   ⚠️ FK2: " . $e->getMessage() . "\n";
    }
    
    // Step 7: Rebuild cache
    echo "7. Rebuilding cache...\n";
    $connection->exec("
        INSERT INTO leaderboard_cache (event_id, contestant_id, category_id, total_votes, updated_at)
        SELECT 
            v.event_id,
            v.contestant_id,
            v.category_id,
            SUM(v.quantity) as total_votes,
            NOW()
        FROM votes v
        WHERE v.category_id IS NOT NULL
        GROUP BY v.event_id, v.contestant_id, v.category_id
    ");
    echo "   ✅ Rebuilt cache\n";
    
    // Step 8: Verify John Bongo
    echo "8. Verifying John Bongo...\n";
    $stmt = $connection->prepare("
        SELECT 
            c.name as contestant_name,
            cat.name as category_name,
            lc.total_votes
        FROM leaderboard_cache lc
        INNER JOIN contestants c ON lc.contestant_id = c.id
        INNER JOIN categories cat ON lc.category_id = cat.id
        WHERE c.name LIKE '%John%Bongo%'
        ORDER BY cat.name
    ");
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    if (!empty($results)) {
        echo "   John Bongo results:\n";
        foreach ($results as $result) {
            echo "   ✅ {$result['category_name']}: {$result['total_votes']} votes\n";
        }
    } else {
        echo "   ❌ No results found\n";
    }
    
    echo "\n🎉 Constraint fix completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
