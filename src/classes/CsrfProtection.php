<?php

namespace AsOlympique;

/**
 * CSRF Protection Handler
 *
 * Provides CSRF token generation and validation to protect against
 * Cross-Site Request Forgery attacks.
 *
 * @package AsOlympique
 */
class CsrfProtection
{
    /**
     * Default token name
     */
    private const TOKEN_NAME = 'csrf_token';

    /**
     * Token length in bytes (will be doubled when hex encoded)
     */
    private const TOKEN_LENGTH = 32;

    /**
     * Generate a new CSRF token
     *
     * Creates a cryptographically secure random token and stores it in the session.
     * If a token already exists, it returns the existing one.
     *
     * @param string $tokenName Optional custom token name
     * @return string The generated or existing token
     */
    public static function generateToken(string $tokenName = self::TOKEN_NAME): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            trigger_error('Session must be started before generating CSRF token', E_USER_WARNING);
        }

        if (empty($_SESSION[$tokenName])) {
            $_SESSION[$tokenName] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }

        return $_SESSION[$tokenName];
    }

    /**
     * Verify a CSRF token
     *
     * Compares the provided token with the one stored in the session using
     * a timing-safe comparison function to prevent timing attacks.
     *
     * @param string $token The token to verify
     * @param string $tokenName Optional custom token name
     * @return bool True if token is valid, false otherwise
     */
    public static function verifyToken(string $token, string $tokenName = self::TOKEN_NAME): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $sessionToken = $_SESSION[$tokenName] ?? '';
        $providedToken = $token ?? '';

        // Use hash_equals to prevent timing attacks
        return hash_equals($sessionToken, $providedToken);
    }

    /**
     * Get the token name
     *
     * @return string
     */
    public static function getTokenName(): string
    {
        return self::TOKEN_NAME;
    }

    /**
     * Generate HTML input field for CSRF token
     *
     * @param string $tokenName Optional custom token name
     * @return string HTML input field
     */
    public static function getTokenField(string $tokenName = self::TOKEN_NAME): string
    {
        $token = self::generateToken($tokenName);
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars($tokenName, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Validate CSRF token from POST request
     *
     * Convenience method to validate token from $_POST.
     *
     * @param string $tokenName Optional custom token name
     * @return bool True if token is valid, false otherwise
     */
    public static function validatePost(string $tokenName = self::TOKEN_NAME): bool
    {
        $token = $_POST[$tokenName] ?? '';
        return self::verifyToken($token, $tokenName);
    }

    /**
     * Regenerate CSRF token
     *
     * Useful after sensitive operations to prevent token reuse.
     *
     * @param string $tokenName Optional custom token name
     * @return string The new token
     */
    public static function regenerateToken(string $tokenName = self::TOKEN_NAME): string
    {
        unset($_SESSION[$tokenName]);
        return self::generateToken($tokenName);
    }
}
