<?php
/**
 * Membership Renewal Page
 * Search by Member ID and process renewal
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Member.php';

// Set page title
$pageTitle = 'Renew Membership';

// Start building page content
$pageContent = '
<div class="page-header">
    <h1><i class="fas fa-sync-alt"></i> Renew Membership</h1>
    <p>Search member by ID to process renewal and payment</p>
</div>

<div class="row">
    <div class="col-md-5">
        <!-- Search Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-search"></i> Search Member</h5>
            </div>
            <div class="card-body">
                <form id="searchMemberForm" onsubmit="searchMember(event)">
                    <div class="mb-3">
                        <label for="searchMemberId" class="form-label">Membership No / ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="number" id="searchMemberId" class="form-control form-control-lg" placeholder="e.g. 000005" required>
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div>
                        <small class="text-muted">Enter the 6-digit membership number or ID</small>
                    </div>
                </form>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="card border-info">
            <div class="card-body">
                <h6><i class="fas fa-info-circle text-info"></i> How it works:</h6>
                <ol class="small mb-0 mt-2">
                    <li>Enter Member ID and search.</li>
                    <li>Verify member details and total amount.</li>
                    <li>Select payment method.</li>
                    <li>Click <strong>Process Renewal</strong>.</li>
                    <li>System will automatically update status, extend dates and generate a receipt.</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <!-- Result Card -->
        <div id="memberDetailsCard" class="card" style="display: none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-user-check"></i> Member Details</h5>
                <span id="memberStatusBadge"></span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Full Name</label>
                        <h4 id="displayMemberName" class="mb-0"></h4>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Phone Number</label>
                        <p id="displayMemberPhone" class="mb-0 fw-bold"></p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Current Plan</label>
                        <p id="displayMemberPlan" class="mb-0"></p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Expiry Date</label>
                        <p id="displayMemberExpiry" class="mb-0"></p>
                    </div>
                </div>

                <hr class="glass-hr">

                <div class="payment-section mt-4">
                    <h5 class="mb-3">Renewal Payment</h5>
                    <div class="row align-items-end g-3">
                        <div class="col-md-6">
                            <label class="form-label">Plan Fee (Rs)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs</span>
                                <input type="number" id="renewalAmount" class="form-control form-control-lg fw-bold" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select id="paymentMethod" class="form-select form-control-lg">
                                <option value="cash">Cash</option>
                                <option value="easypaisa">EasyPaisa</option>
                                <option value="jazzcash">JazzCash</option>
                                <option value="nayapay">NayaPay</option>
                                <option value="sadapay">SadaPay</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="button" class="btn btn-success btn-lg" onclick="processRenewal()">
                            <i class="fas fa-check-circle"></i> Process Renewal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Placeholder -->
        <div id="searchPlaceholder" class="card bg-transparent border-dashed text-center py-5">
            <div class="card-body">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <p class="text-muted">Search for a member to see details</p>
            </div>
        </div>
    </div>
</div>

<script>
let currentMember = null;

function searchMember(e) {
    if (e) e.preventDefault();
    const id = document.getElementById("searchMemberId").value;
    if (!id) return;

    $.ajax({
        url: "' . APP_URL . '/ajax/member_get_by_id.php",
        type: "POST",
        data: { id: id },
        dataType: "json",
        success: function(response) {
            if (response.success) {
                currentMember = response.data;
                showMemberDetails(response.data);
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Not Found",
                    text: response.message
                });
                resetSearch();
            }
        }
    });
}

function showMemberDetails(member) {
    document.getElementById("searchPlaceholder").style.display = "none";
    document.getElementById("memberDetailsCard").style.display = "block";

    document.getElementById("displayMemberName").textContent = member.full_name;
    document.getElementById("displayMemberPhone").textContent = member.phone;
    document.getElementById("displayMemberPlan").textContent = member.plan_name;
    document.getElementById("displayMemberExpiry").textContent = formatDate(member.end_date);
    document.getElementById("renewalAmount").value = member.price;

    const statusBadge = document.getElementById("memberStatusBadge");
    if (member.status === "active") {
        statusBadge.className = "badge badge-success";
        statusBadge.textContent = "Active";
    } else {
        statusBadge.className = "badge badge-danger";
        statusBadge.textContent = "Expired";
    }
}

function resetSearch() {
    currentMember = null;
    document.getElementById("searchPlaceholder").style.display = "block";
    document.getElementById("memberDetailsCard").style.display = "none";
}

function processRenewal() {
    if (!currentMember) return;

    Swal.fire({
        title: "Confirm Renewal?",
        text: `Process renewal for ${currentMember.full_name} for Rs ${currentMember.price}?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#28a745",
        confirmButtonText: "Yes, Renew Now"
    }).then((result) => {
        if (result.isConfirmed) {
            const amount = document.getElementById("renewalAmount").value;
            const method = document.getElementById("paymentMethod").value;

            $.ajax({
                url: "' . APP_URL . '/ajax/membership_renew.php",
                type: "POST",
                data: {
                    member_id: currentMember.id,
                    amount: amount,
                    payment_method: method
                },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Renewed!",
                            text: response.message,
                            showCancelButton: true,
                            confirmButtonText: "Print Receipt",
                            cancelButtonText: "Close"
                        }).then((printResult) => {
                            if (printResult.isConfirmed) {
                                printReceipt(response.receipt);
                            }
                            // Refresh member data or reset
                            searchMember(); 
                        });
                    } else {
                        Swal.fire("Error", response.message, "error");
                    }
                }
            });
        }
    });
}

function printReceipt(receipt) {
    const printWindow = window.open("", "_blank");
    printWindow.document.write(`
        <html>
            <head>
                <title>Receipt #${receipt.receipt_number}</title>
                <style>
                    @page {
                        size: 80mm auto;
                        margin: 0;
                    }
                    body {
                        font-family: \'Courier New\', monospace;
                        background: #eee;
                        padding: 20px;
                        margin: 0;
                    }
                    .thermal-receipt {
                        width: 70mm;
                        margin: 0 auto;
                        padding: 5px;
                        background: white;
                        box-shadow: 0 0 5px rgba(0,0,0,0.1);
                        font-size: 12px;
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
                        filter: grayscale(100%);
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
                    }
                </style>
            </head>
            <body>
                <div class="thermal-receipt">
                    <div class="center">
                        <img src="${window.location.origin}${window.location.pathname.split(\'/\').slice(0, -3).join(\'/\')}/assets/images/logo.png" alt="Logo" class="logo" onerror="this.src=\'https://cdn-icons-png.flaticon.com/512/2964/2964514.png\'">
                        <div class="line">
                            <strong style="font-size: 16px;">PAYMENT RECEIPT</strong>
                        </div>
                    </div>
                    
                    <div class="dashed-line">- - - - - - - - - - - - - - - - - - - -</div>
                    
                    <div class="row">
                        <span class="label">Receipt No:</span>
                        <span class="value">${receipt.receipt_number}</span>
                    </div>
                    <div class="row">
                        <span class="label">Date:</span>
                        <span class="value">${new Date(receipt.date).toLocaleDateString(\'en-GB\', {day: \'2-digit\', month: \'short\', year: \'numeric\'})}</span>
                    </div>
                    
                    <div class="dashed-line">- - - - - - - - - - - - - - - - - - - -</div>
                    
                    <div style="margin: 8px 0;">
                        <strong style="font-size: 11px;">MEMBER DETAILS</strong>
                    </div>
                    <div class="row">
                        <span class="label">Name:</span>
                        <span class="value">${currentMember.full_name}</span>
                    </div>
                    <div class="row">
                        <span class="label">Phone:</span>
                        <span class="value">${currentMember.phone}</span>
                    </div>
                    
                    <div class="dashed-line">- - - - - - - - - - - - - - - - - - - -</div>
                    
                    <div style="margin: 8px 0;">
                        <strong style="font-size: 11px;">PAYMENT DETAILS</strong>
                    </div>
                    <div class="row">
                        <span class="label">Membership ${currentMember.plan_name}:</span>
                        <span class="value">Rs ${parseFloat(receipt.amount).toLocaleString()}</span>
                    </div>
                    
                    <div class="amount-row">
                        TOTAL: Rs ${parseFloat(receipt.amount).toLocaleString()}
                    </div>
                    
                    <div class="center" style="margin-top: 10px; font-size: 10px; text-align: center;">
                        <p>** Thank You **</p>
                        <p style="font-size: 10px; margin-top: 5px;">
                            Software Design & Developed By: AR Cloud<br>
                            Contact: +92 3313771572
                        </p>
                    </div>
                </div>
                <script>window.onload = function() { window.print(); setTimeout(function() { window.close(); }, 500); }<\/script>
            </body>
        </html>
    `);
    printWindow.document.close();
}

function formatDate(date) {
    if (!date) return "-";
    return new Date(date).toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" });
}

function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}
</script>
';

// Output page with layout
include __DIR__ . '/../../views/layout/header.php';
?>
