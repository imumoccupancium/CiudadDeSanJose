<?php
// ============================================================
// get_token_cache.php
// Called by scanner_relay.py to download a local copy of all
// valid QR tokens for offline fallback operation.
// ============================================================
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    // --- Homeowners (active, not expired) ---
    $stmt1 = $pdo->prepare("
        SELECT 
            id            AS user_internal_id,
            homeowner_id  AS homeowner_id,
            name          AS name,
            qr_token      AS qr_token,
            current_status,
            'homeowner'   AS user_type
        FROM homeowners
        WHERE status = 'active'
          AND qr_token IS NOT NULL
          AND qr_token != ''
          AND (qr_expiry IS NULL OR qr_expiry > NOW())
    ");
    $stmt1->execute();
    $homeowners = $stmt1->fetchAll();

    // --- Family Members (active, not expired) ---
    $stmt2 = $pdo->prepare("
        SELECT 
            id            AS user_internal_id,
            homeowner_id  AS homeowner_id,
            full_name     AS name,
            qr_token      AS qr_token,
            current_status,
            'family'      AS user_type
        FROM family_members
        WHERE access_status = 'active'
          AND qr_token IS NOT NULL
          AND qr_token != ''
          AND (qr_expiry IS NULL OR qr_expiry > NOW())
    ");
    $stmt2->execute();
    $family_members = $stmt2->fetchAll();

    $all_tokens = array_merge($homeowners, $family_members);

    // --- Visitors (currently inside, not expired) ---
    $stmt3 = $pdo->prepare("
        SELECT 
            id            AS user_internal_id,
            homeowner_id  AS homeowner_id,
            visitor_name  AS name,
            qr_token      AS qr_token,
            current_status,
            'visitor'     AS user_type
        FROM visitor_logs
        WHERE current_status = 'IN'
          AND qr_token IS NOT NULL
          AND qr_token != ''
          AND (qr_expiry IS NULL OR qr_expiry > NOW())
    ");
    $stmt3->execute();
    $visitors = $stmt3->fetchAll();

    $all_tokens = array_merge($all_tokens, $visitors);

    echo json_encode([
        'success' => true,
        'count' => count($all_tokens),
        'tokens' => $all_tokens,
        'synced_at' => date('Y-m-d H:i:s')
    ]);

}
catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
}
?>
