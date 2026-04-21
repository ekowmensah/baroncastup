<?php
/**
 * Debug Vote Duplication Issue - Comprehensive Analysis
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<h2>🔍 Comprehensive Vote Duplication Debug</h2>";
    
    // Find John Bongo
    $stmt = $connection->prepare("SELECT * FROM contestants WHERE name LIKE '%John%Bongo%'");
    $stmt->execute();
    $johnBongo = $stmt->fetch();
    
    if (!$johnBongo) {
        echo "<p>❌ John Bongo not found</p>";
        exit;
    }
    
    echo "<h3>👤 John Bongo Info</h3>";
    echo "<p><strong>ID:</strong> {$johnBongo['id']}</p>";
    echo "<p><strong>Event ID:</strong> {$johnBongo['event_id']}</p>";
    
    // 1. Check raw votes table
    echo "<h3>1. 📊 Raw Votes Table</h3>";
    $stmt = $connection->prepare("
        SELECT 
            v.*,
            c.name as category_name,
            t.amount,
            t.status as transaction_status
        FROM votes v
        LEFT JOIN categories c ON v.category_id = c.id
        LEFT JOIN transactions t ON v.transaction_id = t.id
        WHERE v.contestant_id = ?
        ORDER BY v.created_at DESC
    ");
    $stmt->execute([$johnBongo['id']]);
    $rawVotes = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Vote ID</th><th>Category</th><th>Quantity</th><th>Amount</th><th>Status</th><th>Created</th></tr>";
    
    $totalVoteRecords = 0;
    $totalQuantity = 0;
    
    foreach ($rawVotes as $vote) {
        $totalVoteRecords++;
        $totalQuantity += $vote['quantity'];
        
        echo "<tr>";
        echo "<td>{$vote['id']}</td>";
        echo "<td>{$vote['category_name']} (ID: {$vote['category_id']})</td>";
        echo "<td><strong>{$vote['quantity']}</strong></td>";
        echo "<td>\${$vote['amount']}</td>";
        echo "<td>{$vote['transaction_status']}</td>";
        echo "<td>{$vote['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>Summary:</strong> {$totalVoteRecords} vote records, {$totalQuantity} total votes</p>";
    
    // 2. Check leaderboard cache
    echo "<h3>2. 🏆 Leaderboard Cache</h3>";
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
        echo "<p>❌ No leaderboard cache data found</p>";
    }
    
    // 3. Check vote ledger
    echo "<h3>3. 📋 Vote Ledger</h3>";
    $stmt = $connection->prepare("
        SELECT vl.*, c.name as category_name
        FROM vote_ledger vl
        LEFT JOIN votes v ON vl.vote_id = v.id
        LEFT JOIN categories c ON v.category_id = c.id
        WHERE vl.contestant_id = ?
        ORDER BY vl.created_at DESC
    ");
    $stmt->execute([$johnBongo['id']]);
    $ledgerData = $stmt->fetchAll();
    
    if (!empty($ledgerData)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Ledger ID</th><th>Vote ID</th><th>Category</th><th>Quantity</th><th>Hash</th><th>Created</th></tr>";
        
        foreach ($ledgerData as $ledger) {
            echo "<tr>";
            echo "<td>{$ledger['id']}</td>";
            echo "<td>{$ledger['vote_id']}</td>";
            echo "<td>{$ledger['category_name']}</td>";
            echo "<td><strong>{$ledger['quantity']}</strong></td>";
            echo "<td>" . substr($ledger['hash'], 0, 8) . "...</td>";
            echo "<td>{$ledger['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No vote ledger data found</p>";
    }
    
    // 4. Check what categories John Bongo is in
    echo "<h3>4. 📂 John Bongo's Categories</h3>";
    $stmt = $connection->prepare("
        SELECT cc.*, c.name as category_name
        FROM contestant_categories cc
        INNER JOIN categories c ON cc.category_id = c.id
        WHERE cc.contestant_id = ? AND cc.active = 1
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
    
    // 5. Check if there are any views or other tables being used
    echo "<h3>5. 🔍 Database Schema Analysis</h3>";
    
    // Check for views
    $stmt = $connection->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
    $views = $stmt->fetchAll();
    
    if (!empty($views)) {
        echo "<h4>Database Views:</h4>";
        echo "<ul>";
        foreach ($views as $view) {
            echo "<li>{$view['Tables_in_smartcast']}</li>";
        }
        echo "</ul>";
    }
    
    // Check leaderboard_cache structure
    echo "<h4>Leaderboard Cache Structure:</h4>";
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
    
    // 6. Test the current leaderboard queries
    echo "<h3>6. 🧪 Testing Current Queries</h3>";
    
    foreach ($categories as $category) {
        echo "<h4>Category: {$category['category_name']}</h4>";
        
        // Test direct vote count
        $stmt = $connection->prepare("
            SELECT SUM(v.quantity) as total_votes
            FROM votes v
            WHERE v.contestant_id = ? AND v.category_id = ?
        ");
        $stmt->execute([$johnBongo['id'], $category['category_id']]);
        $directCount = $stmt->fetch();
        
        echo "<p><strong>Direct vote count:</strong> {$directCount['total_votes']}</p>";
        
        // Test cache count
        $stmt = $connection->prepare("
            SELECT total_votes
            FROM leaderboard_cache
            WHERE contestant_id = ? AND category_id = ?
        ");
        $stmt->execute([$johnBongo['id'], $category['category_id']]);
        $cacheCount = $stmt->fetch();
        
        echo "<p><strong>Cache count:</strong> " . ($cacheCount ? $cacheCount['total_votes'] : 'Not found') . "</p>";
        
        // Test leaderboard query
        $stmt = $connection->prepare("
            SELECT lc.*, c.name, c.contestant_code
            FROM leaderboard_cache lc
            INNER JOIN contestants c ON lc.contestant_id = c.id
            WHERE lc.event_id = ? AND lc.category_id = ?
            ORDER BY lc.total_votes DESC
            LIMIT 5
        ");
        $stmt->execute([$johnBongo['event_id'], $category['category_id']]);
        $leaderboard = $stmt->fetchAll();
        
        echo "<p><strong>Leaderboard results:</strong></p>";
        if (!empty($leaderboard)) {
            echo "<ul>";
            foreach ($leaderboard as $entry) {
                $highlight = $entry['contestant_id'] == $johnBongo['id'] ? 'style="color: red; font-weight: bold;"' : '';
                echo "<li {$highlight}>{$entry['name']}: {$entry['total_votes']} votes</li>";
            }
            echo "</ul>";
        } else {
            echo "<p><em>No leaderboard data</em></p>";
        }
    }
    
    // 7. Analysis and recommendations
    echo "<h3>7. 🎯 Analysis</h3>";
    
    $issueFound = false;
    $issues = [];
    
    // Check if votes are duplicated in raw table
    $categoryVotes = [];
    foreach ($rawVotes as $vote) {
        if (!isset($categoryVotes[$vote['category_id']])) {
            $categoryVotes[$vote['category_id']] = 0;
        }
        $categoryVotes[$vote['category_id']] += $vote['quantity'];
    }
    
    $uniqueVoteCounts = array_unique(array_values($categoryVotes));
    if (count($uniqueVoteCounts) === 1 && count($categoryVotes) > 1) {
        $issues[] = "❌ Same vote count ({$uniqueVoteCounts[0]}) in multiple categories in votes table";
        $issueFound = true;
    }
    
    // Check if cache is duplicated
    $cacheVotes = [];
    foreach ($cacheData as $cache) {
        $cacheVotes[$cache['category_id']] = $cache['total_votes'];
    }
    
    $uniqueCacheCounts = array_unique(array_values($cacheVotes));
    if (count($uniqueCacheCounts) === 1 && count($cacheVotes) > 1) {
        $issues[] = "❌ Same vote count ({$uniqueCacheCounts[0]}) in multiple categories in cache";
        $issueFound = true;
    }
    
    if ($issueFound) {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
        echo "<h4 style='color: #721c24; margin: 0 0 10px 0;'>🚨 Issues Found</h4>";
        foreach ($issues as $issue) {
            echo "<p style='margin: 5px 0; color: #721c24;'>{$issue}</p>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
        echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>✅ No Duplication Found</h4>";
        echo "<p style='margin: 0; color: #155724;'>Vote counts appear to be properly separated by category.</p>";
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
h2 { color: #2c3e50; border-bottom: 2px solid #e74c3c; padding-bottom: 10px; }
h3 { color: #34495e; margin-top: 30px; }
</style>
