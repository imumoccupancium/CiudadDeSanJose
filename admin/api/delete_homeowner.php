<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $id = $_POST['id'] ?? 0;
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID is required']);
        exit;
    }
    
    $stmt = $pdo->prepare("DELETE FROM homeowners WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true, 'message' => 'Homeowner deleted successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
