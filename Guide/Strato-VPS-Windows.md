# Strato VPS Windows - PHP Upload Fix

## VPS Windows oplossing

### 1. PHP.ini aanpassen (Direct toegang)

**Vind je php.ini bestand:**
- Meestal in: `C:\Program Files\PHP\v8.x\php.ini`
- Of: `C:\inetpub\php\php.ini`
- Of: `C:\xampp\php\php.ini` (als je XAMPP gebruikt)

**Open php.ini en zoek naar:**
```ini
upload_max_filesize = 2M
post_max_size = 8M
max_execution_time = 60
max_input_time = 60
memory_limit = 128M
```

**Verander naar:**
```ini
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

### 2. IIS/Apache herstarten

**Via Services (Windows):**
1. Open **Services** (services.msc)
2. Zoek **"IIS Admin Service"** of **"Apache"**
3. Rechts-klik → **Restart**

**Via Command Prompt (als Administrator):**
```cmd
# Voor IIS
iisreset

# Voor Apache (als je XAMPP gebruikt)
net stop apache2.4
net start apache2.4
```

### 3. Directory aanmaken (PowerShell als Administrator)

```powershell
# Ga naar je website directory
cd C:\inetpub\wwwroot\nt2taallesinternational

# Maak directories aan
New-Item -ItemType Directory -Path "uploads" -Force
New-Item -ItemType Directory -Path "uploads\videos" -Force
New-Item -ItemType Directory -Path "uploads\assignments" -Force

# Geef IIS_IUSRS schrijfrechten
icacls uploads /grant IIS_IUSRS:(OI)(CI)F
icacls uploads\videos /grant IIS_IUSRS:(OI)(CI)F
icacls uploads\assignments /grant IIS_IUSRS:(OI)(CI)F
```

### 4. Via File Explorer

**Directory permissions:**
1. Rechts-klik op `uploads` folder
2. **Properties** → **Security** tab
3. **Edit** → **Add**
4. Type: `IIS_IUSRS`
5. Check **"Write"** en **"Modify"**
6. **OK**

### 5. Test via Remote Desktop

**Als je Remote Desktop toegang hebt:**
1. Log in op je VPS
2. Open **IIS Manager**
3. Ga naar je website
4. Dubbel-klik **"PHP"**
5. Pas de settings aan
6. **Apply**

### 6. Automatische directory creation

Upload `admin/debug/create_dirs.php` en ga naar:
`https://nt2taallesinternational.com/admin/debug/create_dirs.php`

### 7. Test configuratie

Ga naar: `https://nt2taallesinternational.com/admin/debug/php_config.php`

### 8. Als je geen directe toegang hebt

**Via Strato VPS Control Panel:**
1. Log in op Strato VPS Control Panel
2. Ga naar **"Server Management"**
3. Zoek **"PHP Configuration"**
4. Pas de waarden aan
5. **Save** en **Restart** services

**Via Strato Support:**
- Vraag om PHP settings aanpassing
- Vraag om directory permissions
- Vermeld dat je VPS Windows gebruikt

### 9. Alternatieve oplossing

**Tijdelijk via PHP:**
Voeg dit toe aan het begin van `handlers/upload_video.php`:

```php
// VPS Windows temporary fix
ini_set('upload_max_filesize', '500M');
ini_set('post_max_size', '500M');
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);
ini_set('memory_limit', '256M');
```

---

**Volgende stappen:**
1. Vind en pas php.ini aan
2. Herstart IIS/Apache
3. Maak directories aan
4. Test configuratie
5. Probeer video upload

**Heb je Remote Desktop toegang tot je VPS?** Dan kan ik specifiekere instructies geven voor jouw setup.
