<?php
/**
 * Revenue Reports Page
 * Monthly and yearly revenue analysis
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Payment.php';

$paymentModel = new Payment($pdo);

// Get selected year or current year
$year = $_GET['year'] ?? date('Y');
$yearlyRevenue = $paymentModel->getYearlyRevenueBreakdown($year);
$totalRevenue = $paymentModel->getTotalRevenue();

// Set page title
$pageTitle = 'Revenue Reports';

// Start building page content
$pageContent = '
<div class="page-header">
    <h1><i class="fas fa-chart-bar"></i> Revenue Reports</h1>
    <p>Analyze gym revenue and payments</p>
</div>

<!-- Year Filter -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label for="yearFilter" class="form-label">Select Year</label>
                <select id="yearFilter" class="form-select" onchange="loadReport()">
                    <option value="2024" ' . ($year === '2024' ? 'selected' : '') . '>2024</option>
                    <option value="2025" ' . ($year === '2025' ? 'selected' : '') . '>2025</option>
                    <option value="2026" ' . ($year === '2026' ? 'selected' : '') . '>2026</option>
                </select>
            </div>
            <div class="col-md-8 d-flex align-items-end gap-2">
                <button class="btn btn-primary" onclick="exportReportToCSV()">
                    <i class="fas fa-download"></i> Export to CSV
                </button>
                <button class="btn btn-info" onclick="printReport()">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 32px;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div style="margin-left: 20px;">
                        <h6 style="color: #999; margin: 0; font-size: 12px; text-transform: uppercase;">Total Revenue (All Time)</h6>
                        <h2 style="color: #667eea; margin: 5px 0 0 0; font-weight: 700;">Rs ' . number_format($totalRevenue, 0) . '</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 32px;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div style="margin-left: 20px;">
                        <h6 style="color: #999; margin: 0; font-size: 12px; text-transform: uppercase;">Year ' . $year . ' Revenue</h6>
                        <h2 style="color: #28a745; margin: 5px 0 0 0; font-weight: 700;">Rs ' . number_format(array_sum(array_column($yearlyRevenue, 'total')), 0) . '</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Monthly Revenue Breakdown - ' . $year . '</h5>
    </div>
    <div class="card-body">
        <canvas id="revenueChart"></canvas>
    </div>
</div>

<!-- Monthly Breakdown Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Monthly Details</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="revenueTable">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Revenue</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>';

$months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
$totalYearRevenue = array_sum(array_column($yearlyRevenue, 'total'));

if ($totalYearRevenue > 0) {
    for ($i = 1; $i <= 12; $i++) {
        $monthRevenue = 0;
        foreach ($yearlyRevenue as $revenue) {
            if ($revenue['month'] == $i) {
                $monthRevenue = $revenue['total'];
                break;
            }
        }
        $percentage = ($totalYearRevenue > 0) ? round(($monthRevenue / $totalYearRevenue) * 100, 1) : 0;
        $pageContent .= '
                    <tr>
                        <td><strong>' . $months[$i-1] . '</strong></td>
                        <td><strong>Rs ' . number_format($monthRevenue, 0) . '</strong></td>
                        <td>
                            <div style="width: 100%; background-color: #f0f0f0; border-radius: 5px; height: 20px; overflow: hidden;">
                                <div style="width: ' . $percentage . '%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-size: 11px; font-weight: 600;">
                                    ' . $percentage . '%
                                </div>
                            </div>
                        </td>
                    </tr>';
    }
} else {
    $pageContent .= '<tr><td colspan="3" class="text-center text-muted py-4">No data for this year</td></tr>';
}

$pageContent .= '
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const yearlyData = ' . json_encode($yearlyRevenue) . ';
const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

function loadReport() {
    const year = document.getElementById("yearFilter").value;
    window.location.href = "' . APP_URL . '/views/reports/revenue.php?year=" + year;
}

function initChart() {
    const data = new Array(12).fill(0);
    yearlyData.forEach(item => {
        data[item.month - 1] = parseFloat(item.total);
    });
    
    const ctx = document.getElementById("revenueChart");
    if (ctx) {
        new Chart(ctx, {
            type: "bar",
            data: {
                labels: months,
                datasets: [{
                    label: "Revenue (PKR)",
                    data: data,
                    backgroundColor: "rgba(102, 126, 234, 0.8)",
                    borderColor: "#667eea",
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
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
    }
}

function exportReportToCSV() {
    const table = document.getElementById("revenueTable");
    APP.exportTableToCSV("revenue-report-' . $year . '.csv", table);
}

function printReport() {
    window.print();
}

document.addEventListener("DOMContentLoaded", function() {
    initChart();
});
</script>
';

// Output page with layout
$additionalJS = '<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>';
include __DIR__ . '/../../views/layout/header.php';
?>
