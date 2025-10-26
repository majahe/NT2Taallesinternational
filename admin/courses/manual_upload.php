<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/index.php");
    exit;
}

require_once __DIR__ . '/../../includes/db_connect.php';

// Handle file registration
if ($_POST['action'] ?? '' === 'register_file') {
    $filename = $_POST['filename'] ?? '';
    $original_name = $_POST['original_name'] ?? '';
    
    if ($filename && $original_name) {
        $staging_dir = __DIR__ . '/../../uploads/videos/staging/';
        $final_dir = __DIR__ . '/../../uploads/videos/';
        
        $staging_path = $staging_dir . $filename;
        $final_path = $final_dir . $filename;
        
        if (file_exists($staging_path)) {
            // Move file from staging to final location
            if (rename($staging_path, $final_path)) {
                // Generate unique filename for database
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                $db_filename = uniqid('video_', true) . '_' . time() . '.' . $extension;
                $db_path = $final_dir . $db_filename;
                
                // Rename to database filename
                rename($final_path, $db_path);
                
                // Get file size
                $file_size = filesize($db_path);
                
                // Get video duration if possible
                $duration = null;
                if (function_exists('shell_exec')) {
                    $ffprobe_cmd = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($db_path);
                    $output = @shell_exec($ffprobe_cmd);
                    if ($output) {
                        $duration = (int)floatval(trim($output));
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'filename' => $db_filename,
                    'path' => '/uploads/videos/' . $db_filename,
                    'size' => $file_size,
                    'duration' => $duration,
                    'message' => 'File registered successfully'
                ]);
                exit;
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to move file from staging']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'File not found in staging directory']);
            exit;
        }
    }
}

// Get list of files in staging directory
$staging_dir = __DIR__ . '/../../uploads/videos/staging/';
$staging_files = [];

if (!file_exists($staging_dir)) {
    mkdir($staging_dir, 0755, true);
}

if (is_dir($staging_dir)) {
    $files = scandir($staging_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && !is_dir($staging_dir . $file)) {
            $file_path = $staging_dir . $file;
            $staging_files[] = [
                'name' => $file,
                'size' => filesize($file_path),
                'modified' => filemtime($file_path)
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Video Upload - Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .admin-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }
        .section {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .section h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0.5rem;
        }
        .instructions {
            background: #e3f2fd;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        .instructions ol {
            margin: 0.5rem 0;
            padding-left: 1.5rem;
        }
        .file-list {
            display: grid;
            gap: 1rem;
        }
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: #f9fafb;
        }
        .file-info {
            flex: 1;
        }
        .file-name {
            font-weight: 600;
            color: #333;
        }
        .file-details {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.25rem;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5a67d8;
        }
        .btn-success {
            background: #10b981;
            color: white;
        }
        .btn-success:hover {
            background: #059669;
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        .no-files {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="page-header">
            <h1>Manual Video Upload</h1>
            <a href="../dashboard/dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
        </div>
        
        <div id="messageContainer"></div>
        
        <div class="section">
            <h2>📋 Instructions</h2>
            <div class="instructions">
                <p><strong>Since PHP upload limits are restrictive, use this manual method:</strong></p>
                <ol>
                    <li>Upload your video file to the server using FTP, file manager, or any other method</li>
                    <li>Place the file in: <code>uploads/videos/staging/</code></li>
                    <li>Come back to this page and click "Register File" next to your video</li>
                    <li>The system will move the file to the proper location and generate a unique filename</li>
                </ol>
                <p><strong>Supported formats:</strong> MP4, MOV, AVI, WebM</p>
            </div>
        </div>
        
        <div class="section">
            <h2>📁 Files in Staging Directory</h2>
            <?php if (empty($staging_files)): ?>
                <div class="no-files">
                    <p>No files found in staging directory.</p>
                    <p>Upload a video file to <code>uploads/videos/staging/</code> to get started.</p>
                </div>
            <?php else: ?>
                <div class="file-list">
                    <?php foreach ($staging_files as $file): ?>
                        <div class="file-item">
                            <div class="file-info">
                                <div class="file-name"><?php echo htmlspecialchars($file['name']); ?></div>
                                <div class="file-details">
                                    Size: <?php echo formatBytes($file['size']); ?> | 
                                    Modified: <?php echo date('Y-m-d H:i:s', $file['modified']); ?>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-success" onclick="registerFile('<?php echo htmlspecialchars($file['name']); ?>')">
                                    Register File
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>🔧 Alternative: Direct Upload</h2>
            <div class="alert alert-info">
                <p><strong>If you prefer the traditional upload method:</strong></p>
                <p>Try the <a href="upload_test.php" class="btn btn-primary">Upload Test Page</a> to see if the PHP configuration changes have taken effect.</p>
            </div>
        </div>
    </div>

    <script>
        function registerFile(filename) {
            if (!confirm('Register this file: ' + filename + '?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'register_file');
            formData.append('filename', filename);
            formData.append('original_name', filename);
            
            fetch('manual_upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('✅ File registered successfully!<br>Filename: ' + data.filename + '<br>Path: ' + data.path, 'success');
                    // Reload page to update file list
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showMessage('❌ Error: ' + data.error, 'error');
                }
            })
            .catch(error => {
                showMessage('❌ Network error: ' + error.message, 'error');
            });
        }
        
        function showMessage(message, type) {
            const container = document.getElementById('messageContainer');
            container.innerHTML = '<div class="alert alert-' + type + '">' + message + '</div>';
        }
    </script>
</body>
</html>

<?php
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}
?>
