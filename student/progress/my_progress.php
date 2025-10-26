<?php
require_once __DIR__ . '/../../includes/student_auth.php';
require_once __DIR__ . '/../../includes/db_connect.php';
require_student_login();

$student_id = $_SESSION['student_id'];
$courses = get_student_courses($student_id);

// Get detailed progress for each course
$detailed_progress = [];
foreach ($courses as $course) {
    $progress = get_course_progress($student_id, $course['id']);
    $total_points = get_student_points($student_id);
    
    // Get completed lessons count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as completed
        FROM student_progress sp
        JOIN lessons l ON sp.lesson_id = l.id
        JOIN course_modules m ON l.module_id = m.id
        WHERE sp.student_id = ? AND m.course_id = ? AND sp.status = 'completed'
    ");
    $stmt->bind_param("ii", $student_id, $course['id']);
    $stmt->execute();
    $completed = $stmt->get_result()->fetch_assoc()['completed'];
    $stmt->close();
    
    // Get total lessons
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM lessons l
        JOIN course_modules m ON l.module_id = m.id
        WHERE m.course_id = ?
    ");
    $stmt->bind_param("i", $course['id']);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    
    $detailed_progress[] = [
        'course' => $course,
        'progress' => $progress,
        'completed' => $completed,
        'total' => $total,
        'points' => $total_points
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Progress - NT2 Taalles International</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/student_portal.css">
</head>
<body>
    <?php include __DIR__ . '/../../includes/student_header.php'; ?>
    
    <main class="student-main">
        <div class="container">
            <h1>My Progress</h1>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-content">
                        <h3><?= count($courses) ?></h3>
                        <p>Courses Enrolled</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <h3><?= array_sum(array_column($detailed_progress, 'completed')) ?></h3>
                        <p>Lessons Completed</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-content">
                        <h3><?= get_student_points($student_id) ?></h3>
                        <p>Total Points</p>
                    </div>
                </div>
            </div>
            
            <?php foreach ($detailed_progress as $item): ?>
                <div class="dashboard-section">
                    <h2><?= htmlspecialchars($item['course']['title']) ?></h2>
                    
                    <div class="progress-container">
                        <div class="progress-bar-large">
                            <div class="progress-fill-large" style="width: <?= $item['progress'] ?>%"></div>
                        </div>
                        <span class="progress-percentage"><?= $item['progress'] ?>% Complete</span>
                    </div>
                    
                    <div class="progress-details">
                        <p><strong>Lessons:</strong> <?= $item['completed'] ?> / <?= $item['total'] ?> completed</p>
                        <p><strong>Course Level:</strong> <?= htmlspecialchars($item['course']['level']) ?></p>
                        <p><strong>Language:</strong> <?= htmlspecialchars($item['course']['language_from']) ?> → <?= htmlspecialchars($item['course']['language_to']) ?></p>
                    </div>
                    
                    <a href="/student/course/view_course.php?id=<?= $item['course']['id'] ?>" class="btn btn-primary">Continue Learning</a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    
    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>

