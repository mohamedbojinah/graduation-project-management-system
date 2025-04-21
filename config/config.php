<?php
// المسار إلى db.php الموجود في src
require_once '../src/db.php';

$servername = "localhost";
$username = "root";  // اسم المستخدم لقاعدة البيانات
$password = "";      // كلمة المرور لقاعدة البيانات
$dbname = "project_db"; // اسم قاعدة البيانات

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
?>
