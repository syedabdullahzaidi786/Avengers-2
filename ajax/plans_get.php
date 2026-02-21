<?php
/**
 * AJAX - Get Plans
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Plan.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode([]);
    exit;
}

$planModel = new Plan($pdo);
$plans = $planModel->getAllPlans(false);

echo json_encode($plans);
?>
