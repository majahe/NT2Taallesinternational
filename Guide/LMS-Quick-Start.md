# LMS Platform - Quick Start Guide

## Snelle Setup voor Administrators

### 1. Database Installeren (5 minuten)

```bash
# Via MySQL command line:
mysql -u root -p nt2_db < database/lms_schema.sql

# OF via phpMyAdmin:
# - Selecteer database 'nt2_db'
# - Ga naar Import
# - Upload database/lms_schema.sql
# - Klik Go
```

### 2. Directories Aanmaken (1 minuut)

**Windows (PowerShell):**
```powershell
New-Item -ItemType Directory -Path "uploads\videos" -Force
New-Item -ItemType Directory -Path "uploads\assignments" -Force
```

**Linux/Mac:**
```bash
mkdir -p uploads/videos
mkdir -p uploads/assignments
chmod 755 uploads
```

### 3. Eerste Cursus Aanmaken (10 minuten)

**Stap 1: Cursus**
- Login als admin → Manage Courses → + New Course
- Title: "Nederlands voor Beginners"
- Level: Beginner
- Language From: English
- Create

**Stap 2: Module**
- Klik Manage Modules → + New Module
- Title: "Module 1: Woordenschat"
- Order: 0
- Create

**Stap 3: Les**
- Klik Manage Lessons → + New Lesson
- Title: "Les 1: Basis Woorden"
- Content: Typ lesinhoud
- Order: 0
- Create

**Stap 4: Video Upload**
- Ga naar Upload Video
- Upload je video
- Kopieer het pad (bijv. `/uploads/videos/video_123.mp4`)
- Ga terug naar les → Edit → Plak pad in Video Path

**Stap 5: Opdracht**
- Bij les → Manage Assignments → + New Assignment
- Type: Multiple Choice
- Add Question
- Vul vraag + opties in
- Create

### 4. Student Toegang Verlenen (2 minuten)

- Ga naar Registered Students
- Klik op student → Grant Course Access
- Selecteer cursus
- Grant Access
- Student krijgt email automatisch

---

## Snelle Start voor Studenten

### 1. Wachtwoord Instellen
- Check email voor "Course Access Granted"
- Klik link → Stel wachtwoord in

### 2. Inloggen
- Klik "Student Login" op website
- Email + wachtwoord
- Login

### 3. Cursus Volgen
- Dashboard → Kies cursus
- Klik op les
- Bekijk video + content
- Maak opdrachten
- Voltooi les

---

## Checklist voor Administrators

- [ ] Database schema geïnstalleerd
- [ ] Upload directories aangemaakt
- [ ] Test cursus aangemaakt
- [ ] Test video geüpload
- [ ] Test opdracht aangemaakt
- [ ] Test student toegang gegeven
- [ ] Complete flow getest

---

## Belangrijke URL's

**Admin:**
- Dashboard: `/admin/dashboard/dashboard.php`
- Manage Courses: `/admin/courses/manage_courses.php`
- Upload Video: `/admin/courses/upload_video.php`

**Student:**
- Login: `/student/auth/login.php`
- Dashboard: `/student/dashboard/dashboard.php`

---

**Tip**: Begin met een kleine test cursus om het systeem te leren kennen!

