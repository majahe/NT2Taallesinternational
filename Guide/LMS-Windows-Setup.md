# LMS Platform - Windows Setup Guide

## Windows-specifieke Installatie Instructies

### Vereisten voor Windows

- **XAMPP** of **WAMP** of **Laragon** (PHP + MySQL + Apache)
- PHP 7.4 of hoger
- MySQL 5.7 of hoger
- PHP extensies: mysqli, pdo_mysql, mbstring, openssl

### Stap 1: Directories Aanmaken

Open **PowerShell** of **Command Prompt** in je project directory:

```powershell
# PowerShell commando's
New-Item -ItemType Directory -Path "uploads\videos" -Force
New-Item -ItemType Directory -Path "uploads\assignments" -Force
```

OF via **File Explorer**:
1. Ga naar je project folder
2. Maak handmatig mappen aan: `uploads\videos` en `uploads\assignments`

### Stap 2: Permissions Instellen (Windows)

**Optie 1: Via File Explorer**
1. Rechts-klik op `uploads` folder → Properties
2. Ga naar **Security** tab
3. Klik **Edit** → **Add**
4. Type: `IIS_IUSRS` (voor IIS) of `Everyone` (voor development)
5. Check **Write** en **Modify** permissions
6. Klik **OK**

**Optie 2: Via PowerShell (als Administrator)**
```powershell
# Geef IIS_IUSRS schrijfrechten
icacls uploads /grant IIS_IUSRS:(OI)(CI)F

# OF voor Everyone (alleen development!)
icacls uploads /grant Everyone:(OI)(CI)F
```

**Optie 3: Via Command Prompt (als Administrator)**
```cmd
icacls uploads /grant Users:(OI)(CI)F
```

### Stap 3: Database Setup

**Via phpMyAdmin:**
1. Open XAMPP/WAMP Control Panel
2. Start Apache en MySQL
3. Open browser → `http://localhost/phpmyadmin`
4. Selecteer database `nt2_db`
5. Ga naar **Import** tab
6. Kies bestand: `database\lms_schema.sql`
7. Klik **Go**

**Via Command Line:**
```powershell
# Navigeer naar project directory
cd "C:\path\to\project"

# Voer SQL uit (vervang root met je MySQL user)
mysql -u root -p nt2_db < database\lms_schema.sql
```

**Via MySQL Workbench:**
1. Open MySQL Workbench
2. Connect met je database
3. File → Open SQL Script
4. Kies `database\lms_schema.sql`
5. Klik **Execute**

### Stap 4: PHP Configuratie Check

**Check php.ini voor uploads:**

1. Open `php.ini` (meestal in `C:\xampp\php\php.ini`)
2. Zorg dat deze waarden correct zijn:
```ini
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 300
memory_limit = 256M
```

3. Herstart Apache na wijzigingen

**Check via PHP info:**
```php
<?php phpinfo(); ?>
```
Save als `test.php` en open in browser om settings te checken.

### Stap 5: SMTP Configuratie (voor emails)

Edit `includes\config.php`:

```php
// Voor Gmail SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'jouw-email@gmail.com');
define('SMTP_PASSWORD', 'jouw-app-password'); // App Password, niet gewoon wachtwoord!
```

**Gmail App Password aanmaken:**
1. Google Account → Security
2. 2-Step Verification aanzetten
3. App Passwords → Generate
4. Kopieer het gegenereerde wachtwoord

### Stap 6: Test Installatie

**Test Database Connectie:**
```php
<?php
require_once 'includes/db_connect.php';
echo "Database connected!";
?>
```

**Test Upload Directory:**
```php
<?php
$test_file = 'uploads/videos/test.txt';
file_put_contents($test_file, 'test');
if (file_exists($test_file)) {
    echo "Upload directory is writable!";
    unlink($test_file);
} else {
    echo "Upload directory is NOT writable!";
}
?>
```

**Test Video Upload:**
1. Login als admin
2. Ga naar `admin/courses/upload_video.php`
3. Upload een test video
4. Check of bestand in `uploads/videos/` staat

### Veelvoorkomende Windows Problemen

**Probleem: "chmod is not recognized"**
```
Oplossing: Gebruik icacls in plaats van chmod op Windows
```

**Probleem: "Permission denied" bij upload**
```
Oplossing:
1. Check folder permissions (Security tab)
2. Zorg dat webserver account Write rechten heeft
3. Check eventuele antivirus blocking
```

**Probleem: MySQL connection refused**
```
Oplossing:
1. Check of MySQL service draait (XAMPP/WAMP Control Panel)
2. Check MySQL port (standaard 3306)
3. Check firewall settings
```

**Probleem: PHP mail() werkt niet**
```
Oplossing:
1. Gebruik SMTP in plaats van mail()
2. Check SMTP credentials in config.php
3. Voor development: gebruik Mailtrap of Mailhog
```

**Probleem: File paths werken niet**
```
Oplossing:
- Gebruik forward slashes / in plaats van backslashes \
- OF gebruik __DIR__ voor absolute paths
- Check include paths in PHP
```

### XAMPP Specifieke Tips

**Apache niet start:**
1. Check of poort 80 of 443 al in gebruik is
2. Change poort in XAMPP Control Panel → Config → Apache → httpd.conf
3. Zoek `Listen 80` en verander naar `Listen 8080`

**MySQL niet start:**
1. Check of poort 3306 al in gebruik is
2. Check XAMPP error logs
3. Reinstall MySQL in XAMPP indien nodig

**PhpMyAdmin toegang:**
- URL: `http://localhost/phpmyadmin`
- Default user: `root`
- Default password: (leeg) OF `root`

### WAMP Specifieke Tips

**Apache service niet start:**
1. WAMP icon → Tools → Check port 80
2. Change poort indien conflict
3. Right-click WAMP → Run as Administrator

**Virtual Host setup:**
1. WAMP → Tools → Add Virtual Host
2. Name: `nt2taalles.local`
3. Path: `C:\path\to\project`
4. Add to hosts file automatisch
5. Access via: `http://nt2taalles.local`

### Development vs Production

**Development (Localhost):**
- Gebruik `http://localhost` voor WEBSITE_URL
- SSL verify uit voor SMTP testing
- Debug mode aan

**Production (Live Server):**
- Update WEBSITE_URL naar je domein
- SSL verify aan voor SMTP
- Debug mode uit
- Check file permissions op server
- Gebruik absolute paths

### Backup Procedures (Windows)

**Database Backup:**
```powershell
mysqldump -u root -p nt2_db > backup_$(Get-Date -Format "yyyyMMdd").sql
```

**Files Backup:**
```powershell
Compress-Archive -Path uploads -DestinationPath "backup_uploads_$(Get-Date -Format 'yyyyMMdd').zip"
```

**Complete Backup Script:**
```powershell
# backup.ps1
$date = Get-Date -Format "yyyyMMdd"
mysqldump -u root -p nt2_db > "backup_db_$date.sql"
Compress-Archive -Path uploads -DestinationPath "backup_uploads_$date.zip"
Write-Host "Backup completed: $date"
```

---

**Nog meer hulp nodig?**
- Check `LMS-Troubleshooting.md` voor meer problemen
- Check error logs in `C:\xampp\apache\logs\` of `C:\wamp\logs\`
- Contact: Info@nt2taallesinternational.com

