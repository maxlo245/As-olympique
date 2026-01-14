<?php
/**
 * Utility Functions
 *
 * Collection of reusable helper functions for the AS Olympique application.
 * These functions provide common functionality for escaping, CSRF protection,
 * validation, and user interface helpers.
 *
 * @package AsOlympique
 */

/**
 * Escape string for safe HTML output
 *
 * Converts special characters to HTML entities to prevent XSS attacks.
 * This function should be used whenever outputting user-generated content.
 *
 * @param string|null $s String to escape
 * @return string Escaped string
 */
function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 *
 * Creates a cryptographically secure random token and stores it in the session.
 * Uses bin2hex(random_bytes()) to generate an unpredictable token.
 *
 * @return string CSRF token
 */
function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 *
 * Compares the provided token with the one stored in the session.
 * Uses hash_equals() to prevent timing attacks.
 *
 * @param string $token Token to verify
 * @return bool True if token is valid, false otherwise
 */
function verify_csrf_token(string $token): bool
{
    $csrf = $_SESSION['csrf_token'] ?? '';
    $tok = $token ?? '';
    return hash_equals($csrf, $tok);
}

/**
 * Secure filename
 *
 * Sanitizes a filename by removing or replacing dangerous characters.
 * Keeps only alphanumeric characters, dots, hyphens, and underscores.
 *
 * @param string $name Filename to sanitize
 * @return string Sanitized filename
 */
function secure_filename(string $name): string
{
    $name = preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $name);
    return $name;
}

/**
 * Generate success message HTML
 *
 * Creates a styled success alert box for user feedback.
 *
 * @param string $message Success message
 * @return string HTML alert box
 */
function success_message(string $message): string
{
    return sprintf(
        '<div class="alert alert-success">✓ %s</div>',
        e($message)
    );
}

/**
 * Generate error message HTML
 *
 * Creates a styled error alert box for user feedback.
 *
 * @param string $message Error message
 * @return string HTML alert box
 */
function error_message(string $message): string
{
    return sprintf(
        '<div class="alert alert-danger">✗ %s</div>',
        e($message)
    );
}

/**
 * Generate warning message HTML
 *
 * Creates a styled warning alert box for user feedback.
 *
 * @param string $message Warning message
 * @return string HTML alert box
 */
function warning_message(string $message): string
{
    return sprintf(
        '<div class="alert alert-warning">⚠ %s</div>',
        e($message)
    );
}

/**
 * Safe redirect
 *
 * Performs a safe HTTP redirect with proper headers.
 * Validates URL to prevent open redirect vulnerabilities.
 *
 * @param string $url URL to redirect to
 * @param int $statusCode HTTP status code (default: 302)
 * @return void
 */
function redirect(string $url, int $statusCode = 302): void
{
    // Validate URL to prevent open redirect
    if (!filter_var($url, FILTER_VALIDATE_URL) && !preg_match('/^\/[^\/]/', $url)) {
        $url = '/';
    }

    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Check if user is logged in
 *
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in(): bool
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Require authentication
 *
 * Redirects to login page if user is not authenticated.
 *
 * @param string $loginUrl Login page URL (default: /src/secure/connexion_secure.php)
 * @return void
 */
function require_auth(string $loginUrl = '/src/secure/connexion_secure.php'): void
{
    if (!is_logged_in()) {
        redirect($loginUrl);
    }
}

/**
 * Get current user role
 *
 * @return string|null User role or null if not logged in
 */
function get_user_role(): ?string
{
    return $_SESSION['user_role'] ?? null;
}

/**
 * Get current user ID
 *
 * @return int|null User ID or null if not logged in
 */
function get_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user name
 *
 * @return string|null User name or null if not logged in
 */
function get_user_name(): ?string
{
    return $_SESSION['user_nom'] ?? null;
}

/**
 * Log security event
 *
 * Logs security-related events with context information.
 * This is a simplified version for educational purposes.
 *
 * @param string $event Event type (e.g., 'failed_login', 'csrf_attempt')
 * @param string $details Event details
 * @return void
 */
function log_security_event(string $event, string $details): void
{
    $logEntry = sprintf(
        "[%s] SECURITY: %s - %s (IP: %s, User-Agent: %s)\n",
        date('Y-m-d H:i:s'),
        $event,
        $details,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    );

    $logFile = __DIR__ . '/../logs/security.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    error_log($logEntry, 3, $logFile);
}

/**
 * Format file size
 *
 * Converts bytes to human-readable format.
 *
 * @param int $bytes Size in bytes
 * @param int $precision Decimal precision (default: 2)
 * @return string Formatted size (e.g., "1.5 MB")
 */
function format_bytes(int $bytes, int $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Truncate string with ellipsis
 *
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $ellipsis Ellipsis character (default: '...')
 * @return string Truncated text
 */
function truncate(string $text, int $length, string $ellipsis = '...'): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length - mb_strlen($ellipsis)) . $ellipsis;
}

/**
 * Generate random string
 *
 * Creates a cryptographically secure random string.
 *
 * @param int $length String length
 * @return string Random string
 */
function random_string(int $length = 32): string
{
    return bin2hex(random_bytes($length / 2));
}
