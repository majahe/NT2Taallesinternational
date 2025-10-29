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
        .modal {
            display: none !important;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.show {
            display: flex !important;
        }
        .modal-content {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .modal-content h2 {
            margin: 0 0 1.5rem 0;
            color: #1a365d;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .modal .form-group {
            margin-bottom: 1.5rem !important;
        }
        .modal .form-group label {
            display: block !important;
            margin-bottom: 0.6rem !important;
            font-weight: 600 !important;
            color: #2d3748 !important;
            font-size: 0.95rem !important;
        }
        .modal .form-group input,
        .modal .form-group select,
        .modal .form-group textarea {
            width: 100% !important;
            padding: 0.85rem !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 8px !important;
            font-size: 0.95rem !important;
            font-family: inherit !important;
            transition: border-color 0.3s ease !important;
        }
        .modal .form-group input:focus,
        .modal .form-group select:focus,
        .modal .form-group textarea:focus {
            outline: none !important;
            border-color: #1a365d !important;
            box-shadow: 0 0 0 3px rgba(26, 54, 93, 0.1) !important;
        }
        .modal .form-group textarea {
            min-height: 120px !important;
            resize: vertical !important;
        }
        .form-actions {
            display: flex !important;
            gap: 1rem !important;
            margin-top: 2rem !important;
            padding-top: 1.5rem !important;
            border-top: 1px solid #e2e8f0 !important;
        }
        .form-actions button {
            flex: 1 !important;
            padding: 0.9rem 1.5rem !important;
            border: none !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
        }
        .form-actions .btn-primary {
            background: #1a365d !important;
            color: white !important;
        }
        .form-actions .btn-primary:hover {
            background: #0f2444 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 8px 16px rgba(26, 54, 93, 0.2) !important;
        }
        .form-actions .btn-cancel {
            background: #f0f4f8 !important;
            color: #2d3748 !important;
            border: 1.5px solid #e2e8f0 !important;
        }
        .form-actions .btn-cancel:hover {
            background: #e2e8f0 !important;
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
                <div class="form-actions">
                    <button type="submit" name="create_module" class="btn btn-primary">Create Module</button>
                    <button type="button" onclick="closeCreateModal()" class="btn btn-cancel">Cancel</button>
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

