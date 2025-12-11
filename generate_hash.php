<?php
// FILE: generate_hash.php (TEMPORARY)

$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "The password to use in the SQL UPDATE command is: <br>";
echo "<strong>" . $hashed_password . "</strong>";
?>