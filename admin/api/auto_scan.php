<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

$token = $_POST['token'] ?? '';
$deviceName = 'Admin Panel Auto-Scanner';

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'No QR code detected']);
    exit;
}

try {
    // 1. Search in Homeowners
    $stmt = $pdo->prepare("SELECT id, name, homeowner_id, current_status, status, qr_expiry, 'homeowner' as type FROM homeowners WHERE qr_token = ? OR qr_code = ?");
    $stmt->execute([$token, $token]);
    $user = $stmt->fetch();

    // 2. Search in Family Members if not found
    if (!$user) {
        $stmt = $pdo->prepare("SELECT id, full_name as name, homeowner_id, current_status, access_status as status, qr_expiry, 'family' as type FROM family_members WHERE qr_token = ? OR qr_code = ?");
        $stmt->execute([$token, $token]);
        $user = $stmt->fetch();
    }

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid QR Code']);
        exit;
    }

    // 3. Status Check
    if ($user['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Access Suspended/Disabled']);
        exit;
    }

    if (!empty($user['qr_expiry']) && strtotime($user['qr_expiry']) < time()) {
        echo json_encode(['success' => false, 'message' => 'QR Code Expired']);
        exit;
    }

    // 4. Determine New Status (Toggle)
    $currentStatus = $user['current_status'] ?: 'OUT'; // Default to OUT if null
    $newStatus = ($currentStatus === 'OUT') ? 'IN' : 'OUT';

    // 5. Update Database & Log
    if ($user['type'] === 'homeowner') {
        // Log Entry
        $logStmt = $pdo->prepare("INSERT INTO entry_logs (homeowner_id, action, timestamp, device_name) VALUES (?, ?, NOW(), ?)");
        $logStmt->execute([$user['id'], $newStatus, $deviceName]);

        // Update Homeowner
        $updateStmt = $pdo->prepare("UPDATE homeowners SET current_status = ?, last_scan_time = NOW() WHERE id = ?");
        $updateStmt->execute([$newStatus, $user['id']]);
    } else {
        // Log Entry
        $logStmt = $pdo->prepare("INSERT INTO family_member_logs (family_member_id, homeowner_id, action, timestamp, device_name) VALUES (?, ?, ?, NOW(), ?)");
        $logStmt->execute([$user['id'], $user['homeowner_id'], $newStatus, $deviceName]);

        // Update Family Member
        $updateStmt = $pdo->prepare("UPDATE family_members SET current_status = ?, last_scan_time = NOW() WHERE id = ?");
        $updateStmt->execute([$newStatus, $user['id']]);
    }

    echo json_encode([
        'success' => true,
        'message' => ($newStatus === 'IN') ? 'Access Granted. Welcome, ' . $user['name'] . '!' : 'Departure Logged. Goodbye, ' . $user['name'] . '!',
        'user' => [
            'name' => $user['name'],
            'id' => $user['homeowner_id'],
            'status' => ($newStatus === 'IN') ? 'INSIDE' : 'OUTSIDE',
            'type' => ucfirst($user['type'])
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
