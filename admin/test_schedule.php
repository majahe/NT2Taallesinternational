<?php
// Simple test script to verify database updates work
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: index.php");
  exit;
}

include '../includes/db_connect.php';

echo "<h1>Test Course Scheduling</h1>";

// First, update the status ENUM to include 'Scheduled'
$alter_status_sql = "ALTER TABLE registrations MODIFY COLUMN status ENUM('New', 'Pending', 'Planned', 'Scheduled', 'Completed', 'Cancelled') DEFAULT 'New'";
$result = $conn->query($alter_status_sql);
if ($result) {
    echo "✅ Status ENUM updated successfully<br>";
} else {
    echo "❌ Status ENUM update failed: " . $conn->error . "<br>";
}

// Test updating a record
if (isset($_GET['test_update'])) {
    $test_id = intval($_GET['test_id']);
    $test_date = '2025-10-25';
    $test_time = '14:00:00';
    $test_instructor = 'Dr. Test Instructor';
    $test_location = 'Test Room';
    $test_notes = 'Test scheduling';
    
    $sql = "UPDATE registrations SET 
            course_date = ?, 
            course_time = ?, 
            instructor = ?, 
            location = ?, 
            planning_notes = ?,
            status = 'Scheduled'
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssssi", $test_date, $test_time, $test_instructor, $test_location, $test_notes, $test_id);
        if ($stmt->execute()) {
            echo "✅ Test update successful! Affected rows: " . $stmt->affected_rows . "<br>";
        } else {
            echo "❌ Test update failed: " . $stmt->error . "<br>";
        }
        $stmt->close();
    } else {
        echo "❌ Prepare failed: " . $conn->error . "<br>";
    }
}

// Show current records
$result = $conn->query("SELECT id, name, status, course_date, course_time, instructor FROM registrations ORDER BY id DESC LIMIT 5");
echo "<h2>Current Records:</h2>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Name</th><th>Status</th><th>Course Date</th><th>Course Time</th><th>Instructor</th><th>Test</th></tr>";

while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
    echo "<td>" . ($row['course_date'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['course_time'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['instructor'] ?? 'NULL') . "</td>";
    echo "<td><a href='?test_update=1&test_id=" . $row['id'] . "'>Test Update</a></td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><a href='planning_fixed.php'>Go to Fixed Planning Page</a>";
?>
