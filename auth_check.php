<?php
// Authentication check helper file
// Include this file at the beginning of any page that requires authentication

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header("Location: uiusupplementlogin.html");
    exit();
}
?>
