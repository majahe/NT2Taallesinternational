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

// Handle lesson update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_lesson'])) {
    $lesson_id = intval($_POST['lesson_id']);
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $content = $_POST['content'] ?? '';
    $video_path = $_POST['video_path'] ?? '';
    $order_index = intval($_POST['order_index'] ?? 0);
    $is_preview = isset($_POST['is_preview']) ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE lessons SET title=?, description=?, content=?, video_path=?, order_index=?, is_preview=? WHERE id=? AND module_id=?");
    $stmt->bind_param("ssssiiii", $title, $description, $content, $video_path, $order_index, $is_preview, $lesson_id, $module_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: manage_lessons.php?module_id=" . $module_id . "&success=Lesson updated");
    exit;
}

// Handle lesson deletion
if (isset($_GET['delete'])) {
    $lesson_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM lessons WHERE id=? AND module_id=?");
    $stmt->bind_param("ii", $lesson_id, $module_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: manage_lessons.php?module_id=" . $module_id . "&success=Lesson deleted");
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
        .btn-small {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            border-radius: 6px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background-color: #3182ce;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2c5aa0;
        }
        .btn-danger {
            background-color: #e53e3e;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c53030;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #1a365d;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1rem;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }
        .form-group small {
            color: #718096;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php if (isset($_GET['success'])): ?>
            <div style="background-color: #c6f6d5; color: #22543d; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                ✅ <?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>
        
        <div class="page-header">
            <a href="manage_modules.php?course_id=<?= $module['course_id'] ?>">← Back to Modules</a>
            <h1>Lessons: <?= htmlspecialchars($module['title']) ?></h1>
            <div style="display: flex; gap: 0.5rem;">
                <button onclick="openCreateModal()" class="btn btn-primary">+ New Lesson</button>
                <button onclick="testEditModal()" class="btn" style="background-color: #48bb78; color: white;">Test Edit</button>
            </div>
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
                        <button onclick="openEditModal(<?= $lesson['id'] ?>)" 
                                class="btn btn-small btn-primary"
                                data-lesson-id="<?= $lesson['id'] ?>"
                                data-lesson-title="<?= htmlspecialchars($lesson['title'], ENT_QUOTES) ?>"
                                data-lesson-description="<?= htmlspecialchars($lesson['description'], ENT_QUOTES) ?>"
                                data-lesson-content="<?= htmlspecialchars($lesson['content'], ENT_QUOTES) ?>"
                                data-lesson-video-path="<?= htmlspecialchars($lesson['video_path'], ENT_QUOTES) ?>"
                                data-lesson-order-index="<?= $lesson['order_index'] ?>"
                                data-lesson-is-preview="<?= $lesson['is_preview'] ?>">Edit</button>
                        <a href="manage_lessons.php?module_id=<?= $module_id ?>&delete=<?= $lesson['id'] ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this lesson?')">Delete</a>
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
    
    <!-- Edit Lesson Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Edit Lesson</h2>
            <form method="POST">
                <input type="hidden" name="lesson_id" id="edit_lesson_id">
                <div class="form-group">
                    <label>Lesson Title *</label>
                    <input type="text" name="title" id="edit_title" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_description"></textarea>
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" id="edit_content" rows="10"></textarea>
                </div>
                <div class="form-group">
                    <label>Video Path</label>
                    <input type="text" name="video_path" id="edit_video_path" placeholder="/uploads/videos/video.mp4">
                    <small>Upload video first, then paste path here</small>
                </div>
                <div class="form-group">
                    <label>Order Index</label>
                    <input type="number" name="order_index" id="edit_order_index" value="0">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_preview" id="edit_is_preview"> Preview Lesson
                    </label>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" name="update_lesson" class="btn btn-primary">Update Lesson</button>
                    <button type="button" onclick="closeEditModal()" class="btn">Cancel</button>
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
        
        function openEditModal(lessonId) {
            console.log('Opening edit modal for lesson:', lessonId);
            const button = event.target;
            const title = button.getAttribute('data-lesson-title');
            const description = button.getAttribute('data-lesson-description');
            const content = button.getAttribute('data-lesson-content');
            const videoPath = button.getAttribute('data-lesson-video-path');
            const orderIndex = button.getAttribute('data-lesson-order-index');
            const isPreview = button.getAttribute('data-lesson-is-preview');
            
            console.log('Lesson data:', {title, description, content, videoPath, orderIndex, isPreview});
            
            document.getElementById('edit_lesson_id').value = lessonId;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_content').value = content;
            document.getElementById('edit_video_path').value = videoPath;
            document.getElementById('edit_order_index').value = orderIndex;
            document.getElementById('edit_is_preview').checked = isPreview == 1;
            document.getElementById('editModal').classList.add('show');
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }
        
        function testEditModal() {
            console.log('Testing edit modal...');
            document.getElementById('edit_lesson_id').value = '999';
            document.getElementById('edit_title').value = 'Test Lesson';
            document.getElementById('edit_description').value = 'Test Description';
            document.getElementById('edit_content').value = 'Test Content';
            document.getElementById('edit_video_path').value = '/test/video.mp4';
            document.getElementById('edit_order_index').value = '1';
            document.getElementById('edit_is_preview').checked = true;
            document.getElementById('editModal').classList.add('show');
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const createModal = document.getElementById('createModal');
            const editModal = document.getElementById('editModal');
            if (event.target === createModal) {
                closeCreateModal();
            }
            if (event.target === editModal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>

