# Security Architecture Guide

**Version:** 1.0  
**Last Updated:** January 2025  
**Compatible With:** NT2 Taalles International v3.0+

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Security Components](#security-components)
3. [Admin Authentication](#admin-authentication)
4. [Database Security](#database-security)
5. [CSRF Protection](#csrf-protection)
6. [Error Handling](#error-handling)
7. [Session Management](#session-management)
8. [Security Best Practices](#security-best-practices)
9. [Before & After Comparison](#before--after-comparison)
10. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

The NT2 Taalles International admin portal now uses a **professional security architecture** designed to protect against common web vulnerabilities and provide a solid foundation for secure operations.

### Key Security Improvements

✅ **Centralized Authentication** - Single source of truth for admin access  
✅ **SQL Injection Prevention** - QueryBuilder with prepared statements  
✅ **CSRF Protection** - All forms protected against cross-site attacks  
✅ **Session Timeout** - Automatic logout after 30 minutes  
✅ **Password Security** - Modern password hashing (password_hash)  
✅ **Error Handling** - Centralized error management  
✅ **Input Validation** - All user inputs validated and sanitized

---

## 🛡️ Security Components

### 1. Admin Authentication (`includes/admin_auth.php`)

**Purpose:** Centralized authentication middleware for all admin pages.

**Features:**
- Session-based authentication
- Automatic session timeout (30 minutes)
- Redirect handling for protected pages
- Helper functions for checking auth status

**Usage:**
```php
<?php
require_once __DIR__ . '/../../includes/admin_auth.php';
require_admin_auth(); // Automatically redirects if not logged in
?>
```

**Functions Available:**
- `require_admin_auth()` - Require admin login (redirects if not)
- `is_admin_logged_in()` - Check if admin is logged in
- `get_admin_username()` - Get current admin username

---

### 2. QueryBuilder (`includes/database/QueryBuilder.php`)

**Purpose:** Secure database query abstraction layer.

**Features:**
- Prepared statements (prevents SQL injection)
- Type-safe parameter binding
- Easy-to-use API for common operations

**Usage:**
```php
<?php
require_once __DIR__ . '/../../includes/database/QueryBuilder.php';
$db = new QueryBuilder($conn);

// Select records
$users = $db->select('registrations', '*', ['status' => 'New'], 'created_at DESC');

// Count records
$total = $db->count('registrations', ['status' => 'Active']);

// Insert record
$id = $db->insert('registrations', [
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Update record
$db->update('registrations', ['status' => 'Registered'], ['id' => $id]);

// Delete record
$db->delete('registrations', ['id' => $id]);
?>
```

**Methods:**
- `select($table, $columns, $where, $orderBy, $limit)` - Select records
- `count($table, $where)` - Count records
- `insert($table, $data)` - Insert new record
- `update($table, $data, $where)` - Update records
- `delete($table, $where)` - Delete records

---

### 3. CSRF Protection (`includes/csrf.php`)

**Purpose:** Prevents Cross-Site Request Forgery (CSRF) attacks.

**How It Works:**
1. Generates unique token per session
2. Token included in all forms
3. Validates token on form submission
4. Rejects requests without valid token

**Usage in Forms:**
```php
<form method="POST">
    <?= CSRF::getTokenField() ?>
    <!-- form fields -->
</form>
```

**Usage in PHP:**
```php
<?php
require_once __DIR__ . '/../../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireToken(); // Validates token, dies if invalid
    // Process form...
}
?>
```

**Usage in AJAX:**
```javascript
const csrfToken = document.querySelector('input[name="csrf_token"]').value;
fetch('page.php', {
    method: 'POST',
    body: 'data=value&csrf_token=' + csrfToken
});
```

**Methods:**
- `generateToken()` - Generate/retrieve CSRF token
- `validateToken($token)` - Validate token
- `getTokenField()` - Get HTML input field for forms
- `requireToken()` - Require valid token (dies if invalid)

---

### 4. Error Handling (`includes/error_handler.php`)

**Purpose:** Centralized error and exception handling.

**Features:**
- Exception handling
- Error logging
- Production/development modes
- User-friendly error pages

**Usage:**
```php
<?php
require_once __DIR__ . '/../../includes/error_handler.php';
ErrorHandler::init(false); // false = development, true = production
?>
```

**Production Mode:**
- Errors logged to log file
- User sees friendly error page
- No sensitive information exposed

**Development Mode:**
- Errors displayed on screen
- Full stack traces
- Debugging information shown

---

### 5. Error Page Template (`includes/errors/500.php`)

**Purpose:** User-friendly error page for production.

**Features:**
- Professional design
- No technical details exposed
- Link back to home page

---

## 🔐 Admin Authentication

### How It Works

1. **Login Process:**
   ```
   User enters credentials
   → QueryBuilder validates (no SQL injection)
   → Password verified (password_hash or SHA2 fallback)
   → Session created with admin info
   → Last activity timestamp set
   ```

2. **Session Management:**
   ```
   Every page load
   → Check if admin session exists
   → Check if session expired (>30 minutes)
   → Update last activity timestamp
   → Allow access or redirect to login
   ```

3. **Session Timeout:**
   - Automatic logout after 30 minutes of inactivity
   - Session destroyed on timeout
   - Redirect to login with expired message

### Implementation Example

**Before (Insecure):**
```php
<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/index.php");
    exit;
}
// Direct SQL queries (vulnerable to SQL injection)
$result = $conn->query("SELECT * FROM table WHERE id = $id");
?>
```

**After (Secure):**
```php
<?php
require_once __DIR__ . '/../../includes/admin_auth.php';
require_admin_auth();

require_once __DIR__ . '/../../includes/database/QueryBuilder.php';
$db = new QueryBuilder($conn);

// Secure query (SQL injection prevented)
$result = $db->select('table', '*', ['id' => $id]);
?>
```

---

## 🗄️ Database Security

### SQL Injection Prevention

**Old Way (VULNERABLE):**
```php
$id = $_GET['id'];
$conn->query("DELETE FROM registrations WHERE id = $id");
// Attacker can inject: ?id=1 OR 1=1 -- (deletes everything!)
```

**New Way (SECURE):**
```php
$id = intval($_GET['id']);
$db->delete('registrations', ['id' => $id]);
// QueryBuilder uses prepared statements - injection impossible
```

### QueryBuilder Benefits

✅ **Automatic Escaping** - All values escaped automatically  
✅ **Type Safety** - Correct parameter types (int/string/float)  
✅ **Readable Code** - Clear, maintainable queries  
✅ **Consistent** - Same pattern everywhere  
✅ **Secure by Default** - No way to introduce SQL injection

---

## 🔒 CSRF Protection

### What is CSRF?

**Cross-Site Request Forgery** - Attack where malicious site tricks user into submitting forms on your site.

**Example Attack:**
```
1. User logged into admin panel
2. User visits malicious website
3. Malicious site sends form to your admin panel
4. Form executes without user knowing
5. Data deleted/changed
```

### How CSRF Protection Works

1. **Token Generation:**
   ```php
   // On page load
   $token = CSRF::generateToken(); // Unique token per session
   ```

2. **Token in Form:**
   ```html
   <form method="POST">
       <input type="hidden" name="csrf_token" value="abc123...">
       <!-- form fields -->
   </form>
   ```

3. **Token Validation:**
   ```php
   // On form submission
   CSRF::requireToken(); // Validates token matches session
   ```

4. **Protection:**
   - Attacker can't access token (session-only)
   - Token changes per session
   - Invalid token = request rejected

### CSRF Protection Checklist

- [ ] All forms include CSRF token
- [ ] All POST requests validate token
- [ ] All AJAX requests include token
- [ ] All DELETE links include token
- [ ] Test: Forms fail without token

---

## ⚠️ Error Handling

### Error Levels

**Development Mode:**
- All errors displayed
- Full stack traces
- Debugging information
- Logs to file

**Production Mode:**
- Errors logged only
- User sees friendly page
- No sensitive data exposed
- 500 error page shown

### Configuration

```php
// Development
ErrorHandler::init(false);

// Production
ErrorHandler::init(true);
```

### Error Types Handled

- **Exceptions** - Caught and logged
- **PHP Errors** - Logged and handled
- **Fatal Errors** - Caught if possible
- **Database Errors** - Logged with context

---

## 🔄 Session Management

### Session Security Features

1. **Session Timeout:**
   - 30 minutes of inactivity
   - Automatic logout
   - Session destroyed

2. **Session Regeneration:**
   - New session ID on login
   - Prevents session fixation

3. **Secure Session Storage:**
   - Server-side only
   - No sensitive data in cookies
   - HTTP-only cookies (if used)

### Session Variables

**Admin Session:**
- `$_SESSION['admin']` - Admin username
- `$_SESSION['admin_id']` - Admin ID
- `$_SESSION['admin_last_activity']` - Last activity timestamp
- `$_SESSION['csrf_token']` - CSRF token
- `$_SESSION['redirect_after_login']` - Redirect URL

---

## ✅ Security Best Practices

### Password Security

✅ **Minimum Length:** 8 characters  
✅ **Use Password Hash:** password_hash() function  
✅ **Password Upgrade:** Auto-upgrade from SHA2  
✅ **No Plain Text:** Never store passwords in plain text

### Code Security

✅ **Prepared Statements:** Always use QueryBuilder  
✅ **Input Validation:** Validate all user inputs  
✅ **Output Escaping:** Use htmlspecialchars()  
✅ **CSRF Tokens:** All forms protected  
✅ **Authentication:** Check on every page

### Session Security

✅ **Timeout:** 30 minutes inactivity  
✅ **Regeneration:** New session on login  
✅ **Secure Storage:** Server-side only  
✅ **Logout:** Proper session destruction

### Database Security

✅ **Least Privilege:** Minimal database permissions  
✅ **Backup Regularly:** Daily backups recommended  
✅ **Monitor Logs:** Check for suspicious activity  
✅ **Update Regularly:** Keep PHP and MySQL updated

---

## 📊 Before & After Comparison

### Authentication

| Aspect | Before | After |
|--------|--------|-------|
| **Code Duplication** | Manual checks on every page | Single function call |
| **Session Timeout** | None | 30 minutes automatic |
| **Redirect Handling** | Manual | Automatic |
| **Maintainability** | Hard to update | Easy to update |

### Database Queries

| Aspect | Before | After |
|--------|--------|-------|
| **SQL Injection Risk** | High (direct queries) | None (prepared statements) |
| **Code Readability** | Mixed | Consistent |
| **Type Safety** | Manual | Automatic |
| **Error Handling** | Inconsistent | Centralized |

### Form Security

| Aspect | Before | After |
|--------|--------|-------|
| **CSRF Protection** | None | All forms protected |
| **Token Management** | Manual | Automatic |
| **Attack Prevention** | Vulnerable | Protected |

### Error Handling

| Aspect | Before | After |
|--------|--------|-------|
| **Error Display** | Raw PHP errors | User-friendly pages |
| **Error Logging** | Manual | Automatic |
| **Production Mode** | Errors exposed | Secure error pages |

---

## 🔧 Troubleshooting

### Issue: "Call to undefined function require_admin_auth()"

**Solution:**
```php
// Make sure you include the file first
require_once __DIR__ . '/../../includes/admin_auth.php';
require_admin_auth();
```

### Issue: "Class 'QueryBuilder' not found"

**Solution:**
```php
// Include QueryBuilder before use
require_once __DIR__ . '/../../includes/database/QueryBuilder.php';
$db = new QueryBuilder($conn);
```

### Issue: "CSRF token validation failed"

**Solution:**
1. Check form includes token: `<?= CSRF::getTokenField() ?>`
2. Check PHP validates token: `CSRF::requireToken()`
3. Check AJAX includes token in request
4. Refresh page to get new token

### Issue: Session expires too quickly

**Solution:**
```php
// In admin_auth.php, adjust timeout (currently 1800 = 30 minutes)
if (time() - $_SESSION['admin_last_activity'] > 1800) {
    // Change 1800 to desired seconds (e.g., 3600 = 1 hour)
}
```

### Issue: Password not working after upgrade

**Solution:**
- System supports both SHA2 and password_hash
- First login upgrades password automatically
- If issues persist, reset password via admin panel

---

## 📚 Quick Reference

### Required Includes (Every Admin Page)

```php
<?php
require_once __DIR__ . '/../../includes/admin_auth.php';
require_admin_auth();

require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/database/QueryBuilder.php';
require_once __DIR__ . '/../../includes/csrf.php';

$db = new QueryBuilder($conn);
?>
```

### Form Template

```html
<form method="POST">
    <?= CSRF::getTokenField() ?>
    <!-- form fields -->
    <button type="submit">Submit</button>
</form>
```

### AJAX Template

```javascript
const csrfToken = document.querySelector('input[name="csrf_token"]').value;
fetch('handler.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'data=value&csrf_token=' + csrfToken
});
```

---

## 🎓 Learning Resources

### Security Concepts

- **SQL Injection:** OWASP Top 10
- **CSRF:** Cross-Site Request Forgery explained
- **Session Management:** PHP session security
- **Password Hashing:** password_hash() documentation

### PHP Documentation

- [password_hash()](https://www.php.net/password_hash)
- [Prepared Statements](https://www.php.net/manual/en/mysqli.quickstart.prepared-statements.php)
- [Session Security](https://www.php.net/manual/en/session.security.php)

---

## ✅ Security Checklist

Use this checklist when creating new admin pages:

- [ ] Include `admin_auth.php` and call `require_admin_auth()`
- [ ] Use QueryBuilder for all database queries
- [ ] Include CSRF token in all forms
- [ ] Validate CSRF token on POST requests
- [ ] Include CSRF token in AJAX requests
- [ ] Use `htmlspecialchars()` for output
- [ ] Validate and sanitize all inputs
- [ ] Use prepared statements (via QueryBuilder)
- [ ] Test without CSRF token (should fail)
- [ ] Test with invalid credentials (should fail)
- [ ] Test session timeout (should logout)

---

## 🔗 Related Documentation

- [Admin Security Migration Guide](Admin-Security-Migration-Guide.md) - How to migrate existing pages
- [CSRF Testing Guide](CSRF-Testing-Guide.md) - How to test CSRF protection
- [Database Migration Guide](../database/MIGRATION-GUIDE.md) - Migration script usage

---

**Last Updated:** January 2025  
**Version:** 1.0  
**Maintainer:** NT2 Taalles International Development Team

