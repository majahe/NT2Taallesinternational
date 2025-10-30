<?php
session_start();
require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/database/QueryBuilder.php';
require_once __DIR__ . '/../../includes/csrf.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    CSRF::requireToken();
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $db = new QueryBuilder($conn);
        
        // Use prepared statement to prevent SQL injection
        $admin = $db->select('admins', '*', ['username' => $username]);
        
        if ($admin && $admin->num_rows === 1) {
            $adminData = $admin->fetch_assoc();
            
            // Check password (support both SHA2 and password_hash)
            $passwordValid = false;
            
            if (isset($adminData['password'])) {
                // Check if password is hashed with password_hash (recommended)
                if (password_verify($password, $adminData['password'])) {
                    $passwordValid = true;
                } 
                // Fallback to SHA2 for existing passwords
                elseif (hash_equals($adminData['password'], hash('sha256', $password))) {
                    $passwordValid = true;
                    // Upgrade to password_hash on next login
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $db->update('admins', ['password' => $newHash], ['id' => $adminData['id']]);
                }
            }
            
            if ($passwordValid) {
                $_SESSION['admin'] = $username;
                $_SESSION['admin_id'] = $adminData['id'];
                $_SESSION['admin_last_activity'] = time();
                
                $redirect = $_SESSION['redirect_after_login'] ?? '../dashboard/dashboard.php';
                unset($_SESSION['redirect_after_login']);
                header("Location: " . $redirect);
                exit;
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="login-body">
  <div class="login-card">
    <div class="login-logo">
      <img src="../../assets/img/LOGO.png" alt="NT2 Taalles International" class="logo-image">
    </div>
    <h2 class="login-title">Admin Login</h2>
    
    <?php if($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" class="login-form">
      <?= CSRF::getTokenField() ?>
      
      <label>Username</label>
      <input type="text" name="username" required autofocus>

      <label>Password</label>
      <input type="password" name="password" required>

      <button type="submit" class="login-btn">Login</button>
    </form>

    <a href="../../index.php" class="back-link">← Back to website</a>
  </div>
</body>
</html>
