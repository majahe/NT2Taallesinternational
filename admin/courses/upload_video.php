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
    <title>Upload Video - Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="admin-container" style="max-width: 800px; margin: 2rem auto; padding: 2rem;">
        <h1>Upload Video</h1>
        
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label>Select Video File</label>
                <input type="file" name="video" accept="video/*" required>
                <small>Max size: 500MB. Formats: MP4, MOV, AVI, WebM</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Upload Video</button>
        </form>
        
        <div id="uploadProgress" style="display: none; margin-top: 1rem;">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill" style="width: 0%"></div>
            </div>
            <p id="progressText">Uploading...</p>
        </div>
        
        <div id="uploadResult" style="margin-top: 1rem;"></div>
        
        <script>
            document.getElementById('uploadForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData();
                formData.append('video', document.querySelector('input[type="file"]').files[0]);
                
                const xhr = new XMLHttpRequest();
                
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        document.getElementById('progressFill').style.width = percentComplete + '%';
                        document.getElementById('progressText').textContent = 'Uploading: ' + Math.round(percentComplete) + '%';
                    }
                });
                
                xhr.addEventListener('load', function() {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            document.getElementById('uploadResult').innerHTML = 
                                '<div class="alert alert-success">' +
                                '<strong>Upload successful!</strong><br>' +
                                'Path: <code>' + response.path + '</code><br>' +
                                '<button onclick="copyToClipboard(\'' + response.path + '\')" class="btn btn-small">Copy Path</button>' +
                                '</div>';
                        } else {
                            document.getElementById('uploadResult').innerHTML = 
                                '<div class="alert alert-error">Error: ' + response.error + '</div>';
                        }
                    }
                    document.getElementById('uploadProgress').style.display = 'none';
                });
                
                xhr.open('POST', '../../handlers/upload_video.php');
                xhr.send(formData);
                document.getElementById('uploadProgress').style.display = 'block';
            });
            
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(function() {
                    alert('Path copied to clipboard!');
                });
            }
        </script>
    </div>
</body>
</html>

