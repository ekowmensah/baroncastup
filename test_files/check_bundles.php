<?php
/**
 * Check Vote Bundles for Event 23
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<h2>🔍 Vote Bundles for Event 23</h2>";
    
    // Check if bundles exist for event 23
    $stmt = $connection->prepare("SELECT * FROM vote_bundles WHERE event_id = 23 ORDER BY price ASC");
    $stmt->execute();
    $bundles = $stmt->fetchAll();
    
    if (empty($bundles)) {
        echo "<h3>❌ No bundles found for Event 23</h3>";
        echo "<p>Creating default bundles...</p>";
        
        // Get event details
        $stmt = $connection->prepare("SELECT * FROM events WHERE id = 23");
        $stmt->execute();
        $event = $stmt->fetch();
        
        if ($event) {
            $votePrice = $event['vote_price'] ?? 2.00;
            echo "<p><strong>Event:</strong> {$event['name']}</p>";
            echo "<p><strong>Vote Price:</strong> \${$votePrice}</p>";
            
            // Create default bundles
            $defaultBundles = [
                ['name' => 'Single Vote', 'votes' => 1, 'price' => $votePrice],
                ['name' => 'Vote Pack (5)', 'votes' => 5, 'price' => $votePrice * 5 * 0.9], // 10% discount
                ['name' => 'Vote Pack (10)', 'votes' => 10, 'price' => $votePrice * 10 * 0.8], // 20% discount
                ['name' => 'Vote Pack (25)', 'votes' => 25, 'price' => $votePrice * 25 * 0.7], // 30% discount
                ['name' => 'Vote Pack (50)', 'votes' => 50, 'price' => $votePrice * 50 * 0.6], // 40% discount
                ['name' => 'Vote Pack (100)', 'votes' => 100, 'price' => $votePrice * 100 * 0.5], // 50% discount
            ];
            
            foreach ($defaultBundles as $bundleData) {
                $stmt = $connection->prepare("
                    INSERT INTO vote_bundles (event_id, name, votes, price, active, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, 1, NOW(), NOW())
                ");
                $stmt->execute([
                    23,
                    $bundleData['name'],
                    $bundleData['votes'],
                    $bundleData['price']
                ]);
                
                echo "<p>✅ Created: {$bundleData['name']} - {$bundleData['votes']} votes for \${$bundleData['price']}</p>";
            }
            
            echo "<h3>🎉 Default bundles created!</h3>";
            
            // Reload bundles
            $stmt = $connection->prepare("SELECT * FROM vote_bundles WHERE event_id = 23 ORDER BY price ASC");
            $stmt->execute();
            $bundles = $stmt->fetchAll();
        } else {
            echo "<p>❌ Event 23 not found</p>";
        }
    }
    
    if (!empty($bundles)) {
        echo "<h3>📦 Available Vote Bundles:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Votes</th><th>Price</th><th>Price per Vote</th><th>Discount</th><th>Status</th></tr>";
        
        $basePrice = $bundles[0]['price']; // Single vote price
        
        foreach ($bundles as $bundle) {
            $pricePerVote = $bundle['price'] / $bundle['votes'];
            $discount = $bundle['votes'] > 1 ? round((1 - ($pricePerVote / $basePrice)) * 100, 1) : 0;
            $status = $bundle['active'] ? '✅ Active' : '❌ Inactive';
            
            echo "<tr>";
            echo "<td>{$bundle['id']}</td>";
            echo "<td>{$bundle['name']}</td>";
            echo "<td>{$bundle['votes']}</td>";
            echo "<td>\${$bundle['price']}</td>";
            echo "<td>\${$pricePerVote}</td>";
            echo "<td>{$discount}%</td>";
            echo "<td>{$status}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>🔗 Test Links:</h3>";
        echo "<p><a href='/smartcast/events/23/vote/41?category=42' target='_blank'>Vote Form for Event 23</a></p>";
        echo "<p><a href='/smartcast/organizer/financial/bundles' target='_blank'>Manage Bundles</a></p>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
th { background-color: #f5f5f5; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
