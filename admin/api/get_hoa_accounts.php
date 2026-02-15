<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

try {
    $stmt = $pdo->prepare("SELECT id, username, name, email, status, last_login FROM users WHERE role = 'hoa' ORDER BY name ASC");
    $stmt->execute();
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
