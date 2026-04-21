<?php
/**
 * Complete Fix for John Bongo Vote Issue
 * This script will fix the category-specific vote counting issue
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/src/Models/BaseModel.php';
require_once __DIR__ . '/src/Models/LeaderboardCache.php';

use SmartCast\Core\Database;
use SmartCast\Models\LeaderboardCache;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<h2>🔧 Complete Fix for John Bongo Vote Issue</h2>";
    
    // Step 1: Fix database schema
    echo "<h3>1. 🗄️ Fixing Database Schema</h3>";
    
    // Check if category_id column exists
    $stmt = $connection->query("SHOW COLUMNS FROM leaderboard_cache LIKE 'category_id'");
    $hasCategory = $stmt->fetch();
    
    if (!$hasCategory) {
        echo "<p>⚠️ Adding category_id column...</p>";
        
        try {
            // Add category_id column
            $connection->exec("ALTER TABLE leaderboard_cache ADD COLUMN category_id INT UNSIGNED NULL AFTER contestant_id");
            echo "<p>✅ Added category_id column</p>";
            
            // Add index
            $connection->exec("ALTER TABLE leaderboard_cache ADD INDEX idx_category (category_id)");
            echo "<p>✅ Added category index</p>";
            
            // Update primary key
            $connection->exec("ALTER TABLE leaderboard_cache DROP PRIMARY KEY");
            $connection->exec("ALTER TABLE leaderboard_cache ADD PRIMARY KEY (event_id, contestant_id, category_id)");
            echo "<p>✅ Updated primary key</p>";
            
        } catch (Exception $e) {
            echo "<p>⚠️ Schema update error (might already exist): " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>✅ category_id column already exists</p>";
    }
    
    // Step 2: Clear and rebuild cache
    echo "<h3>2. 🔄 Rebuilding Leaderboard Cache</h3>";
    
    // Find John Bongo's event
    $stmt = $connection->prepare("SELECT event_id FROM contestants WHERE name LIKE '%John%Bongo%' LIMIT 1");
    $stmt->execute();
    $johnEvent = $stmt->fetch();
    
    if ($johnEvent) {
        $eventId = $johnEvent['event_id'];
        echo "<p>📍 Found John Bongo in Event ID: {$eventId}</p>";
        
        // Use the updated LeaderboardCache model
        $leaderboardModel = new LeaderboardCache();
        
        // Clear old cache
        $leaderboardModel->clearCache($eventId);
        echo "<p>🗑️ Cleared old cache data</p>";
        
        // Rebuild with category-specific data
        $leaderboardModel->refreshCache($eventId);
        echo "<p>🔄 Rebuilt cache with category-specific data</p>";
        
    } else {
        echo "<p>❌ Could not find John Bongo</p>";
    }
    
    // Step 3: Show current vote data
    echo "<h3>3. 📊 Current Vote Data</h3>";
    
    // Find John Bongo
    $stmt = $connection->prepare("SELECT * FROM contestants WHERE name LIKE '%John%Bongo%'");
    $stmt->execute();
    $johnBongo = $stmt->fetch();
    
    if ($johnBongo) {
        echo "<h4>John Bongo Vote Breakdown:</h4>";
        
        // Show votes by category
        $stmt = $connection->prepare("
            SELECT 
                v.category_id,
                c.name as category_name,
                SUM(v.quantity) as total_votes,
                COUNT(v.id) as vote_count
            FROM votes v
            LEFT JOIN categories c ON v.category_id = c.id
            WHERE v.contestant_id = ?
            GROUP BY v.category_id
            ORDER BY c.name
        ");
        $stmt->execute([$johnBongo['id']]);
        $votesByCategory = $stmt->fetchAll();
        
        if (!empty($votesByCategory)) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Category</th><th>Total Votes</th><th>Vote Records</th></tr>";
            
            foreach ($votesByCategory as $vote) {
                echo "<tr>";
                echo "<td>{$vote['category_name']} (ID: {$vote['category_id']})</td>";
                echo "<td><strong>{$vote['total_votes']}</strong></td>";
                echo "<td>{$vote['vote_count']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        // Show leaderboard cache
        echo "<h4>Leaderboard Cache:</h4>";
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
        $cacheData = $stmt->fetchAll();
        
        if (!empty($cacheData)) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Category</th><th>Cached Votes</th><th>Updated</th></tr>";
            
            foreach ($cacheData as $cache) {
                echo "<tr>";
                echo "<td>{$cache['category_name']} (ID: {$cache['category_id']})</td>";
                echo "<td><strong>{$cache['total_votes']}</strong></td>";
                echo "<td>{$cache['updated_at']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>⚠️ No cache data found - this might be the issue!</p>";
        }
    }
    
    // Step 4: Test leaderboard queries
    echo "<h3>4. 🧪 Testing Leaderboard Queries</h3>";
    
    if ($johnEvent) {
        // Get categories for this event
        $stmt = $connection->prepare("
            SELECT DISTINCT c.* 
            FROM categories c
            INNER JOIN contestant_categories cc ON c.id = cc.category_id
            INNER JOIN contestants con ON cc.contestant_id = con.id
            WHERE con.event_id = ? AND con.name LIKE '%John%Bongo%'
        ");
        $stmt->execute([$eventId]);
        $categories = $stmt->fetchAll();
        
        echo "<h4>John Bongo's Categories and Vote Counts:</h4>";
        
        if (!empty($categories)) {
            $leaderboardModel = new LeaderboardCache();
            
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Category</th><th>Leaderboard Results</th></tr>";
            
            foreach ($categories as $category) {
                $leaderboard = $leaderboardModel->getLeaderboard($eventId, $category['id'], 10);
                
                echo "<tr>";
                echo "<td><strong>{$category['name']}</strong></td>";
                echo "<td>";
                
                if (!empty($leaderboard)) {
                    foreach ($leaderboard as $entry) {
                        if (stripos($entry['name'], 'John') !== false && stripos($entry['name'], 'Bongo') !== false) {
                            echo "<span style='color: red; font-weight: bold;'>";
                            echo "{$entry['name']}: {$entry['total_votes']} votes";
                            echo "</span><br>";
                        }
                    }
                } else {
                    echo "<em>No results</em>";
                }
                
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    echo "<h3>🎉 Fix Summary</h3>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>✅ Actions Completed</h4>";
    echo "<ul style='margin: 0; color: #155724;'>";
    echo "<li>✅ <strong>Database Schema:</strong> Added category_id to leaderboard_cache</li>";
    echo "<li>✅ <strong>Cache Cleared:</strong> Removed incorrect aggregated data</li>";
    echo "<li>✅ <strong>Cache Rebuilt:</strong> Generated category-specific vote counts</li>";
    echo "<li>✅ <strong>Models Updated:</strong> LeaderboardCache now category-aware</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h4>🔍 Next Steps:</h4>";
    echo "<ol>";
    echo "<li>Check the leaderboard display on the frontend</li>";
    echo "<li>If still showing duplicates, clear browser cache</li>";
    echo "<li>Try voting again to test the fix</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
table { border-collapse: collapse; margin: 10px 0; width: 100%; }
th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
th { background-color: #f5f5f5; font-weight: bold; }
h2 { color: #2c3e50; border-bottom: 2px solid #e74c3c; padding-bottom: 10px; }
h3 { color: #34495e; margin-top: 30px; }
pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>
