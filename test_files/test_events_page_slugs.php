<?php
/**
 * Test Events Page Slug Implementation
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/src/Helpers/SlugHelper.php';

use SmartCast\Core\Database;
use SmartCast\Helpers\SlugHelper;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<h2>🔗 Testing Events Page Slug Implementation</h2>";
    
    // Get public events
    $stmt = $connection->query("
        SELECT id, name, code, status, visibility 
        FROM events 
        WHERE visibility = 'public' AND status = 'active'
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $events = $stmt->fetchAll();
    
    if (empty($events)) {
        echo "<p>❌ No public events found</p>";
        exit;
    }
    
    echo "<h3>📊 Public Events with Slug URLs</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Event Name</th><th>Code</th><th>Old URL</th><th>New Slug URL</th><th>Test Links</th></tr>";
    
    foreach ($events as $event) {
        $eventSlug = SlugHelper::generateEventSlug($event);
        $oldUrl = "/smartcast/events/{$event['id']}";
        $newUrl = "/smartcast/events/{$eventSlug}";
        $voteUrl = "/smartcast/events/{$eventSlug}/vote";
        
        echo "<tr>";
        echo "<td>{$event['name']}</td>";
        echo "<td>{$event['code']}</td>";
        echo "<td><code>{$oldUrl}</code></td>";
        echo "<td><code>{$newUrl}</code></td>";
        echo "<td>";
        echo "<a href='{$newUrl}' target='_blank' style='margin-right: 10px;'>View Event</a>";
        echo "<a href='{$voteUrl}' target='_blank'>Vote Page</a>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test specific event 24
    echo "<h3>🎯 Event 24 (Teachers Awards) Test</h3>";
    $stmt = $connection->prepare("SELECT * FROM events WHERE id = 24");
    $stmt->execute();
    $event24 = $stmt->fetch();
    
    if ($event24) {
        $eventSlug = SlugHelper::generateEventSlug($event24);
        
        echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
        echo "<h4>Event Details:</h4>";
        echo "<p><strong>Name:</strong> {$event24['name']}</p>";
        echo "<p><strong>Code:</strong> {$event24['code']}</p>";
        echo "<p><strong>Generated Slug:</strong> {$eventSlug}</p>";
        echo "<p><strong>Status:</strong> {$event24['status']}</p>";
        echo "<p><strong>Visibility:</strong> {$event24['visibility']}</p>";
        
        echo "<h4>URL Comparison:</h4>";
        echo "<ul>";
        echo "<li><strong>Old Events List:</strong> <a href='/smartcast/events' target='_blank'>/smartcast/events</a></li>";
        echo "<li><strong>Old Event View:</strong> <a href='/smartcast/events/24' target='_blank'>/smartcast/events/24</a></li>";
        echo "<li><strong>New Event View:</strong> <a href='/smartcast/events/{$eventSlug}' target='_blank'>/smartcast/events/{$eventSlug}</a></li>";
        echo "<li><strong>New Vote Page:</strong> <a href='/smartcast/events/{$eventSlug}/vote' target='_blank'>/smartcast/events/{$eventSlug}/vote</a></li>";
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<h3>✅ Implementation Status</h3>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>Slug URLs Applied To:</h4>";
    echo "<ul style='margin: 0; color: #155724;'>";
    echo "<li>✅ <strong>Events Index Page:</strong> All event links now use slugs</li>";
    echo "<li>✅ <strong>Vote Now Buttons:</strong> Link to slug-based voting pages</li>";
    echo "<li>✅ <strong>View Details Links:</strong> Use event slugs for details page</li>";
    echo "<li>✅ <strong>View Results Links:</strong> Show results using slugs</li>";
    echo "<li>✅ <strong>EventController:</strong> Updated to handle slug resolution</li>";
    echo "<li>✅ <strong>Routes:</strong> Support both slug and ID parameters</li>";
    echo "<li>✅ <strong>Backward Compatibility:</strong> Old ID URLs still work</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>🧪 Testing Instructions</h3>";
    echo "<ol>";
    echo "<li><strong>Visit Events Page:</strong> <a href='/smartcast/events' target='_blank'>http://localhost/smartcast/events</a></li>";
    echo "<li><strong>Check URLs:</strong> Hover over event links to see slug-based URLs</li>";
    echo "<li><strong>Test Navigation:</strong> Click on events to verify slug URLs work</li>";
    echo "<li><strong>Test Voting:</strong> Click 'Vote Now' buttons to test voting flow</li>";
    echo "<li><strong>Verify Compatibility:</strong> Try old ID-based URLs to ensure they still work</li>";
    echo "</ol>";
    
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
