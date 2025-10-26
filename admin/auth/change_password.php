<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: index.php");
  exit;
}

include '../../includes/db_connect.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $current_password = $_POST['current_password'];
  $new_password = $_POST['new_password'];
  $confirm_password = $_POST['confirm_password'];
  
  // Validate inputs
  if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $error = "All fields are required.";
  } elseif ($new_password !== $confirm_password) {
    $error = "New passwords do not match.";
  } elseif (strlen($new_password) < 6) {
    $error = "New password must be at least 6 characters long.";
  } else {
    // Verify current password
    $username = $_SESSION['admin'];
    $sql = "SELECT * FROM admins WHERE username='$username' AND password=SHA2('$current_password', 256)";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
      // Update password
      $update_sql = "UPDATE admins SET password=SHA2('$new_password', 256) WHERE username='$username'";
      if ($conn->query($update_sql)) {
        $message = "Password changed successfully!";
      } else {
        $error = "Error updating password: " . $conn->error;
      }
    } else {
      $error = "Current password is incorrect.";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Change Password - Admin</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <style>
    .password-form {
      max-width: 500px;
      margin: 2rem auto;
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .form-group {
      margin-bottom: 1rem;
    }
    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #333;
    }
    .form-group input {
      width: 100%;
      padding: 0.8rem;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 1rem;
      box-sizing: border-box;
    }
    .form-group input:focus {
      outline: none;
      border-color: #6366f1;
      box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    }
    .btn-change {
      background: linear-gradient(135deg, #6366f1, #7c3aed);
      color: white;
      border: none;
      padding: 0.8rem 2rem;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: bold;
      cursor: pointer;
      width: 100%;
      margin-top: 1rem;
    }
    .btn-change:hover {
      opacity: 0.9;
    }
    .btn-back {
      background: #6b7280;
      color: white;
      border: none;
      padding: 0.6rem 1.5rem;
      border-radius: 6px;
      font-size: 0.9rem;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      margin-top: 1rem;
    }
    .btn-back:hover {
      background: #4b5563;
    }
    .message {
      padding: 1rem;
      border-radius: 6px;
      margin-bottom: 1rem;
    }
    .message.success {
      background: #d1fae5;
      color: #065f46;
      border: 1px solid #a7f3d0;
    }
    .message.error {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fca5a5;
    }
    .password-requirements {
      background: #f3f4f6;
      padding: 1rem;
      border-radius: 6px;
      margin-top: 1rem;
      font-size: 0.9rem;
      color: #6b7280;
    }
    .password-requirements ul {
      margin: 0.5rem 0 0 1rem;
    }
  </style>
</head>
<body class="dashboard-body">
  <header class="admin-header">
    <h1>🔐 Change Admin Password</h1>
    <div class="admin-controls">
      <span>Logged in as: <strong><?= $_SESSION['admin'] ?></strong></span>
      <a href="../dashboard/dashboard.php" class="btn small">← Dashboard</a>
      <a href="logout.php" class="btn danger small">Logout</a>
    </div>
  </header>

  <div class="password-form">
    <h2>Change Your Password</h2>
    
    <?php if($message): ?>
      <div class="message success"><?= $message ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
      <div class="message error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label for="current_password">Current Password</label>
        <input type="password" id="current_password" name="current_password" required>
      </div>

      <div class="form-group">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required>
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm New Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
      </div>

      <button type="submit" class="btn-change">Change Password</button>
    </form>

    <div class="password-requirements">
      <strong>Password Requirements:</strong>
      <ul>
        <li>At least 6 characters long</li>
        <li>Use a combination of letters, numbers, and symbols for better security</li>
        <li>Avoid common passwords like "password" or "123456"</li>
      </ul>
    </div>

      <a href="../dashboard/dashboard.php" class="btn-back">← Back to Dashboard</a>
  </div>

  <script>
    // Real-time password confirmation validation
    document.getElementById('confirm_password').addEventListener('input', function() {
      const newPassword = document.getElementById('new_password').value;
      const confirmPassword = this.value;
      
      if (confirmPassword && newPassword !== confirmPassword) {
        this.style.borderColor = '#ef4444';
        this.style.backgroundColor = '#fef2f2';
      } else {
        this.style.borderColor = '#d1d5db';
        this.style.backgroundColor = 'white';
      }
    });

    // Password strength indicator
    document.getElementById('new_password').addEventListener('input', function() {
      const password = this.value;
      const strength = getPasswordStrength(password);
      
      // You could add a visual strength indicator here
      if (password.length < 6) {
        this.style.borderColor = '#ef4444';
      } else if (password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password)) {
        this.style.borderColor = '#10b981';
      } else {
        this.style.borderColor = '#f59e0b';
      }
    });

    function getPasswordStrength(password) {
      let strength = 0;
      if (password.length >= 6) strength++;
      if (password.length >= 8) strength++;
      if (/[A-Z]/.test(password)) strength++;
      if (/[0-9]/.test(password)) strength++;
      if (/[^A-Za-z0-9]/.test(password)) strength++;
      return strength;
    }
  </script>
</body>
</html>
