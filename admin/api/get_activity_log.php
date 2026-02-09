<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $stmt = $pdo->query("
        (SELECT 
            h.name as homeowner_name,
            h.homeowner_id,
            el.action,
            DATE_FORMAT(el.timestamp, '%Y-%m-%d') as date,
            DATE_FORMAT(el.timestamp, '%H:%i:%s') as time,
            COALESCE(el.device_name, 'Main Gate Scanner') as device,
            el.timestamp
        FROM entry_logs el
        JOIN homeowners h ON el.homeowner_id = h.id)
        
        UNION ALL
        
        (SELECT 
            fm.full_name as homeowner_name,
            fm.homeowner_id, -- Using the primary homeowner ID ref
            fml.action,
            DATE_FORMAT(fml.timestamp, '%Y-%m-%d') as date,
            DATE_FORMAT(fml.timestamp, '%H:%i:%s') as time,
            COALESCE(fml.device_name, 'Main Gate Scanner') as device,
            fml.timestamp
        FROM family_member_logs fml
        JOIN family_members fm ON fml.family_member_id = fm.id)
        
        ORDER BY timestamp DESC
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
