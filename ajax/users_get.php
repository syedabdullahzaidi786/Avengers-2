<?php
/**
 * AJAX - Get All Users
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

$userModel = new User($pdo);
$users = $userModel->getAllUsers();

echo json_encode($users);
?>
