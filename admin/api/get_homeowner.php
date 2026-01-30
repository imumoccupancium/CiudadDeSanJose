<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;

try {
    $stmt = $pdo->prepare("
        SELECT 
            h.*,
            DATE_FORMAT(h.last_scan_time, '%Y-%m-%d %H:%i:%s') as last_scan_time
        FROM homeowners h
        WHERE h.id = ?
    ");
    $stmt->execute([$id]);
    $homeowner = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($homeowner) {
        echo json_encode($homeowner);
    } else {
        echo json_encode(['error' => 'Homeowner not found']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
