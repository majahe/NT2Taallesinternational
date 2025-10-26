# PHP Upload Configuration Fix

## Probleem
Je PHP-instellingen zijn te restrictief voor video-uploads:
- `upload_max_filesize: 2M` (moet 500M zijn)
- `post_max_size: 8M` (moet 500M zijn)

## Oplossing

### Optie 1: Via php.ini (Aanbevolen)

1. **Vind je php.ini bestand:**
   - XAMPP: `C:\xampp\php\php.ini`
   - WAMP: `C:\wamp\bin\php\php7.4.3\php.ini`
   - Laragon: `C:\laragon\bin\php\php8.1.10-Win32-vs16-x64\php.ini`

2. **Open php.ini en zoek naar deze regels:**
```ini
upload_max_filesize = 2M
post_max_size = 8M
max_execution_time = 60
max_input_time = 60
memory_limit = 128M
```

3. **Verander naar:**
```ini
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

4. **Herstart Apache** via XAMPP/WAMP Control Panel

### Optie 2: Via .htaccess (Alternatief)

Maak een `.htaccess` bestand in je project root:

```apache
php_value upload_max_filesize 500M
php_value post_max_size 500M
php_value max_execution_time 300
php_value max_input_time 300
php_value memory_limit 256M
```

### Optie 3: Via PHP (Tijdelijk)

Voeg dit toe aan het begin van `handlers/upload_video.php`:

```php
ini_set('upload_max_filesize', '500M');
ini_set('post_max_size', '500M');
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);
ini_set('memory_limit', '256M');
```

## Directory Permissions Fix

### Windows (PowerShell als Administrator):
```powershell
# Maak directories aan
New-Item -ItemType Directory -Path "uploads\videos" -Force

# Geef IIS_IUSRS schrijfrechten
icacls uploads /grant IIS_IUSRS:(OI)(CI)F

# OF voor Everyone (alleen development!)
icacls uploads /grant Everyone:(OI)(CI)F
```

### Via File Explorer:
1. Rechts-klik op `uploads` folder
2. Properties → Security tab
3. Edit → Add
4. Type: `IIS_IUSRS`
5. Check "Write" en "Modify"
6. OK

## Test

1. Ga naar: `http://localhost/admin/debug/php_config.php`
2. Check of alle settings correct zijn
3. Test een kleine file upload
4. Probeer dan je video upload opnieuw

## Controleer na wijzigingen

```php
<?php
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
?>
```

Sla dit op als `test.php` en open in browser.
