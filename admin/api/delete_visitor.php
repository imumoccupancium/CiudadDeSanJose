<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing ID']);
        exit();
    }

    $stmt = $pdo->prepare("DELETE FROM visitor_logs WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Visitor log deleted successfully'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
