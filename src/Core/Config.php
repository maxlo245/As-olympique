<?php

declare(strict_types=1);

namespace AsOlympique\Core;

use Exception;

/**
 * Configuration manager with environment variable support
 * 
 * Provides centralized configuration management with:
 * - Environment variable loading
 * - Configuration validation
 * - Type-safe configuration access
 * - Multiple environment support (dev, staging, prod)
 * - Configuration caching
 * 
 * @package AsOlympique\Core
 * @author Florence PEYRATAUD
 */
class Config
{
    /**
     * Singleton instance
     * 
     * @var Config|null
     */
    private static ?Config $instance = null;

    /**
     * Configuration array
     * 
     * @var array<string, mixed>
     */
    private array $config = [];

    /**
     * Environment name
     * 
     * @var string
     */
    private string $environment;

    /**
     * Private constructor to prevent direct instantiation
     * 
     * @param string|null $envFile Path to .env file
     */
    private function __construct(?string $envFile = null)
    {
        $this->loadEnvironment($envFile);
        $this->loadConfiguration();
        $this->validate();
    }

    /**
     * Get singleton instance
     * 
     * @param string|null $envFile Path to .env file
     * @return Config
     */
    public static function getInstance(?string $envFile = null): Config
    {
        if (self::$instance === null) {
            self::$instance = new self($envFile);
        }

        return self::$instance;
    }

    /**
     * Load environment variables from .env file
     * 
     * @param string|null $envFile Path to .env file
     * @return void
     */
    private function loadEnvironment(?string $envFile = null): void
    {
        if ($envFile === null) {
            $envFile = __DIR__ . '/../../.env';
        }

        // Load environment variables if .env file exists
        if (file_exists($envFile) && is_readable($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                // Parse KEY=VALUE format
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);

                    // Remove quotes if present
                    if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                        $value = $matches[2];
                    }

                    // Set environment variable if not already set
                    if (!isset($_ENV[$key]) && !getenv($key)) {
                        putenv("$key=$value");
                        $_ENV[$key] = $value;
                    }
                }
            }
        }
    }

    /**
     * Load configuration from environment variables
     * 
     * @return void
     */
    private function loadConfiguration(): void
    {
        $this->environment = $this->getEnv('APP_ENV', 'development');

        $this->config = [
            'app' => [
                'env' => $this->environment,
                'debug' => $this->getEnv('APP_DEBUG', 'false') === 'true',
            ],
            'db' => [
                'host' => $this->getEnv('DB_HOST', 'localhost'),
                'port' => (int) $this->getEnv('DB_PORT', '3306'),
                'dbname' => $this->getEnv('DB_NAME', 'as_olympique_db'),
                'user' => $this->getEnv('DB_USER', 'root'),
                'pass' => $this->getEnv('DB_PASS', 'root'),
                'charset' => $this->getEnv('DB_CHARSET', 'utf8mb4'),
            ],
            'upload' => [
                'dir' => $this->getEnv('UPLOAD_DIR', __DIR__ . '/../../uploads/'),
                'max_size' => (int) $this->getEnv('MAX_UPLOAD_SIZE', '2097152'), // 2MB
            ],
            'session' => [
                'lifetime' => (int) $this->getEnv('SESSION_LIFETIME', '0'),
                'secure' => $this->getEnv('SESSION_SECURE', 'false') === 'true',
                'httponly' => $this->getEnv('SESSION_HTTPONLY', 'true') === 'true',
                'name' => $this->getEnv('SESSION_NAME', 'AS_OLYMPIQUE_SESSION'),
            ],
            'security' => [
                'csrf_token_lifetime' => (int) $this->getEnv('CSRF_TOKEN_LIFETIME', '3600'),
            ],
            'rate_limit' => [
                'login_attempts' => (int) $this->getEnv('RATE_LIMIT_LOGIN_ATTEMPTS', '5'),
                'login_window' => (int) $this->getEnv('RATE_LIMIT_LOGIN_WINDOW', '300'), // 5 minutes
            ],
            'logging' => [
                'level' => $this->getEnv('LOG_LEVEL', 'debug'),
                'path' => $this->getEnv('LOG_PATH', __DIR__ . '/../../logs/'),
            ],
            'cache' => [
                'enabled' => $this->getEnv('CACHE_ENABLED', 'true') === 'true',
                'lifetime' => (int) $this->getEnv('CACHE_LIFETIME', '3600'),
            ],
        ];
    }

    /**
     * Get environment variable with fallback
     * 
     * @param string $key Environment variable key
     * @param string $default Default value if not found
     * @return string
     */
    private function getEnv(string $key, string $default = ''): string
    {
        // Check $_ENV first
        if (isset($_ENV[$key])) {
            return (string) $_ENV[$key];
        }

        // Check getenv()
        $value = getenv($key);
        if ($value !== false) {
            return (string) $value;
        }

        // Return default
        return $default;
    }

    /**
     * Validate configuration
     * 
     * @return void
     * @throws Exception If configuration is invalid
     */
    private function validate(): void
    {
        // Validate database configuration
        if (empty($this->config['db']['host'])) {
            throw new Exception('Database host is required');
        }

        if (empty($this->config['db']['dbname'])) {
            throw new Exception('Database name is required');
        }

        if (empty($this->config['db']['user'])) {
            throw new Exception('Database user is required');
        }

        // Validate upload directory
        $uploadDir = $this->config['upload']['dir'];
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception('Upload directory could not be created: ' . $uploadDir);
            }
        }

        if (!is_writable($uploadDir)) {
            throw new Exception('Upload directory is not writable: ' . $uploadDir);
        }

        // Validate logging directory
        $logDir = $this->config['logging']['path'];
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0755, true)) {
                throw new Exception('Logging directory could not be created: ' . $logDir);
            }
        }

        if (!is_writable($logDir)) {
            throw new Exception('Logging directory is not writable: ' . $logDir);
        }
    }

    /**
     * Get configuration value by dot notation
     * 
     * Example: get('db.host') returns $config['db']['host']
     * 
     * @param string $key Configuration key in dot notation
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Get all configuration
     * 
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Get database configuration
     * 
     * @return array<string, mixed>
     */
    public function getDatabase(): array
    {
        return $this->config['db'];
    }

    /**
     * Get current environment
     * 
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Check if in development environment
     * 
     * @return bool
     */
    public function isDevelopment(): bool
    {
        return $this->environment === 'development';
    }

    /**
     * Check if in production environment
     * 
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    /**
     * Check if debugging is enabled
     * 
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->config['app']['debug'] === true;
    }

    /**
     * Prevent cloning of singleton
     * 
     * @return void
     */
    private function __clone(): void
    {
    }

    /**
     * Prevent unserialization of singleton
     * 
     * @return void
     */
    public function __wakeup(): void
    {
        throw new Exception('Cannot unserialize singleton');
    }
}
