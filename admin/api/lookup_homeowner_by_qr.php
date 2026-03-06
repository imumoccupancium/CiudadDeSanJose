<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'No token provided']);
    exit;
}

try {
    // 1. Check homeowners
    $stmt = $pdo->prepare("SELECT id, name, homeowner_id, status, qr_token, current_status, qr_expiry, address, phone, email, last_scan_time FROM homeowners WHERE qr_token = ?");
    $stmt->execute([$token]);
    $result = $stmt->fetch();
    $type = 'homeowner';

    // 2. Check family members if not found
    if (!$result) {
        $stmt = $pdo->prepare("SELECT id, full_name as name, homeowner_id, access_status as status, qr_token, current_status, qr_expiry, phone, email, last_scan_time FROM family_members WHERE qr_token = ?");
        $stmt->execute([$token]);
        $result = $stmt->fetch();
        $type = 'family';
    }

    if ($result) {
        // Format expiry and last scan if needed
        if ($result['qr_expiry']) {
            $result['qr_expiry_formatted'] = date('M d, Y', strtotime($result['qr_expiry']));
        }
        
        echo json_encode([
            'success' => true,
            'data' => $result,
            'type' => $type
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'QR Code not found in registry'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Search error: ' . $e->getMessage()]);
}
