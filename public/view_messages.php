<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// جلب الرسائل الموجهة للمستخدم
$stmt = $conn->prepare("SELECT messages.*, user.name AS sender_name FROM messages JOIN user ON messages.sender_id = user.id WHERE receiver_id = ? ORDER BY sent_at DESC");
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>📨 الرسائل المستلمة</title>
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            direction: rtl;
        }

        .message-box {
            background-color: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 6px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
            cursor: pointer;
        }

        .message-box:hover {
            background-color: #f9f9f9;
        }

        .sender {
            font-weight: bold;
            color: #2c3e50;
        }

        .preview {
            color: #555;
        }

        .timestamp {
            font-size: 0.85em;
            color: #888;
        }

        .full-message {
            display: none;
            margin-top: 10px;
            color: #333;
        }
    </style>
</head>
<body>

<h2>📨 الرسائل المستلمة</h2>

<?php if (count($messages) === 0): ?>
    <p>لا توجد رسائل حالياً.</p>
<?php else: ?>
    <?php foreach ($messages as $index => $msg): ?>
        <div class="message-box" onclick="toggleMessage(<?= $index ?>)">
            <div class="sender">من: <?= htmlspecialchars($msg['sender_name']) ?></div>
            <div class="preview"><?= htmlspecialchars(mb_substr($msg['message'], 0, 50)) ?>...</div>
            <div class="timestamp">📅 <?= $msg['sent_at'] ?></div>
            <div id="full-<?= $index ?>" class="full-message">
                <?= nl2br(htmlspecialchars($msg['message'])) ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
function toggleMessage(index) {
    const element = document.getElementById('full-' + index);
    element.style.display = (element.style.display === 'block') ? 'none' : 'block';
}
</script>

</body>
</html>
