<?php

/**
 * Helper functions for backward compatibility and convenience
 * 
 * This file provides global helper functions that wrap the core classes
 * for easier use throughout the application.
 * 
 * @package AsOlympique
 * @author Florence PEYRATAUD
 */

use AsOlympique\Core\Config;
use AsOlympique\Core\Security;

if (!function_exists('config')) {
    /**
     * Get configuration value
     * 
     * @param string|null $key Configuration key in dot notation
     * @param mixed $default Default value
     * @return mixed
     */
    function config(?string $key = null, $default = null)
    {
        $config = Config::getInstance();
        
        if ($key === null) {
            return $config;
        }
        
        return $config->get($key, $default);
    }
}

if (!function_exists('security')) {
    /**
     * Get Security instance
     * 
     * @return Security
     */
    function security(): Security
    {
        static $security = null;
        
        if ($security === null) {
            $security = new Security(Config::getInstance());
        }
        
        return $security;
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML entities (XSS prevention)
     * 
     * @param mixed $value Value to escape
     * @return string Escaped value
     */
    function e($value): string
    {
        return security()->escape($value);
    }
}

if (!function_exists('generate_csrf_token')) {
    /**
     * Generate CSRF token
     * 
     * @return string CSRF token
     */
    function generate_csrf_token(): string
    {
        return security()->generateCsrfToken();
    }
}

if (!function_exists('verify_csrf_token')) {
    /**
     * Verify CSRF token
     * 
     * @param string $token Token to verify
     * @return bool True if valid
     */
    function verify_csrf_token(string $token): bool
    {
        return security()->verifyCsrfToken($token);
    }
}

if (!function_exists('secure_filename')) {
    /**
     * Sanitize filename
     * 
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    function secure_filename(string $filename): string
    {
        return security()->sanitizeFilename($filename);
    }
}

if (!function_exists('success_message')) {
    /**
     * Generate success message HTML
     * 
     * @param string $message Message text
     * @return string HTML for success message
     */
    function success_message(string $message): string
    {
        return '<div class="alert alert-success">' . e($message) . '</div>';
    }
}

if (!function_exists('error_message')) {
    /**
     * Generate error message HTML
     * 
     * @param string $message Message text
     * @return string HTML for error message
     */
    function error_message(string $message): string
    {
        return '<div class="alert alert-danger">' . e($message) . '</div>';
    }
}

if (!function_exists('warning_message')) {
    /**
     * Generate warning message HTML
     * 
     * @param string $message Message text
     * @return string HTML for warning message
     */
    function warning_message(string $message): string
    {
        return '<div class="alert alert-warning">' . e($message) . '</div>';
    }
}

if (!function_exists('get_client_ip')) {
    /**
     * Get client IP address
     * 
     * Handles proxy headers securely.
     * 
     * @return string Client IP address
     */
    function get_client_ip(): string
    {
        // Check for proxy headers (use with caution in production)
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Handle multiple IPs (take first one)
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirect to URL
     * 
     * @param string $url URL to redirect to
     * @param int $statusCode HTTP status code
     * @return void
     */
    function redirect(string $url, int $statusCode = 302): void
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
}

if (!function_exists('json_response')) {
    /**
     * Send JSON response
     * 
     * @param mixed $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     * @return void
     */
    function json_response($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('is_post')) {
    /**
     * Check if request method is POST
     * 
     * @return bool
     */
    function is_post(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}

if (!function_exists('is_get')) {
    /**
     * Check if request method is GET
     * 
     * @return bool
     */
    function is_get(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value (from previous form submission)
     * 
     * @param string $key Input name
     * @param mixed $default Default value
     * @return mixed
     */
    function old(string $key, $default = '')
    {
        return $_SESSION['old_input'][$key] ?? $default;
    }
}

if (!function_exists('flash_old_input')) {
    /**
     * Flash current input to session for next request
     * 
     * @return void
     */
    function flash_old_input(): void
    {
        $_SESSION['old_input'] = $_POST;
    }
}

if (!function_exists('clear_old_input')) {
    /**
     * Clear old input from session
     * 
     * @return void
     */
    function clear_old_input(): void
    {
        unset($_SESSION['old_input']);
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die (for debugging)
     * 
     * @param mixed ...$vars Variables to dump
     * @return void
     */
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
        exit(1);
    }
}

if (!function_exists('logger')) {
    /**
     * Log message to file
     * 
     * @param string $message Message to log
     * @param string $level Log level (debug, info, warning, error)
     * @return void
     */
    function logger(string $message, string $level = 'info'): void
    {
        $logPath = config('logging.path', __DIR__ . '/../logs/');
        $logFile = $logPath . 'app.log';
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        error_log($logMessage, 3, $logFile);
    }
}
