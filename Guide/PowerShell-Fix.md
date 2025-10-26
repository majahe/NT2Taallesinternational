# PowerShell Commands voor Strato VPS Windows

## Correcte icacls syntax

**Probleem:** PowerShell interpreteert `(OI)(CI)F` als aparte commands.

**Oplossing:** Gebruik quotes rond de permission string.

## Correcte commands:

```powershell
# Navigeer naar je website directory
cd C:\inetpub\wwwroot\nt2taallesinternational

# Maak directories aan
New-Item -ItemType Directory -Path "uploads" -Force
New-Item -ItemType Directory -Path "uploads\videos" -Force
New-Item -ItemType Directory -Path "uploads\assignments" -Force

# Geef IIS_IUSRS schrijfrechten (met quotes!)
icacls uploads /grant "IIS_IUSRS:(OI)(CI)F"
icacls uploads\videos /grant "IIS_IUSRS:(OI)(CI)F"
icacls uploads\assignments /grant "IIS_IUSRS:(OI)(CI)F"
```

## Alternatieve methode (als icacls niet werkt):

```powershell
# Via File Explorer permissions
# Rechts-klik op uploads folder → Properties → Security → Edit → Add → IIS_IUSRS → Write/Modify
```

## Test permissions:

```powershell
# Check of directory writable is
Test-Path "uploads\videos" -PathType Container
Get-Acl "uploads" | Format-List
```

## Als je in verkeerde directory zit:

```powershell
# Zoek je website directory
Get-ChildItem C:\inetpub\wwwroot\
# Of
Get-ChildItem C:\websites\
```

## Volledige workflow:

1. **Open PowerShell als Administrator**
2. **Navigeer naar juiste directory:**
   ```powershell
   cd C:\inetpub\wwwroot\nt2taallesinternational
   ```
3. **Maak directories:**
   ```powershell
   New-Item -ItemType Directory -Path "uploads\videos" -Force
   ```
4. **Set permissions:**
   ```powershell
   icacls uploads /grant "IIS_IUSRS:(OI)(CI)F"
   ```
5. **Test:**
   ```powershell
   Test-Path "uploads\videos" -PathType Container
   ```

**Belangrijk:** Gebruik altijd quotes rond de permission string in PowerShell!
