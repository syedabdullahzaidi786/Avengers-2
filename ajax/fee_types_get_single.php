<?php
/**
 * AJAX - Get Single Fee Type
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/FeeType.php';

header('Content-Type: application/json');

if (empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID required']);
    exit;
}

$feeTypeModel = new FeeType($pdo);
$feeType = $feeTypeModel->getFeeTypeById($_POST['id']);

if ($feeType) {
    echo json_encode($feeType);
}
else {
    echo json_encode(['success' => false, 'message' => 'Fee Type not found']);
}
?>
