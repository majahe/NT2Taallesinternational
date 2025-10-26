<?php
require_once __DIR__ . '/../../includes/student_auth.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_student_login();

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// Get enrolled courses
$courses = get_student_courses($student_id);

// Get recent activity
$stmt = $conn->prepare("
    SELECT l.title as lesson_title, l.id as lesson_id, c.title as course_title, c.id as course_id, sp.completed_at
    FROM student_progress sp
    JOIN lessons l ON sp.lesson_id = l.id
    JOIN course_modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE sp.student_id = ? AND sp.status = 'completed'
    ORDER BY sp.completed_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$recent_activity = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get total points
$total_points = get_student_points($student_id);

// Get courses with progress
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
    <title>Dashboard - NT2 Taalles International</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/student_portal.css">
</head>
<body>
    <?php include __DIR__ . '/../../includes/student_header.php'; ?>
    
    <main class="student-main">
        <div class="container">
            <h1>Welcome back, <?= htmlspecialchars($student_name) ?>!</h1>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-content">
                        <h3><?= count($courses) ?></h3>
                        <p>Enrolled Courses</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-content">
                        <h3><?= $total_points ?></h3>
                        <p>Total Points</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <h3><?= count($recent_activity) ?></h3>
                        <p>Completed Lessons</p>
                    </div>
                </div>
            </div>
            
            <!-- My Courses -->
            <section class="dashboard-section">
                <h2>My Courses</h2>
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
                                <p class="course-description"><?= htmlspecialchars(substr($course['description'], 0, 100)) ?>...</p>
                                
                                <div class="progress-container">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= $course['progress'] ?>%"></div>
                                    </div>
                                    <span class="progress-text"><?= $course['progress'] ?>% Complete</span>
                                </div>
                                
                                <div class="course-footer">
                                    <a href="/student/course/view_course.php?id=<?= $course['id'] ?>" class="btn btn-primary">Continue Learning</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            
            <!-- Recent Activity -->
            <?php if (!empty($recent_activity)): ?>
            <section class="dashboard-section">
                <h2>Recent Activity</h2>
                <div class="activity-list">
                    <?php foreach ($recent_activity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">✅</div>
                            <div class="activity-content">
                                <strong><?= htmlspecialchars($activity['lesson_title']) ?></strong>
                                <p><?= htmlspecialchars($activity['course_title']) ?></p>
                                <small><?= date('M d, Y', strtotime($activity['completed_at'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>

