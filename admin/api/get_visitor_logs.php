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

    // System-only restriction: If no date filter is chosen, default to last 30 days
    // to keep the "Active Visitor Registry" clean and fast.
    if (empty($_GET['from']) && empty($_GET['to'])) {
        $where[] = "v.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }

    // Filter by visitor type
    if (!empty($_GET['type'])) {
        $where[] = "v.visitor_type = ?";
        $params[] = $_GET['type'];
    }

    // Filter by visitor status (Robust handling for current status from scans)
    if (!empty($_GET['status'])) {
        if ($_GET['status'] === 'INSIDE') {
            $where[] = "(v.status = 'INSIDE' OR v.current_status = 'IN' OR v.current_status = 'INSIDE') AND (v.current_status != 'OUT' AND v.status != 'OUT')";
        } else if ($_GET['status'] === 'OUT') {
            $where[] = "(v.status = 'OUT' OR v.current_status = 'OUT' OR v.current_status = 'OUTSIDE')";
        }
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
