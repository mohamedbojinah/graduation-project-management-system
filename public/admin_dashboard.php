<?php
session_start();
include '../config/config.php'; // تأكد من أنك قمت بتضمين إعدادات الاتصال بقاعدة البيانات

// التحقق من أن المستخدم هو إدمن
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php"); // إذا لم يكن إدمن، إعادة توجيه إلى صفحة تسجيل الدخول
    exit();
}

// استعلام لعرض جميع المشاريع
$sql = "SELECT * FROM Project";
$result = $conn->query($sql);

echo "<h1>لوحة تحكم الإدمن</h1>";
echo "<h2>إدارة المشاريع</h2>";

if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr><th>اسم المشروع</th><th>الوصف</th><th>تاريخ البدء</th><th>تاريخ الانتهاء</th><th>إجراءات</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["title"] . "</td><td>" . $row["description"] . "</td><td>" . $row["startDate"] . "</td><td>" . $row["endDate"] . "</td>
              <td><a href='edit_project.php?id=" . $row["projectId"] . "'>تعديل</a> | <a href='delete_project.php?id=" . $row["projectId"] . "'>حذف</a></td></tr>";
    }
    echo "</table>";
} else {
    echo "لا توجد مشاريع حالياً.";
}
?>
