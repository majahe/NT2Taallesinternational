<?php
/**
 * CSRF Protection Class
 * Prevents Cross-Site Request Forgery attacks
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class CSRF {
    /**
     * Generate CSRF token
     */
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateToken($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Get token field for forms
     */
    public static function getTokenField() {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::generateToken()) . '">';
    }
    
    /**
     * Require valid CSRF token (throws exception if invalid)
     */
    public static function requireToken() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
            $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
            
            if (!self::validateToken($token)) {
                http_response_code(403);
                die('Invalid CSRF token. Please refresh the page and try again.');
            }
        }
    }
    
    /**
     * Get token for AJAX requests
     */
    public static function getTokenForAjax() {
        return self::generateToken();
    }
}

