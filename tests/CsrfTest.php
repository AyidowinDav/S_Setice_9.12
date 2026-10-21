<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/csrf.php';

class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        // Start session for each test
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Clear session data
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        // Clean up session
        session_destroy();
    }

    public function testGenerateCsrfTokenCreatesToken()
    {
        $token = generateCsrfToken();
        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token)); // bin2hex(32 bytes) = 64 chars
        $this->assertEquals($token, $_SESSION['csrf_token']);
    }

    public function testGenerateCsrfTokenReturnsSameTokenOnSubsequentCalls()
    {
        $token1 = generateCsrfToken();
        $token2 = generateCsrfToken();
        $this->assertEquals($token1, $token2);
    }

    public function testVerifyCsrfTokenReturnsTrueForValidToken()
    {
        $token = generateCsrfToken();
        $this->assertTrue(verifyCsrfToken($token));
    }

    public function testVerifyCsrfTokenReturnsFalseForInvalidToken()
    {
        generateCsrfToken(); // Set a token
        $this->assertFalse(verifyCsrfToken('invalid_token'));
    }

    public function testVerifyCsrfTokenReturnsFalseForEmptyToken()
    {
        generateCsrfToken();
        $this->assertFalse(verifyCsrfToken(''));
    }

    public function testVerifyCsrfTokenReturnsFalseWhenNoTokenSet()
    {
        // No token generated
        $this->assertFalse(verifyCsrfToken('some_token'));
    }

    public function testValidateCsrfTokenIsAliasOfVerifyCsrfToken()
    {
        $token = generateCsrfToken();
        $this->assertEquals(verifyCsrfToken($token), validateCsrfToken($token));
    }
}
