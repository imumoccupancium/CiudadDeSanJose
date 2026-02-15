<?php
require_once __DIR__ . '/config/database.php';
$pass = password_hash('security123', PASSWORD_DEFAULT);
try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, name, email, role, status) VALUES ('security', ?, 'Security Guard', 'security@example.com', 'guard', 'active')");
    $stmt->execute([$pass]);
    echo "Security user created.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
