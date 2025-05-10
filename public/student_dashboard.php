<?php
session_start();
include '../config/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// استرجاع معرف المستخدم (الطالب)
$user_id = $_SESSION['user_id']; // تأكد من أن هذا هو المعرف الصحيح للمستخدم

// استرجاع المشاريع الخاصة بالطالب مع التقييمات
$stmt = $conn->prepare("
    SELECT project.*, evaluations.evaluation_score
    FROM project
    LEFT JOIN evaluations ON project.id = evaluations.project_id
    WHERE project.student_id = ?
");
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الطالب</title>
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

        .container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-right: 270px; /* لتجنب تداخل المحتوى مع الشريط الجانبي */
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.8rem;
            color: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #2980b9;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .back-btn {
            display: block;
            width: 150px;
            margin: 20px auto;
            padding: 10px;
            background-color: #2980b9;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
        }

        .back-btn:hover {
            background-color: #3498db;
        }

        /* الشريط الجانبي */
        .sidebar {
            position: fixed;
            top: 0;
            right: 0; /* وضع الشريط الجانبي على اليمين */
            background-color: #34495e;
            width: 250px;
            height: 100%;
            color: white;
            box-shadow: -3px 0 10px rgba(0, 0, 0, 0.1);
            padding-top: 30px;
        }

        .sidebar a {
            display: block;
            padding: 15px 20px;
            text-decoration: none;
            color: white;
            font-size: 1.2rem;
            transition: background-color 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #2980b9;
        }

        .sidebar a.active {
            background-color: #2980b9;
        }

        .sidebar .back-btn {
            margin-top: 20px;
            background-color: #e74c3c;
        }

    </style>
</head>
<body>

<!-- الشريط الجانبي -->
<div class="sidebar">
    <a href="student_dashboard.php">الصفحة الرئيسية</a>
    <a href="completed_projects_student.php" class="active">مشاريع مكتملة</a>
    <a href="logout.php">تسجيل الخروج</a>
</div>

<!-- المحتوى الرئيسي -->
<div class="container">
    <h2>مشاريعك</h2>

    <?php if (count($projects) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>عنوان المشروع</th>
                    <th>الوصف</th>
                    <th>تاريخ البدء</th>
                    <th>تاريخ الانتهاء</th>
                    <th>الحالة</th>
                    <th>التقييم</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($project['title'] ?? 'غير محدد'); ?></td>
                        <td><?php echo htmlspecialchars($project['description'] ?? 'غير محدد'); ?></td>
                        <td><?php echo htmlspecialchars($project['start_date'] ?? 'غير محدد'); ?></td>
                        <td><?php echo htmlspecialchars($project['end_date'] ?? 'غير محدد'); ?></td>
                        <td><?php echo htmlspecialchars($project['status'] ?? 'غير محدد'); ?></td>
                        <td><?php echo ($project['evaluation_score'] !== null) ? htmlspecialchars($project['evaluation_score']) : 'لم يتم التقييم بعد'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>لا توجد مشاريع مخصصة لك حالياً.</p>
    <?php endif; ?>

</div>

</body>
</html>
