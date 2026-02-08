<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['homeowner_id'])) {
    echo json_encode(['error' => 'Homeowner ID is required']);
    exit;
}

$homeowner_id = $_GET['homeowner_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM family_members WHERE homeowner_id = ? ORDER BY created_at DESC");
    $stmt->execute([$homeowner_id]);
    $family_members = $stmt->fetchAll();
    
    // Format dates
    foreach ($family_members as &$member) {
        if ($member['qr_expiry']) {
            $member['qr_expiry_formatted'] = date('M d, Y', strtotime($member['qr_expiry']));
        }
    }
    
    echo json_encode($family_members);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
