<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../notification_handler.php';

class NotificationTest extends TestCase {
    private $conn;
    private $stmt;

    protected function setUp(): void {
        $this->conn = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->conn->method('prepare')->willReturn($this->stmt);
    }

    public function testGetUnreadNotifications() {
        $expected = [
            ['id' => 1, 'type' => 'task', 'message' => 'مهمة جديدة', 'created_at' => '2024-01-01']
        ];

        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetchAll')->willReturn($expected);

        $result = getUnreadNotifications(5, $this->conn);
        $this->assertEquals($expected, $result);
    }

    public function testMarkNotificationAsReadSuccess() {
        $this->stmt->method('execute')->willReturn(true);
        $result = markNotificationAsRead(1, 5, $this->conn);
        $this->assertTrue($result);
    }

    public function testMarkNotificationAsReadFails() {
        $this->stmt->method('execute')->willReturn(false);
        $result = markNotificationAsRead(1, 5, $this->conn);
        $this->assertFalse($result);
    }
}
