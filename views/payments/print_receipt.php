<?php
/**
 * Direct Thermal Printing Script
 * Uses mike42/escpos-php to print directly to a Windows USB thermal printer
 */

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Payment.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

requireLogin();

if (!isset($_GET['id']) || $_GET['id'] === '') {
    die("Error: No payment ID provided.");
}

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
    die("Error: Payment not found. (ID: $paymentId, Receipt: $receiptNo)");
}

$payments = $paymentModel->getPaymentsByReceipt($firstPayment['receipt_number']);
$totalAmount = 0;
foreach ($payments as $p) {
    $totalAmount += $p['amount'];
}

try {
    /* 1. INITIALIZE CONNECTOR - USING SHARE NAME BC88 */
    // This is the most reliable way for non-admin users.
    $printerName = "BC88";
    $connector = new WindowsPrintConnector($printerName);
    $printer = new Printer($connector);

    /* 2. SET FONT AND WIDTH (80mm / 48 Chars) */
    $printer->selectPrintMode(Printer::MODE_FONT_A);

    // Customize title if requested
    $receiptTitle = $_GET['title'] ?? 'PAYMENT RECEIPT';

    /* 3. HEADER - AR FITNESS CLUB */
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_DOUBLE_HEIGHT);
    $printer->text("Avengers Gym & Fitness 2\n");
    $printer->selectPrintMode(Printer::MODE_FONT_A); // Reset to standard font
    $printer->text($receiptTitle . "\n");
    $printer->feed();

    /* 4. RECEIPT INFORMATION */
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->setEmphasis(true);
    $printer->text("Receipt No: ");
    $printer->setEmphasis(false);
    $printer->text($firstPayment['receipt_number'] . "\n");

    $printer->setEmphasis(true);
    $printer->text("Date:       ");
    $printer->setEmphasis(false);
    $printer->text(date('d-M-Y', strtotime($firstPayment['payment_date'])) . "\n");

    $printer->setEmphasis(true);
    $printer->text("Time:       ");
    $printer->setEmphasis(false);
    $printer->text(date('h:i A', strtotime($firstPayment['created_at'])) . "\n");

    $printer->text("------------------------------------------------\n"); // 48 chars

    /* 5. MEMBER DETAILS */
    $printer->setEmphasis(true);
    $printer->text("Mem. No: ");
    $printer->setEmphasis(false);
    $printer->text(str_pad($firstPayment['member_id'], 6, '0', STR_PAD_LEFT) . "\n");

    $printer->setEmphasis(true);
    $printer->text("MEMBER:  ");
    $printer->setEmphasis(false);
    $printer->text($firstPayment['full_name'] . "\n");
    $printer->text("Phone:   " . $firstPayment['phone'] . "\n");
    $printer->text("------------------------------------------------\n");

    /* 6. PAYMENT DETAILS TABLE */
    $printer->setEmphasis(true);
    $printer->text(str_pad("DESCRIPTION", 35) . str_pad("AMOUNT", 13, " ", STR_PAD_LEFT) . "\n");
    $printer->setEmphasis(false);

    // Parsing discount from description
    $discountData = null;
    if (!empty($firstPayment['description']) && preg_match('/\(Discount Applied: (\d+)% - Rs ([\d,.]+)\)/', $firstPayment['description'], $matches)) {
        $discountData = [
            'percent' => $matches[1],
            'amount' => (float)str_replace(',', '', $matches[2])
        ];
    }

    foreach ($payments as $index => $item) {
        $desc = $item['fee_type_name'] ?? 'Membership Fee';
        $itemAmount = $item['amount'];

        // Add discount back to the first item for "Actual Fee" display
        if ($index === 0 && $discountData) {
            $itemAmount += $discountData['amount'];
        }

        $amountStr = "Rs " . number_format($itemAmount, 0);
        $printer->text(str_pad(substr($desc, 0, 34), 35) . str_pad($amountStr, 13, " ", STR_PAD_LEFT) . "\n");
    }

    // Add dedicated discount row if present
    if ($discountData) {
        $discDesc = "Discount (" . $discountData['percent'] . "%)";
        $discAmount = "- Rs " . number_format($discountData['amount'], 0);
        $printer->text(str_pad(substr($discDesc, 0, 34), 35) . str_pad($discAmount, 13, " ", STR_PAD_LEFT) . "\n");
    }

    $printer->text("------------------------------------------------\n");

    /* 7. TOTAL SECTION */
    $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
    $printer->setEmphasis(true);
    $printer->text(str_pad("TOTAL:", 12) . str_pad("Rs " . number_format($totalAmount, 0), 12, " ", STR_PAD_LEFT) . "\n");
    $printer->selectPrintMode(Printer::MODE_FONT_A);
    $printer->setEmphasis(false);
    $printer->text("------------------------------------------------\n");

    /* 8. FOOTER */
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->feed();
    $printer->text("** Thank You For Choosing Us **\n");
    $printer->text("Software Developed By: AR Cloud\n");
    $printer->text("Contact: +92 3313771572\n");

    /* 9. FINALIZE PRINTING */
    $printer->feed(2);
    $printer->cut();
    $printer->close();

    // Success response for the browser
    echo "<script>alert('Receipt printed successfully!'); window.close();</script>";

} catch (Exception $e) {
    /* ERROR HANDLING FOR NON-ADMIN USERS */
    echo "<div style='color: red; padding: 25px; border: 3px solid red; font-family: sans-serif; background: #fff; max-width: 600px; margin: 20px auto;'>";
    echo "<h2 style='margin-top:0'>Printer Connection Error</h2>";
    echo "<p>Could not connect to shared printer: <strong>BC88</strong></p>";
    echo "<hr>";
    echo "<h3>If printing failed, follow these steps:</h3>";
    echo "<ol>";
    echo "<li><strong>Check Sharing:</strong> Right-click printer > Properties > Sharing. Share as <strong>BC88</strong>.</li>";
    echo "<li><strong>Check Online:</strong> Ensure the BlackCopper printer is plugged in and <strong>ON</strong>.</li>";
    echo "<li><strong>Clear Queue:</strong> Clear any stuck documents in 'See what's printing'.</li>";
    echo "<li><strong>Permissions:</strong> In Security tab, ensure 'Everyone' has Print permissions.</li>";
    echo "</ol>";
    echo "<p><strong>Error Details:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<button onclick='window.history.back()' style='padding: 10px 20px; cursor: pointer; background: #000; color: #fff; border: none; border-radius: 4px;'>Go Back</button>";
    echo "</div>";
}
