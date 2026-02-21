<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Expense.php';
require_once __DIR__ . '/../models/Trainer.php';

$paymentModel = new Payment($pdo);
$expenseModel = new Expense($pdo);
$trainerModel = new Trainer($pdo);

$month = 2; // February
$year = 2026;

echo "--- MONTHLY DATA VERIFICATION FOR $month/$year ---\n";

$payments = $paymentModel->getMonthlyDetailedPayments($month, $year);
$expenses = $expenseModel->getAllExpenses('', '', $month, $year);
$commissions = $trainerModel->getMonthlyCommissionsSummary($month, $year);

$totalRev = array_sum(array_column($payments, 'amount'));
$totalExp = array_sum(array_column($expenses, 'amount'));
$totalComm = array_sum(array_column($commissions, 'total_commission'));

echo "Calculated Revenue: Rs " . number_format($totalRev, 0) . "\n";
echo "Calculated Expenses: Rs " . number_format($totalExp, 0) . "\n";
echo "Calculated Commissions: Rs " . number_format($totalComm, 0) . "\n";
echo "NET PROFIT: Rs " . number_format($totalRev - ($totalExp + $totalComm), 0) . "\n";

echo "\n--- SAMPLE PAYMENTS ---\n";
if (!empty($payments)) {
    echo "Found " . count($payments) . " payments.\n";
}
else {
    echo "No payments found.\n";
}

echo "\n--- SAMPLE EXPENSES ---\n";
if (!empty($expenses)) {
    echo "Found " . count($expenses) . " expenses.\n";
}
else {
    echo "No expenses found.\n";
}
?>
