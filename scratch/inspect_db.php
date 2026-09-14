<?php
require_once __DIR__ . '/../classes/DatabaseTeachingReport.php';
$db = new \App\DatabaseTeachingReport();
$pdo = $db->getPDO();

$stmt = $pdo->query("DESCRIBE subjects");
while ($row = $stmt->fetch()) {
    echo "Field: {$row['Field']} | Type: {$row['Type']} \n";
}
