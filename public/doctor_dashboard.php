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
    $query = "SELECT * FROM user WHERE id = :doctor_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':doctor_id', $doctor_id);
    $stmt->execute();
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctor) {
        header("Location: logout.php");
        exit();
    }

    // جلب المشاريع التي يشرف عليها الدكتور
    $project_stmt = $conn->prepare("SELECT * FROM project WHERE manager_id = ?");
    $project_stmt->execute([$doctor_id]);
    $projects = $project_stmt->fetchAll(PDO::FETCH_ASSOC);

    // إعداد قائمة الطلبة المرتبطين بالمشاريع
    $student_ids = [];
    foreach ($projects as $project) {
        if (!empty($project['student_id'])) $student_ids[] = $project['student_id'];
        if (!empty($project['student_id_2'])) $student_ids[] = $project['student_id_2'];
    }
    $student_ids = array_unique($student_ids);

    $students = [];
    if (count($student_ids)) {
        $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
        $stmt = $conn->prepare("SELECT id, name FROM user WHERE id IN ($placeholders)");
        $stmt->execute($student_ids);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // إرسال الرسالة
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
        $message = trim($_POST['message']);
        $receivers = $_POST['receivers'] ?? [];

        foreach ($receivers as $receiver_id) {
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$doctor_id, $receiver_id, $message]);
        }

        $success = "تم إرسال الرسالة بنجاح.";
    }

} catch (PDOException $e) {
    echo "فشل الاتصال: " . $e->getMessage();
    exit();
}

function getStatusClass($status) {
    switch (strtolower($status)) {
        case 'معلق':
        case 'pending':
            return 'status-pending';
        case 'مكتمل':
        case 'completed':
            return 'status-success';
        case 'نشط':
        case 'قيد التنفيذ':
        case 'active':
            return 'status-active';
        default:
            return 'status-default';
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم الدكتور</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --active-color: #2980b9;
            --danger-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --light-color: #ecf0f1;
            --text-color: #333;
            --white: #fff;
            --gray: #95a5a6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f5f5;
            color: var(--text-color);
            direction: rtl;
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
            background-color: var(--primary-color);
            color: var(--white);
            padding: 2rem;
            text-align: center;
        }

        .sidebar-header h2 {
            font-size: 1.8rem;
            font-weight: bold;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
        }

        .sidebar-menu li a {
            color: var(--light-color);
            padding: 18px 30px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s;
            border-right: 3px solid transparent;
            font-size: 1.2rem;
        }

        .sidebar-menu li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-right-color: var(--active-color);
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            background-color: var(--light-color);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #ddd;
        }

        .page-header h2 {
            color: var(--primary-color);
            font-size: 2rem;
            font-weight: 600;
        }

        .tasks-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        .tasks-table th, 
        .tasks-table td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }

        .tasks-table th {
            background-color: var(--primary-color);
            color: white;
        }

        .status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-success {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-default {
            background-color: #eeeeee;
            color: #333;
        }

        .message-success {
            color: green;
            margin-top: 15px;
            text-align: center;
        }

        .chat-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 999;
            background-color: white;
            padding: 20px;
            width: 350px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        .form-container h3 {
            margin-top: 0;
            color: #2980b9;
            text-align: center;
        }

        .receiver-option {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            gap: 10px;
        }

        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            resize: none;
        }

        .btn {
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            width: 48%;
        }

        .btn-success {
            background-color: #27ae60;
            color: white;
        }

        .btn-cancel {
            background-color: #e74c3c;
            color: white;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>لوحة تحكم الدكتور</h2>
        </div>
        <!-- ضمن القائمة الجانبية بعد "إرسال رسالة" -->
<ul class="sidebar-menu">
    <li><a href="doctor_dashboard.php">الصفحة الرئيسية</a></li>
    <li><a href="doctor_tasks.php">إدارة المهام</a></li>
    <li><a href="#" id="toggleMessageForm">📩 إرسال رسالة</a></li>
    <li><a href="messages_log.php">📨 سجل الرسائل</a></li> <!-- تمت إضافته هنا -->
    <li><a href="evaluation.php"> التقييمات</a></li>
    <li><a href="doctor_profile.php">الملف الشخصي</a></li>
    <li><a href="logout.php" class="btn btn-danger">تسجيل الخروج</a></li>
</ul>

    </div>

    <div class="main-content">
        <div class="page-header">
            <h2>مرحبًا، دكتور <?= $doctor['name']; ?>!</h2>
        </div>

        <?php if (isset($success)) echo "<p class='message-success'>$success</p>"; ?>

        <div class="doctor-info">
            <h3>المشاريع التي تُشرف عليها</h3>
            <?php if (count($projects) === 0): ?>
                <p>لا توجد مشاريع حالياً.</p>
            <?php else: ?>
                <table class="tasks-table">
                    <thead>
                        <tr>
                            <th>عنوان المشروع</th>
                            <th>الوصف</th>
                            <th>تاريخ البدء</th>
                            <th>تاريخ الانتهاء</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?= htmlspecialchars($project['title']) ?></td>
                                <td><?= htmlspecialchars($project['description']) ?></td>
                                <td><?= htmlspecialchars($project['startDate']) ?></td>
                                <td><?= htmlspecialchars($project['endDate']) ?></td>
                                <td><span class="status <?= getStatusClass($project['status']) ?>"><?= htmlspecialchars($project['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- نموذج إرسال رسالة -->
<div class="chat-popup" id="messageForm">
    <form method="POST" class="form-container">
        <h3>إرسال رسالة للطلاب</h3>
        <?php foreach ($students as $student): ?>
            <div class="receiver-option">
                <input type="checkbox" name="receivers[]" value="<?= $student['id'] ?>">
                <span><?= htmlspecialchars($student['name']) ?></span>
            </div>
        <?php endforeach; ?>
        <textarea name="message" placeholder="اكتب رسالتك هنا..." rows="4" required></textarea>
        <div style="display: flex; justify-content: space-between;">
            <button type="submit" class="btn btn-success">إرسال</button>
            <button type="button" class="btn btn-cancel" id="closeForm">إغلاق</button>
        </div>
    </form>
</div>

<script>
    document.getElementById("toggleMessageForm").addEventListener("click", function(e) {
        e.preventDefault();
        document.getElementById("messageForm").style.display = "block";
    });
    document.getElementById("closeForm").addEventListener("click", function() {
        document.getElementById("messageForm").style.display = "none";
    });
</script>
</body>
</html>
