<?php
require_once 'config/database.php';

try {
    // Check if table exists, if not create it
    $sql = file_get_contents('database/visitor_logs_schema.sql');
    $pdo->exec($sql);
    echo "Table visitor_logs ensured.\n";

    // Insert sample data if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM visitor_logs");
    if ($stmt->fetchColumn() == 0) {
        $sampleData = [
            ['Maria Santos', 'Personal', null, 1, 'Juan Dela Cruz', 'Main Gate', 'Lunch Visit', 1],
            ['John Doe', 'Professional', 'PLDT', 2, 'Maria Santos', 'North Gate', 'Internet Repair', 1],
            ['GrabFood', 'Service', 'Grab', 3, 'Ricardo Dalisay', 'Main Gate', 'Food Delivery', 1]
        ];

        $stmt = $pdo->prepare("
            INSERT INTO visitor_logs (
                visitor_name, visitor_type, company, homeowner_id, person_to_visit, gate, purpose, guard_id, time_in, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW() - INTERVAL 1 HOUR, 'INSIDE')
        ");

        foreach ($sampleData as $row) {
            $stmt->execute($row);
        }
        echo "Sample visitor logs inserted.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
