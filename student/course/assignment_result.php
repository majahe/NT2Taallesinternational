<?php
require_once __DIR__ . '/../../includes/student_auth.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_student_login();

$student_id = $_SESSION['student_id'];
$assignment_id = intval($_GET['id'] ?? 0);

// Get assignment details
$stmt = $conn->prepare("
    SELECT a.*, l.title as lesson_title, l.id as lesson_id
    FROM assignments a
    JOIN lessons l ON a.lesson_id = l.id
    WHERE a.id = ?
");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get submission
$stmt = $conn->prepare("SELECT * FROM student_assignments WHERE student_id = ? AND assignment_id = ?");
$stmt->bind_param("ii", $student_id, $assignment_id);
$stmt->execute();
$submission = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$submission) {
    header("Location: assignment.php?id=" . $assignment_id);
    exit;
}

// Get questions
$stmt = $conn->prepare("SELECT * FROM assignment_questions WHERE assignment_id = ? ORDER BY order_index");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$answers = json_decode($submission['answers'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Results - NT2 Taalles International</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/student_portal.css">
    <link rel="stylesheet" href="../../assets/css/course_viewer.css">
</head>
<body>
    <?php include __DIR__ . '/../../includes/student_header.php'; ?>
    
    <main class="student-main">
        <div class="container">
            <div class="lesson-breadcrumb">
                <a href="/student/course/view_lesson.php?id=<?= $assignment['lesson_id'] ?>">← Back to Lesson</a>
            </div>
            
            <h1>Assignment Results</h1>
            
            <div class="results-summary">
                <h2><?= htmlspecialchars($assignment['title']) ?></h2>
                <div class="score-display">
                    <span class="score-label">Your Score:</span>
                    <span class="score-value"><?= $submission['score'] ?> / <?= $submission['max_score'] ?></span>
                    <span class="score-percentage">(<?= round(($submission['score'] / $submission['max_score']) * 100) ?>%)</span>
                </div>
                <p class="status-badge status-<?= $submission['status'] ?>"><?= ucfirst($submission['status']) ?></p>
            </div>
            
            <?php if ($submission['feedback']): ?>
                <div class="feedback-section">
                    <h3>Feedback</h3>
                    <p><?= nl2br(htmlspecialchars($submission['feedback'])) ?></p>
                </div>
            <?php endif; ?>
            
            <div class="questions-review">
                <h3>Your Answers</h3>
                <?php foreach ($questions as $index => $question): ?>
                    <?php
                    $student_answer = $answers[$question['id']] ?? '';
                    $is_correct = false;
                    
                    if (in_array($question['question_type'], ['multiple_choice', 'fill_in'])) {
                        $correct_answer = $question['correct_answer'];
                        if (trim(strtolower($student_answer)) === trim(strtolower($correct_answer))) {
                            $is_correct = true;
                        }
                    }
                    ?>
                    <div class="question-review <?= $is_correct ? 'correct' : ($question['question_type'] === 'essay' ? 'pending' : 'incorrect') ?>">
                        <h4>Question <?= $index + 1 ?></h4>
                        <p class="question-text"><?= nl2br(htmlspecialchars($question['question_text'])) ?></p>
                        
                        <div class="answer-section">
                            <strong>Your Answer:</strong>
                            <p><?= nl2br(htmlspecialchars($student_answer)) ?></p>
                        </div>
                        
                        <?php if (in_array($question['question_type'], ['multiple_choice', 'fill_in'])): ?>
                            <div class="correct-answer-section">
                                <strong>Correct Answer:</strong>
                                <p><?= htmlspecialchars($question['correct_answer']) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <p class="points-earned">
                            <?php if ($question['question_type'] === 'essay'): ?>
                                Points: Pending manual review
                            <?php else: ?>
                                Points: <?= $is_correct ? $question['points'] : 0 ?> / <?= $question['points'] ?>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="form-actions">
                <a href="/student/course/view_lesson.php?id=<?= $assignment['lesson_id'] ?>" class="btn btn-primary">Back to Lesson</a>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>

