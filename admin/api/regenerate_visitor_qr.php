<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = $_POST['id'] ?? '';
$expiry_date = $_POST['expiry_date'] ?? '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Visitor ID is required']);
    exit;
}

try {
    // Generate new secure token
    $new_token = 'VIS-' . strtoupper(bin2hex(random_bytes(4))) . '-' . date('His');
    
    // Process expiry
    if (!empty($expiry_date)) {
        $qr_expiry = $expiry_date . ' 23:59:59';
    } else {
        $qr_expiry = date('Y-m-d H:i:s', strtotime('+1 year'));
    }

    $stmt = $pdo->prepare("
        UPDATE visitor_logs 
        SET qr_token = ?, 
            qr_expiry = ?, 
            qr_last_generated = NOW() 
        WHERE id = ?
    ");
    
    $result = $stmt->execute([$new_token, $qr_expiry, $id]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'New QR Pass generated!',
            'token' => $new_token,
            'expiry' => date('M d, Y h:i A', strtotime($qr_expiry))
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update QR token']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
