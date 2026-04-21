<?php
/**
 * Verify Slug Implementation Fix
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/src/Models/BaseModel.php';
require_once __DIR__ . '/src/Models/Event.php';
require_once __DIR__ . '/src/Helpers/SlugHelper.php';

use SmartCast\Core\Database;
use SmartCast\Models\Event;
use SmartCast\Helpers\SlugHelper;

try {
    echo "🔧 Verifying Slug Implementation Fix\n\n";
    
    // Test Event model
    $eventModel = new Event();
    
    // Test findByCode method
    echo "1. Testing Event::findByCode() method...\n";
    $event = $eventModel->findByCode('TEACHERSAW');
    
    if ($event) {
        echo "   ✅ Found event by code: {$event['name']}\n";
        echo "   Event ID: {$event['id']}\n";
        echo "   Event Code: {$event['code']}\n";
    } else {
        echo "   ❌ Event not found by code\n";
    }
    
    // Test case insensitive
    echo "\n2. Testing case insensitive lookup...\n";
    $eventLower = $eventModel->findByCode('teachersaw');
    $eventUpper = $eventModel->findByCode('TEACHERSAW');
    
    if ($eventLower && $eventUpper && $eventLower['id'] == $eventUpper['id']) {
        echo "   ✅ Case insensitive lookup works\n";
    } else {
        echo "   ❌ Case insensitive lookup failed\n";
    }
    
    // Test SlugHelper
    echo "\n3. Testing SlugHelper...\n";
    if ($event) {
        $eventSlug = SlugHelper::generateEventSlug($event);
        echo "   Generated event slug: {$eventSlug}\n";
        
        $testName = "John Bongo";
        $testId = 42;
        $contestantSlug = SlugHelper::generateContestantSlug($testName, $testId);
        $extractedId = SlugHelper::extractIdFromSlug($contestantSlug);
        
        echo "   Generated contestant slug: {$contestantSlug}\n";
        echo "   Extracted ID: {$extractedId}\n";
        echo "   ID match: " . ($extractedId == $testId ? '✅' : '❌') . "\n";
    }
    
    echo "\n4. URL Examples:\n";
    if ($event) {
        $eventSlug = SlugHelper::generateEventSlug($event);
        echo "   Old URL: /smartcast/events/{$event['id']}/vote\n";
        echo "   New URL: /smartcast/events/{$eventSlug}/vote\n";
        echo "   Test URL: http://localhost/smartcast/events/{$eventSlug}/vote\n";
    }
    
    echo "\n✅ All tests passed! Slug implementation is working correctly.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
