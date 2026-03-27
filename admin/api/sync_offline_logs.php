<?php
// ============================================================
// sync_offline_logs.php
// Called by scanner_relay.py to upload scans that happened
// while the internet was down (offline mode).
// Receives a JSON array of log entries in the request body.
// ============================================================
header('Content-Type: application/json');
require_once '../../config/database.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!is_array($data) || empty($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or empty payload. Expected a JSON array.']);
    exit;
}

$processed = 0;
$skipped = 0;
$errors = [];

try {
    $pdo->beginTransaction();

    foreach ($data as $log) {
        // --- Validate required fields ---
        $user_internal_id = $log['user_internal_id'] ?? null;
        $homeowner_id = $log['homeowner_id'] ?? null;
        $action = $log['action'] ?? null;
        $timestamp = $log['timestamp'] ?? date('Y-m-d H:i:s');
        $device_name = $log['device_name'] ?? 'Offline Scanner';
        $user_type = $log['user_type'] ?? null;

        if (!$user_internal_id || !$action || !$user_type) {
            $skipped++;
            $errors[] = "Skipped log - missing required fields: " . json_encode($log);
            continue;
        }

        if ($user_type === 'homeowner') {
            // Insert into entry_logs
            $lStmt = $pdo->prepare("
                INSERT INTO entry_logs (homeowner_id, action, timestamp, device_name)
                VALUES (?, ?, ?, ?)
            ");
            $lStmt->execute([$user_internal_id, $action, $timestamp, $device_name]);

            // Update homeowner current_status (only if this log is the most recent)
            $uStmt = $pdo->prepare("
                UPDATE homeowners 
                SET current_status = ?, last_scan_time = ?
                WHERE id = ?
                  AND (last_scan_time IS NULL OR last_scan_time <= ?)
            ");
            $uStmt->execute([$action, $timestamp, $user_internal_id, $timestamp]);

        }
        elseif ($user_type === 'family') {
            // Insert into family_member_logs
            $lStmt = $pdo->prepare("
                INSERT INTO family_member_logs (family_member_id, homeowner_id, action, timestamp, device_name)
                VALUES (?, ?, ?, ?, ?)
            ");
            $lStmt->execute([$user_internal_id, $homeowner_id, $action, $timestamp, $device_name]);

            // Update family member current_status
            $uStmt = $pdo->prepare("
                UPDATE family_members 
                SET current_status = ?, last_scan_time = ?
                WHERE id = ?
                  AND (last_scan_time IS NULL OR last_scan_time <= ?)
            ");
            $uStmt->execute([$action, $timestamp, $user_internal_id, $timestamp]);
        }
        elseif ($user_type === 'visitor') {
            // General history entry
            $lStmt = $pdo->prepare("INSERT INTO entry_logs (homeowner_id, action, timestamp, device_name) VALUES (?, ?, ?, ?)");
            $lStmt->execute([$homeowner_id, $action, $timestamp, $device_name]);

            // Update Visitor specific record
            if ($action === 'IN') {
                $uStmt = $pdo->prepare("UPDATE visitor_logs SET current_status = 'IN', time_in = ?, last_scan_time = ? WHERE id = ? AND (last_scan_time IS NULL OR last_scan_time <= ?)");
            } else {
                $uStmt = $pdo->prepare("UPDATE visitor_logs SET current_status = 'OUT', time_out = ?, last_scan_time = ? WHERE id = ? AND (last_scan_time IS NULL OR last_scan_time <= ?)");
            }
            $uStmt->execute([$timestamp, $timestamp, $user_internal_id, $timestamp]);
        }
        else {
            $skipped++;
            $errors[] = "Unknown user_type: {$user_type}";
            continue;
        }

        $processed++;
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'processed' => $processed,
        'skipped' => $skipped,
        'errors' => $errors
    ]);

}
catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
