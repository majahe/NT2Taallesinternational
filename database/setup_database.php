<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';

echo "<h2>Database Setup for NT2 Website</h2>";

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✅ Connected to MySQL server<br>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    echo "✅ Database '" . DB_NAME . "' created successfully or already exists<br>";
} else {
    echo "❌ Error creating database: " . $conn->error . "<br>";
}

// Select the database
if (!$conn->select_db(DB_NAME)) {
    die("❌ Error selecting database: " . $conn->error);
}
echo "✅ Database selected<br>";

// Read and execute SQL file
$sql_file = 'database/database_setup.sql';
if (file_exists($sql_file)) {
    $sql_content = file_get_contents($sql_file);
    
    // Split the SQL content by semicolons and execute each statement
    $statements = explode(';', $sql_content);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if ($conn->query($statement) === TRUE) {
                echo "✅ SQL statement executed successfully<br>";
            } else {
                echo "❌ Error executing SQL: " . $conn->error . "<br>";
                echo "Statement: " . $statement . "<br>";
            }
        }
    }
} else {
    echo "❌ SQL file not found: " . $sql_file . "<br>";
}

// Verify tables were created
$result = $conn->query("SHOW TABLES");
if ($result->num_rows > 0) {
    echo "<h3>Tables created:</h3>";
    while($row = $result->fetch_array()) {
        echo "✅ " . $row[0] . "<br>";
    }
} else {
    echo "❌ No tables found<br>";
}

// Check registrations table structure
$result = $conn->query("DESCRIBE registrations");
if ($result->num_rows > 0) {
    echo "<h3>Registrations table structure:</h3>";
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while($row = $result->fetch_array()) {
        echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>" . $row[2] . "</td><td>" . $row[3] . "</td><td>" . $row[4] . "</td></tr>";
    }
    echo "</table>";
}

$conn->close();

echo "<br><h3>Database setup completed!</h3>";
echo "<p><a href='test_registration.php'>Test Registration</a> | <a href='index.php'>Go to Website</a></p>";
?>
