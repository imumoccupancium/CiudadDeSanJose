<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    exit(json_encode(['error' => 'ID is required']));
}

try {
    $stmt = $pdo->prepare("SELECT id, username, name, email, status FROM users WHERE id = ? AND role = 'hoa'");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo json_encode($user);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Account not found']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
