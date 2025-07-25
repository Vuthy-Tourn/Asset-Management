<?php
// logout.php
require_once __DIR__ . '/../components/config/db.php';
require_once __DIR__ . '/../components/flash.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set flash message BEFORE destroying session
flash('success', 'Logged out successfully!');

// Unset all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 12000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();
// Redirect to login page
header("Location: login.php");
exit(); // No output should come after this