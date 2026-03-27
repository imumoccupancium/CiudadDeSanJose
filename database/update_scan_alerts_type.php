<?php
require_once '../config/database.php';
try {
    $pdo->exec("ALTER TABLE scan_alerts ADD COLUMN IF NOT EXISTS type VARCHAR(20) DEFAULT 'system' AFTER status");
    echo "Column 'type' added to scan_alerts successfully.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'type' already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
