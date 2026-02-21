<?php
/**
 * AJAX - Add New User
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($fullName) || empty($username) || empty($email) || empty($password)) {
        $response['message'] = 'All fields are required.';
    }
    else {
        $userModel = new User($pdo);

        if ($userModel->checkUserExists($username, $email)) {
            $response['message'] = 'Username or Email already exists.';
        }
        else {
            if ($userModel->createUser($username, $email, $password, $fullName)) {
                $response['success'] = true;
                $response['message'] = 'User added successfully.';
            }
            else {
                $response['message'] = 'Failed to add user.';
            }
        }
    }
}

echo json_encode($response);
?>
