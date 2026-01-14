<?php

namespace AsOlympique;

use PDO;
use PDOException;
use Exception;

/**
 * Database Connection Singleton
 *
 * Provides a single PDO instance with proper configuration for the application.
 * Implements the Singleton pattern to ensure only one database connection exists.
 *
 * @package AsOlympique
 */
class Database
{
    /**
     * Singleton instance
     *
     * @var Database|null
     */
    private static ?Database $instance = null;

    /**
     * PDO instance
     *
     * @var PDO|null
     */
    private ?PDO $pdo = null;

    /**
     * Database configuration
     *
     * @var array
     */
    private array $config;

    /**
     * Private constructor to prevent direct instantiation
     *
     * @param array $config Database configuration
     */
    private function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * Get singleton instance
     *
     * @param array|null $config Database configuration (required on first call)
     * @return Database
     * @throws Exception If config is not provided on first call
     */
    public static function getInstance(?array $config = null): Database
    {
        if (self::$instance === null) {
            if ($config === null) {
                throw new Exception('Database configuration required on first call');
            }
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Establish database connection
     *
     * @return void
     * @throws PDOException If connection fails
     */
    private function connect(): void
    {
        $host = $this->config['host'] ?? 'localhost';
        $dbname = $this->config['dbname'] ?? '';
        $charset = $this->config['charset'] ?? 'utf8mb4';
        $user = $this->config['user'] ?? '';
        $pass = $this->config['pass'] ?? '';

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false, // Use real prepared statements
            PDO::ATTR_PERSISTENT => false, // Persistent connections can cause issues in some environments
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw new PDOException('Database connection failed');
        }
    }

    /**
     * Get PDO instance
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Prevent cloning of the instance
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization of the instance
     *
     * @return void
     */
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize singleton');
    }
}
