<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? 'active'; // active, disabled, suspended
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Family member ID is required']);
        exit;
    }
    
    $stmt = $pdo->prepare("UPDATE family_members SET access_status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    
    $statusText = ucfirst($status);
    echo json_encode([
        'success' => true, 
        'message' => "Access status updated to $statusText"
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
