<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$id = $_POST['id'] ?? '';
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$status = $_POST['status'] ?? 'active';
$password = $_POST['password'] ?? '';

if (empty($id) || empty($name)) {
    echo json_encode(['success' => false, 'message' => 'ID and Name are required']);
    exit();
}

try {
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, status = ?, password = ? WHERE id = ? AND role = 'hoa'");
        $stmt->execute([$name, $email, $status, $hashed_password, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, status = ? WHERE id = ? AND role = 'hoa'");
        $stmt->execute([$name, $email, $status, $id]);
    }
    
    echo json_encode(['success' => true, 'message' => 'HOA account updated successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
