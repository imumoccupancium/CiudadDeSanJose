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
    $where1 = "WHERE 1=1";

    $where2 = "WHERE 1=1";
    $params = [];
    
    if ($dateFrom) {
        $where1 .= " AND DATE(timestamp) >= :df1";
        $where2 .= " AND DATE(timestamp) >= :df2";
        $params[':df1'] = $dateFrom;
        $params[':df2'] = $dateFrom;
    }
    if ($dateTo) {
        $where1 .= " AND DATE(timestamp) <= :dt1";
        $where2 .= " AND DATE(timestamp) <= :dt2";
        $params[':dt1'] = $dateTo;
        $params[':dt2'] = $dateTo;
    }
    if ($actionCode) {
        $where1 .= " AND action = :ac1";
        $where2 .= " AND action = :ac2";
        $params[':ac1'] = $actionCode;
        $params[':ac2'] = $actionCode;
    }

    $query = "
        (SELECT 
            h.name as homeowner_name,
            h.homeowner_id,
            el.action,
            el.timestamp,
            COALESCE(el.device_name, 'Main Gate Scanner') as device
        FROM entry_logs el
        JOIN homeowners h ON el.homeowner_id = h.id
        $where1)
        
        UNION ALL
        
        (SELECT 
            fm.full_name as homeowner_name,
            fm.homeowner_id,
            fml.action,
            fml.timestamp,
            COALESCE(fml.device_name, 'Main Gate Scanner') as device
        FROM family_member_logs fml
        JOIN family_members fm ON fml.family_member_id = fm.id
        $where2)
        
        ORDER BY timestamp DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for download
    $filename = "activity_logs_" . date('Y-m-d_His') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    
    // Header row
    fputcsv($output, ['Resident Name', 'ID Number', 'Action', 'Date', 'Time', 'Scanner/Gate']);

    // Data rows
    foreach ($logs as $row) {
        $timestamp = strtotime($row['timestamp']);
        fputcsv($output, [
            $row['homeowner_name'],
            $row['homeowner_id'],
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
