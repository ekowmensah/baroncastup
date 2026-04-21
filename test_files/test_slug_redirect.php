<?php
/**
 * Test Slug URL Redirect
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/src/Helpers/SlugHelper.php';

use SmartCast\Core\Database;
use SmartCast\Helpers\SlugHelper;

$db = Database::getInstance();
$connection = $db->getConnection();

// Get event 24
$stmt = $connection->prepare("SELECT * FROM events WHERE id = 24");
$stmt->execute();
$event = $stmt->fetch();

if ($event) {
    $eventSlug = SlugHelper::generateEventSlug($event);
    
    echo "<h2>🔗 URL Redirect Test</h2>";
    echo "<p><strong>Event:</strong> {$event['name']}</p>";
    echo "<p><strong>Event Code:</strong> {$event['code']}</p>";
    echo "<p><strong>Generated Slug:</strong> {$eventSlug}</p>";
    
    echo "<h3>Test URLs:</h3>";
    echo "<ul>";
    echo "<li><a href='/smartcast/events/24/vote'>Old URL: /smartcast/events/24/vote</a></li>";
    echo "<li><a href='/smartcast/events/{$eventSlug}/vote'>New URL: /smartcast/events/{$eventSlug}/vote</a></li>";
    echo "</ul>";
    
    echo "<h3>Expected Behavior:</h3>";
    echo "<p>Both URLs should work and show the same voting page with slug-based links.</p>";
    
    // Create a redirect for old URLs
    echo "<h3>🔄 Auto-redirect Test</h3>";
    echo "<p>Click below to test automatic redirect from ID to slug:</p>";
    echo "<a href='/smartcast/events/24/vote' class='btn'>Test Old URL</a>";
    
    echo "<script>";
    echo "// Check if we're on an old URL and redirect";
    echo "if (window.location.pathname === '/smartcast/events/24/vote') {";
    echo "  console.log('Redirecting to slug URL...');";
    echo "  window.location.replace('/smartcast/events/{$eventSlug}/vote');";
    echo "}";
    echo "</script>";
} else {
    echo "<p>Event 24 not found</p>";
}
?>

<style>
.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}
.btn:hover {
    background: #0056b3;
}
</style>
