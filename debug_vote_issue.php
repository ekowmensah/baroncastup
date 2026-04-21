<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

$db = Database::getInstance();

echo "<h2>Vote Issue Diagnostic</h2>";
echo "<hr>";

// Get all contestants with their vote counts
echo "<h3>1. Contestants and Their Vote Counts</h3>";
$sql = "
    SELECT 
        c.id,
        c.name,
        c.event_id,
        e.name as event_name,
        COUNT(DISTINCT v.id) as vote_records,
        COALESCE(SUM(v.quantity), 0) as total_votes,
        COUNT(DISTINCT v.category_id) as distinct_categories_in_votes,
        GROUP_CONCAT(DISTINCT v.category_id) as category_ids_in_votes
    FROM contestants c
    LEFT JOIN events e ON c.event_id = e.id
    LEFT JOIN votes v ON c.id = v.contestant_id
    WHERE c.active = 1
    GROUP BY c.id
    ORDER BY e.id, c.name
";
$contestants = $db->select($sql);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Event</th><th>Vote Records</th><th>Total Votes</th><th>Categories in Votes</th><th>Category IDs</th></tr>";
foreach ($contestants as $c) {
    echo "<tr>";
    echo "<td>{$c['id']}</td>";
    echo "<td>{$c['name']}</td>";
    echo "<td>{$c['event_name']}</td>";
    echo "<td>{$c['vote_records']}</td>";
    echo "<td>{$c['total_votes']}</td>";
    echo "<td>{$c['distinct_categories_in_votes']}</td>";
    echo "<td>" . ($c['category_ids_in_votes'] ?: 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>2. Contestant Category Assignments</h3>";
$sql = "
    SELECT 
        c.id as contestant_id,
        c.name as contestant_name,
        cc.category_id,
        cat.name as category_name,
        cc.short_code,
        cc.active as assignment_active
    FROM contestants c
    LEFT JOIN contestant_categories cc ON c.id = cc.contestant_id
    LEFT JOIN categories cat ON cc.category_id = cat.id
    WHERE c.active = 1
    ORDER BY c.id, cc.category_id
";
$assignments = $db->select($sql);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Contestant ID</th><th>Contestant Name</th><th>Category ID</th><th>Category Name</th><th>Short Code</th><th>Active</th></tr>";
foreach ($assignments as $a) {
    echo "<tr>";
    echo "<td>{$a['contestant_id']}</td>";
    echo "<td>{$a['contestant_name']}</td>";
    echo "<td>" . ($a['category_id'] ?: 'NULL') . "</td>";
    echo "<td>" . ($a['category_name'] ?: 'N/A') . "</td>";
    echo "<td>" . ($a['short_code'] ?: 'N/A') . "</td>";
    echo "<td>" . ($a['assignment_active'] ?: 'N/A') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>3. Votes with Category Mismatch</h3>";
$sql = "
    SELECT 
        v.id as vote_id,
        v.contestant_id,
        c.name as contestant_name,
        v.category_id as vote_category_id,
        v.quantity,
        v.created_at,
        CASE 
            WHEN cc.id IS NULL THEN 'NO MATCH'
            ELSE 'MATCH'
        END as category_match_status
    FROM votes v
    INNER JOIN contestants c ON v.contestant_id = c.id
    LEFT JOIN contestant_categories cc ON v.contestant_id = cc.contestant_id 
        AND v.category_id = cc.category_id 
        AND cc.active = 1
    WHERE c.active = 1
    ORDER BY v.created_at DESC
    LIMIT 50
";
$votes = $db->select($sql);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Vote ID</th><th>Contestant</th><th>Vote Category ID</th><th>Quantity</th><th>Match Status</th><th>Created</th></tr>";
foreach ($votes as $v) {
    $style = $v['category_match_status'] === 'NO MATCH' ? 'background-color: #ffcccc;' : '';
    echo "<tr style='$style'>";
    echo "<td>{$v['vote_id']}</td>";
    echo "<td>{$v['contestant_name']}</td>";
    echo "<td>" . ($v['vote_category_id'] ?: 'NULL') . "</td>";
    echo "<td>{$v['quantity']}</td>";
    echo "<td><strong>{$v['category_match_status']}</strong></td>";
    echo "<td>{$v['created_at']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>4. Summary by Event</h3>";
$sql = "
    SELECT 
        e.id as event_id,
        e.name as event_name,
        COUNT(DISTINCT c.id) as contestant_count,
        COUNT(DISTINCT v.id) as vote_count,
        COALESCE(SUM(v.quantity), 0) as total_votes,
        COUNT(DISTINCT cat.id) as category_count
    FROM events e
    LEFT JOIN contestants c ON e.id = c.event_id AND c.active = 1
    LEFT JOIN votes v ON c.id = v.contestant_id
    LEFT JOIN categories cat ON e.id = cat.event_id
    GROUP BY e.id
    ORDER BY e.created_at DESC
";
$events = $db->select($sql);

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Event ID</th><th>Event Name</th><th>Contestants</th><th>Vote Records</th><th>Total Votes</th><th>Categories</th></tr>";
foreach ($events as $e) {
    echo "<tr>";
    echo "<td>{$e['event_id']}</td>";
    echo "<td>{$e['event_name']}</td>";
    echo "<td>{$e['contestant_count']}</td>";
    echo "<td>{$e['vote_count']}</td>";
    echo "<td>{$e['total_votes']}</td>";
    echo "<td>{$e['category_count']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<p><strong>Analysis:</strong></p>";
echo "<ul>";
echo "<li>Red rows in section 3 indicate votes that don't match any contestant-category assignment</li>";
echo "<li>If contestants show 0 votes but have vote records, check for category mismatches</li>";
echo "<li>NULL category IDs in votes might cause counting issues</li>";
echo "</ul>";
