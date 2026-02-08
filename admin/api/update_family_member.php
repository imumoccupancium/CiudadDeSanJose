<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $id = $_POST['id'] ?? null;
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $qr_expiry = $_POST['qr_expiry'] ?? null;
    $access_status = $_POST['access_status'] ?? 'active';
    
    if (empty($id) || empty($full_name)) {
        echo json_encode(['success' => false, 'message' => 'Required fields (ID and Full Name) are missing']);
        exit;
    }
    
    // Process Expiry
    if (!empty($qr_expiry)) {
        if (strlen($qr_expiry) == 10) {
            $qr_expiry .= ' 23:59:59';
        }
    }
    
    $stmt = $pdo->prepare("
        UPDATE family_members 
        SET full_name = ?, email = ?, phone = ?, qr_expiry = ?, access_status = ?
        WHERE id = ?
    ");
    
    $stmt->execute([$full_name, $email, $phone, $qr_expiry, $access_status, $id]);
    
    echo json_encode(['success' => true, 'message' => 'Family member updated successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
