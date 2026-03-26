<?php
// Automatic environment detection
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$is_local = ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, '192.168.') === 0);

if ($is_local) {
    // LOCAL SETTINGS (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'ciudad_de_san_jose');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('BASE_URL', 'http://localhost/CiudadDeSanJose/');
} else {
    // PRODUCTION SETTINGS (CPANEL)
    // NOTE: Replace DB_USER and DB_PASS with your actual CPanel DB credentials
    define('DB_HOST', 'localhost'); 
    define('DB_NAME', 'ciudonnv_ciudad_de_san_jose');
    define('DB_USER', 'ciudonnv_ciudaddesanjose');
    define('DB_PASS', '5086795Gkt!');
    define('BASE_URL', 'https://ciudaddesanjose.site/');
}

define('APP_SECRET', 'csj_secure_gate_2026_!@#');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // Don't show full error details in production for security
    if ($is_local) {
        die("Database connection failed: " . $e->getMessage());
    } else {
        error_log("DB Connection Error: " . $e->getMessage());
        die("System is temporarily offline. Please contact the administrator.");
    }
}
?>

