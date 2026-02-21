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
    
    $data = [
        'title' => $_POST['title'] ?? '',
        'amount' => $_POST['amount'] ?? 0,
        'expense_date' => $_POST['expense_date'] ?? date('Y-m-d'),
        'category' => $_POST['category'] ?? 'Other',
        'description' => $_POST['description'] ?? ''
    ];
    
    // Validation
    if (empty($data['title']) || $data['amount'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Valid title and amount are required.']);
        exit;
    }
    
    if ($expenseModel->addExpense($data)) {
        echo json_encode(['success' => true, 'message' => 'Expense added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add expense.']);
    }
}
?>
