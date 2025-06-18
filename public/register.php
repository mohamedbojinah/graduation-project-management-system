<?php
function clean($data) {
    return htmlspecialchars(trim($data));
}

function registerUser($name, $email, $password, $role, $id, $conn) {
    $errors = [];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "البريد الإلكتروني غير صالح.";
    }

    if (strlen($password) < 6) {
        $errors[] = "كلمة المرور يجب أن تكون 6 حروف أو أكثر.";
    }

    if ($role !== 'student' && $role !== 'doctor') {
        $errors[] = "يجب اختيار دور صالح.";
    }

    if (empty($id)) {
        $errors[] = "يجب إدخال رقم الطالب أو رقم الوظيفة.";
    }

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "البريد الإلكتروني مستخدم من قبل.";
    }

    if (!empty($errors)) {
        return $errors;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO user (name, email, password, role, id, state) VALUES (?, ?, ?, ?, ?, 0)");
    $success = $stmt->execute([$name, $email, $hashed_password, $role, $id]);

    return $success ? "تم التسجيل بنجاح!" : ["حدث خطأ أثناء التسجيل."];
}
