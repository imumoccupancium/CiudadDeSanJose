<?php
require_once '../../config/database.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS scan_alerts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        message TEXT NOT NULL,
        status ENUM('success', 'error') NOT NULL,
        is_shown TINYINT(1) DEFAULT 0,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (is_shown)
    )";
    
    $pdo->exec($sql);
    echo json_encode(['success' => true, 'message' => 'Scan alerts table created/already exists']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
