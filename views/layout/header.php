<?php
/**
 * Main Layout Template
 * Used for all authenticated pages
 */

requireLogin();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <?php if (isset($additionalCSS)): ?>
        <?php echo $additionalCSS; ?>
    <?php
endif; ?>
</head>
<body>
    <div class="wrapper">
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navigation -->
            <nav class="top-navbar">
                <div class="container-fluid">
                    <div class="navbar-content">
                        <div class="navbar-left">
                            <a href="<?php echo APP_URL; ?>/index.php" class="navbar-logo">
                                <img src="<?php echo APP_URL; ?>/assets/images/logo.png" alt="Logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2964/2964514.png'">
                                <span><?php echo APP_NAME; ?></span>
                            </a>
                        </div>

                        <!-- Mobile Toggle -->
                        <button class="navbar-toggler d-lg-none" id="toggleNavbar">
                            <i class="fas fa-bars"></i>
                        </button>

                        <!-- Nav Links -->
                        <div class="navbar-center" id="navbarMenu">
                            <ul class="nav-links">
                                <li><a href="<?php echo APP_URL; ?>/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                                <li><a href="<?php echo APP_URL; ?>/views/members/members.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'members.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Members</a></li>
                                <li><a href="<?php echo APP_URL; ?>/views/payments/payments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>"><i class="fas fa-credit-card"></i> Payments</a></li>
                                <li><a href="<?php echo APP_URL; ?>/views/attendance/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'attendance') !== false ? 'active' : ''; ?>"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                                <li><a href="<?php echo APP_URL; ?>/views/trainers/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], 'trainers') !== false ? 'active' : ''; ?>"><i class="fas fa-dumbbell"></i> Trainers</a></li>
                                <li class="nav-item-dropdown">
                                    <a href="javascript:void(0)" class="dropdown-trigger" id="settingsDropdownTrigger">
                                        <i class="fas fa-cog"></i> Settings <i class="fas fa-chevron-down ms-1 dropdown-arrow"></i>
                                    </a>
                                    <div class="nav-dropdown-menu" id="settingsDropdownMenu">
                                        <a href="<?php echo APP_URL; ?>/views/members/renew.php" class="dropdown-item">
                                            <i class="fas fa-sync-alt"></i> Renew Membership
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/views/users/users.php" class="dropdown-item">
                                            <i class="fas fa-user-shield"></i> User Management
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/views/members/expired.php" class="dropdown-item">
                                            <i class="fas fa-user-times"></i> Expired Members
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a href="<?php echo APP_URL; ?>/views/plans/plans.php" class="dropdown-item">
                                            <i class="fas fa-folder"></i> Plans
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/views/expenses/index.php" class="dropdown-item">
                                            <i class="fas fa-file-invoice-dollar"></i> Expenses
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/views/reports/finance.php" class="dropdown-item">
                                            <i class="fas fa-chart-bar"></i> Reports
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="navbar-right d-none d-md-flex">
                            <div class="user-dropdown">
                                <div class="user-profile-trigger" id="userDropdownTrigger">
                                    <div class="user-info">
                                        <span class="user-name"><?php echo escapeHtml($user['full_name']); ?></span>
                                        <span class="user-role">Administrator</span>
                                    </div>
                                    <div class="user-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <i class="fas fa-chevron-down ms-2 dropdown-arrow"></i>
                                </div>
                                <div class="user-dropdown-menu" id="userDropdownMenu">
                                    <div class="dropdown-header">
                                        <strong>Account</strong>
                                    </div>
                                    <a href="<?php echo APP_URL; ?>/logout.php" class="dropdown-item logout-item">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- Flash Messages -->
            <?php
$successMsg = getFlashMessage('success');
$errorMsg = getFlashMessage('error');
$warningMsg = getFlashMessage('warning');
?>
            
            <div class="container-fluid">
                <?php if ($successMsg): ?>
                    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert" id="successAlert">
                        <i class="fas fa-check-circle"></i> <?php echo escapeHtml($successMsg); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php
endif; ?>
                
                <?php if ($errorMsg): ?>
                    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert" id="errorAlert">
                        <i class="fas fa-exclamation-circle"></i> <?php echo escapeHtml($errorMsg); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php
endif; ?>
                
                <?php if ($warningMsg): ?>
                    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert" id="warningAlert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo escapeHtml($warningMsg); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php
endif; ?>
                
                <!-- Page Content -->
                <div class="content-area">
                <?php if (isset($pageContent)): ?>
                    <?php echo $pageContent; ?>
                <?php
endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php echo $additionalJS; ?>
    <?php
endif; ?>
    
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>
