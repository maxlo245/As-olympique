<?php

declare(strict_types=1);

namespace AsOlympique\Core;

use Exception;

/**
 * Security utilities class
 * 
 * Provides comprehensive security functions:
 * - CSRF token generation and validation
 * - Input sanitization and validation
 * - Rate limiting
 * - Session management
 * - Password hashing and verification
 * - Security headers
 * 
 * @package AsOlympique\Core
 * @author Florence PEYRATAUD
 */
class Security
{
    /**
     * Configuration instance
     * 
     * @var Config
     */
    private Config $config;

    /**
     * Constructor
     * 
     * @param Config $config Configuration instance
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Generate CSRF token with expiration
     * 
     * Creates a secure CSRF token and stores it in the session with timestamp
     * for expiration checking.
     * 
     * @param bool $regenerate Force regeneration of token
     * @return string The CSRF token
     */
    public function generateCsrfToken(bool $regenerate = false): string
    {
        if (!isset($_SESSION)) {
            throw new Exception('Session must be started before generating CSRF token');
        }

        $lifetime = $this->config->get('security.csrf_token_lifetime', 3600);
        $now = time();

        // Check if token exists and is still valid
        if (!$regenerate && 
            !empty($_SESSION['csrf_token']) && 
            !empty($_SESSION['csrf_token_time']) &&
            ($now - $_SESSION['csrf_token_time']) < $lifetime) {
            return $_SESSION['csrf_token'];
        }

        // Generate new token
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = $now;

        return $token;
    }

    /**
     * Verify CSRF token
     * 
     * Validates the CSRF token and checks if it's not expired.
     * Uses timing-safe comparison to prevent timing attacks.
     * 
     * @param string $token Token to verify
     * @return bool True if token is valid
     */
    public function verifyCsrfToken(string $token): bool
    {
        if (!isset($_SESSION)) {
            return false;
        }

        // Check if token exists in session
        if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time'])) {
            return false;
        }

        // Check token expiration
        $lifetime = $this->config->get('security.csrf_token_lifetime', 3600);
        $now = time();
        
        if (($now - $_SESSION['csrf_token_time']) >= $lifetime) {
            // Token expired
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            return false;
        }

        // Timing-safe comparison
        $sessionToken = $_SESSION['csrf_token'];
        return hash_equals($sessionToken, $token);
    }

    /**
     * Sanitize string output for HTML
     * 
     * Prevents XSS by encoding special characters.
     * 
     * @param mixed $value Value to sanitize
     * @return string Sanitized value
     */
    public function escape($value): string
    {
        if ($value === null) {
            return '';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitize filename
     * 
     * Removes dangerous characters from filenames and prevents directory traversal.
     * 
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    public function sanitizeFilename(string $filename): string
    {
        // Remove directory traversal attempts
        $filename = basename($filename);

        // Remove any character that is not alphanumeric, dot, dash, or underscore
        $filename = preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $filename);

        // Prevent hidden files
        $filename = ltrim($filename, '.');

        // Limit length
        if (strlen($filename) > 255) {
            $filename = substr($filename, 0, 255);
        }

        return $filename;
    }

    /**
     * Generate secure random filename
     * 
     * Creates a unique filename while preserving the extension.
     * 
     * @param string $originalFilename Original filename
     * @return string Secure random filename
     */
    public function generateSecureFilename(string $originalFilename): string
    {
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        $randomName = bin2hex(random_bytes(16));
        
        if (!empty($extension)) {
            return $randomName . '.' . $extension;
        }

        return $randomName;
    }

    /**
     * Validate file upload
     * 
     * Checks file size, MIME type, and extension against whitelist.
     * 
     * @param array<string, mixed> $file $_FILES array element
     * @param array<int, string> $allowedExtensions Allowed file extensions
     * @param array<int, string> $allowedMimeTypes Allowed MIME types
     * @return array{success: bool, error?: string, mime_type?: string}
     */
    public function validateFileUpload(
        array $file, 
        array $allowedExtensions, 
        array $allowedMimeTypes
    ): array {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => $this->getUploadErrorMessage($file['error'])
            ];
        }

        // Check file size
        $maxSize = $this->config->get('upload.max_size', 2097152);
        if ($file['size'] > $maxSize) {
            return [
                'success' => false,
                'error' => 'File size exceeds maximum allowed size'
            ];
        }

        // Check extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            return [
                'success' => false,
                'error' => 'File extension not allowed'
            ];
        }

        // Check MIME type using finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            return [
                'success' => false,
                'error' => 'File type not allowed'
            ];
        }

        return [
            'success' => true,
            'mime_type' => $mimeType
        ];
    }

    /**
     * Get upload error message
     * 
     * @param int $errorCode PHP upload error code
     * @return string Error message
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary upload directory',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
        ];

        return $errors[$errorCode] ?? 'Unknown upload error';
    }

    /**
     * Check rate limit for login attempts
     * 
     * Prevents brute force attacks by limiting login attempts.
     * 
     * @param string $identifier Login username or IP address
     * @param \PDO $pdo Database connection
     * @return array{allowed: bool, remaining?: int, reset_time?: int}
     */
    public function checkRateLimit(string $identifier, \PDO $pdo): array
    {
        $maxAttempts = $this->config->get('rate_limit.login_attempts', 5);
        $window = $this->config->get('rate_limit.login_window', 300); // 5 minutes

        $cutoffTime = date('Y-m-d H:i:s', time() - $window);

        // Count recent attempts
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) as count 
             FROM login_attempts 
             WHERE login = :identifier 
             AND attempt_time > :cutoff_time 
             AND success = 0"
        );
        $stmt->execute([
            'identifier' => $identifier,
            'cutoff_time' => $cutoffTime
        ]);
        $result = $stmt->fetch();
        $attempts = (int) $result['count'];

        if ($attempts >= $maxAttempts) {
            // Get time of first attempt in window
            $stmt = $pdo->prepare(
                "SELECT attempt_time 
                 FROM login_attempts 
                 WHERE login = :identifier 
                 AND success = 0 
                 ORDER BY attempt_time ASC 
                 LIMIT 1"
            );
            $stmt->execute(['identifier' => $identifier]);
            $firstAttempt = $stmt->fetch();
            
            $resetTime = strtotime($firstAttempt['attempt_time']) + $window;

            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_time' => $resetTime
            ];
        }

        return [
            'allowed' => true,
            'remaining' => $maxAttempts - $attempts
        ];
    }

    /**
     * Log login attempt
     * 
     * @param string $login Login username
     * @param string $ipAddress IP address
     * @param bool $success Whether login was successful
     * @param \PDO $pdo Database connection
     * @return void
     */
    public function logLoginAttempt(string $login, string $ipAddress, bool $success, \PDO $pdo): void
    {
        $stmt = $pdo->prepare(
            "INSERT INTO login_attempts (login, ip_address, success) 
             VALUES (:login, :ip_address, :success)"
        );
        $stmt->execute([
            'login' => $login,
            'ip_address' => $ipAddress,
            'success' => $success ? 1 : 0
        ]);
    }

    /**
     * Configure secure session
     * 
     * Sets secure session configuration to prevent session hijacking.
     * 
     * @return void
     */
    public function configureSecureSession(): void
    {
        $sessionConfig = $this->config->get('session');

        // Set session cookie parameters
        $secure = $sessionConfig['secure'] ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $httponly = $sessionConfig['httponly'] ?? true;
        $lifetime = $sessionConfig['lifetime'] ?? 0;

        // Use SameSite attribute for CSRF protection
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => 'Strict'
            ]);
        } else {
            session_set_cookie_params($lifetime, '/', '', $secure, $httponly);
        }

        // Set session name
        $sessionName = $sessionConfig['name'] ?? 'AS_OLYMPIQUE_SESSION';
        session_name($sessionName);
    }

    /**
     * Regenerate session ID
     * 
     * Prevents session fixation attacks.
     * 
     * @return void
     */
    public function regenerateSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Set security headers
     * 
     * Adds security headers to HTTP response.
     * 
     * @return void
     */
    public function setSecurityHeaders(): void
    {
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');

        // Enable XSS protection (for older browsers)
        header('X-XSS-Protection: 1; mode=block');

        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');

        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'");

        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Permissions Policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // Remove X-Powered-By header
        header_remove('X-Powered-By');
    }

    /**
     * Hash password using modern algorithm
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password against hash
     * 
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool True if password matches
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if password needs rehashing
     * 
     * @param string $hash Hashed password
     * @return bool True if rehashing is needed
     */
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
}
