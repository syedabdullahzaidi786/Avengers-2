<?php
/**
 * System Information & Health Check Page
 * This page helps verify the installation is correct
 */

require_once __DIR__ . '/config/database.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>System Information - Gym Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin-top: 20px; }
        .card { margin-bottom: 20px; }
        .status-ok { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-info-circle"></i> System Information</h1>
        <p class="text-muted">Verify your Gym Management System installation</p>
        
        <div class="card">
            <div class="card-header">
                <h5>PHP Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>PHP Version:</strong></td>
                        <td><?php echo phpversion(); ?></td>
                    </tr>
                    <tr>
                        <td><strong>PHP Extensions:</strong></td>
                        <td>
                            <?php
                            $extensions = ['pdo', 'pdo_mysql', 'json', 'filter'];
                            foreach ($extensions as $ext) {
                                $status = extension_loaded($ext) ? '<span class="status-ok">✓</span>' : '<span class="status-error">✗</span>';
                                echo "$ext: $status<br>";
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Memory Limit:</strong></td>
                        <td><?php echo ini_get('memory_limit'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Max Upload Size:</strong></td>
                        <td><?php echo ini_get('upload_max_filesize'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Database Connection</h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    // Test connection
                    $result = $pdo->query("SELECT 1");
                    echo '<div class="alert alert-success"><span class="status-ok">✓</span> <strong>Connected successfully!</strong></div>';
                    
                    // Check tables
                    $tables = ['users', 'membership_plans', 'members', 'payments'];
                    echo '<h6>Database Tables:</h6><ul>';
                    foreach ($tables as $table) {
                        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                        $exists = $stmt->rowCount() > 0;
                        $status = $exists ? '<span class="status-ok">✓</span>' : '<span class="status-error">✗</span>';
                        echo "<li>$status $table</li>";
                    }
                    echo '</ul>';
                    
                    // Count data
                    echo '<h6>Data Count:</h6><ul>';
                    $counts = [
                        'users' => 'Users',
                        'members' => 'Members',
                        'membership_plans' => 'Plans',
                        'payments' => 'Payments'
                    ];
                    foreach ($counts as $table => $label) {
                        $result = $pdo->query("SELECT COUNT(*) as cnt FROM $table");
                        $row = $result->fetch();
                        echo "<li>$label: {$row['cnt']} records</li>";
                    }
                    echo '</ul>';
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger"><span class="status-error">✗</span> <strong>Connection Failed!</strong><br>' . $e->getMessage() . '</div>';
                }
                ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>File Permissions</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li><?php echo is_writable(__DIR__) ? '<span class="status-ok">✓</span>' : '<span class="status-error">✗</span>'; ?> Root directory writable</li>
                    <li><?php echo is_dir(__DIR__ . '/ajax') ? '<span class="status-ok">✓</span>' : '<span class="status-error">✗</span>'; ?> AJAX folder exists</li>
                    <li><?php echo is_dir(__DIR__ . '/assets') ? '<span class="status-ok">✓</span>' : '<span class="status-error">✗</span>'; ?> Assets folder exists</li>
                    <li><?php echo is_dir(__DIR__ . '/views') ? '<span class="status-ok">✓</span>' : '<span class="status-error">✗</span>'; ?> Views folder exists</li>
                </ul>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Application Status</h5>
            </div>
            <div class="card-body">
                <?php
                $allOk = true;
                
                if (!$pdo) {
                    echo '<div class="alert alert-danger">Database not connected</div>';
                    $allOk = false;
                }
                
                if (!extension_loaded('pdo')) {
                    echo '<div class="alert alert-danger">PDO extension not loaded</div>';
                    $allOk = false;
                }
                
                if ($allOk) {
                    echo '<div class="alert alert-success"><span class="status-ok">✓</span> <strong>All systems operational!</strong></div>';
                    echo '<p><a href="http://localhost/Gym%20System/" class="btn btn-primary">Go to Dashboard</a></p>';
                } else {
                    echo '<div class="alert alert-danger">Some systems have issues. Please fix before proceeding.</div>';
                }
                ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Quick Links</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li><a href="login.php">Login Page</a></li>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="README.md">Documentation</a></li>
                    <li><a href="QUICKSTART.md">Quick Start Guide</a></li>
                    <li><a href="http://localhost/phpmyadmin">phpMyAdmin</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
