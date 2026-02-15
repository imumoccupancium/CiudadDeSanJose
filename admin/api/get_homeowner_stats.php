<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    // 1. Total Residents (Homeowners + Family Members)
    $stmt = $pdo->query("SELECT (SELECT COUNT(*) FROM homeowners) + (SELECT COUNT(*) FROM family_members) as total");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 2. Active Households/Accounts (Main homeowners only)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM homeowners WHERE status = 'active'");
    $active = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 3. Currently Inside (Homeowners + Family Members)
    // We count only those whose status is 'IN'
    $stmt = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM homeowners WHERE current_status = 'IN' AND status = 'active') + 
            (SELECT COUNT(*) FROM family_members WHERE current_status = 'IN' AND access_status = 'active') as total_inside
    ");
    $inside = $stmt->fetch(PDO::FETCH_ASSOC)['total_inside'];
    
    // 4. Currently Outside 
    // We count active people who are 'OUT'
    $stmt = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM homeowners WHERE current_status = 'OUT' AND status = 'active') + 
            (SELECT COUNT(*) FROM family_members WHERE current_status = 'OUT' AND access_status = 'active') as total_outside
    ");
    $outside = $stmt->fetch(PDO::FETCH_ASSOC)['total_outside'];
    
    echo json_encode([
        'total' => $total,
        'active' => $active,
        'inside' => $inside,
        'outside' => $outside
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
