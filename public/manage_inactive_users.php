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

// استعلام لعرض الحسابات التي تكون حالتها false أو NULL مع دعم البحث عن اسم المستخدم أو رقم الـ ID
if ($search_query) {
    $stmt = $conn->prepare("SELECT * FROM user WHERE (name LIKE ? OR id LIKE ?) AND (state = false OR state IS NULL)");
    $stmt->execute(['%' . $search_query . '%', '%' . $search_query . '%']);
} else {
    $stmt = $conn->prepare("SELECT * FROM user WHERE state = false OR state IS NULL");
    $stmt->execute();
}

$users = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // تحديث حالة المستخدم إلى true
    $update_stmt = $conn->prepare("UPDATE user SET state = true WHERE id = ?");
    if ($update_stmt->execute([$user_id])) {
        $message = "تم تفعيل الحساب بنجاح!";
    } else {
        $error = "حدث خطأ أثناء تفعيل الحساب.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الحسابات غير المفعلّة</title>
    
    <style>
        /* إضافة التنسيق الخاص بالصفحة الجديدة */
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: rgb(255, 255, 255);
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

        .sidebar {
            background-color: #2c3e50; /* اللون المنسق */
            width: 300px;
            min-height: 100vh;
            border-radius: 0 20px 20px 0;
            box-shadow: 2px 0 15px rgba(172, 175, 5, 0.1);
            padding: 20px;
            color: white;
        }

        .sidebar-header {
            text-align: center;
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
            color: rgb(255, 255, 255);
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

        .users-table-container {
            margin-top: 30px;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            background-color: rgb(113, 119, 119);
            border-radius: 10px;
            overflow: hidden;
        }

        .users-table th, .users-table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
.users-table th {
            background-color: #2980b9;
            color: white;
        }

        .users-table tr:hover {
            background-color: rgb(87, 146, 255);
        }

        .users-table .actions a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 5px;
        }

        .users-table .actions .btn-activate {
            background-color: #2ecc71; /* أخضر */
        }

        .users-table .actions .btn-activate:hover {
            background-color: #27ae60;
        }

        /* خانة البحث */
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
            background-color: #ecf0f1; /* الخلفية الرمادية الفاتحة */
            color: #333;
        }

        .search-bar input:focus {
            border-color: #2980b9;
            background-color: #fff;
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
            <li><a href="manage_projects.php"><i class="fas fa-project-diagram"></i> إدارة المشاريع</a></li>
            <li class="active"><a href="manage_inactive_users.php"><i class="fas fa-users"></i> إدارة الحسابات غير المفعلّة</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
        </ul>
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="main-content">
        <div class="page-header">
            <h2>إدارة الحسابات غير المفعلّة</h2>
        </div>

        <!-- خانة البحث -->
        <div class="search-bar">
            <form method="GET" action="manage_inactive_users.php">
                <input type="text" name="search" placeholder="ابحث عن مستخدم أو رقم معرف..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="btn btn-primary">بحث</button>
            </form>
        </div>

        <div class="users-table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>اسم المستخدم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الدور</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td class="actions">
                                <form method="POST" action="">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn-activate">تفعيل الحساب</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <a href="admin_dashboard.php" class="back-btn">الرجوع إلى الصفحة الرئيسية</a>
    </div>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
