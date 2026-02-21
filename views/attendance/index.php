<?php
/**
 * Attendance Page
 * Scan Membership No (Check-in)
 */

require_once __DIR__ . '/../../config/database.php';

// Page Title
$pageTitle = 'Attendance';

// Page Content
$pageContent = '
<div class="row justify-content-center">
    <!-- Scan Check-in Input -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-lg border-0 h-100">
            <div class="card-header bg-primary text-white text-center py-4">
                <h2 class="mb-0"><i class="fas fa-qrcode"></i> Check-In</h2>
                <p class="mb-0 opacity-75">Scan QR Code or Enter Membership No</p>
            </div>
            <div class="card-body p-4">
                <!-- Camera Toggle -->
                <div class="text-center mb-3">
                    <button id="toggleCamera" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-camera"></i> Use Camera Scanner
                    </button>
                </div>
                
                <!-- Camera Container -->
                <div id="reader" class="mb-4 rounded overflow-hidden shadow-sm" style="display:none; background: #f8f9fa;"></div>
                
                <form id="attendanceForm">
                    <div class="mb-4">
                        <input type="text" id="membershipNo" class="form-control form-control-lg text-center fs-3" 
                                placeholder="Tap here & scan..." autofocus autocomplete="off">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">Mark Attendance</button>
                    </div>
                </form>
                
                <div id="checkInResult" class="mt-4 text-center" style="display:none;">
                    <!-- Result Message -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Today Attendance Log -->
    <div class="col-md-6 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Today\'s Attendance</h5>
                <button class="btn btn-sm btn-outline-primary" onclick="loadAttendanceLog()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Time</th>
                                <th>Membership No</th>
                                <th>Name</th>
                                <th class="text-center pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceLogBody" class="small">
                            <!-- Logs -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>

<script>
// QR Scanner Logic
let html5QrCode;
const toggleBtn = document.getElementById("toggleCamera");
const readerDiv = document.getElementById("reader");

toggleBtn.addEventListener("click", function() {
    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode("reader");
        readerDiv.style.display = "block";
        toggleBtn.innerHTML = "<i class=\'fas fa-stop\'></i> Stop Scanner";
        toggleBtn.classList.replace("btn-outline-primary", "btn-danger");
        
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        
        html5QrCode.start(
            { facingMode: "environment" }, 
            config,
            (decodedText) => {
                // Success: Populate input and submit
                const input = document.getElementById("membershipNo");
                input.value = decodedText;
                
                // Trigger form submission
                document.getElementById("attendanceForm").requestSubmit();
                
                // Optional: visual feedback on scan
                readerDiv.style.border = "5px solid #28a745";
                setTimeout(() => { readerDiv.style.border = "none"; }, 500);
            },
            (errorMessage) => {
                // Ignore errors for now
            }
        ).catch(err => {
            console.error("Scanner error:", err);
            APP.showError("Camera access denied or error occurred.");
            stopScanner();
        });
    } else {
        stopScanner();
    }
});

function stopScanner() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            html5QrCode = null;
            readerDiv.style.display = "none";
            toggleBtn.innerHTML = "<i class=\'fas fa-camera\'></i> Use Camera Scanner";
            toggleBtn.classList.replace("btn-danger", "btn-outline-primary");
        }).catch(err => console.error("Stop error:", err));
    }
}

document.addEventListener("DOMContentLoaded", function() {
    loadAttendanceLog();
    // Keep focus on input for continuous scanning
    document.getElementById("membershipNo").focus();
    
    // Refocus if clicked away (optional, for kiosk mode)
    // document.addEventListener("click", function() { document.getElementById("membershipNo").focus(); });
});

document.getElementById("attendanceForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const input = document.getElementById("membershipNo");
    const memberId = input.value.trim();
    
    if (!memberId) return;
    
    // Parse ID if it has leading zeros (Membership No 000001 -> 1)
    const parsedId = parseInt(memberId, 10);
    
    $.ajax({
        url: "' . APP_URL . '/ajax/attendance_mark.php",
        type: "POST",
        data: { member_id: parsedId },
        dataType: "json",
        success: function(response) {
            const resultDiv = document.getElementById("checkInResult");
            resultDiv.style.display = "block";
            
            if (response.success) {
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h4 class="alert-heading"><i class="fas fa-check-circle"></i> Success!</h4>
                        <p class="mb-0">${response.message}</p>
                    </div>`;
                // Play success sound (optional)
                // new Audio("/assets/sounds/success.mp3").play();
                
                loadAttendanceLog();
            } else {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h4 class="alert-heading"><i class="fas fa-times-circle"></i> Failed!</h4>
                        <p class="mb-0">${response.message}</p>
                    </div>`;
                // Play error sound (optional)
            }
            
            // Clear input and refocus
            input.value = "";
            input.focus();
            
            // Hide alert after 3 seconds
            setTimeout(() => {
                // resultDiv.style.display = "none"; 
                // Do not fully hide, just fade? Or keep last status until next scan.
            }, 5000);
        },
        error: function() {
            // Network error
             const resultDiv = document.getElementById("checkInResult");
             resultDiv.style.display = "block";
             resultDiv.innerHTML = `<div class="alert alert-warning">Connection Error. Try again.</div>`;
        }
    });
});

function loadAttendanceLog() {
    $.ajax({
        url: "' . APP_URL . '/ajax/attendance_get_daily.php",
        dataType: "json",
        success: function(response) {
            let html = "";
            if (response.length === 0) {
                html = "<tr><td colspan=\"4\" class=\"text-center text-muted py-3\">No attendance marked today</td></tr>";
            } else {
                response.forEach(log => {
                    const time = new Date(log.check_in_time).toLocaleTimeString("en-US", {hour: "2-digit", minute:"2-digit"});
                    const membershipNo = String(log.member_id).padStart(6, "0");
                    
                    html += `
                        <tr>
                            <td><strong>${time}</strong></td>
                            <td><span class="badge bg-secondary">${membershipNo}</span></td>
                            <td>${escapeHtml(log.full_name)}</td>
                            <td><span class="badge bg-success">Present</span></td>
                        </tr>`;
                });
            }
            document.getElementById("attendanceLogBody").innerHTML = html;
        }
    });
}

function escapeHtml(text) {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}
</script>
';

include __DIR__ . '/../../views/layout/header.php';
?>
