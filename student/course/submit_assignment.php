<?php
require_once __DIR__ . '/../../includes/student_auth.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_student_login();

$log_dir = __DIR__ . '/../../logs';
if (!file_exists($log_dir)) {
    @mkdir($log_dir, 0755, true);
}
@ini_set('log_errors', 1);
@ini_set('error_log', $log_dir . '/submit_assignment.log');
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log("Fatal error: {$error['message']} in {$error['file']} on line {$error['line']}");
    }
});

function assignment_fail($message, $log_details = '') {
    if ($log_details !== '') {
        error_log("submit_assignment.php: " . $log_details);
    }
    header("Location: /student/dashboard/dashboard.php?error=" . urlencode($message));
    exit;
}

function fetch_all_stmt($stmt) {
    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    $meta = $stmt->result_metadata();
    if (!$meta) {
        return [];
    }

    $row = [];
    $params = [];
    while ($field = $meta->fetch_field()) {
        $row[$field->name] = null;
        $params[] = &$row[$field->name];
    }
    call_user_func_array([$stmt, 'bind_result'], $params);

    $rows = [];
    while ($stmt->fetch()) {
        $rows[] = array_map(function ($value) {
            return $value;
        }, $row);
    }

    return $rows;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /student/dashboard/dashboard.php?error=Invalid request");
    exit;
}

$student_id = $_SESSION['student_id'];
$assignment_id = intval($_POST['assignment_id'] ?? 0);

if ($assignment_id <= 0) {
    header("Location: /student/dashboard/dashboard.php?error=Invalid assignment");
    exit;
}

// Get assignment details
$stmt = $conn->prepare("SELECT * FROM assignments WHERE id = ?");
if (!$stmt) {
    assignment_fail("Database error", $conn->error);
}
$stmt->bind_param("i", $assignment_id);
if (!$stmt->execute()) {
    assignment_fail("Database error", $stmt->error);
}
$assignment_rows = fetch_all_stmt($stmt);
$stmt->close();
$assignment = $assignment_rows[0] ?? null;

if (!$assignment) {
    header("Location: /student/dashboard/dashboard.php?error=Assignment not found");
    exit;
}

// Get questions
$stmt = $conn->prepare("SELECT * FROM assignment_questions WHERE assignment_id = ?");
if (!$stmt) {
    assignment_fail("Database error", $conn->error);
}
$stmt->bind_param("i", $assignment_id);
if (!$stmt->execute()) {
    assignment_fail("Database error", $stmt->error);
}
$questions = fetch_all_stmt($stmt);
$stmt->close();

// Process answers
$answers = [];
$upload_dir = __DIR__ . '/../../uploads/assignments/';

if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        assignment_fail("Upload directory error", "Failed to create upload directory: " . $upload_dir);
    }
}

foreach ($questions as $question) {
    $qid = $question['id'];
    
    if ($question['question_type'] === 'file_upload') {
        $has_file = isset($_FILES['files']['error'][$qid]) && $_FILES['files']['error'][$qid] === UPLOAD_ERR_OK;
        if ($has_file) {
            $original_name = $_FILES['files']['name'][$qid] ?? '';
            $tmp_name = $_FILES['files']['tmp_name'][$qid] ?? '';
            if ($tmp_name !== '') {
                $filename = uniqid('assignment_', true) . '_' . basename($original_name);
                $filepath = $upload_dir . $filename;
                if (move_uploaded_file($tmp_name, $filepath)) {
                    $answers[$qid] = $filename;
                } else {
                    assignment_fail("Upload failed", "Failed to move uploaded file to: " . $filepath);
                }
            } else {
                $answers[$qid] = '';
            }
        } else {
            $answers[$qid] = '';
        }
    } else {
        $answers[$qid] = $_POST['answers'][$qid] ?? '';
    }
}

$answers_json = json_encode($answers);

// Calculate score for auto-gradable questions
$score = 0;
$max_score = 0;

foreach ($questions as $question) {
    $max_score += $question['points'];
    
    if (in_array($question['question_type'], ['multiple_choice', 'fill_in'])) {
        $student_answer = $answers[$question['id']] ?? '';
        $correct_answer = $question['correct_answer'];
        
        if ($question['question_type'] === 'multiple_choice') {
            if (trim(strtolower($student_answer)) === trim(strtolower($correct_answer))) {
                $score += $question['points'];
            }
        } elseif ($question['question_type'] === 'fill_in') {
            // Simple string matching (case-insensitive, trimmed)
            if (trim(strtolower($student_answer)) === trim(strtolower($correct_answer))) {
                $score += $question['points'];
            }
        }
    }
}

// Determine status
$status = 'pending';
if (in_array($assignment['type'], ['essay', 'speaking', 'file_upload'])) {
    $status = 'pending'; // Needs manual grading
} else {
    $status = 'graded'; // Auto-graded
}

// Save submission
$stmt = $conn->prepare("
    INSERT INTO student_assignments (student_id, assignment_id, answers, score, max_score, status)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        answers = VALUES(answers),
        score = VALUES(score),
        max_score = VALUES(max_score),
        status = VALUES(status),
        submitted_at = CURRENT_TIMESTAMP
");

if (!$stmt) {
    assignment_fail("Database error", $conn->error);
}
$stmt->bind_param("iisdis", $student_id, $assignment_id, $answers_json, $score, $max_score, $status);
if (!$stmt->execute()) {
    assignment_fail("Database error", $stmt->error);
}
$stmt->close();

// Redirect to results
header("Location: assignment_result.php?id=" . $assignment_id . "&submitted=1");
exit;

