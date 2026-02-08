<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Lazy migration check
try {
    $pdo->query("SELECT 1 FROM visitor_logs LIMIT 1");
} catch (Exception $e) {
    $sql = file_get_contents('../../database/visitor_logs_schema.sql');
    $pdo->exec($sql);
}

try {
    $stmt = $pdo->query("
        SELECT 
            v.*,
            h.name as homeowner_name,
            h.address as homeowner_address,
            DATE_FORMAT(v.time_in, '%h:%i %p') as time_in_fmt,
            DATE_FORMAT(v.time_out, '%h:%i %p') as time_out_fmt,
            DATE_FORMAT(v.created_at, '%M %d, %Y') as date_fmt
        FROM visitor_logs v
        JOIN homeowners h ON v.homeowner_id = h.id
        ORDER BY v.created_at DESC
        LIMIT 100
    ");
    
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'success' => true,
        'data' => $logs
    ]);
    
} catch (PDOException $e) {
    // If table doesn't exist or other error, return empty but success structure for frontend compatibility
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>
