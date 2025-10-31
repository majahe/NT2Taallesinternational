<?php
/**
 * Admin Authentication Middleware
 * Centralized authentication and authorization for admin portal
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require admin authentication - redirects if not logged in
 */
function require_admin_auth() {
    if (!isset($_SESSION['admin'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: /admin/auth/index.php");
        exit;
    }
    
    // Check session timeout (30 minutes)
    if (isset($_SESSION['admin_last_activity']) && 
        (time() - $_SESSION['admin_last_activity'] > 1800)) {
        session_destroy();
        header("Location: /admin/auth/index.php?expired=1");
        exit;
    }
    
    // Update last activity time
    $_SESSION['admin_last_activity'] = time();
}

/**
 * Check if admin is logged in
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin']);
}

/**
 * Get current admin username
 */
function get_admin_username() {
    return $_SESSION['admin'] ?? null;
}

/**
 * Require admin authentication with specific permission
 */
function require_admin_permission($permission) {
    require_admin_auth();
    
    // Future: Add permission checking logic here
    // For now, just ensure admin is logged in
}

