<?php
// Simple logout script for both admin and student users
session_start();
session_unset();
session_destroy();

// Redirect to homepage or login
header("Location: index.php");
exit();

