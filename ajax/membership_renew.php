<?php
/**
 * AJAX Handler: Membership Renewal
 * Processes payment, updates dates and status
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Member.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = intval($_POST['member_id']);
    $paymentMethod = $_POST['payment_method'] ?? 'cash';
    $amount = floatval($_POST['amount']);

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
        $startDate = date('Y-m-d');
        $duration = intval($details['duration']);
        $endDate = date('Y-m-d', strtotime("+$duration days"));

        // 3. Update Member record
        $updateStmt = $pdo->prepare("
            UPDATE members 
            SET start_date = ?, end_date = ?, status = 'active', updated_at = NOW() 
            WHERE id = ?
        ");
        $updateStmt->execute([$startDate, $endDate, $memberId]);

        // 4. Generate Receipt Number
        $receiptNumber = 'REC-' . time() . '-' . $memberId;

        // 5. Record Payment
        $payStmt = $pdo->prepare("
            INSERT INTO payments (member_id, amount, payment_method, payment_date, description, receipt_number) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $payStmt->execute([
            $memberId,
            $amount,
            $paymentMethod,
            $startDate,
            "Membership Renewal (" . $startDate . " to " . $endDate . ")",
            $receiptNumber
        ]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Membership renewed successfully!',
            'receipt' => [
                'receipt_number' => $receiptNumber,
                'amount' => $amount,
                'date' => $startDate,
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
