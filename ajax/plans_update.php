<?php
/**
 * AJAX - Update Plan
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
$name = $_POST['name'] ?? '';
$duration = $_POST['duration'] ?? '';
$price = $_POST['price'] ?? '';
$description = $_POST['description'] ?? '';

// Validate
$errors = $planModel->validatePlan($name, $duration, $price);
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Update plan
$result = $planModel->updatePlan($id, $name, $duration, $price, $description);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Plan updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating plan']);
}
?>
