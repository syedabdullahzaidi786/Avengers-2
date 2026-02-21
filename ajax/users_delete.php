<?php
/**
 * AJAX - Delete User
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';

    if (empty($id)) {
        $response['message'] = 'User ID is required.';
    }
    else {
        $userModel = new User($pdo);
        if ($userModel->deleteUser($id)) {
            $response['success'] = true;
            $response['message'] = 'User deleted successfully.';
        }
        else {
            $response['message'] = 'Failed to delete user.';
        }
    }
}

echo json_encode($response);
?>
