<?php
/**
 * Debug Vote Issue - Multiple Category Problem
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<h2>🔍 Vote Issue Debug - John Bongo</h2>";
    
    // Find John Bongo
    $stmt = $connection->prepare("SELECT * FROM contestants WHERE name LIKE '%John%Bongo%'");
    $stmt->execute();
    $johnBongo = $stmt->fetch();
    
    if (!$johnBongo) {
        echo "<p>❌ John Bongo not found</p>";
        exit;
    }
    
    echo "<h3>👤 Contestant Information</h3>";
    echo "<p><strong>Name:</strong> {$johnBongo['name']}</p>";
    echo "<p><strong>ID:</strong> {$johnBongo['id']}</p>";
    echo "<p><strong>Event ID:</strong> {$johnBongo['event_id']}</p>";
    
    // Check categories
    echo "<h3>📂 Categories John Bongo is in:</h3>";
    $stmt = $connection->prepare("
        SELECT cc.*, c.name as category_name 
        FROM contestant_categories cc
        INNER JOIN categories c ON cc.category_id = c.id
        WHERE cc.contestant_id = ?
    ");
    $stmt->execute([$johnBongo['id']]);
    $categories = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Category ID</th><th>Category Name</th><th>Active</th></tr>";
    foreach ($categories as $cat) {
        echo "<tr>";
        echo "<td>{$cat['category_id']}</td>";
        echo "<td>{$cat['category_name']}</td>";
        echo "<td>" . ($cat['active'] ? '✅' : '❌') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check votes
    echo "<h3>🗳️ Votes for John Bongo:</h3>";
    $stmt = $connection->prepare("
        SELECT v.*, c.name as category_name, t.amount
        FROM votes v
        LEFT JOIN categories c ON v.category_id = c.id
        LEFT JOIN transactions t ON v.transaction_id = t.id
        WHERE v.contestant_id = ?
        ORDER BY v.created_at DESC
    ");
    $stmt->execute([$johnBongo['id']]);
    $votes = $stmt->fetchAll();
    
    if (empty($votes)) {
        echo "<p>❌ No votes found for John Bongo</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Vote ID</th><th>Category</th><th>Quantity</th><th>Amount</th><th>Created</th></tr>";
        foreach ($votes as $vote) {
            echo "<tr>";
            echo "<td>{$vote['id']}</td>";
            echo "<td>{$vote['category_name']} (ID: {$vote['category_id']})</td>";
            echo "<td>{$vote['quantity']}</td>";
            echo "<td>\${$vote['amount']}</td>";
            echo "<td>{$vote['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check vote totals by category
        echo "<h3>📊 Vote Totals by Category:</h3>";
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
        ");
        $stmt->execute([$johnBongo['id']]);
        $categoryTotals = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Category</th><th>Total Votes</th><th>Vote Records</th></tr>";
        foreach ($categoryTotals as $total) {
            echo "<tr>";
            echo "<td>{$total['category_name']} (ID: {$total['category_id']})</td>";
            echo "<td>{$total['total_votes']}</td>";
            echo "<td>{$total['vote_count']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check leaderboard cache
    echo "<h3>🏆 Leaderboard Cache:</h3>";
    $stmt = $connection->prepare("SELECT * FROM leaderboard_cache WHERE contestant_id = ?");
    $stmt->execute([$johnBongo['id']]);
    $cacheEntries = $stmt->fetchAll();
    
    if (empty($cacheEntries)) {
        echo "<p>❌ No leaderboard cache entries</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Event ID</th><th>Total Votes</th><th>Updated</th></tr>";
        foreach ($cacheEntries as $cache) {
            echo "<tr>";
            echo "<td>{$cache['event_id']}</td>";
            echo "<td>{$cache['total_votes']}</td>";
            echo "<td>{$cache['updated_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check leaderboard_cache table structure
    echo "<h3>🗄️ Leaderboard Cache Table Structure:</h3>";
    $stmt = $connection->query("DESCRIBE leaderboard_cache");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Analysis
    echo "<h3>🔍 Issue Analysis:</h3>";
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
    echo "<h4 style='color: #856404; margin: 0 0 10px 0;'>⚠️ Problem Identified</h4>";
    echo "<p style='margin: 0; color: #856404;'>";
    
    $hasCategory = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'category_id') {
            $hasCategory = true;
            break;
        }
    }
    
    if (!$hasCategory) {
        echo "<strong>Issue:</strong> The leaderboard_cache table does not have a category_id column. ";
        echo "This means votes are being aggregated across ALL categories for each contestant, ";
        echo "instead of being tracked per category.";
    } else {
        echo "<strong>Issue:</strong> The updateLeaderboardCache method is not using category-specific vote counts.";
    }
    
    echo "</p></div>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
th { background-color: #f5f5f5; font-weight: bold; }
h2 { color: #2c3e50; border-bottom: 2px solid #e74c3c; padding-bottom: 10px; }
h3 { color: #34495e; margin-top: 30px; }
</style>
