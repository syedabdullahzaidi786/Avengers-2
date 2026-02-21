<?php
/**
 * AJAX - Add Fee Type
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/FeeType.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$feeTypeModel = new FeeType($pdo);

$name = $_POST['name'] ?? '';
$defaultAmount = $_POST['default_amount'] ?? 0;

// Validate
$errors = $feeTypeModel->validateFeeType($name, $defaultAmount);
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

$result = $feeTypeModel->addFeeType($name, $defaultAmount);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Fee Type added successfully']);
}
else {
    echo json_encode(['success' => false, 'message' => 'Error adding fee type. Name might already exist.']);
}
?>
