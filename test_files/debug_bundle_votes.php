<?php
/**
 * Debug Bundle Vote Calculation Issue
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<h2>🔍 Debug Bundle Vote Calculation</h2>";
    
    // Find the most recent transaction
    echo "<h3>1. 📊 Recent Transactions</h3>";
    $stmt = $connection->query("
        SELECT 
            t.*,
            c.name as contestant_name,
            cat.name as category_name,
            vb.name as bundle_name,
            vb.votes as bundle_votes,
            vb.price as bundle_price
        FROM transactions t
        LEFT JOIN contestants c ON t.contestant_id = c.id
        LEFT JOIN categories cat ON t.category_id = cat.id
        LEFT JOIN vote_bundles vb ON t.bundle_id = vb.id
        ORDER BY t.created_at DESC
        LIMIT 5
    ");
    $transactions = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Contestant</th><th>Bundle</th><th>Bundle Votes</th><th>Amount</th><th>Status</th><th>Created</th></tr>";
    
    foreach ($transactions as $transaction) {
        echo "<tr>";
        echo "<td>{$transaction['id']}</td>";
        echo "<td>{$transaction['contestant_name']}</td>";
        echo "<td>{$transaction['bundle_name']} (ID: {$transaction['bundle_id']})</td>";
        echo "<td><strong>{$transaction['bundle_votes']}</strong></td>";
        echo "<td>\${$transaction['amount']}</td>";
        echo "<td>{$transaction['status']}</td>";
        echo "<td>{$transaction['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Find the specific 10-vote bundle transaction
    echo "<h3>2. 🎯 10-Vote Bundle Analysis</h3>";
    $stmt = $connection->prepare("
        SELECT 
            t.*,
            c.name as contestant_name,
            vb.name as bundle_name,
            vb.votes as bundle_votes,
            vb.price as bundle_price,
            v.quantity as actual_votes_recorded
        FROM transactions t
        LEFT JOIN contestants c ON t.contestant_id = c.id
        LEFT JOIN vote_bundles vb ON t.bundle_id = vb.id
        LEFT JOIN votes v ON t.id = v.transaction_id
        WHERE vb.votes = 10 OR t.amount IN (
            SELECT price FROM vote_bundles WHERE votes = 10
        )
        ORDER BY t.created_at DESC
        LIMIT 3
    ");
    $stmt->execute();
    $bundleTransactions = $stmt->fetchAll();
    
    if (!empty($bundleTransactions)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Transaction ID</th><th>Contestant</th><th>Bundle Info</th><th>Expected Votes</th><th>Actual Votes</th><th>Amount</th><th>Issue</th></tr>";
        
        foreach ($bundleTransactions as $trans) {
            $issue = '';
            if ($trans['bundle_votes'] != $trans['actual_votes_recorded']) {
                $issue = "❌ Mismatch!";
            } else {
                $issue = "✅ Correct";
            }
            
            echo "<tr>";
            echo "<td>{$trans['id']}</td>";
            echo "<td>{$trans['contestant_name']}</td>";
            echo "<td>{$trans['bundle_name']} (ID: {$trans['bundle_id']})</td>";
            echo "<td><strong>{$trans['bundle_votes']}</strong></td>";
            echo "<td><strong>{$trans['actual_votes_recorded']}</strong></td>";
            echo "<td>\${$trans['amount']}</td>";
            echo "<td>{$issue}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No 10-vote bundle transactions found</p>";
    }
    
    // Check the vote calculation logic
    echo "<h3>3. 🧮 Vote Calculation Logic Test</h3>";
    
    // Get a recent transaction with bundle
    $stmt = $connection->prepare("
        SELECT t.*, vb.votes as bundle_votes, vb.price as bundle_price, e.vote_price
        FROM transactions t
        LEFT JOIN vote_bundles vb ON t.bundle_id = vb.id
        LEFT JOIN events e ON t.event_id = e.id
        WHERE t.bundle_id IS NOT NULL
        ORDER BY t.created_at DESC
        LIMIT 1
    ");
    $stmt->execute();
    $testTransaction = $stmt->fetch();
    
    if ($testTransaction) {
        echo "<h4>Testing Transaction ID: {$testTransaction['id']}</h4>";
        echo "<p><strong>Bundle ID:</strong> {$testTransaction['bundle_id']}</p>";
        echo "<p><strong>Bundle Votes:</strong> {$testTransaction['bundle_votes']}</p>";
        echo "<p><strong>Bundle Price:</strong> \${$testTransaction['bundle_price']}</p>";
        echo "<p><strong>Transaction Amount:</strong> \${$testTransaction['amount']}</p>";
        echo "<p><strong>Event Vote Price:</strong> \${$testTransaction['vote_price']}</p>";
        
        // Simulate the getVoteCountFromTransaction logic
        $votePrice = $testTransaction['vote_price'] ?? 0.50;
        $calculatedVotes = (int) ($testTransaction['amount'] / $votePrice);
        $bundleVotes = $testTransaction['bundle_votes'] ?? 1;
        
        echo "<h4>Vote Calculation Simulation:</h4>";
        echo "<p><strong>Calculated from amount:</strong> {$calculatedVotes} votes</p>";
        echo "<p><strong>Bundle votes:</strong> {$bundleVotes} votes</p>";
        
        if ($calculatedVotes != $bundleVotes) {
            echo "<p><strong>Logic:</strong> Custom vote detected (calculated ≠ bundle), using calculated: {$calculatedVotes}</p>";
            echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px;'>";
            echo "<strong>⚠️ Issue Found:</strong> The system is treating this as a custom vote instead of a bundle vote!";
            echo "</div>";
        } else {
            echo "<p><strong>Logic:</strong> Package vote detected (calculated = bundle), using bundle: {$bundleVotes}</p>";
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px;'>";
            echo "<strong>✅ Correct:</strong> Bundle vote calculation is working properly.";
            echo "</div>";
        }
    }
    
    // Check for any rounding issues or price mismatches
    echo "<h3>4. 💰 Bundle Price Analysis</h3>";
    $stmt = $connection->query("
        SELECT 
            vb.*,
            (vb.price / vb.votes) as price_per_vote,
            e.vote_price as event_vote_price
        FROM vote_bundles vb
        INNER JOIN events e ON vb.event_id = e.id
        WHERE vb.votes = 10
        ORDER BY vb.created_at DESC
        LIMIT 3
    ");
    $bundles = $stmt->fetchAll();
    
    if (!empty($bundles)) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Bundle</th><th>Votes</th><th>Price</th><th>Price/Vote</th><th>Event Vote Price</th><th>Match</th></tr>";
        
        foreach ($bundles as $bundle) {
            $match = abs($bundle['price_per_vote'] - $bundle['event_vote_price']) < 0.01 ? '✅' : '❌';
            
            echo "<tr>";
            echo "<td>{$bundle['name']}</td>";
            echo "<td>{$bundle['votes']}</td>";
            echo "<td>\${$bundle['price']}</td>";
            echo "<td>\${$bundle['price_per_vote']}</td>";
            echo "<td>\${$bundle['event_vote_price']}</td>";
            echo "<td>{$match}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check the actual VoteController logic
    echo "<h3>5. 🔍 Debugging Recommendations</h3>";
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px;'>";
    echo "<h4>Possible Issues:</h4>";
    echo "<ul>";
    echo "<li><strong>Price Mismatch:</strong> Bundle price doesn't match expected calculation</li>";
    echo "<li><strong>Rounding Errors:</strong> Division causing incorrect vote calculation</li>";
    echo "<li><strong>Logic Error:</strong> getVoteCountFromTransaction treating bundle as custom vote</li>";
    echo "<li><strong>Data Corruption:</strong> Bundle data modified after creation</li>";
    echo "</ul>";
    echo "<h4>Next Steps:</h4>";
    echo "<ul>";
    echo "<li>Check the exact transaction that recorded 8 instead of 10 votes</li>";
    echo "<li>Verify the bundle data at time of transaction</li>";
    echo "<li>Test the getVoteCountFromTransaction method with the specific transaction</li>";
    echo "</ul>";
    echo "</div>";
    
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
