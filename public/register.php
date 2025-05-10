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
    $id = $_POST['id'];  // رقم الطالب أو رقم الدكتور

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "البريد الإلكتروني غير صالح.";
    }

    if (strlen($password) < 6) {
        $errors[] = "كلمة المرور يجب أن تكون 6 حروف أو أكثر.";
    }

    if ($role !== 'student' &&  $role != 'doctor') {
        $errors[] = "يجب اختيار دور صالح.";
    }

    if (empty($id)) {
        $errors[] = "يجب إدخال رقم الطالب أو رقم الوظيفة.";
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
        $stmt = $conn->prepare("INSERT INTO user (name, email, password, role, id, state) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $hashed_password, $role, $id, 0])) {
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
        <input type="text" name="name" placeholder="الاسم الكامل" required value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
        <input type="email" name="email" placeholder="البريد الإلكتروني" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" minlength="5" maxlength="255">
        <input type="password" name="password" placeholder="كلمة المرور" required minlength="6" maxlength="255">
        
        <select name="role" required>
            <option value="">اختر الدور</option>
            <option value="student" <?php echo (isset($role) && $role == 'student') ? 'selected' : ''; ?>>طالب</option>
            <option value="doctor" <?php echo (isset($role) && $role == 'doctor') ? 'selected' : ''; ?>>دكتور</option>
           
        </select>

        <input type="text" name="id" placeholder="ادخل رقم المستخدم" value="<?php echo isset($id) ? htmlspecialchars($id) : ''; ?>" required>

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
        echo "<p><a href='login.php'><button>رجوع إلى صفحة تسجيل الدخول</button></a></p>";
    }
    ?>
</div>
</body>
</html>
