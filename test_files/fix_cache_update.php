<?php
/**
 * Fix Cache Update Issue
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "🔧 Fixing Cache Update Issue\n\n";
    
    // Find John Bongo
    $stmt = $connection->prepare("SELECT * FROM contestants WHERE name LIKE '%John%Bongo%'");
    $stmt->execute();
    $johnBongo = $stmt->fetch();
    
    if (!$johnBongo) {
        echo "❌ John Bongo not found\n";
        exit;
    }
    
    echo "👤 John Bongo ID: {$johnBongo['id']}, Event: {$johnBongo['event_id']}\n\n";
    
    // Step 1: Clear existing cache for John Bongo
    echo "1. Clearing existing cache for John Bongo...\n";
    $stmt = $connection->prepare("DELETE FROM leaderboard_cache WHERE contestant_id = ?");
    $stmt->execute([$johnBongo['id']]);
    echo "   ✅ Cleared cache\n";
    
    // Step 2: Get actual vote data by category
    echo "2. Getting actual vote data...\n";
    $stmt = $connection->prepare("
        SELECT 
            v.category_id,
            c.name as category_name,
            SUM(v.quantity) as total_votes
        FROM votes v
        LEFT JOIN categories c ON v.category_id = c.id
        WHERE v.contestant_id = ?
        GROUP BY v.category_id
    ");
    $stmt->execute([$johnBongo['id']]);
    $voteData = $stmt->fetchAll();
    
    if (empty($voteData)) {
        echo "   ❌ No vote data found\n";
        exit;
    }
    
    // Step 3: Manually insert correct cache entries
    echo "3. Inserting correct cache entries...\n";
    $insertStmt = $connection->prepare("
        INSERT INTO leaderboard_cache (event_id, contestant_id, category_id, total_votes, updated_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    foreach ($voteData as $data) {
        $insertStmt->execute([
            $johnBongo['event_id'],
            $johnBongo['id'],
            $data['category_id'],
            $data['total_votes']
        ]);
        
        echo "   ✅ {$data['category_name']}: {$data['total_votes']} votes\n";
    }
    
    // Step 4: Verify the fix
    echo "4. Verifying the fix...\n";
    $stmt = $connection->prepare("
        SELECT 
            lc.*,
            c.name as category_name
        FROM leaderboard_cache lc
        LEFT JOIN categories c ON lc.category_id = c.id
        WHERE lc.contestant_id = ?
        ORDER BY c.name
    ");
    $stmt->execute([$johnBongo['id']]);
    $cacheResults = $stmt->fetchAll();
    
    echo "   Cache entries:\n";
    foreach ($cacheResults as $cache) {
        echo "   - {$cache['category_name']}: {$cache['total_votes']} votes\n";
    }
    
    // Step 5: Test leaderboard queries for each category
    echo "5. Testing leaderboard queries...\n";
    
    // Get John Bongo's categories
    $stmt = $connection->prepare("
        SELECT cc.category_id, c.name as category_name
        FROM contestant_categories cc
        INNER JOIN categories c ON cc.category_id = c.id
        WHERE cc.contestant_id = ? AND cc.active = 1
    ");
    $stmt->execute([$johnBongo['id']]);
    $categories = $stmt->fetchAll();
    
    foreach ($categories as $category) {
        echo "   Category: {$category['category_name']}\n";
        
        $stmt = $connection->prepare("
            SELECT c.name, lc.total_votes
            FROM leaderboard_cache lc
            INNER JOIN contestants c ON lc.contestant_id = c.id
            WHERE lc.event_id = ? AND lc.category_id = ?
            ORDER BY lc.total_votes DESC
            LIMIT 5
        ");
        $stmt->execute([$johnBongo['event_id'], $category['category_id']]);
        $leaderboard = $stmt->fetchAll();
        
        if (!empty($leaderboard)) {
            foreach ($leaderboard as $entry) {
                $highlight = stripos($entry['name'], 'John') !== false ? ' ⭐' : '';
                echo "     - {$entry['name']}: {$entry['total_votes']} votes{$highlight}\n";
            }
        } else {
            echo "     - No leaderboard data\n";
        }
    }
    
    echo "\n🎉 Cache fix completed!\n";
    echo "John Bongo should now show correct vote counts per category.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
