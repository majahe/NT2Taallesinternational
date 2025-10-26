<?php
// Debug script to check assignment questions
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/index.php");
    exit;
}

include '../../includes/db_connect.php';

$assignment_id = intval($_GET['assignment_id'] ?? 0);

if ($assignment_id <= 0) {
    echo "Please provide assignment_id";
    exit;
}

echo "<h2>Debug Assignment Questions</h2>";
echo "<p>Assignment ID: $assignment_id</p>";

// Get assignment info
$stmt = $conn->prepare("SELECT * FROM assignments WHERE id = ?");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();
$stmt->close();

echo "<h3>Assignment Info:</h3>";
echo "<pre>" . print_r($assignment, true) . "</pre>";

// Get questions
$stmt = $conn->prepare("SELECT * FROM assignment_questions WHERE assignment_id = ? ORDER BY order_index");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo "<h3>Questions:</h3>";
foreach ($questions as $question) {
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo "<strong>Question:</strong> " . htmlspecialchars($question['question_text']) . "<br>";
    echo "<strong>Type:</strong> " . htmlspecialchars($question['question_type']) . "<br>";
    echo "<strong>Correct Answer:</strong> " . htmlspecialchars($question['correct_answer']) . "<br>";
    echo "<strong>Options (Raw):</strong> " . htmlspecialchars($question['options']) . "<br>";
    
    if ($question['options']) {
        $decoded_options = json_decode($question['options'], true);
        echo "<strong>Options (Decoded):</strong> ";
        if (is_array($decoded_options)) {
            echo "<pre>" . print_r($decoded_options, true) . "</pre>";
        } else {
            echo "Failed to decode JSON<br>";
        }
    } else {
        echo "<strong>Options:</strong> NULL<br>";
    }
    echo "</div>";
}

echo "<h3>Raw Database Query:</h3>";
echo "<pre>SELECT * FROM assignment_questions WHERE assignment_id = $assignment_id ORDER BY order_index</pre>";

// Test JSON encoding/decoding
echo "<h3>JSON Test:</h3>";
$test_options = ["Option A", "Option B", "Option C"];
$encoded = json_encode($test_options);
$decoded = json_decode($encoded, true);
echo "Original: " . print_r($test_options, true) . "<br>";
echo "Encoded: " . htmlspecialchars($encoded) . "<br>";
echo "Decoded: " . print_r($decoded, true) . "<br>";
?>
