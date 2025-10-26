<?php
session_start();
include '../../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $sql = "SELECT * FROM admins WHERE username='$username' AND password=SHA2('$password', 256)";
  $result = $conn->query($sql);

  if ($result->num_rows == 1) {
    $_SESSION['admin'] = $username;
    header("Location: ../dashboard/dashboard.php");
    exit;
  } else {
    $error = "Invalid username or password.";
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
    
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST" class="login-form">
      <label>Username</label>
      <input type="text" name="username" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <button type="submit" class="login-btn">Login</button>
    </form>

    <a href="../../index.php" class="back-link">← Back to website</a>
  </div>
</body>
</html>
