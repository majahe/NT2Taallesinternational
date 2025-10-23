<?php
// Quick password fix script
include '../includes/db_connect.php';

// Update the admin password to 'mjh123' using SHA2(256) to match the login script
$username = 'admin';
$password = 'mjh123';

$sql = "UPDATE admins SET password=SHA2('$password', 256) WHERE username='$username'";

if ($conn->query($sql)) {
    echo "Password updated successfully!<br>";
    echo "Username: admin<br>";
    echo "Password: mjh123<br>";
    echo "<a href='index.php'>Go to Login</a>";
} else {
    echo "Error updating password: " . $conn->error;
}
?>
