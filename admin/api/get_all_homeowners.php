<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $stmt = $pdo->query("
        SELECT 
            h.id,
            h.homeowner_id,
            h.name,
            h.email,
            h.phone,
            h.address,
            h.qr_code,
            h.qr_token,
            h.qr_expiry,
            h.current_status,
            h.status,
            DATE_FORMAT(h.last_scan_time, '%Y-%m-%d %H:%i:%s') as last_scan_time,
            DATE_FORMAT(h.created_at, '%Y-%m-%d') as created_at,
            (SELECT COUNT(*) FROM family_members WHERE homeowner_id = h.id) as family_count
        FROM homeowners h
        ORDER BY h.homeowner_id ASC
    ");
    
    $homeowners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($homeowners);
    
} catch (PDOException $e) {
    // Return sample data if database not set up yet
    $sampleData = [
        [
            'id' => '1',
            'homeowner_id' => 'HO-001',
            'name' => 'Juan Dela Cruz',
            'email' => 'juan.delacruz@email.com',
            'phone' => '09171234567',
            'address' => 'Block 1 Lot 1',
            'qr_code' => 'QR-HO-001',
            'current_status' => 'IN',
            'status' => 'active',
            'last_scan_time' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
            'created_at' => date('Y-m-d')
        ],
        [
            'id' => '2',
            'homeowner_id' => 'HO-002',
            'name' => 'Maria Santos',
            'email' => 'maria.santos@email.com',
            'phone' => '09181234567',
            'address' => 'Block 1 Lot 2',
            'qr_code' => 'QR-HO-002',
            'current_status' => 'OUT',
            'status' => 'active',
            'last_scan_time' => date('Y-m-d H:i:s', strtotime('-15 minutes')),
            'created_at' => date('Y-m-d')
        ]
    ];
    echo json_encode($sampleData);
}
?>
