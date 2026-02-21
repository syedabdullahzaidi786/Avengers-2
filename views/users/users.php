<?php
/**
 * User Management Page
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/User.php';

$pageTitle = 'User Management';
$pageContent = '
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-user-shield"></i> User Management</h1>
        <p>Manage system access and administrators</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="addUserForm()">
        <i class="fas fa-plus"></i> Add New User
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <!-- Loaded via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content glass-bg">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalTitle">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="userForm">
                <div class="modal-body">
                    <input type="hidden" id="userId" name="id">
                    
                    <div class="mb-3">
                        <label for="fullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="fullName" name="full_name" required placeholder="e.g. John Doe">
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" required placeholder="e.g. johndoe">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="e.g. john@example.com">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span id="pwdRequired" class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="Minimum 6 characters">
                        <small class="form-text text-muted" id="pwdHelp">Leave blank to keep existing password when editing.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="userSubmitBtn">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function loadUsers() {
    $.ajax({
        url: "' . APP_URL . '/ajax/users_get.php",
        type: "GET",
        dataType: "json",
        success: function(response) {
            let html = "";
            if (response.length === 0) {
                html = "<tr><td colspan=\"6\" class=\"text-center text-muted py-4\">No users found</td></tr>";
            } else {
                response.forEach(user => {
                    html += `
                        <tr>
                            <td>${user.id}</td>
                            <td>${escapeHtml(user.full_name)}</td>
                            <td>${escapeHtml(user.username)}</td>
                            <td>${escapeHtml(user.email)}</td>
                            <td>${new Date(user.created_at).toLocaleDateString()}</td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-warning" onclick=\'editUser(${JSON.stringify(user)})\' title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                });
            }
            document.getElementById("usersTableBody").innerHTML = html;
        }
    });
}

function addUserForm() {
    document.getElementById("userForm").reset();
    document.getElementById("userId").value = "";
    document.getElementById("userModalTitle").textContent = "Add New User";
    document.getElementById("userSubmitBtn").textContent = "Save User";
    document.getElementById("password").required = true;
    document.getElementById("pwdRequired").style.display = "inline";
    document.getElementById("pwdHelp").style.display = "none";
}

function editUser(user) {
    document.getElementById("userId").value = user.id;
    document.getElementById("fullName").value = user.full_name;
    document.getElementById("username").value = user.username;
    document.getElementById("email").value = user.email;
    document.getElementById("password").value = "";
    document.getElementById("password").required = false;
    document.getElementById("pwdRequired").style.display = "none";
    document.getElementById("pwdHelp").style.display = "block";
    
    document.getElementById("userModalTitle").textContent = "Edit User";
    document.getElementById("userSubmitBtn").textContent = "Update User";
    
    new bootstrap.Modal(document.getElementById("userModal")).show();
}

function deleteUser(id) {
    APP.showConfirm("Delete User", "Are you sure you want to delete this user? This action cannot be undone.", function() {
        $.ajax({
            url: "' . APP_URL . '/ajax/users_delete.php",
            type: "POST",
            data: { id: id },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    APP.showSuccess(response.message);
                    loadUsers();
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

document.addEventListener("DOMContentLoaded", loadUsers);

document.getElementById("userForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const id = document.getElementById("userId").value;
    const url = id ? "' . APP_URL . '/ajax/users_update.php" : "' . APP_URL . '/ajax/users_add.php";
    
    $.ajax({
        url: url,
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(response) {
            if (response.success) {
                APP.showSuccess(response.message);
                bootstrap.Modal.getInstance(document.getElementById("userModal")).hide();
                loadUsers();
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
