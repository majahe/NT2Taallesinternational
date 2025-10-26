<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/index.php");
    exit;
}

include '../../includes/db_connect.php';

$module_id = intval($_GET['module_id'] ?? 0);

if ($module_id <= 0) {
    header("Location: manage_courses.php");
    exit;
}

// Get module and course info
$stmt = $conn->prepare("
    SELECT m.*, c.title as course_title 
    FROM course_modules m 
    JOIN courses c ON m.course_id = c.id 
    WHERE m.id = ?
");
$stmt->bind_param("i", $module_id);
$stmt->execute();
$module = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle lesson creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_lesson'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $content = $_POST['content'] ?? '';
    $video_path = $_POST['video_path'] ?? '';
    $order_index = intval($_POST['order_index'] ?? 0);
    $is_preview = isset($_POST['is_preview']) ? 1 : 0;
    
    $stmt = $conn->prepare("INSERT INTO lessons (module_id, title, description, content, video_path, order_index, is_preview) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssii", $module_id, $title, $description, $content, $video_path, $order_index, $is_preview);
    $stmt->execute();
    $stmt->close();
    
    header("Location: manage_lessons.php?module_id=" . $module_id . "&success=Lesson created");
    exit;
}

// Get lessons
$stmt = $conn->prepare("SELECT * FROM lessons WHERE module_id = ? ORDER BY order_index");
$stmt->bind_param("i", $module_id);
$stmt->execute();
$lessons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lessons - <?= htmlspecialchars($module['title']) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .lessons-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .lesson-item {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .lesson-item h3 {
            margin: 0 0 0.5rem 0;
            color: #1a365d;
        }
        .lesson-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="page-header">
            <a href="manage_modules.php?course_id=<?= $module['course_id'] ?>">← Back to Modules</a>
            <h1>Lessons: <?= htmlspecialchars($module['title']) ?></h1>
            <button onclick="openCreateModal()" class="btn btn-primary">+ New Lesson</button>
        </div>
        
        <div class="lessons-list">
            <?php foreach ($lessons as $lesson): ?>
                <div class="lesson-item">
                    <h3><?= htmlspecialchars($lesson['title']) ?></h3>
                    <p><?= htmlspecialchars(substr($lesson['description'], 0, 100)) ?>...</p>
                    <?php if ($lesson['video_path']): ?>
                        <small>Video: <?= htmlspecialchars($lesson['video_path']) ?></small>
                    <?php endif; ?>
                    <div class="lesson-actions">
                        <a href="../assignments/manage_assignments.php?lesson_id=<?= $lesson['id'] ?>" class="btn btn-small">Manage Assignments</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Create Lesson Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <h2>Create New Lesson</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Lesson Title *</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"></textarea>
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" rows="10"></textarea>
                </div>
                <div class="form-group">
                    <label>Video Path</label>
                    <input type="text" name="video_path" placeholder="/uploads/videos/video.mp4">
                    <small>Upload video first, then paste path here</small>
                </div>
                <div class="form-group">
                    <label>Order Index</label>
                    <input type="number" name="order_index" value="0">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_preview"> Preview Lesson
                    </label>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" name="create_lesson" class="btn btn-primary">Create Lesson</button>
                    <button type="button" onclick="closeCreateModal()" class="btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openCreateModal() {
            document.getElementById('createModal').classList.add('show');
        }
        function closeCreateModal() {
            document.getElementById('createModal').classList.remove('show');
        }
    </script>
</body>
</html>

