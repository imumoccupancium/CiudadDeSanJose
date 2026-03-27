<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

$token = $_POST['token'] ?? '';
$deviceName = $_POST['device_name'] ?? 'Hardware Scanner';

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'No QR code detected']);
    exit;
}

try {
    // 1. Search in Homeowners
    $stmt = $pdo->prepare("SELECT id, name, homeowner_id, current_status, status, qr_expiry, 'homeowner' as type FROM homeowners WHERE qr_token = ? OR qr_code = ?");
    $stmt->execute([$token, $token]);
    $user = $stmt->fetch();

    // 2. Search in Family Members if homeowners not found
    if (!$user) {
        $stmt = $pdo->prepare("SELECT id, full_name as name, homeowner_id, current_status, access_status as status, qr_expiry, 'family' as type FROM family_members WHERE qr_token = ? OR qr_code = ?");
        $stmt->execute([$token, $token]);
        $user = $stmt->fetch();
    }

    // 3. Search in Visitors if still not found
    if (!$user) {
        $stmt = $pdo->prepare("SELECT id, visitor_name as name, homeowner_id, current_status, 'active' as status, qr_expiry, 'visitor' as type FROM visitor_logs WHERE qr_token = ?");
        $stmt->execute([$token]);
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

    // 4. Determine New Status & Validate
    $action = $_POST['action'] ?? ''; // Can be 'IN' or 'OUT'
    $currentStatus = $user['current_status'] ?: 'OUT';

    if ($action === 'IN' || $action === 'OUT') {
        // Validation: Prevent duplicate status
        if ($action === $currentStatus) {
            $msg = ($action === 'IN')
                ? "You cannot go Inside while your status is INSIDE."
                : "You cannot go Outside while your status is OUTSIDE.";

            // Log alert for the UI
            $alertStmt = $pdo->prepare("INSERT INTO scan_alerts (message, status, type) VALUES (?, 'error', ?)");
            $alertStmt->execute([$msg, $user['type']]);

            echo json_encode([
                'success' => false,
                'message' => $msg
            ]);
            exit;
        }
        $newStatus = $action;
    }
    else {
        // Default to toggle if no explicit action
        $newStatus = ($currentStatus === 'OUT') ? 'IN' : 'OUT';
    }

    // 5. Update Database & Log
    if ($user['type'] === 'homeowner') {
        // Log Entry
        $logStmt = $pdo->prepare("INSERT INTO entry_logs (homeowner_id, action, timestamp, device_name) VALUES (?, ?, NOW(), ?)");
        $logStmt->execute([$user['id'], $newStatus, $deviceName]);

        // Update Homeowner
        $updateStmt = $pdo->prepare("UPDATE homeowners SET current_status = ?, last_scan_time = NOW() WHERE id = ?");
        $updateStmt->execute([$newStatus, $user['id']]);
    }
    elseif ($user['type'] === 'visitor') {
        // Update Visitor Log
        if ($newStatus === 'IN') {
            $updateStmt = $pdo->prepare("UPDATE visitor_logs SET current_status = 'IN', status = 'INSIDE', time_in = NOW(), last_scan_time = NOW() WHERE id = ?");
        }
        else {
            $updateStmt = $pdo->prepare("UPDATE visitor_logs SET current_status = 'OUT', status = 'OUT', time_out = NOW(), last_scan_time = NOW() WHERE id = ?");
        }
        $updateStmt->execute([$user['id']]);

        // Specific visitor activity log for history
        $logStmt = $pdo->prepare("INSERT INTO visitor_activity_logs (visitor_id, homeowner_id, action, timestamp, device_name) VALUES (?, ?, ?, NOW(), ?)");
        $logStmt->execute([$user['id'], $user['homeowner_id'], $newStatus, $deviceName]);

        // General entry log for global history (optional, currently uses homeowner_id)
        $logStmt = $pdo->prepare("INSERT INTO entry_logs (homeowner_id, action, timestamp, device_name) VALUES (?, ?, NOW(), ?)");
        $logStmt->execute([$user['homeowner_id'], $newStatus, $deviceName]);
    }
    else {
        // Log Entry for Family
        $logStmt = $pdo->prepare("INSERT INTO family_member_logs (family_member_id, homeowner_id, action, timestamp, device_name) VALUES (?, ?, ?, NOW(), ?)");
        $logStmt->execute([$user['id'], $user['homeowner_id'], $newStatus, $deviceName]);

        // Update Family Member
        $updateStmt = $pdo->prepare("UPDATE family_members SET current_status = ?, last_scan_time = NOW() WHERE id = ?");
        $updateStmt->execute([$newStatus, $user['id']]);
    }

    $successMsg = ($newStatus === 'IN') ? 'Access Granted. Welcome, ' . $user['name'] . '!' : 'Departure Logged. Goodbye, ' . $user['name'] . '!';

    // Log success alert for UI with specific type
    $alertStmt = $pdo->prepare("INSERT INTO scan_alerts (message, status, type) VALUES (?, 'success', ?)");
    $alertStmt->execute([$successMsg, $user['type']]);

    echo json_encode([
        'success' => true,
        'message' => $successMsg,
        'user' => [
            'name' => $user['name'],
            'id' => $user['homeowner_id'],
            'status' => ($newStatus === 'IN') ? 'INSIDE' : 'OUTSIDE',
            'type' => ucfirst($user['type'])
        ]
    ]);

}
catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
