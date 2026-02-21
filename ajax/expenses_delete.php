<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Expense.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expenseModel = new Expense($pdo);
    $id = $_POST['id'] ?? 0;
    
    if ($expenseModel->deleteExpense($id)) {
        echo json_encode(['success' => true, 'message' => 'Expense deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete expense.']);
    }
}
?>
