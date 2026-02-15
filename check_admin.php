<?php
require_once __DIR__ . '/config/database.php';
$stmt = $pdo->prepare("SELECT username, password FROM users WHERE username = 'admin'");
$stmt->execute();
$admin = $stmt->fetch();
if ($admin) {
    echo "User exists. Hash: " . $admin['password'] . "\n";
    echo "Verifying 'admin123': " . (password_verify('admin123', $admin['password']) ? 'MATCH' : 'NO MATCH') . "\n";
    echo "Verifying 'password': " . (password_verify('password', $admin['password']) ? 'MATCH' : 'NO MATCH') . "\n";
} else {
    echo "Admin user not found in database.\n";
}
?>
