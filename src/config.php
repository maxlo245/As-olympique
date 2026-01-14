<?php
/**
 * Application Configuration
 *
 * Main configuration file for AS Olympique.
 * Uses environment variables with fallback to defaults.
 *
 * @package AsOlympique
 */

// Load environment variables
require_once __DIR__ . '/config/env.php';

// Return configuration object
return (object) [
    // Database configuration
    'db' => (object) [
        'host' => env('DB_HOST', 'localhost'),
        'dbname' => env('DB_NAME', 'as_olympique_db'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', 'root'),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
    ],

    // Application configuration
    'app' => (object) [
        'env' => env('APP_ENV', 'development'),
        'debug' => env('APP_DEBUG', true),
        'url' => env('APP_URL', 'http://localhost/as_olympique'),
    ],

    // Upload configuration
    // Prefer storing uploads outside webroot for security
    'upload_dir' => env('UPLOAD_DIR', __DIR__ . '/../uploads/'),
    'max_upload_size' => env('UPLOAD_MAX_SIZE', 2 * 1024 * 1024), // 2MB default

    // Session configuration
    'session' => (object) [
        'lifetime' => env('SESSION_LIFETIME', 3600), // 1 hour
        'secure' => env('SESSION_SECURE', false),
        'httponly' => env('SESSION_HTTPONLY', true),
    ],

    // Security configuration
    'security' => (object) [
        'csrf_token_name' => env('CSRF_TOKEN_NAME', 'csrf_token'),
        'password_min_length' => env('PASSWORD_MIN_LENGTH', 8),
    ],

    // Rate limiting (educational purposes)
    'rate_limit' => (object) [
        'enabled' => env('RATE_LIMIT_ENABLED', false),
        'max_attempts' => env('RATE_LIMIT_MAX_ATTEMPTS', 5),
        'window' => env('RATE_LIMIT_WINDOW', 300), // 5 minutes
    ],

    // Logging configuration
    'logging' => (object) [
        'level' => env('LOG_LEVEL', 'debug'),
        'path' => env('LOG_PATH', __DIR__ . '/../logs/app.log'),
        'security_events' => env('LOG_SECURITY_EVENTS', true),
    ],
];
