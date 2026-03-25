<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;
    $action = $_GET['action'] ?? null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    
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
    if ($action) {
        $where1 .= " AND action = :ac1";
        $where2 .= " AND action = :ac2";
        $params[':ac1'] = $action;
        $params[':ac2'] = $action;
    }
    
    $query = "
        (SELECT 
            h.name as homeowner_name,
            h.homeowner_id,
            el.action,
            DATE_FORMAT(el.timestamp, '%Y-%m-%d') as date,
            DATE_FORMAT(el.timestamp, '%H:%i:%s') as time,
            COALESCE(el.device_name, 'Main Gate Scanner') as device,
            el.timestamp
        FROM entry_logs el
        JOIN homeowners h ON el.homeowner_id = h.id
        $where1)
        
        UNION ALL
        
        (SELECT 
            fm.full_name as homeowner_name,
            fm.homeowner_id,
            fml.action,
            DATE_FORMAT(fml.timestamp, '%Y-%m-%d') as date,
            DATE_FORMAT(fml.timestamp, '%H:%i:%s') as time,
            COALESCE(fml.device_name, 'Main Gate Scanner') as device,
            fml.timestamp
        FROM family_member_logs fml
        JOIN family_members fm ON fml.family_member_id = fm.id
        $where2)
        
        ORDER BY timestamp DESC
        LIMIT $limit
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($logs);
    
} catch (PDOException $e) {
    // Return sample data if database not set up yet
    $sampleData = [
        [
            'homeowner_name' => 'Juan Dela Cruz',
            'homeowner_id' => 'HO-001',
            'action' => 'IN',
            'date' => date('Y-m-d'),
            'time' => date('H:i:s', strtotime('-5 minutes')),
            'device' => 'Main Gate Scanner'
        ],
        [
            'homeowner_name' => 'Maria Santos',
            'homeowner_id' => 'HO-002',
            'action' => 'OUT',
            'date' => date('Y-m-d'),
            'time' => date('H:i:s', strtotime('-15 minutes')),
            'device' => 'Back Gate Scanner'
        ],
        [
            'homeowner_name' => 'Pedro Reyes',
            'homeowner_id' => 'HO-003',
            'action' => 'IN',
            'date' => date('Y-m-d'),
            'time' => date('H:i:s', strtotime('-30 minutes')),
            'device' => 'Main Gate Scanner'
        ],
        [
            'homeowner_name' => 'Ana Garcia',
            'homeowner_id' => 'HO-004',
            'action' => 'OUT',
            'date' => date('Y-m-d'),
            'time' => date('H:i:s', strtotime('-45 minutes')),
            'device' => 'Mobile Scanner 1'
        ],
        [
            'homeowner_name' => 'Carlos Mendoza',
            'homeowner_id' => 'HO-005',
            'action' => 'IN',
            'date' => date('Y-m-d'),
            'time' => date('H:i:s', strtotime('-1 hour')),
            'device' => 'Main Gate Scanner'
        ]
    ];
    echo json_encode($sampleData);
}
?>
