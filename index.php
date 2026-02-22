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
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
        <p class="mb-0">Welcome back! Here\'s your gym management summary.</p>
    </div>
    <div class="text-end">
        <div id="dashboard-clock" class="h2 fw-bold mb-0" style="color: #667eea; letter-spacing: 2px;">00:00:00</div>
        <div id="dashboard-date" class="text-muted small">Loading date...</div>
    </div>
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

<!-- Actions and Expiring Section -->
<div class="row">
    <!-- Quick Actions -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center gap-3 py-4">
                <button class="btn btn-lg shadow-sm fw-bold px-4 py-3 w-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 12px; transition: all 0.3s ease; font-size: 1.1rem;" onclick="syncMembershipStatus()">
                    <i class="fas fa-sync-alt me-2 fa-lg"></i> SYNC MEMBERSHIP STATUS
                </button>
                <a href="' . APP_URL . '/views/attendance/index.php" class="btn btn-lg shadow-sm fw-bold px-4 py-3 w-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; border: none; border-radius: 12px; transition: all 0.3s ease; font-size: 1.1rem;">
                    <i class="fas fa-calendar-check me-2 fa-lg"></i> MARK ATTENDANCE
                </a>
                <a href="' . APP_URL . '/views/members/renew.php" class="btn btn-lg shadow-sm fw-bold px-4 py-3 w-100" style="background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%); color: white; border: none; border-radius: 12px; transition: all 0.3s ease; font-size: 1.1rem;">
                    <i class="fas fa-sync-alt me-2 fa-lg"></i> RENEW MEMBERSHIP
                </a>
                <a href="' . APP_URL . '/views/members/generate_id.php" class="btn btn-lg shadow-sm fw-bold px-4 py-3 w-100" style="background: linear-gradient(135deg, #FF512F 0%, #DD2476 100%); color: white; border: none; border-radius: 12px; transition: all 0.3s ease; font-size: 1.1rem;">
                    <i class="fas fa-id-card me-2 fa-lg"></i> GENERATE ID CARDS
                </a>
            </div>
        </div>
    </div>
    
    <!-- Expiring Soon -->
    <div class="col-lg-6 mb-4">
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
function syncMembershipStatus() {
    Swal.fire({
        title: "Sync Membership Status?",
        text: "This will check all members and automatically mark those with past expiry dates as EXPIRED.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#667eea",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, Start Sync"
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: "Syncing...",
                text: "Please wait while we update records",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "' . APP_URL . '/ajax/members_update_expiry.php",
                type: "POST",
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Sync Complete",
                            text: response.message
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire("Error", response.message, "error");
                    }
                },
                error: function() {
                    Swal.fire("Error", "A server error occurred during sync.", "error");
                }
            });
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    function updateClock() {
        const now = new Date();
        const timeStr = now.getHours().toString().padStart(2, \'0\') + \':\' + 
                        now.getMinutes().toString().padStart(2, \'0\') + \':\' + 
                        now.getSeconds().toString().padStart(2, \'0\');
        
        const dateOptions = { weekday: \'long\', year: \'numeric\', month: \'long\', day: \'numeric\' };
        const dateStr = now.toLocaleDateString(\'en-US\', dateOptions);
        
        const clockEl = document.getElementById(\'dashboard-clock\');
        const dateEl = document.getElementById(\'dashboard-date\');
        
        if (clockEl) clockEl.textContent = timeStr;
        if (dateEl) dateEl.textContent = dateStr;
    }

    updateClock();
    setInterval(updateClock, 1000);
});
</script>
';

// Output page with layout
include __DIR__ . '/views/layout/header.php';
?>
