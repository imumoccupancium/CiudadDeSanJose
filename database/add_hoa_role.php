<?php
require_once __DIR__ . '/../config/database.php';

try {
    // Check if 'hoa' role already exists in the enum
    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'role'");
    $stmt->execute();
    $row = $stmt->fetch();
    
    if ($row && strpos($row['Type'], "'hoa'") === false) {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'guard', 'supervisor', 'hoa') DEFAULT 'guard'");
        echo "Successfully added 'hoa' role to users table.\n";
    } else {
        echo "'hoa' role already exists or users table not found.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
