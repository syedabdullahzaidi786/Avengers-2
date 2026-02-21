<?php
/**
 * AJAX - Update Fee Type
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/FeeType.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$feeTypeModel = new FeeType($pdo);

$id = $_POST['id'];
$name = $_POST['name'] ?? '';
$defaultAmount = $_POST['default_amount'] ?? 0;

// Validate
$errors = $feeTypeModel->validateFeeType($name, $defaultAmount);
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

$result = $feeTypeModel->updateFeeType($id, $name, $defaultAmount);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Fee Type updated successfully']);
}
else {
    echo json_encode(['success' => false, 'message' => 'Error updating fee type']);
}
?>
