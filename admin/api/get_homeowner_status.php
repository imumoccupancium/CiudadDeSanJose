<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $stmt = $pdo->query("
        SELECT 
            h.id,
            h.name,
            h.homeowner_id,
            h.current_status,
            DATE_FORMAT(h.last_scan_time, '%Y-%m-%d %H:%i:%s') as last_scan_time
        FROM homeowners h
        WHERE h.status = 'active'
        ORDER BY h.last_scan_time DESC
    ");
    
    $homeowners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($homeowners);
    
} catch (PDOException $e) {
    // Return sample data if database not set up yet
    $sampleData = [
        [
            'id' => '1',
            'name' => 'Juan Dela Cruz',
            'homeowner_id' => 'HO-001',
            'current_status' => 'IN',
            'last_scan_time' => date('Y-m-d H:i:s', strtotime('-5 minutes'))
        ],
        [
            'id' => '2',
            'name' => 'Maria Santos',
            'homeowner_id' => 'HO-002',
            'current_status' => 'OUT',
            'last_scan_time' => date('Y-m-d H:i:s', strtotime('-15 minutes'))
        ],
        [
            'id' => '3',
            'name' => 'Pedro Reyes',
            'homeowner_id' => 'HO-003',
            'current_status' => 'IN',
            'last_scan_time' => date('Y-m-d H:i:s', strtotime('-30 minutes'))
        ],
        [
            'id' => '4',
            'name' => 'Ana Garcia',
            'homeowner_id' => 'HO-004',
            'current_status' => 'OUT',
            'last_scan_time' => date('Y-m-d H:i:s', strtotime('-45 minutes'))
        ],
        [
            'id' => '5',
            'name' => 'Carlos Mendoza',
            'homeowner_id' => 'HO-005',
            'current_status' => 'IN',
            'last_scan_time' => date('Y-m-d H:i:s', strtotime('-1 hour'))
        ],
        [
            'id' => '6',
            'name' => 'Rosa Fernandez',
            'homeowner_id' => 'HO-006',
            'current_status' => 'IN',
            'last_scan_time' => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ],
        [
            'id' => '7',
            'name' => 'Miguel Torres',
            'homeowner_id' => 'HO-007',
            'current_status' => 'OUT',
            'last_scan_time' => date('Y-m-d H:i:s', strtotime('-3 hours'))
        ],
        [
            'id' => '8',
            'name' => 'Sofia Ramirez',
            'homeowner_id' => 'HO-008',
            'current_status' => 'IN',
            'last_scan_time' => date('Y-m-d H:i:s', strtotime('-4 hours'))
        ]
    ];
    echo json_encode($sampleData);
}
?>
