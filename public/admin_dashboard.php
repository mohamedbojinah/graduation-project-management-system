<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../config/db.php';

// استعلام لعرض عدد المستخدمين والمشاريع
$stmt = $conn->prepare("SELECT COUNT(*) FROM user");
$stmt->execute();
$user_count = $stmt->fetchColumn();

$stmt = $conn->prepare("SELECT COUNT(*) FROM project");
$stmt->execute();
$project_count = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الأدمن</title>
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

        /* الحاوية الرئيسية للواجهة */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* الشريط الجانبي */
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

        .sidebar-menu i {
            margin-left: 12px;
            font-size: 1.3rem;
        }

        /* المحتوى الرئيسي */
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

        /* أزرار */
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

        .btn i {
            margin-left: 8px;
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

        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }

        .btn-warning:hover {
            background-color: #e67e22;
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #219955;
        }

        /* جدول المشاريع */
        .projects-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        .projects-table th, 
        .projects-table td {
            padding: 15px;
            text-align: right;
            border-bottom: 1px solid #eee;
        }

        .projects-table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }

        .projects-table tr:hover {
            background-color: #f9f9f9;
        }

        .projects-table .actions {
            display: flex;
            gap: 8px;
            justify-content: flex-start;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
        }

        .no-projects {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
            font-size: 1.1rem;
        }

        /* حالة المشاريع */
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

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        /* التجاوبية */
        @media (max-width: 992px) {
            .projects-table {
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

            .projects-table .actions {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- الشريط الجانبي -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>لوحة تحكم الأدمن</h2>
            </div>
            <ul class="sidebar-menu">
                <li class="active"><a href="admin_dashboard.php"><i class="fas fa-home"></i> الصفحة الرئيسية</a></li>
                <li><a href="manage_users.php"><i class="fas fa-users"></i> إدارة المستخدمين</a></li>
                <li><a href="manage_projects.php"><i class="fas fa-project-diagram"></i> إدارة المشاريع</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-line"></i> التقارير</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
            <div class="page-header">
                <h2>مرحبًا بك في لوحة تحكم الأدمن</h2>
            </div>

            <div class="stats">
                <div class="stat-box">
                    <h3>عدد المستخدمين</h3>
                    <p><?php echo $user_count; ?></p>
                </div>
                <div class="stat-box">
                    <h3>عدد المشاريع</h3>
                    <p><?php echo $project_count; ?></p>
                </div>
            </div>

            <div class="management-options">
                
            </div>
        </div>
    </div>

    <!-- إضافة مكتبة Font Awesome للأيقونات -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
