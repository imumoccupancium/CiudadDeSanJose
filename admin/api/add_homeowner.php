<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $name = $_POST['name'] ?? '';
    $homeowner_id = $_POST['homeowner_id'] ?? '';
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $address = $_POST['address'] ?? '';
    $generate_qr = isset($_POST['generate_qr']);
    
    if (empty($name) || empty($homeowner_id) || empty($address)) {
        echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
        exit;
    }
    
    $qr_token = null;
    $qr_expiry = null;
    $qr_last_generated = null;

    if ($generate_qr) {
        $qr_token = bin2hex(random_bytes(16)); // Secure random token
        $qr_expiry = date('Y-m-d H:i:s', strtotime('+1 year')); // 1 year validity
        $qr_last_generated = date('Y-m-d H:i:s');
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO homeowners (homeowner_id, name, email, phone, address, qr_code, qr_token, qr_expiry, qr_last_generated, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    
    $stmt->execute([$homeowner_id, $name, $email, $phone, $address, $qr_token, $qr_token, $qr_expiry, $qr_last_generated]);
    
    echo json_encode(['success' => true, 'message' => 'Homeowner added successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
