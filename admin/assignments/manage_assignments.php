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

// Handle assignment update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_assignment'])) {
    $assignment_id = intval($_POST['assignment_id']);
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $type = $_POST['type'] ?? 'multiple_choice';
    $points = intval($_POST['points'] ?? 10);
    $is_required = isset($_POST['is_required']) ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE assignments SET title=?, description=?, type=?, points=?, is_required=? WHERE id=? AND lesson_id=?");
    $stmt->bind_param("sssiiii", $title, $description, $type, $points, $is_required, $assignment_id, $lesson_id);
    $stmt->execute();
    $stmt->close();
    
    // Update questions
    if (isset($_POST['questions']) && is_array($_POST['questions'])) {
        // First, delete existing questions
        $stmt = $conn->prepare("DELETE FROM assignment_questions WHERE assignment_id = ?");
        $stmt->bind_param("i", $assignment_id);
        $stmt->execute();
        $stmt->close();
        
        // Then add updated questions
        foreach ($_POST['questions'] as $qdata) {
            $question_text = $qdata['text'] ?? '';
            $question_type = $qdata['type'] ?? 'multiple_choice';
            $correct_answer = $qdata['correct'] ?? '';
            
            // Handle options properly
            $options = null;
            if (isset($qdata['options'])) {
                if (is_array($qdata['options'])) {
                    $options = json_encode($qdata['options']);
                } else {
                    $options = $qdata['options']; // Already JSON string
                }
            }
            
            $qpoints = intval($qdata['points'] ?? 1);
            $order_index = intval($qdata['order'] ?? 0);
            
            $stmt = $conn->prepare("INSERT INTO assignment_questions (assignment_id, question_text, question_type, correct_answer, options, points, order_index) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssii", $assignment_id, $question_text, $question_type, $correct_answer, $options, $qpoints, $order_index);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    header("Location: manage_assignments.php?lesson_id=" . $lesson_id . "&success=Assignment updated");
    exit;
}

// Handle assignment creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_assignment'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $type = $_POST['type'] ?? 'multiple_choice';
    $points = intval($_POST['points'] ?? 10);
    $is_required = isset($_POST['is_required']) ? 1 : 0;
    
    $stmt = $conn->prepare("INSERT INTO assignments (lesson_id, title, description, type, points, is_required) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssii", $lesson_id, $title, $description, $type, $points, $is_required);
    $stmt->execute();
    $assignment_id = $conn->insert_id;
    $stmt->close();
    
    // Add questions
    if (isset($_POST['questions']) && is_array($_POST['questions'])) {
        foreach ($_POST['questions'] as $qdata) {
            $question_text = $qdata['text'] ?? '';
            $question_type = $qdata['type'] ?? 'multiple_choice';
            $correct_answer = $qdata['correct'] ?? '';
            $options = isset($qdata['options']) ? json_encode($qdata['options']) : null;
            $qpoints = intval($qdata['points'] ?? 1);
            $order_index = intval($qdata['order'] ?? 0);
            
            $stmt = $conn->prepare("INSERT INTO assignment_questions (assignment_id, question_text, question_type, correct_answer, options, points, order_index) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssii", $assignment_id, $question_text, $question_type, $correct_answer, $options, $qpoints, $order_index);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    header("Location: manage_assignments.php?lesson_id=" . $lesson_id . "&success=Assignment created");
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
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .assignments-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .assignment-item {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .assignment-item h3 {
            margin: 0 0 0.5rem 0;
            color: #1a365d;
        }
        .assignment-meta {
            display: flex;
            gap: 1rem;
            margin: 0.5rem 0;
            font-size: 0.875rem;
            color: #718096;
        }
        .assignment-meta span {
            background: #f7fafc;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        .assignment-actions {
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
        .btn-success {
            background-color: #48bb78;
            color: white;
        }
        .btn-success:hover {
            background-color: #38a169;
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
            max-width: 800px;
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
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1rem;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3182ce;
            box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
        }
        .form-group small {
            color: #718096;
            font-size: 0.875rem;
        }
        .question-form {
            border: 1px solid #e2e8f0;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 8px;
            background: #f7fafc;
        }
        .breadcrumb {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            color: #4a5568;
        }
        .breadcrumb a {
            color: #3182ce;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
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
        
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="../dashboard/dashboard.php">Dashboard</a> → 
            <a href="../courses/manage_courses.php">Courses</a> → 
            <a href="../courses/manage_modules.php?course_id=<?= $lesson['course_id'] ?? '' ?>">Modules</a> → 
            <a href="../courses/manage_lessons.php?module_id=<?= $lesson['module_id'] ?>">Lessons</a> → 
            <strong>Assignments</strong>
        </div>
        
        <div class="page-header">
            <a href="../courses/manage_lessons.php?module_id=<?= $lesson['module_id'] ?>">← Back to Lessons</a>
            <h1>📝 Assignments: <?= htmlspecialchars($lesson['title']) ?></h1>
            <div style="display: flex; gap: 0.5rem;">
                <button onclick="openCreateModal()" class="btn btn-primary">+ New Assignment</button>
                <a href="../courses/upload_video.php" class="btn btn-small" style="background-color: #10b981; color: white;">🎥 Upload Video</a>
            </div>
        </div>
        
        <div class="assignments-list">
            <?php if (empty($assignments)): ?>
                <div class="assignment-item" style="text-align: center; padding: 3rem;">
                    <h3>No assignments yet</h3>
                    <p>Create your first assignment for this lesson.</p>
                    <button onclick="openCreateModal()" class="btn btn-primary">+ Create First Assignment</button>
                </div>
            <?php else: ?>
                <?php foreach ($assignments as $assignment): ?>
                    <div class="assignment-item">
                        <h3><?= htmlspecialchars($assignment['title']) ?></h3>
                        <p><?= htmlspecialchars($assignment['description']) ?></p>
                        <div class="assignment-meta">
                            <span>📋 Type: <?= ucfirst(str_replace('_', ' ', $assignment['type'])) ?></span>
                            <span>⭐ Points: <?= $assignment['points'] ?></span>
                            <span><?= $assignment['is_required'] ? '🔒 Required' : '📝 Optional' ?></span>
                        </div>
                        <div class="assignment-actions">
                            <a href="view_submissions.php?assignment_id=<?= $assignment['id'] ?>" class="btn btn-small btn-success">📊 View Submissions</a>
                            <button onclick="openEditModal(<?= $assignment['id'] ?>)" class="btn btn-small btn-primary">✏️ Edit</button>
                            <a href="manage_assignments.php?lesson_id=<?= $lesson_id ?>&delete=<?= $assignment['id'] ?>" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this assignment?')">🗑️ Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Create Assignment Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <h2>📝 Create New Assignment</h2>
            <form method="POST" id="assignmentForm">
                <div class="form-group">
                    <label>Assignment Title *</label>
                    <input type="text" name="title" required placeholder="e.g., Vocabulary Quiz">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Describe what students need to do..."></textarea>
                </div>
                <div class="form-group">
                    <label>Assignment Type</label>
                    <select name="type" id="assignmentType">
                        <option value="multiple_choice">📋 Multiple Choice</option>
                        <option value="fill_in">✏️ Fill in the Blank</option>
                        <option value="essay">📝 Essay</option>
                        <option value="file_upload">📎 File Upload</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Points</label>
                    <input type="number" name="points" value="10" min="1" max="100">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_required" checked> 🔒 Required Assignment
                    </label>
                </div>
                
                <div id="questionsContainer">
                    <h3>📝 Questions</h3>
                    <div id="questionsList"></div>
                    <button type="button" onclick="addQuestion()" class="btn btn-small" style="background-color: #48bb78; color: white;">+ Add Question</button>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" name="create_assignment" class="btn btn-primary">✅ Create Assignment</button>
                    <button type="button" onclick="closeCreateModal()" class="btn">❌ Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Assignment Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>✏️ Edit Assignment</h2>
            <form method="POST">
                <input type="hidden" name="assignment_id" id="edit_assignment_id">
                <div class="form-group">
                    <label>Assignment Title *</label>
                    <input type="text" name="title" id="edit_title" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="edit_description"></textarea>
                </div>
                <div class="form-group">
                    <label>Assignment Type</label>
                    <select name="type" id="edit_type">
                        <option value="multiple_choice">📋 Multiple Choice</option>
                        <option value="fill_in">✏️ Fill in the Blank</option>
                        <option value="essay">📝 Essay</option>
                        <option value="file_upload">📎 File Upload</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Points</label>
                    <input type="number" name="points" id="edit_points" min="1" max="100">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_required" id="edit_is_required"> 🔒 Required Assignment
                    </label>
                </div>
                
                <div id="editQuestionsContainer">
                    <h3>📝 Questions</h3>
                    <div id="editQuestionsList"></div>
                    <button type="button" onclick="addEditQuestion()" class="btn btn-small" style="background-color: #48bb78; color: white;">+ Add Question</button>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" name="update_assignment" class="btn btn-primary">💾 Update Assignment</button>
                    <button type="button" onclick="closeEditModal()" class="btn">❌ Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let questionCount = 0;
        
        function addQuestion() {
            questionCount++;
            const type = document.getElementById('assignmentType').value;
            const html = `
                <div class="question-form">
                    <h4>📝 Question ${questionCount}</h4>
                    <div class="form-group">
                        <label>Question Text *</label>
                        <textarea name="questions[${questionCount}][text]" required placeholder="Enter your question here..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Question Type</label>
                        <select name="questions[${questionCount}][type]" onchange="updateQuestionType(${questionCount}, this.value)">
                            <option value="multiple_choice">📋 Multiple Choice</option>
                            <option value="fill_in">✏️ Fill in the Blank</option>
                            <option value="essay">📝 Essay</option>
                            <option value="file_upload">📎 File Upload</option>
                        </select>
                    </div>
                    <div id="question-${questionCount}-options"></div>
                    <div class="form-group">
                        <label>Points</label>
                        <input type="number" name="questions[${questionCount}][points]" value="1" min="1">
                    </div>
                    <input type="hidden" name="questions[${questionCount}][order]" value="${questionCount}">
                    <button type="button" onclick="removeQuestion(${questionCount})" class="btn btn-small btn-danger" style="margin-top: 0.5rem;">🗑️ Remove Question</button>
                </div>
            `;
            document.getElementById('questionsList').insertAdjacentHTML('beforeend', html);
            updateQuestionType(questionCount, 'multiple_choice');
        }
        
        function removeQuestion(qid) {
            const questionElement = document.querySelector(`#question-${qid}-options`).closest('.question-form');
            questionElement.remove();
        }
        
        function updateQuestionType(qid, type) {
            const container = document.getElementById(`question-${qid}-options`);
            if (type === 'multiple_choice') {
                container.innerHTML = `
                    <div class="form-group">
                        <label>📋 Options (one per line)</label>
                        <textarea name="questions[${qid}][options_text]" rows="4" placeholder="Option A&#10;Option B&#10;Option C&#10;Option D"></textarea>
                    </div>
                    <div class="form-group">
                        <label>✅ Correct Answer</label>
                        <input type="text" name="questions[${qid}][correct]" required placeholder="e.g., Option A">
                    </div>
                `;
            } else if (type === 'fill_in') {
                container.innerHTML = `
                    <div class="form-group">
                        <label>✅ Correct Answer</label>
                        <input type="text" name="questions[${qid}][correct]" required placeholder="The correct answer">
                    </div>
                `;
            } else {
                container.innerHTML = '';
            }
        }
        
        document.getElementById('assignmentForm').addEventListener('submit', function(e) {
            // Convert options text to JSON array
            const optionsTexts = document.querySelectorAll('[name$="[options_text]"]');
            optionsTexts.forEach(function(el) {
                const qid = el.name.match(/\[(\d+)\]/)[1];
                const options = el.value.split('\n').filter(o => o.trim());
                const correctAnswer = document.querySelector(`[name="questions[${qid}][correct]"]`).value;
                
                // Create hidden input with JSON options
                const jsonInput = document.createElement('input');
                jsonInput.type = 'hidden';
                jsonInput.name = `questions[${qid}][options]`;
                jsonInput.value = JSON.stringify(options);
                el.parentNode.appendChild(jsonInput);
            });
        });
        
         // Handle edit form submission
         document.querySelector('#editModal form').addEventListener('submit', function(e) {
             console.log('Edit form submitting...');
             
             // Convert options text to JSON array for edit form
             const optionsTexts = document.querySelectorAll('#editModal [name$="[options_text]"]');
             console.log('Found options texts:', optionsTexts.length);
             
             optionsTexts.forEach(function(el) {
                 const qid = el.name.match(/\[(\d+)\]/)[1];
                 const options = el.value.split('\n').filter(o => o.trim());
                 console.log(`Question ${qid} options:`, options);
                 
                 // Create hidden input with JSON options
                 const jsonInput = document.createElement('input');
                 jsonInput.type = 'hidden';
                 jsonInput.name = `questions[${qid}][options]`;
                 jsonInput.value = JSON.stringify(options);
                 el.parentNode.appendChild(jsonInput);
                 
                 console.log(`Added hidden input for question ${qid}:`, jsonInput.value);
             });
             
             // Also handle correct answers
             const correctAnswers = document.querySelectorAll('#editModal [name$="[correct]"]');
             correctAnswers.forEach(function(el) {
                 const qid = el.name.match(/\[(\d+)\]/)[1];
                 const correctAnswer = el.value;
                 console.log(`Question ${qid} correct answer:`, correctAnswer);
                 
                 // Ensure correct answer is set
                 if (!correctAnswer.trim()) {
                     alert('Please fill in the correct answer for all questions');
                     e.preventDefault();
                     return false;
                 }
             });
             
             // Debug: Log all form data
             const formData = new FormData(this);
             console.log('Form data being submitted:');
             for (let [key, value] of formData.entries()) {
                 console.log(key, ':', value);
             }
         });
        
        function openCreateModal() {
            document.getElementById('createModal').classList.add('show');
        }
        function closeCreateModal() {
            document.getElementById('createModal').classList.remove('show');
            // Reset form
            document.getElementById('assignmentForm').reset();
            document.getElementById('questionsList').innerHTML = '';
            questionCount = 0;
        }
        
        function openEditModal(assignmentId) {
            // Get assignment data from the button
            const button = event.target;
            const assignmentItem = button.closest('.assignment-item');
            const title = assignmentItem.querySelector('h3').textContent;
            const description = assignmentItem.querySelector('p').textContent;
            
            // Extract type and points from meta spans
            const metaSpans = assignmentItem.querySelectorAll('.assignment-meta span');
            let type = 'multiple_choice';
            let points = 10;
            let isRequired = false;
            
            metaSpans.forEach(span => {
                if (span.textContent.includes('Type:')) {
                    type = span.textContent.replace('📋 Type: ', '').toLowerCase().replace(' ', '_');
                }
                if (span.textContent.includes('Points:')) {
                    points = parseInt(span.textContent.replace('⭐ Points: ', ''));
                }
                if (span.textContent.includes('Required')) {
                    isRequired = true;
                }
            });
            
            // Populate edit form
            document.getElementById('edit_assignment_id').value = assignmentId;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_type').value = type;
            document.getElementById('edit_points').value = points;
            document.getElementById('edit_is_required').checked = isRequired;
            
            // Load existing questions
            loadEditQuestions(assignmentId);
            
            document.getElementById('editModal').classList.add('show');
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
            // Clear edit questions
            document.getElementById('editQuestionsList').innerHTML = '';
            editQuestionCount = 0;
        }
        
        let editQuestionCount = 0;
        
        function loadEditQuestions(assignmentId) {
            // Get questions from PHP data
            const questions = <?= json_encode($assignment_questions) ?>;
            const assignmentQuestions = questions[assignmentId] || [];
            
            // Clear existing questions
            document.getElementById('editQuestionsList').innerHTML = '';
            editQuestionCount = 0;
            
            // Add each existing question
            assignmentQuestions.forEach(question => {
                addEditQuestion(question);
            });
        }
        
        function addEditQuestion(existingQuestion = null) {
            editQuestionCount++;
            const qid = editQuestionCount;
            
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
                    <h4>📝 Question ${qid}</h4>
                    <div class="form-group">
                        <label>Question Text *</label>
                        <textarea name="questions[${qid}][text]" required placeholder="Enter your question here...">${questionText}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Question Type</label>
                        <select name="questions[${qid}][type]" onchange="updateEditQuestionType(${qid}, this.value)">
                            <option value="multiple_choice" ${questionType === 'multiple_choice' ? 'selected' : ''}>📋 Multiple Choice</option>
                            <option value="fill_in" ${questionType === 'fill_in' ? 'selected' : ''}>✏️ Fill in the Blank</option>
                            <option value="essay" ${questionType === 'essay' ? 'selected' : ''}>📝 Essay</option>
                            <option value="file_upload" ${questionType === 'file_upload' ? 'selected' : ''}>📎 File Upload</option>
                        </select>
                    </div>
                    <div id="edit-question-${qid}-options"></div>
                    <div class="form-group">
                        <label>Points</label>
                        <input type="number" name="questions[${qid}][points]" value="${points}" min="1">
                    </div>
                    <input type="hidden" name="questions[${qid}][order]" value="${qid}">
                    <button type="button" onclick="removeEditQuestion(${qid})" class="btn btn-small btn-danger" style="margin-top: 0.5rem;">🗑️ Remove Question</button>
                </div>
            `;
            document.getElementById('editQuestionsList').insertAdjacentHTML('beforeend', html);
            updateEditQuestionType(qid, questionType, correctAnswer, optionsText);
        }
        
        function removeEditQuestion(qid) {
            const questionElement = document.querySelector(`#edit-question-${qid}-options`).closest('.question-form');
            questionElement.remove();
        }
        
        function updateEditQuestionType(qid, type, correctAnswer = '', optionsText = '') {
            const container = document.getElementById(`edit-question-${qid}-options`);
            if (type === 'multiple_choice') {
                container.innerHTML = `
                    <div class="form-group">
                        <label>📋 Options (one per line)</label>
                        <textarea name="questions[${qid}][options_text]" rows="4" placeholder="Option A&#10;Option B&#10;Option C&#10;Option D">${optionsText}</textarea>
                    </div>
                    <div class="form-group">
                        <label>✅ Correct Answer</label>
                        <input type="text" name="questions[${qid}][correct]" required placeholder="e.g., Option A" value="${correctAnswer}">
                    </div>
                `;
            } else if (type === 'fill_in') {
                container.innerHTML = `
                    <div class="form-group">
                        <label>✅ Correct Answer</label>
                        <input type="text" name="questions[${qid}][correct]" required placeholder="The correct answer" value="${correctAnswer}">
                    </div>
                `;
            } else {
                container.innerHTML = '';
            }
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

