<?php
function getDoctorProjects($doctor_id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM project WHERE manager_id = ?");
    $stmt->execute([$doctor_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
