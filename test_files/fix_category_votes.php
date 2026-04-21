<?php
/**
 * Fix Category-Specific Vote Counting
 * This script fixes the issue where votes are aggregated across all categories
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<h2>🔧 Fixing Category-Specific Vote Counting</h2>";
    
    // Step 1: Check if leaderboard_cache has category_id column
    echo "<h3>1. 🔍 Checking Database Structure</h3>";
    
    $stmt = $connection->query("SHOW COLUMNS FROM leaderboard_cache LIKE 'category_id'");
    $hasCategory = $stmt->fetch();
    
    if (!$hasCategory) {
        echo "<p>⚠️ Adding category_id column to leaderboard_cache table...</p>";
        
        // Add category_id column
        $connection->exec("
            ALTER TABLE leaderboard_cache 
            ADD COLUMN category_id INT UNSIGNED NULL AFTER contestant_id,
            ADD INDEX idx_category (category_id)
        ");
        
        // Update the primary key to include category_id
        $connection->exec("
            ALTER TABLE leaderboard_cache 
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (event_id, contestant_id, category_id)
        ");
        
        echo "<p>✅ Added category_id column and updated indexes</p>";
    } else {
        echo "<p>✅ category_id column already exists</p>";
    }
    
    // Step 2: Clear existing cache data (it's incorrect)
    echo "<h3>2. 🗑️ Clearing Incorrect Cache Data</h3>";
    
    $connection->exec("DELETE FROM leaderboard_cache");
    echo "<p>✅ Cleared existing leaderboard cache</p>";
    
    // Step 3: Rebuild cache with correct category-specific data
    echo "<h3>3. 🔄 Rebuilding Category-Specific Cache</h3>";
    
    // Get all vote records with category information
    $stmt = $connection->query("
        SELECT 
            v.event_id,
            v.contestant_id,
            v.category_id,
            SUM(v.quantity) as total_votes
        FROM votes v
        WHERE v.category_id IS NOT NULL
        GROUP BY v.event_id, v.contestant_id, v.category_id
    ");
    $voteData = $stmt->fetchAll();
    
    if (!empty($voteData)) {
        $insertStmt = $connection->prepare("
            INSERT INTO leaderboard_cache (event_id, contestant_id, category_id, total_votes, updated_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        foreach ($voteData as $data) {
            $insertStmt->execute([
                $data['event_id'],
                $data['contestant_id'],
                $data['category_id'],
                $data['total_votes']
            ]);
        }
        
        echo "<p>✅ Rebuilt cache with " . count($voteData) . " category-specific entries</p>";
    } else {
        echo "<p>ℹ️ No vote data found to rebuild cache</p>";
    }
    
    // Step 4: Show the corrected data
    echo "<h3>4. 📊 Corrected Vote Data</h3>";
    
    // Find John Bongo specifically
    $stmt = $connection->prepare("
        SELECT 
            c.name as contestant_name,
            cat.name as category_name,
            lc.total_votes,
            lc.updated_at
        FROM leaderboard_cache lc
        INNER JOIN contestants c ON lc.contestant_id = c.id
        INNER JOIN categories cat ON lc.category_id = cat.id
        WHERE c.name LIKE '%John%Bongo%'
        ORDER BY cat.name
    ");
    $stmt->execute();
    $johnBongoData = $stmt->fetchAll();
    
    if (!empty($johnBongoData)) {
        echo "<h4>John Bongo Vote Counts by Category:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Contestant</th><th>Category</th><th>Total Votes</th><th>Updated</th></tr>";
        
        foreach ($johnBongoData as $data) {
            echo "<tr>";
            echo "<td>{$data['contestant_name']}</td>";
            echo "<td>{$data['category_name']}</td>";
            echo "<td>{$data['total_votes']}</td>";
            echo "<td>{$data['updated_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>ℹ️ No cached data found for John Bongo yet</p>";
    }
    
    // Step 5: Show all category-specific data
    echo "<h4>All Category-Specific Vote Counts:</h4>";
    $stmt = $connection->query("
        SELECT 
            c.name as contestant_name,
            cat.name as category_name,
            lc.total_votes
        FROM leaderboard_cache lc
        INNER JOIN contestants c ON lc.contestant_id = c.id
        INNER JOIN categories cat ON lc.category_id = cat.id
        ORDER BY cat.name, lc.total_votes DESC
        LIMIT 20
    ");
    $allData = $stmt->fetchAll();
    
    if (!empty($allData)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Contestant</th><th>Category</th><th>Total Votes</th></tr>";
        
        foreach ($allData as $data) {
            echo "<tr>";
            echo "<td>{$data['contestant_name']}</td>";
            echo "<td>{$data['category_name']}</td>";
            echo "<td>{$data['total_votes']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>🎉 Fix Complete!</h3>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>✅ Category Vote Counting Fixed</h4>";
    echo "<ul style='margin: 0; color: #155724;'>";
    echo "<li>✅ <strong>Database Schema:</strong> Added category_id to leaderboard_cache</li>";
    echo "<li>✅ <strong>Cache Rebuild:</strong> Regenerated with category-specific data</li>";
    echo "<li>✅ <strong>Vote Model:</strong> Updated to use category-aware caching</li>";
    echo "<li>✅ <strong>Issue Resolution:</strong> Votes now tracked per category</li>";
    echo "</ul>";
    echo "<p style='margin: 10px 0 0 0; color: #155724;'>";
    echo "<strong>Next:</strong> Try voting again and votes should only appear in the specific category voted for.";
    echo "</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<h4>Manual Fix:</h4>";
    echo "<p>Run this SQL manually:</p>";
    echo "<pre>";
    echo "ALTER TABLE leaderboard_cache ADD COLUMN category_id INT UNSIGNED NULL AFTER contestant_id;\n";
    echo "ALTER TABLE leaderboard_cache ADD INDEX idx_category (category_id);\n";
    echo "ALTER TABLE leaderboard_cache DROP PRIMARY KEY, ADD PRIMARY KEY (event_id, contestant_id, category_id);\n";
    echo "DELETE FROM leaderboard_cache;";
    echo "</pre>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
th { background-color: #f5f5f5; font-weight: bold; }
h2 { color: #2c3e50; border-bottom: 2px solid #e74c3c; padding-bottom: 10px; }
h3 { color: #34495e; margin-top: 30px; }
pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
</style>
