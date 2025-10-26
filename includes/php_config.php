<?php
// PHP Configuration Override
// This file should be included at the top of PHP files that need larger upload limits

// Set PHP configuration (these may not work on all servers)
@ini_set('upload_max_filesize', '500M');
@ini_set('post_max_size', '500M');
@ini_set('max_execution_time', 300);
@ini_set('max_input_time', 300);
@ini_set('memory_limit', '512M');

// Alternative method using .user.ini (if supported)
if (function_exists('ini_set')) {
    ini_set('upload_max_filesize', '500M');
    ini_set('post_max_size', '500M');
    ini_set('max_execution_time', 300);
    ini_set('max_input_time', 300);
    ini_set('memory_limit', '512M');
}
?>
