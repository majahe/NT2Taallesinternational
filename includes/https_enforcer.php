<?php
/**
 * HTTPS Enforcer
 * Forces HTTPS connections in production environment
 */

// Only enforce HTTPS in production (not on localhost)
function enforceHttps() {
    $isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']);
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               $_SERVER['SERVER_PORT'] == 443 ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    
    // Don't enforce on localhost
    if ($isLocalhost) {
        return;
    }
    
    // Redirect to HTTPS if not already using it
    if (!$isHttps) {
        $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: ' . $redirectUrl, true, 301);
        exit;
    }
}

// Uncomment the line below to enable HTTPS enforcement in production
// enforceHttps();

