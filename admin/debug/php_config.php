<?php
/**
 * PHP Configuration Checker
 * Check and display current PHP upload settings
 */

echo "<h2>PHP Upload Configuration</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Current Value</th><th>Recommended</th></tr>";

$settings = [
    'upload_max_filesize' => '500M',
    'post_max_size' => '500M', 
    'max_execution_time' => '300',
    'max_input_time' => '300',
    'memory_limit' => '256M',
    'file_uploads' => 'On'
];

foreach ($settings as $setting => $recommended) {
    $current = ini_get($setting);
    $status = ($current === $recommended || $current >= $recommended) ? '✅' : '❌';
    echo "<tr><td>$setting</td><td>$current</td><td>$recommended $status</td></tr>";
}

echo "</table>";

echo "<h3>Directory Check</h3>";
$upload_dir = __DIR__ . '/../uploads/videos/';

echo "<p>Upload directory: $upload_dir</p>";
echo "<p>Directory exists: " . (file_exists($upload_dir) ? '✅ Yes' : '❌ No') . "</p>";
echo "<p>Directory writable: " . (is_writable($upload_dir) ? '✅ Yes' : '❌ No') . "</p>";

if (file_exists($upload_dir)) {
    echo "<p>Permissions: " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "</p>";
}

echo "<h3>Test File Upload</h3>";
if (isset($_FILES['test'])) {
    echo "<p>File received: " . $_FILES['test']['name'] . "</p>";
    echo "<p>File size: " . $_FILES['test']['size'] . " bytes</p>";
    echo "<p>Error code: " . $_FILES['test']['error'] . "</p>";
} else {
    echo "<form method='post' enctype='multipart/form-data'>";
    echo "<input type='file' name='test'>";
    echo "<input type='submit' value='Test Upload'>";
    echo "</form>";
}
?>
