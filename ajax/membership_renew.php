<?php
/**
 * AJAX Handler: Membership Renewal
 * Processes payment, updates dates and status
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../models/Payment.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = intval($_POST['member_id']);
    $paymentMethod = $_POST['payment_method'] ?? 'cash';
    $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
    $items = isset($_POST['items']) ? json_decode($_POST['items'], true) : [];
    $discountPercent = floatval($_POST['discount_percent'] ?? 0);
    $discountAmount = floatval($_POST['discount_amount'] ?? 0);

    $paymentModel = new Payment($pdo);

    try {
        $pdo->beginTransaction();

        // 1. Get member and plan details
        $stmt = $pdo->prepare("
            SELECT m.plan_id, p.duration, p.price 
            FROM members m 
            JOIN membership_plans p ON m.plan_id = p.id 
            WHERE m.id = ?
        ");
        $stmt->execute([$memberId]);
        $details = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$details) {
            throw new Exception("Member or plan not found.");
        }

        // 2. Calculate new dates
        $duration = intval($details['duration']);
        $endDate = date('Y-m-d', strtotime($paymentDate . " + " . $duration . " days"));

        // 3. Update Member record
        $updateStmt = $pdo->prepare("
            UPDATE members 
            SET start_date = ?, end_date = ?, status = 'active', updated_at = NOW() 
            WHERE id = ?
        ");
        $updateStmt->execute([$paymentDate, $endDate, $memberId]);

        // 4. Generate Shared Receipt Number
        $receiptNumber = 'REC-' . date('YmdHis') . '-' . $memberId;

        // 5. Build Description
        $description = "Membership Renewal (" . $paymentDate . " to " . $endDate . ")";
        if ($discountPercent > 0) {
            $description .= " (Discount Applied: " . $discountPercent . "% - Rs " . number_format($discountAmount, 2) . ")";
        }

        // 6. Record Payments
        $firstPaymentId = null;
        $totalProcessed = 0;

        foreach ($items as $index => $item) {
            $feeTypeId = intval($item['fee_type_id']);
            $amount = floatval($item['amount']);

            // Apply discount to the first valid item (usually Membership Fee)
            if ($index === 0 && $discountAmount > 0) {
                $amount = max(0, $amount - $discountAmount);
            }

            if ($amount > 0) {
                $id = $paymentModel->addPayment(
                    $memberId, 
                    $amount, 
                    $paymentMethod, 
                    $paymentDate, 
                    $description, 
                    $feeTypeId, 
                    $receiptNumber
                );
                
                if (!$firstPaymentId) $firstPaymentId = $id;
                $totalProcessed += $amount;
            }
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Membership renewed successfully!',
            'receipt' => [
                'id' => $firstPaymentId,
                'receipt_number' => $receiptNumber,
                'amount' => $totalProcessed + $discountAmount, // Shows original total on receipt logic if needed
                'date' => $paymentDate,
                'member_id' => $memberId
            ]
        ]);

    }
    catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
