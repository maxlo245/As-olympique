<?php

namespace AsOlympique\Tests;

use PHPUnit\Framework\TestCase;
use AsOlympique\Database;
use PDO;

/**
 * Tests for Database class
 *
 * Note: These tests require a test database to be configured.
 * Set environment variables or skip if not available.
 */
class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        // Load environment configuration
        if (file_exists(__DIR__ . '/../src/config/env.php')) {
            require_once __DIR__ . '/../src/config/env.php';
        }
    }

    public function testGetInstanceRequiresConfigOnFirstCall(): void
    {
        // Reset singleton (for testing only - not possible in real usage)
        // This test just documents the expected behavior
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database configuration required on first call');

        // Force getting instance without config
        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null);

        Database::getInstance();
    }

    public function testGetInstanceReturnsSameInstance(): void
    {
        $config = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'dbname' => getenv('DB_NAME') ?: 'as_olympique_test',
            'user' => getenv('DB_USER') ?: 'root',
            'pass' => getenv('DB_PASS') ?: '',
            'charset' => 'utf8mb4',
        ];

        try {
            // Reset singleton
            $reflection = new \ReflectionClass(Database::class);
            $instance = $reflection->getProperty('instance');
            $instance->setAccessible(true);
            $instance->setValue(null);

            $db1 = Database::getInstance($config);
            $db2 = Database::getInstance();

            $this->assertSame($db1, $db2);
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    public function testGetConnectionReturnsPDO(): void
    {
        $config = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'dbname' => getenv('DB_NAME') ?: 'as_olympique_test',
            'user' => getenv('DB_USER') ?: 'root',
            'pass' => getenv('DB_PASS') ?: '',
            'charset' => 'utf8mb4',
        ];

        try {
            // Reset singleton
            $reflection = new \ReflectionClass(Database::class);
            $instance = $reflection->getProperty('instance');
            $instance->setAccessible(true);
            $instance->setValue(null);

            $db = Database::getInstance($config);
            $pdo = $db->getConnection();

            $this->assertInstanceOf(PDO::class, $pdo);
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    public function testDatabaseConnectionSettings(): void
    {
        $config = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'dbname' => getenv('DB_NAME') ?: 'as_olympique_test',
            'user' => getenv('DB_USER') ?: 'root',
            'pass' => getenv('DB_PASS') ?: '',
            'charset' => 'utf8mb4',
        ];

        try {
            // Reset singleton
            $reflection = new \ReflectionClass(Database::class);
            $instance = $reflection->getProperty('instance');
            $instance->setAccessible(true);
            $instance->setValue(null);

            $db = Database::getInstance($config);
            $pdo = $db->getConnection();

            // Test error mode is EXCEPTION
            $this->assertEquals(
                PDO::ERRMODE_EXCEPTION,
                $pdo->getAttribute(PDO::ATTR_ERRMODE)
            );

            // Test default fetch mode is ASSOC
            $this->assertEquals(
                PDO::FETCH_ASSOC,
                $pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE)
            );

            // Test emulated prepares is disabled
            $this->assertFalse(
                (bool) $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES)
            );
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    public function testCannotCloneDatabase(): void
    {
        $config = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'dbname' => getenv('DB_NAME') ?: 'as_olympique_test',
            'user' => getenv('DB_USER') ?: 'root',
            'pass' => getenv('DB_PASS') ?: '',
            'charset' => 'utf8mb4',
        ];

        try {
            // Reset singleton
            $reflection = new \ReflectionClass(Database::class);
            $instance = $reflection->getProperty('instance');
            $instance->setAccessible(true);
            $instance->setValue(null);

            $db = Database::getInstance($config);

            // Try to clone (should fail because __clone is private)
            $reflection = new \ReflectionMethod(Database::class, '__clone');
            $this->assertTrue($reflection->isPrivate());
        } catch (\PDOException $e) {
            $this->markTestSkipped('Database connection not available: ' . $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        // Reset singleton after each test
        $reflection = new \ReflectionClass(Database::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null);
    }
}
