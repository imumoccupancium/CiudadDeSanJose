<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    // 1. Get the latest homeowner_id from the database
    // We assume the format is 'HO-XXX' where XXX is numeric
    $stmt = $pdo->query("SELECT homeowner_id FROM homeowners ORDER BY id DESC LIMIT 1");
    $latest = $stmt->fetch();

    if ($latest) {
        $last_id = $latest['homeowner_id'];
        
        // Extract the numeric part (everything after '-')
        $parts = explode('-', $last_id);
        if (count($parts) > 1 && is_numeric($parts[1])) {
            $last_num = (int)$parts[1];
            $next_num = $last_num + 1;
        } else {
            // Fallback if formatting is weird
            $next_num = 1;
        }
    } else {
        // No homeowners yet
        $next_num = 1;
    }

    // Format with HO- prefix and 3-digit padding (e.g., HO-001)
    $next_id = 'HO-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);

    echo json_encode([
        'success' => true,
        'next_id' => $next_id
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
