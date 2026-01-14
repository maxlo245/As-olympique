<?php

namespace AsOlympique;

/**
 * Input Validator
 *
 * Provides comprehensive validation and sanitization methods for user input.
 * All methods return false on validation failure and sanitized value on success.
 *
 * @package AsOlympique
 */
class Validator
{
    /**
     * Validate and sanitize email address
     *
     * @param string $email Email address to validate
     * @return string|false Sanitized email or false on failure
     */
    public static function validateEmail(string $email)
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate phone number (French format)
     *
     * Accepts formats: 0123456789, 01 23 45 67 89, 01.23.45.67.89, 01-23-45-67-89
     *
     * @param string $phone Phone number to validate
     * @return string|false Sanitized phone (digits only) or false on failure
     */
    public static function validatePhone(string $phone)
    {
        // Remove all non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // French phone: 10 digits starting with 0
        if (preg_match('/^0[1-9][0-9]{8}$/', $cleaned)) {
            return $cleaned;
        }

        // International format: +33 followed by 9 digits
        if (preg_match('/^(\+33|0033)[1-9][0-9]{8}$/', $phone)) {
            return $cleaned;
        }

        return false;
    }

    /**
     * Validate date format
     *
     * @param string $date Date string to validate
     * @param string $format Expected date format (default: Y-m-d)
     * @return string|false Validated date string or false on failure
     */
    public static function validateDate(string $date, string $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return ($d && $d->format($format) === $date) ? $date : false;
    }

    /**
     * Sanitize string for safe output
     *
     * Removes or encodes potentially dangerous characters.
     *
     * @param string $string String to sanitize
     * @param bool $allowHtml Whether to allow basic HTML tags
     * @return string Sanitized string
     */
    public static function sanitizeString(string $string, bool $allowHtml = false): string
    {
        if ($allowHtml) {
            // Allow only safe HTML tags
            $allowedTags = '<p><br><strong><em><u><a><ul><ol><li>';
            return strip_tags($string, $allowedTags);
        }

        // Remove all HTML tags
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate and sanitize integer
     *
     * @param mixed $value Value to validate
     * @param int|null $min Minimum value (inclusive)
     * @param int|null $max Maximum value (inclusive)
     * @return int|false Validated integer or false on failure
     */
    public static function validateInteger($value, ?int $min = null, ?int $max = null)
    {
        if (!is_numeric($value)) {
            return false;
        }

        $int = filter_var($value, FILTER_VALIDATE_INT);

        if ($int === false) {
            return false;
        }

        if ($min !== null && $int < $min) {
            return false;
        }

        if ($max !== null && $int > $max) {
            return false;
        }

        return $int;
    }

    /**
     * Validate and sanitize float/decimal
     *
     * @param mixed $value Value to validate
     * @param float|null $min Minimum value (inclusive)
     * @param float|null $max Maximum value (inclusive)
     * @return float|false Validated float or false on failure
     */
    public static function validateFloat($value, ?float $min = null, ?float $max = null)
    {
        $float = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($float === false) {
            return false;
        }

        if ($min !== null && $float < $min) {
            return false;
        }

        if ($max !== null && $float > $max) {
            return false;
        }

        return $float;
    }

    /**
     * Validate URL
     *
     * @param string $url URL to validate
     * @param bool $requireHttps Whether to require HTTPS
     * @return string|false Validated URL or false on failure
     */
    public static function validateUrl(string $url, bool $requireHttps = false)
    {
        $url = filter_var($url, FILTER_VALIDATE_URL);

        if ($url === false) {
            return false;
        }

        if ($requireHttps && strpos($url, 'https://') !== 0) {
            return false;
        }

        return $url;
    }

    /**
     * Validate username
     *
     * Username must be 3-50 characters, alphanumeric plus underscore and hyphen.
     *
     * @param string $username Username to validate
     * @return string|false Validated username or false on failure
     */
    public static function validateUsername(string $username)
    {
        $username = trim($username);

        if (strlen($username) < 3 || strlen($username) > 50) {
            return false;
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            return false;
        }

        return $username;
    }

    /**
     * Validate password strength
     *
     * Password must be at least 8 characters with at least one uppercase,
     * one lowercase, one digit, and one special character.
     *
     * @param string $password Password to validate
     * @param int $minLength Minimum password length
     * @return bool True if password meets requirements
     */
    public static function validatePassword(string $password, int $minLength = 8): bool
    {
        if (strlen($password) < $minLength) {
            return false;
        }

        // Check for at least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // Check for at least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // Check for at least one digit
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        // Check for at least one special character
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }

        return true;
    }

    /**
     * Sanitize filename
     *
     * Removes or replaces dangerous characters in filenames.
     *
     * @param string $filename Filename to sanitize
     * @return string Sanitized filename
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove path information
        $filename = basename($filename);

        // Replace spaces with underscores
        $filename = str_replace(' ', '_', $filename);

        // Keep only alphanumeric, underscore, hyphen, and dot
        $filename = preg_replace('/[^A-Za-z0-9\._-]/', '', $filename);

        // Remove any leading dots (hidden files)
        $filename = ltrim($filename, '.');

        // Ensure filename is not empty
        if (empty($filename)) {
            $filename = 'unnamed';
        }

        return $filename;
    }

    /**
     * Validate array of values
     *
     * @param mixed $value Value to check
     * @param array $allowed Array of allowed values
     * @return mixed|false The value if it's in allowed array, false otherwise
     */
    public static function validateInArray($value, array $allowed)
    {
        return in_array($value, $allowed, true) ? $value : false;
    }
}
