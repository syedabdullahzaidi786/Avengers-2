<?php
/**
 * Payment Receipt Page
 * Printable invoice/receipt
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Payment.php';

requireLogin();

$paymentId = $_GET['id'] ?? null;
$receiptNo = $_GET['receipt_no'] ?? null;
$paymentModel = new Payment($pdo);
$firstPayment = null;

if ($paymentId) {
    $firstPayment = $paymentModel->getPaymentById($paymentId);
}

if (!$firstPayment && $receiptNo) {
    $payments = $paymentModel->getPaymentsByReceipt($receiptNo);
    if (!empty($payments)) {
        $firstPayment = $payments[0];
    }
}

if (!$firstPayment) {
    die("Error: Payment not found.");
}

// Fetch all items for this receipt
$payments = $paymentModel->getPaymentsByReceipt($firstPayment['receipt_number']);
$totalAmount = 0;
foreach ($payments as $p) {
    $totalAmount += $p['amount'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo $firstPayment['receipt_number']; ?></title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', monospace;
            background: #eee;
            padding: 20px;
            margin: 0;
        }
        .thermal-receipt {
            width: 70mm; /* Reduced to safely fit 80mm paper */
            margin: 0 auto;
            padding: 5px;
            background: white;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            font-size: 12px; /* Reduced font size */
            line-height: 1.3;
            color: #000;
            font-weight: 600;
            word-wrap: break-word;
        }
        .center {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .logo {
            max-width: 80px;
            max-height: 80px;
            margin-bottom: 5px;
            filter: grayscale(100%); /* Thermal printers are B&W */
        }
        .line {
            text-align: center;
            margin: 2px 0;
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
        .label {
            font-weight: 800;
            font-size: 11px;
            width: 40%;
            text-align: left;
        }
        .value {
            text-align: right;
            font-size: 11px;
            width: 60%;
            word-wrap: break-word;
        }
        .amount-row {
            margin: 5px 0;
            padding: 5px 0;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-weight: 800;
            font-size: 14px;
            text-align: center;
        }
        .footer-text {
            margin-top: 10px;
            font-size: 10px;
            text-align: center;
        }
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            .thermal-receipt {
                box-shadow: none;
                width: 100%;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="thermal-receipt">
        <!-- Header -->
        <div class="center">
            <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="Logo" class="logo">
            <div class="line">
                <strong style="font-size: 16px;"><?php echo $_GET['title'] ?? 'PAYMENT RECEIPT'; ?></strong>
            </div>
        </div>
        
        <!-- Dashed Line -->
        <div class="dashed-line">------------------------------------------------</div>
        
        <!-- Receipt Number and Date -->
        <div class="row">
            <span class="label">Receipt No:</span>
            <span class="value"><?php echo escapeHtml($firstPayment['receipt_number']); ?></span>
        </div>
        <div class="row">
            <span class="label">Date:</span>
            <span class="value"><?php echo date('d-M-Y', strtotime($firstPayment['payment_date'])); ?></span>
        </div>
        <div class="row">
            <span class="label">Time:</span>
            <span class="value"><?php echo date('h:i A', strtotime($firstPayment['created_at'])); ?></span>
        </div>
        
        <!-- Dashed Line -->
        <div class="dashed-line">- - - - - - - - - - - - - - - - - - - -</div>
        
        <!-- Member Details -->
        <div style="margin: 8px 0;">
            <strong style="font-size: 11px;">MEMBER DETAILS</strong>
        </div>
        <div class="row">
            <span class="label">Membership No:</span>
            <span class="value"><?php echo str_pad($firstPayment['member_id'], 6, '0', STR_PAD_LEFT); ?></span>
        </div>
        <div class="row">
            <span class="label">Name:</span>
            <span class="value"><?php echo escapeHtml($firstPayment['full_name']); ?></span>
        </div>
        <div class="row">
            <span class="label">Phone:</span>
            <span class="value"><?php echo escapeHtml($firstPayment['phone']); ?></span>
        </div>
        
        <!-- Dashed Line -->
        <div class="dashed-line">------------------------------------------------</div>
        
        <!-- Payment Details -->
        <div style="margin: 8px 0;">
            <strong style="font-size: 11px;">PAYMENT DETAILS</strong>
        </div>
        
        <?php 
        // Parsing discount from description
        $discountData = null;
        if (!empty($firstPayment['description']) && preg_match('/\(Discount Applied: (\d+)% - Rs ([\d,.]+)\)/', $firstPayment['description'], $matches)) {
            $discountData = [
                'percent' => $matches[1],
                'amount' => (float)str_replace(',', '', $matches[2])
            ];
        }

        foreach ($payments as $index => $item): 
            $displayAmount = $item['amount'];
            // Add discount back to the first item for "Actual Fee" display
            if ($index === 0 && $discountData) {
                $displayAmount += $discountData['amount'];
            }
        ?>
        <div class="row">
            <span class="label"><?php echo escapeHtml($item['fee_type_name'] ?? 'Membership Fee'); ?>:</span>
            <span class="value">Rs <?php echo number_format($displayAmount, 0); ?></span>
        </div>
        <?php endforeach; ?>

        <?php if ($discountData): ?>
        <div class="row" style="color: #d9534f; font-style: italic;">
            <span class="label">Discount (<?php echo $discountData['percent']; ?>%):</span>
            <span class="value">- Rs <?php echo number_format($discountData['amount'], 0); ?></span>
        </div>
        <?php endif; ?>

        <?php 
        // Clean description for display (remove the machine-readable discount part if desired, 
        // or just show everything else)
        $cleanDesc = $firstPayment['description'];
        if ($discountData) {
            $cleanDesc = trim(preg_replace('/\(Discount Applied: \d+% - Rs [\d,.]+\)/', '', $cleanDesc));
        }
        ?>
        
        <div class="row">
            <span class="label">Method:</span>
            <span class="value"><?php echo ucfirst(str_replace('_', ' ', $firstPayment['payment_method'])); ?></span>
        </div>
        
        <!-- Amount -->
        <div class="amount-row">
            TOTAL: Rs <?php echo number_format($totalAmount, 0); ?>
        </div>
        
        <!-- Footer -->
        <div class="center footer-text">
            <p style="margin-top: 10px;">** Thank You **</p>
            <p style="font-size: 10px; margin-top: 5px;">
                Software Design & Developed By: AR Cloud<br>
                Contact: +92 3313771572
            </p>
        </div>
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.location.href='print_receipt.php?id=<?php echo $_GET['id']; ?>&title=<?php echo urlencode($_GET['title'] ?? 'PAYMENT RECEIPT'); ?>'" style="padding: 10px 20px; font-size: 14px; cursor: pointer; background: #28a745; color: white; border: none; border-radius: 4px;">
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
