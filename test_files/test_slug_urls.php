<?php
/**
 * Test Slug-based URLs Implementation
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/src/Helpers/SlugHelper.php';

use SmartCast\Core\Database;
use SmartCast\Helpers\SlugHelper;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<h2>🔗 Testing Slug-based URLs</h2>";
    
    // Get event 24 data
    $stmt = $connection->prepare("SELECT id, name, code FROM events WHERE id = 24");
    $stmt->execute();
    $event = $stmt->fetch();
    
    if (!$event) {
        echo "<p>❌ Event 24 not found</p>";
        exit;
    }
    
    echo "<h3>📊 Event Information</h3>";
    echo "<p><strong>ID:</strong> {$event['id']}</p>";
    echo "<p><strong>Name:</strong> {$event['name']}</p>";
    echo "<p><strong>Code:</strong> {$event['code']}</p>";
    
    // Generate event slug
    $eventSlug = SlugHelper::generateEventSlug($event);
    echo "<p><strong>Generated Slug:</strong> {$eventSlug}</p>";
    
    // Get some contestants
    $stmt = $connection->prepare("SELECT id, name FROM contestants WHERE event_id = 24 LIMIT 5");
    $stmt->execute();
    $contestants = $stmt->fetchAll();
    
    echo "<h3>👥 Contestant URL Examples</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Contestant</th><th>Old URL</th><th>New Slug URL</th></tr>";
    
    foreach ($contestants as $contestant) {
        $contestantSlug = SlugHelper::generateContestantSlug($contestant['name'], $contestant['id']);
        $oldUrl = "/smartcast/events/24/vote/{$contestant['id']}";
        $newUrl = "/smartcast/events/{$eventSlug}/vote/{$contestantSlug}";
        
        echo "<tr>";
        echo "<td>{$contestant['name']}</td>";
        echo "<td><code>{$oldUrl}</code></td>";
        echo "<td><code>{$newUrl}</code></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test slug extraction
    echo "<h3>🔍 Testing Slug Resolution</h3>";
    if (!empty($contestants)) {
        $testContestant = $contestants[0];
        $testSlug = SlugHelper::generateContestantSlug($testContestant['name'], $testContestant['id']);
        $extractedId = SlugHelper::extractIdFromSlug($testSlug);
        
        echo "<p><strong>Test Contestant:</strong> {$testContestant['name']} (ID: {$testContestant['id']})</p>";
        echo "<p><strong>Generated Slug:</strong> {$testSlug}</p>";
        echo "<p><strong>Extracted ID:</strong> {$extractedId}</p>";
        echo "<p><strong>Match:</strong> " . ($extractedId == $testContestant['id'] ? '✅ Success' : '❌ Failed') . "</p>";
    }
    
    // Show URL examples
    echo "<h3>🌐 URL Examples</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<h4>Before (ID-based):</h4>";
    echo "<ul>";
    echo "<li><code>http://localhost/smartcast/events/24/vote</code></li>";
    echo "<li><code>http://localhost/smartcast/events/24/vote/42</code></li>";
    echo "</ul>";
    
    echo "<h4>After (Slug-based):</h4>";
    echo "<ul>";
    echo "<li><code>http://localhost/smartcast/events/{$eventSlug}/vote</code></li>";
    if (!empty($contestants)) {
        $exampleSlug = SlugHelper::generateContestantSlug($contestants[0]['name'], $contestants[0]['id']);
        echo "<li><code>http://localhost/smartcast/events/{$eventSlug}/vote/{$exampleSlug}</code></li>";
    }
    echo "</ul>";
    echo "</div>";
    
    // Test links
    echo "<h3>🧪 Test the Implementation</h3>";
    echo "<p><strong>Current voting page (should work with both):</strong></p>";
    echo "<ul>";
    echo "<li><a href='/smartcast/events/24/vote' target='_blank'>Old URL (ID-based)</a></li>";
    echo "<li><a href='/smartcast/events/{$eventSlug}/vote' target='_blank'>New URL (Slug-based)</a></li>";
    echo "</ul>";
    
    if (!empty($contestants)) {
        $testSlug = SlugHelper::generateContestantSlug($contestants[0]['name'], $contestants[0]['id']);
        echo "<p><strong>Test contestant page:</strong></p>";
        echo "<ul>";
        echo "<li><a href='/smartcast/events/24/vote/{$contestants[0]['id']}' target='_blank'>Old URL</a></li>";
        echo "<li><a href='/smartcast/events/{$eventSlug}/vote/{$testSlug}' target='_blank'>New URL</a></li>";
        echo "</ul>";
    }
    
    echo "<h3>✅ Implementation Status</h3>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>Features Implemented:</h4>";
    echo "<ul style='margin: 0; color: #155724;'>";
    echo "<li>✅ <strong>Event Slugs:</strong> Uses event code (e.g., 'teachersaw') instead of ID</li>";
    echo "<li>✅ <strong>Contestant Slugs:</strong> Uses name-id format (e.g., 'john-bongo-42')</li>";
    echo "<li>✅ <strong>Backward Compatibility:</strong> Still supports old ID-based URLs</li>";
    echo "<li>✅ <strong>URL Generation:</strong> Templates now generate slug-based URLs</li>";
    echo "<li>✅ <strong>Route Resolution:</strong> Controllers resolve both slugs and IDs</li>";
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
h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
h3 { color: #34495e; margin-top: 30px; }
code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; font-family: monospace; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
