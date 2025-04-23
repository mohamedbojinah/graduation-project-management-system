<?php
session_start();
require_once '../src/Project.php';
require_once '../config/config.php';

// تحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$p = new Project($conn);

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['name'])) {
        $errors['name'] = 'اسم المشروع مطلوب';
    }

    if (empty($_POST['start_date'])) {
        $errors['start_date'] = 'تاريخ البدء مطلوب';
    }

    if (empty($_POST['status'])) {
        $errors['status'] = 'حالة المشروع مطلوبة';
    }

    if (empty($errors)) {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? null,
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'] ?? null,
            'status' => $_POST['status'],
            'created_by' => $_SESSION['user_id'],
            'team_id' => $_POST['team_id'] ?? null
        ];

        if ($p->create($data)) {
            $_SESSION['success_message'] = 'تم إضافة المشروع بنجاح';
            header('Location: index.php');
            exit;
        } else {
            $errors['general'] = 'حدث خطأ أثناء حفظ المشروع';
        }
    }

    $old = $_POST;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة مشروع جديد</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            width: 250px;
            background-color: var(--primary-color);
            color: var(--white);
            padding: 20px;
            height: 100vh;
        }

        .sidebar-header h2 {
            font-size: 22px;
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 15px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--white);
            text-decoration: none;
            padding: 10px;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu .active a {
            background-color: var(--active-color);
        }

        .main-content {
            flex: 1;
            padding: 30px;
            background-color: #fff;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-header h2 {
            font-size: 24px;
            color: var(--primary-color);
        }

        .btn {
            padding: 10px 20px;
            font-weight: bold;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
        }

        .btn-secondary {
            background-color: var(--gray);
            color: var(--white);
        }

        .btn-secondary:hover {
            background-color: var(--secondary-color);
        }

        .btn-success {
            background-color: var(--success-color);
            color: var(--white);
        }

        .btn-success:hover {
            background-color: #1e8449;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .form-control:focus {
            border-color: var(--active-color);
            box-shadow: 0 0 5px var(--active-color);
        }

        .is-invalid {
            border-color: var(--danger-color);
        }

        .invalid-feedback {
            color: var(--danger-color);
            font-size: 13px;
            margin-top: 5px;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>لوحة التحكم</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a></li>
            <li class="active"><a href="create_project.php"><i class="fas fa-project-diagram"></i> إدارة المشاريع</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h2>إضافة مشروع جديد</h2>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> رجوع</a>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger"><?= $errors['general'] ?></div>
        <?php endif; ?>

        <form method="POST" class="modal-content" style="max-width: 800px; margin: 0 auto;">
            <div class="modal-body">
                <div class="form-group">
                    <label>اسم المشروع *</label>
                    <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                    <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= $errors['name'] ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>وصف المشروع</label>
                    <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                </div>
                
               
                <div class="form-group">
                    <label>حالة المشروع *</label>
                    <select class="form-control <?= isset($errors['status']) ? 'is-invalid' : '' ?>" name="status">
                        <option value="">اختر الحالة</option>
                        <option value="active"  >نشط</option>
                        <option value="inactive">غير نشط</option>
                        <option value="pending" >قيد الانتظار</option>
                    </select>
                    <?php if (isset($errors['status'])): ?><div class="invalid-feedback"><?= $errors['status'] ?></div><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>الفريق (اختياري)</label>
                    <input type="number" class="form-control" name="team_id" value="<?= htmlspecialchars($old['team_id'] ?? '') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <a href="index.php" class="btn btn-secondary">إلغاء</a>
                <button type="submit" class="btn btn-success">حفظ</button>
            </div>
        </form>
    </main>
</div>
</body>
</html>
