<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../doctor_dashboard_handler.php';

class DoctorDashboardTest extends TestCase {
    private $conn;
    private $stmt;

    protected function setUp(): void {
        $this->conn = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);

        $this->conn->method('prepare')->willReturn($this->stmt);
    }

    public function testGetDoctorProjects() {
        $expected = [
            ['id' => 1, 'title' => 'مشروع التخرج', 'description' => 'تفاصيل', 'status' => 'نشط']
        ];

        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetchAll')->willReturn($expected);

        $result = getDoctorProjects(101, $this->conn);
        $this->assertEquals($expected, $result);
    }
}
