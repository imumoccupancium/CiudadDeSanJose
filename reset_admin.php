<?php
require_once __DIR__ . '/config/database.php';
$new_pass = password_hash('admin123', PASSWORD_DEFAULT);
try {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$new_pass]);
    echo "Admin password updated to 'admin123'.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
