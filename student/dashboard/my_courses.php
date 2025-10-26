<?php
require_once __DIR__ . '/../../includes/student_auth.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_student_login();

$student_id = $_SESSION['student_id'];
$courses = get_student_courses($student_id);

// Get progress for each course
$courses_with_progress = [];
foreach ($courses as $course) {
    $progress = get_course_progress($student_id, $course['id']);
    $course['progress'] = $progress;
    $courses_with_progress[] = $course;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - NT2 Taalles International</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/student_portal.css">
</head>
<body>
    <?php include __DIR__ . '/../../includes/student_header.php'; ?>
    
    <main class="student-main">
        <div class="container">
            <h1>My Courses</h1>
            
            <?php if (empty($courses_with_progress)): ?>
                <div class="empty-state">
                    <p>You are not enrolled in any courses yet.</p>
                    <p>Contact your administrator to get access.</p>
                </div>
            <?php else: ?>
                <div class="course-grid">
                    <?php foreach ($courses_with_progress as $course): ?>
                        <div class="course-card">
                            <div class="course-header">
                                <h3><?= htmlspecialchars($course['title']) ?></h3>
                                <span class="course-level"><?= htmlspecialchars($course['level']) ?></span>
                            </div>
                            <p class="course-description"><?= htmlspecialchars(substr($course['description'], 0, 150)) ?>...</p>
                            
                            <div class="progress-container">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $course['progress'] ?>%"></div>
                                </div>
                                <span class="progress-text"><?= $course['progress'] ?>% Complete</span>
                            </div>
                            
                            <div class="course-meta-info">
                                <p><strong>Language:</strong> <?= htmlspecialchars($course['language_from']) ?> → <?= htmlspecialchars($course['language_to']) ?></p>
                                <?php if ($course['access_until']): ?>
                                    <p><strong>Access until:</strong> <?= date('M d, Y', strtotime($course['access_until'])) ?></p>
                                <?php else: ?>
                                    <p><strong>Access:</strong> Unlimited</p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="course-footer">
                                <a href="/student/course/view_course.php?id=<?= $course['id'] ?>" class="btn btn-primary">View Course</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>

