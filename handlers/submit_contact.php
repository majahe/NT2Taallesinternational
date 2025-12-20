<?php
// Contact form submission handler - Simplified version
use PHPMailer\PHPMailer\PHPMailer;

session_start();

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $_SESSION['contact_errors'] = ['Invalid request method.'];
    header('Location: /pages/contact.php');
    exit;
}

try {
    // Include required files
    require_once __DIR__ . '/../includes/db_connect.php';
    require_once __DIR__ . '/../includes/config.php';
    require_once __DIR__ . '/../includes/rate_limit.php';
    require __DIR__ . '/../includes/PHPMailer/src/Exception.php';
    require __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/../includes/PHPMailer/src/SMTP.php';

    // Rate Limiting
    if (!RateLimit::check('contact')) {
        $remainingTime = RateLimit::getTimeUntilReset('contact');
        $minutes = ceil($remainingTime / 60);
        $_SESSION['contact_errors'] = ["Too many contact form submissions. Please try again in {$minutes} minute(s)."];
        header('Location: /pages/contact.php');
        exit;
    }
    
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
        // Send email notification to admin
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            
            // SSL Configuration for local development
            if (!SMTP_SSL_VERIFY) {
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
            }
            
            // Map subject values to readable text
            $subjectMap = [
                'general' => 'General Inquiry',
                'course-info' => 'Course Information',
                'registration' => 'Registration Help',
                'technical' => 'Technical Support',
                'feedback' => 'Feedback',
                'other' => 'Other'
            ];
            
            $courseInterestMap = [
                'russian-dutch' => 'Russian to Dutch',
                'english-dutch' => 'English to Dutch',
                'both' => 'Both Courses',
                'not-sure' => 'Not Sure Yet'
            ];
            
            $subjectText = $subjectMap[$subject] ?? $subject;
            $courseInterestText = !empty($courseInterest) ? ($courseInterestMap[$courseInterest] ?? $courseInterest) : 'Not specified';
            
            // Email to admin
            $mail->setFrom(SMTP_FROM_EMAIL, 'NT2 Website');
            $mail->addAddress(ADMIN_EMAIL);
            $mail->isHTML(true);
            
            // Build styled email content
            $adminDashboardUrl = WEBSITE_URL . '/admin/auth/index.php';
            $phoneDisplay = !empty($phone) ? $phone : 'Not provided';
            $messageDisplay = !empty($message) ? nl2br(htmlspecialchars($message)) : 'No message provided';
            
            $mailContent = "
            <div style='font-family:Arial,Helvetica,sans-serif;background:#f6f8fc;padding:20px;'>
                <div style='max-width:600px;margin:auto;background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.1);overflow:hidden;'>
                    <div style='background:linear-gradient(135deg,#6366f1,#7c3aed);padding:20px;text-align:center;'>
                        <h1 style='color:#fff;margin:0;font-size:24px;font-weight:bold;'>NT2 Taalles International</h1>
                    </div>
                    
                    <div style='padding:30px;color:#333;'>
                        <h2 style='color:#4f46e5;margin-top:0;font-size:22px;'>New Contact Form</h2>
                        <p style='font-size:15px;line-height:1.6;color:#666;margin-bottom:20px;'>New Contact Form Submission</p>
                        
                        <div style='font-size:15px;line-height:1.8;'>
                            <p style='margin:10px 0;'><strong style='color:#333;'>Name:</strong> " . htmlspecialchars($firstName . ' ' . $lastName) . "</p>
                            <p style='margin:10px 0;'><strong style='color:#333;'>Email:</strong> <a href='mailto:" . htmlspecialchars($email) . "' style='color:#6366f1;text-decoration:none;'>" . htmlspecialchars($email) . "</a></p>
                            <p style='margin:10px 0;'><strong style='color:#333;'>Phone:</strong> " . htmlspecialchars($phoneDisplay) . "</p>
                            <p style='margin:10px 0;'><strong style='color:#333;'>Subject:</strong> " . htmlspecialchars($subjectText) . "</p>
                            <p style='margin:10px 0;'><strong style='color:#333;'>Course Interest:</strong> " . htmlspecialchars($courseInterestText) . "</p>
                            <p style='margin:10px 0;'><strong style='color:#333;'>Newsletter Opt-in:</strong> " . ($newsletter ? 'Yes' : 'No') . "</p>
                            " . (!empty($message) ? "<p style='margin:15px 0 10px 0;'><strong style='color:#333;'>Message:</strong></p><div style='background:#f1f3f8;padding:15px;border-radius:6px;margin-top:10px;'>" . $messageDisplay . "</div>" : "") . "
                        </div>
                        
                        <div style='text-align:center;margin-top:30px;'>
                            <a href='{$adminDashboardUrl}' style='background:#6366f1;color:#fff;padding:12px 25px;border-radius:6px;text-decoration:none;font-weight:bold;display:inline-block;'>Open Admin Dashboard</a>
                        </div>
                    </div>
                    
                    <div style='background:#f1f3f8;padding:15px;text-align:center;font-size:13px;color:#666;'>
                        <p style='margin:5px 0;'>© " . date('Y') . " NT2 Taalles International</p>
                        <p style='margin:5px 0;'>
                            <a href='" . WEBSITE_URL . "' style='color:#6366f1;text-decoration:none;'>Website</a> |
                            <a href='mailto:" . ADMIN_EMAIL . "' style='color:#6366f1;text-decoration:none;'>Contact us</a>
                        </p>
                    </div>
                </div>
            </div>
            ";
            
            $mail->Subject = "New Contact Form: {$subjectText}";
            $mail->Body = $mailContent;
            $mail->send();
            
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            // Log email error but continue (don't fail the form submission)
            error_log('Contact form email error: ' . $mail->ErrorInfo);
        }
        
        // Increment rate limit counter on successful submission
        RateLimit::increment('contact');
        
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
