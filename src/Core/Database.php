<?php

declare(strict_types=1);

namespace AsOlympique\Core;

use PDO;
use PDOException;
use Exception;

/**
 * Database connection manager with singleton pattern
 * 
 * Provides a centralized database connection with:
 * - Singleton pattern to prevent multiple connections
 * - Connection retry logic
 * - Connection health checks
 * - Proper error handling
 * - Performance optimizations
 * 
 * @package AsOlympique\Core
 * @author Florence PEYRATAUD
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
     * PDO connection instance
     * 
     * @var PDO|null
     */
    private ?PDO $connection = null;

    /**
     * Database configuration
     * 
     * @var array<string, mixed>
     */
    private array $config;

    /**
     * Maximum connection retry attempts
     * 
     * @var int
     */
    private int $maxRetries = 3;

    /**
     * Retry delay in seconds
     * 
     * @var int
     */
    private int $retryDelay = 2;

    /**
     * Private constructor to prevent direct instantiation
     * 
     * @param array<string, mixed> $config Database configuration
     */
    private function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * Get singleton instance
     * 
     * @param array<string, mixed>|null $config Database configuration
     * @return Database
     * @throws Exception If configuration is missing
     */
    public static function getInstance(?array $config = null): Database
    {
        if (self::$instance === null) {
            if ($config === null) {
                throw new Exception('Database configuration required for first initialization');
            }
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Establish database connection with retry logic
     * 
     * @return void
     * @throws Exception If connection fails after all retries
     */
    private function connect(): void
    {
        $attempts = 0;
        $lastException = null;

        while ($attempts < $this->maxRetries) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    $this->config['host'] ?? 'localhost',
                    $this->config['dbname'] ?? '',
                    $this->config['charset'] ?? 'utf8mb4'
                );

                // Add port if specified
                if (!empty($this->config['port']) && $this->config['port'] !== 3306) {
                    $dsn .= ';port=' . $this->config['port'];
                }

                $options = [
                    // Enable exceptions for error handling
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    
                    // Return associative arrays by default
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    
                    // Disable emulated prepares for true prepared statements
                    PDO::ATTR_EMULATE_PREPARES => false,
                    
                    // Enable persistent connections for performance
                    PDO::ATTR_PERSISTENT => true,
                    
                    // Set connection timeout
                    PDO::ATTR_TIMEOUT => 5,
                    
                    // Use buffered queries for better performance with large result sets
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    
                    // Enable MySQL native prepared statements
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                $this->connection = new PDO(
                    $dsn,
                    $this->config['user'] ?? '',
                    $this->config['pass'] ?? '',
                    $options
                );

                // Connection successful
                error_log('[Database] Connection established successfully');
                return;

            } catch (PDOException $e) {
                $lastException = $e;
                $attempts++;

                if ($attempts < $this->maxRetries) {
                    error_log(sprintf(
                        '[Database] Connection attempt %d/%d failed: %s. Retrying in %d seconds...',
                        $attempts,
                        $this->maxRetries,
                        $e->getMessage(),
                        $this->retryDelay
                    ));
                    sleep($this->retryDelay);
                } else {
                    error_log(sprintf(
                        '[Database] Connection failed after %d attempts: %s',
                        $this->maxRetries,
                        $e->getMessage()
                    ));
                }
            }
        }

        throw new Exception(
            'Database connection failed after ' . $this->maxRetries . ' attempts: ' . 
            ($lastException ? $lastException->getMessage() : 'Unknown error')
        );
    }

    /**
     * Get PDO connection instance
     * 
     * @return PDO
     * @throws Exception If connection is not established
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            throw new Exception('Database connection not established');
        }

        // Check if connection is still alive
        if (!$this->isConnected()) {
            error_log('[Database] Connection lost, reconnecting...');
            $this->connect();
        }

        return $this->connection;
    }

    /**
     * Check if database connection is alive
     * 
     * @return bool
     */
    public function isConnected(): bool
    {
        if ($this->connection === null) {
            return false;
        }

        try {
            // Simple ping query
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            error_log('[Database] Connection check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reconnect to database
     * 
     * @return void
     * @throws Exception If reconnection fails
     */
    public function reconnect(): void
    {
        $this->disconnect();
        $this->connect();
    }

    /**
     * Close database connection
     * 
     * @return void
     */
    public function disconnect(): void
    {
        $this->connection = null;
        error_log('[Database] Connection closed');
    }

    /**
     * Execute a query with automatic retry on connection failure
     * 
     * @param string $sql SQL query
     * @param array<int|string, mixed> $params Parameters for prepared statement
     * @return \PDOStatement
     * @throws PDOException If query fails
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // If connection error, try to reconnect once
            if ($e->getCode() === 'HY000' || $e->getCode() === '2006') {
                error_log('[Database] Query failed with connection error, attempting reconnect');
                $this->reconnect();
                
                // Retry query once
                $stmt = $this->getConnection()->prepare($sql);
                $stmt->execute($params);
                return $stmt;
            }
            
            throw $e;
        }
    }

    /**
     * Begin transaction
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     * 
     * @return bool
     */
    public function commit(): bool
    {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback transaction
     * 
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->getConnection()->rollBack();
    }

    /**
     * Get last insert ID
     * 
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->getConnection()->lastInsertId();
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
