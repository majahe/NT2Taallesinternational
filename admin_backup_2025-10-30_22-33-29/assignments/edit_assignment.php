<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/index.php");
    exit;
}

include '../../includes/db_connect.php';

$assignment_id = intval($_GET['assignment_id'] ?? 0);

if ($assignment_id <= 0) {
    header("Location: manage_assignments.php");
    exit;
}

// Get assignment info with lesson details
$stmt = $conn->prepare("
    SELECT a.*, l.id as lesson_id, l.title as lesson_title, m.id as module_id
    FROM assignments a
    JOIN lessons l ON a.lesson_id = l.id
    JOIN course_modules m ON l.module_id = m.id
    WHERE a.id = ?
");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$assignment) {
    header("Location: manage_assignments.php");
    exit;
}

// Get assignment questions
$stmt = $conn->prepare("SELECT * FROM assignment_questions WHERE assignment_id = ? ORDER BY order_index");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Decode JSON options
foreach ($questions as &$question) {
    if ($question['options']) {
        $question['options'] = json_decode($question['options'], true);
    }
}

// Handle assignment update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_assignment'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $type = $_POST['type'] ?? 'multiple_choice';
    $points = intval($_POST['points'] ?? 10);
    $is_required = isset($_POST['is_required']) ? 1 : 0;
    
    if (empty($title)) {
        $error = "Assignment title is required";
    } else {
        $stmt = $conn->prepare("UPDATE assignments SET title=?, description=?, type=?, points=?, is_required=? WHERE id=?");
        $stmt->bind_param("sssiiii", $title, $description, $type, $points, $is_required, $assignment_id);
        if ($stmt->execute()) {
            $stmt->close();
            
            // Delete existing questions
            $stmt = $conn->prepare("DELETE FROM assignment_questions WHERE assignment_id = ?");
            $stmt->bind_param("i", $assignment_id);
            $stmt->execute();
            $stmt->close();
            
            // Add updated questions
            if (isset($_POST['questions']) && is_array($_POST['questions'])) {
                foreach ($_POST['questions'] as $qdata) {
                    $question_text = $qdata['text'] ?? '';
                    $question_type = $qdata['type'] ?? 'multiple_choice';
                    $correct_answer = $qdata['correct'] ?? '';
                    $options = isset($qdata['options']) ? json_encode($qdata['options']) : null;
                    $qpoints = intval($qdata['points'] ?? 1);
                    $order_index = intval($qdata['order'] ?? 0);
                    
                    $qstmt = $conn->prepare("INSERT INTO assignment_questions (assignment_id, question_text, question_type, correct_answer, options, points, order_index) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $qstmt->bind_param("issssii", $assignment_id, $question_text, $question_type, $correct_answer, $options, $qpoints, $order_index);
                    $qstmt->execute();
                    $qstmt->close();
                }
            }
            
            header("Location: manage_assignments.php?lesson_id=" . $assignment['lesson_id'] . "&success=Assignment updated successfully");
            exit;
        } else {
            $error = "Error updating assignment: " . $stmt->error;
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
    <title>Edit Assignment - <?= htmlspecialchars($assignment['title']) ?></title>
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
        
        .header-content {
            width: 100%;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-sizing: border-box;
        }
        
        .header-logo {
            display: flex;
            flex-shrink: 0;
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
        
        .header-nav {
            display: flex;
            flex-shrink: 0;
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
            min-height: 100px;
            resize: vertical;
        }
        
        .form-group small {
            display: block;
            color: #718096;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
        
        .questions-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .questions-section h3 {
            color: #1a365d;
            margin-bottom: 1rem;
            font-size: 1.1rem;
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
            flex: 1;
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
            background: rgb(3, 161, 252);
            color: #2d3748;
            border: 1.5px solid #e2e8f0;
        }
        
        .btn-cancel:hover {
            background:rgb(3, 161, 252);
            border-color: #cbd5e0;
        }
        
        .btn-secondary {
            background: #10b981;
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
            flex: none;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35);
        }
        
        .btn-remove {
            background: #ef4444;
            color: white;
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
            flex: none;
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
                <a href="manage_assignments.php?lesson_id=<?= $assignment['lesson_id'] ?>">
                    <i class="fas fa-chevron-left"></i> Back to Assignments
                </a>
                <h1><i class="fas fa-edit"></i> Edit Assignment</h1>
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
            <form method="POST" id="assignmentForm">
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Assignment Title <span class="required">*</span></label>
                    <input type="text" name="title" value="<?= htmlspecialchars($assignment['title']) ?>" placeholder="e.g., Vocabulary Quiz" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Description</label>
                    <textarea name="description" placeholder="Describe what students need to do..."><?= htmlspecialchars($assignment['description']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-list"></i> Assignment Type</label>
                    <select name="type" id="assignmentType">
                        <option value="multiple_choice" <?= $assignment['type'] === 'multiple_choice' ? 'selected' : '' ?>>Multiple Choice</option>
                        <option value="fill_in" <?= $assignment['type'] === 'fill_in' ? 'selected' : '' ?>>Fill in the Blank</option>
                        <option value="essay" <?= $assignment['type'] === 'essay' ? 'selected' : '' ?>>Essay</option>
                        <option value="file_upload" <?= $assignment['type'] === 'file_upload' ? 'selected' : '' ?>>File Upload</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-star"></i> Points</label>
                    <input type="number" name="points" value="<?= $assignment['points'] ?>" min="1" max="100">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_required" <?= $assignment['is_required'] ? 'checked' : '' ?>> 
                        <i class="fas fa-lock"></i> Required Assignment
                    </label>
                </div>
                
                <div class="questions-section">
                    <h3><i class="fas fa-question-circle"></i> Questions</h3>
                    <div id="questionsList"></div>
                    <button type="button" onclick="addQuestion()" class="btn btn-secondary">
                        <i class="fas fa-plus"></i> Add Question
                    </button>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update_assignment" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Assignment
                    </button>
                    <a href="manage_assignments.php?lesson_id=<?= $assignment['lesson_id'] ?>" class="btn btn-cancel">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let questionCount = 0;
        const existingQuestions = <?= json_encode($questions) ?>;
        
        function initializeQuestions() {
            existingQuestions.forEach(question => {
                addQuestion(question);
            });
        }
        
        function addQuestion(existingQuestion = null) {
            questionCount++;
            const qid = questionCount;
            
            let questionText = '';
            let questionType = 'multiple_choice';
            let correctAnswer = '';
            let optionsText = '';
            let points = 1;
            
            if (existingQuestion) {
                questionText = existingQuestion.question_text || '';
                questionType = existingQuestion.question_type || 'multiple_choice';
                correctAnswer = existingQuestion.correct_answer || '';
                points = existingQuestion.points || 1;
                
                if (existingQuestion.options && Array.isArray(existingQuestion.options)) {
                    optionsText = existingQuestion.options.join('\n');
                }
            }
            
            const html = `
                <div class="question-form">
                    <h4><i class="fas fa-question"></i> Question ${qid}</h4>
                    <div class="form-group">
                        <label>Question Text <span class="required">*</span></label>
                        <textarea name="questions[${qid}][text]" required placeholder="Enter your question here...">${questionText}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Question Type</label>
                        <select name="questions[${qid}][type]" onchange="updateQuestionType(${qid}, this.value)">
                            <option value="multiple_choice" ${questionType === 'multiple_choice' ? 'selected' : ''}>Multiple Choice</option>
                            <option value="fill_in" ${questionType === 'fill_in' ? 'selected' : ''}>Fill in the Blank</option>
                            <option value="essay" ${questionType === 'essay' ? 'selected' : ''}>Essay</option>
                            <option value="file_upload" ${questionType === 'file_upload' ? 'selected' : ''}>File Upload</option>
                        </select>
                    </div>
                    <div id="question-${qid}-options"></div>
                    <div class="form-group">
                        <label><i class="fas fa-star"></i> Points</label>
                        <input type="number" name="questions[${qid}][points]" value="${points}" min="1">
                    </div>
                    <input type="hidden" name="questions[${qid}][order]" value="${qid}">
                    <button type="button" onclick="removeQuestion(${qid})" class="btn btn-remove">
                        <i class="fas fa-trash-alt"></i> Remove
                    </button>
                </div>
            `;
            document.getElementById('questionsList').insertAdjacentHTML('beforeend', html);
            updateQuestionType(qid, questionType, correctAnswer, optionsText);
        }
        
        function removeQuestion(qid) {
            const questionElement = document.querySelector(`#question-${qid}-options`).closest('.question-form');
            questionElement.remove();
        }
        
        function updateQuestionType(qid, type, correctAnswer = '', optionsText = '') {
            const container = document.getElementById(`question-${qid}-options`);
            if (type === 'multiple_choice') {
                container.innerHTML = `
                    <div class="form-group">
                        <label>Options (one per line)</label>
                        <textarea name="questions[${qid}][options_text]" rows="3" placeholder="Option A&#10;Option B&#10;Option C&#10;Option D">${optionsText}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Correct Answer</label>
                        <input type="text" name="questions[${qid}][correct]" required placeholder="e.g., Option A" value="${correctAnswer}">
                    </div>
                `;
            } else if (type === 'fill_in') {
                container.innerHTML = `
                    <div class="form-group">
                        <label>Correct Answer</label>
                        <input type="text" name="questions[${qid}][correct]" required placeholder="The correct answer" value="${correctAnswer}">
                    </div>
                `;
            } else {
                container.innerHTML = '';
            }
        }
        
        document.getElementById('assignmentForm').addEventListener('submit', function(e) {
            const optionsTexts = document.querySelectorAll('[name$="[options_text]"]');
            optionsTexts.forEach(function(el) {
                const qid = el.name.match(/\[(\d+)\]/)[1];
                const options = el.value.split('\n').filter(o => o.trim());
                const jsonInput = document.createElement('input');
                jsonInput.type = 'hidden';
                jsonInput.name = `questions[${qid}][options]`;
                jsonInput.value = JSON.stringify(options);
                el.parentNode.appendChild(jsonInput);
            });
        });
        
        // Initialize existing questions
        window.addEventListener('DOMContentLoaded', initializeQuestions);
    </script>
</body>
</html>
