<?php
// Always start the session first
session_start();
require_once('db.php'); // Your database connection file

// --- 1. SESSION CHECK & REDIRECTION ---
// Check if the user ID is even set in the session.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    // If not logged in, redirect immediately and stop execution.
    header("location:login.php");
    exit(); 
}

// Retrieve the session variables
$user_id_check = $_SESSION['user_id'];
$user_type = $_SESSION['user_type']; // Assuming you set a user_type upon login (e.g., 'admin' or 'student')

// --- 2. DYNAMIC TABLE SELECTION ---
// This section determines which table to query based on the user type.
$user_table = '';
$login_table = '';

if ($user_type === 'admin') {
    $user_table = 'admin';
    $login_id_field = 'admin_id';
    $name_field = 'name';
} elseif ($user_type === 'student') {
    $user_table = 'students';
    $login_id_field = 'student_id';
    $name_field = 'firstname'; // Assuming you want to use the student's first name
} else {
    // If user_type is invalid, destroy session and redirect
    session_unset();
    session_destroy();
    header("location:login.php");
    exit();
}

// --- 3. DATABASE QUERY (SECURELY) ---
// Use prepared statements to prevent SQL Injection.
// The query selects the ID and the name field from the appropriate table.
$sql = "SELECT {$login_id_field}, {$name_field} FROM `{$user_table}` WHERE `{$login_id_field}` = ?";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Check if the statement was prepared successfully
if ($stmt === false) {
    // Handle error (e.g., log the error and redirect)
    // For now, let's just terminate:
    die('Database query failed: ' . htmlspecialchars($conn->error));
}

// Bind the parameter (i = integer, assuming your ID fields are INTs)
$stmt->bind_param("i", $user_id_check);

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// --- 4. DATA VALIDATION AND VARIABLE SETTING ---
$row = $result->fetch_assoc();

if ($row) {
    // User exists in the database
    $login_session = ucfirst($row[$name_field]); // e.g., 'John' or 'Administrator Name'
    $user_id = $row[$login_id_field];
    
    // Optional: Store the entire user row in a session for easy access
    // $_SESSION['user_data'] = $row; 
} else {
    // The user ID in the session does not match an actual user in the database.
    // This could happen if the user was deleted while they were logged in.
    
    // Force log out and redirect to login
    session_unset();
    session_destroy();
    header("location:login.php");
    exit();
}

// Close the statement
$stmt->close();

// $conn remains open if needed elsewhere, otherwise, close it here: $conn->close();

// At this point, the variables $login_session, $user_id, and $user_type are set and secure.
?>