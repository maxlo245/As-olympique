<?php

namespace AsOlympique;

/**
 * Session Management Wrapper
 *
 * Provides secure session handling with protection against common attacks.
 * Implements session fixation protection and secure cookie parameters.
 *
 * @package AsOlympique
 */
class Session
{
    /**
     * Start a secure session
     *
     * @param array $options Session configuration options
     * @return void
     */
    public static function start(array $options = []): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Default secure options
        $defaults = [
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Strict',
        ];

        $config = array_merge($defaults, $options);

        // Set cookie parameters (PHP 7.3+ syntax)
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => $config['lifetime'],
                'path' => $config['path'],
                'domain' => $config['domain'],
                'secure' => $config['secure'],
                'httponly' => $config['httponly'],
                'samesite' => $config['samesite'],
            ]);
        } else {
            // PHP < 7.3 fallback
            session_set_cookie_params(
                $config['lifetime'],
                $config['path'],
                $config['domain'],
                $config['secure'],
                $config['httponly']
            );
        }

        // Set session name
        session_name('AS_OLYMPIQUE_SESSION');

        // Start session
        session_start();

        // Regenerate session ID on first visit (anti-fixation)
        if (!isset($_SESSION['_initiated'])) {
            self::regenerate();
            $_SESSION['_initiated'] = true;
        }

        // Validate session
        self::validate();
    }

    /**
     * Regenerate session ID (protection against session fixation)
     *
     * @param bool $deleteOldSession Whether to delete the old session
     * @return void
     */
    public static function regenerate(bool $deleteOldSession = true): void
    {
        session_regenerate_id($deleteOldSession);
    }

    /**
     * Validate session integrity
     *
     * Checks user agent and IP address to detect session hijacking attempts.
     * Note: IP checking can be problematic with mobile networks and proxies.
     *
     * @return void
     */
    private static function validate(): void
    {
        // Store and validate user agent
        if (!isset($_SESSION['_user_agent'])) {
            $_SESSION['_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        } elseif ($_SESSION['_user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            // User agent changed - possible session hijacking
            self::destroy();
            return;
        }

        // Optional: IP address validation (commented out due to mobile/proxy issues)
        // if (!isset($_SESSION['_ip_address'])) {
        //     $_SESSION['_ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        // } elseif ($_SESSION['_ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
        //     self::destroy();
        //     return;
        // }
    }

    /**
     * Set a session value
     *
     * @param string $key Session key
     * @param mixed $value Session value
     * @return void
     */
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value
     *
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if a session key exists
     *
     * @param string $key Session key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session value
     *
     * @param string $key Session key
     * @return void
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Destroy the session
     *
     * @return void
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        return self::get('logged_in', false) === true;
    }

    /**
     * Get current user ID
     *
     * @return int|null
     */
    public static function getUserId(): ?int
    {
        return self::get('user_id');
    }
}
