<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../message_handler.php';

class ReceivedMessagesTest extends TestCase {
    private $conn;
    private $stmt;

    protected function setUp(): void {
        $this->conn = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);

        $this->conn->method('prepare')->willReturn($this->stmt);
    }

    public function testReturnsMessages() {
        $expected = [
            ['id' => 1, 'message' => 'أهلاً', 'sender_name' => 'أحمد', 'sent_at' => '2025-06-01']
        ];

        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetchAll')->willReturn($expected);

        $result = getReceivedMessages(101, $this->conn);
        $this->assertEquals($expected, $result);
    }
}
