<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;
    $action = $_GET['action'] ?? null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    
    $where = "WHERE v.last_scan_time IS NOT NULL";
    $params = [];
    
    if ($dateFrom) {
        $where .= " AND DATE(v.last_scan_time) >= :df";
        $params[':df'] = $dateFrom;
    }
    if ($dateTo) {
        $where .= " AND DATE(v.last_scan_time) <= :dt";
        $params[':dt'] = $dateTo;
    }
    if ($action) {
        $where .= " AND v.current_status = :ac";
        $params[':ac'] = $action;
    }
    
    $query = "
        SELECT 
            v.visitor_name as name,
            CONCAT('VIS-', v.id) as id_number,
            v.current_status as action,
            DATE_FORMAT(v.last_scan_time, '%Y-%m-%d') as date,
            DATE_FORMAT(v.last_scan_time, '%H:%i:%s') as time,
            COALESCE(v.gate, 'Main Gate Scanner') as device,
            v.last_scan_time as timestamp,
            h.name as host_name
        FROM visitor_logs v
        JOIN homeowners h ON v.homeowner_id = h.id
        $where
        ORDER BY v.last_scan_time DESC
        LIMIT $limit
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($logs);
    
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
