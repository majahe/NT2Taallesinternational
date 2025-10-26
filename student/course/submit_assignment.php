<?php
require_once __DIR__ . '/../../includes/student_auth.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_student_login();

$student_id = $_SESSION['student_id'];
$assignment_id = intval($_POST['assignment_id'] ?? 0);

if ($assignment_id <= 0) {
    header("Location: /student/dashboard/dashboard.php?error=Invalid assignment");
    exit;
}

// Get assignment details
$stmt = $conn->prepare("SELECT * FROM assignments WHERE id = ?");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$assignment) {
    header("Location: /student/dashboard/dashboard.php?error=Assignment not found");
    exit;
}

// Get questions
$stmt = $conn->prepare("SELECT * FROM assignment_questions WHERE assignment_id = ?");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Process answers
$answers = [];
$upload_dir = __DIR__ . '/../../uploads/assignments/';

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

foreach ($questions as $question) {
    $qid = $question['id'];
    
    if ($question['question_type'] === 'file_upload' && isset($_FILES['files'][$qid])) {
        $file = $_FILES['files'][$qid];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $filename = uniqid('assignment_', true) . '_' . $file['name'];
            $filepath = $upload_dir . $filename;
            move_uploaded_file($file['tmp_name'], $filepath);
            $answers[$qid] = $filename;
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

$stmt->bind_param("iisdis", $student_id, $assignment_id, $answers_json, $score, $max_score, $status);
$stmt->execute();
$stmt->close();

// Redirect to results
header("Location: assignment_result.php?id=" . $assignment_id . "&submitted=1");
exit;

