<?php
/**
 * Application Initialization
 *
 * Initializes the application environment, session, and database connection.
 * This file should be included at the beginning of every PHP script.
 *
 * @package AsOlympique
 */

// Load configuration
$config = require __DIR__ . '/config.php';

// Start session with secure parameters
// Compatible with PHP 7.3+ and earlier versions
$secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

if (PHP_VERSION_ID >= 70300) {
    // PHP 7.3+ syntax
    session_set_cookie_params([
        'lifetime' => $config->session->lifetime,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => $config->session->httponly,
        'samesite' => 'Strict',
    ]);
} else {
    // PHP < 7.3 fallback
    session_set_cookie_params(
        $config->session->lifetime,
        '/',
        '',
        $secure,
        $config->session->httponly
    );
}

// Set custom session name for security
session_name('AS_OLYMPIQUE_SESSION');

// Start session
session_start();

// Regenerate session ID on first visit (anti-fixation)
if (!isset($_SESSION['_initiated'])) {
    session_regenerate_id(true);
    $_SESSION['_initiated'] = true;
}

// Initialize PDO connection with error handling
try {
    $dsn = "mysql:host={$config->db->host};dbname={$config->db->dbname};charset={$config->db->charset}";

    // PDO options for security and performance
    $options = [
        // Throw exceptions on errors instead of silent failures
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        // Return associative arrays by default
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        // Use real prepared statements (more secure)
        // Prevents emulation which could be vulnerable to certain SQL injection
        PDO::ATTR_EMULATE_PREPARES => false,

        // Set connection timeout
        PDO::ATTR_TIMEOUT => 5,

        // Disable persistent connections for better resource management
        // Note: Persistent connections can be enabled in production if needed
        PDO::ATTR_PERSISTENT => false,
    ];

    // Create PDO instance
    $pdo = new PDO($dsn, $config->db->user, $config->db->pass, $options);

    // Optional: Set MySQL-specific options for better performance
    // $pdo->exec("SET NAMES {$config->db->charset}");
    // $pdo->exec("SET CHARACTER SET {$config->db->charset}");

} catch (PDOException $e) {
    // Log error without exposing sensitive information
    error_log('Database connection error: ' . $e->getMessage());

    // Display user-friendly error message
    http_response_code(500);

    if ($config->app->debug) {
        // Development: Show detailed error
        echo '<h1>Database Connection Error</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    } else {
        // Production: Generic error message
        echo '<h1>Service Unavailable</h1>';
        echo '<p>Unable to connect to the database. Please try again later.</p>';
    }

    exit;
} catch (Exception $e) {
    // Catch any other exceptions during initialization
    error_log('Initialization error: ' . $e->getMessage());
    http_response_code(500);
    echo '<h1>Application Error</h1>';
    echo '<p>An error occurred during initialization.</p>';
    exit;
}

// Optional: Initialize error handler if in development mode
if ($config->app->debug) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}
