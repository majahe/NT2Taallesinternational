<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/index.php");
    exit;
}

include '../../includes/db_connect.php';

$lesson_id = intval($_GET['lesson_id'] ?? 0);

if ($lesson_id <= 0) {
    header("Location: ../courses/manage_courses.php");
    exit;
}

// Get lesson info
$stmt = $conn->prepare("SELECT l.*, m.title as module_title, m.course_id FROM lessons l JOIN course_modules m ON l.module_id = m.id WHERE l.id = ?");
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle assignment deletion
if (isset($_GET['delete'])) {
    $assignment_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM assignments WHERE id=? AND lesson_id=?");
    $stmt->bind_param("ii", $assignment_id, $lesson_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: manage_assignments.php?lesson_id=" . $lesson_id . "&success=Assignment deleted");
    exit;
}

// Get assignments
$stmt = $conn->prepare("SELECT * FROM assignments WHERE lesson_id = ?");
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$assignments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get questions for each assignment (for editing)
$assignment_questions = [];
foreach ($assignments as $assignment) {
    $stmt = $conn->prepare("SELECT * FROM assignment_questions WHERE assignment_id = ? ORDER BY order_index");
    $stmt->bind_param("i", $assignment['id']);
    $stmt->execute();
    $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Decode JSON options for each question
    foreach ($questions as &$question) {
        if ($question['options']) {
            $question['options'] = json_decode($question['options'], true);
        }
    }
    
    $assignment_questions[$assignment['id']] = $questions;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments - <?= htmlspecialchars($lesson['title']) ?></title>
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
        
        .btn-small {
            padding: 0.6rem 1.2rem;
            font-size: 0.85rem;
        }
        
        .btn-secondary {
            background: #10b981;
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
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
        
        .alert i {
            font-size: 1.3rem;
        }
        
        /* Assignments List */
        .assignments-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .assignment-item {
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
        
        .assignment-item:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        
        .assignment-info h3 {
            margin: 0 0 0.5rem 0;
            color: #1a365d;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .assignment-info p {
            color: #718096;
            margin: 0.5rem 0;
            line-height: 1.6;
        }
        
        .assignment-meta {
            display: flex;
            gap: 1rem;
            margin: 0.75rem 0 0 0;
            flex-wrap: wrap;
        }
        
        .assignment-meta span {
            background: #f7fafc;
            color: #4a5568;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 0.85rem;
        }
        
        .assignment-actions {
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
            max-width: 700px;
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
            min-height: 100px;
            resize: vertical;
        }
        
        .form-group small {
            display: block;
            color: #718096;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
        
        .question-form {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
            margin: 1rem 0;
            border-radius: 8px;
        }
        
        .question-form h4 {
            color: #1a365d;
            margin: 0 0 1rem 0;
            font-size: 1rem;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .form-actions button,
        .form-actions a {
            flex: 1;
            padding: 0.9rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .form-actions .btn-primary {
            background: linear-gradient(135deg, #1a365d 0%, #2c5282 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(26, 54, 93, 0.25);
        }
        
        .form-actions .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(26, 54, 93, 0.35);
        }
        
        .form-actions .btn-cancel {
            background: #f0f4f8;
            color: #2d3748;
            border: 1.5px solid #e2e8f0;
        }
        
        .form-actions .btn-cancel:hover {
            background: #e2e8f0;
        }
        
        .add-question-btn {
            background: #10b981;
            color: white;
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
        }
        
        .btn-remove {
            background: #ef4444;
            color: white;
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .page-header-actions {
                width: 100%;
                flex-direction: column;
            }
            
            .page-header-actions button,
            .page-header-actions a {
                width: 100%;
            }
            
            .assignment-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .assignment-actions {
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
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions button,
            .form-actions a {
                flex: 1 !important;
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
                <a href="../courses/manage_courses.php"><i class="fas fa-book"></i> Courses</a>
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="admin-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <a href="../courses/manage_lessons.php?module_id=<?= $lesson['module_id'] ?>">
                    <i class="fas fa-chevron-left"></i> Back to Lessons
                </a>
                <h1><i class="fas fa-tasks"></i> Assignments: <?= htmlspecialchars($lesson['title']) ?></h1>
            </div>
            <div class="page-header-actions">
                <a href="create_assignment.php?lesson_id=<?= $lesson_id ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Assignment
                </a>
            </div>
        </div>
        
        <!-- Alerts -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($_GET['success']) ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Assignments List -->
        <div class="assignments-list">
            <?php if (empty($assignments)): ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard"></i>
                    <p>No assignments created yet.</p>
                    <p style="color: #a0aec0; font-size: 0.95rem;">Click the "New Assignment" button to get started.</p>
                </div>
            <?php else: ?>
                <?php foreach ($assignments as $assignment): ?>
                    <div class="assignment-item">
                        <div class="assignment-info">
                            <h3><?= htmlspecialchars($assignment['title']) ?></h3>
                            <p><?= htmlspecialchars(substr($assignment['description'], 0, 150)) ?><?= strlen($assignment['description']) > 150 ? '...' : '' ?></p>
                            <div class="assignment-meta">
                                <span><i class="fas fa-list"></i> <?= ucfirst(str_replace('_', ' ', $assignment['type'])) ?></span>
                                <span><i class="fas fa-star"></i> <?= $assignment['points'] ?> points</span>
                                <span><?= $assignment['is_required'] ? '<i class="fas fa-lock"></i> Required' : '<i class="fas fa-file-alt"></i> Optional' ?></span>
                            </div>
                        </div>
                        <div class="assignment-actions">
                            <a href="view_submissions.php?assignment_id=<?= $assignment['id'] ?>" class="btn btn-secondary btn-small">
                                <i class="fas fa-chart-bar"></i> Submissions
                            </a>
                            <a href="edit_assignment.php?assignment_id=<?= $assignment['id'] ?>" class="btn btn-primary btn-small">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?lesson_id=<?= $lesson_id ?>&delete=<?= $assignment['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this assignment?')">
                                <i class="fas fa-trash-alt"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // The modal and question management JavaScript has been removed as per the edit hint.
        // The assignment creation and editing are now handled by separate pages.
    </script>
</body>
</html>

