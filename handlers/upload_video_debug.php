<?php
/**
 * Video Upload Handler - Debug Version
 * Handles video file uploads with validation and storage
 */

// Include PHP configuration override
require_once __DIR__ . '/../includes/php_config.php';

session_start();
if (!isset($_SESSION['admin'])) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/db_connect.php';

// Configuration
$upload_dir = __DIR__ . '/../uploads/videos/';
$max_file_size = 500 * 1024 * 1024; // 500MB
$allowed_types = ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo', 'video/webm'];

// Debug: Check if upload directory exists and is writable
$debug_info = [];

// Check if uploads directory exists
if (!file_exists(__DIR__ . '/../uploads/')) {
    $debug_info[] = "uploads/ directory does not exist";
    if (!mkdir(__DIR__ . '/../uploads/', 0755, true)) {
        $debug_info[] = "Failed to create uploads/ directory";
    } else {
        $debug_info[] = "Created uploads/ directory";
    }
}

// Check if videos directory exists
if (!file_exists($upload_dir)) {
    $debug_info[] = "uploads/videos/ directory does not exist";
    if (!mkdir($upload_dir, 0755, true)) {
        $debug_info[] = "Failed to create uploads/videos/ directory";
    } else {
        $debug_info[] = "Created uploads/videos/ directory";
    }
}

// Check if directory is writable
if (!is_writable($upload_dir)) {
    $debug_info[] = "uploads/videos/ directory is not writable";
    $debug_info[] = "Current permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4);
}

// Check PHP upload settings
$debug_info[] = "upload_max_filesize: " . ini_get('upload_max_filesize');
$debug_info[] = "post_max_size: " . ini_get('post_max_size');
$debug_info[] = "max_execution_time: " . ini_get('max_execution_time');

// Check if file was uploaded
if (!isset($_FILES['video'])) {
    echo json_encode([
        'success' => false,
        'error' => 'No file uploaded',
        'debug' => $debug_info,
        'files' => $_FILES,
        'request_method' => $_SERVER['REQUEST_METHOD'],
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
        'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 'not set'
    ]);
    exit;
}

if ($_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    
    $error_msg = $error_messages[$_FILES['video']['error']] ?? 'Unknown upload error';
    
    echo json_encode([
        'success' => false,
        'error' => 'Upload error: ' . $error_msg,
        'debug' => $debug_info,
        'file_error' => $_FILES['video']['error']
    ]);
    exit;
}

$file = $_FILES['video'];

// Validate file size
if ($file['size'] > $max_file_size) {
    echo json_encode([
        'success' => false,
        'error' => 'File size exceeds maximum allowed size (500MB)',
        'debug' => $debug_info,
        'file_size' => $file['size']
    ]);
    exit;
}

// Validate file type
if (!function_exists('finfo_open')) {
    echo json_encode([
        'success' => false,
        'error' => 'Fileinfo extension not available',
        'debug' => $debug_info
    ]);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid file type. Allowed: MP4, MOV, AVI, WebM',
        'debug' => $debug_info,
        'detected_mime' => $mime_type
    ]);
    exit;
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('video_', true) . '_' . time() . '.' . $extension;
$filepath = $upload_dir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save uploaded file',
        'debug' => $debug_info,
        'target_path' => $filepath,
        'temp_path' => $file['tmp_name']
    ]);
    exit;
}

// Get video duration if possible (requires ffmpeg or similar)
$duration = null;
if (function_exists('shell_exec')) {
    // Try to get duration using ffprobe if available
    $ffprobe_cmd = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($filepath);
    $output = @shell_exec($ffprobe_cmd);
    if ($output) {
        $duration = (int)floatval(trim($output));
    }
}

// Return success response
echo json_encode([
    'success' => true,
    'filename' => $filename,
    'path' => '/uploads/videos/' . $filename,
    'size' => $file['size'],
    'duration' => $duration,
    'message' => 'Video uploaded successfully',
    'debug' => $debug_info
]);

