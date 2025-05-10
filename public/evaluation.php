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
$dbname = 'project_db';  // اسم قاعدة البيانات
$username = 'root';
$password = '';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // جلب المشاريع الخاصة بالدكتور لتقييمها
    $query = "SELECT * FROM project WHERE manager_id = :doctor_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':doctor_id', $_SESSION['user_id']);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // إضافة التقييم
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_evaluation'])) {
        $project_id = $_POST['project_id'];
        $evaluation_score = $_POST['evaluation_score'];
        $comments = $_POST['comments'];
        $evaluator_id = $_SESSION['user_id']; // معرّف الدكتور من الجلسة

        // جلب student_id من المشروع
        $query_student_id = "SELECT student_id FROM project WHERE id = :project_id";
        $stmt_student = $conn->prepare($query_student_id);
        $stmt_student->bindParam(':project_id', $project_id);
        $stmt_student->execute();
        $project_details = $stmt_student->fetch(PDO::FETCH_ASSOC);

        $student_id = $project_details['student_id']; // الحصول على student_id

        // استعلام لإضافة التقييم
        $addQuery = "INSERT INTO evaluations (project_id, evaluator_id, student_id, evaluation_score, comments) 
                     VALUES (:project_id, :evaluator_id, :student_id, :evaluation_score, :comments)";
        $addStmt = $conn->prepare($addQuery);
        $addStmt->bindParam(':project_id', $project_id);
        $addStmt->bindParam(':evaluator_id', $evaluator_id);
        $addStmt->bindParam(':student_id', $student_id); // تخزين student_id بشكل صحيح
        $addStmt->bindParam(':evaluation_score', $evaluation_score);
        $addStmt->bindParam(':comments', $comments);
        $addStmt->execute();

        // عرض رسالة النجاح ثم التوجيه إلى الصفحة الرئيسية
        $success_message = "تم التقييم بنجاح";
        echo "<script>
                setTimeout(function(){
                    window.location.href = 'doctor_evaluations.php'; // إعادة التوجيه بعد 3 ثواني
                }, 3000);
              </script>";
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
    <title>تقييم المشروع - دكتور</title>
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f5f5f5;
            color: #333;
            direction: rtl;
        }

        .form-container {
            width: 60%;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            padding: 10px 20px;
            background-color: #2980b9;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #3498db;
        }

        .project-name {
            font-weight: bold;
            color: #2c3e50;
        }

        .project-details {
            margin: 20px 0;
            font-size: 1rem;
        }

        .project-details div {
            margin: 5px 0;
        }

        .success-message {
            background-color: #27ae60;
            color: white;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            border-radius: 5px;
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <h2>تقييم المشروع</h2>
        <form method="POST" action="">
            <label for="project_id">اختر المشروع:</label>
            <select name="project_id" id="project_id" required onchange="displayProjectDetails()">
                <option value="">اختار المشروع</option>
                <?php foreach ($projects as $project): ?>
                    <option value="<?php echo $project['id']; ?>"><?php echo $project['title']; ?></option>
                <?php endforeach; ?>
            </select>

            <!-- عرض تفاصيل المشروع بعد اختياره -->
            <div id="project_details" class="project-details">
                <?php if (isset($project_details)): ?>
                    <div><strong>اسم الطالب:</strong> <?php echo $project_details['student_id']; ?></div>
                    <div><strong>تاريخ البدء:</strong> <?php echo $project_details['startDate']; ?></div>
                    <div><strong>تاريخ الانتهاء:</strong> <?php echo $project_details['endDate']; ?></div>
                    <div><strong>الحالة:</strong> <?php echo $project_details['status']; ?></div>
                <?php endif; ?>
            </div>

            <label for="evaluation_score">التقييم (من 0 إلى 100):</label>
            <input type="number" name="evaluation_score" min="0" max="100" required>

            <label for="comments">الملاحظات:</label>
            <textarea name="comments" placeholder="اكتب ملاحظاتك هنا" required></textarea>

            <button type="submit" name="add_evaluation">إضافة التقييم</button>
        </form>

        <!-- زر الرجوع إلى الصفحة الرئيسية -->
        <a href="doctor_dashboard.php" class="back-button">رجوع إلى الصفحة الرئيسية</a>
    </div>

    <script>
        function displayProjectDetails() {
            var projectSelect = document.getElementById('project_id');
            var projectId = projectSelect.value;

            if (projectId) {
                // إرسال طلب عبر AJAX لجلب تفاصيل المشروع
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status == 200) {
                        var projectDetails = JSON.parse(xhr.responseText);
                        var detailsHtml = `
                            <div><strong>اسم الطالب:</strong> ${projectDetails.student_id}</div>
                            <div><strong>تاريخ البدء:</strong> ${projectDetails.startDate}</div>
                            <div><strong>تاريخ الانتهاء:</strong> ${projectDetails.endDate}</div>
                            <div><strong>الحالة:</strong> ${projectDetails.status}</div>
                        `;
                        document.getElementById('project_details').innerHTML = detailsHtml;
                    }
                };
                xhr.send('project_id=' + projectId);
            } else {
                document.getElementById('project_details').innerHTML = '';
            }
        }
    </script>
</body>
</html>
