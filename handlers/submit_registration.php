<?php
// Registration form submission handler - Simplified
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

try {
    require __DIR__ . '/../includes/PHPMailer/src/Exception.php';
    require __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/../includes/PHPMailer/src/SMTP.php';
    require __DIR__ . '/../includes/config.php';
    require __DIR__ . '/../includes/db_connect.php';
    require __DIR__ . '/../includes/csrf.php';
    require __DIR__ . '/../includes/rate_limit.php';

    // CSRF Protection
    CSRF::requireToken();

    // reCAPTCHA Verification
    if (empty(RECAPTCHA_SECRET_KEY)) {
        error_log("reCAPTCHA secret key is not configured");
        $_SESSION['registration_error'] = 'reCAPTCHA is not properly configured. Please contact the administrator.';
        header('Location: /pages/register.php');
        exit;
    }

    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    if (empty($recaptchaResponse)) {
        $_SESSION['registration_error'] = 'Please complete the reCAPTCHA verification.';
        header('Location: /pages/register.php');
        exit;
    }

    // Verify reCAPTCHA with Google
    $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptchaData = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];

    $recaptchaOptions = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($recaptchaData)
        ]
    ];

    $recaptchaContext = stream_context_create($recaptchaOptions);
    $recaptchaResult = @file_get_contents($recaptchaUrl, false, $recaptchaContext);
    
    if ($recaptchaResult === false) {
        error_log("Failed to verify reCAPTCHA: Could not connect to Google's servers");
        $_SESSION['registration_error'] = 'Unable to verify reCAPTCHA. Please try again later.';
        header('Location: /pages/register.php');
        exit;
    }

    $recaptchaJson = json_decode($recaptchaResult, true);
    
    if (!isset($recaptchaJson['success']) || $recaptchaJson['success'] !== true) {
        $errorCodes = $recaptchaJson['error-codes'] ?? ['unknown'];
        error_log("reCAPTCHA verification failed: " . implode(', ', $errorCodes));
        $_SESSION['registration_error'] = 'reCAPTCHA verification failed. Please try again.';
        header('Location: /pages/register.php');
        exit;
    }

    // Rate Limiting
    if (!RateLimit::check('registration')) {
        $remainingTime = RateLimit::getTimeUntilReset('registration');
        $minutes = ceil($remainingTime / 60);
        $_SESSION['registration_error'] = "Too many registration attempts. Please try again in {$minutes} minute(s).";
        header('Location: /pages/register.php');
        exit;
    }

    // Get form data with null coalescing operators for safety
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $spoken_language = trim($_POST['spoken_language'] ?? '');
    $preferred_time = trim($_POST['preferred_time'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate required fields
    if (empty($name) || empty($email) || empty($course) || empty($spoken_language) || empty($preferred_time)) {
        $_SESSION['registration_error'] = 'Please fill in all required fields.';
        header('Location: /pages/register.php');
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['registration_error'] = 'Please provide a valid email address.';
        header('Location: /pages/register.php');
        exit;
    }

    // Validate field lengths
    if (strlen($name) > 100) {
        $_SESSION['registration_error'] = 'Name is too long (maximum 100 characters).';
        header('Location: /pages/register.php');
        exit;
    }

    if (strlen($email) > 255) {
        $_SESSION['registration_error'] = 'Email address is too long.';
        header('Location: /pages/register.php');
        exit;
    }

    // Whitelist validation for course
    $allowedCourses = ['Beginner Dutch', 'Intermediate Dutch', 'Advanced Dutch', 'Business Dutch', 'Conversation Practice'];
    if (!in_array($course, $allowedCourses)) {
        $_SESSION['registration_error'] = 'Invalid course selection.';
        header('Location: /pages/register.php');
        exit;
    }

    // Whitelist validation for spoken language
    $allowedLanguages = ['Russian', 'English', 'Other'];
    if (!in_array($spoken_language, $allowedLanguages)) {
        $_SESSION['registration_error'] = 'Invalid language selection.';
        header('Location: /pages/register.php');
        exit;
    }

    // Whitelist validation for preferred time
    $allowedTimes = ['Morning (9:00 - 12:00)', 'Afternoon (12:00 - 17:00)', 'Evening (17:00 - 21:00)'];
    if (!in_array($preferred_time, $allowedTimes)) {
        $_SESSION['registration_error'] = 'Invalid time selection.';
        header('Location: /pages/register.php');
        exit;
    }

    // Select the database first
    if (!$conn->select_db(DB_NAME)) {
        error_log("Database selection failed: " . $conn->error);
        $_SESSION['registration_error'] = 'An error occurred. Please try again later.';
        header('Location: /pages/register.php');
        exit;
    }

    // Insert with prepared statement (prevents SQL injection)
    $sql = "INSERT INTO registrations (name,email,course,spoken_language,preferred_time,message,status) VALUES (?,?,?,?,?,?,'New')";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        $_SESSION['registration_error'] = 'An error occurred. Please try again later.';
        header('Location: /pages/register.php');
        exit;
    }

    $stmt->bind_param("ssssss", $name, $email, $course, $spoken_language, $preferred_time, $message);

    if (!$stmt->execute()) {
        error_log("Database insert failed: " . $stmt->error);
        $_SESSION['registration_error'] = 'An error occurred. Please try again later.';
        header('Location: /pages/register.php');
        exit;
    }

    $stmt->close();

    // Increment rate limit counter on successful submission
    RateLimit::increment('registration');

    // --- MAIL CONFIG ---
    $mail = new PHPMailer(true);
    try {
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

        // --- E-mail naar student ---
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email);
        $mail->isHTML(true);

        // Sanitize all user input for email content
        $nameSafe = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $courseSafe = htmlspecialchars($course, ENT_QUOTES, 'UTF-8');
        $spokenLanguageSafe = htmlspecialchars($spoken_language, ENT_QUOTES, 'UTF-8');
        $preferredTimeSafe = htmlspecialchars($preferred_time, ENT_QUOTES, 'UTF-8');

        $content = "
          <p>Dear {$nameSafe},</p>
          <p>Thank you for registering for a Dutch course at <strong>NT2 Taalles International</strong>!</p>
          <p>Your registration is currently <strong>under review</strong>.</p>
          <p><strong>Course:</strong> {$courseSafe}<br>
             <strong>Native Language:</strong> {$spokenLanguageSafe}<br>
             <strong>Preferred Time:</strong> {$preferredTimeSafe}</p>
          <p>We will contact you soon with more details.</p>
          <p>Warm regards,<br><strong>Maico Heemskerk</strong><br>NT2 Taalles International</p>";

        $mail->Subject = "Your Dutch Course Registration";
        $mail->Body = $content;
        $mail->send();

        // --- E-mail naar beheerder ---
        $adminMail = new PHPMailer(true);
        $adminMail->isSMTP();
        $adminMail->Host = SMTP_HOST;
        $adminMail->SMTPAuth = true;
        $adminMail->Username = SMTP_USERNAME;
        $adminMail->Password = SMTP_PASSWORD;
        $adminMail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $adminMail->Port = SMTP_PORT;
        
        if (!SMTP_SSL_VERIFY) {
            $adminMail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        }

        $adminMail->setFrom(SMTP_FROM_EMAIL, 'NT2 Website');
        $adminMail->addAddress(ADMIN_EMAIL);
        $adminMail->isHTML(true);

        // Sanitize all user input for admin email
        $emailSafe = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $messageSafe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $messageDisplay = !empty($messageSafe) ? nl2br($messageSafe) : 'No message provided';

        $adminContent = "
          <p><strong>New Registration Received</strong></p>
          <p><strong>Name:</strong> {$nameSafe}<br>
             <strong>Email:</strong> {$emailSafe}<br>
             <strong>Course:</strong> {$courseSafe}<br>
             <strong>Native Language:</strong> {$spokenLanguageSafe}<br>
             <strong>Preferred Time:</strong> {$preferredTimeSafe}<br>
             <strong>Message:</strong> {$messageDisplay}</p>";

        $adminMail->Subject = "New Course Registration";
        $adminMail->Body = $adminContent;
        $adminMail->send();

    } catch (Exception $e) {
        // Log email error but continue
        error_log("Email error: " . $mail->ErrorInfo);
    }

    // --- Success page ---
    $_SESSION['registration_success'] = true;
    header('Location: /pages/register_success.php');
    exit;

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    $_SESSION['registration_error'] = 'An error occurred. Please try again later.';
    header('Location: /pages/register.php');
    exit;
}
?>