<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $today = date('Y-m-d');
    
    // Total Visitor Events
    $totalLogs = $pdo->query("SELECT COUNT(*) FROM visitor_logs WHERE last_scan_time IS NOT NULL")->fetchColumn();
    
    // Entries Today
    $stmtEntries = $pdo->prepare("SELECT COUNT(*) FROM visitor_logs WHERE current_status = 'IN' AND DATE(last_scan_time) = :today");
    $stmtEntries->execute([':today' => $today]);
    $totalEntries = $stmtEntries->fetchColumn();
    
    // Exits Today 
    $stmtExits = $pdo->prepare("SELECT COUNT(*) FROM visitor_logs WHERE current_status = 'OUT' AND DATE(last_scan_time) = :today");
    $stmtExits->execute([':today' => $today]);
    $totalExits = $stmtExits->fetchColumn();
    
    // Unique Visitors today
    $stmtUnique = $pdo->prepare("SELECT COUNT(DISTINCT id) FROM visitor_logs WHERE DATE(last_scan_time) = :today");
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
