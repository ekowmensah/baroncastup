<?php
/**
 * Test Category Vote Fix
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<h2>🧪 Testing Category Vote Fix</h2>";
    
    // Find John Bongo
    $stmt = $connection->prepare("SELECT * FROM contestants WHERE name LIKE '%John%Bongo%'");
    $stmt->execute();
    $johnBongo = $stmt->fetch();
    
    if (!$johnBongo) {
        echo "<p>❌ John Bongo not found</p>";
        exit;
    }
    
    echo "<h3>📊 Current Vote Status</h3>";
    echo "<p><strong>Contestant:</strong> {$johnBongo['name']} (ID: {$johnBongo['id']})</p>";
    
    // Get categories John Bongo is in
    $stmt = $connection->prepare("
        SELECT cc.*, c.name as category_name 
        FROM contestant_categories cc
        INNER JOIN categories c ON cc.category_id = c.id
        WHERE cc.contestant_id = ? AND cc.active = 1
    ");
    $stmt->execute([$johnBongo['id']]);
    $categories = $stmt->fetchAll();
    
    echo "<h4>Categories John Bongo is in:</h4>";
    echo "<ul>";
    foreach ($categories as $cat) {
        echo "<li>{$cat['category_name']} (ID: {$cat['category_id']})</li>";
    }
    echo "</ul>";
    
    // Check votes by category from votes table
    echo "<h4>Actual Votes by Category (from votes table):</h4>";
    $stmt = $connection->prepare("
        SELECT 
            v.category_id,
            c.name as category_name,
            SUM(v.quantity) as total_votes,
            COUNT(v.id) as vote_records
        FROM votes v
        LEFT JOIN categories c ON v.category_id = c.id
        WHERE v.contestant_id = ?
        GROUP BY v.category_id
        ORDER BY c.name
    ");
    $stmt->execute([$johnBongo['id']]);
    $actualVotes = $stmt->fetchAll();
    
    if (!empty($actualVotes)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Category</th><th>Total Votes</th><th>Vote Records</th></tr>";
        foreach ($actualVotes as $vote) {
            echo "<tr>";
            echo "<td>{$vote['category_name']}</td>";
            echo "<td><strong>{$vote['total_votes']}</strong></td>";
            echo "<td>{$vote['vote_records']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No votes found in votes table</p>";
    }
    
    // Check leaderboard cache
    echo "<h4>Cached Votes by Category (from leaderboard_cache):</h4>";
    $stmt = $connection->prepare("
        SELECT 
            lc.category_id,
            c.name as category_name,
            lc.total_votes,
            lc.updated_at
        FROM leaderboard_cache lc
        LEFT JOIN categories c ON lc.category_id = c.id
        WHERE lc.contestant_id = ?
        ORDER BY c.name
    ");
    $stmt->execute([$johnBongo['id']]);
    $cachedVotes = $stmt->fetchAll();
    
    if (!empty($cachedVotes)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Category</th><th>Cached Votes</th><th>Updated</th></tr>";
        foreach ($cachedVotes as $cache) {
            echo "<tr>";
            echo "<td>{$cache['category_name']}</td>";
            echo "<td><strong>{$cache['total_votes']}</strong></td>";
            echo "<td>{$cache['updated_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No cached votes found</p>";
    }
    
    // Test the new leaderboard method
    echo "<h4>Testing New Leaderboard Method:</h4>";
    
    require_once __DIR__ . '/src/Models/BaseModel.php';
    require_once __DIR__ . '/src/Models/LeaderboardCache.php';
    
    $leaderboardModel = new \SmartCast\Models\LeaderboardCache();
    
    foreach ($categories as $category) {
        echo "<h5>Category: {$category['category_name']}</h5>";
        
        $leaderboard = $leaderboardModel->getLeaderboard($johnBongo['event_id'], $category['category_id'], 5);
        
        if (!empty($leaderboard)) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Rank</th><th>Contestant</th><th>Votes</th></tr>";
            foreach ($leaderboard as $index => $entry) {
                $highlight = (stripos($entry['name'], 'John') !== false && stripos($entry['name'], 'Bongo') !== false) ? 'style="background-color: #ffffcc;"' : '';
                echo "<tr {$highlight}>";
                echo "<td>" . ($index + 1) . "</td>";
                echo "<td>{$entry['name']}</td>";
                echo "<td><strong>{$entry['total_votes']}</strong></td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p><em>No leaderboard data for this category</em></p>";
        }
    }
    
    // Summary
    echo "<h3>🎯 Fix Status</h3>";
    
    $duplicateIssue = false;
    $voteCounts = [];
    
    foreach ($actualVotes as $vote) {
        $voteCounts[] = $vote['total_votes'];
    }
    
    // Check if all vote counts are the same (indicating duplication)
    if (count(array_unique($voteCounts)) === 1 && count($voteCounts) > 1) {
        $duplicateIssue = true;
    }
    
    if ($duplicateIssue) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
        echo "<h4 style='color: #721c24; margin: 0 0 10px 0;'>❌ Issue Still Exists</h4>";
        echo "<p style='margin: 0; color: #721c24;'>John Bongo still has the same vote count ({$voteCounts[0]}) in multiple categories.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
        echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>✅ Fix Successful</h4>";
        echo "<p style='margin: 0; color: #155724;'>John Bongo's votes are now properly separated by category!</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
table { border-collapse: collapse; margin: 10px 0; width: 100%; }
th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
th { background-color: #f5f5f5; font-weight: bold; }
h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
h3 { color: #34495e; margin-top: 30px; }
</style>
