<?php
// Debug version of planning.php to identify the 500 error
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: index.php");
  exit;
}

echo "<h1>Debug: Course Planning</h1>";

try {
    include '../includes/db_connect.php';
    echo "✅ Database connection successful<br>";
    
    // Check if the table exists
    $result = $conn->query("SHOW TABLES LIKE 'registrations'");
    if ($result->num_rows > 0) {
        echo "✅ Registrations table exists<br>";
    } else {
        echo "❌ Registrations table does not exist<br>";
        exit;
    }
    
    // Check table structure
    $result = $conn->query("DESCRIBE registrations");
    echo "<h3>Table structure:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $result->fetch_array()) {
        echo "<tr>";
        for($i = 0; $i < 6; $i++) {
            echo "<td>" . ($row[$i] ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // Check if new columns exist
    $columns_to_check = ['course_date', 'course_time', 'instructor', 'location', 'planning_notes'];
    foreach($columns_to_check as $column) {
        $result = $conn->query("SHOW COLUMNS FROM registrations LIKE '$column'");
        if ($result->num_rows > 0) {
            echo "✅ Column '$column' exists<br>";
        } else {
            echo "❌ Column '$column' does not exist<br>";
        }
    }
    
    // Try to get planned registrations
    echo "<h3>Testing planned registrations query:</h3>";
    $planned_registrations = $conn->query("SELECT * FROM registrations WHERE status = 'Planned' ORDER BY created_at ASC");
    if ($planned_registrations) {
        echo "✅ Planned registrations query successful<br>";
        echo "Number of planned registrations: " . $planned_registrations->num_rows . "<br>";
    } else {
        echo "❌ Planned registrations query failed: " . $conn->error . "<br>";
    }
    
    // Try to get scheduled courses
    echo "<h3>Testing scheduled courses query:</h3>";
    $scheduled_courses = $conn->query("SELECT * FROM registrations WHERE status = 'Scheduled' AND course_date IS NOT NULL ORDER BY course_date, course_time ASC");
    if ($scheduled_courses) {
        echo "✅ Scheduled courses query successful<br>";
        echo "Number of scheduled courses: " . $scheduled_courses->num_rows . "<br>";
    } else {
        echo "❌ Scheduled courses query failed: " . $conn->error . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
