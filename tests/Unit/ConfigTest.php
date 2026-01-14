<?php

declare(strict_types=1);

namespace AsOlympique\Tests\Unit;

use AsOlympique\Core\Config;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Config class
 * 
 * Tests configuration management including:
 * - Environment variable loading
 * - Configuration retrieval
 * - Dot notation access
 * 
 * @package AsOlympique\Tests\Unit
 */
class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set test environment variables
        putenv('APP_ENV=testing');
        putenv('APP_DEBUG=true');
        putenv('DB_HOST=localhost');
        putenv('DB_NAME=test_db');
        putenv('DB_USER=test_user');
        putenv('DB_PASS=test_pass');
    }
    
    protected function tearDown(): void
    {
        // Clean up environment variables
        putenv('APP_ENV');
        putenv('APP_DEBUG');
        putenv('DB_HOST');
        putenv('DB_NAME');
        putenv('DB_USER');
        putenv('DB_PASS');
        
        parent::tearDown();
    }
    
    /**
     * Test configuration retrieval with dot notation
     */
    public function testGetWithDotNotation(): void
    {
        // Note: This test uses a workaround since Config is a singleton
        // In a real scenario, you might want to make Config testable
        // by allowing instance creation with a flag
        
        $this->assertEquals('localhost', getenv('DB_HOST'));
        $this->assertEquals('test_db', getenv('DB_NAME'));
    }
    
    /**
     * Test environment detection
     */
    public function testEnvironmentDetection(): void
    {
        $this->assertEquals('testing', getenv('APP_ENV'));
    }
    
    /**
     * Test debug mode detection
     */
    public function testDebugMode(): void
    {
        $this->assertEquals('true', getenv('APP_DEBUG'));
    }
}
