<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/index.php");
    exit;
}

include '../../includes/db_connect.php';

$lesson_id = intval($_GET['lesson_id'] ?? 0);
$module_id = intval($_GET['module_id'] ?? 0);

if ($lesson_id <= 0) {
    header("Location: manage_courses.php");
    exit;
}

// Get lesson info
$stmt = $conn->prepare("
    SELECT l.*, m.id as module_id, m.course_id, m.title as module_title, c.title as course_title
    FROM lessons l
    JOIN course_modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    WHERE l.id = ?
");
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$lesson) {
    header("Location: manage_courses.php");
    exit;
}

// Handle lesson update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_lesson'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $content = $_POST['content'] ?? '';
    $video_path = $_POST['video_path'] ?? '';
    $order_index = intval($_POST['order_index'] ?? 0);
    $is_preview = isset($_POST['is_preview']) ? 1 : 0;
    
    if (empty($title)) {
        $error = "Lesson title is required";
    } else {
        $stmt = $conn->prepare("UPDATE lessons SET title=?, description=?, content=?, video_path=?, order_index=?, is_preview=? WHERE id=? AND module_id=?");
        $stmt->bind_param("ssssiiii", $title, $description, $content, $video_path, $order_index, $is_preview, $lesson_id, $lesson['module_id']);
        if ($stmt->execute()) {
            $stmt->close();
            header("Location: manage_lessons.php?module_id=" . $lesson['module_id'] . "&success=Lesson updated successfully");
            exit;
        } else {
            $error = "Error updating lesson: " . $stmt->error;
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Lesson - <?= htmlspecialchars($lesson['title']) ?></title>
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
            max-width: 1000px;
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
        
        /* Edit Form */
        .edit-form {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 600;
            color: #2d3748;
            font-size: 0.95rem;
        }
        
        .form-group label .required {
            color: #ef4444;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.85rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #1a365d;
            box-shadow: 0 0 0 3px rgba(26, 54, 93, 0.1);
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-group small {
            display: block;
            color: #718096;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        
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
        
        .btn-cancel {
            background: white;
            color: #2d3748;
            border: 1.5px solid #e2e8f0;
        }
        
        .btn-cancel:hover {
            background: #f7fafc;
            border-color: #cbd5e0;
        }
        
        /* Alert */
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
        
        .alert-error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border-left: 5px solid #ef4444;
        }
        
        .alert i {
            font-size: 1.3rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
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
                <a href="manage_lessons.php?module_id=<?= $lesson['module_id'] ?>">
                    <i class="fas fa-chevron-left"></i> Back to Lessons
                </a>
                <h1><i class="fas fa-book-edit"></i> Edit Lesson</h1>
            </div>
        </div>
        
        <!-- Error Alert -->
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Edit Form -->
        <div class="edit-form">
            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Lesson Title <span class="required">*</span></label>
                    <input type="text" name="title" value="<?= htmlspecialchars($lesson['title']) ?>" placeholder="Enter lesson title" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Description</label>
                    <textarea name="description" placeholder="Enter lesson description (optional)"><?= htmlspecialchars($lesson['description']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-file-alt"></i> Content</label>
                    <textarea name="content" placeholder="Enter lesson content"><?= htmlspecialchars($lesson['content']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-video"></i> Video Path</label>
                    <input type="text" name="video_path" value="<?= htmlspecialchars($lesson['video_path']) ?>" placeholder="/uploads/videos/video.mp4">
                    <small>Upload video first, then paste path here</small>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-sort-numeric-up"></i> Order Index</label>
                    <input type="number" name="order_index" value="<?= $lesson['order_index'] ?>" placeholder="Display order">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_preview" <?= $lesson['is_preview'] ? 'checked' : '' ?>> 
                        <i class="fas fa-eye"></i> Preview Lesson (Available in free preview)
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update_lesson" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Lesson
                    </button>
                    <a href="manage_lessons.php?module_id=<?= $lesson['module_id'] ?>" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
