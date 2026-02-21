<?php
/**
 * AJAX - Get Single Plan
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Plan.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode([]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    echo json_encode([]);
    exit;
}

$planModel = new Plan($pdo);
$plan = $planModel->getPlanById($_POST['id']);

echo json_encode($plan ?: []);
?>
