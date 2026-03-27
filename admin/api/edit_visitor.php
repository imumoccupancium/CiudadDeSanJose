<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $id = $_POST['id'] ?? null;
    $visitor_name = $_POST['visitor_name'] ?? '';
    $visitor_type = $_POST['visitor_type'] ?? 'Personal';
    $company = $_POST['company'] ?? null;
    $homeowner_id = $_POST['homeowner_id'] ?? null;
    $person_to_visit = $_POST['person_to_visit'] ?? '';
    $qr_expiry = $_POST['qr_expiry'] ?? null;
    $purpose = $_POST['purpose'] ?? '';
    $status = $_POST['status'] ?? 'INSIDE';
    
    // Set current_status based on status
    $current_status = ($status === 'INSIDE') ? 'IN' : 'OUT';

    if (!$id || !$visitor_name || !$homeowner_id) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }

    if (!empty($qr_expiry)) {
        $qr_expiry = $qr_expiry . ' 23:59:59';
    }

    $stmt = $pdo->prepare("
        UPDATE visitor_logs 
        SET visitor_name = ?, 
            visitor_type = ?, 
            company = ?, 
            homeowner_id = ?, 
            person_to_visit = ?,
            qr_expiry = ?, 
            purpose = ?, 
            status = ?,
            current_status = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $visitor_name,
        $visitor_type,
        $company,
        $homeowner_id,
        $person_to_visit,
        $qr_expiry,
        $purpose,
        $status,
        $current_status,
        $id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Visitor log updated successfully'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
