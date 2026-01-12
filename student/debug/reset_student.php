<?php
require __DIR__ . '/../../includes/config.php';
require __DIR__ . '/../../includes/db_connect.php';

// TEST student
$email = 'majahe62@gmail.com'; // <-- AANPASSEN
$newPasswordPlain = 'Student123!';
$newPasswordHash  = password_hash($newPasswordPlain, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "UPDATE registrations SET password = ? WHERE email = ?"
);
$stmt->bind_param("ss", $newPasswordHash, $email);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "✅ Student wachtwoord gereset<br>";
    echo "Email: {$email}<br>";
    echo "Password: {$newPasswordPlain}";
} else {
    echo "⚠️ Geen student gevonden met dit e-mailadres";
}

$stmt->close();
$conn->close();
