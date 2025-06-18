<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../add_project.php';

class AddProjectTest extends TestCase {
    private $conn;
    private $stmt;

    protected function setUp(): void {
        $this->conn = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
    }

    public function testStudentNotFound() {
        $this->conn->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetch')->willReturn(false); // الطالب غير موجود

        $result = addProject("عنوان", "وصف", "123", null, "456", $this->conn);
        $this->assertEquals("الطالب الذي أدخلت رقمه غير موجود.", $result);
    }

    public function testManagerNotFound() {
        $this->conn->method('prepare')->willReturnOnConsecutiveCalls($this->stmt, $this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetch')->willReturnOnConsecutiveCalls(
            ['id' => '123'], // الطالب موجود
            false            // المشرف غير موجود
        );

        $result = addProject("عنوان", "وصف", "123", null, "456", $this->conn);
        $this->assertEquals("المشرف الذي أدخلت رقمه غير موجود.", $result);
    }

    public function testProjectSuccess() {
        $this->conn->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetch')->willReturn(['id' => 'x']); // الكل موجود

        $result = addProject("مشروع", "تفاصيل", "1", null, "2", $this->conn);
        $this->assertEquals("تم إضافة المشروع بنجاح.", $result);
    }
}
