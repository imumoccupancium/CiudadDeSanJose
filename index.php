<?php
/**
 * Ciudad De San Jose - Main Entry Point
 * Redirects to login page
 */

// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    // Redirect to appropriate dashboard based on role
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'security':
            header('Location: scanner/index.php');
            break;
        case 'homeowner':
            header('Location: portal/dashboard.php');
            break;
        default:
            header('Location: auth/login.php');
    }
} else {
    // Not logged in, redirect to login page
    header('Location: auth/login.php');
}

exit();
?>
