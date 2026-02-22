<?php
/**
 * Direct Thermal Printing for Financial Reports
 * Uses mike42/escpos-php to print directly to a Windows USB thermal printer
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Payment.php';
require_once __DIR__ . '/../../models/Expense.php';
require_once __DIR__ . '/../../models/Trainer.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

requireLogin();

$paymentModel = new Payment($pdo);
$expenseModel = new Expense($pdo);
$trainerModel = new Trainer($pdo);

// Get selected period
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? 'all';

// Fetch data
if ($month === 'all') {
    $title = "ANNUAL REPORT - $year";
    $yearlyRevenue = $paymentModel->getYearlyRevenueBreakdown($year);
    $yearlyExpenses = $expenseModel->getMonthlyExpensesByYear($year);
    $trainerSummary = $trainerModel->getYearlyCommissionsSummary($year);

    $totalRevenue = array_sum(array_column($yearlyRevenue, 'total'));
    $totalExpenses = array_sum(array_column($yearlyExpenses, 'total'));
    $totalCommissions = array_sum(array_column($trainerSummary, 'total_commission'));
}
else {
    $monthsFull = ["JANUARY", "FEBRUARY", "MARCH", "APRIL", "MAY", "JUNE", "JULY", "AUGUST", "SEPTEMBER", "OCTOBER", "NOVEMBER", "DECEMBER"];
    $title = $monthsFull[$month - 1] . " REPORT - $year";
    $totalRevenue = $paymentModel->getMonthlyRevenue($month, $year);
    $totalExpenses = $expenseModel->getTotalExpenses($month, $year);
    $detailedPayments = $paymentModel->getMonthlyDetailedPayments($month, $year);
    $detailedExpenses = $expenseModel->getAllExpenses('', '', $month, $year);
    $trainerSummary = $trainerModel->getMonthlyCommissionsSummary($month, $year);
    $totalCommissions = array_sum(array_column($trainerSummary, 'total_commission'));
}

$netProfit = $totalRevenue - ($totalExpenses + $totalCommissions);

try {
    /* 1. INITIALIZE CONNECTOR - USING SHARE NAME BC88 */
    $printerName = "BC88";
    $connector = new WindowsPrintConnector($printerName);
    $printer = new Printer($connector);

    /* 2. SET FONT */
    $printer->selectPrintMode(Printer::MODE_FONT_A);

    /* 3. HEADER - AR FITNESS CLUB */
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_DOUBLE_HEIGHT);
    $printer->text("Avengers Gym & Fitness 2\n");
    $printer->selectPrintMode(Printer::MODE_FONT_A);
    $printer->setEmphasis(true);
    $printer->text($title . "\n");
    $printer->setEmphasis(false);
    $printer->text(date('d-M-Y h:i A') . "\n");
    $printer->feed();

    /* 4. FINANCIAL SUMMARY */
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("------------------------------------------------\n");
    $printer->setEmphasis(true);
    $printer->text("FINANCIAL SUMMARY\n");
    $printer->setEmphasis(false);
    $printer->text("------------------------------------------------\n");

    $printer->text(str_pad("Total Revenue:", 30) . str_pad("Rs " . number_format($totalRevenue, 0), 18, " ", STR_PAD_LEFT) . "\n");
    $printer->text(str_pad("Total Expenses:", 30) . str_pad("Rs " . number_format($totalExpenses, 0), 18, " ", STR_PAD_LEFT) . "\n");
    $printer->text(str_pad("Total Commissions:", 30) . str_pad("Rs " . number_format($totalCommissions, 0), 18, " ", STR_PAD_LEFT) . "\n");

    $printer->text("------------------------------------------------\n");
    $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
    $printer->setEmphasis(true);
    $printer->text(str_pad("NET PROFIT:", 12) . str_pad("Rs " . number_format($netProfit, 0), 12, " ", STR_PAD_LEFT) . "\n");
    $printer->selectPrintMode(Printer::MODE_FONT_A);
    $printer->setEmphasis(false);
    $printer->text("------------------------------------------------\n");
    $printer->feed();

    /* 5. DETAILED DATA (Monthly Only) */
    if ($month !== 'all') {
        $printer->setEmphasis(true);
        $printer->text("DETAILED PAYMENTS\n");
        $printer->setEmphasis(false);
        foreach ($detailedPayments as $p) {
            $name = substr($p['full_name'], 0, 30);
            $amount = "Rs " . number_format($p['amount'], 0);
            $printer->text(str_pad($name, 35) . str_pad($amount, 13, " ", STR_PAD_LEFT) . "\n");
        }
        $printer->feed();

        if (!empty($detailedExpenses)) {
            $printer->setEmphasis(true);
            $printer->text("DETAILED EXPENSES\n");
            $printer->setEmphasis(false);
            foreach ($detailedExpenses as $e) {
                $title_exp = substr($e['title'], 0, 30);
                $amount_exp = "Rs " . number_format($e['amount'], 0);
                $printer->text(str_pad($title_exp, 35) . str_pad($amount_exp, 13, " ", STR_PAD_LEFT) . "\n");
            }
            $printer->feed();
        }
    }

    /* 6. TRAINER COMMISSIONS SUMMARY */
    if (!empty($trainerSummary)) {
        $printer->setEmphasis(true);
        $printer->text("TRAINER COMMISSIONS\n");
        $printer->setEmphasis(false);
        foreach ($trainerSummary as $t) {
            if ($t['total_commission'] > 0) {
                $name_tr = substr($t['name'], 0, 30);
                $amount_tr = "Rs " . number_format($t['total_commission'], 0);
                $printer->text(str_pad($name_tr, 35) . str_pad($amount_tr, 13, " ", STR_PAD_LEFT) . "\n");
            }
        }
        $printer->text("------------------------------------------------\n");
    }

    /* 7. FOOTER */
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->feed();
    $printer->text("** End of Report **\n");
    $printer->text("Software Developed By: AR Cloud\n");
    $printer->text("Contact: +92 3313771572\n");

    /* 8. FINALIZE */
    $printer->feed(2);
    $printer->cut();
    $printer->close();

    echo "<script>alert('Report printed successfully!'); window.close();</script>";

} catch (Exception $e) {
    echo "<div style='color: red; padding: 25px; border: 3px solid red; font-family: sans-serif; background: #fff;'>";
    echo "<h3>Report Print Error</h3>";
    echo "<p>Ensure printer is shared as <strong>BC88</strong> and online.</p>";
    echo "<p><strong>Details:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<button onclick='window.close()'>Close</button>";
    echo "</div>";
}
