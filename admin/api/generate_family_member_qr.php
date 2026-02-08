<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $id = $_POST['id'] ?? 0;
    $expiry_date = $_POST['expiry_date'] ?? null;
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Family member ID is required']);
        exit;
    }
    
    $token = bin2hex(random_bytes(16));
    
    if (!empty($expiry_date)) {
        $expiry = $expiry_date . ' 23:59:59';
    } else {
        $expiry = date('Y-m-d H:i:s', strtotime('+1 year'));
    }
    
    $now = date('Y-m-d H:i:s');
    
    $stmt = $pdo->prepare("
        UPDATE family_members 
        SET qr_code = ?, qr_token = ?, qr_expiry = ?, qr_last_generated = ?
        WHERE id = ?
    ");
    
    $stmt->execute([$token, $token, $expiry, $now, $id]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'QR Code generated successfully',
        'token' => $token,
        'expiry' => date('M d, Y', strtotime($expiry))
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
