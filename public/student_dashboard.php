<?php
session_start();
require_once '../config/config.php';  // الرجوع للمجلد الأعلى ثم إلى config/config.php

// التحقق من أن المستخدم هو الطالب
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// استعلام للحصول على مشاريع الطالب
$stmt = $conn->prepare("SELECT * FROM Project WHERE student_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$projects = $stmt->get_result();

// استعلام للحصول على المهام الخاصة بالطالب
$stmt2 = $conn->prepare("SELECT * FROM Task WHERE assignedTo = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$tasks = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الطالب</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>لوحة تحكم الطالب</h2>
    <nav>
        <ul>
            <li><a href="profile.php">الملف الشخصي</a></li>
            <li><a href="tasks.php">المهام</a></li>
            <li><a href="projects.php">المشاريع</a></li>
            <li><a href="reports.php">التقارير</a></li>
        </ul>
    </nav>

    <h3>مشاريعك:</h3>
    <ul>
        <?php while ($project = $projects->fetch_assoc()): ?>
            <li><?= $project['title'] ?> - <?= $project['status'] ?></li>
        <?php endwhile; ?>
    </ul>

    <h3>مهامك:</h3>
    <ul>
        <?php while ($task = $tasks->fetch_assoc()): ?>
            <li><?= $task['description'] ?> - <?= $task['status'] ?> (الموعد النهائي: <?= $task['dueDate'] ?>)</li>
        <?php endwhile; ?>
    </ul>

    <footer>
        <p>&copy; 2025 لوحة تحكم الطالب</p>
    </footer>
</body>
</html>
