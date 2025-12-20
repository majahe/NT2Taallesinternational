<?php
require_once __DIR__ . '/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    // Log error details but don't expose them to users
    error_log("Database connection failed: " . $conn->connect_error);
    error_log("Connection attempt to: " . DB_HOST . " database: " . DB_NAME);
    
    // Show generic error message to users
    http_response_code(500);
    die("Database connection error. Please contact the administrator if this problem persists.");
}
?>