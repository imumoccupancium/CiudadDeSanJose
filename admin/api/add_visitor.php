<?php
header('Content-Type: application/json');
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$visitor_name = $_POST['visitor_name'] ?? '';
$visitor_type = $_POST['visitor_type'] ?? 'Personal';
$company = $_POST['company'] ?? null;
$person_to_visit = $_POST['person_to_visit'] ?? '';
$homeowner_id = $_POST['homeowner_id'] ?? null;
$gate = $_POST['gate'] ?? 'Main Gate';
$purpose = $_POST['purpose'] ?? '';

// Robust guard_id handling
$guard_id = $_SESSION['user_id'] ?? null;

// Validate if guard exists in database to prevent foreign key errors
if ($guard_id) {
    try {
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $checkStmt->execute([$guard_id]);
        if (!$checkStmt->fetch()) {
            $guard_id = null; // Set to null if guard doesn't exist in DB (e.g. demo account)
        }
    }
    catch (PDOException $e) {
        $guard_id = null;
    }
}

if (empty($visitor_name) || empty($homeowner_id)) {
    echo json_encode(['success' => false, 'message' => 'Visitor name and homeowner selection are required']);
    exit();
}

try {
    // Generate QR Token for Visitor
    $qr_token = 'VIS-' . strtoupper(bin2hex(random_bytes(4))) . '-' . date('His');
    
    // Custom expiry handling
    $expiry_input = $_POST['qr_expiry'] ?? '';
    if (!empty($expiry_input)) {
        $qr_expiry = $expiry_input . ' 23:59:59';
    } else {
        $qr_expiry = date('Y-m-d H:i:s', strtotime('+1 year'));
    }
    
    $qr_last_generated = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("
        INSERT INTO visitor_logs (
            visitor_name, 
            visitor_type, 
            company, 
            homeowner_id, 
            person_to_visit, 
            gate, 
            purpose, 
            guard_id,
            qr_token,
            qr_expiry,
            qr_last_generated,
            time_in,
            status,
            current_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'INSIDE', 'IN')
    ");

    $result = $stmt->execute([
        $visitor_name,
        $visitor_type,
        $company,
        $homeowner_id,
        $person_to_visit,
        $gate,
        $purpose,
        $guard_id,
        $qr_token,
        $qr_expiry,
        $qr_last_generated
    ]);

    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Visitor logged and QR Pass generated!',
            'qr_token' => $qr_token,
            'qr_expiry' => date('M d, Y h:i A', strtotime($qr_expiry))
        ]);
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Failed to log visitor']);
    }


}
catch (PDOException $e) {
    // Graceful handling of foreign key errors
    if ($e->getCode() == '23000') {
        echo json_encode([
            'success' => false,
            'message' => 'Database Sync Error: Please ensure the selected homeowner and your account are correctly registered in the system.'
        ]);
    }
    else {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?>
