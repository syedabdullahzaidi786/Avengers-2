<?php
/**
 * AJAX - Get All Trainers
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Trainer.php';

$trainerModel = new Trainer($pdo);

$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$trainers = $trainerModel->getAllTrainers($search);

echo json_encode($trainers);
?>
