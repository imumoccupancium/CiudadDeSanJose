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
    
    $qr_code = $generate_qr ? 'QR-' . $homeowner_id : null;
    
    $stmt = $pdo->prepare("
        INSERT INTO homeowners (homeowner_id, name, email, phone, address, qr_code, status)
        VALUES (?, ?, ?, ?, ?, ?, 'active')
    ");
    
    $stmt->execute([$homeowner_id, $name, $email, $phone, $address, $qr_code]);
    
    echo json_encode(['success' => true, 'message' => 'Homeowner added successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
