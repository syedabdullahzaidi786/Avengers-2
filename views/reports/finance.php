<?php
/**
 * Finance Reports Page
 * Integrated Revenue, Expenses, and Profit analysis
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Payment.php';
require_once __DIR__ . '/../../models/Expense.php';
require_once __DIR__ . '/../../models/Trainer.php';

$paymentModel = new Payment($pdo);
$expenseModel = new Expense($pdo);
$trainerModel = new Trainer($pdo);

// Get selected period
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? 'all'; // 'all' or 1-12

// Fetch data based on selection
if ($month === 'all') {
    $yearlyRevenue = $paymentModel->getYearlyRevenueBreakdown($year);
    $yearlyExpenses = $expenseModel->getMonthlyExpensesByYear($year);
    $trainerSummary = $trainerModel->getYearlyCommissionsSummary($year);

    $totalRevenue = array_sum(array_column($yearlyRevenue, 'total'));
    $totalExpenses = array_sum(array_column($yearlyExpenses, 'total'));
    $totalCommissions = array_sum(array_column($trainerSummary, 'total_commission'));
}
else {
    $monthlyRevTotal = $paymentModel->getMonthlyRevenue($month, $year);
    $monthlyExpTotal = $expenseModel->getTotalExpenses($month, $year);
    $detailedPayments = $paymentModel->getMonthlyDetailedPayments($month, $year);
    $detailedExpenses = $expenseModel->getAllExpenses('', '', $month, $year);
    $trainerSummary = $trainerModel->getMonthlyCommissionsSummary($month, $year);

    $totalRevenue = $monthlyRevTotal;
    $totalExpenses = $monthlyExpTotal;
    $totalCommissions = array_sum(array_column($trainerSummary, 'total_commission'));
}

$netProfit = $totalRevenue - ($totalExpenses + $totalCommissions);

// Set page title
$pageTitle = 'Financial Reports';

// Months helper
$monthsFull = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
$monthsShort = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

// Start building page content
$pageContent = '
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-chart-pie"></i> Financial Reports</h1>
        <p>Comprehensive Revenue, Expenses, and Profit analysis</p>
    </div>
    <div class="d-flex gap-2">
        <select id="monthFilter" class="form-select w-auto" onchange="loadReport()">
            <option value="all" ' . ($month === 'all' ? 'selected' : '') . '>All Months</option>';
for ($i = 1; $i <= 12; $i++) {
    $selected = ($month == $i) ? 'selected' : '';
    $pageContent .= '<option value="' . $i . '" ' . $selected . '>' . $monthsFull[$i - 1] . '</option>';
}
$pageContent .= '
        </select>
        <select id="yearFilter" class="form-select w-auto" onchange="loadReport()">';

for ($y = date('Y'); $y >= 2023; $y--) {
    $selected = ($year == $y) ? 'selected' : '';
    $pageContent .= '<option value="' . $y . '" ' . $selected . '>' . $y . '</option>';
}

$pageContent .= '
        </select>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
        <button class="btn btn-dark" onclick="thermalPrint()">
            <i class="fas fa-receipt"></i> Thermal Print
        </button>
        <button class="btn btn-success" onclick="directThermalPrint()">
            <i class="fas fa-bolt"></i> Direct Print
        </button>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-success text-white shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase opacity-75 small fw-bold">Total Revenue</h6>
                        <h2 class="mb-0">Rs ' . number_format($totalRevenue, 0) . '</h2>
                    </div>
                    <div class="fs-1 opacity-25">
                        <i class="fas fa-arrow-trend-up"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase opacity-75 small fw-bold">Total Expenses</h6>
                        <h2 class="mb-0">Rs ' . number_format($totalExpenses, 0) . '</h2>
                    </div>
                    <div class="fs-1 opacity-25">
                        <i class="fas fa-arrow-trend-down"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase opacity-75 small fw-bold">Trainers Commission</h6>
                        <h2 class="mb-0">Rs ' . number_format($totalCommissions, 0) . '</h2>
                    </div>
                    <div class="fs-1 opacity-25">
                        <i class="fas fa-user-ninja"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card ' . ($netProfit >= 0 ? 'bg-primary' : 'bg-warning') . ' text-white shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase opacity-75 small fw-bold">Net Profit</h6>
                        <h2 class="mb-0">Rs ' . number_format($netProfit, 0) . '</h2>
                    </div>
                    <div class="fs-1 opacity-25">
                        <i class="fas fa-scale-balanced"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

if ($month === 'all') {
    // YEARLY OVERVIEW
    $pageContent .= '
    <div class="row mb-4">
        <!-- Financial Chart -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Monthly Trend - ' . $year . '</h5>
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="financeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Trainer Earnings -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Trainer Commissions - ' . $year . '</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Trainer</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>';

    if (empty($trainerSummary) || $totalCommissions == 0) {
        $pageContent .= '<tr><td colspan="2" class="text-center text-muted py-4">No commissions recorded</td></tr>';
    }
    else {
        foreach ($trainerSummary as $t) {
            if ($t['total_commission'] > 0) {
                $pageContent .= '
                                <tr>
                                    <td><strong>' . escapeHtml($t['name']) . '</strong><br><small class="text-muted">' . $t['payment_count'] . ' payments</small></td>
                                    <td class="text-end fw-bold">Rs ' . number_format($t['total_commission'], 0) . '</td>
                                </tr>';
            }
        }
    }

    $pageContent .= '
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">Monthly Revenue & Expenses Breakdown</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="financeTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Month</th>
                            <th>Revenue</th>
                            <th>Expenses</th>
                            <th class="pe-4">Revenue - Expenses</th>
                        </tr>
                    </thead>
                    <tbody>';

    for ($i = 1; $i <= 12; $i++) {
        $rev = 0;
        foreach ($yearlyRevenue as $r) {
            if ($r['month'] == $i) {
                $rev = $r['total'];
                break;
            }
        }

        $exp = 0;
        foreach ($yearlyExpenses as $e) {
            if ($e['month'] == $i) {
                $exp = $e['total'];
                break;
            }
        }

        $diff = $rev - $exp;
        $diffClass = ($diff >= 0) ? 'text-dark' : 'text-danger';

        $pageContent .= '
                        <tr>
                            <td class="ps-4"><strong>' . $monthsFull[$i - 1] . '</strong></td>
                            <td class="text-success fw-bold">Rs ' . number_format($rev, 0) . '</td>
                            <td class="text-danger fw-bold">Rs ' . number_format($exp, 0) . '</td>
                            <td class="pe-4 ' . $diffClass . ' fw-bold">Rs ' . number_format($diff, 0) . '</td>
                        </tr>';
    }

    $pageContent .= '
                    </tbody>
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td class="ps-4">YEARLY TOTALS</td>
                            <td class="text-success">Rs ' . number_format($totalRevenue, 0) . '</td>
                            <td class="text-danger">Rs ' . number_format($totalExpenses, 0) . '</td>
                            <td class="pe-4">Rs ' . number_format($totalRevenue - $totalExpenses, 0) . '</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>';

}
else {
    // MONTHLY DETAILED VIEW
    $pageContent .= '
    <div class="row">
        <!-- Payments Section -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Revenue Details - ' . $monthsFull[$month - 1] . ' ' . $year . '</h5>
                    <span class="badge bg-success">Total: Rs ' . number_format($totalRevenue, 0) . '</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Member</th>
                                    <th>Fee Type</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>';

    if (empty($detailedPayments)) {
        $pageContent .= '<tr><td colspan="4" class="text-center text-muted py-4">No payments found for this month</td></tr>';
    }
    else {
        foreach ($detailedPayments as $p) {
            $pageContent .= '
                            <tr>
                                <td>' . date('d M', strtotime($p['payment_date'])) . '</td>
                                <td><strong>' . escapeHtml($p['full_name']) . '</strong></td>
                                <td><span class="badge bg-light text-dark border">' . escapeHtml($p['fee_type_name']) . '</span></td>
                                <td class="text-end fw-bold">Rs ' . number_format($p['amount'], 0) . '</td>
                            </tr>';
        }
    }

    $pageContent .= '
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Expenses & Commissions -->
        <div class="col-lg-5">
            <!-- Expenses Section -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Expenses - ' . $monthsFull[$month - 1] . '</h5>
                    <span class="badge bg-danger">Total: Rs ' . number_format($totalExpenses, 0) . '</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Title</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>';

    if (empty($detailedExpenses)) {
        $pageContent .= '<tr><td colspan="2" class="text-center text-muted py-4">No expenses found for this month</td></tr>';
    }
    else {
        foreach ($detailedExpenses as $e) {
            $pageContent .= '
                            <tr>
                                <td>' . escapeHtml($e['title']) . '<br><small class="text-muted">' . date('d M', strtotime($e['expense_date'])) . '</small></td>
                                <td class="text-end text-danger fw-bold">Rs ' . number_format($e['amount'], 0) . '</td>
                            </tr>';
        }
    }

    $pageContent .= '
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Commissions Section -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Trainer Commissions</h5>
                    <span class="badge bg-info">Total: Rs ' . number_format($totalCommissions, 0) . '</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <tbody>';

    if (empty($trainerSummary) || $totalCommissions == 0) {
        $pageContent .= '<tr><td class="text-center text-muted py-4">No commissions for this month</td></tr>';
    }
    else {
        foreach ($trainerSummary as $t) {
            if ($t['total_commission'] > 0) {
                $pageContent .= '
                                <tr>
                                    <td><strong>' . escapeHtml($t['name']) . '</strong></td>
                                    <td class="text-end fw-bold">Rs ' . number_format($t['total_commission'], 0) . '</td>
                                </tr>';
            }
        }
    }

    $pageContent .= '
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>';
}

$pageContent .= '
<div class="alert alert-info border-0 shadow-sm mb-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Note:</strong> Net Profit is calculated as <code>Total Revenue - (Expenses + Trainers Commissions)</code>.
</div>

<script>
function loadReport() {
    const year = document.getElementById("yearFilter").value;
    const month = document.getElementById("monthFilter").value;
    window.location.href = "finance.php?year=" + year + "&month=" + month;
}

function thermalPrint() {
    const year = document.getElementById("yearFilter").value;
    const month = document.getElementById("monthFilter").value;
    const url = "print_finance.php?year=" + year + "&month=" + month;
    window.open(url, "ThermalPrint", "width=400,height=600");
}

function directThermalPrint() {
    const year = document.getElementById("yearFilter").value;
    const month = document.getElementById("monthFilter").value;
    const url = "print_report_direct.php?year=" + year + "&month=" + month;
    window.open(url, "DirectThermalPrint", "width=400,height=600");
}
';

if ($month === 'all') {
    $pageContent .= '
    document.addEventListener("DOMContentLoaded", function() {
        const revData = new Array(12).fill(0);
        const expData = new Array(12).fill(0);
        
        const yearlyRev = ' . json_encode($yearlyRevenue) . ';
        const yearlyExp = ' . json_encode($yearlyExpenses) . ';
        
        yearlyRev.forEach(item => revData[item.month - 1] = parseFloat(item.total));
        yearlyExp.forEach(item => expData[item.month - 1] = parseFloat(item.total));
        
        const ctx = document.getElementById("financeChart").getContext("2d");
        new Chart(ctx, {
            type: "line",
            data: {
                labels: ' . json_encode($monthsShort) . ',
                datasets: [
                    {
                        label: "Revenue",
                        data: revData,
                        backgroundColor: "rgba(40, 167, 69, 0.1)",
                        borderColor: "#28a745",
                        borderWidth: 3,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: "Expenses",
                        data: expData,
                        backgroundColor: "rgba(220, 53, 69, 0.1)",
                        borderColor: "#dc3545",
                        borderWidth: 3,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: "top" },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ": Rs " + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return "Rs " + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });';
}

$pageContent .= '
</script>
';

// Add Chart.js explicitly (though header has it, being safe or using newer version if needed)
$additionalJS = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

// Final Output
include __DIR__ . '/../../views/layout/header.php';
?>
