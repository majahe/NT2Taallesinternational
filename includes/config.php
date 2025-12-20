<?php
// Load environment variables
require_once __DIR__ . '/env_loader.php';

// Database Configuration (from environment variables with fallback)
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'nt2_db'));

// SMTP Configuration (from environment variables with fallback)
define('SMTP_HOST', env('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', env('SMTP_PORT', 587));
define('SMTP_USERNAME', env('SMTP_USERNAME', ''));
define('SMTP_PASSWORD', env('SMTP_PASSWORD', ''));
define('SMTP_FROM_EMAIL', env('SMTP_FROM_EMAIL', ''));
define('SMTP_FROM_NAME', env('SMTP_FROM_NAME', 'NT2 Taalles International'));

// Admin Configuration
define('ADMIN_EMAIL', env('ADMIN_EMAIL', 'info@nt2taallesinternational.com'));

// Website Configuration
define('WEBSITE_URL', env('WEBSITE_URL', 'https://nt2taallesinternational.com'));

// SSL Settings (from environment variables, default false for local development)
define('SMTP_SSL_VERIFY', env('SMTP_SSL_VERIFY', 'false') === 'true' || env('SMTP_SSL_VERIFY', false) === true);
define('SMTP_DEBUG', env('SMTP_DEBUG', 'false') === 'true' || env('SMTP_DEBUG', false) === true);
?>
