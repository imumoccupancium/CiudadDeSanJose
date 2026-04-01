<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;
    $action = $_GET['action'] ?? null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5000;
    
    $params = [];
    
    // System-only restriction: Filter out records older than 90 days by default.
    // Use filters or Excel Export to see older data.
    if (!$dateFrom && !$dateTo) {
        $dateFrom = date('Y-m-d', strtotime('-90 days'));
    }

    $where = "WHERE 1=1";
    
    if ($dateFrom) {
        $where .= " AND DATE(val.timestamp) >= :df";
        $params[':df'] = $dateFrom;
    }
    if ($dateTo) {
        $where .= " AND DATE(val.timestamp) <= :dt";
        $params[':dt'] = $dateTo;
    }
    if ($action) {
        $where .= " AND val.action = :ac";
        $params[':ac'] = $action;
    }
    
    $query = "
        SELECT 
            v.visitor_name as name,
            CONCAT('VIS-', v.id) as id_number,
            val.action,
            DATE_FORMAT(val.timestamp, '%Y-%m-%d') as date,
            DATE_FORMAT(val.timestamp, '%h:%i:%s %p') as time,
            COALESCE(val.device_name, 'Main Gate Scanner') as device,
            val.timestamp,
            h.name as host_name
        FROM visitor_activity_logs val
        JOIN visitor_logs v ON val.visitor_id = v.id
        JOIN homeowners h ON val.homeowner_id = h.id
        $where
        ORDER BY val.timestamp DESC
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
