<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

use SmartCast\Core\Database;

$db = Database::getInstance();
$connection = $db->getConnection();

echo "Leaderboard Cache Table Structure:\n";
$stmt = $connection->query('SHOW CREATE TABLE leaderboard_cache');
$result = $stmt->fetch();
echo $result['Create Table'] . "\n";
?>
