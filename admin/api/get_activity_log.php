<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;
    $action = $_GET['action'] ?? null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5000;

    $params = [];

    // System-only restriction: If no date filter is provided, default to last 90 days to keep UI fast.
    // This does NOT affect Excel exports.
    if (!$dateFrom && !$dateTo) {
        $dateFrom = date('Y-m-d', strtotime('-90 days'));
    }

    $where1 = "WHERE 1=1";
    $where2 = "WHERE 1=1";

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
    if ($action) {
        $where1 .= " AND action = :ac1";
        $where2 .= " AND action = :ac2";
        $params[':ac1'] = $action;
        $params[':ac2'] = $action;
    }

    $query = "
        SELECT * FROM (
            (SELECT 
                h.name as homeowner_name,
                h.homeowner_id,
                'homeowner' as user_type,
                el.action,
                DATE_FORMAT(el.timestamp, '%Y-%m-%d') as date,
                DATE_FORMAT(el.timestamp, '%h:%i:%s %p') as time,
                COALESCE(el.device_name, 'Main Gate Scanner') as device,
                el.timestamp
            FROM entry_logs el
            JOIN homeowners h ON el.homeowner_id = h.id
            $where1)
            
            UNION ALL
            
            (SELECT 
                fm.full_name as homeowner_name,
                fm.homeowner_id,
                'family' as user_type,
                fml.action,
                DATE_FORMAT(fml.timestamp, '%Y-%m-%d') as date,
                DATE_FORMAT(fml.timestamp, '%h:%i:%s %p') as time,
                COALESCE(fml.device_name, 'Main Gate Scanner') as device,
                fml.timestamp
            FROM family_member_logs fml
            JOIN family_members fm ON fml.family_member_id = fm.id
            $where2)
        ) as combined_logs
        ORDER BY timestamp DESC
        LIMIT $limit
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($logs);


}
catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
