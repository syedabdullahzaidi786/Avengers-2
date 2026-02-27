<?php
/**
 * Members List Page
 * List all members with CRUD operations
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Member.php';
require_once __DIR__ . '/../../models/Plan.php';
require_once __DIR__ . '/../../models/Trainer.php';

$memberModel = new Member($pdo);
$planModel = new Plan($pdo);
$trainerModel = new Trainer($pdo);

$plans = $planModel->getAllPlans();
$trainers = $trainerModel->getAllTrainers();

$nextMemberId = $memberModel->getNextMemberId();

// Set page title
$pageTitle = 'Members';

// Additional CSS
$additionalCSS = '';

// Additional JS
$additionalJS = '<script src="' . APP_URL . '/assets/js/members.js"></script>';

// Start building page content
$pageContent = '
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-users"></i> Members</h1>
        <p>Manage gym members</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#memberModal" onclick="addMemberForm()">
        <i class="fas fa-plus"></i> Add Member
    </button>
</div>

<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <input type="text" id="memberSearch" class="form-control" placeholder="Search by membership no (digits only)">
            </div>
            <div class="col-md-4">
                <select id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="expired">Expired</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Members Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable" id="membersTable">
                <thead>
                    <tr>
                        <th>Membership No</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Plan</th>
                        <th>Trainer</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="membersTableBody">
                    <!-- Loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Member Modal -->
<div class="modal fade" id="memberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="memberModalTitle">Add Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="memberForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- When editing, memberId is filled; for create we use proposed id field below -->
                    <input type="hidden" id="memberId" name="id">
                    <input type="hidden" id="memberProposedId" name="proposed_id">
                    
                    <!-- Auto-generated Membership No Display -->
                    <div class="mb-3" id="membershipNoGroup" style="display:none;">
                        <label class="form-label">Membership No</label>
                        <input type="text" class="form-control" id="membershipNoDisplay" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label for="memberName" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="memberName" name="full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="memberPhone" class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="memberPhone" name="phone" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                             <div class="mb-3">
                                <label for="memberGender" class="form-label">Gender</label>
                                <select class="form-select" id="memberGender" name="gender">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                         <div class="col-md-6">
                            <div class="mb-3">
                                <label for="memberTrainer" class="form-label">Trainer</label>
                                <select class="form-select" id="memberTrainer" name="trainer_id">
                                    <option value="">No Trainer</option>';
                                    foreach ($trainers as $trainer) {
                                        $pageContent .= '<option value="' . $trainer['id'] . '">' . escapeHtml($trainer['name']) . '</option>';
                                    }
                                    $pageContent .= '
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="memberPicture" class="form-label">Profile Picture</label>
                        <input type="file" class="form-control" id="memberPicture" name="profile_picture" accept="image/*">
                        <small class="text-muted">Max size: 5MB (JPG, PNG, GIF)</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="memberPlan" class="form-label">Plan <span class="text-danger">*</span></label>
                                <select class="form-select" id="memberPlan" name="plan_id" required>
                                    <option value="">Select Plan</option>';

foreach ($plans as $plan) {
    $pageContent .= '<option value="' . $plan['id'] . '">' . escapeHtml($plan['name']) . ' - Rs ' . number_format($plan['price'], 0) . '</option>';
}

$pageContent .= '
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="memberStartDate" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="memberStartDate" name="start_date" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="memberSubmitBtn">Save Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const plansData = ' . json_encode($plans) . ';
const NEXT_MEMBER_ID = ' . intval($nextMemberId) . ';

function loadMembers() {
    const search = document.getElementById("memberSearch").value;
    const status = document.getElementById("statusFilter").value;
    
    $.ajax({
        url: "' . APP_URL . '/ajax/members_get.php",
        type: "POST",
        data: { search: search, status: status },
        dataType: "json",
        success: function(response) {
            let html = "";
            if (response.length === 0) {
                html = "<tr><td colspan=\"8\" class=\"text-center text-muted py-4\">No members found</td></tr>";
            } else {
                response.forEach(member => {
                    const statusBadge = member.status === "active" ? 
                        "<span class=\"badge badge-success\">Active</span>" : 
                        "<span class=\"badge badge-danger\">Expired</span>";
                    
                    // Format Membership No to be 6 digits (e.g., 000001)
                    const membershipNo = String(member.id).padStart(6, "0");

                    html += `
                        <tr>
                            <td><strong>${membershipNo}</strong></td>
                            <td>${escapeHtml(member.full_name)}</td>
                            <td>${escapeHtml(member.phone)}</td>
                            <td>${escapeHtml(member.plan_name)}</td>
                            <td>${escapeHtml(member.trainer_name || "-")}</td>
                            <td>${formatDate(member.end_date)}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-info" onclick="viewMemberProfile(${member.id})" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-secondary" onclick="printIdCard(${member.id})" title="Print ID Card">
                                        <i class="fas fa-id-card"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="editMember(${member.id})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteMember(${member.id})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                });
            }
            document.getElementById("membersTableBody").innerHTML = html;
        }
    });
}

function addMemberForm() {
    document.getElementById("memberForm").reset();
    // Prefill next membership id (proposed) and lock the display; leave memberId empty so form uses add endpoint
    document.getElementById("memberId").value = "";
    document.getElementById("memberProposedId").value = NEXT_MEMBER_ID;
    document.getElementById("membershipNoGroup").style.display = "block";
    document.getElementById("membershipNoDisplay").value = String(NEXT_MEMBER_ID).padStart(6, "0");
    document.getElementById("memberModalTitle").textContent = "Add Member";
    document.getElementById("memberSubmitBtn").textContent = "Save Member";
    document.getElementById("memberStartDate").valueAsDate = new Date();
}

function editMember(id) {
    $.ajax({
        url: "' . APP_URL . '/ajax/members_get_single.php",
        type: "POST",
        data: { id: id },
        dataType: "json",
        success: function(member) {
            document.getElementById("memberId").value = member.id;
            // Clear any proposed id when editing
            if (document.getElementById("memberProposedId")) {
                document.getElementById("memberProposedId").value = "";
            }
            
            // Show Membership No
            document.getElementById("membershipNoGroup").style.display = "block";
            document.getElementById("membershipNoDisplay").value = String(member.id).padStart(6, "0");

            document.getElementById("memberName").value = member.full_name;
            document.getElementById("memberPhone").value = member.phone;
            document.getElementById("memberGender").value = member.gender || "Male";
            document.getElementById("memberPlan").value = member.plan_id;
            document.getElementById("memberTrainer").value = member.trainer_id || ""; // Select trainer
            document.getElementById("memberStartDate").value = member.start_date;
            
            document.getElementById("memberModalTitle").textContent = "Edit Member";
            document.getElementById("memberSubmitBtn").textContent = "Update Member";
            
            const modal = new bootstrap.Modal(document.getElementById("memberModal"));
            modal.show();
        }
    });
}

function deleteMember(id) {
    APP.showConfirm("Delete Member", "Are you sure you want to delete this member?", function() {
        $.ajax({
            url: "' . APP_URL . '/ajax/members_delete.php",
            type: "POST",
            data: { id: id },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    APP.showSuccess(response.message);
                    loadMembers();
                } else {
                    APP.showError(response.message);
                }
            }
        });
    });
}

function viewMemberProfile(id) {
    window.location.href = "' . APP_URL . '/views/members/member_profile.php?id=" + id;
}

function printIdCard(id) {
    window.open("' . APP_URL . '/views/members/id_card.php?id=" + id, "_blank");
}

function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(date) {
    return new Date(date).toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" });
}

// Event listeners
document.addEventListener("DOMContentLoaded", function() {
    loadMembers();
    document.getElementById("memberSearch").addEventListener("keyup", loadMembers);
    document.getElementById("statusFilter").addEventListener("change", loadMembers);
});

// Form submission
document.getElementById("memberForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const id = document.getElementById("memberId").value;
    const url = id ? "' . APP_URL . '/ajax/members_update.php" : "' . APP_URL . '/ajax/members_add.php";
    
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
                bootstrap.Modal.getInstance(document.getElementById("memberModal")).hide();
                loadMembers();
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

