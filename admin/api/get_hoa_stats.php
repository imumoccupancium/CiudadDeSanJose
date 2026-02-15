<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['error' => 'Unauthorized']));
}

try {
    $stats = [
        'total' => 0,
        'active' => 0,
        'recent_activity' => 'None'
    ];

    // Get total and active counts
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active
        FROM users WHERE role = 'hoa'");
    $stmt->execute();
    $counts = $stmt->fetch();
    $stats['total'] = $counts['total'] ?? 0;
    $stats['active'] = $counts['active'] ?? 0;

    // Get most recent login
    $stmt = $pdo->prepare("SELECT last_login FROM users WHERE role = 'hoa' AND last_login IS NOT NULL ORDER BY last_login DESC LIMIT 1");
    $stmt->execute();
    $recent = $stmt->fetch();
    if ($recent) {
        $stats['recent_activity'] = date('M d, H:i', strtotime($recent['last_login']));
    }

    echo json_encode($stats);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
