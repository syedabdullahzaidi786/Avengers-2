<?php
/**
 * AJAX - Update Payment
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
$memberId = $_POST['member_id'] ?? '';
$amount = $_POST['amount'] ?? '';
$paymentMethod = $_POST['payment_method'] ?? '';
$paymentDate = $_POST['payment_date'] ?? '';
$description = $_POST['description'] ?? '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Payment ID required']);
    exit;
}

// Validate
$errors = $paymentModel->validatePayment($memberId, $amount, $paymentMethod);
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

$data = [
    'member_id' => $memberId,
    'fee_type_id' => $_POST['fee_type_id'] ?? 1,
    'amount' => $amount,
    'payment_method' => $paymentMethod,
    'payment_date' => $paymentDate,
    'description' => $description
];

if ($paymentModel->updatePayment($id, $data)) {
    echo json_encode(['success' => true, 'message' => 'Payment updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating payment']);
}
?>
