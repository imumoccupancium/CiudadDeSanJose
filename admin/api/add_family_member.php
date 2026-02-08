<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $homeowner_id = $_POST['homeowner_id'] ?? null;
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $qr_expiry = $_POST['qr_expiry'] ?? null;
    
    if (empty($homeowner_id) || empty($full_name)) {
        echo json_encode(['success' => false, 'message' => 'Required fields (Homeowner and Full Name) are missing']);
        exit;
    }
    
    // Generate QR Token
    $qr_token = bin2hex(random_bytes(16));
    
    // Process Expiry
    if (!empty($qr_expiry)) {
        // If it's just a date, append end of day. If it's datetime, use it.
        if (strlen($qr_expiry) == 10) {
            $qr_expiry .= ' 23:59:59';
        }
    } else {
        $qr_expiry = date('Y-m-d H:i:s', strtotime('+1 year'));
    }
    
    $qr_last_generated = date('Y-m-d H:i:s');
    
    $stmt = $pdo->prepare("
        INSERT INTO family_members (homeowner_id, full_name, email, phone, qr_code, qr_token, qr_expiry, qr_last_generated, access_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    
    $stmt->execute([$homeowner_id, $full_name, $email, $phone, $qr_token, $qr_token, $qr_expiry, $qr_last_generated]);
    
    echo json_encode(['success' => true, 'message' => 'Family member added successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
