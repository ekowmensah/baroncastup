<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

echo "=== FINAL CLEANUP OF MALFORMED URLS ===\n\n";

try {
    $db = new \SmartCast\Core\Database();
    
    // Clean up contestants with malformed URLs
    echo "1. Cleaning up contestants:\n";
    $malformedContestants = $db->select("SELECT id, name, image_url FROM contestants WHERE image_url LIKE 'http%'");
    
    foreach ($malformedContestants as $contestant) {
        $oldUrl = $contestant['image_url'];
        
        // Fix malformed http:/ to http://
        $fixedUrl = str_replace('http:/', 'http://', $oldUrl);
        
        // Convert to relative path
        $relativePath = str_replace(APP_URL . '/', '', $fixedUrl);
        
        $db->update('contestants', ['image_url' => $relativePath], 'id = :id', ['id' => $contestant['id']]);
        
        echo "   Contestant #{$contestant['id']}: {$contestant['name']}\n";
        echo "     OLD: $oldUrl\n";
        echo "     NEW: $relativePath\n\n";
    }
    
    echo "   Cleaned " . count($malformedContestants) . " contestant URLs.\n\n";
    
    // Clean up events with malformed URLs
    echo "2. Cleaning up events:\n";
    $malformedEvents = $db->select("SELECT id, name, featured_image FROM events WHERE featured_image LIKE 'http%'");
    
    foreach ($malformedEvents as $event) {
        $oldUrl = $event['featured_image'];
        
        // Fix malformed http:/ to http://
        $fixedUrl = str_replace('http:/', 'http://', $oldUrl);
        
        // Convert to relative path
        $relativePath = str_replace(APP_URL . '/', '', $fixedUrl);
        
        $db->update('events', ['featured_image' => $relativePath], 'id = :id', ['id' => $event['id']]);
        
        echo "   Event #{$event['id']}: {$event['name']}\n";
        echo "     OLD: $oldUrl\n";
        echo "     NEW: $relativePath\n\n";
    }
    
    echo "   Cleaned " . count($malformedEvents) . " event URLs.\n\n";
    
    echo "✅ CLEANUP COMPLETED!\n\n";
    
    // Final verification
    echo "3. Final Verification:\n";
    $remainingMalformed = $db->select("SELECT 'contestants' as table_name, COUNT(*) as count FROM contestants WHERE image_url LIKE 'http%' UNION SELECT 'events' as table_name, COUNT(*) as count FROM events WHERE featured_image LIKE 'http%'");
    
    foreach ($remainingMalformed as $result) {
        echo "   {$result['table_name']}: {$result['count']} remaining malformed URLs\n";
    }
    
    if (array_sum(array_column($remainingMalformed, 'count')) == 0) {
        echo "\n🎉 ALL MALFORMED URLS CLEANED UP!\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";
?>
