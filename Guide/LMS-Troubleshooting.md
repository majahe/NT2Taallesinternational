# LMS Platform - Troubleshooting Guide

## Veelvoorkomende Problemen en Oplossingen

### Database Problemen

**Probleem: "Table doesn't exist" error**
```
Oplossing:
1. Check of database/lms_schema.sql is uitgevoerd
2. Controleer of je in de juiste database bent (nt2_db)
3. Voer SQL queries handmatig uit via phpMyAdmin
```

**Probleem: "Duplicate column" error bij ALTER TABLE**
```
Oplossing:
1. Check welke kolommen al bestaan: SHOW COLUMNS FROM registrations;
2. Verwijder IF NOT EXISTS en voer alleen nieuwe kolommen toe
3. Of gebruik: DESCRIBE registrations; om te zien wat er is
```

**Probleem: Foreign key constraint errors**
```
Oplossing:
1. Zorg dat courses tabel eerst bestaat voordat je modules maakt
2. Zorg dat modules bestaan voordat je lessen maakt
3. Check of verwijderde records nog references hebben
```

### Upload Problemen

**Probleem: Video upload faalt**
```
Oplossing:
1. Check upload_max_filesize in php.ini (minimaal 500M)
2. Check post_max_size in php.ini
3. Check of uploads/videos directory bestaat en schrijfbaar is
4. Check file permissions: chmod 755 uploads/videos
```

**Probleem: "Permission denied" bij upload**
```
Oplossing (Linux/Mac):
1. Check directory permissions: ls -la uploads/
2. Zet permissions: chmod 755 uploads && chmod 755 uploads/videos
3. Check owner: chown -R www-data:www-data uploads/

Oplossing (Windows):
1. Check folder properties → Security tab
2. Zorg dat IIS_IUSRS of IUSR account Write rechten heeft
3. OF gebruik Administrator account voor webserver
4. Check via: icacls uploads /grant Users:(OI)(CI)F
```

**Probleem: Video speelt niet af**
```
Oplossing:
1. Check of video path correct is (moet beginnen met /uploads/videos/)
2. Check of bestand bestaat op server
3. Check video format (gebruik MP4 H.264)
4. Check browser console voor errors
```

### Student Login Problemen

**Probleem: "Invalid email or password"**
```
Oplossing:
1. Check of student wachtwoord heeft ingesteld
2. Check of course_access_granted = TRUE in database
3. Check of email correct is
4. Reset wachtwoord via database indien nodig
```

**Probleem: "Course access not granted"**
```
Oplossing:
1. Ga naar Registered Students
2. Klik Grant Course Access
3. Selecteer cursus en grant
4. Check student_enrollments tabel in database
```

**Probleem: Token expired**
```
Oplossing:
1. Genereer nieuwe token in database:
   UPDATE registrations 
   SET password_token = 'NEW_TOKEN_HERE', 
       password_token_expires = DATE_ADD(NOW(), INTERVAL 7 DAY)
   WHERE email = 'student@email.com';
2. Of grant access opnieuw via admin panel
```

### Course Access Problemen

**Probleem: Les is locked maar student heeft toegang**
```
Oplossing:
1. Check of vorige les voltooid is
2. Check student_progress tabel
3. Markeer vorige les als completed handmatig indien nodig:
   INSERT INTO student_progress (student_id, lesson_id, status, completed_at)
   VALUES (STUDENT_ID, LESSON_ID, 'completed', NOW())
   ON DUPLICATE KEY UPDATE status = 'completed';
```

**Probleem: Course niet zichtbaar voor student**
```
Oplossing:
1. Check student_enrollments tabel
2. Check of status = 'active'
3. Check of access_until niet verstreken is
4. Check of course is_active = TRUE
```

### Assignment Problemen

**Probleem: Opdracht kan niet ingediend worden**
```
Oplossing:
1. Check of alle required velden ingevuld zijn
2. Check browser console voor JavaScript errors
3. Check PHP error logs
4. Check of assignment bestaat in database
```

**Probleem: Auto-grading werkt niet**
```
Oplossing:
1. Check correct_answer veld in assignment_questions
2. Check of question_type correct is (multiple_choice/fill_in)
3. Check submit_assignment.php voor errors
4. Check student_assignments tabel voor submission
```

**Probleem: Score wordt niet getoond**
```
Oplossing:
1. Check student_assignments tabel
2. Check of status = 'graded' of 'returned'
3. Check score en max_score velden
4. Refresh pagina
```

### Progress Tracking Problemen

**Probleem: Voortgang wordt niet bijgewerkt**
```
Oplossing:
1. Check browser console voor JavaScript errors
2. Check of progress_tracker.js geladen wordt
3. Check handlers/update_progress.php
4. Check student_progress tabel handmatig
5. Check AJAX requests in Network tab
```

**Probleem: Video time niet getrackt**
```
Oplossing:
1. Check of video element ID correct is ('lessonVideo')
2. Check JavaScript console voor errors
3. Check of update_progress.php bereikbaar is
4. Check database voor time_spent updates
```

### Email Problemen

**Probleem: Student ontvangt geen email**
```
Oplossing:
1. Check SMTP settings in includes/config.php
2. Check PHP error logs voor email errors
3. Test email versturen met test script
4. Check spam folder student
5. Check database voor password_token
```

**Probleem: Email werkt niet op localhost**
```
Oplossing:
1. Gebruik SMTP debug mode
2. Check SMTP_SSL_VERIFY setting
3. Voor development: gebruik Mailtrap of Mailhog
4. Check firewall voor poort 587
```

## Database Queries voor Debugging

### Check Student Status
```sql
SELECT r.id, r.name, r.email, r.password_set, r.course_access_granted,
       COUNT(se.id) as enrolled_courses
FROM registrations r
LEFT JOIN student_enrollments se ON r.id = se.student_id
WHERE r.email = 'student@email.com'
GROUP BY r.id;
```

### Check Course Access
```sql
SELECT c.title, se.status, se.access_until, se.enrolled_at
FROM student_enrollments se
JOIN courses c ON se.course_id = c.id
WHERE se.student_id = STUDENT_ID;
```

### Check Student Progress
```sql
SELECT l.title, sp.status, sp.time_spent, sp.completed_at
FROM student_progress sp
JOIN lessons l ON sp.lesson_id = l.id
WHERE sp.student_id = STUDENT_ID
ORDER BY sp.updated_at DESC;
```

### Check Assignment Submissions
```sql
SELECT a.title, sa.status, sa.score, sa.max_score, sa.submitted_at
FROM student_assignments sa
JOIN assignments a ON sa.assignment_id = a.id
WHERE sa.student_id = STUDENT_ID
ORDER BY sa.submitted_at DESC;
```

## Log Files Bekijken

**PHP Errors:**
```bash
tail -f /var/log/apache2/error.log
# OF
tail -f /var/log/php_errors.log
```

**MySQL Errors:**
```bash
tail -f /var/log/mysql/error.log
```

**PHP Error Log Location (in script):**
```php
error_log("Debug message", 3, "/path/to/debug.log");
```

## Performance Optimalisatie

**Probleem: Trage pagina laadtijden**
```
Oplossing:
1. Zet database indexes: CREATE INDEX idx_student ON student_progress(student_id);
2. Gebruik LIMIT bij grote queries
3. Cache statische content
4. Compress video's voor web
5. Gebruik CDN voor video's (optioneel)
```

**Probleem: Database queries zijn traag**
```
Oplossing:
1. Voeg indexes toe aan veelgebruikte kolommen
2. Gebruik EXPLAIN om queries te analyseren
3. Optimaliseer JOINs
4. Cache vaak gebruikte data
```

## Backup & Restore

**Database Backup (Linux/Mac):**
```bash
mysqldump -u root -p nt2_db > backup_$(date +%Y%m%d).sql
```

**Database Backup (Windows):**
```powershell
mysqldump -u root -p nt2_db > backup_$(Get-Date -Format "yyyyMMdd").sql
```

**Database Restore (Linux/Mac):**
```bash
mysql -u root -p nt2_db < backup_20241201.sql
```

**Database Restore (Windows):**
```powershell
Get-Content backup_20241201.sql | mysql -u root -p nt2_db
```

**Files Backup (Linux/Mac):**
```bash
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz uploads/
```

**Files Backup (Windows):**
```powershell
Compress-Archive -Path uploads -DestinationPath "uploads_backup_$(Get-Date -Format 'yyyyMMdd').zip"
```

## Contact voor Support

Als problemen blijven bestaan:
- Email: Info@nt2taallesinternational.com
- Check error logs
- Document exacte foutmeldingen
- Screenshots van problemen

---

**Tip**: Houd altijd backups bij voordat je database wijzigingen maakt!

