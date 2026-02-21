<?php
/**
 * AJAX - Toggle Fee Type Commission Eligibility
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$id = $_POST['id'] ?? '';
$is_commissionable = $_POST['is_commissionable'] ?? 0;

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE fee_types SET is_commissionable = ? WHERE id = ?');
    $result = $stmt->execute([(int)$is_commissionable, $id]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Commission status updated successfully']);
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Failed to update commission status']);
    }
}
catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
