<?php
/**
 * Member Profile Page
 * View individual member details and payment history
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Member.php';
require_once __DIR__ . '/../../models/Payment.php';

$memberModel = new Member($pdo);
$paymentModel = new Payment($pdo);

if (empty($_GET['id'])) {
    header('Location: ' . APP_URL . '/views/members/members.php');
    exit;
}

$member = $memberModel->getMemberById($_GET['id']);
if (!$member) {
    header('Location: ' . APP_URL . '/views/members/members.php');
    exit;
}

$payments = $paymentModel->getPaymentsByMember($member['id']);
$daysRemaining = (new DateTime($member['end_date']))->diff(new DateTime())->days;
$daysRemaining = ($member['status'] === 'active') ? $daysRemaining : 0;

// Set page title
$pageTitle = 'Member Profile - ' . $member['full_name'];

// Start building page content
$pageContent = '
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-user-circle"></i> ' . escapeHtml($member['full_name']) . '</h1>
        <p>Member Profile & History</p>
    </div>
    <div>
        <a href="<?php echo APP_URL; ?>/views/members/id_card.php?id=<?php echo $member['id']; ?>" target="_blank" class="btn btn-info me-2">
            <i class="fas fa-id-card"></i> Print ID Card
        </a>
        <a href="<?php echo APP_URL; ?>/views/members/members.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Members
        </a>
    </div>
</div>

<!-- Member Info Cards -->
<div class="row mb-4">
    <div class="col-lg-3">
        <div class="card text-center">
            <div class="card-body">
                ' . ($member['profile_picture'] ? 
                    '<img src="' . APP_URL . '/' . $member['profile_picture'] . '" alt="' . escapeHtml($member['full_name']) . '" class="img-fluid rounded-circle mb-3" style="max-width: 200px; height: 200px; object-fit: cover;">' : 
                    '<div class="bg-light rounded-circle p-5 mb-3 d-inline-block"><i class="fas fa-user-circle" style="font-size: 5rem; color: #ccc;"></i></div>') . '
                <h5>' . escapeHtml($member['full_name']) . '</h5>
                <p class="text-muted">' . escapeHtml($member['phone']) . '</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Member Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Full Name:</strong></td>
                        <td>' . escapeHtml($member['full_name']) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td>' . escapeHtml($member['phone']) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Gender:</strong></td>
                        <td>' . ($member['gender'] ?? '-') . '</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Membership Status</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Plan:</strong></td>
                        <td>' . escapeHtml($member['plan_name']) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Duration:</strong></td>
                        <td>' . $member['duration'] . ' days</td>
                    </tr>
                    <tr>
                        <td><strong>Start Date:</strong></td>
                        <td>' . date('M d, Y', strtotime($member['start_date'])) . '</td>
                    </tr>
                    <tr>
                        <td><strong>End Date:</strong></td>
                        <td>' . date('M d, Y', strtotime($member['end_date'])) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            ' . ($member['status'] === 'active' 
                                ? '<span class="badge badge-success">Active (' . $daysRemaining . ' days)</span>' 
                                : '<span class="badge badge-danger">Expired</span>') . '
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Payment History -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Payment History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Receipt</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>';

if (empty($payments)) {
    $pageContent .= '<tr><td colspan="6" class="text-center text-muted py-4">No payments found</td></tr>';
} else {
    foreach ($payments as $payment) {
        $methodBadge = $payment['payment_method'] === 'cash' 
            ? '<span class="badge badge-warning">Cash</span>' 
            : ($payment['payment_method'] === 'card' 
                ? '<span class="badge badge-info">Card</span>' 
                : '<span class="badge badge-success">Online</span>');
        
        $pageContent .= '
                            <tr>
                                <td>' . date('M d, Y', strtotime($payment['payment_date'])) . '</td>
                                <td><small class="text-muted">' . escapeHtml($payment['receipt_number']) . '</small></td>
                                <td><strong>Rs ' . number_format($payment['amount'], 2) . '</strong></td>
                                <td>' . $methodBadge . '</td>
                                <td>' . ($payment['description'] ? escapeHtml($payment['description']) : '-') . '</td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="printReceipt(' . $payment['id'] . ')">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
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
function printReceipt(id) {
    window.open("' . APP_URL . '/views/payments/receipt.php?id=" + id, "_blank");
}
</script>
';

// Output page with layout
include __DIR__ . '/../../views/layout/header.php';
?>
