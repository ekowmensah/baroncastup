<?php
/**
 * Debug Specific Bundle Transaction Issue
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "🔍 Debug Bundle Vote Issue\n\n";
    
    // Find the most recent transaction that should have 10 votes but recorded 8
    echo "1. Finding problematic transaction...\n";
    $stmt = $connection->query("
        SELECT 
            t.*,
            c.name as contestant_name,
            vb.name as bundle_name,
            vb.votes as bundle_votes,
            vb.price as bundle_price,
            v.quantity as actual_votes_recorded,
            e.vote_price as event_vote_price
        FROM transactions t
        LEFT JOIN contestants c ON t.contestant_id = c.id
        LEFT JOIN vote_bundles vb ON t.bundle_id = vb.id
        LEFT JOIN votes v ON t.id = v.transaction_id
        LEFT JOIN events e ON t.event_id = e.id
        WHERE t.status = 'success' AND t.bundle_id IS NOT NULL
        ORDER BY t.created_at DESC
        LIMIT 5
    ");
    $transactions = $stmt->fetchAll();
    
    foreach ($transactions as $trans) {
        echo "Transaction ID: {$trans['id']}\n";
        echo "  Contestant: {$trans['contestant_name']}\n";
        echo "  Bundle: {$trans['bundle_name']} (ID: {$trans['bundle_id']})\n";
        echo "  Expected votes: {$trans['bundle_votes']}\n";
        echo "  Actual votes: {$trans['actual_votes_recorded']}\n";
        echo "  Amount paid: \${$trans['amount']}\n";
        echo "  Bundle price: \${$trans['bundle_price']}\n";
        echo "  Event vote price: \${$trans['event_vote_price']}\n";
        
        // Simulate the calculation
        $calculatedVotes = (int) ($trans['amount'] / $trans['event_vote_price']);
        echo "  Calculated votes (amount/vote_price): {$calculatedVotes}\n";
        
        if ($trans['bundle_votes'] != $trans['actual_votes_recorded']) {
            echo "  ❌ ISSUE FOUND!\n";
            
            // Check if it's a calculation vs bundle mismatch
            if ($calculatedVotes != $trans['bundle_votes']) {
                echo "  Problem: Calculated votes ({$calculatedVotes}) != Bundle votes ({$trans['bundle_votes']})\n";
                echo "  System used calculated votes instead of bundle votes\n";
                
                // Check for precision issues
                $exactCalculation = $trans['amount'] / $trans['event_vote_price'];
                echo "  Exact calculation: {$exactCalculation}\n";
                echo "  Rounded down to: {$calculatedVotes}\n";
                
                if ($exactCalculation > $calculatedVotes && $exactCalculation < ($calculatedVotes + 1)) {
                    echo "  ⚠️ ROUNDING ISSUE: {$exactCalculation} rounded down to {$calculatedVotes}\n";
                }
            }
        } else {
            echo "  ✅ Correct\n";
        }
        echo "\n";
    }
    
    // Check for any bundle price inconsistencies
    echo "2. Checking bundle price consistency...\n";
    $stmt = $connection->query("
        SELECT 
            vb.*,
            e.vote_price,
            (vb.price / vb.votes) as calculated_price_per_vote,
            ABS((vb.price / vb.votes) - e.vote_price) as price_difference
        FROM vote_bundles vb
        INNER JOIN events e ON vb.event_id = e.id
        WHERE vb.active = 1
        ORDER BY price_difference DESC
        LIMIT 10
    ");
    $bundles = $stmt->fetchAll();
    
    foreach ($bundles as $bundle) {
        echo "Bundle: {$bundle['name']} ({$bundle['votes']} votes)\n";
        echo "  Bundle price: \${$bundle['price']}\n";
        echo "  Price per vote: \${$bundle['calculated_price_per_vote']}\n";
        echo "  Event vote price: \${$bundle['vote_price']}\n";
        echo "  Difference: \${$bundle['price_difference']}\n";
        
        if ($bundle['price_difference'] > 0.01) {
            echo "  ⚠️ PRICE MISMATCH!\n";
        }
        echo "\n";
    }
    
    echo "3. Recommendations:\n";
    echo "   - Check if bundle prices are calculated correctly\n";
    echo "   - Verify event vote price is consistent\n";
    echo "   - Consider using bundle votes directly instead of calculation\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
