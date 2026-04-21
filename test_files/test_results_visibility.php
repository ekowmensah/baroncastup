<?php
/**
 * Test Results Visibility Implementation
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<h2>🔍 Testing Results Visibility Implementation</h2>";
    
    // Check event 24 status
    echo "<h3>1. 📊 Event 24 Current Status</h3>";
    $stmt = $connection->prepare("SELECT id, name, results_visible FROM events WHERE id = 24");
    $stmt->execute();
    $event = $stmt->fetch();
    
    if ($event) {
        $status = $event['results_visible'] ? '✅ Visible' : '❌ Hidden';
        echo "<p><strong>Event:</strong> {$event['name']}</p>";
        echo "<p><strong>Results Status:</strong> {$status}</p>";
        
        // Show what the voting page will display
        echo "<h4>Voting Page Display:</h4>";
        if ($event['results_visible']) {
            echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
            echo "<span style='color: #155724;'><i class='fas fa-eye'></i> <strong>Results Visible</strong></span><br>";
            echo "Vote counts will be shown on contestant cards";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
            echo "<span style='color: #721c24;'><i class='fas fa-eye-slash'></i> <strong>Results Hidden</strong></span><br>";
            echo "Vote counts will show 'Results Hidden' instead of numbers";
            echo "</div>";
        }
    } else {
        echo "<p>❌ Event 24 not found</p>";
    }
    
    // Show all events and their visibility status
    echo "<h3>2. 📋 All Events Visibility Status</h3>";
    $stmt = $connection->query("
        SELECT id, name, results_visible, status, active
        FROM events 
        ORDER BY id DESC 
        LIMIT 10
    ");
    $events = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Event Name</th><th>Status</th><th>Active</th><th>Results Visible</th><th>Action</th></tr>";
    
    foreach ($events as $evt) {
        $statusBadge = $evt['status'] == 'active' ? '🟢' : '🟡';
        $activeBadge = $evt['active'] ? '✅' : '❌';
        $resultsBadge = $evt['results_visible'] ? '👁️ Visible' : '🚫 Hidden';
        $toggleAction = $evt['results_visible'] ? 'Hide' : 'Show';
        
        echo "<tr>";
        echo "<td>{$evt['id']}</td>";
        echo "<td>{$evt['name']}</td>";
        echo "<td>{$statusBadge} {$evt['status']}</td>";
        echo "<td>{$activeBadge}</td>";
        echo "<td>{$resultsBadge}</td>";
        echo "<td><a href='/smartcast/organizer/events/{$evt['id']}/toggle-results' onclick='return confirm(\"Toggle results visibility?\")'>$toggleAction Results</a></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test the toggle functionality
    echo "<h3>3. 🔧 Toggle Functionality Test</h3>";
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px;'>";
    echo "<h4>How to Test:</h4>";
    echo "<ol>";
    echo "<li><strong>Visit Organizer Dashboard:</strong> <a href='/smartcast/organizer/events/24' target='_blank'>Event 24 Management</a></li>";
    echo "<li><strong>Click 'Toggle Results' button</strong> to hide/show results</li>";
    echo "<li><strong>Visit Voting Page:</strong> <a href='/smartcast/events/24/vote' target='_blank'>Event 24 Voting</a></li>";
    echo "<li><strong>Check if vote counts are hidden/shown</strong> on contestant cards</li>";
    echo "</ol>";
    
    echo "<h4>Expected Behavior:</h4>";
    echo "<ul>";
    echo "<li><strong>Results Visible:</strong> Vote counts show actual numbers</li>";
    echo "<li><strong>Results Hidden:</strong> Vote counts show 'Results Hidden'</li>";
    echo "<li><strong>Header Badge:</strong> Shows current visibility status</li>";
    echo "</ul>";
    echo "</div>";
    
    // Show implementation summary
    echo "<h3>4. ✅ Implementation Summary</h3>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>Features Implemented:</h4>";
    echo "<ul style='margin: 0; color: #155724;'>";
    echo "<li>✅ <strong>Database Integration:</strong> Uses existing results_visible field</li>";
    echo "<li>✅ <strong>Voting Page:</strong> Conditionally shows/hides vote counts</li>";
    echo "<li>✅ <strong>Visual Indicator:</strong> Badge shows current status</li>";
    echo "<li>✅ <strong>Organizer Control:</strong> Toggle button in event management</li>";
    echo "<li>✅ <strong>Route & Controller:</strong> /events/{id}/toggle-results endpoint</li>";
    echo "<li>✅ <strong>Security:</strong> Tenant ownership verification</li>";
    echo "<li>✅ <strong>Activity Logging:</strong> Changes are logged for audit</li>";
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
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
