<?php
/**
 * Membership Plans Page
 * Manage gym membership plans
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Plan.php';

$planModel = new Plan($pdo);
$plans = $planModel->getAllPlans(false);

// Set page title
$pageTitle = 'Membership Plans';

// Additional JS
$additionalJS = '<script src="' . APP_URL . '/assets/js/plans.js"></script>';

// Start building page content
$pageContent = '
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-folder"></i> Membership Plans</h1>
        <p>Manage gym membership plans</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#planModal" onclick="addPlanForm()">
        <i class="fas fa-plus"></i> Add Plan
    </button>
</div>

<!-- Plans Grid -->
<div class="row mb-4" id="plansContainer">
    <!-- Loaded via JavaScript -->
</div>

<!-- Add/Edit Plan Modal -->
<div class="modal fade" id="planModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planModalTitle">Add Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="planForm">
                <div class="modal-body">
                    <input type="hidden" id="planId" name="id">
                    
                    <div class="mb-3">
                        <label for="planName" class="form-label">Plan Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="planName" name="name" placeholder="e.g., 1 Month" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="planDuration" class="form-label">Duration (days) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="planDuration" name="duration" placeholder="30" min="1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="planPrice" class="form-label">Price (PKR) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="planPrice" name="price" placeholder="2500" step="0.01" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="planDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="planDescription" name="description" rows="3" placeholder="Optional description..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="planSubmitBtn">Save Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function loadPlans() {
    $.ajax({
        url: "' . APP_URL . '/ajax/plans_get.php",
        type: "POST",
        dataType: "json",
        success: function(response) {
            let html = "";
            if (response.length === 0) {
                html = "<div class=\"col-12\"><p class=\"text-center text-muted py-5\">No plans found</p></div>";
            } else {
                response.forEach(plan => {
                    html += `
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">${escapeHtml(plan.name)}</h5>
                                    <p class="text-muted">${plan.duration} days</p>
                                    <div class="mb-3">
                                        <span class="h4 text-primary">Rs ${parseFloat(plan.price).toLocaleString("en-PK")}</span>
                                    </div>
                                    <p class="card-text text-muted">${plan.description || "No description"}</p>
                                    <div class="btn-group w-100">
                                        <button class="btn btn-sm btn-warning" onclick="editPlan(${plan.id})">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deletePlan(${plan.id})">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });
            }
            document.getElementById("plansContainer").innerHTML = html;
        }
    });
}

function addPlanForm() {
    document.getElementById("planForm").reset();
    document.getElementById("planId").value = "";
    document.getElementById("planModalTitle").textContent = "Add Plan";
    document.getElementById("planSubmitBtn").textContent = "Save Plan";
}

function editPlan(id) {
    $.ajax({
        url: "' . APP_URL . '/ajax/plans_get_single.php",
        type: "POST",
        data: { id: id },
        dataType: "json",
        success: function(plan) {
            document.getElementById("planId").value = plan.id;
            document.getElementById("planName").value = plan.name;
            document.getElementById("planDuration").value = plan.duration;
            document.getElementById("planPrice").value = plan.price;
            document.getElementById("planDescription").value = plan.description || "";
            
            document.getElementById("planModalTitle").textContent = "Edit Plan";
            document.getElementById("planSubmitBtn").textContent = "Update Plan";
            
            const modal = new bootstrap.Modal(document.getElementById("planModal"));
            modal.show();
        }
    });
}

function deletePlan(id) {
    APP.showConfirm("Delete Plan", "Are you sure you want to delete this plan?", function() {
        $.ajax({
            url: "' . APP_URL . '/ajax/plans_delete.php",
            type: "POST",
            data: { id: id },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    APP.showSuccess(response.message);
                    loadPlans();
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
    loadPlans();
});

// Form submission
document.getElementById("planForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const id = document.getElementById("planId").value;
    const url = id ? "' . APP_URL . '/ajax/plans_update.php" : "' . APP_URL . '/ajax/plans_add.php";
    
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
                bootstrap.Modal.getInstance(document.getElementById("planModal")).hide();
                loadPlans();
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
