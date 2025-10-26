<?php
/**
 * Student Authentication & Authorization Functions
 * Provides session management and access control for students
 */

session_start();

/**
 * Check if student is logged in
 */
function check_student_login() {
    return isset($_SESSION['student_id']) && isset($_SESSION['student_email']);
}

/**
 * Require student login - redirect if not logged in
 */
function require_student_login() {
    if (!check_student_login()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: /student/auth/login.php");
        exit;
    }
}

/**
 * Check if student has access to a course
 */
function check_course_access($student_id, $course_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT se.* 
        FROM student_enrollments se 
        WHERE se.student_id = ? 
        AND se.course_id = ? 
        AND se.status = 'active'
        AND (se.access_until IS NULL OR se.access_until >= CURDATE())
    ");
    $stmt->bind_param("ii", $student_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result->num_rows > 0;
}

/**
 * Check if lesson is unlocked for student
 * A lesson is unlocked if:
 * 1. It's the first lesson in its module, OR
 * 2. Previous lesson in module is completed
 */
function check_lesson_unlocked($student_id, $lesson_id) {
    global $conn;
    
    // Get lesson details
    $stmt = $conn->prepare("
        SELECT l.module_id, l.order_index, m.course_id
        FROM lessons l
        JOIN course_modules m ON l.module_id = m.id
        WHERE l.id = ?
    ");
    $stmt->bind_param("i", $lesson_id);
    $stmt->execute();
    $lesson = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$lesson) return false;
    
    // Check course access first
    if (!check_course_access($student_id, $lesson['course_id'])) {
        return false;
    }
    
    // If it's the first lesson in module, it's unlocked
    if ($lesson['order_index'] == 1) {
        return true;
    }
    
    // Check if previous lesson is completed
    $stmt = $conn->prepare("
        SELECT l2.id
        FROM lessons l2
        WHERE l2.module_id = ?
        AND l2.order_index = ?
    ");
    $prev_order = $lesson['order_index'] - 1;
    $stmt->bind_param("ii", $lesson['module_id'], $prev_order);
    $stmt->execute();
    $prev_lesson = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$prev_lesson) return true;
    
    // Check if previous lesson is completed
    $stmt = $conn->prepare("
        SELECT status 
        FROM student_progress 
        WHERE student_id = ? AND lesson_id = ? AND status = 'completed'
    ");
    $stmt->bind_param("ii", $student_id, $prev_lesson['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result->num_rows > 0;
}

/**
 * Get student's enrolled courses
 */
function get_student_courses($student_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT c.*, se.enrolled_at, se.access_until, se.status as enrollment_status
        FROM courses c
        JOIN student_enrollments se ON c.id = se.course_id
        WHERE se.student_id = ?
        AND se.status = 'active'
        AND (se.access_until IS NULL OR se.access_until >= CURDATE())
        ORDER BY se.enrolled_at DESC
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    $stmt->close();
    
    return $courses;
}

/**
 * Get course progress percentage for student
 */
function get_course_progress($student_id, $course_id) {
    global $conn;
    
    // Get total lessons in course
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM lessons l
        JOIN course_modules m ON l.module_id = m.id
        WHERE m.course_id = ?
    ");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    
    if ($total == 0) return 0;
    
    // Get completed lessons
    $stmt = $conn->prepare("
        SELECT COUNT(*) as completed
        FROM student_progress sp
        JOIN lessons l ON sp.lesson_id = l.id
        JOIN course_modules m ON l.module_id = m.id
        WHERE sp.student_id = ?
        AND m.course_id = ?
        AND sp.status = 'completed'
    ");
    $stmt->bind_param("ii", $student_id, $course_id);
    $stmt->execute();
    $completed = $stmt->get_result()->fetch_assoc()['completed'];
    $stmt->close();
    
    return round(($completed / $total) * 100);
}

/**
 * Get student's total points
 */
function get_student_points($student_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT SUM(score) as total_points
        FROM student_assignments
        WHERE student_id = ? AND status IN ('graded', 'returned')
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['total_points'] ?? 0;
}

