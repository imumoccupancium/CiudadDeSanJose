<?php
require_once __DIR__ . '/config/database.php';
$stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'role'");
$stmt->execute();
print_r($stmt->fetch());
?>
