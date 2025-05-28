<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: index.php");
    exit();
}

$host = '127.0.0.1';
$port = '3307';
$dbname = 'project_db';
$username = 'root';
$password = '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $doctor_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT name FROM user WHERE id = ?");
    $stmt->execute([$doctor_id]);
    $doctor_name = $stmt->fetchColumn();

    $stmt = $conn->prepare("
        SELECT 
            m.message,
            m.sent_at,
            sender.name AS sender_name,
            receiver.name AS receiver_name
        FROM messages m
        JOIN user sender ON m.sender_id = sender.id
        JOIN user receiver ON m.receiver_id = receiver.id
        WHERE m.sender_id = :doctor_id OR m.receiver_id = :doctor_id
        ORDER BY m.sent_at DESC
    ");
    $stmt->execute(['doctor_id' => $doctor_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "فشل الاتصال: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>📨 سجل الرسائل</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --active-color: #2980b9;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
            --white: #fff;
            --text-color: #333;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f5f5;
            color: var(--text-color);
            direction: rtl;
            margin: 0;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            background-color: var(--secondary-color);
            width: 280px;
            min-height: 100vh;
            border-radius: 0 20px 20px 0;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 2rem;
            text-align: center;
        }

        .sidebar-header h2 {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--light-color);
            margin: 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
        }

        .sidebar-menu li a {
            color: var(--light-color);
            padding: 18px 30px;
            display: block;
            text-decoration: none;
            font-size: 1.1rem;
            transition: 0.3s;
        }

        .sidebar-menu li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-right: 4px solid var(--active-color);
        }

        .main-content {
            flex: 1;
            padding: 2rem;
        }

        h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--white);
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: var(--primary-color);
            color: var(--white);
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .no-messages {
            text-align: center;
            padding: 40px;
            font-size: 1.2rem;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }

        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: var(--active-color);
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>لوحة الدكتور</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="doctor_dashboard.php">🏠 الصفحة الرئيسية</a></li>
            <li><a href="doctor_tasks.php">🗂️ إدارة المهام</a></li>
            <li><a href="messages_log.php">📨 سجل الرسائل</a></li>
            <li><a href="evaluation.php">📊 التقييمات</a></li>
            <li><a href="doctor_profile.php">👤 الملف الشخصي</a></li>
            <li><a href="logout.php" style="color: #e74c3c;">🚪 تسجيل الخروج</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2>📨 سجل الرسائل - دكتور <?= htmlspecialchars($doctor_name) ?></h2>

        <?php if (empty($messages)): ?>
            <div class="no-messages">لا توجد رسائل لعرضها حالياً.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>المرسل</th>
                        <th>المستلم</th>
                        <th>الرسالة</th>
                        <th>تاريخ الإرسال</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td><?= htmlspecialchars($msg['sender_name']) ?></td>
                            <td><?= htmlspecialchars($msg['receiver_name']) ?></td>
                            <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
                            <td><?= htmlspecialchars($msg['sent_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="doctor_dashboard.php" class="back-link">⬅ العودة إلى لوحة التحكم</a>
    </div>
</div>
</body>
</html>
