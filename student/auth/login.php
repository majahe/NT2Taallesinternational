<?php
session_start();
require_once __DIR__ . '/../../includes/db_connect.php';

$error = '';
$success = '';

// Check if already logged in
if (isset($_SESSION['student_id'])) {
    header("Location: /student/dashboard/dashboard.php");
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, password_set, course_access_granted FROM registrations WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $student = $result->fetch_assoc();
            
            // Check if password is set
            if (!$student['password_set'] || empty($student['password'])) {
                $error = 'Please set your password first. Check your email for setup instructions.';
            } elseif (!password_verify($password, $student['password'])) {
                $error = 'Invalid email or password';
            } elseif (!$student['course_access_granted']) {
                $error = 'Course access has not been granted yet. Please contact the administrator.';
            } else {
                // Successful login
                $_SESSION['student_id'] = $student['id'];
                $_SESSION['student_email'] = $student['email'];
                $_SESSION['student_name'] = $student['name'];
                
                // Redirect to dashboard or intended page
                $redirect = $_SESSION['redirect_after_login'] ?? '/student/dashboard/dashboard.php';
                unset($_SESSION['redirect_after_login']);
                header("Location: " . $redirect);
                exit;
            }
        } else {
            $error = 'Invalid email or password';
        }
        $stmt->close();
    }
}

// Check for success messages
if (isset($_GET['success'])) {
    $success = htmlspecialchars($_GET['success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - NT2 Taalles International</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/student_portal.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1>Student Login</h1>
            <p class="login-subtitle">NT2 Taalles International</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <div class="login-footer">
                <a href="../../index.php">← Back to Website</a>
            </div>
        </div>
    </div>
</body>
</html>

