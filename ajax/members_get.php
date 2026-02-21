<?php
/**
 * AJAX - Get Members List
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Member.php';

header('Content-Type: application/json');

// Check if user is authenticated
if (!isAuthenticated()) {
    echo json_encode([]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([]);
    exit;
}

$memberModel = new Member($pdo);

$search = $_POST['search'] ?? '';
$status = $_POST['status'] ?? '';
$page = $_POST['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get all members first (simple search - can be optimized)
$allMembers = $memberModel->getAllMembers(1000, 0, $search);

// Filter by status if specified
if (!empty($status)) {
    $allMembers = array_filter($allMembers, function($member) use ($status) {
        return $member['status'] === $status;
    });
}

// Reindex array
$allMembers = array_values($allMembers);

// Return JSON
echo json_encode($allMembers);
?>
