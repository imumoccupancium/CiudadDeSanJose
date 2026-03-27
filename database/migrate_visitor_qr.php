<?php
require_once 'config/database.php';

try {
    echo "Starting Visitor Logs Migration...\n";

    // Add qr_token, qr_expiry, qr_last_generated to visitor_logs
    $pdo->exec("ALTER TABLE visitor_logs 
                ADD COLUMN qr_token VARCHAR(100) NULL AFTER guard_id,
                ADD COLUMN qr_expiry DATETIME NULL AFTER qr_token,
                ADD COLUMN qr_last_generated DATETIME NULL AFTER qr_expiry,
                ADD COLUMN last_scan_time DATETIME NULL AFTER qr_last_generated,
                ADD COLUMN current_status ENUM('IN', 'OUT') DEFAULT 'IN' AFTER status");
    
    // Update existing records to match the ENUM current_status if 'INSIDE' was used
    $pdo->exec("UPDATE visitor_logs SET current_status = 'IN' WHERE status = 'INSIDE'");
    $pdo->exec("UPDATE visitor_logs SET current_status = 'OUT' WHERE status = 'OUT'");

    echo "Migration completed successfully!\n";
    echo "New columns added: qr_token, qr_expiry, qr_last_generated, last_scan_time, current_status.\n";

} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Migration already applied or columns exist.\n";
    } else {
        echo "Migration failed: " . $e->getMessage() . "\n";
    }
}
?>
