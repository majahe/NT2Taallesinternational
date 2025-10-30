<?php
require_once __DIR__ . '/../../includes/admin_auth.php';
require_admin_auth();

include '../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/database/QueryBuilder.php';
$db = new QueryBuilder($conn);

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
require_once __DIR__ . '/../../includes/csrf.php';
    CSRF::requireToken();
    
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
require_once __DIR__ . '/../../includes/csrf.php';
    CSRF::requireToken();
    
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: #2d3748;
        }
        
        /* Header Styles */
        .admin-header {
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content { width: 100%;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-sizing: border-box;
        }
        
        .header-logo { display: flex; flex-shrink: 0;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            color: white;
        }
        
        .header-logo i {
            font-size: 1.8rem;
        }
        
        .header-nav { display: flex; flex-shrink: 0;
            gap: 2rem;
            align-items: center;
        }
        
        .header-nav a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.3s ease;
            white-space: nowrap;
        }
        
        .header-nav a:hover {
            color: white;
        }
        
        /* Main Container */
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        /* Page Header */
        .page-header {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        
        .page-header-content {
            flex: 1;
            min-width: 300px;
        }
        
        .page-header-content a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #1a365d;
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
            transition: color 0.3s ease;
        }
        
        .page-header-content a:hover {
            color: #2c5282;
        }
        
        .page-header h1 {
            font-size: 2rem;
            color: #1a365d;
            margin: 0;
            font-weight: 700;
        }
        
        .page-header-actions {
            display: flex;
            gap: 1rem;
        }
        
        /* Buttons */
        .btn {
            padding: 0.85rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(26, 54, 93, 0.25);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(26, 54, 93, 0.35);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-small {
            padding: 0.6rem 1.2rem;
            font-size: 0.85rem;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.35);
        }
        
        .btn-cancel {
            background: white;
            color: #2d3748;
            border: 1.5px solid #e2e8f0;
        }
        
        .btn-cancel:hover {
            background: #f7fafc;
            border-color: #cbd5e0;
        }
        
        /* Alerts */
        .alert {
            padding: 1.2rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 500;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border-left: 5px solid #10b981;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border-left: 5px solid #ef4444;
        }
        
        .alert i {
            font-size: 1.3rem;
        }
        
        /* Lessons List */
        .lessons-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .lesson-item {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            border-left: 5px solid #1a365d;
        }
        
        .lesson-item:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        
        .lesson-info h3 {
            margin: 0 0 0.5rem 0;
            color: #1a365d;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .lesson-info p {
            color: #718096;
            margin: 0.5rem 0;
            line-height: 1.6;
        }
        
        .lesson-info small {
            color: #a0aec0;
            display: block;
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }
        
        .lesson-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: flex-end;
            align-items: center;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            color: #718096;
            font-size: 1.1rem;
            margin: 1rem 0;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            overflow-y: auto;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .modal.show {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        .modal-content {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            margin: 0;
            animation: slideUp 0.3s ease;
        }
        
        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .modal-content h2 {
            margin: 0 0 1.5rem 0;
            color: #1a365d;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .form-group {
            margin-bottom: 1.5rem !important;
        }
        
        .form-group label {
            display: block !important;
            margin-bottom: 0.6rem !important;
            font-weight: 600 !important;
            color: #2d3748 !important;
            font-size: 0.95rem !important;
        }
        
        .form-group label .required {
            color: #ef4444;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100% !important;
            padding: 0.85rem !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 8px !important;
            font-size: 0.95rem !important;
            font-family: inherit !important;
            transition: all 0.3s ease !important;
            box-sizing: border-box !important;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none !important;
            border-color: #1a365d !important;
            box-shadow: 0 0 0 3px rgba(26, 54, 93, 0.1) !important;
        }
        
        .form-group textarea {
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
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%) !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(26, 54, 93, 0.25) !important;
        }
        
        .form-actions .btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 16px rgba(26, 54, 93, 0.35) !important;
        }
        
        .form-actions .btn-cancel {
            background: #f0f4f8 !important;
            color: #2d3748 !important;
            border: 1.5px solid #e2e8f0 !important;
        }
        
        .form-actions .btn-cancel:hover {
            background: #e2e8f0 !important;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .page-header-actions {
                width: 100%;
            }
            
            .lesson-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .lesson-actions {
                width: 100%;
                justify-content: flex-start;
            }
            
            .header-nav {
                gap: 1rem;
                font-size: 0.85rem;
            }
            
            .admin-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Professional Header -->
    <header class="admin-header">
        <div class="header-content">
            <a href="../../index.php" class="header-logo">
                <i class="fas fa-graduation-cap"></i>
                <span>NT2 Learning Platform</span>
            </a>
            <nav class="header-nav">
                <a href="../dashboard/dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="manage_courses.php"><i class="fas fa-book"></i> Courses</a>
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <a href="manage_modules.php?course_id=<?= $module['course_id'] ?>">
                    <i class="fas fa-chevron-left"></i> Back to Modules
                </a>
                <h1><i class="fas fa-book"></i> Lessons: <?= htmlspecialchars($module['title']) ?></h1>
            </div>
            <div class="page-header-actions">
                <button onclick="openCreateModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Lesson
                </button>
            </div>
        </div>
        
        <!-- Alerts -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($_GET['success']) ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Lessons List -->
        <div class="lessons-list">
            <?php if (empty($lessons)): ?>
                <div class="empty-state">
                    <i class="fas fa-book"></i>
                    <p>No lessons created yet.</p>
                    <p style="color: #a0aec0; font-size: 0.95rem;">Click the "New Lesson" button to get started.</p>
                </div>
            <?php else: ?>
                <?php foreach ($lessons as $lesson): ?>
                    <div class="lesson-item">
                        <div class="lesson-info">
                            <h3><?= htmlspecialchars($lesson['title']) ?></h3>
                            <p><?= htmlspecialchars(substr($lesson['description'], 0, 150)) ?><?= strlen($lesson['description']) > 150 ? '...' : '' ?></p>
                            <?php if ($lesson['video_path']): ?>
                                <small><i class="fas fa-video"></i> <?= htmlspecialchars($lesson['video_path']) ?></small>
                            <?php endif; ?>
                            <small><i class="fas fa-sort"></i> Order Position: <?= $lesson['order_index'] ?></small>
                        </div>
                        <div class="lesson-actions">
                            <a href="edit_lesson.php?lesson_id=<?= $lesson['id'] ?>" class="btn btn-primary btn-small">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?module_id=<?= $module_id ?>&delete=<?= $lesson['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this lesson?')">
                                <i class="fas fa-trash-alt"></i> Delete
                            </a>
                            <a href="../assignments/manage_assignments.php?lesson_id=<?= $lesson['id'] ?>" class="btn btn-primary btn-small">
                                <i class="fas fa-tasks"></i> Assignments
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Create Lesson Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <h2><i class="fas fa-book-plus"></i> Create New Lesson</h2>
            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Lesson Title <span class="required">*</span></label>
                    <input type="text" name="title" placeholder="Enter lesson title" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Description</label>
                    <textarea name="description" placeholder="Enter lesson description (optional)"></textarea>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-file-alt"></i> Content</label>
                    <textarea name="content" rows="8" placeholder="Enter lesson content"></textarea>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-video"></i> Video Path</label>
                    <input type="text" name="video_path" placeholder="/uploads/videos/video.mp4">
                    <small style="color: #718096;">Upload video first, then paste path here</small>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-sort-numeric-up"></i> Order Index</label>
                    <input type="number" name="order_index" value="0" placeholder="Display order">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_preview"> <i class="fas fa-eye"></i> Preview Lesson (Available in free preview)
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" name="create_lesson" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Lesson
                    </button>
                    <button type="button" onclick="closeCreateModal()" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </button>
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
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const createModal = document.getElementById('createModal');
            if (event.target === createModal) {
                closeCreateModal();
            }
        }
    </script>
</body>
</html>

