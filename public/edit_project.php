<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../config/db.php';

if (isset($_GET['id'])) {
    $project_id = $_GET['id'];
} else {
    die("المشروع غير موجود");
}

$stmt = $conn->prepare("SELECT * FROM project WHERE id = ?");
$stmt->execute([$project_id]);
$project = $stmt->fetch();

if (!$project) {
    die("المشروع غير موجود في قاعدة البيانات.");
}

// جلب الطلاب
$students = $conn->query("SELECT id, name FROM user WHERE role = 'student'")->fetchAll();

// جلب المشرفين
$doctors = $conn->query("SELECT id, name FROM user WHERE role = 'doctor'")->fetchAll(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $status = $_POST['status'];
    $student_id = $_POST['student_id'];
    
    // التأكد من أن id المشرف تم إدخاله بشكل صحيح
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $doctor_id = $_POST['id']; // تغيير من doctor_id إلى id
    } else {
        $error_message = "يرجى إدخال ID المشرف."; // رسالة خطأ إذا لم يتم إدخال id
    }

    // التأكد من أن جميع الحقول تم تعبئتها
    if (empty($title) || empty($description) || empty($startDate) || empty($status) || empty($student_id) || empty($doctor_id)) {
        $error_message = "يرجى تعبئة جميع الحقول.";
    } else {
        // تحديث بيانات المشروع
        $stmt = $conn->prepare("UPDATE project SET title = ?, description = ?, startDate = ?, endDate = ?, status = ?, student_id = ?, id = ? WHERE id = ?");
        $stmt->execute([$title, $description, $startDate, $endDate, $status, $student_id, $doctor_id, $project_id]);
        header("Location: manage_projects.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تعديل المشروع</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            direction: rtl;
            font-size: 16px;
            margin: 0;
            padding: 60px 0 0 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
            width: 400px;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: 600;
        }

        input, select, textarea, button {
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        textarea {
            min-height: 100px;
        }

        button {
            background: #2c3e50;
            color: white;
            cursor: pointer;
            border: none;
        }

        button:hover {
            background: #34495e;
        }

        .back-btn {
            background-color: #3498db;
            color: white;
            text-decoration: none;
            text-align: center;
            padding: 10px;
            display: block;
            border-radius: 5px;
            margin-top: 15px;
        }

        .error-message {
            color: #e74c3c;
            font-size: 1rem;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>تعديل المشروع</h2>

        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="title">اسم المشروع:</label>
            <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($project['title']); ?>" required>

            <label for="description">وصف المشروع:</label>
            <textarea name="description" id="description" required><?php echo htmlspecialchars($project['description']); ?></textarea>

            <label for="startDate">تاريخ البدء:</label>
            <input type="date" name="startDate" id="startDate" value="<?php echo htmlspecialchars($project['startDate']); ?>" required>

            <label for="endDate">تاريخ الانتهاء:</label>
            <input type="date" name="endDate" id="endDate" value="<?php echo htmlspecialchars($project['endDate']); ?>">

            <label for="status">حالة المشروع:</label>
            <select name="status" id="status" required>
                <option value="نشط" <?php echo ($project['status'] == 'نشط') ? 'selected' : ''; ?>>نشط</option>
                <option value="مكتمل" <?php echo ($project['status'] == 'مكتمل') ? 'selected' : ''; ?>>مكتمل</option>
                <option value="ملغى" <?php echo ($project['status'] == 'ملغى') ? 'selected' : ''; ?>>ملغى</option>
            </select>

            <label for="student_id">الطالب:</label>
            <select name="student_id" id="student_id" required>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo $student['id']; ?>" <?php echo ($student['id'] == $project['student_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($student['name']); ?></option>
                <?php endforeach; ?>
            </select>

            <!-- استبدال قائمة المشرفين بـ حقل إدخال ID -->
            <label for="id">أدخل ID المشرف:</label>
            <input type="text" name="id" id="id" value="<?php echo htmlspecialchars($project['id']); ?>" required>

            <button type="submit">تحديث المشروع</button>
        </form>

        <a href="manage_projects.php" class="back-btn">الرجوع إلى إدارة المشاريع</a>
    </div>
</body>
</html>
