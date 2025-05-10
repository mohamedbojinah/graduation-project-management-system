<?php
session_start();



include '../config/db.php';

// التحقق من وجود كلمة البحث في الـ GET
$search_query = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
}

// استعلام لعرض المشاريع المكتملة
if ($search_query) {
    // البحث في عنوان المشروع أو اسم الطالب
    $stmt = $conn->prepare("
        SELECT p.*, u.name as student_name
        FROM project p
        LEFT JOIN user u ON p.student_id = u.id
        WHERE p.status = 'completed' AND (p.title LIKE ? OR u.name LIKE ?)
    ");
    $stmt->execute(['%' . $search_query . '%', '%' . $search_query . '%']);
} else {
    // استعلام لعرض المشاريع المكتملة
    $stmt = $conn->prepare("
        SELECT p.*, u.name as student_name
        FROM project p
        LEFT JOIN user u ON p.student_id = u.id
        WHERE p.status = 'completed'
    ");
    $stmt->execute();
}

$projects = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المشاريع المكتملة</title>
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

        .sidebar-menu i {
            margin-left: 12px;
            font-size: 1.3rem;
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

        .search-container {
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
        }

        .search-container input {
            padding: 12px;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 300px;
            transition: border-color 0.3s;
        }

        .search-container input:focus {
            border-color: #2980b9;
        }

        .search-container button {
            background-color: var(--active-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .search-container button:hover {
            background-color: #3498db;
        }

        .projects-table-container {
            margin-top: 30px;
        }

        .projects-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--white);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        .projects-table th, .projects-table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .projects-table th {
            background-color: var(--primary-color);
            color: white;
        }

        .projects-table tr:hover {
            background-color: #f9f9f9;
        }

        .back-btn {
            padding: 12px 25px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1.2rem;
            margin-top: 30px;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>لوحة تحكم الطالب </h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="student_dashboard.php"><i class="fas fa-home"></i> الصفحة الرئيسية</a></li>
                <li class="active"><a href="completed_projects_student.php"><i class="fas fa-check-circle"></i> المشاريع المكتملة</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h2>المشاريع المكتملة</h2>
            </div>

            <div class="search-container">
                <form method="GET" action="completed_projects_student.php">
                    <input type="text" name="search" placeholder="ابحث عن مشروع أو طالب..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit">بحث</button>
                </form>
            </div>

            <div class="projects-table-container">
                <table class="projects-table">
                    <thead>
                        <tr>
                            <th>اسم المشروع</th>
                            <th>اسم الطالب</th>
                            <th>تاريخ البدء</th>
                            <th>تاريخ الانتهاء</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($projects): ?>
                            <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($project['title']); ?></td>
                                    <td><?php echo htmlspecialchars($project['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($project['startDate']); ?></td>
                                    <td><?php echo isset($project['endDate']) ? htmlspecialchars($project['endDate']) : 'غير محدد'; ?></td>
                                    <td><?php echo htmlspecialchars($project['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5">لا توجد نتائج للبحث</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <a href="student_dashboard.php" class="back-btn">الرجوع إلى الصفحة الرئيسية</a>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
