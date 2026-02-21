<?php
/**
 * Dashboard Page
 * Main dashboard showing key metrics and recent activity
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/models/Member.php';

$dashboardController = new DashboardController($pdo);
$data = $dashboardController->getDashboardData();

// Set page title
$pageTitle = 'Dashboard';

// Start building page content
$pageContent = '
<div class="page-header">
    <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
    <p>Welcome back! Here\'s your gym management summary.</p>
</div>

<!-- Stats Row -->
<div class="row mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>Total Members</h3>
                <div class="number">' . $data['totalMembers'] . '</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3>Active Members</h3>
                <div class="number">' . $data['activeMembers'] . '</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon danger">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <h3>Expired Members</h3>
                <div class="number">' . $data['expiredMembers'] . '</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-content">
                <h3>Monthly Revenue</h3>
                <div class="number">Rs ' . number_format($data['monthlyRevenue'], 0) . '</div>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Recent Activity -->
<div class="row">
    <!-- Revenue Chart -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Revenue Breakdown (2026)</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Expiring Soon -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Expiring Soon (7 days)</h5>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
';

if (empty($data['expiringMembers'])) {
    $pageContent .= '<p class="text-muted text-center py-4">No members expiring soon</p>';
} else {
    $pageContent .= '<ul class="list-unstyled">';
    foreach ($data['expiringMembers'] as $member) {
        $daysLeft = (new DateTime($member['end_date']))->diff(new DateTime())->days;
        $pageContent .= '
        <li class="mb-3 pb-3" style="border-bottom: 1px solid #e0e0e0;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>' . escapeHtml($member['full_name']) . '</strong>
                    <br><small class="text-muted">' . escapeHtml($member['plan_name']) . '</small>
                </div>
                <span class="badge badge-warning">' . $daysLeft . ' days</span>
            </div>
        </li>';
    }
    $pageContent .= '</ul>';
}

$pageContent .= '
            </div>
        </div>
    </div>
</div>

<!-- Recent Payments -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Payments</h5>
                <a href="' . APP_URL . '/views/payments/payments.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>';

if (empty($data['recentPayments'])) {
    $pageContent .= '<tr><td colspan="4" class="text-center text-muted py-4">No payments yet</td></tr>';
} else {
    foreach ($data['recentPayments'] as $payment) {
        $pageContent .= '
                            <tr>
                                <td>
                                    <strong>' . escapeHtml($payment['full_name']) . '</strong><br>
                                    <small class="text-muted">' . escapeHtml($payment['phone']) . '</small>
                                </td>
                                <td><strong>Rs ' . number_format($payment['amount'], 2) . '</strong></td>
                                <td>
                                    <span class="badge badge-info">' . ucfirst($payment['payment_method']) . '</span>
                                </td>
                                <td>' . date('M d, Y', strtotime($payment['payment_date'])) . '</td>
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
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Revenue Chart
    const ctx = document.getElementById("revenueChart");
    if (ctx) {
        const chartData = ' . json_encode($data['monthlyBreakdown']) . ';
        const labels = [
            "Jan", "Feb", "Mar", "Apr", "May", "Jun",
            "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
        ];
        const data = new Array(12).fill(0);
        
        chartData.forEach(item => {
            data[item.month - 1] = parseFloat(item.total);
        });
        
        new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [{
                    label: "Monthly Revenue (PKR)",
                    data: data,
                    borderColor: "#667eea",
                    backgroundColor: "rgba(102, 126, 234, 0.1)",
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: "#667eea",
                    pointBorderColor: "#fff",
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
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
});
</script>
';

// Output page with layout
include __DIR__ . '/views/layout/header.php';
?>
