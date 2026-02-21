<?php
/**
 * AJAX - Update User
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($id) || empty($fullName) || empty($username) || empty($email)) {
        $response['message'] = 'Required fields are missing.';
    }
    else {
        $userModel = new User($pdo);

        if ($userModel->checkUserExists($username, $email, $id)) {
            $response['message'] = 'Username or Email already exists.';
        }
        else {
            if ($userModel->updateUser($id, $username, $email, $fullName, empty($password) ? null : $password)) {
                $response['success'] = true;
                $response['message'] = 'User updated successfully.';
            }
            else {
                $response['message'] = 'Failed to update user.';
            }
        }
    }
}

echo json_encode($response);
?>
