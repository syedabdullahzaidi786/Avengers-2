<?php
/**
 * Financial Report Thermal Print View
 * 80mm Thermal Printer Friendly
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Payment.php';
require_once __DIR__ . '/../../models/Expense.php';
require_once __DIR__ . '/../../models/Trainer.php';

requireLogin();

$paymentModel = new Payment($pdo);
$expenseModel = new Expense($pdo);
$trainerModel = new Trainer($pdo);

// Get selected period
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? 'all';

// Fetch data (same logic as finance.php)
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Report Print - <?php echo $title; ?></title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', monospace;
            background: #eee;
            padding: 10px;
            margin: 0;
        }
        .thermal-receipt {
            width: 70mm;
            margin: 0 auto;
            padding: 5px;
            background: white;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            font-size: 11px;
            line-height: 1.2;
            color: #000;
            font-weight: 600;
        }
        .center {
            text-align: center;
        }
        .logo {
            max-width: 80px;
            max-height: 80px;
            filter: grayscale(100%);
            margin-bottom: 5px;
        }
        .dashed-line {
            text-align: center;
            margin: 5px 0;
            border-top: 1px dashed #000;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }
        .bold {
            font-weight: 800;
        }
        .summary-box {
            border: 1px solid #000;
            padding: 5px;
            margin: 10px 0;
        }
        .amount-row {
            padding: 3px 0;
            border-bottom: 1px solid #000;
        }
        .profit-row {
            padding: 5px 0;
            border-top: 1px solid #000;
            font-size: 14px;
            text-align: center;
            font-weight: 800;
        }
        .section-title {
            text-align: center;
            font-size: 12px;
            margin: 8px 0 3px 0;
            text-decoration: underline;
        }
        @media print {
            body { background: white; padding: 0; margin: 0; }
            .thermal-receipt { box-shadow: none; width: 100%; margin: 0; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="thermal-receipt">
        <div class="center">
            <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="Logo" class="logo">
            <div class="bold" style="font-size: 16px;">Avengers Gym & Fitness 2</div>
            <div class="bold"><?php echo $title; ?></div>
            <div><?php echo date('d-M-Y h:i A'); ?></div>
        </div>

        <div class="dashed-line">------------------------------------------------</div>

        <div class="summary-box">
            <div class="row amount-row">
                <span>TOTAL REVENUE:</span>
                <span class="bold">Rs <?php echo number_format($totalRevenue, 0); ?></span>
            </div>
            <div class="row amount-row">
                <span>TOTAL EXPENSES:</span>
                <span class="bold">Rs <?php echo number_format($totalExpenses, 0); ?></span>
            </div>
            <div class="row amount-row">
                <span>COMMISSIONS:</span>
                <span class="bold">Rs <?php echo number_format($totalCommissions, 0); ?></span>
            </div>
            <div class="profit-row">
                NET PROFIT: Rs <?php echo number_format($netProfit, 0); ?>
            </div>
        </div>

        <?php if ($month !== 'all'): ?>
            <div class="section-title">DETAILED BREAKDOWN</div>
            
            <div class="bold" style="margin-top: 5px;">PAYMENTS:</div>
            <?php foreach ($detailedPayments as $p): ?>
                <div class="row" style="font-size: 10px;">
                    <span><?php echo substr(escapeHtml($p['full_name']), 0, 15); ?></span>
                    <span>Rs <?php echo number_format($p['amount'], 0); ?></span>
                </div>
            <?php
    endforeach; ?>

            <?php if (!empty($detailedExpenses)): ?>
                <div class="bold" style="margin-top: 10px;">EXPENSES:</div>
                <?php foreach ($detailedExpenses as $e): ?>
                    <div class="row" style="font-size: 10px;">
                        <span><?php echo substr(escapeHtml($e['title']), 0, 15); ?></span>
                        <span style="color:red">Rs <?php echo number_format($e['amount'], 0); ?></span>
                    </div>
                <?php
        endforeach; ?>
            <?php
    endif; ?>
        <?php
endif; ?>

        <div class="section-title">TRAINER EARNINGS</div>
        <?php foreach ($trainerSummary as $t):
    if ($t['total_commission'] > 0): ?>
            <div class="row">
                <span><?php echo escapeHtml($t['name']); ?>:</span>
                <span class="bold">Rs <?php echo number_format($t['total_commission'], 0); ?></span>
            </div>
        <?php
    endif;
endforeach; ?>

        <div class="dashed-line">------------------------------------------------</div>
        
        <div class="center" style="font-size: 10px; margin-top: 10px;">
            ** End of Report **<br>
            Software Design & Developed By: AR Cloud<br>
            Contact: +92 3313771572
        </div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.location.href='print_report_direct.php?year=<?php echo $year; ?>&month=<?php echo $month; ?>'" style="padding: 10px 20px; font-size: 14px; cursor: pointer; background: #28a745; color: white; border: none; border-radius: 4px;">
            ⚡ Direct Thermal Print
        </button>
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; margin-left: 10px;">
            🖨️ Browser Print
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; margin-left: 10px;">
            ✕ Close
        </button>
    </div>
</body>
</html>
