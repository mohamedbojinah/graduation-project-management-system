<?php
session_start();

// التحقق من صلاحية المستخدم (هل هو دكتور؟)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: index.php");
    exit();
}

// الاتصال بقاعدة البيانات
$host = '127.0.0.1';
$port = '3306';
$dbname = 'project_db';  // اسم قاعدة البيانات الصحيحة
$username = 'root';
$password = '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // حذف المهمة
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_task'])) {
        $task_id = $_POST['task_id'];

        // استعلام لحذف المهمة
        $deleteQuery = "DELETE FROM task WHERE task_id = :task_id";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':task_id', $task_id);
        $deleteStmt->execute();

        header("Location: doctor_tasks.php"); // إعادة التوجيه بعد الحذف
        exit();
    }

    // جلب المشاريع المتاحة للدكتور
    $doctor_id = $_SESSION['user_id'];
    $query = "SELECT * FROM project WHERE manager_id = :doctor_id";
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

} catch (PDOException $e) {
    echo "فشل الاتصال: " . $e->getMessage();
    exit();
}
?>
