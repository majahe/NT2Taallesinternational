<?php
/**
 * Update Database with LMS Tables
 * Run this file once to create all necessary tables
 */

require_once __DIR__ . '/../includes/db_connect.php';

// Read SQL file
$sql = file_get_contents(__DIR__ . '/lms_schema.sql');

// Split into individual statements
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        return !empty($stmt) && !preg_match('/^(CREATE|ALTER|--)/i', $stmt);
    }
);

// Execute each statement
foreach ($statements as $statement) {
    if (empty(trim($statement))) continue;
    
    if ($conn->query($statement)) {
        echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
    } else {
        echo "✗ Error: " . $conn->error . "\n";
        echo "Statement: " . substr($statement, 0, 100) . "...\n";
    }
}

echo "\nDatabase update complete!\n";

// Check if tables exist
$tables = ['courses', 'course_modules', 'lessons', 'assignments', 'assignment_questions', 'student_enrollments', 'student_progress', 'student_assignments'];
echo "\nChecking tables:\n";
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✓ Table '$table' exists\n";
    } else {
        echo "✗ Table '$table' missing\n";
    }
}

