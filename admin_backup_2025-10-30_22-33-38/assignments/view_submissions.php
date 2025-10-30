<?php
require_once __DIR__ . '/../../includes/admin_auth.php';
require_admin_auth();

include '../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/database/QueryBuilder.php';
$db = new QueryBuilder($conn);

$assignment_id = intval($_GET['assignment_id'] ?? 0);

if ($assignment_id <= 0) {
    header("Location: ../courses/manage_courses.php");
    exit;
}

// Get assignment info
$stmt = $conn->prepare("SELECT * FROM assignments WHERE id = ?");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle grading
require_once __DIR__ . '/../../includes/csrf.php';
    CSRF::requireToken();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_submission'])) {
    $submission_id = intval($_POST['submission_id']);
    $score = floatval($_POST['score']);
    $feedback = $_POST['feedback'] ?? '';
    $status = 'graded';
    
    $stmt = $conn->prepare("UPDATE student_assignments SET score = ?, feedback = ?, status = ? WHERE id = ?");
    $stmt->bind_param("dssi", $score, $feedback, $status, $submission_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: view_submissions.php?assignment_id=" . $assignment_id . "&success=Graded");
    exit;
}

// Get submissions
$stmt = $conn->prepare("
    SELECT sa.*, r.name, r.email 
    FROM student_assignments sa
    JOIN registrations r ON sa.student_id = r.id
    WHERE sa.assignment_id = ?
    ORDER BY sa.submitted_at DESC
");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get questions
$stmt = $conn->prepare("SELECT * FROM assignment_questions WHERE assignment_id = ? ORDER BY order_index");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submissions - <?= htmlspecialchars($assignment['title']) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="admin-container" style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
        <div class="page-header">
            <a href="manage_assignments.php?lesson_id=<?= $assignment['lesson_id'] ?>">← Back to Assignments</a>
            <h1>Submissions: <?= htmlspecialchars($assignment['title']) ?></h1>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        
        <div class="submissions-list">
            <?php foreach ($submissions as $submission): ?>
                <?php
                $answers = json_decode($submission['answers'], true);
                ?>
                <div class="submission-item">
                    <div class="submission-header">
                        <h3><?= htmlspecialchars($submission['name']) ?> (<?= htmlspecialchars($submission['email']) ?>)</h3>
                        <div>
                            <span class="status-badge status-<?= $submission['status'] ?>"><?= ucfirst($submission['status']) ?></span>
                            <?php if ($submission['status'] === 'graded' || $submission['status'] === 'returned'): ?>
                                <strong>Score: <?= $submission['score'] ?> / <?= $submission['max_score'] ?></strong>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <p><small>Submitted: <?= date('Y-m-d H:i', strtotime($submission['submitted_at'])) ?></small></p>
                    
                    <div class="submission-answers">
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="answer-item">
                                <h4>Question <?= $index + 1 ?></h4>
                                <p><strong><?= htmlspecialchars($question['question_text']) ?></strong></p>
                                <div class="student-answer">
                                    <strong>Answer:</strong>
                                    <p><?= nl2br(htmlspecialchars($answers[$question['id']] ?? 'No answer')) ?></p>
                                </div>
                                <?php if ($question['question_type'] !== 'essay' && $question['question_type'] !== 'file_upload'): ?>
                                    <div class="correct-answer">
                                        <strong>Correct:</strong> <?= htmlspecialchars($question['correct_answer']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($submission['status'] === 'pending' && in_array($assignment['type'], ['essay', 'speaking', 'file_upload'])): ?>
                        <form method="POST" class="grading-form">
                            <input type="hidden" name="submission_id" value="<?= $submission['id'] ?>">
                            <div class="form-group">
                                <label>Score (out of <?= $submission['max_score'] ?>)</label>
                                <input type="number" name="score" step="0.01" max="<?= $submission['max_score'] ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Feedback</label>
                                <textarea name="feedback" rows="5"></textarea>
                            </div>
                            <button type="submit" name="grade_submission" class="btn btn-primary">Grade Submission</button>
                        </form>
                    <?php elseif ($submission['feedback']): ?>
                        <div class="feedback-display">
                            <strong>Feedback:</strong>
                            <p><?= nl2br(htmlspecialchars($submission['feedback'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>

