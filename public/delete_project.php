<?php
session_start();

// التأكد من أن المستخدم هو "أدمن"
if (!isset($_SESSION['role'])  $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../config/db.php';

// تحقق من وجود الـ id في الـ GET
if (!isset($_GET['id'])  empty($_GET['id'])) {
    die("المشروع غير موجود");
}

$id = $_GET['id'];

// استعلام لحذف المشروع من قاعدة البيانات
$stmt = $conn->prepare("DELETE FROM project WHERE id = ?");
$stmt->execute([$id]);

// إعادة التوجيه إلى صفحة إدارة المشاريع بعد الحذف
header("Location: manage_projects.php");
eie();
?>
