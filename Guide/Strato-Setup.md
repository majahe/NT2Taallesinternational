# Strato Hosting - PHP Upload Fix

## Strato-specifieke oplossing

### 1. .htaccess bestand (Aanbevolen)
Het .htaccess bestand is al aangemaakt met Strato-specifieke instellingen.

### 2. Via Strato Control Panel (Alternatief)

**Log in op Strato Control Panel:**
1. Ga naar **"Websites & Domains"**
2. Klik op je domein
3. Ga naar **"PHP Settings"** of **"PHP Configuration"**
4. Pas deze waarden aan:
   - `upload_max_filesize`: 500M
   - `post_max_size`: 500M
   - `max_execution_time`: 300
   - `max_input_time`: 300
   - `memory_limit`: 256M
5. **Save** wijzigingen

### 3. Directory aanmaken via Strato File Manager

**Via Strato Control Panel:**
1. Ga naar **"File Manager"**
2. Navigeer naar je website directory
3. Maak folder `uploads` aan
4. In `uploads` maak folder `videos` aan
5. In `uploads` maak folder `assignments` aan
6. Zet permissions op `755` voor alle folders

**Via FTP:**
```
uploads/
  videos/ (755 permissions)
  assignments/ (755 permissions)
```

### 4. Strato-specifieke problemen

**Als .htaccess niet werkt:**
- Strato blokkeert soms php_value directives
- Gebruik dan Control Panel methode
- Of contact Strato support

**Directory permissions:**
- Strato gebruikt vaak `755` in plaats van `777`
- Test eerst met `755`

### 5. Test stappen

1. **Upload .htaccess** naar je website root
2. **Maak directories** via File Manager of FTP
3. **Test configuratie:**
   ```
   https://nt2taallesinternational.com/admin/debug/php_config.php
   ```
4. **Test directory creation:**
   ```
   https://nt2taallesinternational.com/admin/debug/create_dirs.php
   ```
5. **Probeer video upload** opnieuw

### 6. Als het nog niet werkt

**Contact Strato Support:**
- Vraag om PHP upload limits verhoging
- Vraag om directory permissions check
- Vermeld dat je video uploads nodig hebt tot 500MB

**Strato Support contact:**
- Telefoon: 030-670 09 670
- Email: via Control Panel → Support
- Live Chat: via Strato website

### 7. Alternatieve oplossing

**Tijdelijk via PHP:**
Voeg dit toe aan het begin van `handlers/upload_video.php`:

```php
// Strato temporary fix
ini_set('upload_max_filesize', '500M');
ini_set('post_max_size', '500M');
ini_set('max_execution_time', 300);
ini_set('max_input_time', 300);
ini_set('memory_limit', '256M');
```

Dit overschrijft de server-instellingen tijdelijk.

---

**Volgende stappen:**
1. Upload het bijgewerkte .htaccess bestand
2. Maak directories aan via Strato File Manager
3. Test de configuratie pagina
4. Probeer video upload opnieuw
