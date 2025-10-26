<?php
require_once __DIR__ . '/../../includes/student_auth.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_student_login();

$student_id = $_SESSION['student_id'];
$course_id = intval($_GET['id'] ?? 0);

// Check course access
if (!check_course_access($student_id, $course_id)) {
    header("Location: /student/dashboard/dashboard.php?error=No access to this course");
    exit;
}

// Get course details
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    header("Location: /student/dashboard/dashboard.php?error=Course not found");
    exit;
}

// Get modules with lessons
$stmt = $conn->prepare("
    SELECT m.*, 
           COUNT(l.id) as lesson_count,
           SUM(CASE WHEN sp.status = 'completed' THEN 1 ELSE 0 END) as completed_count
    FROM course_modules m
    LEFT JOIN lessons l ON m.id = l.module_id
    LEFT JOIN student_progress sp ON l.id = sp.lesson_id AND sp.student_id = ?
    WHERE m.course_id = ?
    GROUP BY m.id
    ORDER BY m.order_index
");
$stmt->bind_param("ii", $student_id, $course_id);
$stmt->execute();
$modules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get course progress
$course_progress = get_course_progress($student_id, $course_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['title']) ?> - NT2 Taalles International</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/student_portal.css">
    <link rel="stylesheet" href="../../assets/css/course_viewer.css">
</head>
<body>
    <?php include __DIR__ . '/../../includes/student_header.php'; ?>
    
    <main class="student-main">
        <div class="container">
            <div class="course-header-section">
                <h1><?= htmlspecialchars($course['title']) ?></h1>
                <p class="course-level-badge"><?= htmlspecialchars($course['level']) ?> • <?= htmlspecialchars($course['language_from']) ?> → <?= htmlspecialchars($course['language_to']) ?></p>
                
                <div class="course-progress-large">
                    <div class="progress-bar-large">
                        <div class="progress-fill-large" style="width: <?= $course_progress ?>%"></div>
                    </div>
                    <span class="progress-percentage"><?= $course_progress ?>% Complete</span>
                </div>
            </div>
            
            <div class="course-description-full">
                <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
            </div>
            
            <div class="modules-container">
                <?php foreach ($modules as $module): ?>
                    <div class="module-card">
                        <div class="module-header">
                            <h2><?= htmlspecialchars($module['title']) ?></h2>
                            <span class="module-progress">
                                <?= $module['completed_count'] ?> / <?= $module['lesson_count'] ?> Lessons
                            </span>
                        </div>
                        
                        <?php if ($module['description']): ?>
                            <p class="module-description"><?= htmlspecialchars($module['description']) ?></p>
                        <?php endif; ?>
                        
                        <?php
                        // Get lessons for this module
                        $stmt = $conn->prepare("
                            SELECT l.*, 
                                   sp.status as progress_status,
                                   sp.completed_at
                            FROM lessons l
                            LEFT JOIN student_progress sp ON l.id = sp.lesson_id AND sp.student_id = ?
                            WHERE l.module_id = ?
                            ORDER BY l.order_index
                        ");
                        $stmt->bind_param("ii", $student_id, $module['id']);
                        $stmt->execute();
                        $lessons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        $stmt->close();
                        ?>
                        
                        <div class="lessons-list">
                            <?php foreach ($lessons as $index => $lesson): ?>
                                <?php
                                $is_unlocked = $index === 0 || ($lessons[$index-1]['progress_status'] ?? 'not_started') === 'completed';
                                $is_completed = ($lesson['progress_status'] ?? 'not_started') === 'completed';
                                ?>
                                <div class="lesson-item <?= $is_completed ? 'completed' : '' ?> <?= !$is_unlocked ? 'locked' : '' ?>">
                                    <div class="lesson-icon">
                                        <?php if ($is_completed): ?>
                                            ✅
                                        <?php elseif (!$is_unlocked): ?>
                                            🔒
                                        <?php else: ?>
                                            📹
                                        <?php endif; ?>
                                    </div>
                                    <div class="lesson-content">
                                        <h3><?= htmlspecialchars($lesson['title']) ?></h3>
                                        <?php if ($lesson['description']): ?>
                                            <p><?= htmlspecialchars(substr($lesson['description'], 0, 100)) ?>...</p>
                                        <?php endif; ?>
                                        <?php if ($lesson['video_duration']): ?>
                                            <small>Duration: <?= gmdate("i:s", $lesson['video_duration']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="lesson-action">
                                        <?php if ($is_unlocked): ?>
                                            <a href="view_lesson.php?id=<?= $lesson['id'] ?>" class="btn btn-primary">View</a>
                                        <?php else: ?>
                                            <span class="btn btn-disabled">Locked</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>

