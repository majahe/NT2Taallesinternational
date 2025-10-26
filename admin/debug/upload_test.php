<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Test - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        .container { max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input[type="file"] { width: 100%; padding: 0.5rem; border: 1px solid #ccc; }
        .btn { background: #007cba; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #005a87; }
        .result { margin-top: 1rem; padding: 1rem; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Video Upload Test</h1>
        
        <div id="result"></div>
        
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="video">Select Video File:</label>
                <input type="file" id="video" name="video" accept="video/*" required>
            </div>
            <button type="submit" class="btn">Upload Video</button>
        </form>
        
        <div id="phpInfo" style="margin-top: 2rem;">
            <h3>PHP Configuration:</h3>
            <div class="info">
                <p><strong>upload_max_filesize:</strong> <?php echo ini_get('upload_max_filesize'); ?></p>
                <p><strong>post_max_size:</strong> <?php echo ini_get('post_max_size'); ?></p>
                <p><strong>max_execution_time:</strong> <?php echo ini_get('max_execution_time'); ?></p>
                <p><strong>memory_limit:</strong> <?php echo ini_get('memory_limit'); ?></p>
                <p><strong>file_uploads:</strong> <?php echo ini_get('file_uploads') ? 'On' : 'Off'; ?></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            const fileInput = document.getElementById('video');
            
            if (!fileInput.files[0]) {
                showResult('Please select a file first.', 'error');
                return;
            }
            
            formData.append('video', fileInput.files[0]);
            
            const xhr = new XMLHttpRequest();
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            showResult('✓ Upload successful!<br>File: ' + response.filename + '<br>Path: ' + response.path, 'success');
                        } else {
                            showResult('✗ Upload failed: ' + response.error + '<br><br>Debug info:<br>' + (response.debug ? response.debug.join('<br>') : 'No debug info'), 'error');
                        }
                    } catch (e) {
                        showResult('✗ Failed to parse response: ' + xhr.responseText, 'error');
                    }
                } else {
                    showResult('✗ Upload failed with status: ' + xhr.status, 'error');
                }
            };
            
            xhr.onerror = function() {
                showResult('✗ Network error occurred', 'error');
            };
            
            xhr.open('POST', '../../handlers/upload_video_debug.php');
            xhr.send(formData);
            
            showResult('Uploading...', 'info');
        });
        
        function showResult(message, type) {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="result ' + type + '">' + message + '</div>';
        }
    </script>
</body>
</html>
