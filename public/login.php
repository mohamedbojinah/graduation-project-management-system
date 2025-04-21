<?php
session_start();
include '../config/config.php';  // الاتصال بقاعدة البيانات

// إذا كان المستخدم قد قام بتسجيل الدخول بالفعل، نوجهه مباشرة إلى لوحة التحكم المناسبة
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_dashboard.php"); // توجيه الإدمن إلى لوحة تحكم الإدمن
    } else {
        header("Location: student_dashboard.php"); // توجيه الطالب إلى لوحة تحكم الطالب
    }
    exit();
}

// التحقق من بيانات تسجيل الدخول
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // استلام بيانات البريد الإلكتروني وكلمة المرور من النموذج
    $email = $_POST['email'];
    $password = $_POST['password'];

    // استعلام للتحقق من بيانات المستخدم
    $sql = "SELECT * FROM User WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // إذا تم العثور على المستخدم
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['personId']; // تخزين الشخص في الجلسة
        $_SESSION['role'] = $user['role']; // تخزين دور المستخدم (إدمن أو طالب)

        // التوجيه بناءً على الدور
        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php"); // إذا كان إدمن، توجيهه إلى لوحة تحكم الإدمن
        } else {
            header("Location: student_dashboard.php"); // إذا كان طالبًا، توجيهه إلى لوحة تحكم الطالب
        }
        exit();
    } else {
        echo "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
    }
}
?>

<!-- نموذج تسجيل الدخول -->
<form method="POST" action="login.php">
    <label for="email">البريد الإلكتروني:</label>
    <input type="email" name="email" required><br><br>
    
    <label for="password">كلمة المرور:</label>
    <input type="password" name="password" required><br><br>
    
    <input type="submit" value="تسجيل الدخول">
</form>
