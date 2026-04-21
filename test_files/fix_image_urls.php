<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

echo "=== FIXING MALFORMED IMAGE URLS IN DATABASE ===\n\n";

try {
    $db = new \SmartCast\Core\Database();
    
    // Fix events table
    echo "1. Fixing events.featured_image:\n";
    $events = $db->select("SELECT id, name, featured_image FROM events WHERE featured_image LIKE 'http:/%' AND featured_image NOT LIKE 'http://%'");
    
    foreach ($events as $event) {
        $oldUrl = $event['featured_image'];
        $newUrl = str_replace('http:/', 'http://', $oldUrl);
        
        $db->update('events', ['featured_image' => $newUrl], 'id = :id', ['id' => $event['id']]);
        
        echo "   Event #{$event['id']}: {$event['name']}\n";
        echo "     OLD: $oldUrl\n";
        echo "     NEW: $newUrl\n\n";
    }
    
    echo "   Fixed " . count($events) . " event images.\n\n";
    
    // Fix contestants table
    echo "2. Fixing contestants.image_url:\n";
    $contestants = $db->select("SELECT id, name, image_url FROM contestants WHERE image_url LIKE 'http:/%' AND image_url NOT LIKE 'http://%'");
    
    foreach ($contestants as $contestant) {
        $oldUrl = $contestant['image_url'];
        $newUrl = str_replace('http:/', 'http://', $oldUrl);
        
        $db->update('contestants', ['image_url' => $newUrl], 'id = :id', ['id' => $contestant['id']]);
        
        echo "   Contestant #{$contestant['id']}: {$contestant['name']}\n";
        echo "     OLD: $oldUrl\n";
        echo "     NEW: $newUrl\n\n";
    }
    
    echo "   Fixed " . count($contestants) . " contestant images.\n\n";
    
    // Check for other malformed patterns
    echo "3. Checking for other malformed patterns:\n";
    
    // Check for https:/ patterns
    $httpsEvents = $db->select("SELECT id, featured_image FROM events WHERE featured_image LIKE 'https:/%' AND featured_image NOT LIKE 'https://%'");
    $httpsContestants = $db->select("SELECT id, image_url FROM contestants WHERE image_url LIKE 'https:/%' AND image_url NOT LIKE 'https://%'");
    
    if (count($httpsEvents) > 0 || count($httpsContestants) > 0) {
        echo "   Found " . count($httpsEvents) . " events and " . count($httpsContestants) . " contestants with https:/ pattern\n";
        echo "   These will be handled by the updated image_url() function.\n\n";
    } else {
        echo "   No https:/ patterns found.\n\n";
    }
    
    echo "✅ DATABASE FIX COMPLETED!\n\n";
    
    // Test the fixes
    echo "4. Testing fixed URLs:\n";
    $testEvent = $db->selectOne("SELECT id, name, featured_image FROM events WHERE featured_image IS NOT NULL LIMIT 1");
    if ($testEvent) {
        echo "   Test Event: {$testEvent['name']}\n";
        echo "   Raw DB: {$testEvent['featured_image']}\n";
        echo "   image_url(): " . image_url($testEvent['featured_image']) . "\n\n";
    }
    
    $testContestant = $db->selectOne("SELECT id, name, image_url FROM contestants WHERE image_url IS NOT NULL LIMIT 1");
    if ($testContestant) {
        echo "   Test Contestant: {$testContestant['name']}\n";
        echo "   Raw DB: {$testContestant['image_url']}\n";
        echo "   image_url(): " . image_url($testContestant['image_url']) . "\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "=== DONE ===\n";
?>
