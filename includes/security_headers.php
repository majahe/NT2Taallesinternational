<?php
/**
 * Security Headers
 * Sets important security headers to protect against common web vulnerabilities
 */

// Prevent clickjacking - don't allow page to be embedded in frames
header('X-Frame-Options: DENY');

// Prevent MIME type sniffing
header('X-Content-Type-Options: nosniff');

// Content Security Policy - restrict resource loading
// Adjust as needed for your site's requirements
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self';");

// XSS Protection (legacy but still useful)
header('X-XSS-Protection: 1; mode=block');

// Referrer Policy - control referrer information
header('Referrer-Policy: strict-origin-when-cross-origin');

// Permissions Policy (formerly Feature Policy)
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

// Strict Transport Security (only set if HTTPS is available)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Remove server signature
header_remove('X-Powered-By');

