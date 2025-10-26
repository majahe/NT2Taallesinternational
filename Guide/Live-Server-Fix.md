# Live Server PHP Upload Fix

## Stap 1: Maak .htaccess bestand

Maak een `.htaccess` bestand in je project root (waar index.php staat):

```apache
# PHP Upload Settings
php_value upload_max_filesize 500M
php_value post_max_size 500M
php_value max_execution_time 300
php_value max_input_time 300
php_value memory_limit 256M

# Security
<Files "*.php">
    Order Allow,Deny
    Allow from all
</Files>

# Directory Protection
<Files "*.sql">
    Order Deny,Allow
    Deny from all
</Files>
```

## Stap 2: Maak upload directories

Via **File Manager** in cPanel:
1. Ga naar je project directory
2. Maak folder `uploads` aan
3. In `uploads` maak folder `videos` aan
4. In `uploads` maak folder `assignments` aan
5. Zet permissions op `755` voor beide folders

Via **FTP**:
```
uploads/
  videos/
  assignments/
```

## Stap 3: Test directory creation

Upload dit PHP bestand als `create_dirs.php`:

```php
<?php
// Create upload directories
$dirs = [
    'uploads',
    'uploads/videos', 
    'uploads/assignments'
];

foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✅ Created: $dir<br>";
        } else {
            echo "❌ Failed: $dir<br>";
        }
    } else {
        echo "✅ Exists: $dir<br>";
    }
    
    if (is_writable($dir)) {
        echo "✅ Writable: $dir<br>";
    } else {
        echo "❌ Not writable: $dir<br>";
    }
}
?>
```

## Stap 4: Check hosting provider

Als .htaccess niet werkt, contact je hosting provider voor:
- PHP settings aanpassing
- Directory permissions
- Upload limits verhoging

## Stap 5: Test opnieuw

1. Upload .htaccess bestand
2. Maak directories aan
3. Ga naar: `https://nt2taallesinternational.com/admin/debug/php_config.php`
4. Check of settings zijn aangepast
5. Test video upload opnieuw

## Alternatief: Via hosting control panel

**cPanel → PHP Selector:**
1. Selecteer PHP versie
2. Klik "Options"
3. Pas settings aan:
   - upload_max_filesize: 500M
   - post_max_size: 500M
   - max_execution_time: 300
4. Save

**Plesk:**
1. Websites & Domains
2. PHP Settings
3. Pas values aan
4. Apply

Welke hosting provider gebruik je? Dan kan ik specifiekere instructies geven.
