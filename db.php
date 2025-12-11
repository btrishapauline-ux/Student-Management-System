<?php
// --- CONNECTION DETAILS ---
// WARNING: THESE CREDENTIALS ARE INSECURE FOR PRODUCTION
$host = 'localhost';
$user = 'root'; 
$pass = ''; // Should be a strong password for live servers
$dbname = 'sm_system'; // Using the 'sm_system' name from your SQL file

// 1. Create a new connection
$conn = new mysqli($host, $user, $pass, $dbname);

// 2. Check the connection for immediate failure
if ($conn->connect_error) {
    // Stop execution and display the error
    die("Connection failed: " . $conn->connect_error);
}

// 3. Set the character set to UTF-8 (best practice for all modern applications)
if (!$conn->set_charset("utf8mb4")) {
    // Handle error if setting character set fails (uncommon)
    // You could log this, but we'll die for simplicity here.
    die("Error loading character set utf8mb4: " . $conn->error);
}

// Note: The $conn object is now available to other files that 'require_once' this file.
?>