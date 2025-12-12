<?php
require_once('db.php');

// Simple direct insert test
$test_sql = "INSERT INTO students (firstname, lastname, course, year_level, email) 
             VALUES ('Test', 'User', 'BS Information Technology', '1st Year', 'test@test.com')";

if ($conn->query($test_sql)) {
    echo "SUCCESS: Test record inserted. ID: " . $conn->insert_id;
} else {
    echo "ERROR: " . $conn->error;
}
?>