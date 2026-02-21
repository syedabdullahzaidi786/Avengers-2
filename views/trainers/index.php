<?php
/**
 * Trainers List Page
 */

require_once __DIR__ . '/../../config/database.php';

// Page Title
$pageTitle = 'Trainers';

// Additional JS
$additionalJS = '
<script src="' . APP_URL . '/assets/js/trainers.js"></script>
';

// Page Content
$pageContent = '
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-user-ninja"></i> Trainers</h1>
        <p>Manage gym trainers and commissions</p>
    </div>
    <div class="d-flex gap-2">
        <a href="commissions_setup.php" class="btn btn-dark">
            <i class="fas fa-cog"></i> Commission Setup
        </a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#trainerModal" onclick="addTrainerForm()">
            <i class="fas fa-plus"></i> Add Trainer
        </button>
    </div>
</div>

<!-- Search -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <input type="text" id="trainerSearch" class="form-control" placeholder="Search by name or phone...">
            </div>
        </div>
    </div>
</div>

<!-- Trainers Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable" id="trainersTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Specialization</th>
                        <th>Fee (Rs)</th>
                        <th>Commission Rate (%)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="trainersTableBody">
                    <!-- Loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Trainer Modal -->
<div class="modal fade" id="trainerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="trainerModalTitle">Add Trainer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="trainerForm">
                <div class="modal-body">
                    <input type="hidden" id="trainerId" name="id">
                    
                    <div class="mb-3">
                        <label for="trainerName" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="trainerName" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="trainerPhone" class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="trainerPhone" name="phone" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="trainerSpec" class="form-label">Specialization</label>
                        <input type="text" class="form-control" id="trainerSpec" name="specialization" placeholder="e.g. Bodybuilding, Yoga">
                    </div>

                    <div class="mb-3">
                        <label for="trainerFee" class="form-label">Monthly Fee (Rs)</label>
                        <input type="number" class="form-control" id="trainerFee" name="fee" min="0" step="0.01" value="0.00">
                        <small class="text-muted">Standard monthly fee for this trainer.</small>
                    </div>

                    <div class="mb-3">
                        <label for="trainerCommission" class="form-label">Commission Rate (%)</label>
                        <input type="number" class="form-control" id="trainerCommission" name="commission_rate" min="0" max="100" step="0.01" value="80.00">
                        <small class="text-muted">Percentage of payment given to trainer.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="trainerSubmitBtn">Save Trainer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Main Trainer Logic
function loadTrainers() {
    const search = document.getElementById("trainerSearch").value;
    
    $.ajax({
        url: "' . APP_URL . '/ajax/trainers_get.php",
        type: "POST",
        data: { search: search },
        dataType: "json",
        success: function(response) {
            let html = "";
            if (response.length === 0) {
                html = "<tr><td colspan=\"5\" class=\"text-center text-muted py-4\">No trainers found</td></tr>";
            } else {
                response.forEach(trainer => {
                    html += `
                        <tr>
                            <td><strong>${escapeHtml(trainer.name)}</strong></td>
                            <td>${escapeHtml(trainer.phone)}</td>
                            <td>${escapeHtml(trainer.specialization || "-")}</td>
                            <td><strong>Rs ${parseFloat(trainer.fee || 0).toLocaleString()}</strong></td>
                            <td>${parseFloat(trainer.commission_rate).toFixed(2)}%</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editTrainer(${trainer.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteTrainer(${trainer.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                });
            }
            document.getElementById("trainersTableBody").innerHTML = html;
        }
    });
}

function addTrainerForm() {
    document.getElementById("trainerForm").reset();
    document.getElementById("trainerId").value = "";
    document.getElementById("trainerCommission").value = "80.00";
    document.getElementById("trainerFee").value = "0.00";
    document.getElementById("trainerModalTitle").textContent = "Add Trainer";
    document.getElementById("trainerSubmitBtn").textContent = "Save Trainer";
}

function editTrainer(id) {
    $.ajax({
        url: "' . APP_URL . '/ajax/trainers_get_single.php",
        type: "POST",
        data: { id: id },
        dataType: "json",
        success: function(trainer) {
            document.getElementById("trainerId").value = trainer.id;
            document.getElementById("trainerName").value = trainer.name;
            document.getElementById("trainerPhone").value = trainer.phone;
            document.getElementById("trainerSpec").value = trainer.specialization;
            document.getElementById("trainerCommission").value = trainer.commission_rate;
            document.getElementById("trainerFee").value = trainer.fee;
            
            document.getElementById("trainerModalTitle").textContent = "Edit Trainer";
            document.getElementById("trainerSubmitBtn").textContent = "Update Trainer";
            
            const modal = new bootstrap.Modal(document.getElementById("trainerModal"));
            modal.show();
        }
    });
}

function deleteTrainer(id) {
    APP.showConfirm("Delete Trainer", "Are you sure? This will remove the trainer link from members.", function() {
        $.ajax({
            url: "' . APP_URL . '/ajax/trainers_delete.php",
            type: "POST",
            data: { id: id },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    APP.showSuccess(response.message);
                    loadTrainers();
                } else {
                    APP.showError(response.message);
                }
            }
        });
    });
}

function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

// Event Listeners
document.addEventListener("DOMContentLoaded", function() {
    loadTrainers();
    document.getElementById("trainerSearch").addEventListener("keyup", loadTrainers);
});

document.getElementById("trainerForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const id = document.getElementById("trainerId").value;
    const url = id ? "' . APP_URL . '/ajax/trainers_update.php" : "' . APP_URL . '/ajax/trainers_add.php";
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
                bootstrap.Modal.getInstance(document.getElementById("trainerModal")).hide();
                loadTrainers();
            } else {
                APP.showError(response.message);
            }
        }
    });
});
</script>
';

include __DIR__ . '/../../views/layout/header.php';
?>
