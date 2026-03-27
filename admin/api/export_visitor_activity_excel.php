<?php
require_once '../../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$dateFrom = $_GET['date_from'] ?? null;
$dateTo = $_GET['date_to'] ?? null;
$actionCode = $_GET['action'] ?? null;

try {
    $where = "WHERE 1=1";
    $params = [];
    
    if ($dateFrom) {
        $where .= " AND DATE(val.timestamp) >= :df";
        $params[':df'] = $dateFrom;
    }
    if ($dateTo) {
        $where .= " AND DATE(val.timestamp) <= :dt";
        $params[':dt'] = $dateTo;
    }
    if ($actionCode) {
        $where .= " AND val.action = :ac";
        $params[':ac'] = $actionCode;
    }

    $query = "
        SELECT 
            v.visitor_name,
            CONCAT('VIS-', v.id) as visitor_id_num,
            val.action,
            val.timestamp,
            COALESCE(val.device_name, 'Main Gate Scanner') as device,
            h.name as host_name
        FROM visitor_activity_logs val
        JOIN visitor_logs v ON val.visitor_id = v.id
        JOIN homeowners h ON val.homeowner_id = h.id
        $where
        ORDER BY val.timestamp DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for download
    $filename = "visitor_activity_logs_" . date('Y-m-d_His') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    
    // Header row
    fputcsv($output, ['Visitor Name', 'Visitor ID', 'Host Resident', 'Action', 'Date', 'Time', 'Scanner/Gate']);

    // Data rows
    foreach ($logs as $row) {
        $timestamp = strtotime($row['timestamp']);
        fputcsv($output, [
            $row['visitor_name'],
            $row['visitor_id_num'],
            $row['host_name'],
            $row['action'] == 'IN' ? 'ENTRY' : 'EXIT',
            date('M d, Y', $timestamp),
            date('h:i A', $timestamp),
            $row['device']
        ]);
    }

    fclose($output);

} catch (PDOException $e) {
    die('Error exporting: ' . $e->getMessage());
}
?>
