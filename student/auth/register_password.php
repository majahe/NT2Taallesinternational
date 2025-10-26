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

// Verify token and get student info
// First check what columns exist
$check_password_set = $conn->query("SHOW COLUMNS FROM registrations LIKE 'password_set'");
$has_password_set = ($check_password_set->num_rows > 0);

$stmt = $conn->prepare("SELECT id, email, name, password_token, password_token_expires FROM registrations WHERE password_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: login.php?error=Invalid access token");
    exit;
}

$student = $result->fetch_assoc();
$student_id = $student['id'];

// Check if token is expired
if (isset($student['password_token_expires']) && $student['password_token_expires']) {
    if (strtotime($student['password_token_expires']) < time()) {
        header("Location: login.php?error=Token has expired");
        exit;
    }
}

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
        // Check if password column exists
        $check_password = $conn->query("SHOW COLUMNS FROM registrations LIKE 'password'");
        if ($check_password->num_rows == 0) {
            $conn->query("ALTER TABLE registrations ADD COLUMN password VARCHAR(255) NULL");
        }
        
        // Check if password_set column exists
        $check_password_set = $conn->query("SHOW COLUMNS FROM registrations LIKE 'password_set'");
        if ($check_password_set->num_rows == 0) {
            $conn->query("ALTER TABLE registrations ADD COLUMN password_set BOOLEAN DEFAULT FALSE");
        }
        
        // Set password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE registrations SET password = ?, password_set = TRUE, password_token = NULL, password_token_expires = NULL WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $student_id);
        
        if ($stmt->execute()) {
            $success = 'Password set successfully! You can now login.';
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

