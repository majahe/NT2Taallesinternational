<?php
/**
 * Rate Limiting
 * Prevents abuse by limiting form submissions per IP address
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class RateLimit {
    private static $maxAttempts = 3;
    private static $timeWindow = 3600; // 1 hour in seconds
    
    /**
     * Check if rate limit is exceeded
     * @param string $action Action identifier (e.g., 'registration', 'contact')
     * @return bool True if allowed, false if rate limited
     */
    public static function check($action = 'form_submission') {
        $ip = self::getClientIp();
        $key = "rate_limit_{$action}_{$ip}";
        
        // Initialize rate limit data if not exists
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [
                'count' => 0,
                'reset_time' => time() + self::$timeWindow
            ];
        }
        
        // Reset if time window expired
        if (time() > $_SESSION[$key]['reset_time']) {
            $_SESSION[$key] = [
                'count' => 0,
                'reset_time' => time() + self::$timeWindow
            ];
        }
        
        // Check if limit exceeded
        if ($_SESSION[$key]['count'] >= self::$maxAttempts) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Increment rate limit counter
     * @param string $action Action identifier
     */
    public static function increment($action = 'form_submission') {
        $ip = self::getClientIp();
        $key = "rate_limit_{$action}_{$ip}";
        
        if (!isset($_SESSION[$key])) {
            self::check($action);
        }
        
        $_SESSION[$key]['count']++;
    }
    
    /**
     * Get remaining attempts
     * @param string $action Action identifier
     * @return int Remaining attempts
     */
    public static function getRemainingAttempts($action = 'form_submission') {
        $ip = self::getClientIp();
        $key = "rate_limit_{$action}_{$ip}";
        
        if (!isset($_SESSION[$key])) {
            return self::$maxAttempts;
        }
        
        if (time() > $_SESSION[$key]['reset_time']) {
            return self::$maxAttempts;
        }
        
        return max(0, self::$maxAttempts - $_SESSION[$key]['count']);
    }
    
    /**
     * Get time until reset (in seconds)
     * @param string $action Action identifier
     * @return int Seconds until reset
     */
    public static function getTimeUntilReset($action = 'form_submission') {
        $ip = self::getClientIp();
        $key = "rate_limit_{$action}_{$ip}";
        
        if (!isset($_SESSION[$key])) {
            return 0;
        }
        
        $remaining = $_SESSION[$key]['reset_time'] - time();
        return max(0, $remaining);
    }
    
    /**
     * Get client IP address
     * @return string IP address
     */
    private static function getClientIp() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

