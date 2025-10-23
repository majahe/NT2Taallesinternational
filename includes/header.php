<?php
  $isInPages = strpos($_SERVER['SCRIPT_NAME'], '/pages/') !== false || strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
  $basePath = $isInPages ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NT2 Taallessen International</title>
  <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
</head>
<body>
  <header>
    <nav class="navbar">
      <div class="logo-container">
        <span class="logo-text">NT2 Taallessen International</span>
      </div>
      <div class="nav-right">
        <a href="<?php echo $basePath; ?>index.php" class="nav-btn">Home</a>
        <a href="<?php echo $basePath; ?>pages/about.php" class="nav-btn">About</a>
        <a href="<?php echo $basePath; ?>pages/contact.php" class="nav-btn">Contact</a>
        <div class="dropdown">
          <a href="#" class="nav-btn dropdown-toggle">Courses</a>
          <div class="dropdown-content">
            <a href="<?php echo $basePath; ?>pages/cursus-russisch-nederlands.php">Russian to Dutch</a>
            <a href="<?php echo $basePath; ?>pages/cursus-engels-nederlands.php">English to Dutch</a>
          </div>
        </div>
        <a href="<?php echo $basePath; ?>pages/register.php" class="nav-btn register-btn">Register</a>
      </div>
    </nav>
  </header>
  <main>
