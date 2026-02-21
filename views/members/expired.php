<?php
/**
 * Expired Members Page
 * Lists only members with expired status
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Member.php';

$memberModel = new Member($pdo);

// Set page title
$pageTitle = 'Expired Members';

// Start building page content
$pageContent = '
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-user-times"></i> Expired Members</h1>
        <p>List of members whose membership has expired</p>
    </div>
    <div>
        <button id="bulkWhatsAppBtn" class="btn btn-success" onclick="sendBulkWhatsApp()">
            <i class="fab fa-whatsapp"></i> Send Bulk Reminder
        </button>
    </div>
</div>

<!-- Search area -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-9">
                <input type="text" id="memberSearch" class="form-control" placeholder="Search by name, phone, or membership no...">
            </div>
            <div class="col-md-3 text-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAllMembers" onclick="toggleSelectAll(this)">
                    <label class="form-check-label" for="selectAllMembers">
                        Select All
                    </label>
                </div>
            </div>
        </div>
        <div class="mt-2">
            <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> <strong>Note:</strong> Bulk messaging opens multiple tabs. Please allow "Pop-ups" in your browser settings if they are blocked.</small>
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
                        <th width="40px"></th>
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

<!-- Bulk Sender Modal -->
<div class="modal fade" id="bulkSenderModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fab fa-whatsapp"></i> WhatsApp Sender Queue</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="stopQueue()"></button>
            </div>
            <div class="modal-body text-center">
                <div id="queueStatus">
                    <p class="mb-2">Processing <span id="currentProcessName" class="text-primary fw-bold">...</span></p>
                    <div class="progress mb-4" style="height: 10px; background: rgba(255,255,255,0.1);">
                        <div id="queueProgressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                    </div>
                    <p class="small text-muted mb-4">Click the button below to send the message for the current member. Once you send, come back here to process the next one.</p>
                </div>
                
                <div id="queueComplete" style="display: none;">
                    <div class="text-success mb-3"><i class="fas fa-check-circle fa-4x"></i></div>
                    <h4>All Messages Processed!</h4>
                    <p>Total messages sent: <span id="totalSentCount">0</span></p>
                    <button class="btn btn-primary mt-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
            <div class="modal-footer justify-content-center border-0" id="queueFooter">
                <button id="nextMessageBtn" class="btn btn-success btn-lg px-5" onclick="processNextInQueue()">
                    Send Message to <span id="nextMemberBtnName">...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let expiredMembersList = [];
let messageQueue = [];
let currentQueueIndex = 0;
let bulkModal;

function loadExpiredMembers() {
    const search = document.getElementById("memberSearch").value;
    
    $.ajax({
        url: "' . APP_URL . '/ajax/members_get.php",
        type: "POST",
        data: { 
            search: search, 
            status: "expired" 
        },
        dataType: "json",
        success: function(response) {
            expiredMembersList = response;
            let html = "";
            if (response.length === 0) {
                html = "<tr><td colspan=\"9\" class=\"text-center text-muted py-4\">No expired members found</td></tr>";
            } else {
                response.forEach(member => {
                    const membershipNo = String(member.id).padStart(6, "0");

                    html += `
                        <tr>
                            <td>
                                <input type="checkbox" class="member-checkbox form-check-input" value="${member.id}" 
                                    data-name="${escapeJs(member.full_name)}" data-phone="${member.phone}">
                            </td>
                            <td><strong>${membershipNo}</strong></td>
                            <td>${escapeHtml(member.full_name)}</td>
                            <td>${escapeHtml(member.phone)}</td>
                            <td>${escapeHtml(member.plan_name)}</td>
                            <td>${escapeHtml(member.trainer_name || "-")}</td>
                            <td>${formatDate(member.end_date)}</td>
                            <td><span class="badge badge-danger">Expired</span></td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-info" onclick="viewMemberProfile(${member.id})" title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="sendWhatsApp(\'${escapeJs(member.full_name)}\', \'${member.phone}\')" title="Send WhatsApp Reminder">
                                        <i class="fab fa-whatsapp"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="renewMember(${member.id})" title="Renew Membership">
                                        <i class="fas fa-sync"></i>
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

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll(".member-checkbox");
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

function sendBulkWhatsApp() {
    const selected = document.querySelectorAll(".member-checkbox:checked");
    if (selected.length === 0) {
        Swal.fire({ icon: "warning", title: "No Selection", text: "Please select at least one member." });
        return;
    }

    messageQueue = [];
    selected.forEach(cb => {
        messageQueue.push({
            name: cb.getAttribute("data-name"),
            phone: cb.getAttribute("data-phone")
        });
    });

    currentQueueIndex = 0;
    updateModalUI();
    
    if (!bulkModal) {
        bulkModal = new bootstrap.Modal(document.getElementById("bulkSenderModal"));
    }
    
    document.getElementById("queueStatus").style.display = "block";
    document.getElementById("queueComplete").style.display = "none";
    document.getElementById("queueFooter").style.display = "flex";
    
    bulkModal.show();
}

function updateModalUI() {
    const member = messageQueue[currentQueueIndex];
    if (member) {
        document.getElementById("currentProcessName").textContent = member.name;
        document.getElementById("nextMemberBtnName").textContent = member.name;
        
        const progress = (currentQueueIndex / messageQueue.length) * 100;
        document.getElementById("queueProgressBar").style.width = progress + "%";
    }
}

function processNextInQueue() {
    const member = messageQueue[currentQueueIndex];
    if (member) {
        sendWhatsApp(member.name, member.phone);
        
        currentQueueIndex++;
        
        if (currentQueueIndex < messageQueue.length) {
            updateModalUI();
        } else {
            // Queue Complete
            document.getElementById("queueProgressBar").style.width = "100%";
            document.getElementById("queueStatus").style.display = "none";
            document.getElementById("queueFooter").style.display = "none";
            document.getElementById("queueComplete").style.display = "block";
            document.getElementById("totalSentCount").textContent = messageQueue.length;
        }
    }
}

function stopQueue() {
    messageQueue = [];
    currentQueueIndex = 0;
}

function sendWhatsApp(name, phone) {
    let cleanPhone = phone.replace(/\\D/g, "");
    if (cleanPhone.startsWith("0")) {
        cleanPhone = "92" + cleanPhone.substring(1);
    } else if (!cleanPhone.startsWith("92") && cleanPhone.length === 10) {
        cleanPhone = "92" + cleanPhone;
    }

    const message = `Assalam-o-Alaikum *${name}*,%0A%0AUmid hai aap khairiyat say hon gay. Aap ki *Avengers Gym* ki membership *Expire* ho chuki hay. Meharbani ker kay Gym aker staff say contact karen aur apni membership renew kerwalen. Shukriya!`;
    const whatsappUrl = `https://wa.me/${cleanPhone}?text=${message}`;
    window.open(whatsappUrl, "_blank");
}

function escapeJs(text) {
    if (!text) return "";
    return text.replace(/\'/g, "\\\'").replace(/\"/g, \'\\\\"\');
}

function viewMemberProfile(id) {
    window.location.href = "' . APP_URL . '/views/members/member_profile.php?id=" + id;
}

function renewMember(id) {
    window.location.href = "' . APP_URL . '/views/members/members.php";
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

document.addEventListener("DOMContentLoaded", function() {
    loadExpiredMembers();
    document.getElementById("memberSearch").addEventListener("keyup", loadExpiredMembers);
});
</script>
';

// Output page with layout
include __DIR__ . '/../../views/layout/header.php';
?>
