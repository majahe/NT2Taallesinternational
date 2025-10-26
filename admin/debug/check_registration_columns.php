<?php
session_start();
if (!isset($_SESSION['admin'])) {
    die("Admin access required");
}

require_once __DIR__ . '/../../includes/db_connect.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Check Registration Columns</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        table { border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Check and Fix Registration Table Columns</h1>
    
    <h2>Current Columns:</h2>
    <?php
    $columns_result = $conn->query("SHOW COLUMNS FROM registrations");
    echo "<table>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $columns_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    ?>
    
    <h2>Adding Missing Columns:</h2>
    
    <?php
    $columns_to_add = [
        'password' => 'VARCHAR(255) NULL',
        'password_set' => 'BOOLEAN DEFAULT FALSE',
        'password_token' => 'VARCHAR(64) NULL',
        'password_token_expires' => 'DATETIME NULL',
        'course_access_granted' => 'BOOLEAN DEFAULT FALSE'
    ];
    
    foreach ($columns_to_add as $column => $definition) {
        $check = $conn->query("SHOW COLUMNS FROM registrations LIKE '$column'");
        if ($check->num_rows == 0) {
            $sql = "ALTER TABLE registrations ADD COLUMN $column $definition";
            if ($conn->query($sql)) {
                echo "<p class='success'>✓ Added column: $column</p>";
            } else {
                echo "<p class='error'>✗ Error adding column $column: " . $conn->error . "</p>";
            }
        } else {
            echo "<p class='info'>○ Column $column already exists</p>";
        }
    }
    ?>
    
    <h2>Verification:</h2>
    <?php
    $columns_result = $conn->query("SHOW COLUMNS FROM registrations");
    $existing_columns = [];
    while ($row = $columns_result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }
    
    $required_columns = ['password', 'password_set', 'password_token', 'password_token_expires', 'course_access_granted'];
    
    echo "<ul>";
    foreach ($required_columns as $col) {
        if (in_array($col, $existing_columns)) {
            echo "<li class='success'>✓ $col exists</li>";
        } else {
            echo "<li class='error'>✗ $col missing</li>";
        }
    }
    echo "</ul>";
    ?>
    
    <p><a href="../dashboard/dashboard.php">← Back to Dashboard</a></p>
</body>
</html>
