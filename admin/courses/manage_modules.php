<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/index.php");
    exit;
}

include '../../includes/db_connect.php';

$course_id = intval($_GET['course_id'] ?? 0);

if ($course_id <= 0) {
    header("Location: manage_courses.php");
    exit;
}

// Get course info
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle module creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_module'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $order_index = intval($_POST['order_index'] ?? 0);
    
    $stmt = $conn->prepare("INSERT INTO course_modules (course_id, title, description, order_index) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $course_id, $title, $description, $order_index);
    $stmt->execute();
    $stmt->close();
    
    header("Location: manage_modules.php?course_id=" . $course_id . "&success=Module created");
    exit;
}

// Get modules
$stmt = $conn->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY order_index");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$modules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Modules - <?= htmlspecialchars($course['title']) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .page-header {
            margin-bottom: 2rem;
        }
        .modules-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .module-item {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .module-info h3 {
            margin: 0 0 0.5rem 0;
            color: #1a365d;
        }
        .module-actions {
            display: flex;
            gap: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="page-header">
            <a href="manage_courses.php">← Back to Courses</a>
            <h1>Modules: <?= htmlspecialchars($course['title']) ?></h1>
            <button onclick="openCreateModal()" class="btn btn-primary">+ New Module</button>
        </div>
        
        <div class="modules-list">
            <?php foreach ($modules as $module): ?>
                <div class="module-item">
                    <div class="module-info">
                        <h3><?= htmlspecialchars($module['title']) ?></h3>
                        <p><?= htmlspecialchars($module['description']) ?></p>
                        <small>Order: <?= $module['order_index'] ?></small>
                    </div>
                    <div class="module-actions">
                        <a href="manage_lessons.php?module_id=<?= $module['id'] ?>" class="btn btn-small">Manage Lessons</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Create Module Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <h2>Create New Module</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Module Title *</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"></textarea>
                </div>
                <div class="form-group">
                    <label>Order Index</label>
                    <input type="number" name="order_index" value="0">
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" name="create_module" class="btn btn-primary">Create Module</button>
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

