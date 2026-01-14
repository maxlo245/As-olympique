<?php
/**
 * Security Headers Configuration
 *
 * Sets HTTP security headers to protect against common web vulnerabilities.
 * These headers should be included in all secure pages.
 *
 * @package AsOlympique
 */

// Prevent clickjacking attacks
header('X-Frame-Options: DENY');

// Prevent MIME type sniffing
header('X-Content-Type-Options: nosniff');

// Enable XSS protection (for older browsers)
header('X-XSS-Protection: 1; mode=block');

// Enforce HTTPS (only if using HTTPS)
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Content Security Policy
// Note: Adjust based on your needs. This is a restrictive default.
$csp = [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline'", // unsafe-inline needed for inline scripts (consider removing in production)
    "style-src 'self' 'unsafe-inline'",  // unsafe-inline needed for inline styles
    "img-src 'self' data:",
    "font-src 'self'",
    "connect-src 'self'",
    "frame-ancestors 'none'",
    "base-uri 'self'",
    "form-action 'self'",
];
header('Content-Security-Policy: ' . implode('; ', $csp));

// Referrer Policy - control referrer information
header('Referrer-Policy: strict-origin-when-cross-origin');

// Permissions Policy (formerly Feature-Policy)
// Restrict access to browser features
$permissions = [
    'geolocation=()',
    'microphone=()',
    'camera=()',
    'payment=()',
    'usb=()',
];
header('Permissions-Policy: ' . implode(', ', $permissions));

// Additional security headers for API responses
if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    header('X-Content-Type-Options: nosniff');
    header('Content-Type: application/json; charset=utf-8');
}
