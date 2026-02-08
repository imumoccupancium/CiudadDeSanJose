<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Member ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM family_members WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $member = $stmt->fetch();
    
    if ($member) {
        // Format expiry for datetime-local input
        if ($member['qr_expiry']) {
            $member['qr_expiry_input'] = date('Y-m-d\TH:i', strtotime($member['qr_expiry']));
        }
        echo json_encode($member);
    } else {
        echo json_encode(['error' => 'Member not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
