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
    } catch (PDOException $e) {
        $guard_id = null;
    }
}

if (empty($visitor_name) || empty($homeowner_id)) {
    echo json_encode(['success' => false, 'message' => 'Visitor name and homeowner selection are required']);
    exit();
}

try {
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
            time_in,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'INSIDE')
    ");
    
    $result = $stmt->execute([
        $visitor_name,
        $visitor_type,
        $company,
        $homeowner_id,
        $person_to_visit,
        $gate,
        $purpose,
        $guard_id
    ]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Visitor logged successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to log visitor']);
    }
    
} catch (PDOException $e) {
    // Graceful handling of foreign key errors
    if ($e->getCode() == '23000') {
        echo json_encode([
            'success' => false, 
            'message' => 'Database Sync Error: Please ensure the selected homeowner and your account are correctly registered in the system.'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?>
