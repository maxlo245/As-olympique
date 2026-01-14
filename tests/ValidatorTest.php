<?php

namespace AsOlympique\Tests;

use PHPUnit\Framework\TestCase;
use AsOlympique\Validator;

/**
 * Tests for Validator class
 */
class ValidatorTest extends TestCase
{
    public function testValidateEmail(): void
    {
        // Valid emails
        $this->assertEquals('test@example.com', Validator::validateEmail('test@example.com'));
        $this->assertEquals('user+tag@domain.co.uk', Validator::validateEmail('user+tag@domain.co.uk'));

        // Invalid emails
        $this->assertFalse(Validator::validateEmail('invalid'));
        $this->assertFalse(Validator::validateEmail('test@'));
        $this->assertFalse(Validator::validateEmail('@domain.com'));
    }

    public function testValidatePhone(): void
    {
        // Valid French phones
        $this->assertEquals('0123456789', Validator::validatePhone('0123456789'));
        $this->assertEquals('0123456789', Validator::validatePhone('01 23 45 67 89'));
        $this->assertEquals('0123456789', Validator::validatePhone('01.23.45.67.89'));
        $this->assertEquals('0123456789', Validator::validatePhone('01-23-45-67-89'));

        // Invalid phones
        $this->assertFalse(Validator::validatePhone('123456789')); // Missing leading 0
        $this->assertFalse(Validator::validatePhone('0223456789A')); // Contains letter
    }

    public function testValidateDate(): void
    {
        // Valid dates
        $this->assertEquals('2024-01-15', Validator::validateDate('2024-01-15'));
        $this->assertEquals('2024-12-31', Validator::validateDate('2024-12-31'));

        // Invalid dates
        $this->assertFalse(Validator::validateDate('2024-13-01')); // Invalid month
        $this->assertFalse(Validator::validateDate('2024-02-30')); // Invalid day
        $this->assertFalse(Validator::validateDate('invalid'));
    }

    public function testSanitizeString(): void
    {
        // Without HTML
        $this->assertEquals(
            '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;',
            Validator::sanitizeString('<script>alert("XSS")</script>')
        );

        // With allowed HTML
        $result = Validator::sanitizeString('<p>Test</p><script>bad()</script>', true);
        $this->assertStringContainsString('<p>Test</p>', $result);
        $this->assertStringNotContainsString('<script>', $result);
    }

    public function testValidateInteger(): void
    {
        // Valid integers
        $this->assertEquals(42, Validator::validateInteger(42));
        $this->assertEquals(0, Validator::validateInteger('0'));
        $this->assertEquals(-10, Validator::validateInteger(-10));

        // With range
        $this->assertEquals(50, Validator::validateInteger(50, 0, 100));
        $this->assertFalse(Validator::validateInteger(150, 0, 100)); // Above max
        $this->assertFalse(Validator::validateInteger(-10, 0, 100)); // Below min

        // Invalid
        $this->assertFalse(Validator::validateInteger('abc'));
        $this->assertFalse(Validator::validateInteger(3.14));
    }

    public function testValidateFloat(): void
    {
        // Valid floats
        $this->assertEquals(3.14, Validator::validateFloat(3.14));
        $this->assertEquals(0.5, Validator::validateFloat('0.5'));

        // With range
        $this->assertEquals(5.5, Validator::validateFloat(5.5, 0.0, 10.0));
        $this->assertFalse(Validator::validateFloat(15.0, 0.0, 10.0)); // Above max
        $this->assertFalse(Validator::validateFloat(-1.0, 0.0, 10.0)); // Below min

        // Invalid
        $this->assertFalse(Validator::validateFloat('abc'));
    }

    public function testValidateUrl(): void
    {
        // Valid URLs
        $this->assertEquals('http://example.com', Validator::validateUrl('http://example.com'));
        $this->assertEquals('https://example.com/path', Validator::validateUrl('https://example.com/path'));

        // HTTPS required
        $this->assertEquals('https://secure.com', Validator::validateUrl('https://secure.com', true));
        $this->assertFalse(Validator::validateUrl('http://not-secure.com', true));

        // Invalid URLs
        $this->assertFalse(Validator::validateUrl('not a url'));
        $this->assertFalse(Validator::validateUrl('ftp://example.com'));
    }

    public function testValidateUsername(): void
    {
        // Valid usernames
        $this->assertEquals('john_doe', Validator::validateUsername('john_doe'));
        $this->assertEquals('user123', Validator::validateUsername('user123'));
        $this->assertEquals('test-user', Validator::validateUsername('test-user'));

        // Invalid usernames
        $this->assertFalse(Validator::validateUsername('ab')); // Too short
        $this->assertFalse(Validator::validateUsername('user@name')); // Invalid char
        $this->assertFalse(Validator::validateUsername('user name')); // Space
    }

    public function testValidatePassword(): void
    {
        // Valid passwords
        $this->assertTrue(Validator::validatePassword('Test123!'));
        $this->assertTrue(Validator::validatePassword('MyP@ssw0rd'));
        $this->assertTrue(Validator::validatePassword('Secure#Pass1'));

        // Invalid passwords
        $this->assertFalse(Validator::validatePassword('short')); // Too short
        $this->assertFalse(Validator::validatePassword('alllowercase123!')); // No uppercase
        $this->assertFalse(Validator::validatePassword('ALLUPPERCASE123!')); // No lowercase
        $this->assertFalse(Validator::validatePassword('NoNumbers!')); // No digits
        $this->assertFalse(Validator::validatePassword('NoSpecial123')); // No special char
    }

    public function testSanitizeFilename(): void
    {
        $this->assertEquals('test.jpg', Validator::sanitizeFilename('test.jpg'));
        $this->assertEquals('my_file.pdf', Validator::sanitizeFilename('my file.pdf'));
        $this->assertEquals('document.docx', Validator::sanitizeFilename('../../../document.docx'));
        $this->assertEquals('file.txt', Validator::sanitizeFilename('file@#$.txt'));
        $this->assertEquals('unnamed', Validator::sanitizeFilename('.hidden'));
    }

    public function testValidateInArray(): void
    {
        $allowed = ['red', 'green', 'blue'];

        $this->assertEquals('red', Validator::validateInArray('red', $allowed));
        $this->assertEquals('blue', Validator::validateInArray('blue', $allowed));
        $this->assertFalse(Validator::validateInArray('yellow', $allowed));
        $this->assertFalse(Validator::validateInArray('RED', $allowed)); // Case sensitive
    }
}
