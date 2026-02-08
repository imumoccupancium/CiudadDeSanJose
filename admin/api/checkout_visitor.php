<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Visitor ID is required']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        UPDATE visitor_logs 
        SET time_out = NOW(), 
            status = 'OUT' 
        WHERE id = ? AND status = 'INSIDE'
    ");
    
    $result = $stmt->execute([$id]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Visitor checked out successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Visitor already checked out or not found']);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
