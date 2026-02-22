<?php
/**
 * Fee Types Page
 * Manage fee types (Admission, Personal Training, etc.)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/FeeType.php';

$feeTypeModel = new FeeType($pdo);
// $feeTypes = $feeTypeModel->getAllFeeTypes(); // Loaded via AJAX

// Set page title
$pageTitle = 'Fee Types';

// Start building page content
$pageContent = '
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-tags"></i> Fee Types</h1>
        <p>Manage additional fee types</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#feeTypeModal" onclick="addFeeTypeForm()">
        <i class="fas fa-plus"></i> Add Fee Type
    </button>
</div>

<!-- Fee Types Grid -->
<div class="row mb-4" id="feeTypesContainer">
    <!-- Loaded via JavaScript -->
</div>

<!-- Add/Edit Fee Type Modal -->
<div class="modal fade" id="feeTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feeTypeModalTitle">Add Fee Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="feeTypeForm">
                <div class="modal-body">
                    <input type="hidden" id="feeTypeId" name="id">
                    
                    <div class="mb-3">
                        <label for="feeTypeName" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="feeTypeName" name="name" placeholder="e.g., Admission Fee" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="feeTypeAmount" class="form-label">Default Amount (PKR) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="feeTypeAmount" name="default_amount" placeholder="1000" step="0.01" min="0" required>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="feeTypeCommissionable" name="is_commissionable" value="1">
                            <label class="form-check-label" for="feeTypeCommissionable">
                                Commissionable (Trainer gets commission on this fee)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="feeTypeActive" name="is_active" value="1" checked>
                            <label class="form-check-label" for="feeTypeActive">
                                Active (Show in dropdown)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="feeTypeSubmitBtn">Save Fee Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function loadFeeTypes() {
    $.ajax({
        url: "' . APP_URL . '/ajax/fee_types_get.php",
        type: "GET",
        dataType: "json",
        success: function(response) {
            let html = "";
            if (response.length === 0) {
                html = "<div class=\"col-12\"><p class=\"text-center text-muted py-5\">No fee types found</p></div>";
            } else {
                response.forEach(ft => {
                    const isCommissionable = ft.is_commissionable == 1 ? \'<span class="badge bg-success">Commissionable</span>\' : \'<span class="badge bg-secondary">No Commission</span>\';
                    const isActive = ft.is_active == 1 ? \'<span class="badge bg-info">Active</span>\' : \'<span class="badge bg-warning">Inactive</span>\';
                    
                    const cardHtml = "<div class=\"col-md-6 col-lg-4 mb-4\">" +
                        "<div class=\"card h-100\">" +
                        "<div class=\"card-body\">" +
                        "<div class=\"d-flex justify-content-between align-items-start mb-2\">" +
                        "<h5 class=\"card-title mb-0\">" + escapeHtml(ft.name) + "</h5>" +
                        "<div>" + isActive + "</div>" +
                        "</div>" +
                        "<div class=\"mb-2\">" +
                        "<small>" + isCommissionable + "</small>" +
                        "</div>" +
                        "<div class=\"mb-3\">" +
                        "<span class=\"h4 text-primary\">Rs " + parseFloat(ft.default_amount).toLocaleString("en-PK") + "</span>" +
                        "</div>" +
                        "<div class=\"btn-group w-100\" role=\"group\">" +
                        "<button class=\"btn btn-sm btn-warning\" onclick=\"editFeeType(" + ft.id + ")\">" +
                        "<i class=\"fas fa-edit\"></i> Edit" +
                        "</button>" +
                        "<button class=\"btn btn-sm btn-danger\" onclick=\"deleteFeeType(" + ft.id + ")\">" +
                        "<i class=\"fas fa-trash\"></i> Delete" +
                        "</button>" +
                        "</div>" +
                        "</div>" +
                        "</div>" +
                        "</div>";
                    
                    html += cardHtml;
                });
            }
            document.getElementById("feeTypesContainer").innerHTML = html;
        },
        error: function() {
            document.getElementById("feeTypesContainer").innerHTML = "<div class=\"col-12\"><p class=\"text-center text-danger py-5\">Error loading fee types</p></div>";
        }
    });
}

function addFeeTypeForm() {
    document.getElementById("feeTypeForm").reset();
    document.getElementById("feeTypeId").value = "";
    document.getElementById("feeTypeModalTitle").textContent = "Add Fee Type";
    document.getElementById("feeTypeSubmitBtn").textContent = "Save Fee Type";
}

function editFeeType(id) {
    $.ajax({
        url: "' . APP_URL . '/ajax/fee_types_get_single.php",
        type: "POST",
        data: { id: id },
        dataType: "json",
        success: function(ft) {
            document.getElementById("feeTypeId").value = ft.id;
            document.getElementById("feeTypeName").value = ft.name;
            document.getElementById("feeTypeAmount").value = ft.default_amount;
            document.getElementById("feeTypeCommissionable").checked = ft.is_commissionable == 1;
            document.getElementById("feeTypeActive").checked = ft.is_active == 1;
            
            document.getElementById("feeTypeModalTitle").textContent = "Edit Fee Type";
            document.getElementById("feeTypeSubmitBtn").textContent = "Update Fee Type";
            
            const modal = new bootstrap.Modal(document.getElementById("feeTypeModal"));
            modal.show();
        }
    });
}

function deleteFeeType(id) {
    APP.showConfirm("Delete Fee Type", "Are you sure you want to delete this fee type?", function() {
        $.ajax({
            url: "' . APP_URL . '/ajax/fee_types_delete.php",
            type: "POST",
            data: { id: id },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    APP.showSuccess(response.message);
                    loadFeeTypes();
                } else {
                    APP.showError(response.message);
                }
            }
        });
    });
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

// Event listeners
document.addEventListener("DOMContentLoaded", function() {
    loadFeeTypes();
});

// Form submission
document.getElementById("feeTypeForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const id = document.getElementById("feeTypeId").value;
    const url = id ? "' . APP_URL . '/ajax/fee_types_update.php" : "' . APP_URL . '/ajax/fee_types_add.php";
    
    const formData = new FormData(this);
    
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
                bootstrap.Modal.getInstance(document.getElementById("feeTypeModal")).hide();
                loadFeeTypes();
            } else {
                APP.showError(response.message);
            }
        }
    });
});
</script>
';

// Output page with layout
include __DIR__ . '/../../views/layout/header.php';
?>
