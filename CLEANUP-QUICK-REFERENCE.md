# 🗑️ Quick Delete Reference

## ✅ SAFE TO DELETE (33 files, ~8,550 lines)

### 🔴 DELETE IMMEDIATELY (Security Risk)
```
❌ admin/debug/                          (8 files - SECURITY ISSUE)
   ├── fix_password.php                  ⚠️ Anyone can reset admin password!
   ├── assignment_debug.php
   ├── check_registration_columns.php
   ├── create_dirs.php
   ├── php_config.php
   ├── planning_fixed.php
   ├── server_diagnostic.php
   └── upload_test.php

❌ test_db.php                           (in root - test file)
```

### 🟠 DELETE HIGH PRIORITY (Broken/Unused)
```
❌ student/                              (11 files - ORPHANED CODE)
   ├── auth/
   │   ├── login.php
   │   ├── logout.php
   │   └── register_password.php
   ├── course/
   │   ├── assignment.php
   │   ├── assignment_result.php
   │   ├── submit_assignment.php
   │   └── view_course.php
   ├── view_lesson.php
   ├── dashboard/
   │   ├── dashboard.php
   │   └── my_courses.php
   └── progress/
       └── my_progress.php

❌ admin/courses/                        (5 files - UNUSED)
   ├── edit_lesson.php
   ├── manage_courses.php
   ├── manage_lessons.php
   ├── manage_modules.php
   └── upload_video.php

❌ admin/assignments/                    (4 files - UNUSED)
   ├── create_assignment.php
   ├── edit_assignment.php
   ├── manage_assignments.php
   └── view_submissions.php

❌ database/setup_database.php           (broken - references missing file)
❌ database/update_lms_tables.php        (broken - references missing file)
❌ handlers/upload_video.php             (incomplete)
❌ handlers/upload_video_debug.php       (debug file)
```

### 🟡 DELETE MEDIUM PRIORITY (Documentation)
```
❌ Guide/LMS-Quick-Start.md
❌ Guide/LMS-Troubleshooting.md
❌ Guide/LMS-User-Guide.md
❌ Guide/LMS-Windows-Setup.md
❌ Guide/GitHub-Setup-Guide.md
❌ Guide/GitHub-Update-Guide.md
❌ Guide/Live-Server-Fix.md
❌ Guide/PowerShell-Fix.md
❌ Guide/PHP-Upload-Fix.md
❌ Guide/Strato-Setup.md
❌ Guide/Strato-VPS-Windows.md

⚠️  CONSIDER REMOVING:
    pages/cursus-engels-nederlands.php   (static duplicate, low priority)
    pages/cursus-russisch-nederlands.php (static duplicate, low priority)
```

---

## ✅ KEEP (Active Business Logic)

```
✓ index.php                              (Homepage)
✓ web.config                             (IIS configuration)
✓ README.md                              (Main documentation)

✓ admin/auth/                            (Login system)
  ├── index.php
  ├── logout.php
  └── change_password.php

✓ admin/dashboard/dashboard.php          (Main dashboard)

✓ admin/students/
  └── registered_students.php            (Student management)

✓ admin/payments/
  ├── pending_payments.php
  └── print_pending_payments.php

✓ admin/planning/planning.php            (Course planning)

✓ handlers/
  ├── submit_contact.php
  └── submit_registration.php

✓ pages/
  ├── about.php
  ├── contact.php
  ├── contact_success.php
  ├── register.php
  └── register_success.php

✓ includes/                              (All files - configuration & functions)
✓ assets/                                (All files - CSS, images, JS)

✓ Guide/README.md                        (Documentation hub)
✓ Guide/FEATURE-OVERVIEW.md              (ACTIVE system)
✓ Guide/IMPLEMENTATION-SUMMARY.md        (ACTIVE system)
✓ Guide/Registered-Students-Guide.md     (ACTIVE system)
✓ Guide/Registered-Students-Quick-Setup.md (ACTIVE system)
```

---

## 📊 Impact Summary

| Metric | Before | After | Savings |
|--------|--------|-------|---------|
| **PHP Files** | ~53 | ~20 | 62% ↓ |
| **Lines of Code** | ~25,000+ | ~15,000 | 40% ↓ |
| **Directories** | 13 | 9 | 31% ↓ |
| **Unused Tables** | 10+ | 0 | Clean DB |
| **Debug Files** | 8 | 0 | Security ✓ |

---

## 🚀 Deletion Sequence (Safe Order)

### Step 1: Security (do first)
```bash
# Remove debug folder - SECURITY CRITICAL
rm -r admin/debug/
```

### Step 2: Broken Files (do second)
```bash
# Remove broken database files
rm database/setup_database.php
rm database/update_lms_tables.php
```

### Step 3: Test Files (do third)
```bash
# Remove test files
rm test_db.php
rm handlers/upload_video_debug.php
```

### Step 4: Dead Code (do fourth)
```bash
# Remove unused admin modules
rm -r admin/courses/
rm -r admin/assignments/

# Remove student portal (orphaned)
rm -r student/
```

### Step 5: Cleanup (do last)
```bash
# Remove incomplete handler
rm handlers/upload_video.php

# Remove outdated guides
rm Guide/LMS-*.md
rm Guide/GitHub-*.md
rm Guide/Strato-*.md
rm Guide/*-Fix.md
```

### Step 6: Optional Cleanup
```bash
# Simplify course pages (optional)
# Keep or simplify pages/cursus-engels-nederlands.php
# Keep or simplify pages/cursus-russisch-nederlands.php
```

---

## ⚠️ CRITICAL SECURITY ISSUES

1. **Debug Folder Has Password Reset Tool** ⚠️
   - File: `admin/debug/fix_password.php`
   - Risk: Anyone knowing the URL can reset admin password
   - Action: DELETE IMMEDIATELY

2. **Exposed Credentials in Config** ⚠️
   - File: `includes/config.php`
   - Risk: Database and email passwords are hardcoded
   - Action: Move to `.env` file (not in Git)

3. **SQL Injection in Dashboard** ⚠️
   - File: `admin/dashboard/dashboard.php`
   - Risk: Status updates use string concatenation
   - Action: Use prepared statements

---

## ✨ Benefits After Cleanup

✅ **40% Less Code** - Easier to maintain  
✅ **No Debug Tools** - Better security  
✅ **Clear Architecture** - Faster onboarding  
✅ **Smaller Deployments** - Fewer files to upload  
✅ **No Dead Code** - Easier troubleshooting  
✅ **Cleaner Database** - Only active tables  

---

## 📋 Verification Checklist (After Deletion)

After deleting files, verify:

- [ ] Admin login still works
- [ ] Dashboard shows registrations
- [ ] Registered students page works
- [ ] Payment tracking works
- [ ] Course planning works
- [ ] Public registration form works
- [ ] Contact form works
- [ ] No 404 errors on admin pages
- [ ] No database errors in logs
- [ ] All email notifications send

---

## 💡 Pro Tips

**Don't delete before:**
- [ ] Taking a Git backup (`git branch backup-before-cleanup`)
- [ ] Testing in development environment
- [ ] Verifying no hardlinks to deleted files
- [ ] Updating dashboard navigation if needed

**Recommended approach:**
1. Create branch: `git checkout -b cleanup-dead-code`
2. Delete files one phase at a time
3. Test after each phase
4. Merge when confident: `git merge cleanup-dead-code`

---

**Estimated Time to Complete**: 3-4 hours total  
**Risk Level**: LOW (only unused code is deleted)  
**Rollback**: Easy (use Git history)

For detailed analysis, see: `CODE-CLEANUP-ANALYSIS.md`
