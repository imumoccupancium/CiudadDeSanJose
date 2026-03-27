<?php
require_once 'config/database.php';
$stmt = $pdo->query("DESCRIBE visitor_logs");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
