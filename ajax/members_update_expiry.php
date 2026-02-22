<?php
/**
 * AJAX Handler: Bulk update membership status
 * Checks all members and expires those with past end dates
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Member.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberModel = new Member($pdo);
    
    $updatedCount = $memberModel->updateAllExpiredMemberships();
    
    if ($updatedCount !== false) {
        echo json_encode([
            'success' => true, 
            'message' => $updatedCount > 0 
                ? "Success! $updatedCount member(s) were found expired and updated." 
                : "No members found with expired dates that were still marked active.",
            'updated_count' => $updatedCount
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'An error occurred while updating memberships.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
