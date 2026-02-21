<?php
/**
 * AJAX - Delete Payment
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Payment.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$paymentModel = new Payment($pdo);
$id = $_POST['id'] ?? '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Payment ID required']);
    exit;
}

if ($paymentModel->deletePayment($id)) {
    echo json_encode(['success' => true, 'message' => 'Payment deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting payment']);
}
?>
