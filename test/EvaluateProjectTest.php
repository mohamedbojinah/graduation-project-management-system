<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../evaluation_handler.php';

class EvaluateProjectTest extends TestCase {
    private $conn;
    private $stmt;

    protected function setUp(): void {
        $this->conn = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);

        // mock prepare() لإرجاع نفس الـ statement دائماً
        $this->conn->method('prepare')->willReturn($this->stmt);
    }

    public function testProjectNotFound() {
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetch')->willReturn(false); // المشروع غير موجود

        $result = evaluateProject(1, 90, "جيد", 101, $this->conn);
        $this->assertEquals("المشروع غير موجود.", $result);
    }

    public function testEvaluationSuccess() {
        $this->stmt->method('execute')->willReturnOnConsecutiveCalls(true, true, true);
        $this->stmt->method('fetch')->willReturnOnConsecutiveCalls(
            ['student_id' => 999] // نتيجة SELECT للمشروع
        );

        $result = evaluateProject(1, 85, "ممتاز", 101, $this->conn);
        $this->assertEquals("تم التقييم بنجاح", $result);
    }

    public function testEvaluationInsertFails() {
        $this->stmt->method('execute')->willReturnOnConsecutiveCalls(true, false); // الأولى SELECT ناجحة، الثانية INSERT تفشل
        $this->stmt->method('fetch')->willReturn(['student_id' => 999]);

        $result = evaluateProject(1, 70, "تعليق", 101, $this->conn);
        $this->assertEquals("فشل في حفظ التقييم.", $result);
    }
}
