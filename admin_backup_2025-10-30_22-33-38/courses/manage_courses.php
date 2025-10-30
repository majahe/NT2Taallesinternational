<?php
require_once __DIR__ . '/../../includes/admin_auth.php';
require_admin_auth();

include '../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/database/QueryBuilder.php';
$db = new QueryBuilder($conn);

// Handle course creation
require_once __DIR__ . '/../../includes/csrf.php';
    CSRF::requireToken();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_course'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $level = $_POST['level'] ?? 'Beginner';
    $language_from = $_POST['language_from'] ?? '';
    $language_to = $_POST['language_to'] ?? 'Dutch';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $stmt = $conn->prepare("INSERT INTO courses (title, description, level, language_from, language_to, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $title, $description, $level, $language_from, $language_to, $is_active);
    $stmt->execute();
    $stmt->close();
    
    header("Location: manage_courses.php?success=Course created");
    exit;
}

// Handle course deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: manage_courses.php?success=Course deleted");
    exit;
}

// Get all courses
$result = $conn->query("SELECT * FROM courses ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .course-card-admin {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .course-card-admin h3 {
            margin: 0 0 0.5rem 0;
            color: #1a365d;
        }
        .course-meta {
            display: flex;
            gap: 1rem;
            margin: 1rem 0;
            font-size: 0.9rem;
            color: #666;
        }
        .course-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.show {
            display: flex;
        }
        .modal-content {
            background: white;
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
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .form-group textarea {
            min-height: 100px;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-active {
            background: #d1fae5;
            color: #065f46;
        }
        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="page-header">
            <h1>Manage Courses</h1>
            <div>
                <a href="../dashboard/dashboard.php" class="btn">← Dashboard</a>
                <button onclick="openCreateModal()" class="btn btn-primary">+ New Course</button>
            </div>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        
        <div class="courses-grid">
            <?php while ($course = $result->fetch_assoc()): ?>
                <div class="course-card-admin">
                    <h3><?= htmlspecialchars($course['title']) ?></h3>
                    <p><?= htmlspecialchars(substr($course['description'], 0, 100)) ?>...</p>
                    <div class="course-meta">
                        <span><?= htmlspecialchars($course['level']) ?></span>
                        <span>•</span>
                        <span><?= htmlspecialchars($course['language_from']) ?> → <?= htmlspecialchars($course['language_to']) ?></span>
                    </div>
                    <div>
                        <span class="status-badge status-<?= $course['is_active'] ? 'active' : 'inactive' ?>">
                            <?= $course['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                    <div class="course-actions">
                        <a href="manage_modules.php?course_id=<?= $course['id'] ?>" class="btn btn-small">Manage Modules</a>
                        <a href="?delete=<?= $course['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete this course?')">Delete</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    
    <!-- Create Course Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <h2>Create New Course</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Course Title *</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"></textarea>
                </div>
                <div class="form-group">
                    <label>Level</label>
                    <select name="level">
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Language From</label>
                    <input type="text" name="language_from" placeholder="e.g., English, Russian" required>
                </div>
                <div class="form-group">
                    <label>Language To</label>
                    <input type="text" name="language_to" value="Dutch" required>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" checked> Active
                    </label>
                </div>
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" name="create_course" class="btn btn-primary">Create Course</button>
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

