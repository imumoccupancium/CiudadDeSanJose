<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

$name = $_POST['name'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$email = $_POST['email'] ?? '';

if (empty($name) || empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
    exit();
}

try {
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, name, email, role, status) VALUES (?, ?, ?, ?, 'hoa', 'active')");
    $stmt->execute([$username, $hashed_password, $name, $email]);
    
    echo json_encode(['success' => true, 'message' => 'HOA account created successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
