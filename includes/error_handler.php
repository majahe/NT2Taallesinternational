<?php
/**
 * Error Handler
 * Centralized error and exception handling
 */

class ErrorHandler {
    private static $isProduction = false;
    
    /**
     * Initialize error handling
     */
    public static function init($isProduction = false) {
        self::$isProduction = $isProduction;
        
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        
        if ($isProduction) {
            error_reporting(E_ALL);
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }
    }
    
    /**
     * Handle exceptions
     */
    public static function handleException($exception) {
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTraceAsString();
        
        error_log("Exception: $message in $file on line $line");
        error_log("Stack trace: $trace");
        
        if (php_sapi_name() !== 'cli') {
            http_response_code(500);
            
            if (self::$isProduction) {
                include __DIR__ . '/errors/500.php';
            } else {
                echo "<h1>Exception</h1>";
                echo "<p><strong>Message:</strong> " . htmlspecialchars($message) . "</p>";
                echo "<p><strong>File:</strong> $file</p>";
                echo "<p><strong>Line:</strong> $line</p>";
                echo "<pre>" . htmlspecialchars($trace) . "</pre>";
            }
        }
        
        exit(1);
    }
    
    /**
     * Handle errors
     */
    public static function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        error_log("Error [$errno]: $errstr in $errfile on line $errline");
        
        if (php_sapi_name() !== 'cli') {
            if (self::$isProduction) {
                // Don't display errors in production
                return true;
            } else {
                echo "<div style='background: #fee; border: 1px solid #f00; padding: 10px; margin: 10px;'>";
                echo "<strong>Error [$errno]:</strong> " . htmlspecialchars($errstr) . "<br>";
                echo "<strong>File:</strong> $errfile<br>";
                echo "<strong>Line:</strong> $errline";
                echo "</div>";
            }
        }
        
        return true;
    }
}

