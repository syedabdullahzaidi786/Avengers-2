<?php
/**
 * AJAX - Delete Plan
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Plan.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$planModel = new Plan($pdo);
$id = $_POST['id'];

// Delete plan (with validation)
$result = $planModel->deletePlan($id);

echo json_encode($result);
?>
