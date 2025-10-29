# 🔐 Security Recommendations

## Critical Issues Found & How to Fix

---

## 🔴 CRITICAL - Fix Immediately

### 1. Debug Folder Security Risk

**File**: `admin/debug/fix_password.php`

**Problem**:
```php
// ANYONE WITH THE URL CAN RESET ADMIN PASSWORD!
$sql = "UPDATE admins SET password=SHA2('mjh123', 256) WHERE username='admin'";
```

**Why It's Dangerous**:
- No authentication required to access
- Password is hardcoded to 'mjh123'
- Anyone who knows the URL can compromise admin account
- URL is easy to guess: `yoursite.com/admin/debug/fix_password.php`

**Solution**:
```bash
# DELETE THE ENTIRE admin/debug/ FOLDER
rm -r admin/debug/
```

**Verification**:
- ✅ Folder no longer accessible
- ✅ No debug tools exposed
- ✅ No temporary passwords embedded

---

### 2. Exposed Credentials in Source Code

**File**: `includes/config.php`

**Problem**:
```php
// PASSWORDS VISIBLE IN SOURCE CODE!
define('DB_PASS', 'STRSQL!@Maarten62#$');
define('SMTP_PASSWORD', 'wybs joes ngev yxbw');
define('ADMIN_EMAIL', 'majahe62@gmail.com');
```

**Why It's Dangerous**:
- Credentials are checked into Git (visible in history)
- Anyone with repository access can see passwords
- If repo is public, passwords are globally visible
- Attackers can directly access your database
- Anyone can send emails from your account

**Solution A - Using .env File (Recommended)**:

1. Create `.env` file in project root:
```bash
DB_HOST=localhost
DB_USER=root
DB_PASS=STRSQL!@Maarten62#$
DB_NAME=nt2_db

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=majahe62@gmail.com
SMTP_PASSWORD=wybs joes ngev yxbw
SMTP_FROM_EMAIL=majahe62@gmail.com
SMTP_FROM_NAME=NT2 Taalles International

ADMIN_EMAIL=majahe62@gmail.com
WEBSITE_URL=https://nt2taallesinternational.com

SMTP_SSL_VERIFY=false
SMTP_DEBUG=false
```

2. Add to `.gitignore`:
```
.env
.env.local
*.env
```

3. Update `includes/config.php`:
```php
<?php
// Load environment variables
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Get values from .env or use defaults
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'nt2_db');

define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? '');
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? '');
define('SMTP_FROM_EMAIL', $_ENV['SMTP_FROM_EMAIL'] ?? '');
define('SMTP_FROM_NAME', $_ENV['SMTP_FROM_NAME'] ?? 'NT2 Taalles International');

define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL'] ?? '');
define('WEBSITE_URL', $_ENV['WEBSITE_URL'] ?? 'https://nt2taallesinternational.com');

define('SMTP_SSL_VERIFY', $_ENV['SMTP_SSL_VERIFY'] === 'true' ? true : false);
define('SMTP_DEBUG', $_ENV['SMTP_DEBUG'] === 'true' ? true : false);
?>
```

4. Deploy `.env` separately (via SFTP/admin panel, never via Git)

**Verification**:
- ✅ No passwords in Git history
- ✅ `.env` is in `.gitignore`
- ✅ Local `.env` file is not committed
- ✅ Production uses server-specific `.env`

---

### 3. SQL Injection Vulnerability

**File**: `admin/dashboard/dashboard.php`

**Problem**:
```php
// VULNERABLE TO SQL INJECTION!
if (isset($_POST['update_status'])) {
  $id = intval($_POST['id']);
  $status = $_POST['status'];  // No sanitization!
  $conn->query("UPDATE registrations SET status='$status' WHERE id=$id");
  // ...
}
```

**Why It's Dangerous**:
```
Attacker can send: status = "', password='hacked' WHERE username='admin' #"
Resulting query becomes: UPDATE registrations SET status='', password='hacked' WHERE username='admin' #' WHERE id=1
This would update the admin password!
```

**Solution - Use Prepared Statements**:

```php
<?php
// SECURE - Use prepared statements
if (isset($_POST['update_status'])) {
  $id = intval($_POST['id']);
  $status = $_POST['status'];
  
  // Use prepared statement
  $stmt = $conn->prepare("UPDATE registrations SET status = ? WHERE id = ?");
  $stmt->bind_param("si", $status, $id);
  
  if ($stmt->execute()) {
    echo "OK";
  } else {
    echo "ERROR: " . $stmt->error;
  }
  $stmt->close();
  exit;
}
?>
```

**Verification**:
- ✅ All database queries use prepared statements
- ✅ No string concatenation in SQL
- ✅ No direct $_POST variables in queries

---

## 🟠 HIGH PRIORITY - Fix This Week

### 4. Input Validation Issues

**Problem**: Some forms accept input without validation

**Example - Contact Form**:
```php
// admin/dashboard/dashboard.php
$email = $_POST['email'] ?? '';  // No validation!
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email");
}
```

**Solution - Validate All Inputs**:

```php
<?php
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_phone($phone) {
    return preg_match('/^[0-9\s\-\+\(\)]+$/', $phone);
}

function validate_name($name) {
    return strlen(trim($name)) >= 2 && strlen(trim($name)) <= 255;
}

// Usage in registration handler
if (empty($name) || !validate_name($name)) {
    die("Invalid name");
}

if (empty($email) || !validate_email($email)) {
    die("Invalid email");
}
?>
```

---

### 5. Output Escaping (XSS Prevention)

**Problem**: User input displayed without escaping

**Example - Registrations Dashboard**:
```php
// VULNERABLE - User input shown directly
echo $row['message'];  // Could contain JavaScript!
```

**Solution - Always Escape Output**:

```php
<!-- SECURE - Always escape output -->
<?= htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8') ?>
```

**Where to Apply**:
- All user-submitted data in HTML
- Admin dashboard tables
- Student information displays
- Contact form submissions

---

### 6. File Upload Security

**Issue**: Video upload handler exists but isn't used

**If you implement file uploads**:

```php
<?php
// Secure file upload
$allowed_extensions = ['mp4', 'webm', 'ogg'];
$max_file_size = 500 * 1024 * 1024; // 500MB

function secure_upload($file) {
    global $allowed_extensions, $max_file_size;
    
    // Check file size
    if ($file['size'] > $max_file_size) {
        return "File too large";
    }
    
    // Get actual MIME type (not extension)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    // Only allow video files
    $allowed_mimes = ['video/mp4', 'video/webm', 'video/ogg'];
    if (!in_array($mime, $allowed_mimes)) {
        return "Invalid file type";
    }
    
    // Generate safe filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safe_name = bin2hex(random_bytes(16)) . '.' . $extension;
    
    // Store outside web root if possible
    $upload_dir = __DIR__ . '/../../uploads/videos/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Move file
    if (move_uploaded_file($file['tmp_name'], $upload_dir . $safe_name)) {
        return $safe_name;
    }
    return "Upload failed";
}
?>
```

---

## 🟡 MEDIUM PRIORITY - Consider Soon

### 7. Session Security

**Recommendation**: Add session timeout
```php
<?php
// In includes/config.php
define('SESSION_TIMEOUT', 30 * 60); // 30 minutes

// In each admin page
if (isset($_SESSION['admin'])) {
    $current_time = time();
    $login_time = $_SESSION['login_time'] ?? $current_time;
    
    if ($current_time - $login_time > SESSION_TIMEOUT) {
        session_destroy();
        header("Location: /admin/auth/index.php?session_expired=1");
        exit;
    }
    
    $_SESSION['login_time'] = $current_time;
}
?>
```

### 8. CSRF Protection

**Add CSRF tokens to all forms**:
```php
<?php
// Generate token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- In forms -->
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <!-- form fields -->
</form>

<?php
// Verify token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('CSRF token validation failed');
    }
}
?>
```

### 9. Rate Limiting

**Prevent brute force attacks on login**:
```php
<?php
function check_rate_limit($identifier, $max_attempts = 5, $window = 300) {
    $cache_key = "rate_limit_$identifier";
    $attempts = apcu_fetch($cache_key) ?: [];
    
    $now = time();
    $attempts = array_filter($attempts, function($time) use ($now, $window) {
        return $time > ($now - $window);
    });
    
    if (count($attempts) >= $max_attempts) {
        return false; // Rate limited
    }
    
    $attempts[] = $now;
    apcu_store($cache_key, $attempts, $window);
    return true;
}

// In login script
$email = $_POST['email'] ?? '';
if (!check_rate_limit($email)) {
    die('Too many login attempts. Try again later.');
}
?>
```

---

## ✅ Security Checklist

### Before Going Live
- [ ] Debug folder deleted
- [ ] Credentials moved to `.env`
- [ ] All SQL queries use prepared statements
- [ ] All output is escaped with `htmlspecialchars()`
- [ ] All user input is validated
- [ ] `.env` is in `.gitignore`
- [ ] Passwords are hashed with `password_hash()`
- [ ] Session timeout implemented
- [ ] CSRF tokens on all forms
- [ ] No sensitive data in error messages
- [ ] PHP display_errors is OFF in production
- [ ] File uploads validated (extension & MIME type)
- [ ] Rate limiting on login
- [ ] HTTPS forced (check web.config)
- [ ] Secure headers set (X-Frame-Options, etc.)

### Regular Checks
- [ ] Review access logs for suspicious activity
- [ ] Audit admin action logs
- [ ] Check for outdated dependencies
- [ ] Test SQL injection vulnerabilities
- [ ] Test XSS vulnerabilities
- [ ] Check password strength requirements

---

## 🚀 Quick Fix Implementation

**Estimated Time**: 4-6 hours

### Hour 1-2: Delete & Move Credentials
1. Delete `admin/debug/` folder
2. Create `.env` file
3. Update `includes/config.php`
4. Update `.gitignore`

### Hour 2-3: Fix SQL Injection
1. Review all database queries
2. Convert to prepared statements
3. Test each update operation

### Hour 3-4: Input/Output Security
1. Add input validation functions
2. Escape all user output
3. Add CSRF token generation

### Hour 4-6: Additional Security
1. Add session timeout
2. Implement rate limiting
3. Add security headers
4. Test everything

---

## 📚 Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [OWASP SQL Injection](https://owasp.org/www-community/attacks/SQL_Injection)
- [OWASP XSS Prevention](https://owasp.org/www-community/attacks/xss/)
- [NIST Cybersecurity Framework](https://www.nist.gov/cyberframework)

---

**Priority**: Start with critical issues (sections 1-3) immediately before any public deployment.
