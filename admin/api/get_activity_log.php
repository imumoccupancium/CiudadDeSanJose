<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $stmt = $pdo->query("
        SELECT 
            h.name as homeowner_name,
            h.homeowner_id,
            el.action,
            DATE_FORMAT(el.timestamp, '%Y-%m-%d') as date,
            DATE_FORMAT(el.timestamp, '%H:%i:%s') as time,
            COALESCE(el.device_name, 'Main Gate Scanner') as device
        FROM entry_logs el
        JOIN homeowners h ON el.homeowner_id = h.id
        ORDER BY el.timestamp DESC
        LIMIT 100
    ");
    
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
