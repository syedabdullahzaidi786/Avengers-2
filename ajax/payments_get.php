<?php
/**
 * AJAX - Get Payments
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Payment.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode([]);
    exit;
}

$paymentModel = new Payment($pdo);
$payments = $paymentModel->getAllPayments(100, 0);

echo json_encode($payments);
?>
