<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $today = date('Y-m-d');
    
    // Total Visitor Events
    $totalLogs = $pdo->query("SELECT COUNT(*) FROM visitor_activity_logs")->fetchColumn();
    
    // Entries Today
    $stmtEntries = $pdo->prepare("SELECT COUNT(*) FROM visitor_activity_logs WHERE action = 'IN' AND DATE(timestamp) = :today");
    $stmtEntries->execute([':today' => $today]);
    $totalEntries = $stmtEntries->fetchColumn();
    
    // Exits Today 
    $stmtExits = $pdo->prepare("SELECT COUNT(*) FROM visitor_activity_logs WHERE action = 'OUT' AND DATE(timestamp) = :today");
    $stmtExits->execute([':today' => $today]);
    $totalExits = $stmtExits->fetchColumn();
    
    // Unique Visitors today
    $stmtUnique = $pdo->prepare("SELECT COUNT(DISTINCT visitor_id) FROM visitor_activity_logs WHERE DATE(timestamp) = :today");
    $stmtUnique->execute([':today' => $today]);
    $uniqueVisitors = $stmtUnique->fetchColumn();
    
    echo json_encode([
        'total_logs' => number_format($totalLogs),
        'total_entries' => number_format($totalEntries),
        'total_exits' => number_format($totalExits),
        'unique_visitors' => number_format($uniqueVisitors)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'total_logs' => 0,
        'total_entries' => 0,
        'total_exits' => 0,
        'unique_visitors' => 0
    ]);
}
?>
