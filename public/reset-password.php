<?php
// تضمين ملف الاتصال بقاعدة البيانات
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // استعلام للتحقق من وجود البريد الإلكتروني في قاعدة البيانات
    $stmt = $conn->prepare("SELECT * FROM User JOIN Person ON User.personId = Person.personId WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // إرسال رمز إعادة تعيين كلمة المرور إلى البريد الإلكتروني
        // هنا يمكنك استخدام مكتبة PHPMailer لإرسال البريد
        echo "تم إرسال تعليمات إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.";
    } else {
        echo "البريد الإلكتروني غير موجود في النظام.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <form method="POST" action="reset-password.php">
        <h2>إعادة تعيين كلمة المرور</h2>
        <input type="email" name="email" placeholder="البريد الإلكتروني" required>
        <button type="submit">إرسال التعليمات</button>
    </form>

</body>
</html>
