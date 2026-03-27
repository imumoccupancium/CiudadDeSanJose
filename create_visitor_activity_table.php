<?php
require_once 'config/database.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS visitor_activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        visitor_id INT NOT NULL,
        homeowner_id INT NOT NULL,
        action ENUM('IN', 'OUT') NOT NULL,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        device_name VARCHAR(100) DEFAULT 'Main Gate Scanner',
        guard_id INT NULL,
        FOREIGN KEY (visitor_id) REFERENCES visitor_logs(id) ON DELETE CASCADE,
        FOREIGN KEY (homeowner_id) REFERENCES homeowners(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "Table visitor_activity_logs created successfully.\n";

    // Migrate existing data if any (optional, but good for consistency)
    // We can infer at least one IN/OUT from visitor_logs.
    // However, since visitor_logs only has one time_in and one time_out, we can only migrate those.
    
    $existingVisitors = $pdo->query("SELECT id, homeowner_id, current_status, time_in, time_out, last_scan_time, gate, guard_id FROM visitor_logs WHERE time_in IS NOT NULL")->fetchAll();
    
    foreach ($existingVisitors as $v) {
        if ($v['time_in']) {
            $stmt = $pdo->prepare("INSERT INTO visitor_activity_logs (visitor_id, homeowner_id, action, timestamp, device_name, guard_id) VALUES (?, ?, 'IN', ?, ?, ?)");
            $stmt->execute([$v['id'], $v['homeowner_id'], $v['time_in'], $v['gate'] ?: 'Main Gate Scanner', $v['guard_id']]);
        }
        if ($v['time_out']) {
            $stmt = $pdo->prepare("INSERT INTO visitor_activity_logs (visitor_id, homeowner_id, action, timestamp, device_name, guard_id) VALUES (?, ?, 'OUT', ?, ?, ?)");
            $stmt->execute([$v['id'], $v['homeowner_id'], $v['time_out'], $v['gate'] ?: 'Main Gate Scanner', $v['guard_id']]);
        }
    }
    echo "Migration completed.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
