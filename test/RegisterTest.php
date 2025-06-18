<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../register.php';

class RegisterTest extends TestCase {
    private $conn;
    private $stmt;

    protected function setUp(): void {
        $this->conn = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
    }

    public function testInvalidEmail() {
        $this->conn->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetch')->willReturn(false);

        $result = registerUser("Ahmed", "bad-email", "123456", "student", "2025", $this->conn);
        $this->assertContains("البريد الإلكتروني غير صالح.", $result);
    }

    public function testShortPassword() {
        $this->conn->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetch')->willReturn(false);

        $result = registerUser("Sara", "sara@example.com", "123", "doctor", "555", $this->conn);
        $this->assertContains("كلمة المرور يجب أن تكون 6 حروف أو أكثر.", $result);
    }

    public function testValidRegistration() {
        $this->conn->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetch')->willReturn(false);

        $result = registerUser("Ali", "ali@example.com", "123456", "student", "001", $this->conn);
        $this->assertEquals("تم التسجيل بنجاح!", $result);
    }
}
