<?php
session_start();

// التأكد من أن المستخدم هو "أدمن"
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../config/db.php';

// تحقق إذا كان تم تمرير الـ id عبر الـ GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("المشروع غير موجود");
}

$id = $_GET['id'];

// استرجاع تفاصيل المشروع من قاعدة البيانات باستخدام id
$stmt = $conn->prepare("SELECT * FROM project WHERE id = ?");
$stmt->execute([$id]);
$project = $stmt->fetch();

// التحقق إذا كانت البيانات موجودة
if (!$project) {
    die("المشروع غير موجود");
}

// معالجة التعديل إذا تم إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $status = $_POST['status'];

    // التحقق من أن جميع الحقول تم تعبئتها
    if (empty($title) || empty($start_date) || empty($status) || empty($description)) {
        $error_message = "يرجى تعبئة جميع الحقول.";
    } else {
        // استعلام لتحديث المشروع
        $stmt = $conn->prepare("UPDATE project SET title = ?, description = ?, start_date = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $description, $start_date, $status, $id]);

        // إعادة التوجيه إلى صفحة إدارة المشاريع بعد التحديث
        header("Location: manage_projects.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            padding: 50px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            width: 70%;
            border-radius: 10px;
            max-width: 900px;
        }

        h2 {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.5rem;
            color: #2c3e50;
        }

        .form-group {
            margin-bottom: 30px;
        }

        .form-group label {
            font-weight: 600;
            font-size: 1.2rem;
            display: block;
            margin-bottom: 10px;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            font-size: 1.2rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #2980b9;
            outline: none;
        }

        .form-group select, .form-group textarea {
            width: 100%;
            padding: 15px;
            font-size: 1.2rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .form-group select:focus, .form-group textarea:focus {
            border-color: #2980b9;
            outline: none;
        }

        .form-group textarea {
            min-height: 150px;
        }

        .btn-primary {
            background-color: #2980b9;
            color: white;
            padding: 15px 30px;
            font-size: 1.3rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #3498db;
        }

        .error-message {
            color: #e74c3c;
            font-size: 1.1rem;
            text-align: center;
            margin-bottom: 20px;
        }

        .back-btn {
            padding: 15px 30px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            margin-top: 30px;
            display: inline-block;
            font-size: 1.2rem;
            width: 100%;
            text-align: center;
        }

        .back-btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

    <div class="admin-container">
        <div class="main-content">
            <h2>تعديل المشروع</h2>

            <!-- عرض رسالة الخطأ إذا كانت موجودة -->
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- نموذج تعديل المشروع -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">اسم المشروع:</label>
                    <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($project['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">وصف المشروع:</label>
                    <textarea name="description" id="description" class="form-control" required><?php echo htmlspecialchars($project['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="start_date">تاريخ البدء:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($project['start_date']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="status">حالة المشروع:</label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="نشط" <?php echo ($project['status'] == 'نشط') ? 'selected' : ''; ?>>نشط</option>
                        <option value="مكتمل" <?php echo ($project['status'] == 'مكتمل') ? 'selected' : ''; ?>>مكتمل</option>
                        <option value="ملغى" <?php echo ($project['status'] == 'ملغى') ? 'selected' : ''; ?>>ملغى</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">تعديل المشروع</button>
            </form>

            <!-- زر الرجوع إلى إدارة المشاريع -->
            <a href="manage_projects.php" class="back-btn">الرجوع إلى إدارة المشاريع</a>
        </div>
    </div>

</body>
</html>
