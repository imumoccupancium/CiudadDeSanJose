<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

// Lazy migration check
try {
    $pdo->query("SELECT 1 FROM homeowners LIMIT 1");
} catch (Exception $e) {
    // If table doesn't exist, we can't really do much here but return empty
    echo json_encode(['success' => true, 'data' => [], 'message' => 'Homeowners table not found']);
    exit();
}

try {
    if (empty($searchTerm)) {
        // Return all homeowners if no search term
        $stmt = $pdo->query("
            SELECT 
                h.id,
                h.homeowner_id,
                h.name,
                h.address
            FROM homeowners h
            WHERE h.status = 'active'
            ORDER BY h.name ASC
            LIMIT 20
        ");
    } else {
        // Search by name or address using unique parameter names
        $stmt = $pdo->prepare("
            SELECT 
                h.id,
                h.homeowner_id,
                h.name,
                h.address
            FROM homeowners h
            WHERE h.status = 'active'
            AND (
                h.name LIKE :s1 
                OR h.address LIKE :s2
                OR h.homeowner_id LIKE :s3
            )
            ORDER BY h.name ASC
            LIMIT 20
        ");
        $searchParam = "%{$searchTerm}%";
        $stmt->bindValue(':s1', $searchParam);
        $stmt->bindValue(':s2', $searchParam);
        $stmt->bindValue(':s3', $searchParam);
        $stmt->execute();
    }
    
    $homeowners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'success' => true,
        'data' => $homeowners
    ]);
    
} catch (PDOException $e) {
    // Return sample data if database not set up yet
    $sampleData = [
        [
            'id' => '1',
            'homeowner_id' => 'HO-001',
            'name' => 'Juan Dela Cruz',
            'address' => 'Block 1 Lot 1'
        ],
        [
            'id' => '2',
            'homeowner_id' => 'HO-002',
            'name' => 'Maria Santos',
            'address' => 'Block 1 Lot 2'
        ],
        [
            'id' => '3',
            'homeowner_id' => 'HO-003',
            'name' => 'Ricardo Dalisay',
            'address' => 'Block 5 Lot 12'
        ],
        [
            'id' => '4',
            'homeowner_id' => 'HO-004',
            'name' => 'Ana Reyes',
            'address' => 'Block 2 Lot 5'
        ],
        [
            'id' => '5',
            'homeowner_id' => 'HO-005',
            'name' => 'Pedro Garcia',
            'address' => 'Block 3 Lot 8'
        ]
    ];
    
    // Filter sample data based on search term
    if (!empty($searchTerm)) {
        $sampleData = array_filter($sampleData, function($item) use ($searchTerm) {
            return stripos($item['name'], $searchTerm) !== false 
                || stripos($item['address'], $searchTerm) !== false
                || stripos($item['homeowner_id'], $searchTerm) !== false;
        });
        $sampleData = array_values($sampleData); // Re-index array
    }
    
    echo json_encode([
        'success' => true,
        'data' => $sampleData
    ]);
}
?>
