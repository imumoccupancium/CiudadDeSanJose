<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

// Lazy migration check
try {
    $pdo->query("SELECT 1 FROM visitor_logs LIMIT 1");
}
catch (Exception $e) {
    $sql = file_get_contents('../../database/visitor_logs_schema.sql');
    $pdo->exec($sql);
}

try {
    $where = [];
    $params = [];

    // Filter by visitor type
    if (!empty($_GET['type'])) {
        $where[] = "v.visitor_type = ?";
        $params[] = $_GET['type'];
    }

    // Filter by visitor status (INSIDE/OUT)
    if (!empty($_GET['status'])) {
        $where[] = "v.status = ?";
        $params[] = $_GET['status'];
    }

    // Filter by date range (inclusive)
    if (!empty($_GET['from'])) {
        $where[] = "DATE(v.created_at) >= ?";
        $params[] = $_GET['from'];
    }
    if (!empty($_GET['to'])) {
        $where[] = "DATE(v.created_at) <= ?";
        $params[] = $_GET['to'];
    }

    $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $stmt = $pdo->prepare("
        SELECT 
            v.*,
            h.name as homeowner_name,
            h.address as homeowner_address,
            DATE_FORMAT(v.time_in, '%h:%i %p') as time_in_fmt,
            DATE_FORMAT(v.time_out, '%h:%i %p') as time_out_fmt,
            DATE_FORMAT(v.created_at, '%M %d, %Y') as date_fmt
        FROM visitor_logs v
        JOIN homeowners h ON v.homeowner_id = h.id
        $whereSql
        ORDER BY v.created_at DESC
        LIMIT 200
    ");

    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $logs
    ]);

}
catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>
