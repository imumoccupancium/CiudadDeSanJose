<?php
$pdo = new PDO('mysql:host=localhost;dbname=ciudad_de_san_jose', 'root', '');
$stmt = $pdo->query("DESCRIBE visitor_logs");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . "\n";
}
