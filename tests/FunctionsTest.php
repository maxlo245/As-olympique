<?php

namespace AsOlympique\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests for utility functions
 */
class FunctionsTest extends TestCase
{
    protected function setUp(): void
    {
        // Start session for CSRF tests
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Load functions
        require_once __DIR__ . '/../src/functions.php';
    }

    public function testEscapeFunction(): void
    {
        $this->assertEquals('&lt;script&gt;', e('<script>'));
        $this->assertEquals('&quot;test&quot;', e('"test"'));
        $this->assertEquals('', e(null));
    }

    public function testGenerateCsrfToken(): void
    {
        $token1 = generate_csrf_token();
        $token2 = generate_csrf_token();

        $this->assertNotEmpty($token1);
        $this->assertEquals(64, strlen($token1)); // 32 bytes = 64 hex chars
        $this->assertEquals($token1, $token2); // Should return same token
    }

    public function testVerifyCsrfToken(): void
    {
        $token = generate_csrf_token();

        $this->assertTrue(verify_csrf_token($token));
        $this->assertFalse(verify_csrf_token('invalid_token'));
        $this->assertFalse(verify_csrf_token(''));
    }

    public function testSecureFilename(): void
    {
        $this->assertEquals('test.jpg', secure_filename('test.jpg'));
        $this->assertEquals('test_file.jpg', secure_filename('test file.jpg'));
        $this->assertEquals('test_file.jpg', secure_filename('test@file!.jpg'));
    }

    public function testSuccessMessage(): void
    {
        $message = success_message('Operation successful');
        $this->assertStringContainsString('alert-success', $message);
        $this->assertStringContainsString('Operation successful', $message);
    }

    public function testErrorMessage(): void
    {
        $message = error_message('An error occurred');
        $this->assertStringContainsString('alert-danger', $message);
        $this->assertStringContainsString('An error occurred', $message);
    }

    public function testWarningMessage(): void
    {
        $message = warning_message('Warning message');
        $this->assertStringContainsString('alert-warning', $message);
        $this->assertStringContainsString('Warning message', $message);
    }

    public function testIsLoggedIn(): void
    {
        // Not logged in initially
        $_SESSION['logged_in'] = false;
        $this->assertFalse(is_logged_in());

        // Logged in
        $_SESSION['logged_in'] = true;
        $this->assertTrue(is_logged_in());
    }

    public function testGetUserRole(): void
    {
        $_SESSION['user_role'] = 'admin';
        $this->assertEquals('admin', get_user_role());

        unset($_SESSION['user_role']);
        $this->assertNull(get_user_role());
    }

    public function testGetUserId(): void
    {
        $_SESSION['user_id'] = 123;
        $this->assertEquals(123, get_user_id());

        unset($_SESSION['user_id']);
        $this->assertNull(get_user_id());
    }

    public function testGetUserName(): void
    {
        $_SESSION['user_nom'] = 'John Doe';
        $this->assertEquals('John Doe', get_user_name());

        unset($_SESSION['user_nom']);
        $this->assertNull(get_user_name());
    }

    public function testFormatBytes(): void
    {
        $this->assertEquals('0 B', format_bytes(0));
        $this->assertEquals('1 KB', format_bytes(1024));
        $this->assertEquals('1 MB', format_bytes(1048576));
        $this->assertEquals('1.5 MB', format_bytes(1572864));
    }

    public function testTruncate(): void
    {
        $this->assertEquals('Hello', truncate('Hello', 10));
        $this->assertEquals('Hello...', truncate('Hello World', 8));
        $this->assertEquals('Test...', truncate('Testing truncation', 7));
    }

    public function testRandomString(): void
    {
        $str1 = random_string(32);
        $str2 = random_string(32);

        $this->assertEquals(32, strlen($str1));
        $this->assertEquals(32, strlen($str2));
        $this->assertNotEquals($str1, $str2); // Should be different
    }

    protected function tearDown(): void
    {
        // Clean up session
        $_SESSION = [];
    }
}
