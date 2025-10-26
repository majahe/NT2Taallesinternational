<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/index.php");
    exit;
}

// Include PHP configuration override
require_once __DIR__ . '/../../includes/php_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Configuration Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section { margin-bottom: 2rem; padding: 1rem; border-radius: 6px; }
        .info { background: #e3f2fd; border-left: 4px solid #2196f3; }
        .warning { background: #fff3e0; border-left: 4px solid #ff9800; }
        .error { background: #ffebee; border-left: 4px solid #f44336; }
        .success { background: #e8f5e8; border-left: 4px solid #4caf50; }
        h2 { margin-top: 0; color: #333; }
        .config-item { display: flex; justify-content: space-between; margin: 0.5rem 0; padding: 0.5rem; background: #f9f9f9; border-radius: 4px; }
        .config-name { font-weight: bold; }
        .config-value { color: #666; }
        .test-btn { background: #2196f3; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; margin: 1rem 0; }
        .test-btn:hover { background: #1976d2; }
        .file-test { margin-top: 1rem; padding: 1rem; background: #f0f0f0; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Server Configuration Diagnostic</h1>
        
        <div class="section info">
            <h2>📋 Current PHP Configuration</h2>
            <div class="config-item">
                <span class="config-name">upload_max_filesize:</span>
                <span class="config-value"><?php echo ini_get('upload_max_filesize'); ?></span>
            </div>
            <div class="config-item">
                <span class="config-name">post_max_size:</span>
                <span class="config-value"><?php echo ini_get('post_max_size'); ?></span>
            </div>
            <div class="config-item">
                <span class="config-name">max_execution_time:</span>
                <span class="config-value"><?php echo ini_get('max_execution_time'); ?></span>
            </div>
            <div class="config-item">
                <span class="config-name">memory_limit:</span>
                <span class="config-value"><?php echo ini_get('memory_limit'); ?></span>
            </div>
            <div class="config-item">
                <span class="config-name">file_uploads:</span>
                <span class="config-value"><?php echo ini_get('file_uploads') ? 'On' : 'Off'; ?></span>
            </div>
        </div>

        <div class="section <?php echo ini_get('upload_max_filesize') === '500M' ? 'success' : 'warning'; ?>">
            <h2><?php echo ini_get('upload_max_filesize') === '500M' ? '✅' : '⚠️'; ?> Upload Limits Status</h2>
            <?php if (ini_get('upload_max_filesize') === '500M'): ?>
                <p><strong>Good!</strong> Upload limits are properly configured.</p>
            <?php else: ?>
                <p><strong>Warning!</strong> Upload limits are still restrictive. Current limit: <?php echo ini_get('upload_max_filesize'); ?></p>
                <p>This means the .htaccess and .user.ini files are not being processed by your server.</p>
            <?php endif; ?>
        </div>

        <div class="section info">
            <h2>🔧 Server Information</h2>
            <div class="config-item">
                <span class="config-name">Server Software:</span>
                <span class="config-value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
            </div>
            <div class="config-item">
                <span class="config-name">PHP Version:</span>
                <span class="config-value"><?php echo PHP_VERSION; ?></span>
            </div>
            <div class="config-item">
                <span class="config-name">Document Root:</span>
                <span class="config-value"><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></span>
            </div>
        </div>

        <div class="section info">
            <h2>📁 File System Tests</h2>
            <div class="file-test">
                <h3>Upload Directory Test</h3>
                <?php
                $upload_dir = __DIR__ . '/../../uploads/videos/';
                if (!file_exists($upload_dir)) {
                    echo '<p style="color: red;">❌ Upload directory does not exist: ' . $upload_dir . '</p>';
                    if (mkdir($upload_dir, 0755, true)) {
                        echo '<p style="color: green;">✅ Created upload directory successfully</p>';
                    } else {
                        echo '<p style="color: red;">❌ Failed to create upload directory</p>';
                    }
                } else {
                    echo '<p style="color: green;">✅ Upload directory exists: ' . $upload_dir . '</p>';
                }
                
                if (is_writable($upload_dir)) {
                    echo '<p style="color: green;">✅ Upload directory is writable</p>';
                } else {
                    echo '<p style="color: red;">❌ Upload directory is not writable</p>';
                    echo '<p>Current permissions: ' . substr(sprintf('%o', fileperms($upload_dir)), -4) . '</p>';
                }
                ?>
            </div>
        </div>

        <div class="section info">
            <h2>🧪 Quick Upload Test</h2>
            <p>Test if the upload handler is accessible:</p>
            <button class="test-btn" onclick="testUploadHandler()">Test Upload Handler</button>
            <div id="testResult"></div>
        </div>

        <div class="section warning">
            <h2>💡 Recommendations</h2>
            <?php if (ini_get('upload_max_filesize') !== '500M'): ?>
                <p><strong>If upload limits are still restrictive:</strong></p>
                <ul>
                    <li>Contact your hosting provider to increase PHP limits</li>
                    <li>Ask them to modify php.ini settings</li>
                    <li>Check if your hosting plan supports .htaccess overrides</li>
                    <li>Consider upgrading to a VPS or dedicated server for full control</li>
                </ul>
            <?php else: ?>
                <p><strong>Configuration looks good!</strong> You should be able to upload videos up to 500MB.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function testUploadHandler() {
            const resultDiv = document.getElementById('testResult');
            resultDiv.innerHTML = '<p>Testing upload handler...</p>';
            
            fetch('../../handlers/upload_video_debug.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'test=1'
            })
            .then(response => {
                if (response.ok) {
                    resultDiv.innerHTML = '<p style="color: green;">✅ Upload handler is accessible (Status: ' + response.status + ')</p>';
                } else {
                    resultDiv.innerHTML = '<p style="color: red;">❌ Upload handler returned error (Status: ' + response.status + ')</p>';
                }
            })
            .catch(error => {
                resultDiv.innerHTML = '<p style="color: red;">❌ Error testing upload handler: ' + error.message + '</p>';
            });
        }
    </script>
</body>
</html>
