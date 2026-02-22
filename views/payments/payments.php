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
                            <tbody id="feeItemsBody">
                                <!-- Dynamic Rows -->
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
                        <button type="button" class="btn btn-sm btn-success" onclick="addFeeRow()">
                            <i class="fas fa-plus"></i> Add Fee
                        </button>
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
    document.getElementById("feeItemsBody").innerHTML = "";
    document.getElementById("paymentSubtotal").value = "0.00";
    document.getElementById("paymentDiscountPercent").value = "0";
    document.getElementById("paymentTotal").value = "0.00";
    document.getElementById("paymentDiscountAmount").value = "0";
    document.getElementById("memberSummaryContainer").style.display = "none";
    document.getElementById("memberSummaryContainer").innerHTML = "";
    
    // Add default row
    addFeeRow(1); // Default to Membership Fee
    
    document.getElementById("membershipNoInput").focus();
    document.querySelector("#paymentModal .modal-title").textContent = "Add Payment";
    document.querySelector("#paymentForm button[type=\'submit\']").textContent = "Save Payment";
}

function addFeeRow(defaultId = null, defaultAmount = null) {
    const tbody = document.getElementById("feeItemsBody");
    const tr = document.createElement("tr");
    
    let options = "<option value=\"\">Select Fee Type</option>";
    if (!feeTypes || feeTypes.length === 0) {
        console.error("feeTypes array is empty or not loaded in addFeeRow!");
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
                   "<td class=\"text-center\"><button type=\"button\" class=\"btn btn-sm btn-outline-danger\" onclick=\"removeFeeRow(this)\"><i class=\"fas fa-times\"></i></button></td>";
    
    console.log("Adding Fee Row [ID: " + defaultId + ", Amt: " + defaultAmount + "]");
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

function removeFeeRow(btn) {
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

function checkAndAddTrainerFee(option) {
    const trainerId = option.getAttribute("data-trainer-id");
    const trainerName = option.getAttribute("data-trainer-name");
    const trainerFee = parseFloat(option.getAttribute("data-trainer-fee")) || 0;
    
    console.log("Checking Trainer Fee:", { trainerId, trainerName, trainerFee });
    
    if (trainerId && trainerFee > 0) {
        let exists = false;
        document.querySelectorAll("#feeItemsBody tr").forEach(row => {
            const feeSelect = row.querySelector(".fee-type-select");
            if (feeSelect && feeSelect.selectedIndex >= 0) {
                const selectedOption = feeSelect.options[feeSelect.selectedIndex];
                if (selectedOption) {
                    const text = selectedOption.text.toLowerCase();
                    // Check if row is already a trainer fee
                    if (text.includes("trainer") || text.includes("training") || (trainerName && text.includes(trainerName.toLowerCase()))) {
                        exists = true;
                    }
                }
            }
        });
        
        console.log("Trainer Fee exists in table?", exists);
        
        if (!exists) {
            const ptFeeType = feeTypes.find(ft => 
                (trainerName && ft.name.toLowerCase().includes(trainerName.toLowerCase())) || 
                ft.name.toLowerCase().includes("trainer") || 
                ft.name.toLowerCase().includes("personal") ||
                ft.name.toLowerCase().includes("training")
            );
            
            console.log("Matched Fee Type for Trainer:", ptFeeType);
            
            if (ptFeeType) {
                addFeeRow(ptFeeType.id, trainerFee);
            } else {
                console.warn("No \"Trainer\" fee type found in feeTypes:", feeTypes);
            }
        }
    }
}

function populateFeesForMember(option) {
    if (!option || !option.value) {
        document.getElementById("memberSummaryContainer").style.display = "none";
        return;
    }
    
    const memberId = option.value;
    const price = option.getAttribute("data-price");
    const planName = option.getAttribute("data-plan-name");
    const trainerName = option.getAttribute("data-trainer-name");
    const trainerFee = option.getAttribute("data-trainer-fee");
    
    // Update Summary UI
    const summary = document.getElementById("memberSummaryContainer");
    summary.style.display = "block";
    summary.innerHTML = "<div class=\"alert alert-info py-2 px-3 mb-0\" style=\"font-size: 0.9rem; border-left: 5px solid #0dcaf0;\">" + 
        "<div class=\"d-flex justify-content-between\"><strong>Member:</strong> <span>" + option.text + "</span></div>" + 
        "<div class=\"d-flex justify-content-between\"><strong>Plan:</strong> <span>" + (planName || "N/A") + " (Rs " + (price || "0") + ")</span></div>" + 
        (trainerName ? "<div class=\"d-flex justify-content-between\"><strong>Trainer:</strong> <span>" + trainerName + " (Rs " + (trainerFee || "0") + ")</span></div>" : "") + 
        "</div>";
    
    // 1. Handle Membership Fee
    let membershipRowFound = false;
    const allRows = document.querySelectorAll("#feeItemsBody tr");
    console.log("Populating fees for Member ID: " + memberId + ". Current rows: " + allRows.length);
    
    allRows.forEach((row, idx) => {
        const select = row.querySelector(".fee-type-select");
        if (select && String(select.value) === "1") { // 1 is Membership Fee
            const amountInput = row.querySelector(".fee-amount-input");
            if (amountInput) {
                amountInput.value = price || "0.00";
                membershipRowFound = true;
                console.log("Row " + idx + ": Updated existing Membership row price to " + amountInput.value);
            }
        }
    });
    
    if (!membershipRowFound) {
        if (allRows.length === 1) {
            const firstSelect = allRows[0].querySelector(".fee-type-select");
            const firstAmount = allRows[0].querySelector(".fee-amount-input");
            if (firstSelect && (firstSelect.value === "" || firstSelect.value == 1)) {
                console.log("Reusing first row for Membership Fee");
                firstSelect.value = 1;
                firstAmount.value = price || "0.00";
                membershipRowFound = true;
            }
        }
    }
    
    if (!membershipRowFound && price != null) {
        console.log("Adding NEW Membership Fee row for price: " + price);
        addFeeRow(1, price);
    }
    
    // 2. Handle Trainer Fee
    checkAndAddTrainerFee(option);
    
    // 3. New Member Check (Admission Fee Suggestion)
    $.ajax({
        url: "' . APP_URL . '/ajax/payments_check_previous.php",
        type: "POST",
        data: { member_id: memberId },
        dataType: "json",
        success: function(response) {
            if (response.success && !response.has_payments) {
                // Suggest Admission Fee (ID 2 usually)
                const adminFeeType = feeTypes.find(ft => ft.id == 2 || ft.name.toLowerCase().includes("admission"));
                if (adminFeeType) {
                    let adminExists = false;
                    document.querySelectorAll("#feeItemsBody tr").forEach(r => {
                        const s = r.querySelector(".fee-type-select");
                        if (s && s.value == adminFeeType.id) adminExists = true;
                    });
                    
                    if (!adminExists) {
                        addFeeRow(adminFeeType.id);
                        APP.showInfo("New member detected. Admission Fee suggested.");
                        calculateTotal();
                    }
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
    
    // Collect Items
    const items = [];
    document.querySelectorAll("#feeItemsBody tr").forEach(tr => {
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
