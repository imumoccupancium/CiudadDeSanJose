<?php
// We'll use a direct PDO connection without the config file to be sure
$host = 'localhost';
$db   = 'ciudad_de_san_jose';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
     $pdo = new PDO($dsn, $user, $pass);
     $stmt = $pdo->query("DESCIBE visitor_logs"); // typos expected, let's fix
     $stmt = $pdo->query("DESCRIBE visitor_logs");
     while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
         print_r($row);
     }
} catch (\PDOException $e) {
     echo "Error: " . $e->getMessage();
}
