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

    // Get form data with null coalescing operators for safety
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $course = $_POST['course'] ?? '';
    $spoken_language = $_POST['spoken_language'] ?? '';
    $preferred_time = $_POST['preferred_time'] ?? '';
    $message = $_POST['message'] ?? '';

    // Validate required fields
    if (empty($name) || empty($email) || empty($course) || empty($spoken_language) || empty($preferred_time)) {
        die("Missing required fields");
    }

    // Select the database first
    if (!$conn->select_db('nt2_db')) {
        die("Database selection failed: " . $conn->error);
    }

    // Insert with prepared statement (prevents SQL injection)
    $sql = "INSERT INTO registrations (name,email,course,spoken_language,preferred_time,message,status) VALUES (?,?,?,?,?,?,'New')";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssssss", $name, $email, $course, $spoken_language, $preferred_time, $message);

    if (!$stmt->execute()) {
        die("Database insert failed: " . $stmt->error);
    }

    $stmt->close();

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

        $content = "
          <p>Dear $name,</p>
          <p>Thank you for registering for a Dutch course at <strong>NT2 Taalles International</strong>!</p>
          <p>Your registration is currently <strong>under review</strong>.</p>
          <p><strong>Course:</strong> $course<br>
             <strong>Native Language:</strong> $spoken_language<br>
             <strong>Preferred Time:</strong> $preferred_time</p>
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

        $adminContent = "
          <p><strong>New Registration Received</strong></p>
          <p><strong>Name:</strong> $name<br>
             <strong>Email:</strong> $email<br>
             <strong>Course:</strong> $course<br>
             <strong>Native Language:</strong> $spoken_language<br>
             <strong>Preferred Time:</strong> $preferred_time<br>
             <strong>Message:</strong> $message</p>";

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
    die("Error: " . $e->getMessage());
}
?>