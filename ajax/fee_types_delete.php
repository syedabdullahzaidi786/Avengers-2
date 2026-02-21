<?php
/**
 * AJAX - Delete Fee Type
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/FeeType.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$feeTypeModel = new FeeType($pdo);
$result = $feeTypeModel->deleteFeeType($_POST['id']);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Fee Type deleted successfully']);
}
else {
    echo json_encode(['success' => false, 'message' => 'Error deleting fee type']);
}
?>
