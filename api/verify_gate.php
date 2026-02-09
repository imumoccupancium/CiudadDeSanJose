<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Sanity check for token
$token = $_POST['token'] ?? '';
$gateName = $_POST['gate_name'] ?? 'Main Gate Simulation';
$gateMode = $_POST['gate_mode'] ?? 'IN'; // IN or OUT

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'No QR code detected']);
    exit;
}

try {
    // 1. Try to find in Homeowners
    $stmt = $pdo->prepare("SELECT id, name, homeowner_id, current_status, status, qr_expiry FROM homeowners WHERE qr_token = ? OR qr_code = ?");
    $stmt->execute([$token, $token]);
    $user = $stmt->fetch();
    $type = 'homeowner';

    // 2. If not found, try Family Members
    if (!$user) {
        $stmt = $pdo->prepare("SELECT id, full_name as name, homeowner_id, access_status as status, qr_expiry, current_status FROM family_members WHERE qr_token = ? OR qr_code = ?");
        $stmt->execute([$token, $token]);
        $user = $stmt->fetch();
        $type = 'family';
    }

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid QR Code']);
        exit;
    }

    // 3. Validation Checks
    if ($user['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Access Suspended']);
        exit;
    }

    if (!empty($user['qr_expiry']) && strtotime($user['qr_expiry']) < time()) {
        echo json_encode(['success' => false, 'message' => 'QR Code Expired']);
        exit;
    }

    // --- GATE LOGIC VALIDATION ---
    // Prevent scanning 'IN' if already inside, or 'OUT' if already outside
    if ($gateMode === 'IN' && $user['current_status'] === 'IN') {
        echo json_encode(['success' => false, 'message' => 'Access Denied: You are already flagged as INSIDE.']);
        exit;
    }
    
    if ($gateMode === 'OUT' && $user['current_status'] === 'OUT') {
        echo json_encode(['success' => false, 'message' => 'Access Denied: You are already flagged as OUTSIDE.']);
        exit;
    }

    // 4. Record the Access
    if ($type === 'homeowner') {
        $logStmt = $pdo->prepare("INSERT INTO entry_logs (homeowner_id, action, timestamp, device_name) VALUES (?, ?, NOW(), ?)");
        $logStmt->execute([$user['id'], $gateMode, $gateName]);

        // Fallback: Manually update homeowner status in case trigger failed
        $updateStmt = $pdo->prepare("UPDATE homeowners SET current_status = ?, last_scan_time = NOW() WHERE id = ?");
        $updateStmt->execute([$gateMode, $user['id']]);
    } else {
        $logStmt = $pdo->prepare("INSERT INTO family_member_logs (family_member_id, homeowner_id, action, timestamp, device_name) VALUES (?, ?, ?, NOW(), ?)");
        $logStmt->execute([$user['id'], $user['homeowner_id'], $gateMode, $gateName]);

        // Fallback: Manually update family member status in case trigger failed
        $updateStmt = $pdo->prepare("UPDATE family_members SET current_status = ?, last_scan_time = NOW() WHERE id = ?");
        $updateStmt->execute([$gateMode, $user['id']]);
    }

    // The triggers in specific tables will handle updating current_status automatically (from schema.sql)

    echo json_encode([
        'success' => true,
        'message' => 'Access Granted. Welcome, ' . $user['name'] . '!',
        'user' => [
            'name' => $user['name'],
            'id' => $user['homeowner_id'] ?? 'N/A',
            'type' => ucfirst($type),
            'action' => $gateMode
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'System Error: ' . $e->getMessage()]);
}
