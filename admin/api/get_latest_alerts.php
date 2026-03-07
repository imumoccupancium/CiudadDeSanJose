<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    // Fetch top 5 unshown alerts
    $stmt = $pdo->query("SELECT id, message, status FROM scan_alerts WHERE is_shown = 0 ORDER BY timestamp ASC LIMIT 5");
    $alerts = $stmt->fetchAll();

    if (!empty($alerts)) {
        // Mark as shown
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
