<?php
require_once __DIR__ . '/../../includes/admin_auth.php';
require_admin_auth();

require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../includes/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../includes/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../includes/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../../includes/config.php';

use PHPMailer\PHPMailer\PHPMailer;

$student_id = intval($_POST['student_id'] ?? 0);
$course_id = intval($_POST['course_id'] ?? 0);
$access_until = $_POST['access_until'] ?? null;

if ($student_id <= 0 || $course_id <= 0) {
    header("Location: registered_students.php?error=Invalid data");
    exit;
}

// Get student info
$stmt = $conn->prepare("SELECT * FROM registrations WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Generate password token
$token = bin2hex(random_bytes(32));
$token_expires = date('Y-m-d H:i:s', strtotime('+7 days'));

// Check if token columns exist and add them if needed
$check_token = $conn->query("SHOW COLUMNS FROM registrations LIKE 'password_token'");
if ($check_token->num_rows == 0) {
    $conn->query("ALTER TABLE registrations ADD COLUMN password_token VARCHAR(64) NULL");
}
$check_expires = $conn->query("SHOW COLUMNS FROM registrations LIKE 'password_token_expires'");
if ($check_expires->num_rows == 0) {
    $conn->query("ALTER TABLE registrations ADD COLUMN password_token_expires DATETIME NULL");
}

$stmt = $conn->prepare("UPDATE registrations SET password_token = ?, password_token_expires = ? WHERE id = ?");
$stmt->bind_param("ssi", $token, $token_expires, $student_id);
$stmt->execute();
$stmt->close();

// Create enrollment
// Convert empty string to NULL for optional date field
if (empty($access_until)) {
    $access_until = null;
}

$stmt = $conn->prepare("
    INSERT INTO student_enrollments (student_id, course_id, access_until, status) 
    VALUES (?, ?, ?, 'active')
    ON DUPLICATE KEY UPDATE access_until = VALUES(access_until), status = 'active'
");
$stmt->bind_param("iis", $student_id, $course_id, $access_until);
$stmt->execute();
$stmt->close();

// Mark course access as granted (check if column exists first)
$check_granted = $conn->query("SHOW COLUMNS FROM registrations LIKE 'course_access_granted'");
if ($check_granted->num_rows > 0) {
    $conn->query("UPDATE registrations SET course_access_granted = TRUE WHERE id = $student_id");
}

// Send email with login instructions
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    
    if (!SMTP_SSL_VERIFY) {
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
    }
    
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress($student['email']);
    $mail->isHTML(true);
    
    $setup_url = WEBSITE_URL . '/student/auth/register_password.php?token=' . $token;
    
    $content = "
        <p>Dear " . htmlspecialchars($student['name']) . ",</p>
        <p>Congratulations! You have been granted access to our online course platform.</p>
        <p><strong>To get started:</strong></p>
        <ol>
            <li>Click the link below to set your password</li>
            <li>Once your password is set, you can login to access your courses</li>
        </ol>
        <p><a href='$setup_url' style='background: #667eea; color: white; padding: 1rem 2rem; text-decoration: none; border-radius: 8px; display: inline-block; margin: 1rem 0;'>Set Password & Access Platform</a></p>
        <p>Or copy this link: $setup_url</p>
        <p>This link will expire in 7 days.</p>
        <p>Best regards,<br><strong>NT2 Taalles International</strong></p>
    ";
    
    $mail->Subject = "Course Access Granted - Set Your Password";
    $mail->Body = $content;
    $mail->send();
    
} catch (Exception $e) {
    error_log("Email error: " . $mail->ErrorInfo);
}

header("Location: registered_students.php?success=Course access granted and email sent");
exit;

