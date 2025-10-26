<?php
/**
 * Video Upload Handler
 * Handles video file uploads with validation and storage
 */

session_start();
if (!isset($_SESSION['admin'])) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../includes/db_connect.php';

// Configuration
$upload_dir = __DIR__ . '/../uploads/videos/';
$max_file_size = 500 * 1024 * 1024; // 500MB
$allowed_types = ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo', 'video/webm'];

// Create upload directory if it doesn't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Check if file was uploaded
if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'success' => false,
        'error' => 'No file uploaded or upload error occurred'
    ]);
    exit;
}

$file = $_FILES['video'];

// Validate file size
if ($file['size'] > $max_file_size) {
    echo json_encode([
        'success' => false,
        'error' => 'File size exceeds maximum allowed size (500MB)'
    ]);
    exit;
}

// Validate file type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid file type. Allowed: MP4, MOV, AVI, WebM'
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
        'error' => 'Failed to save uploaded file'
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
    'message' => 'Video uploaded successfully'
]);

