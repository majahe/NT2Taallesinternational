<?php
// Contact form submission handler - Simplified version
session_start();

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

try {
    // Include database connection
    require_once __DIR__ . '/../includes/db_connect.php';
    
    // Sanitize and validate input data
    function sanitize_input($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
    
    function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    // Get form data
    $firstName = sanitize_input($_POST['firstName'] ?? '');
    $lastName = sanitize_input($_POST['lastName'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $courseInterest = sanitize_input($_POST['courseInterest'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    $privacy = isset($_POST['privacy']) ? 1 : 0;
    
    // Validation
    $errors = [];
    
    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($email) || !validate_email($email)) {
        $errors[] = 'Valid email address is required';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    if (!$privacy) {
        $errors[] = 'You must agree to the Privacy Policy and Terms of Service';
    }
    
    // If there are validation errors, redirect back with errors
    if (!empty($errors)) {
        $_SESSION['contact_errors'] = $errors;
        $_SESSION['contact_form_data'] = $_POST;
        header('Location: /pages/contact.php');
        exit;
    }
    
    // Check if database connection exists
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Select the database
    if (!$conn->select_db(DB_NAME)) {
        throw new Exception('Could not select database: ' . DB_NAME);
    }
    
    // Prepare SQL statement using MySQLi
    $stmt = $conn->prepare("
        INSERT INTO contact_messages 
        (first_name, last_name, email, phone, subject, course_interest, message, newsletter_opt_in, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    // Bind parameters
    $stmt->bind_param("sssssssi", $firstName, $lastName, $email, $phone, $subject, $courseInterest, $message, $newsletter);
    
    // Execute the statement
    $result = $stmt->execute();
    
    if ($result) {
        // Set success message
        $_SESSION['contact_success'] = 'Thank you for your message! We will get back to you within 24 hours.';
        
        // Clean up form data on success
        unset($_SESSION['contact_form_data']);
        
        // Redirect to success page
        header('Location: /pages/contact_success.php');
        exit;
    } else {
        throw new Exception('Failed to save contact message: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    // Log error
    error_log('Contact form error: ' . $e->getMessage());
    
    // Set error message
    $_SESSION['contact_errors'] = ['Sorry, there was an error processing your request. Please try again later.'];
    $_SESSION['contact_form_data'] = $_POST;
    
    // Redirect back to contact page
    header('Location: /pages/contact.php');
    exit;
}
?>
