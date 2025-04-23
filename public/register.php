<?php
include '../config/db.php';
$message = "";
$errors = [];

function clean($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "البريد الإلكتروني غير صالح.";
    }

    if (strlen($password) < 6) {
        $errors[] = "كلمة المرور يجب أن تكون 6 حروف أو أكثر.";
    }

    if ($role !== 'student' && $role !== 'admin') {
        $errors[] = "يجب اختيار دور صالح.";
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "البريد الإلكتروني مستخدم من قبل.";
    }

    // إذا لا توجد أخطاء، نسجّل
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO user (name, email, password, role) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $hashed_password, $role])) {
            $message = "تم التسجيل بنجاح! يمكنك تسجيل الدخول الآن.";
        } else {
            $errors[] = "حدث خطأ أثناء التسجيل.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إنشاء حساب</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="form-container">
    <h2>إنشاء حساب</h2>
    <form method="post" action="">
        <input type="text" name="name" placeholder="الاسم الكامل" required>
        <input type="email" name="email" placeholder="البريد الإلكتروني" required>
        <input type="password" name="password" placeholder="كلمة المرور" required>
        <select name="role" required>
            <option value="">اختر الدور</option>
            <option value="student">طالب</option>
            <option value="admin">مشرف</option>
        </select>
        <button type="submit">تسجيل</button>
    </form>
    <?php
    if (!empty($errors)) {
        echo "<ul style='color:red;'>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
    } elseif ($message) {
        echo "<p style='color:green;'>$message</p>";
        // زر الرجوع
        echo "<p><a href='login.php'><button>رجوع إلى صفحة تسجيل الدخول</button></a></p>";
    }
    ?>
</div>
</body>
</html>
