<?php
/**
 * AJAX Handler: Get Member by ID
 * Fetches member details for renewal purposes
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Member.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    try {
        // Fetch member with plan details
        $stmt = $pdo->prepare("
            SELECT m.*, p.name as plan_name, p.price, p.duration,
                   t.name as trainer_name, t.fee as trainer_fee
            FROM members m 
            JOIN membership_plans p ON m.plan_id = p.id 
            LEFT JOIN trainers t ON m.trainer_id = t.id
            WHERE m.id = ?
        ");
        $stmt->execute([$id]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($member) {
            echo json_encode(['success' => true, 'data' => $member]);
        }
        else {
            echo json_encode(['success' => false, 'message' => 'Member not found with ID: ' . str_pad($id, 6, "0", STR_PAD_LEFT)]);
        }
    }
    catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
