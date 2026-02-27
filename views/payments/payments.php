<?php
/**
 * Payments Page
 * Manage gym payments and receipts
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Payment.php';
require_once __DIR__ . '/../../models/Member.php';

$paymentModel = new Payment($pdo);
$memberModel = new Member($pdo);
require_once __DIR__ . '/../../models/FeeType.php';
$feeTypeModel = new FeeType($pdo);

// Get fee types to embed in JS
$feeTypesList = $feeTypeModel->getAllFeeTypes();
$feeTypesJson = json_encode($feeTypesList);

// Get all active members with their plan and trainer details including trainer fee
$stmt = $pdo->prepare(
    'SELECT m.id, m.full_name, m.phone, m.plan_id, m.trainer_id, 
            p.name as plan_name, p.price, 
            t.name as trainer_name, t.fee as trainer_fee
     FROM members m 
     JOIN membership_plans p ON m.plan_id = p.id 
     LEFT JOIN trainers t ON m.trainer_id = t.id 
     WHERE m.status = "active" 
     ORDER BY m.full_name ASC'
);
$stmt->execute();
$members = $stmt->fetchAll();

// Set page title
$pageTitle = 'Payments';

// Additional JS
$additionalJS = '<script src="' . APP_URL . '/assets/js/payments.js"></script>';

// Start building page content
$pageContent = '
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-credit-card"></i> Payments</h1>
        <p>Manage payments and receipts</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal" onclick="addPaymentForm()">
        <i class="fas fa-plus"></i> Add Payment
    </button>
</div>

<!-- Payments Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="paymentsTable">
                <thead>
                    <tr>
                        <th>Membership No</th>
                        <th>Member</th>
                        <th>Fee Type</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Date</th>
                        <th>Receipt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="paymentsTableBody">
                    <!-- Loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm">
                <input type="hidden" id="paymentId" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="membershipNoInput" class="form-label">Membership No (Scan/Type)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                            <input type="text" class="form-control" id="membershipNoInput" placeholder="Scan or Enter ID" autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" onclick="findMemberByNo()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <small class="text-muted">Enter ID to auto-select member</small>
                    </div>

                    <div id="memberSummaryContainer" class="mb-3" style="display:none;">
                        <!-- Filled by JS -->
                    </div>

                    <div class="mb-3">
                        <label for="paymentMember" class="form-label">Member <span class="text-danger">*</span></label>
                        <select class="form-select" id="paymentMember" name="member_id" required>
                            <option value="">Select Member</option>';

foreach ($members as $member) {
    $trainerId = $member['trainer_id'] ?? '';
    $trainerName = $member['trainer_name'] ?? '';
    $trainerFee = $member['trainer_fee'] ?? 0;
    $planName = $member['plan_name'] ?? '';
    $pageContent .= '<option value="' . $member['id'] . '" data-price="' . $member['price'] . '" data-plan-name="' . escapeHtml($planName) . '" data-trainer-id="' . $trainerId . '" data-trainer-name="' . escapeHtml($trainerName) . '" data-trainer-fee="' . $trainerFee . '">' . escapeHtml($member['full_name']) . ' (' . escapeHtml($member['phone']) . ')</option>';
}

$pageContent .= '
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Fees</label>
                        <table class="table table-bordered table-sm" id="feeItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Fee Type</th>
                                    <th width="120">Amount</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="defaultFeesBody">
                                <!-- Default Fees (Read-only) -->
                            </tbody>
                            <tbody id="customFeesBody">
                                <!-- Custom Fees (Editable) -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-end">Subtotal:</th>
                                    <th>
                                        <input type="number" class="form-control form-control-sm" id="paymentSubtotal" readonly value="0.00">
                                    </th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th class="text-end">Discount (%):</th>
                                    <th>
                                        <input type="number" class="form-control form-control-sm" id="paymentDiscountPercent" name="discount_percent" value="0" min="0" max="100" oninput="calculateTotal()">
                                    </th>
                                    <th></th>
                                </tr>
                                <tr>
                                    <th class="text-end text-primary">Total:</th>
                                    <th>
                                        <input type="number" class="form-control form-control-sm fw-bold border-primary text-primary" id="paymentTotal" readonly value="0.00">
                                        <input type="hidden" id="paymentDiscountAmount" name="discount_amount" value="0">
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                        <button type="button" class="btn btn-sm btn-success" onclick="addCustomFeeRow()">
                            <i class="fas fa-plus"></i> Add Additional Fee
                        </button>
                        <small class="d-block mt-2 text-muted"><strong>Note:</strong> Membership and Trainer fees are auto-selected and cannot be edited.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="paymentMethod" class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" id="paymentMethod" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="easypaisa">Easy Paisa</option>
                            <option value="jazzcash">Jazz Cash</option>
                            <option value="nayapay">Naya Pay</option>
                            <option value="sadapay">Sada Pay</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="paymentDate" class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="paymentDate" name="payment_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="paymentDesc" class="form-label">Description</label>
                        <textarea class="form-control" id="paymentDesc" name="description" rows="2" placeholder="Optional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
';

// Output page with layout
$additionalJS .= '<script>
let feeTypes = ' . $feeTypesJson . ';

console.log("FeeTypes loaded:", feeTypes);
console.log("FeeTypes count:", feeTypes.length);
if (feeTypes.length > 0) {
    console.log("First fee type:", feeTypes[0]);
}

function loadPayments() {
    $.ajax({
        url: "' . APP_URL . '/ajax/payments_get.php",
        type: "POST",
        dataType: "json",
        success: function(response) {
            let html = "";
            if (response.length === 0) {
                html = "<tr><td colspan=\'8\' class=\'text-center text-muted py-4\'>No payments found</td></tr>";
            } else {
                response.forEach(payment => {
                    let methodBadge = "";
                    switch(payment.payment_method) {
                        case "cash":
                            methodBadge = "<span class=\'badge bg-warning\'>Cash</span>";
                            break;
                        case "easypaisa":
                            methodBadge = "<span class=\'badge bg-info\'>Easy Paisa</span>";
                            break;
                        case "jazzcash":
                            methodBadge = "<span class=\'badge bg-primary\'>Jazz Cash</span>";
                            break;
                        case "nayapay":
                            methodBadge = "<span class=\'badge bg-success\'>Naya Pay</span>";
                            break;
                        case "sadapay":
                            methodBadge = "<span class=\'badge bg-secondary\'>Sada Pay</span>";
                            break;
                        case "bank_transfer":
                            methodBadge = "<span class=\'badge bg-dark\'>Bank Transfer</span>";
                            break;
                        default:
                            methodBadge = "<span class=\'badge bg-light text-dark\'>" + payment.payment_method + "</span>";
                    }
                    
                    html += `
                        <tr>
                            <td><span class="badge bg-secondary">${String(payment.member_id).padStart(6, "0")}</span></td>
                            <td><strong>${escapeHtml(payment.full_name)}</strong></td>
                            <td><span class="badge bg-success">${payment.fee_type_name || "Membership Fee"}</span></td>
                            <td><strong>Rs ${parseFloat(payment.amount).toLocaleString("en-PK")}</strong></td>
                            <td>${methodBadge}</td>
                            <td>${formatDate(payment.payment_date)}</td>
                            <td><small class="text-muted">${payment.receipt_number || "-"}</small></td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-info" onclick="printReceipt(${payment.id})" title="Print Receipt">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deletePayment(${payment.id})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                });
            }
            document.getElementById("paymentsTableBody").innerHTML = html;
        }
    });
}

function deletePayment(id) {
    if (confirm("Are you sure you want to delete this payment?")) {
        $.ajax({
            url: "' . APP_URL . '/ajax/payments_delete.php",
            type: "POST",
            data: { id: id },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    APP.showSuccess(response.message);
                    loadPayments();
                } else {
                    APP.showError(response.message);
                }
            }
        });
    }
}

function addPaymentForm() {
    document.getElementById("paymentForm").reset();
    document.getElementById("paymentId").value = "";
    document.getElementById("paymentDate").valueAsDate = new Date();
    document.getElementById("defaultFeesBody").innerHTML = "";
    document.getElementById("customFeesBody").innerHTML = "";
    document.getElementById("paymentSubtotal").value = "0.00";
    document.getElementById("paymentDiscountPercent").value = "0";
    document.getElementById("paymentTotal").value = "0.00";
    document.getElementById("paymentDiscountAmount").value = "0";
    document.getElementById("memberSummaryContainer").style.display = "none";
    document.getElementById("memberSummaryContainer").innerHTML = "";
    
    document.getElementById("membershipNoInput").focus();
    document.querySelector("#paymentModal .modal-title").textContent = "Add Payment";
    document.querySelector("#paymentForm button[type=\'submit\']").textContent = "Save Payment";
}

// Add read-only default fee row (for Membership and Trainer fees)
function addDefaultFeeRow(feeTypeId, feeName, amount) {
    const tbody = document.getElementById("defaultFeesBody");
    const tr = document.createElement("tr");
    
    console.log("addDefaultFeeRow called with:", { feeTypeId, feeName, amount });
    
    tr.classList.add("table-light");
    tr.innerHTML = "<td><span class=\"badge bg-secondary\">" + escapeHtml(feeName) + "</span></td>" + 
                   "<td><input type=\"text\" class=\"form-control form-control-sm\" value=\"Rs " + parseFloat(amount).toFixed(2) + "\" readonly></td>" + 
                   "<td class=\"text-center\"><span class=\"badge bg-info\">Fixed</span></td>";
    
    tr.setAttribute("data-fee-type-id", feeTypeId);
    tr.setAttribute("data-amount", amount);
    
    tbody.appendChild(tr);
    console.log("Default fee row added to tbody");
}

// Add editable custom fee row
function addCustomFeeRow(defaultId = null, defaultAmount = null) {
    const tbody = document.getElementById("customFeesBody");
    const tr = document.createElement("tr");
    
    let options = "<option value=\"\">Select Fee Type</option>";
    if (!feeTypes || feeTypes.length === 0) {
        console.error("feeTypes array is empty or not loaded in addCustomFeeRow!");
    } else {
        console.log("Building options from feeTypes:", feeTypes.length);
    }

    for (let i = 0; i < feeTypes.length; i++) {
        const ft = feeTypes[i];
        const isSelected = (defaultId != null && String(ft.id) === String(defaultId));
        const selectedAttr = isSelected ? "selected" : "";
        options += "<option value=\"" + ft.id + "\" data-amount=\"" + ft.default_amount + "\" " + selectedAttr + ">" + ft.name + "</option>";
    }
    
    tr.innerHTML = "<td><select class=\"form-select form-select-sm fee-type-select\" onchange=\"updateRowAmount(this)\" required>" + options + "</select></td>" + 
                   "<td><input type=\"number\" class=\"form-control form-control-sm fee-amount-input\" value=\"" + (defaultAmount !== null ? defaultAmount : "") + "\" step=\"0.01\" min=\"0\" oninput=\"calculateTotal()\" required></td>" + 
                   "<td class=\"text-center\"><button type=\"button\" class=\"btn btn-sm btn-outline-danger\" onclick=\"removeCustomFeeRow(this)\"><i class=\"fas fa-times\"></i></button></td>";
    
    console.log("Adding Custom Fee Row [ID: " + defaultId + ", Amt: " + defaultAmount + "]");
    tbody.appendChild(tr);
    
    const select = tr.querySelector(".fee-type-select");
    
    if (defaultId != null) {
        select.value = defaultId;
        console.log("Set select value to:", select.value);
        if (select.value != defaultId) {
            console.warn("Could not select defaultId " + defaultId + ". It might be missing from feeTypes list.");
        }
    }

    if (defaultId != null && defaultAmount === null) {
        updateRowAmount(select);
    } else if (defaultAmount !== null) {
        calculateTotal();
    }
}

function removeCustomFeeRow(btn) {
    btn.closest("tr").remove();
    calculateTotal();
}

function updateRowAmount(select) {
    const amountInput = select.closest("tr").querySelector(".fee-amount-input");
    const selectedOption = select.options[select.selectedIndex];
    const defaultAmount = selectedOption.getAttribute("data-amount");
    
    // Only set if input is empty or we just switched types
    if (defaultAmount) {
        amountInput.value = defaultAmount;
    }
    calculateTotal();
}

function calculateTotal() {
    let subtotal = 0;
    
    // Add default fees amounts
    document.querySelectorAll("#defaultFeesBody tr").forEach(tr => {
        const amount = parseFloat(tr.getAttribute("data-amount"));
        if (!isNaN(amount)) subtotal += amount;
    });
    
    // Add custom fees amounts
    document.querySelectorAll(".fee-amount-input").forEach(input => {
        const val = parseFloat(input.value);
        if (!isNaN(val)) subtotal += val;
    });
    
    const discountPercent = parseFloat(document.getElementById("paymentDiscountPercent").value) || 0;
    const discountAmount = subtotal * (discountPercent / 100);
    const total = subtotal - discountAmount;
    
    console.log("Calculated Subtotal:", subtotal, "Discount:", discountAmount, "Total:", total);
    
    document.getElementById("paymentSubtotal").value = subtotal.toFixed(2);
    document.getElementById("paymentDiscountAmount").value = discountAmount.toFixed(2);
    document.getElementById("paymentTotal").value = total.toFixed(2);
}

function populateFeesForMember(option) {
    if (!option || !option.value) {
        document.getElementById("memberSummaryContainer").style.display = "none";
        return;
    }
    
    console.log("populateFeesForMember called with option:", option);
    
    const memberId = option.value;
    const price = option.getAttribute("data-price");
    const planName = option.getAttribute("data-plan-name");
    const trainerName = option.getAttribute("data-trainer-name");
    const trainerFee = option.getAttribute("data-trainer-fee");
    
    console.log("Member data:", { memberId, price, planName, trainerName, trainerFee });
    
    // Update Summary UI
    const summary = document.getElementById("memberSummaryContainer");
    summary.style.display = "block";
    summary.innerHTML = "<div class=\"alert alert-info py-2 px-3 mb-0\" style=\"font-size: 0.9rem; border-left: 5px solid #0dcaf0;\">" + 
        "<div class=\"d-flex justify-content-between\"><strong>Member:</strong> <span>" + option.text + "</span></div>" + 
        "<div class=\"d-flex justify-content-between\"><strong>Plan:</strong> <span>" + (planName || "N/A") + " (Rs " + (price || "0") + ")</span></div>" + 
        (trainerName ? "<div class=\"d-flex justify-content-between\"><strong>Trainer:</strong> <span>" + trainerName + " (Rs " + (trainerFee || "0") + ")</span></div>" : "") + 
        "</div>";
    
    // Clear previous default fees
    document.getElementById("defaultFeesBody").innerHTML = "";
    document.getElementById("customFeesBody").innerHTML = "";
    
    // 1. FORCE ADD Membership Fee (default, read-only) - Always add regardless
    console.log("FORCE Adding Membership Fee with price:", price);
    addDefaultFeeRow(1, "Membership Fee", price || "0.00");
    
    // 2. FORCE ADD Trainer Fee if assigned (default, read-only)
    if (trainerName && parseFloat(trainerFee) > 0) {
        // Use the actual trainer name instead of the generic label
        console.log("FORCE Adding Trainer Fee:", trainerName, trainerFee);
        const label = trainerName ? trainerName : "Personal Trainer";
        addDefaultFeeRow(4, label, trainerFee);
    }
    
    // 3. New Member Check (Admission Fee Suggestion - as custom fee)
    $.ajax({
        url: "' . APP_URL . '/ajax/payments_check_previous.php",
        type: "POST",
        data: { member_id: memberId },
        dataType: "json",
        success: function(response) {
            console.log("Payment check response:", response);
            if (response.success && !response.has_payments) {
                // Suggest Admission Fee (ID 2 usually)
                const adminFeeType = feeTypes.find(ft => parseInt(ft.id) === 2 || ft.name.toLowerCase().includes("admission"));
                console.log("Admin fee type found:", adminFeeType);
                if (adminFeeType) {
                    addCustomFeeRow(adminFeeType.id);
                    APP.showSuccess("New member detected. Admission Fee suggested.");
                    calculateTotal();
                }
            }
        },
        complete: function() {
            calculateTotal();
        }
    });
    
    calculateTotal();
}

function findMemberByNo() {
    const input = document.getElementById("membershipNoInput");
    const id = parseInt(input.value.trim(), 10);
    const select = document.getElementById("paymentMember");
    
    if (!id) return;
    
    let found = false;
    for (let i = 0; i < select.options.length; i++) {
        if (parseInt(select.options[i].value) === id) {
            select.selectedIndex = i;
            // This will trigger the "change" event listener which calls populateFeesForMember
            select.dispatchEvent(new Event("change"));
            
            found = true;
            input.classList.add("is-valid");
            input.classList.remove("is-invalid");
            break;
        }
    }
    
    if (!found) {
        input.classList.add("is-invalid");
        input.classList.remove("is-valid");
        select.value = "";
        APP.showError("Member not found with ID: " + id);
    }
}

// Auto-search on Enter key
document.getElementById("membershipNoInput").addEventListener("keydown", function(e) {
    if (e.key === "Enter") {
        e.preventDefault();
        findMemberByNo();
    }
});

function printReceipt(id) {
    window.open("' . APP_URL . '/views/payments/receipt.php?id=" + id, "_blank");
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(date) {
    return new Date(date).toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" });
}

document.addEventListener("DOMContentLoaded", function() {
    loadPayments();
});

// Auto-update price when member changes manually
document.getElementById("paymentMember").addEventListener("change", function() {
    const selectedOption = this.options[this.selectedIndex];
    populateFeesForMember(selectedOption);
});

// Form submission
document.getElementById("paymentForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const id = document.getElementById("paymentId").value;
    
    // Collect Items from both default and custom fees
    const items = [];
    
    // Collect default fees
    document.querySelectorAll("#defaultFeesBody tr").forEach(tr => {
        const feeTypeId = tr.getAttribute("data-fee-type-id");
        const amount = tr.getAttribute("data-amount");
        if (feeTypeId && amount) {
            items.push({ fee_type_id: feeTypeId, amount: amount });
        }
    });
    
    // Collect custom fees
    document.querySelectorAll("#customFeesBody tr").forEach(tr => {
        const feeTypeId = tr.querySelector(".fee-type-select").value;
        const amount = tr.querySelector(".fee-amount-input").value;
        if (feeTypeId && amount) {
            items.push({ fee_type_id: feeTypeId, amount: amount });
        }
    });
    
    if (items.length === 0) {
        APP.showError("Please add at least one fee item");
        return;
    }
    
    formData.append("items", JSON.stringify(items));
    
    const url = id ? "' . APP_URL . '/ajax/payments_update.php" : "' . APP_URL . '/ajax/payments_add.php";
    
    $.ajax({
        url: url,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function(response) {
            if (response.success) {
                APP.showSuccess(response.message);
                bootstrap.Modal.getInstance(document.getElementById("paymentModal")).hide();
                loadPayments();
                
                if (response.id) {
                    printReceipt(response.id);
                }
            } else {
                APP.showError(response.message);
            }
        }
    });
});
</script>';

include __DIR__ . '/../../views/layout/header.php';
?>
