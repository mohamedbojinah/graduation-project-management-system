<?php
session_start();

// التأكد من أن المستخدم هو "أدمن"
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../config/db.php';

// التحقق من وجود كلمة البحث في الـ GET
$search_query = "";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
}

// استعلام لعرض قائمة المشاريع مع دعم البحث عن طريق اسم المشروع أو اسم الطالب
if ($search_query) {
    $stmt = $conn->prepare("
        SELECT p.*, s.name as student_name
        FROM project p
        LEFT JOIN student s ON p.id = s.project_id
        WHERE p.title LIKE ? OR s.name LIKE ?
    ");
    $stmt->execute(['%' . $search_query . '%', '%' . $search_query . '%']);
} else {
    $stmt = $conn->prepare("SELECT * FROM project");
    $stmt->execute();
}

$projects = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المشاريع</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color:rgb(255, 255, 255);
            color: #333;
            direction: rtl;
            font-size: 16px;
            margin: 0;
            padding: 0;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
            margin: 0;
        }

        /* الشريط الجانبي */
        .sidebar {
            background-color: #34495e;
            width: 300px;
            min-height: 100vh;
            border-radius: 0 20px 20px 0;
            box-shadow: 2px 0 15px rgba(172, 175, 5, 0.1);
            padding: 20px;
        }

        .sidebar-header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .sidebar-header h2 {
            font-size: 2rem;
            font-weight: bold;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
        }

        .sidebar-menu li a {
            color:rgb(255, 255, 255);
            padding: 15px 25px;
            display: block;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .sidebar-menu li a:hover {
            background-color: #2980b9;
        }

        /* المحتوى الرئيسي */
        .main-content {
            flex: 1;
            padding: 40px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-left: 300px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 2rem;
            color: #2c3e50;
        }

        .search-bar {
            margin-bottom: 20px;
            text-align: right;
        }

        .search-bar input {
            width: 300px;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: border-color 0.3s;
        }

        .search-bar input:focus {
            border-color: #2980b9;
        }

        /* جدول المشاريع */
        .projects-table-container {
            margin-top: 30px;
        }

        .projects-table {
            width: 100%;
            border-collapse: collapse;
            background-color:rgb(113, 119, 119);
            border-radius: 10px;
            overflow: hidden;
        }

        .projects-table th, .projects-table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .projects-table th {
            background-color: #2980b9;
            color: white;
        }

        .projects-table tr:hover {
            background-color:rgb(87, 146, 255);
        }

        .projects-table .actions a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 5px;
        }

        .projects-table .actions .btn-edit {
            background-color: #3498db; /* الأزرق الفاتح */
        }

        .projects-table .actions .btn-edit:hover {
            background-color: #2980b9; /* الأزرق الداكن عند المرور */
        }

        .projects-table .actions .btn-delete {
            background-color: #3498db; /* الأزرق الفاتح */
        }

        .projects-table .actions .btn-delete:hover {
            background-color: #2980b9; /* الأزرق الداكن عند المرور */
        }

        .back-btn {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            margin-top: 30px;
            display: inline-block;
        }

        .back-btn:hover {
            background-color: #2980b9;
        }

        .add-project-btn {
            background-color: #27ae60;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1.2rem;
            display: inline-block;
            margin-bottom: 20px;
        }

        .add-project-btn:hover {
            background-color: #2ecc71;
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
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> الصفحة الرئيسية</a></li>
                
                <li class="active"><a href="manage_projects.php"><i class="fas fa-project-diagram"></i> إدارة المشاريع</a></li>
                
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
            </ul>
        </div>

        <!-- المحتوى الرئيسي -->
        <div class="main-content">
            <div class="page-header">
                <h2>إدارة المشاريع</h2>
                <a href="add_project.php" class="add-project-btn"><i class="fas fa-plus"></i> إضافة مشروع</a>
            </div>

            <!-- مربع البحث -->
            <div class="search-bar">
                <form method="GET" action="manage_projects.php">
                    <input type="text" name="search" placeholder="ابحث عن مشروع أو طالب..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="btn btn-primary">بحث</button>
                </form>
            </div>

            <!-- عرض المشاريع -->
            <div class="projects-table-container">
                <table class="projects-table">
                    <thead>
                        <tr>
                            <th>اسم المشروع</th>
                            <th>تاريخ البدء</th>
                            <th>تاريخ الانتهاء</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($project['title']); ?></td>
                                <td><?php echo htmlspecialchars($project['startDate']); ?></td>
                                <td>
                                    <?php
                                    // التحقق من وجود تاريخ الانتهاء
                                    echo isset($project['endDate']) ? htmlspecialchars($project['endDate']) : 'غير محدد';
                                    ?>
                                </td>
                                <td class="status <?php echo strtolower($project['status']); ?>">
                                    <?php echo htmlspecialchars($project['status']); ?>
                                </td>
                                <td class="actions">
                                    <?php if (isset($project['id'])): ?>
                                        <a href="edit_project.php?id=<?php echo $project['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> تعديل</a>
                                        <a href="delete_project.php?id=<?php echo $project['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من حذف هذا المشروع؟')"><i class="fas fa-trash"></i> حذف</a>
                                    <?php else: ?>
                                        <span>لا يمكن تعديل هذا المشروع</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- زر الرجوع إلى الصفحة الرئيسية -->
            <a href="admin_dashboard.php" class="back-btn">الرجوع إلى الصفحة الرئيسية</a>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
