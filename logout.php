<?php
/**
 * Logout Page
 * Destroy session and redirect to login
 */

require_once __DIR__ . '/config/database.php';

// Destroy session
session_destroy();

// Redirect to login
header('Location: ' . APP_URL . '/login.php');
exit;
?>
