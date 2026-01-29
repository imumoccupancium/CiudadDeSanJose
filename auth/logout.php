<?php
// Start session
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Optionally remove remember me cookie
// Uncomment if you want to clear remember me on logout
// setcookie('remembered_user', '', time() - 3600, '/');

// Redirect to login page with logout message
header('Location: login.php?logout=success');
exit();
?>
