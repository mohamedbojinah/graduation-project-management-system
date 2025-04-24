<?php
session_start();

// التأكد من أن المستخدم هو "أدمن"
if (!isset($_SESSION['role'])  $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../config/db.php';

// الحصول على معرف المشروع
if (isset($_GET['id'])) {
    $project_id = $_GET['id'];
} else {
    die("المشروع غير موجود");
}

// جلب بيانات المشروع
$stmt = $conn->prepare("SELECT * FROM project WHERE id = ?");
$stmt->execute([$project_id]);
$project = $stmt->fetch();

if (!$project) {
    die("المشروع غير موجود في قاعدة البيانات.");
}

// التحقق من وجود بيانات بعد التعديل
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $startDate = $_POST['startDate']; // التأكد من الحقل الصحيح
    $endDate = $_POST['endDate'];
    $status = $_POST['status'];

    // التحقق من الحقول
    if (empty($title)  empty($description)  empty($startDate)  empty($status)) {
        $error_message = "يرجى تعبئة جميع الحقول.";
    } else {
        // استعلام لتحديث المشروع
        $stmt = $conn->prepare("UPDATE project SET title = ?, description = ?, startDate = ?, endDate = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $description, $startDate, $endDate, $status, $project_id]);

        // إعادة التوجيه بعد التحديث
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
        /* تنسيق إضافي */
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            direction: rtl;
            font-size: 16px;
            margin: 0;
            padding: 0;
        }

        .form-container {
            width: 50%;
            margin: 30px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: 600;
            font-size: 1.1rem;
            display: block;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: #2980b9;
            outline: none;
        }

        .form-group select {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .form-group select:focus {
            border-color: #2980b9;
            outline: none;
        }

        .form-group textarea {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            min-height: 120px;
        }

        .form-group textarea:focus {
            border-color: #2980b9;
            outline: none;
        }

        .btn-primary {
            background-color: #2980b9;
            color: white;
            padding: 10px 20px;
            font-size: 1.1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: #3498db;
        }

        .error-message {
            color: #e74c3c;
            font-size: 1rem;
            text-align: center;
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <div class="form-container">
        <h2>تعديل المشروع</h2>

        <!-- رسالة الخطأ -->
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- نموذج تعديل المشروع -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="title">اسم المشروع:</label>
                <input type="text" name="title" id="title" class="form-control" value="<?php echo isset($title) ? htmlspecialchars($title) : htmlspecialchars($project['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">وصف المشروع:</label>
                <textarea name="description" id="description" class="form-control" required><?php echo isset($description) ? htmlspecialchars($description) : htmlspecialchars($project['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="startDate">تاريخ البدء:</label>
                <input type="date" name="startDate" id="startDate" class="form-control" value="<?php echo isset($startDate) ? htmlspecialchars($startDate) : htmlspecialchars($project['startDate']); ?>" required>
            </div>

            <div class="form-group">
                <label for="endDate">تاريخ الانتهاء:</label>
                <input type="date" name="endDate" id="endDate" class="form-control" value="<?php echo isset($endDate) ? htmlspecialchars($endDate) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="status">حالة المشروع:</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="نشط" <?php echo (isset($status) && $status == 'نشط') ? 'selected' : ''; ?>>نشط</option>
                    <option value="مكتمل" <?php echo (isset($status) && $status == 'مكتمل') ? 'selected' : ''; ?>>مكتمل</option>
                    <option value="ملغى" <?php echo (isset($status) && $status == 'ملغى') ? 'selected' : ''; ?>>ملغى</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">تحديث المشروع</button>
        </form>

        <!-- زر الرجوع -->
        <a href="manage_projects.php" class="back-btn">الرجوع إلى إدارة المشاريع</a>
    </div>
</body>
</html>
