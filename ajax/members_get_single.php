<?php
/**
 * AJAX - Get Single Member
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Member.php';

header('Content-Type: application/json');

// Check if user is authenticated
if (!isAuthenticated()) {
    echo json_encode([]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    echo json_encode([]);
    exit;
}

$memberModel = new Member($pdo);
$member = $memberModel->getMemberById($_POST['id']);

echo json_encode($member ?: []);
?>
