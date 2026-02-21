<?php
/**
 * Trainer Commission Setup Page
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Trainer.php';

requireLogin();

$trainerModel = new Trainer($pdo);

// Fetch all fee types
$stmt = $pdo->query('SELECT * FROM fee_types ORDER BY name ASC');
$feeTypes = $stmt->fetchAll();

// Fetch all trainers
$trainers = $trainerModel->getAllTrainers();

// Page Title
$pageTitle = 'Commission Setup';

// Page Content
$pageContent = '
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-cog"></i> Commission Setup</h1>
        <p>Manage profit sharing rules and trainer rates</p>
    </div>
    <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Trainers
    </a>
</div>

<div class="row">
    <!-- Fee Types Commission Eligibility -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0"><i class="fas fa-list-check me-2 text-primary"></i> Commissionable Fee Types</h5>
                <small class="text-muted">Select which fees trigger a trainer commission.</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Fee Name</th>
                                <th class="text-center">Apply Commission</th>
                            </tr>
                        </thead>
                        <tbody>';


foreach ($feeTypes as $ft) {
    $isChecked = $ft['is_commissionable'] ? 'checked' : '';
    $pageContent .= '
                            <tr>
                                <td class="ps-4">
                                    <strong>' . escapeHtml($ft['name']) . '</strong>
                                    ' . (!$ft['is_active'] ? '<span class="badge bg-secondary ms-2 small">Inactive</span>' : '') . '
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                               onchange="toggleFeeCommission(' . $ft['id'] . ', this.checked)" ' . $isChecked . '>
                                    </div>
                                </td>
                            </tr>';
}

$pageContent .= '
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Trainer Commission Rates -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0"><i class="fas fa-percent me-2 text-success"></i> Trainer Commission Rates</h5>
                <small class="text-muted">Set the default percentage share for each trainer.</small>
            </div>
            <div class="card-body p-0">
                <form id="bulkRatesForm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Trainer Name</th>
                                    <th class="pe-4" style="width: 150px;">Rate (%)</th>
                                </tr>
                            </thead>
                            <tbody>';

foreach ($trainers as $t) {
    $pageContent .= '
                                <tr>
                                    <td class="ps-4">
                                        <strong>' . escapeHtml($t['name']) . '</strong><br>
                                        <small class="text-muted">' . escapeHtml($t['phone']) . '</small>
                                    </td>
                                    <td class="pe-4">
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control" name="rates[' . $t['id'] . ']" 
                                                   value="' . number_format($t['commission_rate'], 2, '.', '') . '" 
                                                   min="0" max="100" step="0.01">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </td>
                                </tr>';
}

if (empty($trainers)) {
    $pageContent .= '<tr><td colspan="2" class="text-center text-muted py-4">No trainers found</td></tr>';
}

$pageContent .= '
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-white text-end py-3">
                <button type="button" class="btn btn-success" onclick="saveBulkRates()">
                    <i class="fas fa-save me-1"></i> Save All Rates
                </button>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info border-0 shadow-sm">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Rule Summary:</strong> 
    When a payment is added for a <code>Commissionable Fee</code>, the system automatically gives <code>X%</code> of the amount to the assigned trainer (if any).
</div>

<script>
function toggleFeeCommission(id, isChecked) {
    const status = isChecked ? 1 : 0;
    
    $.ajax({
        url: "' . APP_URL . '/ajax/fee_types_toggle_commission.php",
        type: "POST",
        data: { id: id, is_commissionable: status },
        dataType: "json",
        success: function(response) {
            if (response.success) {
                // APP.showSuccess(response.message); // Not needed for toggle usually, silent is fine
            } else {
                APP.showError(response.message);
                // Revert UI if failed (reload)
                location.reload();
            }
        },
        error: function() {
            APP.showError("Network error occurred.");
            location.reload();
        }
    });
}

function saveBulkRates() {
    const formData = $("#bulkRatesForm").serialize();
    
    $.ajax({
        url: "' . APP_URL . '/ajax/trainers_update_bulk_commissions.php",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function(response) {
            if (response.success) {
                APP.showSuccess(response.message);
            } else {
                APP.showError(response.message);
            }
        },
        error: function() {
            APP.showError("Failed to communicate with server.");
        }
    });
}
</script>
';

include __DIR__ . '/../../views/layout/header.php';
?>
