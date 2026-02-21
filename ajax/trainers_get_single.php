<?php
/**
 * AJAX - Get Single Trainer
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Trainer.php';

header('Content-Type: application/json');

if (isset($_POST['id'])) {
    $trainerModel = new Trainer($pdo);
    $trainer = $trainerModel->getTrainerById($_POST['id']);
    echo json_encode($trainer);
} else {
    echo json_encode(['error' => 'Trainer ID not provided']);
}
?>
