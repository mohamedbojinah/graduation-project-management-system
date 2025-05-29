<?php
session_start();
include '../config/db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// عند الضغط على إشعار معين
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $notif_id = $_GET['read'];

    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notif_id, $user_id]);

    // يمكن إعادة التوجيه لصفحة معينة هنا أو البقاء في نفس الصفحة
    header("Location: notifications.php");
    exit();
}

// استرجاع الإشعارات غير المقروءة
$stmt = $conn->prepare("SELECT id, type, message, created_at FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>الإشعارات</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f9f9f9;
            direction: rtl;
        }
        .container {
            width: 70%;
            margin: 50px auto;
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2980b9;
        }
        .notif {
            border-bottom: 1px solid #ddd;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .notif:last-child {
            border-bottom: none;
        }
        .notif a {
            text-decoration: none;
            color: #2980b9;
            font-weight: bold;
        }
        .notif a:hover {
            text-decoration: underline;
        }
        .notif .type {
            font-size: 1rem;
            color: #555;
        }
        .notif .date {
            font-size: 0.9rem;
            color: #999;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>الإشعارات غير المقروءة</h2>
    
    <?php if (count($notifications) > 0): ?>
        <?php foreach ($notifications as $notif): ?>
            <div class="notif">
                <div>
                    <div class="type">📌 النوع: <?= htmlspecialchars($notif['type']) ?></div>
                    <div class="message">💬 <?= htmlspecialchars($notif['message']) ?></div>
                    <div class="date">🕓 <?= $notif['created_at'] ?></div>
                </div>
                <div>
                    <a href="?read=<?= $notif['id'] ?>">تحديد كمقروء</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center; color:#777;">لا توجد إشعارات جديدة.</p>
    <?php endif; ?>
</div>

</body>
</html>
