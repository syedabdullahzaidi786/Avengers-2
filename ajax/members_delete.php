<?php
/**
 * AJAX - Delete Member
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Member.php';

header('Content-Type: application/json');

// Check if user is authenticated
if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$memberModel = new Member($pdo);
$id = $_POST['id'];

// Delete member
$result = $memberModel->deleteMember($id);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Member deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting member']);
}
?>
