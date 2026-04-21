<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

echo "=== CONVERTING FULL URLS TO RELATIVE PATHS ===\n\n";

try {
    $db = new \SmartCast\Core\Database();
    
    // Convert events table
    echo "1. Converting events.featured_image:\n";
    $events = $db->select("SELECT id, name, featured_image FROM events WHERE featured_image LIKE 'http%'");
    
    foreach ($events as $event) {
        $oldUrl = $event['featured_image'];
        
        // Extract relative path from full URL
        $relativePath = str_replace(APP_URL . '/', '', $oldUrl);
        
        $db->update('events', ['featured_image' => $relativePath], 'id = :id', ['id' => $event['id']]);
        
        echo "   Event #{$event['id']}: {$event['name']}\n";
        echo "     OLD: $oldUrl\n";
        echo "     NEW: $relativePath\n";
        echo "     FINAL URL: " . image_url($relativePath) . "\n\n";
    }
    
    echo "   Converted " . count($events) . " event images.\n\n";
    
    // Convert contestants table
    echo "2. Converting contestants.image_url:\n";
    $contestants = $db->select("SELECT id, name, image_url FROM contestants WHERE image_url LIKE 'http%'");
    
    foreach ($contestants as $contestant) {
        $oldUrl = $contestant['image_url'];
        
        // Extract relative path from full URL
        $relativePath = str_replace(APP_URL . '/', '', $oldUrl);
        
        $db->update('contestants', ['image_url' => $relativePath], 'id = :id', ['id' => $contestant['id']]);
        
        echo "   Contestant #{$contestant['id']}: {$contestant['name']}\n";
        echo "     OLD: $oldUrl\n";
        echo "     NEW: $relativePath\n";
        echo "     FINAL URL: " . image_url($relativePath) . "\n\n";
    }
    
    echo "   Converted " . count($contestants) . " contestant images.\n\n";
    
    echo "✅ CONVERSION COMPLETED!\n\n";
    
    // Test the conversions
    echo "3. Testing converted paths:\n";
    $testEvent = $db->selectOne("SELECT id, name, featured_image FROM events WHERE featured_image IS NOT NULL LIMIT 1");
    if ($testEvent) {
        echo "   Test Event: {$testEvent['name']}\n";
        echo "   DB Path: {$testEvent['featured_image']}\n";
        echo "   Final URL: " . image_url($testEvent['featured_image']) . "\n";
        echo "   File exists: " . (file_exists(__DIR__ . '/' . $testEvent['featured_image']) ? 'YES' : 'NO') . "\n\n";
    }
    
    $testContestant = $db->selectOne("SELECT id, name, image_url FROM contestants WHERE image_url IS NOT NULL LIMIT 1");
    if ($testContestant) {
        echo "   Test Contestant: {$testContestant['name']}\n";
        echo "   DB Path: {$testContestant['image_url']}\n";
        echo "   Final URL: " . image_url($testContestant['image_url']) . "\n";
        echo "   File exists: " . (file_exists(__DIR__ . '/' . $testContestant['image_url']) ? 'YES' : 'NO') . "\n\n";
    }
    
    echo "🎯 BENEFITS OF THIS CHANGE:\n";
    echo "   ✅ Portable - Works on any domain\n";
    echo "   ✅ Maintainable - Easy to change base URL\n";
    echo "   ✅ Consistent - All paths are relative\n";
    echo "   ✅ Efficient - Smaller database storage\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "=== DONE ===\n";
?>
