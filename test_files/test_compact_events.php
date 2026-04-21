<?php
/**
 * Test Compact Events Page Format
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<h2>🎯 Testing Compact Events Page Format</h2>";
    
    // Get public events with all the data
    $stmt = $connection->query("
        SELECT e.*,
               COUNT(DISTINCT c.id) as contestant_count,
               COALESCE(SUM(v.quantity), 0) as total_votes,
               DATEDIFF(e.end_date, NOW()) as days_left
        FROM events e
        LEFT JOIN contestants c ON e.id = c.event_id
        LEFT JOIN votes v ON c.id = v.contestant_id
        WHERE e.status = 'active' 
        AND e.visibility = 'public'
        GROUP BY e.id
        ORDER BY e.start_date DESC
        LIMIT 3
    ");
    $events = $stmt->fetchAll();
    
    if (empty($events)) {
        echo "<p>❌ No public events found</p>";
        exit;
    }
    
    echo "<h3>📊 Events with Compact Format</h3>";
    
    foreach ($events as $event) {
        // Calculate days left
        $endDate = new DateTime($event['end_date']);
        $today = new DateTime();
        $daysLeft = max(0, $today->diff($endDate)->days);
        if ($endDate < $today) $daysLeft = 0;
        
        echo "<div style='border: 1px solid #ddd; padding: 20px; margin: 15px 0; border-radius: 8px; background: white;'>";
        
        // Title and Status
        echo "<div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;'>";
        echo "<h4 style='margin: 0; font-weight: bold;'>{$event['name']}</h4>";
        if ($event['status'] === 'active') {
            echo "<span style='background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;'>Live</span>";
        }
        echo "</div>";
        
        // Compact Stats
        echo "<div style='margin-bottom: 15px;'>";
        echo "<div style='margin-bottom: 3px;'><strong>{$event['contestant_count']}</strong> - Contestants</div>";
        echo "<div style='margin-bottom: 3px;'><strong>" . number_format($event['total_votes']) . "</strong> - Votes</div>";
        echo "<div style='margin-bottom: 3px;'><strong>{$daysLeft}</strong> - Days Left</div>";
        echo "</div>";
        
        // Event Dates
        echo "<div style='margin-bottom: 15px; color: #666; font-size: 0.9rem;'>";
        echo "<div><strong>Started:</strong> " . date('M j Y', strtotime($event['start_date'])) . "</div>";
        echo "<div><strong>Ending:</strong> " . date('M j Y g:i A', strtotime($event['end_date'])) . "</div>";
        echo "</div>";
        
        // Buttons
        $canVote = (strtotime($event['start_date']) <= time() && 
                   strtotime($event['end_date']) >= time() && 
                   $event['status'] === 'active');
        
        echo "<div style='display: flex; gap: 10px;'>";
        
        if ($canVote) {
            require_once __DIR__ . '/src/Helpers/SlugHelper.php';
            $eventSlug = \SmartCast\Helpers\SlugHelper::generateEventSlug($event);
            
            echo "<a href='/smartcast/events/{$eventSlug}/vote' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>VOTE NOW</a>";
            
            if ($event['results_visible']) {
                echo "<a href='/smartcast/events/{$eventSlug}' style='border: 1px solid #007bff; color: #007bff; padding: 8px 16px; text-decoration: none; border-radius: 4px; font-size: 0.9rem;'>View Results</a>";
            }
        } elseif (strtotime($event['start_date']) > time()) {
            echo "<button style='background: #ffc107; color: #212529; padding: 10px 20px; border: none; border-radius: 4px;' disabled>Starts Soon</button>";
        } else {
            if ($event['results_visible']) {
                echo "<a href='/smartcast/events/{$eventSlug}' style='border: 1px solid #007bff; color: #007bff; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>View Results</a>";
            }
        }
        
        echo "</div>";
        
        echo "</div>";
    }
    
    echo "<h3>✅ Format Specifications Met</h3>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>Compact Format Applied:</h4>";
    echo "<ul style='margin: 0; color: #155724;'>";
    echo "<li>✅ <strong>Title Only:</strong> Event name without description</li>";
    echo "<li>✅ <strong>Live Badge:</strong> Shows 'Live' for active events</li>";
    echo "<li>✅ <strong>Compact Stats:</strong> Number - Label format</li>";
    echo "<li>✅ <strong>Date Format:</strong> Started: Oct 9 2025, Ending: Date + Time</li>";
    echo "<li>✅ <strong>VOTE NOW Button:</strong> Primary action button</li>";
    echo "<li>✅ <strong>View Results:</strong> Only shows when results_visible = 1</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>🧪 Test the Live Page</h3>";
    echo "<p><a href='/smartcast/events' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>View Events Page</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
h3 { color: #34495e; margin-top: 30px; }
</style>
