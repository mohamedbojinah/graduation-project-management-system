<?php
// تضمين ملف الاتصال بقاعدة البيانات
require_once '../config/config.php';  // الرجوع للمجلد الأعلى ثم إلى config/config.php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = $_POST['name'];
    $role = 'user';  // تعيين دور المستخدم كـ 'مستخدم' بشكل افتراضي

    // استعلام لإضافة المستخدم الجديد إلى الجدول
    $stmt = $conn->prepare("INSERT INTO Person (name, email, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $role);
    $stmt->execute();
    $userId = $stmt->insert_id;

    // إدخال بيانات كلمة المرور في جدول المستخدم
    $stmt = $conn->prepare("INSERT INTO User (personId, password) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $password);
    $stmt->execute();

    echo "تم تسجيل الحساب بنجاح!";
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل حساب جديد</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <form method="POST" action="register.php">
        <h2>إنشاء حساب جديد</h2>
        <input type="text" name="name" placeholder="الاسم الكامل" required>
        <input type="email" name="email" placeholder="البريد الإلكتروني" required>
        <input type="password" name="password" placeholder="كلمة المرور" required>
        <button type="submit">إنشاء حساب</button>
    </form>

</body>
</html>
