# CSRF Protection Testing Guide

**Version:** 1.0  
**Last Updated:** January 2025  
**Compatible With:** NT2 Taalles International v3.0+

---

## 📋 Table of Contents

1. [What is CSRF Protection?](#what-is-csrf-protection)
2. [How to Test CSRF Protection](#how-to-test-csrf-protection)
3. [Testing Methods](#testing-methods)
4. [Verification Checklist](#verification-checklist)
5. [Troubleshooting](#troubleshooting)

---

## 🎯 What is CSRF Protection?

**CSRF (Cross-Site Request Forgery)** is an attack where a malicious website tricks a user into submitting forms on your website without their knowledge.

**Example Attack:**
```
1. User logs into admin panel (your site)
2. User visits malicious website
3. Malicious site sends form to your admin panel
4. Form executes action (delete, update) without user knowing
5. Data is deleted/changed
```

**CSRF Protection Prevents:**
- ✅ Unauthorized form submissions
- ✅ Unauthorized actions (delete, update)
- ✅ Cross-site attacks
- ✅ Malicious requests

---

## 🧪 How to Test CSRF Protection

### Quick Test (30 seconds)

1. **Open any admin form** (e.g., login page)
2. **Open Developer Tools** (F12)
3. **Find CSRF token** in page source:
   ```html
   <input type="hidden" name="csrf_token" value="abc123...">
   ```
4. **Delete the token field**
5. **Submit the form**
6. **Expected Result:** Error message "Invalid CSRF token"

✅ **If you see the error = CSRF protection is working!**

---

## 🔍 Testing Methods

### Method 1: Remove CSRF Token from Form

**Steps:**
1. Open admin page with form
2. Open Developer Tools (F12) → Elements tab
3. Find the form element
4. Locate CSRF token field:
   ```html
   <input type="hidden" name="csrf_token" value="...">
   ```
5. Right-click → Delete element
6. Submit form

**Expected Result:**
- ❌ Form submission fails
- ✅ Error message: "Invalid CSRF token. Please refresh the page and try again."

**If form submits successfully:**
- ⚠️ CSRF protection NOT working - Security issue!

---

### Method 2: Test with Invalid Token

**Steps:**
1. Open admin form
2. Find CSRF token field in HTML
3. Change token value to something invalid:
   ```html
   <input type="hidden" name="csrf_token" value="invalid_token_123">
   ```
4. Submit form

**Expected Result:**
- ❌ Form submission fails
- ✅ Error message appears

---

### Method 3: Test AJAX Requests

**Steps:**
1. Open admin dashboard
2. Open Developer Tools (F12) → Console tab
3. Try AJAX request without CSRF token:
   ```javascript
   fetch('dashboard.php', {
       method: 'POST',
       body: 'update_status=1&id=1&status=New'
   }).then(r => r.text()).then(console.log);
   ```
4. Check response

**Expected Result:**
- ❌ Request fails
- ✅ Response: "Invalid CSRF token..."

**Then test with valid token:**
```javascript
const csrfToken = document.querySelector('input[name="csrf_token"]').value;
fetch('dashboard.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'update_status=1&id=1&status=New&csrf_token=' + csrfToken
}).then(r => r.text()).then(console.log);
```

**Expected Result:**
- ✅ Request succeeds
- ✅ Response: "OK"

---

### Method 4: Test DELETE Links

**Steps:**
1. Open admin page with delete links
2. Check delete link includes CSRF token:
   ```html
   <a href="?delete=1&csrf_token=abc123...">Delete</a>
   ```
3. Try accessing without token:
   ```
   /admin/page.php?delete=1
   ```

**Expected Result:**
- ❌ Action fails
- ✅ Error message or redirect

---

### Method 5: Browser Console Test

**Steps:**
1. Open admin dashboard
2. Open Developer Tools (F12) → Console
3. Check if CSRF token exists:
   ```javascript
   const csrfField = document.querySelector('input[name="csrf_token"]');
   console.log('CSRF Token exists:', csrfField !== null);
   console.log('CSRF Token value:', csrfField ? csrfField.value : 'NOT FOUND');
   ```

**Expected Result:**
- ✅ Token exists: `true`
- ✅ Token value: Long random string (e.g., "a1b2c3d4e5f6...")

---

### Method 6: Visual Inspection

**Steps:**
1. Open admin page
2. View page source (Right-click → View Page Source)
3. Search for "csrf_token"
4. Verify token appears in forms

**Expected Result:**
- ✅ Token found in all forms
- ✅ Token appears as hidden input field

---

## ✅ Verification Checklist

Use this checklist to verify CSRF protection:

### Forms
- [ ] Login form has CSRF token
- [ ] Password change form has CSRF token
- [ ] Course creation form has CSRF token
- [ ] Student edit form has CSRF token
- [ ] All forms submit successfully WITH token
- [ ] All forms fail WITHOUT token

### AJAX Requests
- [ ] Status update AJAX includes token
- [ ] AJAX requests work WITH token
- [ ] AJAX requests fail WITHOUT token

### DELETE Operations
- [ ] Delete links include CSRF token
- [ ] Delete works WITH token
- [ ] Delete fails WITHOUT token

### Visual Checks
- [ ] CSRF token field visible in HTML source
- [ ] Token value is random string (not empty)
- [ ] Token changes on page refresh

---

## 🐛 Troubleshooting

### Issue: CSRF token not found in form

**Possible Causes:**
1. Form doesn't include `<?= CSRF::getTokenField() ?>`
2. CSRF class not loaded
3. Session not started

**Solution:**
```php
// Make sure CSRF is included
require_once __DIR__ . '/../../includes/csrf.php';

// In form
<form method="POST">
    <?= CSRF::getTokenField() ?>
    <!-- form fields -->
</form>
```

### Issue: "Invalid CSRF token" even with token

**Possible Causes:**
1. Session expired
2. Token from different session
3. Multiple tabs open

**Solution:**
1. Refresh page to get new token
2. Close other admin tabs
3. Check session is active
4. Verify session ID matches

### Issue: Form submits without token

**Possible Causes:**
1. CSRF validation not implemented
2. Token validation skipped
3. GET request instead of POST

**Solution:**
```php
// Make sure validation is in place
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireToken(); // This should be called
    // Process form...
}
```

### Issue: AJAX request fails even with token

**Possible Causes:**
1. Token not included in request body
2. Wrong Content-Type header
3. Token expired

**Solution:**
```javascript
// Correct AJAX request
const csrfToken = document.querySelector('input[name="csrf_token"]').value;
fetch('page.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'data=value&csrf_token=' + csrfToken
});
```

---

## 📊 Test Results Template

Use this template to document your tests:

```
CSRF Protection Test Results
Date: _______________
Tester: _______________

Test 1: Login Form
[ ] Token present: Yes/No
[ ] Form submits with token: Yes/No
[ ] Form fails without token: Yes/No

Test 2: Dashboard Status Update
[ ] Token present: Yes/No
[ ] AJAX works with token: Yes/No
[ ] AJAX fails without token: Yes/No

Test 3: Delete Operation
[ ] Token in link: Yes/No
[ ] Delete works with token: Yes/No
[ ] Delete fails without token: Yes/No

Overall Status: ✅ Protected / ⚠️ Issues Found
```

---

## 🔗 Related Documentation

- [Security Architecture Guide](Security-Architecture-Guide.md) - Understand CSRF implementation
- [Admin Security Migration Guide](Admin-Security-Migration-Guide.md) - How to add CSRF protection

---

**Last Updated:** January 2025  
**Version:** 1.0  
**Maintainer:** NT2 Taalles International Development Team

