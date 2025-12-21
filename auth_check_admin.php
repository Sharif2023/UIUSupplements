<?php
// Admin authentication check helper file
// Include this file at the beginning of any page that requires admin privileges

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // Admin is not logged in, redirect to login page
    header("Location: uiusupplementlogin.html");
    exit();
}
?>
