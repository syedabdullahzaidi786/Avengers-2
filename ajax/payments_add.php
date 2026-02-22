<?php
/**
 * AJAX - Add Payment
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

$memberId = $_POST['member_id'] ?? '';
$paymentMethod = $_POST['payment_method'] ?? '';
$paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
$description = $_POST['description'] ?? '';

// Check if we have multiple items
$items = isset($_POST['items']) ? json_decode($_POST['items'], true) : null;

$discountPercent = $_POST['discount_percent'] ?? 0;
$discountAmount = $_POST['discount_amount'] ?? 0;

// Generate shared receipt number
$receiptNumber = 'REC-' . date('YmdHis') . '-' . $memberId;

// Update description if discount applied
if ($discountPercent > 0) {
    if (!empty($description)) {
        $description .= "\n";
    }
    $description .= "(Discount Applied: " . $discountPercent . "% - Rs " . number_format($discountAmount, 2) . ")";
}

$successCount = 0;
$firstPaymentId = null;

try {
    $pdo->beginTransaction();

    if ($items && is_array($items) && count($items) > 0) {
        // Bulk insertion
        foreach ($items as $index => $item) {
            $feeTypeId = $item['fee_type_id'] ?? 1;
            $amount = $item['amount'] ?? 0;

            // Apply discount to the first valid item
            if ($index === 0 && $discountAmount > 0) {
                $amount = max(0, $amount - $discountAmount);
            }

            if ($amount > 0) {
                $result = $paymentModel->addPayment($memberId, $amount, $paymentMethod, $paymentDate, $description, $feeTypeId, $receiptNumber);
                if ($result) {
                    $successCount++;
                    if (!$firstPaymentId)
                        $firstPaymentId = $result;
                }
            }
        }
    }
    else {
        // Single insertion (backward compatibility)
        $feeTypeId = $_POST['fee_type_id'] ?? 1;
        $amount = $_POST['amount'] ?? '';

        // Validate single
        $errors = $paymentModel->validatePayment($memberId, $amount, $paymentMethod);
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            $pdo->rollBack();
            exit;
        }

        $result = $paymentModel->addPayment($memberId, $amount, $paymentMethod, $paymentDate, $description, $feeTypeId, $receiptNumber);
        if ($result) {
            $successCount++;
            $firstPaymentId = $result;
        }
    }

    if ($successCount > 0) {
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Payment recorded successfully', 'id' => $firstPaymentId]);
    }
    else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'No valid payments recorded']);
    }

}
catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
