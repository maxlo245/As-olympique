<?php

namespace AsOlympique;

/**
 * Error Handler
 *
 * Centralized error and exception handling with environment-aware display.
 * Logs errors and provides user-friendly error pages in production.
 *
 * @package AsOlympique
 */
class ErrorHandler
{
    /**
     * Logger instance
     *
     * @var Logger|null
     */
    private static ?Logger $logger = null;

    /**
     * Environment mode (development or production)
     *
     * @var string
     */
    private static string $environment = 'development';

    /**
     * Initialize error handler
     *
     * @param Logger|null $logger Logger instance
     * @param string $environment Environment mode
     * @return void
     */
    public static function init(?Logger $logger = null, string $environment = 'development'): void
    {
        self::$logger = $logger;
        self::$environment = $environment;

        // Set error handler
        set_error_handler([self::class, 'handleError']);

        // Set exception handler
        set_exception_handler([self::class, 'handleException']);

        // Set shutdown function for fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);

        // Configure error reporting based on environment
        if (self::$environment === 'production') {
            error_reporting(E_ALL);
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        }
    }

    /**
     * Handle PHP errors
     *
     * @param int $errno Error number
     * @param string $errstr Error message
     * @param string $errfile File where error occurred
     * @param int $errline Line where error occurred
     * @return bool
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Don't handle errors suppressed with @
        if (!(error_reporting() & $errno)) {
            return false;
        }

        // Map error type to log level
        $logLevel = self::getLogLevel($errno);

        // Log the error
        if (self::$logger) {
            self::$logger->log($logLevel, $errstr, [
                'errno' => $errno,
                'file' => $errfile,
                'line' => $errline,
            ]);
        }

        // Display error if in development
        if (self::$environment === 'development') {
            self::displayError($errno, $errstr, $errfile, $errline);
        }

        // Don't execute PHP internal error handler
        return true;
    }

    /**
     * Handle uncaught exceptions
     *
     * @param \Throwable $exception
     * @return void
     */
    public static function handleException(\Throwable $exception): void
    {
        // Log the exception
        if (self::$logger) {
            self::$logger->error($exception->getMessage(), [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        // Display error page
        if (self::$environment === 'development') {
            self::displayException($exception);
        } else {
            self::displayProductionError();
        }
    }

    /**
     * Handle fatal errors on shutdown
     *
     * @return void
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            // Log the error
            if (self::$logger) {
                self::$logger->critical($error['message'], [
                    'file' => $error['file'],
                    'line' => $error['line'],
                ]);
            }

            // Display error page
            if (self::$environment === 'development') {
                self::displayError($error['type'], $error['message'], $error['file'], $error['line']);
            } else {
                self::displayProductionError();
            }
        }
    }

    /**
     * Get log level for error type
     *
     * @param int $errno Error number
     * @return string Log level
     */
    private static function getLogLevel(int $errno): string
    {
        switch ($errno) {
            case E_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_PARSE:
                return Logger::ERROR;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                return Logger::WARNING;
            case E_NOTICE:
            case E_USER_NOTICE:
                return Logger::NOTICE;
            default:
                return Logger::INFO;
        }
    }

    /**
     * Display error (development mode)
     *
     * @param int $errno Error number
     * @param string $errstr Error message
     * @param string $errfile File path
     * @param int $errline Line number
     * @return void
     */
    private static function displayError(int $errno, string $errstr, string $errfile, int $errline): void
    {
        $errorType = self::getErrorTypeName($errno);

        echo '<div style="background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:15px;margin:10px;border-radius:4px;">';
        echo '<h3 style="margin:0 0 10px 0;">⚠️ ' . htmlspecialchars($errorType) . '</h3>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($errstr) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($errfile) . '</p>';
        echo '<p><strong>Line:</strong> ' . $errline . '</p>';
        echo '</div>';
    }

    /**
     * Display exception (development mode)
     *
     * @param \Throwable $exception
     * @return void
     */
    private static function displayException(\Throwable $exception): void
    {
        echo '<div style="background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:15px;margin:10px;border-radius:4px;">';
        echo '<h3 style="margin:0 0 10px 0;">💥 Uncaught Exception</h3>';
        echo '<p><strong>Type:</strong> ' . htmlspecialchars(get_class($exception)) . '</p>';
        echo '<p><strong>Message:</strong> ' . htmlspecialchars($exception->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($exception->getFile()) . '</p>';
        echo '<p><strong>Line:</strong> ' . $exception->getLine() . '</p>';
        echo '<details style="margin-top:10px;"><summary>Stack Trace</summary>';
        echo '<pre style="margin:10px 0;padding:10px;background:#fff;border:1px solid #ccc;overflow-x:auto;">';
        echo htmlspecialchars($exception->getTraceAsString());
        echo '</pre></details></div>';
    }

    /**
     * Display production error page
     *
     * @return void
     */
    private static function displayProductionError(): void
    {
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><title>Error</title></head><body>';
        echo '<h1>An error occurred</h1>';
        echo '<p>We apologize for the inconvenience. Please try again later.</p>';
        echo '</body></html>';
        exit;
    }

    /**
     * Get error type name
     *
     * @param int $type Error type constant
     * @return string Error type name
     */
    private static function getErrorTypeName(int $type): string
    {
        $types = [
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Standards',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated',
        ];

        return $types[$type] ?? 'Unknown Error';
    }
}
