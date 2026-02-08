<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$response = ['success' => false, 'message' => '', 'tables' => []];

try {
    $sql = file_get_contents(__DIR__ . '/family_members_schema.sql');
    
    // Remove delimiter commands
    $sql = preg_replace('/DELIMITER\s+\$\$/i', '', $sql);
    $sql = preg_replace('/DELIMITER\s+;/i', '', $sql);
    $sql = str_replace('$$', ';', $sql);
    
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        try {
            $pdo->exec($statement);
            
            if (stripos($statement, 'CREATE TABLE') !== false) {
                if (stripos($statement, 'family_members') !== false && stripos($statement, 'family_member_logs') === false) {
                    $response['tables'][] = 'family_members';
                } elseif (stripos($statement, 'family_member_logs') !== false) {
                    $response['tables'][] = 'family_member_logs';
                }
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') === false) {
                throw $e;
            }
        }
    }
    
    $response['success'] = true;
    $response['message'] = 'Database tables created successfully!';
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
