<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../auth.php';

class LoginTest extends TestCase {
    private $conn;

    protected function setUp(): void {
        $this->conn = $this->createMock(PDO::class);
    }

    public function testInvalidEmailFormat() {
        $result = loginUser("bad-email", "123456", $this->conn);
        $this->assertEquals("صيغة البريد الإلكتروني غير صحيحة.", $result);
    }

    public function testShortPassword() {
        $result = loginUser("test@example.com", "123", $this->conn);
        $this->assertEquals("كلمة المرور يجب أن تكون على الأقل 6 حروف.", $result);
    }
}
