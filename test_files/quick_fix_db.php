<?php
/**
 * URGENT: Quick Database Fix for Vote Limits
 * Run this immediately to fix the vote quantity column
 */

// Simple database connection
$host = 'localhost';
$dbname = 'smartcast';  // Update if your database name is different
$username = 'root';     // Update if your username is different
$password = '';         // Update if you have a password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Database Column Fix</h2>";
    
    // Check current column type
    echo "<h3>📊 Current Column Types:</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM votes LIKE 'quantity'");
    $column = $stmt->fetch();
    echo "<p><strong>votes.quantity:</strong> " . $column['Type'] . "</p>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM leaderboard_cache LIKE 'total_votes'");
    $column = $stmt->fetch();
    echo "<p><strong>leaderboard_cache.total_votes:</strong> " . $column['Type'] . "</p>";
    
    // Fix the columns
    echo "<h3>🚀 Applying Fixes:</h3>";
    
    $pdo->exec("ALTER TABLE votes MODIFY COLUMN quantity INT UNSIGNED NOT NULL DEFAULT 1");
    echo "<p>✅ Fixed votes.quantity column</p>";
    
    $pdo->exec("ALTER TABLE leaderboard_cache MODIFY COLUMN total_votes INT UNSIGNED NOT NULL DEFAULT 0");
    echo "<p>✅ Fixed leaderboard_cache.total_votes column</p>";
    
    // Verify changes
    echo "<h3>📊 Updated Column Types:</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM votes LIKE 'quantity'");
    $column = $stmt->fetch();
    echo "<p><strong>votes.quantity:</strong> " . $column['Type'] . " (UPDATED)</p>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM leaderboard_cache LIKE 'total_votes'");
    $column = $stmt->fetch();
    echo "<p><strong>leaderboard_cache.total_votes:</strong> " . $column['Type'] . " (UPDATED)</p>";
    
    // Show recent problematic votes
    echo "<h3>📋 Recent Votes (checking for capped quantities):</h3>";
    $stmt = $pdo->query("
        SELECT v.id, v.quantity, t.amount, (t.amount / 2.0) as expected_votes
        FROM votes v 
        JOIN transactions t ON v.transaction_id = t.id 
        WHERE t.amount > 200 
        ORDER BY v.created_at DESC 
        LIMIT 5
    ");
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Vote ID</th><th>Recorded Quantity</th><th>Amount Paid</th><th>Expected Votes</th><th>Status</th></tr>";
    
    while ($row = $stmt->fetch()) {
        $status = ($row['quantity'] == $row['expected_votes']) ? '✅ Correct' : '❌ Capped';
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['quantity']}</td>";
        echo "<td>\${$row['amount']}</td>";
        echo "<td>{$row['expected_votes']}</td>";
        echo "<td>{$status}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>🎉 Fix Complete!</h3>";
    echo "<p><strong>✅ Database schema updated successfully!</strong></p>";
    echo "<p>🔄 <strong>Now try voting with 150 votes again - it should work!</strong></p>";
    echo "<p>📝 Vote quantities can now support up to 4,294,967,295 votes</p>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>Database connection failed: " . $e->getMessage() . "</p>";
    echo "<h4>Manual Fix:</h4>";
    echo "<p>Run this SQL in phpMyAdmin:</p>";
    echo "<pre>";
    echo "ALTER TABLE votes MODIFY COLUMN quantity INT UNSIGNED NOT NULL DEFAULT 1;\n";
    echo "ALTER TABLE leaderboard_cache MODIFY COLUMN total_votes INT UNSIGNED NOT NULL DEFAULT 0;";
    echo "</pre>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; }
th, td { padding: 8px 12px; border: 1px solid #ddd; }
th { background-color: #f5f5f5; }
pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
</style>
