<?php
/**
 * Database Configuration
 * 
 * This file contains all database connection settings
 * Using PDO for secure prepared statements
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'avengers');

// Set timezone for the application
date_default_timezone_set('Asia/Karachi');

// Application constants
define('APP_NAME', 'Avengers Gym & Fitness');
define('APP_URL', 'http://localhost/Avengers');
define('APP_DEBUG', true);
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds

// Initialize PDO connection
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
        );
}
catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Start session with security settings
if (session_status() === PHP_SESSION_NONE) {
    // Session security - SET BEFORE SESSION START
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);

    session_start();

    // Check session timeout
    if (isset($_SESSION['last_activity'])) {
        if ((time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
            session_destroy();
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Helper function to check if user is authenticated
 */
function isAuthenticated()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Helper function to get current user
 */
function getCurrentUser()
{
    return $_SESSION['user'] ?? null;
}

/**
 * Helper function to redirect if not authenticated
 */
function requireLogin()
{
    if (!isAuthenticated()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

/**
 * Helper function for secure output escaping
 */
function escapeHtml($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Helper function to set flash message
 */
function setFlashMessage($type, $message)
{
    $_SESSION['flash_' . $type] = $message;
}

/**
 * Helper function to get flash message
 */
function getFlashMessage($type)
{
    $message = $_SESSION['flash_' . $type] ?? null;
    unset($_SESSION['flash_' . $type]);
    return $message;
}
?>
