<?php
require_once __DIR__ . '/../../includes/admin_auth.php';
require_admin_auth();

include '../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/database/QueryBuilder.php';
$db = new QueryBuilder($conn);

// Initialize session variables for CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$course_id = intval($_GET['course_id'] ?? 0);

if ($course_id <= 0) {
    header("Location: manage_courses.php");
    exit;
}

// Get course info
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
if (!$stmt) {
    die("Database error: " . $conn->error);
}
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Check if course exists
if (!$course) {
    header("Location: manage_courses.php");
    exit;
}

// Handle module creation
require_once __DIR__ . '/../../includes/csrf.php';
    CSRF::requireToken();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_module'])) {
    // Verify CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed");
    }
    
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $order_index = intval($_POST['order_index'] ?? 0);
    
    if (empty($title)) {
        $error = "Module title is required";
    } else {
        $stmt = $conn->prepare("INSERT INTO course_modules (course_id, title, description, order_index) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            $error = "Database error: " . $conn->error;
        } else {
            $stmt->bind_param("issi", $course_id, $title, $description, $order_index);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: manage_modules.php?course_id=" . $course_id . "&success=Module created");
                exit;
            } else {
                $error = "Error creating module: " . $stmt->error;
                $stmt->close();
            }
        }
    }
}

// Handle module deletion
if (isset($_GET['delete'])) {
    $module_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM course_modules WHERE id = ? AND course_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $module_id, $course_id);
        $stmt->execute();
        $stmt->close();
        
        header("Location: manage_modules.php?course_id=" . $course_id . "&success=Module deleted");
        exit;
    }
}

// Get modules
$stmt = $conn->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY order_index");
if (!$stmt) {
    die("Database error: " . $conn->error);
}
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
        
        .btn-secondary {
            background: white;
            color: #1a365d;
            border: 2px solid #1a365d;
        }
        
        .btn-secondary:hover {
            background: #f0f4f8;
            transform: translateY(-2px);
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
        
        /* Modules List */
        .modules-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .module-item {
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
        
        .module-item:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        
        .module-info h3 {
            margin: 0 0 0.5rem 0;
            color: #1a365d;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .module-info p {
            color: #718096;
            margin: 0.5rem 0;
            line-height: 1.6;
        }
        
        .module-info small {
            color: #a0aec0;
            display: block;
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }
        
        .module-actions {
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
        #createModal {
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
        
        #createModal.show {
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
        
        #createModal .modal-content {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            max-width: 500px;
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
        
        #createModal .form-group {
            margin-bottom: 1.5rem !important;
        }
        
        .modal .form-group label {
            display: block !important;
            margin-bottom: 0.6rem !important;
            font-weight: 600 !important;
            color: #2d3748 !important;
            font-size: 0.95rem !important;
        }
        
        .modal .form-group label .required {
            color: #ef4444;
        }
        
        #createModal .form-group input,
        #createModal .form-group select,
        #createModal .form-group textarea {
            width: 100% !important;
            padding: 0.85rem !important;
            border: 1.5px solid #e2e8f0 !important;
            border-radius: 8px !important;
            font-size: 0.95rem !important;
            font-family: inherit !important;
            transition: all 0.3s ease !important;
            box-sizing: border-box !important;
        }
        
        #createModal .form-group input:focus,
        #createModal .form-group select:focus,
        #createModal .form-group textarea:focus {
            outline: none !important;
            border-color: #1a365d !important;
            box-shadow: 0 0 0 3px rgba(26, 54, 93, 0.1) !important;
        }
        
        #createModal .form-group textarea {
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
            
            .module-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .module-actions {
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
                <a href="manage_courses.php">
                    <i class="fas fa-chevron-left"></i> Back to Courses
                </a>
                <h1><i class="fas fa-cube"></i> Modules: <?= htmlspecialchars($course['title']) ?></h1>
            </div>
            <div class="page-header-actions">
                <button onclick="openCreateModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Module
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
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Modules List -->
        <div class="modules-list">
            <?php if (empty($modules)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No modules created yet.</p>
                    <p style="color: #a0aec0; font-size: 0.95rem;">Click the "New Module" button to get started.</p>
                </div>
            <?php else: ?>
                <?php foreach ($modules as $module): ?>
                    <div class="module-item">
                        <div class="module-info">
                            <h3><?= htmlspecialchars($module['title']) ?></h3>
                            <p><?= htmlspecialchars($module['description']) ?></p>
                            <small><i class="fas fa-sort"></i> Order Position: <?= $module['order_index'] ?></small>
                        </div>
                        <div class="module-actions">
                            <a href="manage_lessons.php?module_id=<?= $module['id'] ?>" class="btn btn-primary btn-small">
                                <i class="fas fa-book-open"></i> Manage Lessons
                            </a>
                            <a href="?course_id=<?= $course_id ?>&delete=<?= $module['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete this module? This will also delete all associated lessons.')">
                                <i class="fas fa-trash-alt"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Create Module Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <h2><i class="fas fa-cube"></i> Create New Module</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Module Title <span class="required">*</span></label>
                    <input type="text" name="title" placeholder="Enter module title" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Description</label>
                    <textarea name="description" placeholder="Enter module description (optional)"></textarea>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-sort-numeric-up"></i> Order Index</label>
                    <input type="number" name="order_index" value="0" placeholder="Display order">
                </div>
                <div class="form-actions">
                    <button type="submit" name="create_module" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Module
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
        
        // Close modal when clicking outside of it
        document.getElementById('createModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeCreateModal();
            }
        });
    </script>
</body>
</html>





