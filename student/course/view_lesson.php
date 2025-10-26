<?php
require_once __DIR__ . '/../../includes/student_auth.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_student_login();

$student_id = $_SESSION['student_id'];
$lesson_id = intval($_GET['id'] ?? 0);

// Check if lesson is unlocked
if (!check_lesson_unlocked($student_id, $lesson_id)) {
    header("Location: /student/dashboard/dashboard.php?error=Lesson is locked");
    exit;
}

// Get lesson details
$stmt = $conn->prepare("
    SELECT l.*, m.title as module_title, m.course_id, c.title as course_title
    FROM lessons l
    JOIN course_modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE l.id = ?
");
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$lesson) {
    header("Location: /student/dashboard/dashboard.php?error=Lesson not found");
    exit;
}

// Get assignments for this lesson
$stmt = $conn->prepare("
    SELECT a.*, 
           COUNT(aq.id) as question_count,
           SUM(CASE WHEN sa.status IN ('graded', 'returned') THEN 1 ELSE 0 END) as submitted_count
    FROM assignments a
    LEFT JOIN assignment_questions aq ON a.id = aq.assignment_id
    LEFT JOIN student_assignments sa ON a.id = sa.assignment_id AND sa.student_id = ?
    WHERE a.lesson_id = ?
    GROUP BY a.id
    ORDER BY a.id
");
$stmt->bind_param("ii", $student_id, $lesson_id);
$stmt->execute();
$assignments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Initialize or update progress
$stmt = $conn->prepare("
    INSERT INTO student_progress (student_id, lesson_id, status) 
    VALUES (?, ?, 'in_progress')
    ON DUPLICATE KEY UPDATE status = 'in_progress', updated_at = CURRENT_TIMESTAMP
");
$stmt->bind_param("ii", $student_id, $lesson_id);
$stmt->execute();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($lesson['title']) ?> - NT2 Taalles International</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/student_portal.css">
    <link rel="stylesheet" href="../../assets/css/course_viewer.css">
</head>
<body>
    <?php include __DIR__ . '/../../includes/student_header.php'; ?>
    
    <main class="student-main">
        <div class="container">
            <div class="lesson-breadcrumb">
                <a href="/student/course/view_course.php?id=<?= $lesson['course_id'] ?>">← Back to Course</a>
                <span>/</span>
                <span><?= htmlspecialchars($lesson['module_title']) ?></span>
            </div>
            
            <h1><?= htmlspecialchars($lesson['title']) ?></h1>
            
            <?php if ($lesson['description']): ?>
                <p class="lesson-description"><?= htmlspecialchars($lesson['description']) ?></p>
            <?php endif; ?>
            
            <?php if ($lesson['video_path']): ?>
                <div class="video-player-container">
                    <video id="lessonVideo" controls>
                        <source src="<?= htmlspecialchars($lesson['video_path']) ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            <?php endif; ?>
            
            <?php if ($lesson['content']): ?>
                <div class="lesson-content-section">
                    <h2>Lesson Content</h2>
                    <div class="content">
                        <?= nl2br(htmlspecialchars($lesson['content'])) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($assignments)): ?>
                <div class="assignments-section">
                    <h2>Assignments</h2>
                    <div class="assignment-list">
                        <?php foreach ($assignments as $assignment): ?>
                            <?php
                            // Check if student has submitted this assignment
                            $stmt = $conn->prepare("SELECT * FROM student_assignments WHERE student_id = ? AND assignment_id = ?");
                            $stmt->bind_param("ii", $student_id, $assignment['id']);
                            $stmt->execute();
                            $submission = $stmt->get_result()->fetch_assoc();
                            $stmt->close();
                            
                            $status_class = 'pending';
                            $status_text = 'Not Started';
                            if ($submission) {
                                $status_class = $submission['status'];
                                $status_text = ucfirst($submission['status']);
                                if ($submission['status'] === 'graded' || $submission['status'] === 'returned') {
                                    $status_text = 'Graded (' . $submission['score'] . '/' . $submission['max_score'] . ')';
                                }
                            }
                            ?>
                            <div class="assignment-item">
                                <h3><?= htmlspecialchars($assignment['title']) ?></h3>
                                <p><?= htmlspecialchars($assignment['description']) ?></p>
                                <div class="assignment-meta">
                                    <span><strong>Type:</strong> <?= ucfirst(str_replace('_', ' ', $assignment['type'])) ?></span>
                                    <span><strong>Points:</strong> <?= $assignment['points'] ?></span>
                                    <span><strong>Questions:</strong> <?= $assignment['question_count'] ?></span>
                                    <span class="assignment-status <?= $status_class ?>"><?= $status_text ?></span>
                                </div>
                                <div style="margin-top: 1rem;">
                                    <?php if ($submission && ($submission['status'] === 'graded' || $submission['status'] === 'returned')): ?>
                                        <a href="assignment_result.php?id=<?= $assignment['id'] ?>" class="btn btn-primary">View Results</a>
                                    <?php else: ?>
                                        <a href="assignment.php?id=<?= $assignment['id'] ?>" class="btn btn-primary">
                                            <?= $submission ? 'Continue Assignment' : 'Start Assignment' ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="lesson-navigation">
                <?php
                // Get previous and next lessons
                $stmt = $conn->prepare("
                    SELECT l.id, l.title, m.course_id
                    FROM lessons l
                    JOIN course_modules m ON l.module_id = m.id
                    WHERE m.course_id = ? AND l.order_index < (
                        SELECT l2.order_index FROM lessons l2 WHERE l2.id = ?
                    )
                    ORDER BY l.order_index DESC
                    LIMIT 1
                ");
                $stmt->bind_param("ii", $lesson['course_id'], $lesson_id);
                $stmt->execute();
                $prev_lesson = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                $stmt = $conn->prepare("
                    SELECT l.id, l.title
                    FROM lessons l
                    JOIN course_modules m ON l.module_id = m.id
                    WHERE m.course_id = ? AND l.order_index > (
                        SELECT l2.order_index FROM lessons l2 WHERE l2.id = ?
                    )
                    ORDER BY l.order_index ASC
                    LIMIT 1
                ");
                $stmt->bind_param("ii", $lesson['course_id'], $lesson_id);
                $stmt->execute();
                $next_lesson = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                ?>
                
                <div class="nav-buttons">
                    <?php if ($prev_lesson): ?>
                        <a href="view_lesson.php?id=<?= $prev_lesson['id'] ?>" class="btn">← Previous Lesson</a>
                    <?php endif; ?>
                    
                    <a href="/student/course/view_course.php?id=<?= $lesson['course_id'] ?>" class="btn">Back to Course</a>
                    
                    <?php if ($next_lesson): ?>
                        <a href="view_lesson.php?id=<?= $next_lesson['id'] ?>" class="btn btn-primary">Next Lesson →</a>
                    <?php else: ?>
                        <button onclick="completeLesson()" class="btn btn-primary">Complete Lesson</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
    
    <script src="../../assets/js/progress_tracker.js"></script>
    <script>
        // Track video progress
        const video = document.getElementById('lessonVideo');
        if (video) {
            let lastUpdate = 0;
            
            video.addEventListener('timeupdate', function() {
                const currentTime = Math.floor(video.currentTime);
                // Update every 5 seconds
                if (currentTime - lastUpdate >= 5) {
                    updateProgress(<?= $lesson_id ?>, currentTime);
                    lastUpdate = currentTime;
                }
            });
            
            video.addEventListener('ended', function() {
                markLessonCompleted(<?= $lesson_id ?>);
            });
        }
        
        function completeLesson() {
            markLessonCompleted(<?= $lesson_id ?>);
            alert('Lesson completed!');
            window.location.href = '/student/course/view_course.php?id=<?= $lesson['course_id'] ?>';
        }
    </script>
</body>
</html>

