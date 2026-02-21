<?php
/**
 * AJAX - Check Previous Payments
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$memberId = $_POST['member_id'] ?? null;

if (!$memberId) {
    echo json_encode(['success' => false, 'message' => 'Member ID required']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM payments WHERE member_id = ?');
    $stmt->execute([$memberId]);
    $count = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'has_payments' => ($count > 0)
    ]);
}
catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
