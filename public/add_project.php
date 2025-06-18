<?php
function addProject($projectTitle, $projectDescription, $studentId, $studentId2, $managerId, $conn) {
    // التحقق من وجود الطالب
    $stmt = $conn->prepare("SELECT * FROM user WHERE id = ? AND role = 'student'");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) {
        return "الطالب الذي أدخلت رقمه غير موجود.";
    }

    // التحقق من وجود المشرف
    $stmt = $conn->prepare("SELECT * FROM user WHERE id = ? AND role = 'doctor'");
    $stmt->execute([$managerId]);
    $manager = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$manager) {
        return "المشرف الذي أدخلت رقمه غير موجود.";
    }

    // التحقق من الزميل إذا وُجد
    if (!empty($studentId2)) {
        $stmt = $conn->prepare("SELECT * FROM user WHERE id = ? AND role = 'student'");
        $stmt->execute([$studentId2]);
        $colleague = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$colleague) {
            return "رقم الزميل غير صحيح أو غير موجود.";
        }
    }

    // إضافة المشروع
    $stmt = $conn->prepare("INSERT INTO project (title, description, status, startDate, endDate, student_id, student_id_2, manager_id)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([
        $projectTitle, $projectDescription, 'نشط', '2023-01-01', '2023-12-31',
        $studentId, $studentId2, $managerId
    ]);

    return $result ? "تم إضافة المشروع بنجاح." : "حدث خطأ أثناء إضافة المشروع.";
}
