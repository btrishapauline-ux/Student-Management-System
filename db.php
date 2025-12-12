<?php
// --- CONNECTION DETAILS ---
// WARNING: THESE CREDENTIALS ARE INSECURE FOR PRODUCTION
$host = 'localhost';
$user = 'root'; 
$pass = ''; // Should be a strong password for live servers
$dbname = 'sm_system'; // Using the 'sm_system' name from your SQL file

// Surface mysqli errors so we can see real connection/SQL issues during signup/login.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // 1. Create a new connection
    $conn = new mysqli($host, $user, $pass, $dbname);

    // 2. Set the character set to UTF-8 (best practice for all modern applications)
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log('DB connection error: ' . $e->getMessage());
    die('Database connection failed. Please contact the administrator.');
}

// Note: The $conn object is now available to other files that 'require_once' this file.
?>