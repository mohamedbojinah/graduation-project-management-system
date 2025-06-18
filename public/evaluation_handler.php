<?php
function evaluateProject($project_id, $evaluation_score, $comments, $doctor_id, $conn) {
    // التحقق من وجود المشروع
    $stmt_student = $conn->prepare("SELECT student_id FROM project WHERE id = :project_id");
    $stmt_student->bindParam(':project_id', $project_id);
    $stmt_student->execute();
    $project_details = $stmt_student->fetch(PDO::FETCH_ASSOC);

    if (!$project_details) {
        return "المشروع غير موجود.";
    }

    $student_id = $project_details['student_id'];

    // إضافة التقييم
    $stmt = $conn->prepare("INSERT INTO evaluations (project_id, evaluator_id, student_id, evaluation_score, comments) 
                            VALUES (:project_id, :evaluator_id, :student_id, :evaluation_score, :comments)");
    $stmt->bindParam(':project_id', $project_id);
    $stmt->bindParam(':evaluator_id', $doctor_id);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->bindParam(':evaluation_score', $evaluation_score);
    $stmt->bindParam(':comments', $comments);
    $success = $stmt->execute();

    if (!$success) {
        return "فشل في حفظ التقييم.";
    }

    // تحديث حالة المشروع
    $update = $conn->prepare("UPDATE project SET status = 'completed' WHERE id = :project_id");
    $update->bindParam(':project_id', $project_id);
    $update->execute();

    return "تم التقييم بنجاح";
}
