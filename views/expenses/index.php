<?php
/**
 * Expenses List Page
 */

require_once __DIR__ . '/../../config/database.php';

// Page Title
$pageTitle = 'Expenses';

// Page Content
$pageContent = '
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-file-invoice-dollar"></i> Expenses</h1>
        <p>Track gym expenditures</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#expenseModal" onclick="addExpenseForm()">
        <i class="fas fa-plus"></i> Add Expense
    </button>
</div>

<!-- Search & Filter -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-5">
                <input type="text" id="expenseSearch" class="form-control" placeholder="Search by title or description...">
            </div>
            <div class="col-md-3">
                <select id="categoryFilter" class="form-select">
                    <option value="">All Categories</option>
                    <option value="Rent">Rent</option>
                    <option value="Utilities">Utilities</option>
                    <option value="Salaries">Salaries</option>
                    <option value="Equipment">Equipment</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="month" id="monthFilter" class="form-control" value="' . date('Y-m') . '">
            </div>
        </div>
    </div>
</div>

<!-- Expenses Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable" id="expensesTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Amount (Rs)</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="expensesTableBody">
                    <!-- Loaded via AJAX -->
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td colspan="3"><strong id="totalExpenses">Rs 0.00</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Expense Modal -->
<div class="modal fade" id="expenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="expenseModalTitle">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="expenseForm">
                <div class="modal-body">
                    <input type="hidden" id="expenseId" name="id">
                    
                    <div class="mb-3">
                        <label for="expenseTitle" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="expenseTitle" name="title" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expenseAmount" class="form-label">Amount (Rs) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="expenseAmount" name="amount" min="1" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="expenseDate" class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="expenseDate" name="expense_date" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="expenseCategory" class="form-label">Category</label>
                        <select class="form-select" id="expenseCategory" name="category">
                            <option value="Rent">Rent</option>
                            <option value="Utilities">Utilities</option>
                            <option value="Salaries">Salaries</option>
                            <option value="Equipment">Equipment</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Other" selected>Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expenseDesc" class="form-label">Description</label>
                        <textarea class="form-control" id="expenseDesc" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="expenseSubmitBtn">Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Main Expense Logic
function loadExpenses() {
    const search = document.getElementById("expenseSearch").value;
    const category = document.getElementById("categoryFilter").value;
    const monthVal = document.getElementById("monthFilter").value; // YYYY-MM
    
    let month = "";
    let year = "";
    
    if (monthVal) {
        const parts = monthVal.split("-");
        year = parts[0];
        month = parts[1];
    }
    
    $.ajax({
        url: "' . APP_URL . '/ajax/expenses_get.php",
        type: "POST",
        data: { search: search, category: category, month: month, year: year },
        dataType: "json",
        success: function(response) {
            let html = "";
            let total = 0;
            
            if (response.length === 0) {
                html = "<tr><td colspan=\"6\" class=\"text-center text-muted py-4\">No expenses found</td></tr>";
            } else {
                response.forEach(expense => {
                    const amount = parseFloat(expense.amount);
                    total += amount;
                    
                    html += `
                        <tr>
                            <td>${formatDate(expense.expense_date)}</td>
                            <td><strong>${escapeHtml(expense.title)}</strong></td>
                            <td><span class="badge bg-secondary">${escapeHtml(expense.category)}</span></td>
                            <td>Rs ${amount.toFixed(2)}</td>
                            <td>${escapeHtml(expense.description || "")}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editExpense(${expense.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteExpense(${expense.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                });
            }
            document.getElementById("expensesTableBody").innerHTML = html;
            document.getElementById("totalExpenses").textContent = "Rs " + total.toFixed(2);
        }
    });
}

function addExpenseForm() {
    document.getElementById("expenseForm").reset();
    document.getElementById("expenseId").value = "";
    document.getElementById("expenseDate").valueAsDate = new Date();
    document.getElementById("expenseModalTitle").textContent = "Add Expense";
    document.getElementById("expenseSubmitBtn").textContent = "Save Expense";
}

function editExpense(id) {
    $.ajax({
        url: "' . APP_URL . '/ajax/expenses_get_single.php",
        type: "POST",
        data: { id: id },
        dataType: "json",
        success: function(expense) {
            document.getElementById("expenseId").value = expense.id;
            document.getElementById("expenseTitle").value = expense.title;
            document.getElementById("expenseAmount").value = expense.amount;
            document.getElementById("expenseDate").value = expense.expense_date;
            document.getElementById("expenseCategory").value = expense.category;
            document.getElementById("expenseDesc").value = expense.description;
            
            document.getElementById("expenseModalTitle").textContent = "Edit Expense";
            document.getElementById("expenseSubmitBtn").textContent = "Update Expense";
            
            const modal = new bootstrap.Modal(document.getElementById("expenseModal"));
            modal.show();
        }
    });
}

function deleteExpense(id) {
    APP.showConfirm("Delete Expense", "Are you sure? This action cannot be undone.", function() {
        $.ajax({
            url: "' . APP_URL . '/ajax/expenses_delete.php",
            type: "POST",
            data: { id: id },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    APP.showSuccess(response.message);
                    loadExpenses();
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

function formatDate(date) {
    return new Date(date).toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" });
}

// Event Listeners
document.addEventListener("DOMContentLoaded", function() {
    loadExpenses();
    document.getElementById("expenseSearch").addEventListener("keyup", loadExpenses);
    document.getElementById("categoryFilter").addEventListener("change", loadExpenses);
    document.getElementById("monthFilter").addEventListener("change", loadExpenses);
});

document.getElementById("expenseForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const id = document.getElementById("expenseId").value;
    const url = id ? "' . APP_URL . '/ajax/expenses_update.php" : "' . APP_URL . '/ajax/expenses_add.php";
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
                bootstrap.Modal.getInstance(document.getElementById("expenseModal")).hide();
                loadExpenses();
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
