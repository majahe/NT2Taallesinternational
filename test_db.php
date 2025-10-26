<?php
require_once 'includes/db_connect.php';

echo "Database connected successfully\n";

// Check if tables exist
$tables = ['assignments', 'assignment_questions', 'student_assignments', 'student_enrollments'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "Table '$table' exists\n";
    } else {
        echo "Table '$table' MISSING\n";
    }
}

// Check if there are any assignments
$result = $conn->query("SELECT COUNT(*) as count FROM assignments");
$row = $result->fetch_assoc();
echo "Total assignments: " . $row['count'] . "\n";

// Check if there are any assignment questions
$result = $conn->query("SELECT COUNT(*) as count FROM assignment_questions");
$row = $result->fetch_assoc();
echo "Total assignment questions: " . $row['count'] . "\n";

$conn->close();
?>
