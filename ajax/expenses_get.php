<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Expense.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$expenseModel = new Expense($pdo);

$search = $_POST['search'] ?? '';
$category = $_POST['category'] ?? '';
$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';

$expenses = $expenseModel->getAllExpenses($search, $category, $month, $year);

echo json_encode($expenses);
?>
