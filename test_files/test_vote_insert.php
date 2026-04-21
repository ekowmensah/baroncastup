<?php
/**
 * Test Direct Vote Insertion
 * This bypasses all application logic to test direct database insertion
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<h2>🧪 Testing Direct Vote Insertion</h2>";
    
    // Test inserting a vote with quantity 150 directly
    $testData = [
        'transaction_id' => 999999, // Fake transaction ID for testing
        'tenant_id' => 2,
        'event_id' => 23,
        'contestant_id' => 41,
        'category_id' => 42,
        'quantity' => 150
    ];
    
    echo "<h3>📝 Test Data:</h3>";
    echo "<pre>" . print_r($testData, true) . "</pre>";
    
    // Direct SQL insertion
    $sql = "INSERT INTO votes (transaction_id, tenant_id, event_id, contestant_id, category_id, quantity, created_at, updated_at) 
            VALUES (:transaction_id, :tenant_id, :event_id, :contestant_id, :category_id, :quantity, NOW(), NOW())";
    
    $stmt = $connection->prepare($sql);
    $result = $stmt->execute($testData);
    
    if ($result) {
        $voteId = $connection->lastInsertId();
        echo "<h3>✅ Direct insertion successful!</h3>";
        echo "<p><strong>Vote ID:</strong> $voteId</p>";
        
        // Verify what was actually inserted
        $stmt = $connection->prepare("SELECT * FROM votes WHERE id = ?");
        $stmt->execute([$voteId]);
        $insertedVote = $stmt->fetch();
        
        echo "<h3>📊 Inserted Vote Data:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        foreach ($insertedVote as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
        
        if ($insertedVote['quantity'] == 150) {
            echo "<h3>🎉 SUCCESS: Quantity 150 was inserted correctly!</h3>";
            echo "<p>✅ Database can handle large quantities</p>";
            echo "<p>❌ The issue is in the application logic, not the database</p>";
        } else {
            echo "<h3>❌ PROBLEM: Quantity was changed during insertion</h3>";
            echo "<p>Expected: 150, Got: {$insertedVote['quantity']}</p>";
        }
        
        // Clean up test data
        $connection->prepare("DELETE FROM votes WHERE id = ?")->execute([$voteId]);
        echo "<p><small>Test vote cleaned up</small></p>";
        
    } else {
        echo "<h3>❌ Direct insertion failed</h3>";
        $errorInfo = $stmt->errorInfo();
        echo "<p>Error: " . $errorInfo[2] . "</p>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px 12px; border: 1px solid #ddd; }
th { background-color: #f5f5f5; }
pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
</style>
