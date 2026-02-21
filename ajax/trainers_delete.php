<?php
/**
 * AJAX - Delete Trainer
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Trainer.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    if (empty($_POST['id'])) {
        throw new Exception('Trainer ID is required');
    }
    
    $trainerModel = new Trainer($pdo);
    $result = $trainerModel->deleteTrainer($_POST['id']);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Trainer deleted successfully']);
    } else {
        throw new Exception('Failed to delete trainer');
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
