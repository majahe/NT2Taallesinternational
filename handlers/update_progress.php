<?php
/**
 * Update Student Progress Handler
 * Updates lesson progress via AJAX
 */

session_start();
require_once __DIR__ . '/../includes/student_auth.php';
require_once __DIR__ . '/../includes/db_connect.php';

header('Content-Type: application/json');

if (!check_student_login()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$student_id = $_SESSION['student_id'];
$lesson_id = intval($_POST['lesson_id'] ?? 0);
$time_spent = intval($_POST['time_spent'] ?? 0);
$status = $_POST['status'] ?? 'in_progress';

if ($lesson_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid lesson ID']);
    exit;
}

// Update or insert progress
$stmt = $conn->prepare("
    INSERT INTO student_progress (student_id, lesson_id, status, time_spent, completed_at) 
    VALUES (?, ?, ?, ?, " . ($status === 'completed' ? 'CURRENT_TIMESTAMP' : 'NULL') . ")
    ON DUPLICATE KEY UPDATE 
        status = VALUES(status),
        time_spent = GREATEST(time_spent, VALUES(time_spent)),
        completed_at = COALESCE(completed_at, VALUES(completed_at)),
        updated_at = CURRENT_TIMESTAMP
");

$completed_at = $status === 'completed' ? date('Y-m-d H:i:s') : null;
$stmt->bind_param("iisis", $student_id, $lesson_id, $status, $time_spent, $completed_at);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Progress updated']);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

$stmt->close();

