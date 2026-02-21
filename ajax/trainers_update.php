<?php
/**
 * AJAX - Update Trainer
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Trainer.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $trainerModel = new Trainer($pdo);

    // Validate inputs
    if (empty($_POST['id'])) {
        throw new Exception('Trainer ID is required');
    }

    if (empty($_POST['name'])) {
        throw new Exception('Trainer name is required');
    }

    if (empty($_POST['phone'])) {
        throw new Exception('Phone number is required');
    }

    $data = [
        'name' => trim($_POST['name']),
        'phone' => trim($_POST['phone']),
        'specialization' => isset($_POST['specialization']) ? trim($_POST['specialization']) : '',
        'commission_rate' => isset($_POST['commission_rate']) ? floatval($_POST['commission_rate']) : 80.00,
        'fee' => isset($_POST['fee']) ? floatval($_POST['fee']) : 0.00
    ];

    $result = $trainerModel->updateTrainer($_POST['id'], $data);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Trainer updated successfully']);
    }
    else {
        throw new Exception('Failed to update trainer');
    }


}
catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
