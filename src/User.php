<?php
// User.php - فئة إدارة المستخدمين
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // وظيفة لتسجيل المستخدم
    public function register($name, $email, $password) {
        $stmt = $this->conn->prepare("INSERT INTO Person (name, email, role) VALUES (?, ?, ?)");
        $role = 'user';  // تحديد دور المستخدم
        $stmt->bind_param("sss", $name, $email, $role);
        $stmt->execute();
        return $stmt->insert_id;
    }

    // وظيفة لتسجيل الدخول
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM Person WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();  // إرجاع بيانات المستخدم
    }
}
?>
