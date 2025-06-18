<?php
function addTask($doctor_id, $task_title, $task_description, $due_date, $project_id, $conn) {
    if (empty($project_id)) {
        return "يجب اختيار المشروع.";
    }

    $stmt = $conn->prepare("INSERT INTO task (doctor_id, task_title, task_description, due_date, project_id, start_date)
                            VALUES (:doctor_id, :task_title, :task_description, :due_date, :project_id, NOW())");

    $stmt->bindParam(':doctor_id', $doctor_id);
    $stmt->bindParam(':task_title', $task_title);
    $stmt->bindParam(':task_description', $task_description);
    $stmt->bindParam(':due_date', $due_date);
    $stmt->bindParam(':project_id', $project_id);

    $success = $stmt->execute();

    return $success ? "تمت إضافة المهمة بنجاح." : "حدث خطأ أثناء إضافة المهمة.";
}
