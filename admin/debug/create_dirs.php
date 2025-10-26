<?php
// Create upload directories
$dirs = [
    'uploads',
    'uploads/videos', 
    'uploads/assignments'
];

echo "<h2>Directory Creation</h2>";

foreach ($dirs as $dir) {
    echo "<p>";
    
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✅ Created: $dir";
        } else {
            echo "❌ Failed to create: $dir";
        }
    } else {
        echo "✅ Already exists: $dir";
    }
    
    echo "<br>";
    
    if (is_writable($dir)) {
        echo "✅ Writable: $dir";
    } else {
        echo "❌ Not writable: $dir";
        echo "<br>Current permissions: " . substr(sprintf('%o', fileperms($dir)), -4);
    }
    
    echo "</p>";
}

echo "<h2>Test File Creation</h2>";
$test_file = 'uploads/videos/test.txt';
if (file_put_contents($test_file, 'test')) {
    echo "✅ Can write to uploads/videos/";
    unlink($test_file);
} else {
    echo "❌ Cannot write to uploads/videos/";
}
?>
