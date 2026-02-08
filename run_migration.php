<?php
require_once 'config/database.php';

try {
    $sql = file_get_contents('database/visitor_logs_schema.sql');
    $pdo->exec($sql);
    echo "Successfully created visitor_logs table.\n";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'visitor_logs'");
    if ($stmt->fetch()) {
        echo "Table visitor_logs confirmed.\n";
    } else {
        echo "Table visitor_logs NOT found!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
