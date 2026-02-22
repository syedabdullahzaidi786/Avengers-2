<?php
/**
 * Membership Renewal Page
 * Search by Member ID and process renewal
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Member.php';
require_once __DIR__ . '/../../models/FeeType.php';

$feeTypeModel = new FeeType($pdo);
$feeTypes = $feeTypeModel->getAllFeeTypes();
$feeTypesJson = json_encode($feeTypes);

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
                    <h5 class="mb-3">Renewal Payment Details</h5>
                    
                    <!-- Fee Items Table -->
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered" id="feeItemsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 55%;">Fee Type</th>
                                    <th style="width: 35%;">Amount (Rs)</th>
                                    <th style="width: 10%;"></th>
                                </tr>
                            </thead>
                            <tbody id="feeItemsBody">
                                <!-- Dynamic Rows -->
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <th class="text-end">Subtotal:</th>
                                    <th><input type="number" class="form-control form-control-sm border-0 bg-transparent fw-bold" id="renewalSubtotal" readonly value="0.00"></th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th class="text-end text-danger text-nowrap">
                                        Discount (%):
                                        <input type="number" class="form-control form-control-sm d-inline-block ms-1" id="renewalDiscountPercent" style="width: 60px;" value="0" min="0" max="100" oninput="calculateTotal()">
                                    </th>
                                    <th><input type="number" class="form-control form-control-sm border-0 bg-transparent text-danger fw-bold" id="renewalDiscountAmount" readonly value="0.00"></th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th class="text-end text-primary">Total:</th>
                                    <th>
                                        <input type="number" class="form-control form-control-sm fw-bold border-0 bg-transparent text-primary" id="renewalTotal" readonly value="0.00">
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFeeRow()">
                            <i class="fas fa-plus"></i> Add Extra Fee
                        </button>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small">Payment Method</label>
                            <select id="paymentMethod" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="easypaisa">EasyPaisa</option>
                                <option value="jazzcash">JazzCash</option>
                                <option value="nayapay">NayaPay</option>
                                <option value="sadapay">SadaPay</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Payment Date</label>
                            <input type="date" id="paymentDate" class="form-control" value="' . date('Y-m-d') . '">
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="button" class="btn btn-success btn-lg" onclick="processRenewal()">
                            <i class="fas fa-check-circle"></i> Process Renewal & Print
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
const feeTypes = ' . $feeTypesJson . ';
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

function addFeeRow(defaultId = null, defaultAmount = null) {
    const tbody = document.getElementById("feeItemsBody");
    const tr = document.createElement("tr");
    
    let options = "<option value=\"\">Select Fee Type</option>";
    feeTypes.forEach(ft => {
        const selected = (defaultId != null && String(ft.id) === String(defaultId)) ? "selected" : "";
        options += `<option value="${ft.id}" data-amount="${ft.default_amount}" ${selected}>${ft.name}</option>`;
    });
    
    tr.innerHTML = `
        <td><select class="form-select form-select-sm fee-type-select" onchange="updateRowAmount(this)" required>${options}</select></td>
        <td><input type="number" class="form-control form-control-sm fee-amount-input" value="${defaultAmount !== null ? defaultAmount : ""}" step="0.01" min="0" oninput="calculateTotal()" required></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFeeRow(this)"><i class="fas fa-times"></i></button></td>
    `;
    
    tbody.appendChild(tr);
    if (defaultId && defaultAmount === null) {
        const select = tr.querySelector(".fee-type-select");
        updateRowAmount(select);
    } else {
        calculateTotal();
    }
}

function removeFeeRow(btn) {
    btn.closest("tr").remove();
    calculateTotal();
}

function updateRowAmount(select) {
    const amountInput = select.closest("tr").querySelector(".fee-amount-input");
    const selectedOption = select.options[select.selectedIndex];
    const defaultAmount = selectedOption.getAttribute("data-amount");
    if (defaultAmount && defaultAmount !== "null") {
        amountInput.value = defaultAmount;
    }
    calculateTotal();
}

function calculateTotal() {
    let subtotal = 0;
    document.querySelectorAll(".fee-amount-input").forEach(input => {
        const val = parseFloat(input.value);
        if (!isNaN(val)) subtotal += val;
    });
    
    const discountPercent = parseFloat(document.getElementById("renewalDiscountPercent").value) || 0;
    const discountAmount = subtotal * (discountPercent / 100);
    const total = subtotal - discountAmount;
    
    document.getElementById("renewalSubtotal").value = subtotal.toFixed(2);
    document.getElementById("renewalDiscountAmount").value = discountAmount.toFixed(2);
    document.getElementById("renewalTotal").value = total.toFixed(2);
}

function showMemberDetails(member) {
    document.getElementById("searchPlaceholder").style.display = "none";
    document.getElementById("memberDetailsCard").style.display = "block";

    document.getElementById("displayMemberName").textContent = member.full_name;
    document.getElementById("displayMemberPhone").textContent = member.phone;
    document.getElementById("displayMemberPlan").textContent = member.plan_name;
    document.getElementById("displayMemberExpiry").textContent = formatDate(member.end_date);
    
    // Clear and build fee table
    document.getElementById("feeItemsBody").innerHTML = "";
    
    // 1. Add Membership Fee (Fee Type 1)
    const planPrice = parseFloat(member.price) || 0;
    addFeeRow(1, planPrice);
    
    // 2. Add Trainer Fee if applicable
    const trainerFee = parseFloat(member.trainer_fee) || 0;
    if (trainerFee > 0) {
        const ft = findTrainerFeeType(member.trainer_name);
        addFeeRow(ft ? ft.id : 4, trainerFee); // Default to 4 if not matched
    }
    
    document.getElementById("renewalDiscountPercent").value = 0;
    calculateTotal();

    const statusBadge = document.getElementById("memberStatusBadge");
    if (member.status === "active") {
        statusBadge.className = "badge bg-success";
        statusBadge.textContent = "Active";
    } else {
        statusBadge.className = "badge bg-danger";
        statusBadge.textContent = "Expired";
    }
}

function findTrainerFeeType(trainerName) {
    if (!trainerName) return null;
    return feeTypes.find(f => 
        f.name.toLowerCase().includes(trainerName.toLowerCase()) || 
        f.name.toLowerCase().includes("trainer") || 
        f.name.toLowerCase().includes("personal")
    );
}

function resetSearch() {
    currentMember = null;
    document.getElementById("searchPlaceholder").style.display = "block";
    document.getElementById("memberDetailsCard").style.display = "none";
}

function processRenewal() {
    if (!currentMember) return;

    const items = [];
    document.querySelectorAll("#feeItemsBody tr").forEach(row => {
        const feeTypeId = row.querySelector(".fee-type-select").value;
        const amount = row.querySelector(".fee-amount-input").value;
        if (feeTypeId && amount > 0) {
            items.push({ fee_type_id: feeTypeId, amount: amount });
        }
    });

    if (items.length === 0) {
        Swal.fire("Error", "Please add at least one fee item.", "error");
        return;
    }

    const subtotal = parseFloat(document.getElementById("renewalSubtotal").value) || 0;
    const discountPercent = parseFloat(document.getElementById("renewalDiscountPercent").value) || 0;
    const discountAmount = parseFloat(document.getElementById("renewalDiscountAmount").value) || 0;
    const totalAmount = parseFloat(document.getElementById("renewalTotal").value) || 0;
    const method = document.getElementById("paymentMethod").value;
    const paymentDate = document.getElementById("paymentDate").value;

    Swal.fire({
        title: "Confirm Renewal?",
        text: `Process renewal for ${currentMember.full_name} for total Rs ${totalAmount.toLocaleString()}?`,
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#28a745",
        confirmButtonText: "Yes, Renew Now"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "' . APP_URL . '/ajax/membership_renew.php",
                type: "POST",
                data: {
                    member_id: currentMember.id,
                    items: JSON.stringify(items),
                    discount_percent: discountPercent,
                    discount_amount: discountAmount,
                    payment_method: method,
                    payment_date: paymentDate
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
    if (!receipt || !receipt.receipt_number) return;
    
    // Use direct thermal printer script with a custom title for renewal
    const printUrl = `' . APP_URL . '/views/payments/print_receipt.php?id=${receipt.id || ""}&receipt_no=${receipt.receipt_number}&title=RENEWAL RECEIPT`;
    
    // We can also fall back to the browseable receipt if needed, 
    // but the user requested direct thermal printer work same as payments.
    window.open(printUrl, "_blank");
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
