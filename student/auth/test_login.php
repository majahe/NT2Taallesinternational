<?php
session_start();
require_once __DIR__ . '/../../includes/db_connect.php';

echo "<h1>Login Test</h1>";
echo "<pre>";
echo "Student ID: " . (isset($_SESSION['student_id']) ? $_SESSION['student_id'] : 'NOT SET') . "\n";
echo "Student Email: " . (isset($_SESSION['student_email']) ? $_SESSION['student_email'] : 'NOT SET') . "\n";
echo "Student Name: " . (isset($_SESSION['student_name']) ? $_SESSION['student_name'] : 'NOT SET') . "\n";
echo "</pre>";

echo "<p><a href='login.php'>Go to Login</a></p>";
echo "<p><a href='../dashboard/dashboard.php'>Go to Dashboard</a></p>";
?>
