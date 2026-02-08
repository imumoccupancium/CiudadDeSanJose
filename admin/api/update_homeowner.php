<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $homeowner_id = $_POST['homeowner_id'] ?? '';
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $address = $_POST['address'] ?? '';
    $status = $_POST['status'] ?? 'active';
    $qr_expiry = !empty($_POST['qr_expiry']) ? $_POST['qr_expiry'] : null;
    
    if (empty($id) || empty($name) || empty($homeowner_id) || empty($address)) {
        echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        UPDATE homeowners 
        SET name = ?, homeowner_id = ?, email = ?, phone = ?, address = ?, status = ?, qr_expiry = ?
        WHERE id = ?
    ");
    
    $stmt->execute([$name, $homeowner_id, $email, $phone, $address, $status, $qr_expiry, $id]);
    
    echo json_encode(['success' => true, 'message' => 'Homeowner updated successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
