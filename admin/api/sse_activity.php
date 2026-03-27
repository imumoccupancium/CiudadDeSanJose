<?php
/**
 * admin/api/sse_activity.php
 * Provides real-time updates for the admin dashboard via Server-Sent Events (SSE).
 */

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Ensure no compression if using Apache
if (function_exists('apache_setenv')) {
    apache_setenv('no-gzip', '1');
}

require_once '../../config/database.php';

// Initial state: Get the current maximum log IDs
$stmt = $pdo->query("SELECT MAX(id) as max_id FROM entry_logs");
$lastEntryId = $stmt->fetch()['max_id'] ?? 0;

$stmt = $pdo->query("SELECT MAX(id) as max_id FROM family_member_logs");
$lastFamilyId = $stmt->fetch()['max_id'] ?? 0;

$stmt = $pdo->query("SELECT MAX(id) as max_id FROM visitor_activity_logs");
$lastVisitorId = $stmt->fetch()['max_id'] ?? 0;

// Set time limit to infinity
set_time_limit(0);

// Re-fetch latest stats for initial push
function getUpdatedStats($pdo) {
    return $pdo->query("SELECT 
        (SELECT COUNT(*) FROM homeowners WHERE status = 'active') as total_homeowners,
        (SELECT COUNT(*) FROM homeowners WHERE current_status = 'IN' AND status = 'active') as currently_inside,
        (SELECT COUNT(*) FROM entry_logs WHERE action = 'IN' AND DATE(timestamp) = CURDATE()) + 
        (SELECT COUNT(*) FROM family_member_logs WHERE action = 'IN' AND DATE(timestamp) = CURDATE()) +
        (SELECT COUNT(*) FROM visitor_activity_logs WHERE action = 'IN' AND DATE(timestamp) = CURDATE()) as total_entries_today,
        (SELECT COUNT(*) FROM entry_logs WHERE action = 'OUT' AND DATE(timestamp) = CURDATE()) +
        (SELECT COUNT(*) FROM family_member_logs WHERE action = 'OUT' AND DATE(timestamp) = CURDATE()) +
        (SELECT COUNT(*) FROM visitor_activity_logs WHERE action = 'OUT' AND DATE(timestamp) = CURDATE()) as total_exits_today
    ")->fetch(PDO::FETCH_ASSOC);
}

$stats = getUpdatedStats($pdo);

$data = [
    'type' => 'init',
    'stats' => $stats,
    'timestamp' => date('Y-m-d H:i:s')
];
echo "data: " . json_encode($data) . "\n\n";

// Flush initial state immediately
if (ob_get_level() > 0) ob_flush();
flush();

while (true) {
    // 1. Check if a new log entry exists in any table
    $entryStmt = $pdo->prepare("SELECT COUNT(*) as count, MAX(id) as max_id FROM entry_logs WHERE id > ?");
    $entryStmt->execute([$lastEntryId]);
    $entryResult = $entryStmt->fetch();

    $familyStmt = $pdo->prepare("SELECT COUNT(*) as count, MAX(id) as max_id FROM family_member_logs WHERE id > ?");
    $familyStmt->execute([$lastFamilyId]);
    $familyResult = $familyStmt->fetch();

    $visitorStmt = $pdo->prepare("SELECT COUNT(*) as count, MAX(id) as max_id FROM visitor_activity_logs WHERE id > ?");
    $visitorStmt->execute([$lastVisitorId]);
    $visitorResult = $visitorStmt->fetch();

    if ($entryResult['count'] > 0 || $familyResult['count'] > 0 || $visitorResult['count'] > 0) {
        $lastEntryId = $entryResult['max_id'] ?? $lastEntryId;
        $lastFamilyId = $familyResult['max_id'] ?? $lastFamilyId;
        $lastVisitorId = $visitorResult['max_id'] ?? $lastVisitorId;

        // 2. Fetch updated statistics
        $stats = getUpdatedStats($pdo);

        $data = [
            'type' => 'new_scan',
            'stats' => $stats,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        echo "data: " . json_encode($data) . "\n\n";
    }

    // Heartbeat to keep connection alive
    echo ": heartbeat " . date('H:i:s') . "\n\n";

    // Flush the output buffer
    if (ob_get_level() > 0) {
        ob_flush();
    }
    flush();

    // Check for client disconnect
    if (connection_aborted()) {
        break;
    }

    // Wait 1.5 seconds before checking again
    usleep(1500000); 
}
