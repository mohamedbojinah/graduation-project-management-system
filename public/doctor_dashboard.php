<?php
session_start();

// التحقق من صلاحية المستخدم (هل هو دكتور؟)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: index.php");
    exit();
}

// الاتصال بقاعدة البيانات
$host = '127.0.0.1';
$port = '3307';
$dbname = 'project_db';  // اسم قاعدة البيانات الصحيحة
$username = 'root';
$password = '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // جلب بيانات الدكتور من قاعدة البيانات باستخدام id
    $doctor_id = $_SESSION['user_id']; // الحصول على معرّف الدكتور من الجلسة
    $query = "SELECT * FROM user WHERE id = :doctor_id"; // استخدام id بدلاً من user_id
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':doctor_id', $doctor_id);
    $stmt->execute();
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctor) {
        // إذا لم يتم العثور على بيانات الدكتور، التوجيه إلى صفحة تسجيل الخروج
        header("Location: logout.php");
        exit();
    }

} catch (PDOException $e) {
    echo "فشل الاتصال: " . $e->getMessage();
    exit();
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
            transition: all 0.3s;
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

        .sidebar-menu li.active a {
            background-color: rgba(0, 0, 0, 0.2);
            border-right-color: var(--active-color);
            font-weight: 500;
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
            font-weight: 500;
        }

        .tasks-table tr:hover {
            background-color: #f9f9f9;
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

        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-family: 'Tajawal', sans-serif;
            font-size: 1rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background-color: var(--active-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #3498db;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #219955;
        }

        @media (max-width: 992px) {
            .tasks-table {
                display: block;
                overflow-x: auto;
            }
        }

        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                min-height: auto;
            }

            .sidebar-menu {
                display: flex;
                flex-wrap: wrap;
            }

            .sidebar-menu li {
                flex: 1 0 auto;
            }

            .sidebar-menu li a {
                justify-content: center;
                border-right: none;
                border-bottom: 3px solid transparent;
            }

            .sidebar-menu li a:hover {
                border-right: none;
                border-bottom-color: var(--active-color);
            }

            .sidebar-menu li.active a {
                border-right: none;
                border-bottom-color: var(--active-color);
            }

            .tasks-table .actions {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>لوحة تحكم الدكتور</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="doctor_dashboard.php">الصفحة الرئيسية</a></li>
                <li><a href="doctor_tasks.php">إدارة المهام</a></li>
                <li><a href="evaluation.php"> التقييمات</a></li>
                <li><a href="doctor_profile.php">الملف الشخصي</a></li>
                <li><a href="logout.php" class="btn btn-danger">تسجيل الخروج</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h2>مرحبًا، دكتور <?php echo $doctor['name']; ?>!</h2>
            </div>

            <div class="doctor-info">
                <h3>الملف الشخصي</h3>
                <p><strong>البريد الإلكتروني:</strong> <?php echo $doctor['email']; ?></p>
            
                <p><strong>المشاريع المكلف بها:</strong> 3 مشاريع</p>
            </div>

            <div class="doctor-tasks">
                <h3>المهام المكلف بها</h3>
                <table class="tasks-table">
                    <thead>
                        <tr>
                            <th>المهمة</th>
                            <th>المشروع</th>
                            <th>تاريخ الاستحقاق</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>كتابة تقرير البحث</td>
                            <td>مشروع تطوير الموقع</td>
                            <td>2025-05-10</td>
                            <td><span class="status status-active">قيد التنفيذ</span></td>
                            <td><a href="edit_task.php?task_id=1" class="btn btn-primary">تعديل</a></td>
                        </tr>
                        <tr>
                            <td>مراجعة التعليمات البرمجية</td>
                            <td>مشروع تحليل البيانات</td>
                            <td>2025-05-15</td>
                            <td><span class="status status-pending">معلق</span></td>
                            <td><a href="edit_task.php?task_id=2" class="btn btn-primary">تعديل</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
