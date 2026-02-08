<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $id = $_POST['id'] ?? null;
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Member ID is required']);
        exit;
    }
    
    $stmt = $pdo->prepare("DELETE FROM family_members WHERE id = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true, 'message' => 'Family member removed successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
