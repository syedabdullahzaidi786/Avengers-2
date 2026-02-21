<?php
/**
 * AJAX - Get Fee Types
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/FeeType.php';

header('Content-Type: application/json');

// Check auth (assuming valid session)
// if (!isAuthenticated()) ... 

$feeTypeModel = new FeeType($pdo);
$feeTypes = $feeTypeModel->getAllFeeTypes();

echo json_encode($feeTypes);
?>
