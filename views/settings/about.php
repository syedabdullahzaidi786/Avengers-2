<?php
/**
 * About Software Page - Simple UI Edition
 */

require_once __DIR__ . '/../../config/database.php';

// Set page title
$pageTitle = 'About Software';

// Start building page content
$pageContent = '
<div class="page-header mb-4">
    <h1><i class="fas fa-info-circle"></i> About Software</h1>
    <p class="text-secondary">Basic information about the application and developers</p>
</div>

<div class="card bg-dark shadow-sm border-0">
    <div class="card-body p-4">
        <div class="row align-items-center mb-4">
            <div class="col-auto">
                <div class="bg-primary rounded-3 p-3 text-white">
                    <i class="fas fa-dumbbell fa-2x"></i>
                </div>
            </div>
            <div class="col">
                <h3 class="mb-0 fw-bold">' . APP_NAME . '</h3>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-borderless align-middle mb-0">
                <tbody>
                    <tr>
                        <td class="ps-0 py-3 text-secondary" style="width: 150px;">Software Version:</td>
                        <td class="py-3 fw-bold">v2.5.0 Premium</td>
                    </tr>
                    <tr>
                        <td class="ps-0 py-3 text-secondary" style="width: 150px;">Developed By:</td>
                        <td class="py-3 fw-bold">AR Cloud</td>
                    </tr>
                    <tr>
                        <td class="ps-0 py-3 text-secondary">Contact:</td>
                        <td class="py-3">
                            <a href="tel:+923313771572" class="text-white text-decoration-none">
                                <i class="fas fa-phone-alt me-2 text-primary"></i> +92 331 3771572
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td class="ps-0 py-3 text-secondary">Release Date:</td>
                        <td class="py-3">March 2, 2026</td>
                    </tr>
                    <tr>
                        <td class="ps-0 py-3 text-secondary">Platform:</td>
                        <td class="py-3">Web-based Management System</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <hr class="my-4 opacity-10">
        
        <div class="text-center text-secondary small">
            &copy; ' . date('Y') . ' AR Cloud. All rights reserved.
        </div>
    </div>
</div>
';

include __DIR__ . '/../../views/layout/header.php';
?>
