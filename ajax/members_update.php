<?php
/**
 * AJAX - Update Member
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

// Get existing member to preserve picture if not updating
$existingMember = $memberModel->getMemberById($id);
if (!$existingMember) {
    echo json_encode(['success' => false, 'message' => 'Member not found']);
    exit;
}

$data = [
    'full_name' => $_POST['full_name'] ?? '',
    'phone' => $_POST['phone'] ?? '',
    'gender' => $_POST['gender'] ?? 'Male',
    'plan_id' => $_POST['plan_id'] ?? '',
    'trainer_id' => $_POST['trainer_id'] ?? null,
    'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
    'profile_picture' => $existingMember['profile_picture']
];

// Handle file upload
if (!empty($_FILES['profile_picture']['name'])) {
    $uploadDir = __DIR__ . '/../uploads/members/';

    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $file = $_FILES['profile_picture'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (in_array($fileExt, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
        $fileName = uniqid() . '.' . $fileExt;
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Delete old picture if exists
            if ($existingMember['profile_picture'] && file_exists(__DIR__ . '/../' . $existingMember['profile_picture'])) {
                unlink(__DIR__ . '/../' . $existingMember['profile_picture']);
            }
            $data['profile_picture'] = 'uploads/members/' . $fileName;
        }
    }
}

// Validate
$errors = $memberModel->validateMember($data);
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Update member
$result = $memberModel->updateMember($id, $data);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Member updated successfully']);
}
else {
    echo json_encode(['success' => false, 'message' => 'Error updating member']);
}
?>
