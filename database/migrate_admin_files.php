<?php
/**
 * Admin Files Migration Script
 * Automatically updates admin files to use new security system
 * 
 * Usage: php database/migrate_admin_files.php [--dry-run] [--backup]
 */

$dryRun = in_array('--dry-run', $argv);
$backup = in_array('--backup', $argv) || !$dryRun; // Always backup unless dry-run

$adminDir = __DIR__ . '/../admin';
$backupDir = __DIR__ . '/../admin_backup_' . date('Y-m-d_H-i-s');

// Files to migrate (excluding debug and auth files which are handled separately)
$filesToMigrate = [
    'dashboard/dashboard.php',
    'courses/manage_courses.php',
    'courses/manage_modules.php',
    'courses/manage_lessons.php',
    'courses/edit_lesson.php',
    'courses/upload_video.php',
    'courses/manual_upload.php',
    'students/registered_students.php',
    'students/grant_course_access.php',
    'assignments/create_assignment.php',
    'assignments/edit_assignment.php',
    'assignments/manage_assignments.php',
    'assignments/view_submissions.php',
    'payments/pending_payments.php',
    'payments/print_pending_payments.php',
    'planning/planning.php',
];

function migrateFile($filePath, $dryRun = false) {
    if (!file_exists($filePath)) {
        echo "⚠️  File not found: $filePath\n";
        return false;
    }
    
    $content = file_get_contents($filePath);
    $original = $content;
    $changes = [];
    
    // 1. Replace manual session check with require_admin_auth
    if (preg_match('/session_start\(\);\s*if\s*\(!\s*isset\(\$_SESSION\[\'admin\'\]\)\)\s*\{[^}]+\}/s', $content)) {
        $content = preg_replace(
            '/session_start\(\);\s*if\s*\(!\s*isset\(\$_SESSION\[\'admin\'\]\)\)\s*\{[^}]+\}/s',
            "require_once __DIR__ . '/../../includes/admin_auth.php';\nrequire_admin_auth();",
            $content
        );
        $changes[] = "✓ Replaced manual session check with require_admin_auth()";
    }
    
    // 2. Add QueryBuilder if database queries exist
    if (preg_match('/\$conn->query\(|->prepare\(/s', $content) && 
        !preg_match('/QueryBuilder/', $content)) {
        // Find the line after db_connect include
        $content = preg_replace(
            "/(include\s+['\"]\.\.\/\.\.\/includes\/db_connect\.php['\"];)/",
            "$1\nrequire_once __DIR__ . '/../../includes/database/QueryBuilder.php';\n\$db = new QueryBuilder(\$conn);",
            $content
        );
        $changes[] = "✓ Added QueryBuilder initialization";
    }
    
    // 3. Add CSRF protection for POST requests
    // Match POST request checks (handles both single and double quotes)
    $hasPostCheck = preg_match('#if\s*\(\$_SERVER\[.*REQUEST_METHOD.*\]\s*===\s*[\'"]POST[\'"]#', $content);
    $hasCsrfProtection = preg_match('/CSRF::requireToken\(\)/', $content);
    
    if ($hasPostCheck && !$hasCsrfProtection) {
        // Match both single and double quote patterns for REQUEST_METHOD
        // Use # delimiter to avoid quote escaping issues
        $content = preg_replace(
            '#if\s*\(\$_SERVER\[[\'"]REQUEST_METHOD[\'"]\]\s*===\s*[\'"]POST[\'"]#',
            "require_once __DIR__ . '/../../includes/csrf.php';\n    CSRF::requireToken();\n    \n    if (\$_SERVER['REQUEST_METHOD'] === 'POST'",
            $content
        );
        $changes[] = "✓ Added CSRF protection";
    }
    
    if ($content !== $original) {
        if (!$dryRun) {
            file_put_contents($filePath, $content);
        }
        return $changes;
    }
    
    return false;
}

echo "🚀 Admin Files Migration Script\n";
echo "================================\n\n";

if ($dryRun) {
    echo "⚠️  DRY RUN MODE - No files will be modified\n\n";
}

if ($backup && !$dryRun) {
    echo "📦 Creating backup...\n";
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    foreach ($filesToMigrate as $file) {
        $source = $adminDir . '/' . $file;
        $dest = $backupDir . '/' . $file;
        
        if (file_exists($source)) {
            $dir = dirname($dest);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            copy($source, $dest);
        }
    }
    echo "✓ Backup created: $backupDir\n\n";
}

echo "📝 Migrating files...\n\n";

$migrated = 0;
$skipped = 0;

foreach ($filesToMigrate as $file) {
    $filePath = $adminDir . '/' . $file;
    echo "Processing: $file\n";
    
    $changes = migrateFile($filePath, $dryRun);
    
    if ($changes) {
        $migrated++;
        foreach ($changes as $change) {
            echo "  $change\n";
        }
    } else {
        $skipped++;
        echo "  ⚠️  No changes needed\n";
    }
    echo "\n";
}

echo "================================\n";
echo "✅ Migration complete!\n";
echo "   Migrated: $migrated files\n";
echo "   Skipped: $skipped files\n";

if ($backup && !$dryRun) {
    echo "   Backup: $backupDir\n";
}

if ($dryRun) {
    echo "\n⚠️  This was a dry run. Run without --dry-run to apply changes.\n";
}

