<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

$db = Database::getInstance();
$connection = $db->getConnection();

$stmt = $connection->prepare('SELECT id, name, results_visible FROM events WHERE id = 24');
$stmt->execute();
$event = $stmt->fetch();

echo "Event 24 Status:\n";
echo "Name: " . $event['name'] . "\n";
echo "results_visible: " . ($event['results_visible'] ? 'TRUE (1)' : 'FALSE (0)') . "\n";
echo "Raw value: " . $event['results_visible'] . "\n";
?>
