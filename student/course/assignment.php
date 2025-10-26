<?php
require_once __DIR__ . '/../../includes/student_auth.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_student_login();

$student_id = $_SESSION['student_id'];
$assignment_id = intval($_GET['id'] ?? 0);

// Get assignment details
$stmt = $conn->prepare("
    SELECT a.*, l.title as lesson_title, l.id as lesson_id, m.course_id
    FROM assignments a
    JOIN lessons l ON a.lesson_id = l.id
    JOIN course_modules m ON l.module_id = m.id
    WHERE a.id = ?
");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$assignment) {
    header("Location: /student/dashboard/dashboard.php?error=Assignment not found");
    exit;
}

// Check course access
if (!check_course_access($student_id, $assignment['course_id'])) {
    header("Location: /student/dashboard/dashboard.php?error=No access");
    exit;
}

// Get questions
$stmt = $conn->prepare("
    SELECT * FROM assignment_questions 
    WHERE assignment_id = ? 
    ORDER BY order_index
");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Check if already submitted
$stmt = $conn->prepare("SELECT * FROM student_assignments WHERE student_id = ? AND assignment_id = ?");
$stmt->bind_param("ii", $student_id, $assignment_id);
$stmt->execute();
$submission = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($submission && ($submission['status'] === 'graded' || $submission['status'] === 'returned')) {
    header("Location: assignment_result.php?id=" . $assignment_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($assignment['title']) ?> - Assignment</title>
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
            
            <h1><?= htmlspecialchars($assignment['title']) ?></h1>
            <p class="lesson-description"><?= htmlspecialchars($assignment['description']) ?></p>
            
            <div class="assignment-info">
                <p><strong>Type:</strong> <?= ucfirst(str_replace('_', ' ', $assignment['type'])) ?></p>
                <p><strong>Points:</strong> <?= $assignment['points'] ?></p>
                <p><strong>Required:</strong> <?= $assignment['is_required'] ? 'Yes' : 'No' ?></p>
            </div>
            
            <form id="assignmentForm" method="POST" action="submit_assignment.php" enctype="multipart/form-data">
                <input type="hidden" name="assignment_id" value="<?= $assignment_id ?>">
                
                <div class="questions-container">
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="question-item">
                            <h3>Question <?= $index + 1 ?></h3>
                            <p class="question-text"><?= nl2br(htmlspecialchars($question['question_text'])) ?></p>
                            <p class="question-points">Points: <?= $question['points'] ?></p>
                            
                            <?php if ($question['question_type'] === 'multiple_choice'): ?>
                                <?php
                                $options = json_decode($question['options'], true);
                                $old_answer = $submission ? json_decode($submission['answers'], true)[$question['id']] ?? '' : '';
                                ?>
                                <div class="options-list">
                                    <?php foreach ($options as $opt_index => $option): ?>
                                        <label class="option-label">
                                            <input type="radio" 
                                                   name="answers[<?= $question['id'] ?>]" 
                                                   value="<?= htmlspecialchars($option) ?>"
                                                   <?= $old_answer === $option ? 'checked' : '' ?>
                                                   required>
                                            <span><?= htmlspecialchars($option) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                
                            <?php elseif ($question['question_type'] === 'fill_in'): ?>
                                <?php $old_answer = $submission ? json_decode($submission['answers'], true)[$question['id']] ?? '' : ''; ?>
                                <input type="text" 
                                       name="answers[<?= $question['id'] ?>]" 
                                       class="form-input"
                                       value="<?= htmlspecialchars($old_answer) ?>"
                                       required>
                                       
                            <?php elseif ($question['question_type'] === 'essay'): ?>
                                <?php $old_answer = $submission ? json_decode($submission['answers'], true)[$question['id']] ?? '' : ''; ?>
                                <textarea name="answers[<?= $question['id'] ?>]" 
                                          class="form-textarea"
                                          rows="10"
                                          required><?= htmlspecialchars($old_answer) ?></textarea>
                                          
                            <?php elseif ($question['question_type'] === 'file_upload'): ?>
                                <input type="file" 
                                       name="files[<?= $question['id'] ?>]" 
                                       class="form-input"
                                       <?= $submission ? '' : 'required' ?>>
                                <?php if ($submission): ?>
                                    <?php
                                    $old_files = json_decode($submission['answers'], true)[$question['id']] ?? '';
                                    if ($old_files):
                                    ?>
                                        <p class="file-info">Previously uploaded: <?= htmlspecialchars($old_files) ?></p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Submit Assignment</button>
                    <a href="view_lesson.php?id=<?= $assignment['lesson_id'] ?>" class="btn">Cancel</a>
                </div>
            </form>
        </div>
    </main>
    
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>

