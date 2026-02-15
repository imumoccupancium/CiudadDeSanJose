<?php
require_once __DIR__ . '/config/database.php';
$stmt = $pdo->query("SELECT username, role FROM users");
print_r($stmt->fetchAll());
?>
