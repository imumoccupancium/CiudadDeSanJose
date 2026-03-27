<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $type = $_GET['type'] ?? '';
    $where = "is_shown = 0";
    $params = [];

    if (!empty($type)) {
        // Special case for homeowner: fetch both homeowner and family alerts
        if ($type === 'homeowner') {
            $where .= " AND (type = 'homeowner' OR type = 'family')";
        } else {
            $where .= " AND type = ?";
            $params[] = $type;
        }
    }

    // Fetch top 5 unshown alerts for this context
    $stmt = $pdo->prepare("SELECT id, message, status FROM scan_alerts WHERE $where ORDER BY timestamp ASC LIMIT 5");
    $stmt->execute($params);
    $alerts = $stmt->fetchAll();

    if (!empty($alerts)) {
        // Mark as shown - unique per page context isn't perfect with is_shown=1,
        // but for this subdivision app, it ensures alerts don't stack up indefinitely.
        $ids = array_column($alerts, 'id');
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $updateStmt = $pdo->prepare("UPDATE scan_alerts SET is_shown = 1 WHERE id IN ($placeholders)");
        $updateStmt->execute($ids);
    }

    echo json_encode($alerts);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
