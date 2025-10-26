# NT2 Taalles International - Online LMS Platform Handleiding

## Inhoudsopgave
1. [Inleiding](#inleiding)
2. [Voor Administrators](#voor-administrators)
3. [Voor Studenten](#voor-studenten)
4. [Installatie & Setup](#installatie--setup)
5. [Veelgestelde Vragen](#veelgestelde-vragen)

---

## Inleiding

Welkom bij het Online Learning Management System (LMS) van NT2 Taalles International. Dit platform stelt studenten in staat om online taalcursussen te volgen, video's te bekijken, opdrachten te maken en hun voortgang te volgen.

---

## Voor Administrators

### Database Setup

Voordat je het systeem kunt gebruiken, moet je de database structuur aanmaken:

1. **Option 1: SQL Bestand Uitvoeren**
   - Open phpMyAdmin of MySQL client
   - Selecteer de database `nt2_db`
   - Voer het bestand `database/lms_schema.sql` uit

2. **Option 2: PHP Script Gebruiken**
   - Open in browser: `http://yourdomain.com/database/update_lms_tables.php`
   - Dit script voert automatisch alle SQL queries uit

### Cursus Aanmaken

1. **Login als Admin**
   - Ga naar `/admin/auth/index.php`
   - Login met je admin credentials

2. **Nieuwe Cursus Aanmaken**
   - Ga naar `Admin Dashboard` → `Manage Courses`
   - Klik op `+ New Course`
   - Vul in:
     - **Title**: Bijv. "Nederlands voor Beginners"
     - **Description**: Beschrijving van de cursus
     - **Level**: Beginner/Intermediate/Advanced
     - **Language From**: Bijv. "English" of "Russian"
     - **Language To**: Standaard "Dutch"
     - **Active**: Checkbox aanvinken voor actieve cursus
   - Klik `Create Course`

3. **Modules Toevoegen**
   - Klik op `Manage Modules` bij een cursus
   - Klik `+ New Module`
   - Vul module titel en beschrijving in
   - **Order Index**: Nummer voor volgorde (0, 1, 2, etc.)
   - Klik `Create Module`

4. **Lessen Toevoegen**
   - Klik op `Manage Lessons` bij een module
   - Klik `+ New Lesson`
   - Vul in:
     - **Title**: Les titel
     - **Description**: Korte beschrijving
     - **Content**: Volledige lesinhoud (tekst)
     - **Video Path**: Pad naar video (na upload)
     - **Order Index**: Volgorde binnen module
     - **Preview**: Checkbox voor preview/grratis les
   - Klik `Create Lesson`

### Video Uploaden

1. **Video Uploaden**
   - Ga naar `Admin Dashboard` → `Upload Video`
   - Selecteer video bestand (MP4, MOV, AVI, WebM)
   - Maximaal 500MB
   - Klik `Upload Video`
   - Wacht tot upload compleet is
   - **Kopieer het pad** dat wordt getoond (bijv. `/uploads/videos/video_123.mp4`)

2. **Video Koppelen aan Les**
   - Ga naar de les die je wilt bewerken
   - Plak het gekopieerde pad in het "Video Path" veld
   - Sla op

### Opdrachten Aanmaken

1. **Nieuwe Opdracht**
   - Ga naar een les → `Manage Assignments`
   - Klik `+ New Assignment`
   - Vul in:
     - **Title**: Opdracht titel
     - **Description**: Beschrijving
     - **Type**: 
       - Multiple Choice
       - Fill in the Blank
       - Essay (handmatig nakijken)
       - File Upload (handmatig nakijken)
     - **Points**: Totaal punten
     - **Required**: Verplicht ja/nee

2. **Vragen Toevoegen**
   - Klik `+ Add Question` in het formulier
   - Voor **Multiple Choice**:
     - Vul vraag in
     - Voeg opties toe (één per regel)
     - Geef correct antwoord op
   - Voor **Fill in the Blank**:
     - Vul vraag in
     - Geef correct antwoord op
   - Voor **Essay**:
     - Vul vraag in
     - Geen correct antwoord nodig (handmatig nakijken)
   - Herhaal voor meerdere vragen
   - Klik `Create Assignment`

### Studenten Toegang Verlenen

1. **Course Access Granten**
   - Ga naar `Registered Students`
   - Klik op een student card
   - Klik `Grant Course Access`
   - Selecteer cursus
   - Optioneel: stel "Access Until" datum in
   - Klik `Grant Access`
   - Student ontvangt automatisch email met login instructies

2. **Wat gebeurt er?**
   - Er wordt een password token gegenereerd
   - Student ontvangt email met link om wachtwoord in te stellen
   - Na wachtwoord setup kan student inloggen
   - Student krijgt toegang tot toegewezen cursus

### Opdrachten Nakijken

1. **Automatisch Genaktekte Opdrachten**
   - Multiple Choice en Fill-in worden automatisch genaktek
   - Studenten zien direct hun score

2. **Handmatig Nakijken**
   - Ga naar `Assignments` → `View Submissions`
   - Bij "pending" status: klik op submission
   - Vul score in (max punten)
   - Geef feedback
   - Klik `Grade Submission`
   - Student wordt op de hoogte gesteld

### Upload Directories Aanmaken

Zorg dat deze directories bestaan en schrijfbaar zijn:
```
uploads/
  videos/
  assignments/
```

**Windows (PowerShell):**
```powershell
New-Item -ItemType Directory -Path "uploads\videos" -Force
New-Item -ItemType Directory -Path "uploads\assignments" -Force
```

**Linux/Mac (Terminal):**
```bash
mkdir -p uploads/videos
mkdir -p uploads/assignments
chmod 755 uploads/videos
chmod 755 uploads/assignments
```

**Via FTP/File Manager:**
- Maak handmatig de mappen aan via je FTP client of file manager
- Zorg dat de webserver schrijfrechten heeft

---

## Voor Studenten

### Eerste Login

1. **Wachtwoord Instellen**
   - Check je email voor "Course Access Granted" bericht
   - Klik op de link in de email
   - Stel je wachtwoord in (minimaal 8 karakters)
   - Bevestig wachtwoord
   - Klik `Set Password`

2. **Inloggen**
   - Ga naar `Student Login` op de website
   - Voer je email en wachtwoord in
   - Klik `Login`

### Dashboard

1. **Overzicht**
   - Zie alle ingeschreven cursussen
   - Bekijk totale punten
   - Zie recente activiteit
   - Zie voortgang statistieken

2. **Navigatie**
   - **Dashboard**: Overzicht van alles
   - **My Courses**: Alle cursussen
   - **Progress**: Gedetailleerde voortgang
   - **Logout**: Uitloggen

### Cursus Volgen

1. **Cursus Openen**
   - Klik op `Continue Learning` bij een cursus
   - Zie modules en lessen overzicht
   - Voortgang wordt getoond per module

2. **Les Bekijken**
   - Klik op een les (unlocked lessen zijn beschikbaar)
   - Video start automatisch
   - Lees les content
   - Video voortgang wordt automatisch bijgehouden

3. **Lessen Unlocken**
   - Eerste les is altijd unlocked
   - Volgende lessen unlocken na voltooiing vorige les
   - Les is "completed" wanneer:
     - Video volledig bekeken
     - Of handmatig op "Complete Lesson" geklikt

### Opdrachten Maken

1. **Opdracht Starten**
   - Ga naar een les met opdrachten
   - Klik op opdracht onder "Assignments"
   - Lees instructies zorgvuldig

2. **Vragen Beantwoorden**
   - **Multiple Choice**: Selecteer juiste antwoord
   - **Fill in**: Type je antwoord
   - **Essay**: Schrijf uitgebreid antwoord
   - **File Upload**: Upload je bestand

3. **Indienen**
   - Controleer alle antwoorden
   - Klik `Submit Assignment`
   - Je krijgt direct feedback (automatisch) of later (handmatig)

4. **Resultaten Bekijken**
   - Ga naar opdracht → `View Results`
   - Zie je score en feedback
   - Bekijk correcte antwoorden bij foute vragen

### Voortgang Volgen

1. **Course Progress**
   - Zie percentage compleet per cursus
   - Aantal lessen voltooid vs totaal
   - Progress bar per cursus

2. **Mijn Progress Pagina**
   - Gedetailleerde voortgang per cursus
   - Totaal punten behaald
   - Completed lessons count

### Problemen Oplossen

**Kan niet inloggen?**
- Check of je wachtwoord correct is ingesteld
- Check of je course access hebt gekregen
- Contact administrator

**Les is locked?**
- Voltooi de vorige les eerst
- Check of je toegang hebt tot de cursus

**Video speelt niet af?**
- Check je internet verbinding
- Probeer andere browser
- Contact administrator

**Opdracht niet ingediend?**
- Check of alle verplichte velden ingevuld zijn
- Check internet verbinding
- Probeer opnieuw

---

## Installatie & Setup

### Vereisten

- PHP 7.4 of hoger
- MySQL 5.7 of hoger
- Apache/Nginx webserver
- PHPMailer (al geïnstalleerd)

### Stap-voor-stap Installatie

1. **Database Setup**
   ```sql
   -- Voer uit in MySQL:
   USE nt2_db;
   SOURCE database/lms_schema.sql;
   ```

2. **Directories Aanmaken**
   ```bash
   mkdir -p uploads/videos
   mkdir -p uploads/assignments
   chmod 755 uploads
   chmod 755 uploads/videos
   chmod 755 uploads/assignments
   ```

3. **Permissions Check**
   - Zorg dat PHP schrijfrechten heeft op uploads/
   - Check `.htaccess` voor file protection

4. **Configuratie**
   - Check `includes/config.php` voor database settings
   - Check SMTP settings voor emails
   - Update `WEBSITE_URL` naar je domein

5. **Test Run**
   - Login als admin
   - Maak test cursus aan
   - Test video upload
   - Grant access aan test student
   - Test complete flow

### Eerste Cursus Aanmaken

1. **Basis Cursus Structuur**
   ```
   Cursus: Nederlands voor Beginners
   ├── Module 1: Woordenschat
   │   ├── Les 1: Basis Woorden
   │   ├── Les 2: Groeten en Begroetingen
   │   └── Les 3: Getallen
   ├── Module 2: Grammatica
   │   ├── Les 1: Werkwoorden
   │   └── Les 2: Zelfstandige Naamwoorden
   └── Module 3: Conversatie
       └── Les 1: Dagelijkse Gesprekken
   ```

2. **Video's Voorbereiden**
   - Exporteer video's in MP4 formaat
   - Compress voor web (H.264 codec)
   - Maximaal 500MB per video
   - Upload via admin interface

3. **Opdrachten Voorbereiden**
   - Maak vragenlijst
   - Noteer correcte antwoorden
   - Bepaal punten per vraag
   - Maak opdrachten aan in systeem

---

## Veelgestelde Vragen

### Voor Administrators

**Q: Hoe verwijder ik een cursus?**
A: Ga naar Manage Courses → Klik Delete bij de cursus. Let op: Dit verwijdert ook alle modules, lessen en opdrachten!

**Q: Kan ik een les bewerken nadat studenten hem al hebben bekeken?**
A: Ja, je kunt lessen altijd bewerken. Student voortgang blijft behouden.

**Q: Hoe werkt het unlock systeem?**
A: Lessen unlocken sequentieel - student moet vorige les voltooien. Je kunt preview lessen instellen die altijd unlocked zijn.

**Q: Kan ik een student toegang geven tot meerdere cursussen?**
A: Ja, gebruik "Grant Course Access" voor elke cursus apart.

**Q: Hoe exporteer ik student voortgang?**
A: Dit is nog niet geïmplementeerd. Gebruik phpMyAdmin om data te exporteren vanuit `student_progress` tabel.

### Voor Studenten

**Q: Kan ik mijn wachtwoord wijzigen?**
A: Dit moet nog geïmplementeerd worden. Contact administrator voor wachtwoord reset.

**Q: Hoelang blijft mijn toegang geldig?**
A: Dit hangt af van wat de administrator heeft ingesteld. Check "My Courses" voor access until datum.

**Q: Kan ik een les opnieuw bekijken?**
A: Ja, je kunt altijd teruggaan naar lessen die je al hebt voltooid.

**Q: Wat gebeurt er als ik een opdracht verkeerd maak?**
A: Bij multiple choice/fill-in zie je direct het correcte antwoord. Bij essays krijg je feedback van de docent.

**Q: Kan ik mijn voortgang delen?**
A: Dit is nog niet mogelijk. Gebruik screenshots indien nodig.

---

## Technische Specificaties

### Ondersteunde Video Formatten
- MP4 (aanbevolen)
- MOV
- AVI
- WebM

### Database Tabellen
- `courses` - Cursussen
- `course_modules` - Modules
- `lessons` - Lessen
- `assignments` - Opdrachten
- `assignment_questions` - Vragen
- `student_enrollments` - Inschrijvingen
- `student_progress` - Voortgang
- `student_assignments` - Inzendingen

### Browser Ondersteuning
- Chrome (aanbevolen)
- Firefox
- Safari
- Edge

### Bestandslimieten
- Video upload: 500MB max
- File upload opdrachten: Afhankelijk van PHP settings
- Image uploads: N/A (nog niet geïmplementeerd)

---

## Support & Contact

Voor vragen of problemen:
- Email: Info@nt2taallesinternational.com
- Website: nt2taallesinternational.com

---

**Laatste Update**: December 2024
**Versie**: 1.0

