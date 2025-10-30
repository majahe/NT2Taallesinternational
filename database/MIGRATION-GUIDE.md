# Admin Files Migration Guide

## Step-by-Step Instructions

### Step 1: Create Directory Structure
```bash
mkdir -p includes/database
mkdir -p includes/errors
```

### Step 2: Create Core Security Files
1. ✅ `includes/admin_auth.php` - Centralized admin authentication
2. ✅ `includes/database/QueryBuilder.php` - Secure database queries
3. ✅ `includes/csrf.php` - CSRF protection
4. ✅ `includes/error_handler.php` - Error handling
5. ✅ `includes/errors/500.php` - Error page template
6. ✅ `includes/init.php` - Optional bootstrap file

### Step 3: Update Authentication Files
1. ✅ `admin/auth/index.php` - Secure login with CSRF and QueryBuilder
2. ✅ `admin/auth/change_password.php` - Secure password change

### Step 4: Update Dashboard
1. ✅ `admin/dashboard/dashboard.php` - Secure dashboard with CSRF protection

### Step 5: Test Login
- Try logging in as admin
- Verify CSRF protection works
- Check session timeout (30 minutes)

### Step 6: Run Migration Script (Optional)
```bash
# Test first (dry run)
php database/migrate_admin_files.php --dry-run

# Apply changes with backup
php database/migrate_admin_files.php --backup
```

### Step 7: Manual Updates Required
Some files need manual updates:
- Add CSRF tokens to all forms
- Update DELETE links to include CSRF tokens
- Update AJAX requests to include CSRF tokens

### Step 8: Test All Admin Pages
- Dashboard
- Courses management
- Students management
- Assignments
- Payments
- Planning

## Important Notes
- Always backup before migration
- Test in development first
- Some complex queries may need manual migration
- Keep prepared statements for complex queries

## Quick Reference

### Replace Session Check
```php
// OLD
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: ../auth/index.php");
  exit;
}

// NEW
require_once __DIR__ . '/../../includes/admin_auth.php';
require_admin_auth();
```

### Replace Direct SQL
```php
// OLD
$conn->query("DELETE FROM table WHERE id = $id");

// NEW
$db->delete('table', ['id' => $id]);
```

### Add CSRF to Forms
```html
<form method="POST">
  <?= CSRF::getTokenField() ?>
  <!-- form fields -->
</form>
```

### Add CSRF to AJAX
```javascript
const csrfToken = document.querySelector('input[name="csrf_token"]').value;
fetch('url.php', {
  method: 'POST',
  body: 'data=value&csrf_token=' + csrfToken
});
```

