<?php
/**
 * UIU Supplements - Configuration File
 * 
 * This file contains all database and application configuration settings.
 * UPDATE THESE VALUES BASED ON YOUR HOSTING ENVIRONMENT.
 * 
 * For local development with XAMPP:
 *   - Uncomment the DEVELOPMENT section below
 *   - Comment out the PRODUCTION section
 */

// ============================================
// PRODUCTION SETTINGS (yzz.me Free Hosting)
// ============================================
define('DB_HOST', 'sql105.yzz.me');
define('DB_USERNAME', 'yzzme_40788122');
define('DB_PASSWORD', 'Sharif2025');
define('DB_NAME', 'yzzme_40788122_uiusupplements');

// Base URL for the application
define('BASE_URL', 'http://uiusupplements.yzz.me');

// ============================================
// DEVELOPMENT SETTINGS (XAMPP/localhost)
// Uncomment below for local development
// ============================================
// define('DB_HOST', 'localhost');
// define('DB_USERNAME', 'root');
// define('DB_PASSWORD', '');
// define('DB_NAME', 'uiusupplements');
// define('BASE_URL', 'http://localhost/UIU_Supplements_Live');

// ============================================
// ERROR REPORTING
// ============================================
// For production, hide errors from users
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// For development, show errors
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);

// ============================================
// DATABASE CONNECTION FUNCTION
// ============================================

/**
 * Get database connection
 * 
 * @return mysqli Database connection object
 */
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        // Log the error but don't expose details to users
        error_log("Database connection failed: " . $conn->connect_error);
        die("Connection failed. Please try again later.");
    }
    
    // Set charset to UTF-8 for proper character encoding
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Get database connection credentials as an array
 * Useful for files that need individual variables
 * 
 * @return array Database credentials
 */
function getDbCredentials() {
    return [
        'host' => DB_HOST,
        'username' => DB_USERNAME,
        'password' => DB_PASSWORD,
        'dbname' => DB_NAME
    ];
}
?>
