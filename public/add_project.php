<?php
session_start();

// التأكد من أن المستخدم هو "أدمن"
if (!isset($_SESSION['role'])  $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../config/db.php';

// معالجة استعلام إضافة المشروع
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // استلام البيانات المدخلة
    $title = $_POST['title'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    $student_id = $_POST['student_id'];
    $manager_id = $_POST['manager_id'];

    // تحديد التاريخ الحالي كـ startDate
    $startDate = date('Y-m-d H:i:s'); // التاريخ الحالي من الجهاز
    $endDate = ''; // حقل تاريخ الانتهاء فارغ

    // التحقق من وجود الحقول المطلوبة
    if (empty($title)  empty($description)  empty($status)  empty($student_id) || empty($manager_id)) {
        $error_message = "يرجى تعبئة جميع الحقول.";
    } else {
        // استعلام لإضافة مشروع جديد
        $stmt = $conn->prepare("INSERT INTO project (title, description, status, startDate, endDate, student_id, manager_id) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $description, $status, $startDate, $endDate, $student_id, $manager_id])) {
            // إعادة التوجيه إلى صفحة إدارة المشاريع بعد إضافة المشروع
            header("Location: manage_projects.php");
            exit();
        } else {
            $error_message = "حدث خطأ أثناء إضافة المشروع.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مشروع</title>
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            direction: rtl;
            font-size: 16px;
            margin: 0;
            padding: 0;
        }

        .admin-container {
            display: flex;
            justify-content: center;
            padding-top: 50px;
            width: 100%;
        }

        .main-content {
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 80%;
            border-radius: 10px;
            margin: 0 auto;
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

    <div class="admin-container">
        <div class="main-content">
            <h2>إضافة مشروع جديد</h2>

            <!-- رسالة الخطأ -->
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- نموذج إضافة المشروع -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">اسم المشروع:</label>
                    <input type="text" name="title" id="title" class="form-control" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">وصف المشروع:</label>
                    <textarea name="description" id="description" class="form-control" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="status">حالة المشروع:</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="نشط" <?php echo isset($status) && $status == 'نشط' ? 'selected' : ''; ?>>نشط</option>
                        <option value="مكتمل" <?php echo isset($status) && $status == 'مكتمل' ? 'selected' : ''; ?>>مكتمل</option>
                        <option value="ملغى" <?php echo isset($status) && $status == 'ملغى' ? 'selected' : ''; ?>>ملغى</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="student_id">رقم الطالب:</label>
                    <input type="text" name="student_id" id="student_id" class="form-control" value="<?php echo isset($student_id) ? htmlspecialchars($student_id) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="manager_id">رقم المشرف:</label>
                    <input type="text" name="manager_id" id="manager_id" class="form-control" value="<?php echo isset($manager_id) ? htmlspecialchars($manager_id) : ''; ?>" required>
                </div>

                <button type="submit" class="btn-primary">إضافة المشروع</button>
            </form>

            <!-- زر الرجوع إلى الصفحة الرئيسية -->
            <a href="manage_projects.php" class="back-btn">الرجوع إلى إدارة المشاريع</a>
        </div>
    </div>

</body>
</html>
