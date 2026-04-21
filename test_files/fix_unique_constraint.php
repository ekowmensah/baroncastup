<?php
/**
 * Fix Unique Constraint Issue in Leaderboard Cache
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "🔧 Fixing Unique Constraint Issue\n\n";
    
    // Step 1: Drop the old unique constraint
    echo "1. Dropping old unique constraint...\n";
    try {
        $connection->exec("ALTER TABLE leaderboard_cache DROP INDEX unique_event_contestant");
        echo "   ✅ Dropped old constraint\n";
    } catch (Exception $e) {
        echo "   ⚠️ Constraint might not exist: " . $e->getMessage() . "\n";
    }
    
    // Step 2: Clear existing data (it's incorrect anyway)
    echo "2. Clearing existing cache data...\n";
    $connection->exec("DELETE FROM leaderboard_cache");
    echo "   ✅ Cleared cache\n";
    
    // Step 3: Add new unique constraint that includes category_id
    echo "3. Adding new unique constraint...\n";
    $connection->exec("ALTER TABLE leaderboard_cache ADD UNIQUE KEY unique_event_contestant_category (event_id, contestant_id, category_id)");
    echo "   ✅ Added new constraint\n";
    
    // Step 4: Rebuild cache with correct data
    echo "4. Rebuilding cache with category-specific data...\n";
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
    
    // Step 5: Verify John Bongo's data
    echo "5. Verifying John Bongo's data...\n";
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
        echo "   John Bongo vote counts:\n";
        foreach ($results as $result) {
            echo "   - {$result['category_name']}: {$result['total_votes']} votes\n";
        }
    } else {
        echo "   ❌ No data found for John Bongo\n";
    }
    
    // Step 6: Show table structure
    echo "6. Updated table structure:\n";
    $stmt = $connection->query("SHOW INDEX FROM leaderboard_cache");
    $indexes = $stmt->fetchAll();
    
    echo "   Indexes:\n";
    foreach ($indexes as $index) {
        if ($index['Key_name'] == 'unique_event_contestant_category') {
            echo "   ✅ {$index['Key_name']} ({$index['Column_name']})\n";
        }
    }
    
    echo "\n🎉 Fix completed!\n";
    echo "The leaderboard cache now supports category-specific vote counts.\n";
    echo "John Bongo should show correct votes per category.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
