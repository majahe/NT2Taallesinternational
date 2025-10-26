<?php
session_start();
require_once __DIR__ . '/../../includes/db_connect.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header("Location: login.php?error=Invalid access token");
    exit;
}

// Verify token (you'll need to add token field to registrations table)
$stmt = $conn->prepare("SELECT id, email, name, password_set FROM registrations WHERE password_token = ? AND password_token_expires > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: login.php?error=Invalid or expired token");
    exit;
}

$student = $result->fetch_assoc();
$student_id = $student['id'];

// Handle password setup
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Set password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE registrations SET password = ?, password_set = TRUE, password_token = NULL WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $student_id);
        
        if ($stmt->execute()) {
            $success = 'Password set successfully! You can now login.';
            // Clear token
            $stmt->close();
            header("Location: login.php?success=" . urlencode($success));
            exit;
        } else {
            $error = 'Failed to set password. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Password - NT2 Taalles International</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/student_portal.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1>Set Your Password</h1>
            <p class="login-subtitle">Welcome, <?= htmlspecialchars($student['name']) ?></p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required minlength="8">
                    <small>Minimum 8 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Set Password</button>
            </form>
        </div>
    </div>
</body>
</html>

