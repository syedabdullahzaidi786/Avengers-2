<?php
/**
 * AJAX - Add Plan
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Plan.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$planModel = new Plan($pdo);

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

// Add plan
$result = $planModel->createPlan($name, $duration, $price, $description);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Plan added successfully', 'id' => $result]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error adding plan']);
}
?>
