<?php
/**
 * Application Initialization
 * Include this at the top of all PHP files (optional)
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
require_once __DIR__ . '/config.php';

// Initialize error handling (set to true in production)
require_once __DIR__ . '/error_handler.php';
ErrorHandler::init(false); // Change to true in production

// Load database connection
require_once __DIR__ . '/db_connect.php';

