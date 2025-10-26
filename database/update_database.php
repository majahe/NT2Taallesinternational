<?php
include_once '../includes/config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Update for Registered Students Feature</h2>";

// Step 1: Update the status ENUM to include 'Registered'
echo "<h3>Updating status ENUM...</h3>";
$alter_status_sql = "ALTER TABLE registrations MODIFY COLUMN status ENUM('New', 'Pending', 'Planned', 'Scheduled', 'Registered', 'Completed', 'Cancelled') DEFAULT 'New'";
if ($conn->query($alter_status_sql) === TRUE) {
    echo "✅ Status ENUM updated to include 'Registered'<br>";
} else {
    echo "ℹ️ Status ENUM already updated or already contains 'Registered': " . $conn->error . "<br>";
}

// Step 2: Add student management columns if they don't exist
echo "<h3>Checking for student management columns...</h3>";

$columns_to_add = array(
    'start_date' => "DATE NULL",
    'end_date' => "DATE NULL",
    'payment_status' => "VARCHAR(50) DEFAULT 'Pending'",
    'amount_paid' => "DECIMAL(10,2) DEFAULT 0",
    'total_amount' => "DECIMAL(10,2) DEFAULT 0",
    'phone' => "VARCHAR(20) NULL",
    'address' => "TEXT NULL",
    'emergency_contact' => "VARCHAR(100) NULL",
    'notes' => "TEXT NULL",
    'total_lessons' => "INT DEFAULT 0",
    'price_per_lesson' => "DECIMAL(10,2) DEFAULT 0"
);

foreach ($columns_to_add as $column => $type) {
    $check = $conn->query("SHOW COLUMNS FROM registrations LIKE '$column'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE registrations ADD COLUMN $column $type";
        if ($conn->query($sql) === TRUE) {
            echo "✅ Column '$column' added<br>";
        } else {
            echo "❌ Error adding column '$column': " . $conn->error . "<br>";
        }
    } else {
        echo "ℹ️ Column '$column' already exists<br>";
    }
}

echo "<h3>Database update completed!</h3>";
echo "<p><a href='../admin/dashboard/dashboard.php'>Go to Dashboard</a></p>";

$conn->close();
?>
