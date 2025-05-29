<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// استرجاع المشاريع الخاصة بالطالب مع التقييمات
$stmt = $conn->prepare("
    SELECT project.*, evaluations.evaluation_score
    FROM project
    LEFT JOIN evaluations ON project.id = evaluations.project_id
    WHERE project.student_id = ? OR project.student_id_2 = ?
");
$stmt->execute([$user_id, $user_id]);
$projects = $stmt->fetchAll();

// استرجاع زميل المشروع والمشرف
$project_info_stmt = $conn->prepare("SELECT student_id, student_id_2, manager_id FROM project WHERE student_id = ? OR project.student_id_2 = ?");
$project_info_stmt->execute([$user_id, $user_id]);
$project_data = $project_info_stmt->fetch(PDO::FETCH_ASSOC);

$colleague_id = null;
$supervisor_id = null;

if ($project_data) {
    $colleague_id = ($project_data['student_id'] == $user_id) ? $project_data['student_id_2'] : $project_data['student_id'];
    $supervisor_id = $project_data['manager_id'];
}

$colleague_name = $supervisor_name = '';
if (!empty($colleague_id)) {
    $stmt = $conn->prepare("SELECT name FROM user WHERE id = ?");
    $stmt->execute([$colleague_id]);
    $colleague_name = $stmt->fetchColumn() ?: 'غير متوفر';
}
if (!empty($supervisor_id)) {
    $stmt = $conn->prepare("SELECT name FROM user WHERE id = ?");
    $stmt->execute([$supervisor_id]);
    $supervisor_name = $stmt->fetchColumn() ?: 'غير متوفر';
}

// 🔔 استرجاع عدد الإشعارات غير المقروءة
$notifQuery = $conn->prepare("SELECT type, COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0 GROUP BY type");
$notifQuery->execute([$user_id]);
$notifications = $notifQuery->fetchAll(PDO::FETCH_ASSOC);

$taskCount = 0;
$messageCount = 0;

foreach ($notifications as $notif) {
    if ($notif['type'] == 'task') $taskCount = $notif['count'];
    elseif ($notif['type'] == 'message') $messageCount = $notif['count'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $receivers = $_POST['receivers'] ?? [];

    foreach ($receivers as $receiver_id) {
        // إدخال الرسالة أولاً
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $receiver_id, $message]);
        $message_id = $conn->lastInsertId();

        // 📨 إضافة إشعار للرسالة
        $notif = $conn->prepare("INSERT INTO notifications (user_id, type, reference_id, message) VALUES (?, 'message', ?, ?)");
        $notif->execute([$receiver_id, $message_id, 'رسالة جديدة من الطالب']);
    }

    $success = "تم إرسال الرسالة بنجاح.";
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الطالب</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dropdown {
            position: relative;
        }

        .dropdown-toggle {
            cursor: pointer;
            display: block;
            padding: 15px 20px;
            color: white;
            font-size: 1.2rem;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .dropdown-toggle:hover {
            background-color: #2980b9;
        }

        .dropdown-menu {
            display: none;
            flex-direction: column;
            background-color: #2c3e50;
        }

        .dropdown-menu a {
            padding: 12px 25px;
            font-size: 1.1rem;
            color: white;
            text-decoration: none;
        }

        .dropdown-menu a:hover {
            background-color: #2980b9;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            direction: rtl;
        }

        .container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-right: 270px;
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

        .sidebar {
            position: fixed;
            top: 0;
            right: 0;
            background-color: #34495e;
            width: 250px;
            height: 100%;
            color: white;
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

        .sidebar a:hover, .sidebar a.active {
            background-color: #2980b9;
        }

        .message-success {
            color: green;
            text-align: center;
            margin: 10px 0;
        }

        .chat-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 1px solid #ccc;
            z-index: 1000;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            border-radius: 10px;
            width: 320px;
        }

        .form-container {
            padding: 20px;
        }

        .form-container h3 {
            margin-top: 0;
            color: #2980b9;
            text-align: center;
        }

        .form-container textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            resize: none;
        }

        .form-container .btn {
            width: 48%;
            padding: 10px;
            margin: 2px 1%;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-container .btn:hover {
            opacity: 0.9;
        }

        .form-container .btn.cancel {
            background-color: #e74c3c;
            color: white;
        }

        .form-container .btn:not(.cancel) {
            background-color: #27ae60;
            color: white;
        }

        .receiver-option {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 10px;
        }

        .icon-container {
            position: relative;
            display: inline-block;
        }

        .badge {
            position: absolute;
            top: 6px;
            right: 15px;
            background: red;
            color: white;
            border-radius: 50%;
            font-size: 12px;
            padding: 2px 6px;
        }
        .main-page{
            width:90%;
            display:flex;
            justify-content: space-between;
            padding-left:4rem;
            align-items: center;
            
        }
        .icon{
            color:#2980b9;
            height:30px;
            width:30px;

        }

    </style>
</head>
<body>

<div class="sidebar">
    <div class="main-page" >
    <a href="student_dashboard.php">الصفحة الرئيسية</a>
        <a href="notifications.php">🔔</a>

</div>

    <div class="dropdown">
        <a href="#" class="dropdown-toggle icon-container">📬 الرسائل
            <?php if ($messageCount > 0): ?>
                <span class="badge"><?= $messageCount ?></span>
            <?php endif; ?>
        </a>
        <div class="dropdown-menu">
            <a href="#" id="toggleMessageForm">📩 إرسال رسالة</a>
            <a href="student_messages_log.php">📨 سجل الرسائل</a>
        </div>
    </div>

    <a href="student_tasks.php">🗒️ المهام
        <?php if ($taskCount > 0): ?>
            <span class="badge"><?= $taskCount ?></span>
        <?php endif; ?>
    </a>
    <a href="logout.php">تسجيل خروج</a>
</div>

<div class="container">
    <h2>مشاريع الطالب</h2>

    <table>
        <thead>
        <tr>
            <th>اسم المشروع</th>
            <th>الوصف</th>
            <th>تقييم المشروع</th>
        </tr>
        </thead>
        <tbody>
        <?php if (count($projects) > 0): ?>
            <?php foreach ($projects as $project): ?>
                <tr>
                    <td><?= htmlspecialchars($project['title']) ?></td>
                    <td><?= htmlspecialchars($project['description']) ?></td>
                    <td><?= isset($project['evaluation_score']) ? $project['evaluation_score'] : '-' ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3">لا توجد مشاريع مسجلة.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div>
        <p><strong>زميل المشروع:</strong> <?= htmlspecialchars($colleague_name) ?></p>
        <p><strong>المشرف:</strong> <?= htmlspecialchars($supervisor_name) ?></p>
    </div>

    <?php if (!empty($success)): ?>
        <p class="message-success"><?= $success ?></p>
    <?php endif; ?>

</div>

<div class="chat-popup" id="messageForm">
    <form method="post" class="form-container">
        <h3>إرسال رسالة</h3>
        <label>الرسالة</label>
        <textarea name="message" rows="5" required></textarea>
        <label>المستلمون:</label><br>

        <div class="receiver-option">
            <input type="checkbox" id="receiver_colleague" name="receivers[]" value="<?= $colleague_id ?>" />
            <label for="receiver_colleague">زميل المشروع (<?= htmlspecialchars($colleague_name) ?>)</label>
        </div>
        <div class="receiver-option">
            <input type="checkbox" id="receiver_supervisor" name="receivers[]" value="<?= $supervisor_id ?>" />
            <label for="receiver_supervisor">المشرف (<?= htmlspecialchars($supervisor_name) ?>)</label>
        </div>

        <button type="submit" class="btn">إرسال</button>
        <button type="button" class="btn cancel" id="closeForm">إلغاء</button>
    </form>
</div>

<script>
    // التحكم بفتح وإغلاق نموذج الإرسال
    document.getElementById('toggleMessageForm').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('messageForm').style.display = 'block';
    });
    document.getElementById('closeForm').addEventListener('click', function() {
        document.getElementById('messageForm').style.display = 'none';
    });

    // قائمة منسدلة للرسائل في الشريط الجانبي
    document.querySelectorAll('.dropdown-toggle').forEach(function(elem) {
        elem.addEventListener('click', function(e) {
            e.preventDefault();
            let menu = this.nextElementSibling;
            if (menu.style.display === 'flex') {
                menu.style.display = 'none';
            } else {
                menu.style.display = 'flex';
            }
        });
    });
</script>

</body>
</html>
