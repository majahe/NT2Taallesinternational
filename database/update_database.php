<?php
// Database update script for course planning features
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/config.php';

echo "<h2>Database Update for Course Planning</h2>";

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✅ Connected to MySQL server<br>";

// Select the database
if (!$conn->select_db(DB_NAME)) {
    die("❌ Error selecting database: " . $conn->error);
}
echo "✅ Database selected<br>";

// Check if columns already exist
$result = $conn->query("SHOW COLUMNS FROM registrations LIKE 'course_date'");
if ($result->num_rows > 0) {
    echo "⚠️ Course planning columns already exist<br>";
} else {
    // Add course scheduling fields
    $sql = "ALTER TABLE registrations 
            ADD COLUMN course_date DATE NULL,
            ADD COLUMN course_time TIME NULL,
            ADD COLUMN instructor VARCHAR(100) NULL,
            ADD COLUMN location VARCHAR(100) NULL,
            ADD COLUMN planning_notes TEXT NULL";
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Course planning columns added successfully<br>";
    } else {
        echo "❌ Error adding columns: " . $conn->error . "<br>";
    }
}

// Verify the new structure
$result = $conn->query("DESCRIBE registrations");
if ($result->num_rows > 0) {
    echo "<h3>Updated registrations table structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $result->fetch_array()) {
        echo "<tr>";
        echo "<td>" . $row[0] . "</td>";
        echo "<td>" . $row[1] . "</td>";
        echo "<td>" . $row[2] . "</td>";
        echo "<td>" . $row[3] . "</td>";
        echo "<td>" . $row[4] . "</td>";
        echo "<td>" . $row[5] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<br><strong>✅ Database update completed!</strong><br>";
echo "<a href='../admin/planning.php'>Go to Course Planning</a>";

$conn->close();
?>
