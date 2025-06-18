<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../task_manager.php';

class AddTaskTest extends TestCase {
    private $conn;
    private $stmt;

    protected function setUp(): void {
        $this->conn = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
    }

    public function testProjectIdIsEmpty() {
        $result = addTask(1, "عنوان", "وصف", "2025-01-01", "", $this->conn);
        $this->assertEquals("يجب اختيار المشروع.", $result);
    }

    public function testAddTaskSuccess() {
        $this->conn->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);

        $result = addTask(1, "عنوان", "وصف", "2025-01-01", 2, $this->conn);
        $this->assertEquals("تمت إضافة المهمة بنجاح.", $result);
    }

    public function testAddTaskFails() {
        $this->conn->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(false);

        $result = addTask(1, "عنوان", "وصف", "2025-01-01", 2, $this->conn);
        $this->assertEquals("حدث خطأ أثناء إضافة المهمة.", $result);
    }
}
