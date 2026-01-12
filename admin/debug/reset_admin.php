<?php
require __DIR__ . '/../../includes/config.php';
require __DIR__ . '/../../includes/db_connect.php';

$newPasswordPlain = 'Admin123!';
$newPasswordHash  = password_hash($newPasswordPlain, PASSWORD_DEFAULT);

// juiste username uit de database
$username = 'admin';

$stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $newPasswordHash, $username);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "✅ Admin wachtwoord gereset<br>";
    echo "Username: admin<br>";
    echo "Password: Admin123!";
} else {
    echo "⚠️ Geen admin geüpdatet (username niet gevonden)";
}

$stmt->close();
$conn->close();
