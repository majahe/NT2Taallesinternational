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
$stmt = $conn->prepare("SELECT l.*, m.title as module_title FROM lessons l JOIN course_modules m ON l.module_id = m.id WHERE l.id = ?");
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();
$stmt->close();

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments - <?= htmlspecialchars($lesson['title']) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="admin-container" style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
        <div class="page-header">
            <a href="../courses/manage_lessons.php?module_id=<?= $lesson['module_id'] ?>">← Back to Lessons</a>
            <h1>Assignments: <?= htmlspecialchars($lesson['title']) ?></h1>
            <button onclick="openCreateModal()" class="btn btn-primary">+ New Assignment</button>
        </div>
        
        <div class="assignments-list">
            <?php foreach ($assignments as $assignment): ?>
                <div class="assignment-item">
                    <h3><?= htmlspecialchars($assignment['title']) ?></h3>
                    <p><?= htmlspecialchars($assignment['description']) ?></p>
                    <div class="assignment-meta">
                        <span>Type: <?= ucfirst(str_replace('_', ' ', $assignment['type'])) ?></span>
                        <span>Points: <?= $assignment['points'] ?></span>
                    </div>
                    <div class="assignment-actions">
                        <a href="view_submissions.php?assignment_id=<?= $assignment['id'] ?>" class="btn btn-small">View Submissions</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Create Assignment Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <h2>Create New Assignment</h2>
            <form method="POST" id="assignmentForm">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"></textarea>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" id="assignmentType">
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="fill_in">Fill in the Blank</option>
                        <option value="essay">Essay</option>
                        <option value="file_upload">File Upload</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Points</label>
                    <input type="number" name="points" value="10">
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="is_required" checked> Required</label>
                </div>
                
                <div id="questionsContainer">
                    <h3>Questions</h3>
                    <div id="questionsList"></div>
                    <button type="button" onclick="addQuestion()" class="btn btn-small">+ Add Question</button>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" name="create_assignment" class="btn btn-primary">Create Assignment</button>
                    <button type="button" onclick="closeCreateModal()" class="btn">Cancel</button>
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
                <div class="question-form" style="border: 1px solid #ddd; padding: 1rem; margin: 1rem 0; border-radius: 8px;">
                    <h4>Question ${questionCount}</h4>
                    <div class="form-group">
                        <label>Question Text *</label>
                        <textarea name="questions[${questionCount}][text]" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Question Type</label>
                        <select name="questions[${questionCount}][type]" onchange="updateQuestionType(${questionCount}, this.value)">
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="fill_in">Fill in the Blank</option>
                            <option value="essay">Essay</option>
                            <option value="file_upload">File Upload</option>
                        </select>
                    </div>
                    <div id="question-${questionCount}-options"></div>
                    <div class="form-group">
                        <label>Points</label>
                        <input type="number" name="questions[${questionCount}][points]" value="1">
                    </div>
                    <input type="hidden" name="questions[${questionCount}][order]" value="${questionCount}">
                </div>
            `;
            document.getElementById('questionsList').insertAdjacentHTML('beforeend', html);
            updateQuestionType(questionCount, 'multiple_choice');
        }
        
        function updateQuestionType(qid, type) {
            const container = document.getElementById(`question-${qid}-options`);
            if (type === 'multiple_choice') {
                container.innerHTML = `
                    <div class="form-group">
                        <label>Options (one per line)</label>
                        <textarea name="questions[${qid}][options_text]" rows="4" placeholder="Option 1&#10;Option 2&#10;Option 3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Correct Answer</label>
                        <input type="text" name="questions[${qid}][correct]" required>
                    </div>
                `;
            } else if (type === 'fill_in') {
                container.innerHTML = `
                    <div class="form-group">
                        <label>Correct Answer</label>
                        <input type="text" name="questions[${qid}][correct]" required>
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
        
        function openCreateModal() {
            document.getElementById('createModal').classList.add('show');
        }
        function closeCreateModal() {
            document.getElementById('createModal').classList.remove('show');
        }
    </script>
</body>
</html>

