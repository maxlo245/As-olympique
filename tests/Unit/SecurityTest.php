<?php

declare(strict_types=1);

namespace AsOlympique\Tests\Unit;

use AsOlympique\Core\Config;
use AsOlympique\Core\Security;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Security class
 * 
 * Tests security functions including:
 * - CSRF token generation and validation
 * - Input sanitization
 * - Filename security
 * - Password hashing
 * 
 * @package AsOlympique\Tests\Unit
 */
class SecurityTest extends TestCase
{
    private Security $security;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock config for testing
        $configMock = $this->createMock(Config::class);
        $configMock->method('get')
            ->willReturnMap([
                ['security.csrf_token_lifetime', 3600, 3600],
                ['upload.max_size', 2097152, 2097152],
            ]);
        
        $this->security = new Security($configMock);
        
        // Start session for CSRF tests
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    protected function tearDown(): void
    {
        // Clean up session
        $_SESSION = [];
        parent::tearDown();
    }
    
    /**
     * Test CSRF token generation
     */
    public function testGenerateCsrfToken(): void
    {
        $token = $this->security->generateCsrfToken();
        
        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 hex characters
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        $this->assertArrayHasKey('csrf_token', $_SESSION);
        $this->assertArrayHasKey('csrf_token_time', $_SESSION);
    }
    
    /**
     * Test CSRF token validation
     */
    public function testVerifyCsrfToken(): void
    {
        $token = $this->security->generateCsrfToken();
        
        // Valid token should pass
        $this->assertTrue($this->security->verifyCsrfToken($token));
        
        // Invalid token should fail
        $this->assertFalse($this->security->verifyCsrfToken('invalid_token'));
        
        // Wrong token should fail
        $this->assertFalse($this->security->verifyCsrfToken(bin2hex(random_bytes(32))));
    }
    
    /**
     * Test CSRF token expiration
     */
    public function testCsrfTokenExpiration(): void
    {
        $token = $this->security->generateCsrfToken();
        
        // Simulate token expiration by setting old time
        $_SESSION['csrf_token_time'] = time() - 3601; // 1 hour + 1 second ago
        
        $this->assertFalse($this->security->verifyCsrfToken($token));
    }
    
    /**
     * Test HTML escaping
     */
    public function testEscape(): void
    {
        $this->assertEquals('&lt;script&gt;alert(&#039;XSS&#039;)&lt;/script&gt;', 
            $this->security->escape('<script>alert(\'XSS\')</script>'));
        
        $this->assertEquals('&quot;test&quot;', $this->security->escape('"test"'));
        $this->assertEquals('&amp;', $this->security->escape('&'));
        $this->assertEquals('', $this->security->escape(null));
        $this->assertEquals('123', $this->security->escape(123));
    }
    
    /**
     * Test filename sanitization
     */
    public function testSanitizeFilename(): void
    {
        $this->assertEquals('test.txt', $this->security->sanitizeFilename('test.txt'));
        $this->assertEquals('test_file.jpg', $this->security->sanitizeFilename('test file.jpg'));
        $this->assertEquals('test_file.pdf', $this->security->sanitizeFilename('test@file!.pdf'));
        $this->assertEquals('document.docx', $this->security->sanitizeFilename('../../../document.docx'));
        $this->assertEquals('image.png', $this->security->sanitizeFilename('.image.png'));
        
        // Test length limit
        $longName = str_repeat('a', 300) . '.txt';
        $sanitized = $this->security->sanitizeFilename($longName);
        $this->assertLessThanOrEqual(255, strlen($sanitized));
    }
    
    /**
     * Test secure filename generation
     */
    public function testGenerateSecureFilename(): void
    {
        $filename = $this->security->generateSecureFilename('test.pdf');
        
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}\.pdf$/', $filename);
        
        // Test without extension
        $filename = $this->security->generateSecureFilename('noextension');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $filename);
        
        // Each generated filename should be unique
        $filename1 = $this->security->generateSecureFilename('test.jpg');
        $filename2 = $this->security->generateSecureFilename('test.jpg');
        $this->assertNotEquals($filename1, $filename2);
    }
    
    /**
     * Test file upload validation
     */
    public function testValidateFileUpload(): void
    {
        // Mock valid file upload
        $validFile = [
            'name' => 'test.jpg',
            'tmp_name' => __FILE__, // Use this file for testing
            'size' => 1000,
            'error' => UPLOAD_ERR_OK
        ];
        
        $result = $this->security->validateFileUpload(
            $validFile,
            ['jpg', 'png', 'gif', 'php'], // Include php for this test file
            ['image/jpeg', 'image/png', 'image/gif', 'text/x-php'] // Include php mime for this file
        );
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('mime_type', $result);
    }
    
    /**
     * Test file upload validation with error
     */
    public function testValidateFileUploadWithError(): void
    {
        $invalidFile = [
            'name' => 'test.jpg',
            'tmp_name' => '/tmp/test',
            'size' => 1000,
            'error' => UPLOAD_ERR_NO_FILE
        ];
        
        $result = $this->security->validateFileUpload(
            $invalidFile,
            ['jpg'],
            ['image/jpeg']
        );
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }
    
    /**
     * Test password hashing
     */
    public function testHashPassword(): void
    {
        $password = 'SecurePassword123!';
        $hash = $this->security->hashPassword($password);
        
        $this->assertIsString($hash);
        $this->assertStringStartsWith('$2y$', $hash); // bcrypt identifier
        $this->assertNotEquals($password, $hash);
        
        // Each hash should be different (due to random salt)
        $hash2 = $this->security->hashPassword($password);
        $this->assertNotEquals($hash, $hash2);
    }
    
    /**
     * Test password verification
     */
    public function testVerifyPassword(): void
    {
        $password = 'MySecurePassword!';
        $hash = $this->security->hashPassword($password);
        
        // Correct password should verify
        $this->assertTrue($this->security->verifyPassword($password, $hash));
        
        // Wrong password should fail
        $this->assertFalse($this->security->verifyPassword('WrongPassword', $hash));
        $this->assertFalse($this->security->verifyPassword('', $hash));
    }
    
    /**
     * Test password rehash check
     */
    public function testNeedsRehash(): void
    {
        $password = 'TestPassword123';
        $hash = $this->security->hashPassword($password);
        
        // Fresh hash shouldn't need rehashing
        $this->assertFalse($this->security->needsRehash($hash));
        
        // Old hash format should need rehashing
        $oldHash = crypt($password, '$2a$07$usesomesillystringforsalt$');
        $this->assertTrue($this->security->needsRehash($oldHash));
    }
}
