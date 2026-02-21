<?php
/**
 * Generate ID Card Page
 * Search for a member by ID and generate their ID card
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Member.php';

$memberModel = new Member($pdo);
$member = null;
$error = null;

if (isset($_GET['member_id']) && !empty($_GET['member_id'])) {
    $member = $memberModel->getMemberById($_GET['member_id']);
    if (!$member) {
        $error = "Member with ID " . htmlspecialchars($_GET['member_id']) . " not found.";
    }
    else {
        // Redirect to the actual ID card view
        header('Location: id_card.php?id=' . $member['id']);
        exit;
    }
}

$pageTitle = 'Generate ID Card';

// Page content
$pageContent = '
<div class="page-header">
    <h1><i class="fas fa-id-card"></i> Generate ID Card</h1>
    <p>Enter Membership ID to generate and share ID card.</p>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">Search Member</h5>
            </div>
            <div class="card-body">
                ' . ($error ? '<div class="alert alert-danger">' . $error . '</div>' : '') . '
                
                <form action="" method="GET" class="search-form">
                    <div class="mb-4">
                        <label for="member_id" class="form-label">Membership ID</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="number" class="form-control border-start-0 ps-0" id="member_id" name="member_id" placeholder="e.g. 1" required autofocus>
                        </div>
                        <div class="form-text mt-2 text-muted">Enter the numeric ID assigned to the member.</div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-magic me-2"></i> Generate Card
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <a href="' . APP_URL . '/views/members/members.php" class="text-white text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Back to Members List
            </a>
        </div>
    </div>
</div>

<style>
.search-form .form-control:focus {
    box-shadow: none;
    border-color: var(--primary-color);
}
.search-form .input-group-text {
    border-color: var(--border-color);
}
.input-group-lg > .form-control, .input-group-lg > .input-group-text {
    padding: 0.75rem 1rem;
}
</style>
';

include __DIR__ . '/../layout/header.php';
?>
