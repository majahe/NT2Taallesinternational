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
    <style>
        .admin-container {
            max-width: 800px;
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
        .alert-message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: none;
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
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        .form-group input[type="file"] {
            width: 100%;
            padding: 0.75rem;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            background: #f9fafb;
        }
        .form-group small {
            display: block;
            margin-top: 0.5rem;
            color: #666;
            font-size: 0.9rem;
        }
        .upload-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
        }
        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .progress-bar {
            width: 100%;
            height: 12px;
            background: #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 1rem;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s;
        }
        #uploadResult {
            margin-top: 1.5rem;
        }
        .result-box {
            padding: 1.5rem;
            border-radius: 8px;
            background: #f9fafb;
            border-left: 4px solid #667eea;
        }
        .result-box code {
            background: white;
            padding: 0.5rem;
            border-radius: 4px;
            display: block;
            margin: 0.5rem 0;
            word-break: break-all;
        }
        .copy-btn {
            background: #10b981;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 0.5rem;
        }
        .copy-btn:hover {
            background: #059669;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="page-header">
            <h1>Upload Video</h1>
            <a href="../dashboard/dashboard.php" class="btn" style="background: #667eea; color: white;">← Back to Dashboard</a>
        </div>
        
        <!-- Success Message -->
        <div id="successMessage" class="alert-message alert-success">
            <strong>✓ Success!</strong> Video uploaded successfully!
        </div>
        
        <!-- Error Message -->
        <div id="errorMessage" class="alert-message alert-error">
            <strong>✗ Error:</strong> <span id="errorText"></span>
        </div>
        
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label>Select Video File</label>
                <input type="file" name="video" accept="video/*" required>
                <small>Max size: 500MB. Formats: MP4, MOV, AVI, WebM</small>
            </div>
            
            <button type="submit" class="upload-btn">Upload Video</button>
        </form>
        
        <div id="uploadProgress" style="display: none; margin-top: 1rem;">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill" style="width: 0%"></div>
            </div>
            <p id="progressText" style="text-align: center; margin-top: 0.5rem; color: #666;">Uploading...</p>
        </div>
        
        <div id="uploadResult"></div>
        
        <script>
            document.getElementById('uploadForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Hide previous messages
                document.getElementById('successMessage').style.display = 'none';
                document.getElementById('errorMessage').style.display = 'none';
                document.getElementById('uploadResult').innerHTML = '';
                
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
                    document.getElementById('uploadProgress').style.display = 'none';
                    
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            
                            if (response.success) {
                                // Show success message
                                document.getElementById('successMessage').style.display = 'block';
                                
                                // Display result with file info
                                document.getElementById('uploadResult').innerHTML = 
                                    '<div class="result-box">' +
                                    '<h3>✓ Upload Complete!</h3>' +
                                    '<p><strong>Filename:</strong> ' + response.filename + '</p>' +
                                    '<p><strong>File Path:</strong></p>' +
                                    '<code>' + response.path + '</code>' +
                                    '<p><strong>File Size:</strong> ' + formatBytes(response.size) + '</p>' +
                                    (response.duration ? '<p><strong>Duration:</strong> ' + formatDuration(response.duration) + '</p>' : '') +
                                    '<button onclick="copyToClipboard(\'' + response.path + '\')" class="copy-btn">📋 Copy Path</button>' +
                                    '</div>';
                                
                                // Reset form
                                document.getElementById('uploadForm').reset();
                            } else {
                                // Show error message
                                document.getElementById('errorMessage').style.display = 'block';
                                document.getElementById('errorText').textContent = response.error || 'Unknown error occurred';
                            }
                        } catch (e) {
                            // Show parse error
                            document.getElementById('errorMessage').style.display = 'block';
                            document.getElementById('errorText').textContent = 'Failed to parse server response';
                        }
                    } else {
                        // Show HTTP error
                        document.getElementById('errorMessage').style.display = 'block';
                        document.getElementById('errorText').textContent = 'Upload failed with status: ' + xhr.status;
                    }
                });
                
                xhr.addEventListener('error', function() {
                    document.getElementById('uploadProgress').style.display = 'none';
                    document.getElementById('errorMessage').style.display = 'block';
                    document.getElementById('errorText').textContent = 'Network error occurred';
                });
                
                xhr.open('POST', '../../handlers/upload_video_debug.php');
                xhr.send(formData);
                document.getElementById('uploadProgress').style.display = 'block';
            });
            
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(function() {
                    alert('✓ Path copied to clipboard!');
                }).catch(function() {
                    alert('Failed to copy to clipboard');
                });
            }
            
            function formatBytes(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
            }
            
            function formatDuration(seconds) {
                const hrs = Math.floor(seconds / 3600);
                const mins = Math.floor((seconds % 3600) / 60);
                const secs = seconds % 60;
                
                if (hrs > 0) {
                    return hrs + ':' + String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
                }
                return mins + ':' + String(secs).padStart(2, '0');
            }
        </script>
    </div>
</body>
</html>

