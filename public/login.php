<?php
session_start();
include '../config/db.php';
$error = "";

function clean($data) {
    return htmlspecialchars(trim($data));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = clean($_POST['email']);
    $password = clean($_POST['password']);

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "صيغة البريد الإلكتروني غير صحيحة.";
    } elseif (strlen($password) < 6) {
        $error = "كلمة المرور يجب أن تكون على الأقل 6 حروف.";
    } else {
        // التحقق من الحساب
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // التحقق من كلمة المرور
            if (password_verify($password, $user['password'])) {
                // التحقق من حالة الحساب (هل تم تفعيله؟)
                if ($user['state'] == 0) { // إذا كانت القيمة 0 الحساب غير مفعل
                    $error = "لم يتم تفعيل الحساب الخاص بك بعد.";
                } else {
                    // إذا كانت الحالة مفعلة، نبدأ الجلسة
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];

                    // توجيه المستخدم إلى لوحة التحكم المناسبة بناءً على الدور
                    if ($user['role'] === 'admin') {
                        header("Location: admin_dashboard.php");
                    } elseif ($user['role'] === 'doctor') {
                        header("Location: doctor_dashboard.php "); // التوجيه إلى صفحة المعلم
                    } else {
                        header("Location: student_dashboard.php");
                    }
                    exit();
                }
            } else {
                $error = "البريد أو كلمة المرور غير صحيحة.";
            }
        } else {
            $error = "البريد الإلكتروني غير موجود.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            direction: rtl;
            font-size: 16px;
            margin: 0;
            padding: 0;
        }

        .form-container {
            width: 100%;
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.8rem;
            color: #2980b9;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        input[type="email"] {
            margin-top: 20px;
        }

        input[type="password"] {
            margin-top: 10px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #2980b9;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
        }

        button:hover {
            background-color: #3498db;
        }

        p {
            text-align: center;
        }

        a {
            color: #2980b9;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            font-size: 0.9rem;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>تسجيل الدخول</h2>
    <form method="POST" action="">
        <input type="email" name="email" placeholder="البريد الإلكتروني" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" minlength="5" maxlength="255">
        <input type="password" name="password" placeholder="كلمة المرور" required minlength="6" maxlength="255">
        <button type="submit">دخول</button>
    </form>
    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <p>ليس لديك حساب؟ <a href="register.php">أنشئ حساب</a></p>
</div>
</body>
</html>
