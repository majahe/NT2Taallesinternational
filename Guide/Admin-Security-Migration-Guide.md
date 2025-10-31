# Admin Security Migration Guide

**Version:** 1.0  
**Last Updated:** January 2025  
**Compatible With:** NT2 Taalles International v3.0+

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [What's Being Migrated](#whats-being-migrated)
3. [Prerequisites](#prerequisites)
4. [Migration Process](#migration-process)
5. [Testing After Migration](#testing-after-migration)
6. [Manual Updates Required](#manual-updates-required)
7. [Troubleshooting](#troubleshooting)
8. [Rollback Procedures](#rollback-procedures)

---

## 🎯 Overview

This guide will help you migrate all admin files to use the new secure architecture. The migration improves security, reduces code duplication, and makes the codebase more maintainable.

### What Changes?

**Before Migration:**
- Manual session checks on every page
- Direct SQL queries (SQL injection risk)
- No CSRF protection
- Inconsistent error handling

**After Migration:**
- Centralized authentication
- Secure database queries (QueryBuilder)
- CSRF protection on all forms
- Consistent error handling

---

## 📦 What's Being Migrated

### Files Automatically Migrated

The migration script updates these files:

- ✅ `admin/dashboard/dashboard.php` (already updated manually)
- ✅ `admin/courses/manage_courses.php`
- ✅ `admin/courses/manage_modules.php`
- ✅ `admin/courses/manage_lessons.php`
- ✅ `admin/courses/edit_lesson.php`
- ✅ `admin/courses/upload_video.php`
- ✅ `admin/courses/manual_upload.php`
- ✅ `admin/students/registered_students.php`
- ✅ `admin/students/grant_course_access.php`
- ✅ `admin/assignments/create_assignment.php`
- ✅ `admin/assignments/edit_assignment.php`
- ✅ `admin/assignments/manage_assignments.php`
- ✅ `admin/assignments/view_submissions.php`
- ✅ `admin/payments/pending_payments.php`
- ✅ `admin/payments/print_pending_payments.php`
- ✅ `admin/planning/planning.php`

### Files Already Updated

These files were updated manually and won't be changed:

- ✅ `admin/auth/index.php` - Secure login
- ✅ `admin/auth/change_password.php` - Secure password change
- ✅ `admin/dashboard/dashboard.php` - Secure dashboard

### Files Not Migrated

These files are excluded from migration:

- ⚠️ `admin/debug/*` - Debug utilities (optional)
- ⚠️ `admin/auth/logout.php` - Simple logout (no changes needed)

---

## ✅ Prerequisites

Before starting migration, ensure:

1. ✅ **Backup Created** - Backup your entire project
2. ✅ **Core Files Exist** - All security files created:
   - `includes/admin_auth.php`
   - `includes/database/QueryBuilder.php`
   - `includes/csrf.php`
   - `includes/error_handler.php`
   - `includes/errors/500.php`

3. ✅ **Database Backup** - Backup your database
4. ✅ **Test Environment** - Test in development first
5. ✅ **Git Commit** - Commit current changes (optional but recommended)

---

## 🚀 Migration Process

### Step 1: Backup Everything

```bash
# Backup entire project
cp -r admin admin_backup_manual
# Or on Windows:
xcopy admin admin_backup_manual /E /I

# Backup database
mysqldump -u username -p database_name > backup.sql
```

### Step 2: Test Migration (Dry Run)

```bash
# Navigate to project directory
cd "C:\Users\majah\OneDrive\Bureaublad\Ai Programmas\Cursor Projecten\NT2Taallesinternational"

# Run dry run (no changes made)
php database/migrate_admin_files.php --dry-run
```

**Review Output:**
- Check which files will be changed
- Verify changes look correct
- Note any files that will be skipped

### Step 3: Run Actual Migration

```bash
# Run migration with automatic backup
php database/migrate_admin_files.php --backup
```

**What Happens:**
1. Creates backup in `admin_backup_YYYY-MM-DD_HH-MM-SS/`
2. Updates files automatically
3. Shows summary of changes

**Expected Output:**
```
🚀 Admin Files Migration Script
================================

📦 Creating backup...
✓ Backup created: admin_backup_2025-01-XX_XX-XX-XX

📝 Migrating files...

Processing: courses/manage_courses.php
  ✓ Replaced manual session check with require_admin_auth()
  ✓ Added QueryBuilder initialization
  ✓ Added CSRF protection

...

================================
✅ Migration complete!
   Migrated: 15 files
   Skipped: 1 files
   Backup: admin_backup_2025-01-XX_XX-XX-XX
```

### Step 4: Verify Backup

```bash
# Check backup was created
dir admin_backup_*

# Verify files exist
dir admin_backup_*\courses\manage_courses.php
```

---

## 🧪 Testing After Migration

### Test 1: Login

1. Go to `/admin/auth/index.php`
2. Login with admin credentials
3. ✅ Should login successfully
4. ✅ Should redirect to dashboard

### Test 2: Dashboard Access

1. After login, check dashboard loads
2. ✅ Should show statistics
3. ✅ Should display registrations
4. ✅ No PHP errors

### Test 3: CSRF Protection

1. Open any admin page with a form
2. View page source
3. ✅ Should see CSRF token field:
   ```html
   <input type="hidden" name="csrf_token" value="...">
   ```
4. Try submitting form without token (remove it in DevTools)
5. ✅ Should show "Invalid CSRF token" error

### Test 4: Database Operations

1. Try updating a registration status
2. ✅ Should work correctly
3. ✅ No SQL errors
4. Try deleting a record (with CSRF token)
5. ✅ Should work correctly

### Test 5: Session Timeout

1. Login to admin panel
2. Wait 30 minutes (or change timeout in code for testing)
3. ✅ Should logout automatically
4. ✅ Should redirect to login

### Test 6: All Admin Pages

Check each admin page loads:

- [ ] `/admin/dashboard/dashboard.php`
- [ ] `/admin/courses/manage_courses.php`
- [ ] `/admin/courses/manage_modules.php`
- [ ] `/admin/students/registered_students.php`
- [ ] `/admin/assignments/manage_assignments.php`
- [ ] `/admin/payments/pending_payments.php`
- [ ] `/admin/planning/planning.php`

---

## 🔧 Manual Updates Required

After running the migration script, you may need to manually update some things:

### 1. Add CSRF Tokens to Forms

**Find all forms:**
```bash
# Search for forms
grep -r "<form" admin/
```

**Add CSRF token after `<form>` tag:**
```html
<form method="POST">
    <?= CSRF::getTokenField() ?>
    <!-- rest of form -->
</form>
```

### 2. Update DELETE Links

**Before:**
```html
<a href="?delete=<?= $id ?>">Delete</a>
```

**After (Option 1 - GET with token):**
```html
<a href="?delete=<?= $id ?>&csrf_token=<?= CSRF::generateToken() ?>">Delete</a>
```

**After (Option 2 - POST form):**
```html
<form method="POST" style="display: inline;">
    <?= CSRF::getTokenField() ?>
    <input type="hidden" name="delete" value="<?= $id ?>">
    <button type="submit" onclick="return confirm('Delete?')">Delete</button>
</form>
```

### 3. Update AJAX Requests

**Before:**
```javascript
fetch('page.php', {
    method: 'POST',
    body: 'data=value'
});
```

**After:**
```javascript
const csrfToken = document.querySelector('input[name="csrf_token"]').value;
fetch('page.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'data=value&csrf_token=' + csrfToken
});
```

### 4. Replace Direct SQL Queries

**Find direct queries:**
```bash
# Search for direct queries
grep -r "\$conn->query" admin/
```

**Replace with QueryBuilder:**
```php
// Before
$result = $conn->query("SELECT * FROM table WHERE id = $id");

// After
$result = $db->select('table', '*', ['id' => $id]);
```

---

## 🐛 Troubleshooting

### Issue: "Class 'QueryBuilder' not found"

**Solution:**
```php
// Make sure QueryBuilder is included
require_once __DIR__ . '/../../includes/database/QueryBuilder.php';
$db = new QueryBuilder($conn);
```

### Issue: "Call to undefined function require_admin_auth()"

**Solution:**
```php
// Make sure admin_auth.php is included first
require_once __DIR__ . '/../../includes/admin_auth.php';
require_admin_auth();
```

### Issue: "CSRF token validation failed"

**Causes:**
1. Form missing CSRF token
2. AJAX request missing token
3. Session expired

**Solution:**
1. Check form includes `<?= CSRF::getTokenField() ?>`
2. Check AJAX includes token in body
3. Refresh page to get new token
4. Check session is active

### Issue: Forms submit but no changes saved

**Possible Causes:**
1. CSRF validation failing silently
2. QueryBuilder query failing
3. Database connection issue

**Solution:**
1. Check PHP error logs
2. Enable error display temporarily
3. Check database connection
4. Verify QueryBuilder queries

### Issue: Migration script says "No changes needed"

**Possible Reasons:**
1. File already migrated
2. File doesn't match patterns
3. File uses different code structure

**Solution:**
1. Check file manually
2. Verify it has security updates
3. If not, migrate manually using templates

### Issue: Some files still use old code

**Solution:**
1. Check if file was in migration list
2. If not, add it manually
3. Use migration templates for guidance
4. Test thoroughly after manual update

---

## ⏪ Rollback Procedures

If something goes wrong, you can rollback:

### Option 1: Restore from Backup

```bash
# Stop using migrated files
mv admin admin_migrated

# Restore backup
mv admin_backup_YYYY-MM-DD_HH-MM-SS admin

# Test everything works
```

### Option 2: Restore Individual Files

```bash
# Restore specific file
cp admin_backup_YYYY-MM-DD_HH-MM-SS/courses/manage_courses.php admin/courses/manage_courses.php
```

### Option 3: Git Rollback (if using Git)

```bash
# Check status
git status

# Restore specific file
git checkout HEAD -- admin/courses/manage_courses.php

# Or restore all admin files
git checkout HEAD -- admin/
```

---

## 📋 Post-Migration Checklist

After migration, verify:

- [ ] All admin pages load without errors
- [ ] Login/logout works correctly
- [ ] CSRF protection active (forms fail without token)
- [ ] Database operations work (CRUD)
- [ ] Session timeout works (30 minutes)
- [ ] No SQL injection vulnerabilities
- [ ] All forms have CSRF tokens
- [ ] All DELETE operations require CSRF token
- [ ] AJAX requests include CSRF tokens
- [ ] Error handling works correctly

---

## 🔄 Re-running Migration

If you need to re-run migration:

1. **Restore from backup first**
2. **Fix any issues** that caused problems
3. **Run dry-run** to verify changes
4. **Run migration** again

**Note:** Migration script is idempotent - safe to run multiple times, but files already migrated will be skipped.

---

## 📚 Migration Templates

### Template 1: Basic Admin Page

```php
<?php
require_once __DIR__ . '/../../includes/admin_auth.php';
require_admin_auth();

require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/database/QueryBuilder.php';
require_once __DIR__ . '/../../includes/csrf.php';

$db = new QueryBuilder($conn);

// Your code here
?>
```

### Template 2: Page with Form

```php
<?php
// ... includes above ...

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireToken();
    
    // Process form
    $data = [
        'field1' => $_POST['field1'],
        'field2' => $_POST['field2']
    ];
    
    $db->insert('table', $data);
}
?>
<form method="POST">
    <?= CSRF::getTokenField() ?>
    <!-- form fields -->
</form>
```

### Template 3: Page with DELETE

```php
<?php
// ... includes above ...

if (isset($_GET['delete']) && isset($_GET['csrf_token'])) {
    CSRF::requireToken();
    
    $id = intval($_GET['delete']);
    if ($db->delete('table', ['id' => $id])) {
        header("Location: page.php?deleted=1");
        exit;
    }
}
?>
<a href="?delete=<?= $id ?>&csrf_token=<?= CSRF::generateToken() ?>">Delete</a>
```

---

## 🎓 Learning More

- [Security Architecture Guide](Security-Architecture-Guide.md) - Understand the security system
- [CSRF Testing Guide](CSRF-Testing-Guide.md) - How to test CSRF protection
- [Database Migration Guide](../database/MIGRATION-GUIDE.md) - Migration script details

---

## ✅ Success Criteria

Migration is successful when:

1. ✅ All admin pages load without errors
2. ✅ All forms have CSRF protection
3. ✅ All database queries use QueryBuilder
4. ✅ All pages use centralized authentication
5. ✅ Session timeout works correctly
6. ✅ No SQL injection vulnerabilities
7. ✅ No CSRF vulnerabilities
8. ✅ Error handling works properly

---

## 📞 Getting Help

If you encounter issues:

1. **Check Troubleshooting** section above
2. **Review Error Logs** - Check PHP error log
3. **Test CSRF** - Verify protection works
4. **Check Backup** - Verify backup exists
5. **Rollback if Needed** - Use backup to restore

---

**Last Updated:** January 2025  
**Version:** 1.0  
**Maintainer:** NT2 Taalles International Development Team

