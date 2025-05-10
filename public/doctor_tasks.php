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

    // حذف المهمة
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_task'])) {
        // التحقق من وجود task_id في POST
        if (isset($_POST['task_id']) && !empty($_POST['task_id'])) {
            $task_id = $_POST['task_id'];

            // استعلام لحذف المهمة
            $deleteQuery = "DELETE FROM task WHERE task_id = :task_id";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':task_id', $task_id);
            $deleteStmt->execute();

            // إعادة التوجيه إلى صفحة المهام بعد الحذف
            header("Location: doctor_tasks.php");
            exit();
        }
    }

    // جلب المشاريع المتاحة للدكتور باستخدام manager_id
    $doctor_id = $_SESSION['user_id']; // الحصول على معرّف الدكتور من الجلسة
    $query = "SELECT * FROM project WHERE manager_id = :doctor_id"; // استخدام manager_id بدلاً من doctor_id
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':doctor_id', $doctor_id);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // جلب المهام المتعلقة بالدكتور
    $query_tasks = "SELECT * FROM task WHERE doctor_id = :doctor_id";
    $stmt_tasks = $conn->prepare($query_tasks);
    $stmt_tasks->bindParam(':doctor_id', $doctor_id);
    $stmt_tasks->execute();
    $tasks = $stmt_tasks->fetchAll(PDO::FETCH_ASSOC);

    // إضافة مهمة جديدة
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_task'])) {
        // تحقق من وجود project_id
        if (!isset($_POST['project_id']) || empty($_POST['project_id'])) {
            $error_message = "يجب اختيار المشروع.";
        } else {
            $task_title = htmlspecialchars($_POST['task_title']);
            $task_description = htmlspecialchars($_POST['task_description']);
            $due_date = $_POST['due_date'];
            $project_id = $_POST['project_id']; // إضافة project_id للمهمة

            $addQuery = "INSERT INTO task (doctor_id, task_title, task_description, due_date, project_id) 
                         VALUES (:doctor_id, :task_title, :task_description, :due_date, :project_id)";
            $addStmt = $conn->prepare($addQuery);
            $addStmt->bindParam(':doctor_id', $doctor_id);
            $addStmt->bindParam(':task_title', $task_title);
            $addStmt->bindParam(':task_description', $task_description);
            $addStmt->bindParam(':due_date', $due_date);
            $addStmt->bindParam(':project_id', $project_id); // ربط المهمة بالمشروع
            $addStmt->execute();

            header("Location: doctor_tasks.php"); // إعادة التوجيه بعد إضافة المهمة
            exit();
        }
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
    <title>إدارة المهام - دكتور</title>
    <style>
        /* نفس التنسيق السابق */
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

        .add-task-form, .update-task-form {
            margin: 20px 0;
            padding: 20px;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .add-task-form input, .update-task-form input, .add-task-form textarea, .update-task-form textarea, .add-task-form select, .update-task-form select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>إدارة المهام</h2>
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
                <h2>إدارة المهام</h2>
            </div>

            <!-- إضافة مهمة جديدة -->
            <div class="add-task-form">
                <h3>إضافة مهمة جديدة</h3>
                <form method="POST" action="">

                    <input type="text" name="task_title" placeholder="عنوان المهمة" required>
                    <textarea name="task_description" placeholder="وصف المهمة" required></textarea>
                    <input type="date" name="due_date" required>

                    <!-- إضافة خيار لاختيار المشروع -->
                    <select name="project_id" required>
                        <option value="">اختار المشروع</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>"><?php echo $project['title']; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" name="add_task" class="btn btn-success">إضافة المهمة</button>
                </form>
            </div>

            <!-- عرض المهام -->
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
                        <?php if (empty($tasks)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center;">لا توجد مهام حالياً</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tasks as $task): ?>
                                <tr>
                                    <td><?php echo $task['task_title']; ?></td>
                                    <td><?php echo $task['project_id']; ?></td>
                                    <td><?php echo $task['due_date']; ?></td>
                                    <td><span class="status status-pending">معلق</span></td>
                                    <td>
                                        <a href="edit_task.php?task_id=<?php echo $task['task_id']; ?>" class="btn btn-primary">تعديل</a>
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="task_id" value="<?php echo $task['task_id']; ?>">
                                            <button type="submit" name="delete_task" class="btn btn-danger">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
