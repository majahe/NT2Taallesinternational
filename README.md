# NT2 Taalles International

PHP-based website and learning management system (LMS) for NT2 (Dutch as a Second Language). Includes a public website, admin tools for course and student management, and a student portal for lessons, assignments, and progress tracking.

## What is included

- Public site with course pages, registration, and contact form
- Admin panel for registrations, payments, planning, courses, lessons, and assignments
- Student portal with authentication, course access, assignments, and progress
- MySQL database integration with shared helpers in `includes/`
- Email notifications using PHPMailer

## Requirements

- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.3+
- IIS (Windows) or Apache (Linux)

## Quick start

1. Set up the database:
   ```bash
   php database/setup_database.php
   php database/update_lms_tables.php
   ```

2. Configure the app:
   - Edit `includes/config.php` with database and SMTP settings.

3. Access the admin login:
   - `/admin/auth/index.php`

4. Grant student access:
   - Admin Dashboard -> Registered Students -> Grant Course Access

5. Student login:
   - `/student/auth/login.php`

## Configuration

Main settings live in `includes/config.php`:

- Database connection
- SMTP credentials
- Website URL
- Admin email

Uploads are stored in `uploads/` (create `uploads/` and `uploads/videos/` if missing).

## URLs

Public pages:

- `index.php` (home)
- `pages/about.php`
- `pages/contact.php`
- `pages/register.php`
- `pages/cursus-engels-nederlands.php`
- `pages/cursus-russisch-nederlands.php`

Admin:

- Login: `/admin/auth/index.php`
- Dashboard: `/admin/dashboard/dashboard.php`

Student:

- Login: `/student/auth/login.php`
- Dashboard: `/student/dashboard/dashboard.php`

## Documentation

See `Guide/README.md` for full documentation and setup guides. Notable entries:

- `Guide/LMS-Quick-Start.md`
- `Guide/LMS-Windows-Setup.md`
- `Guide/LMS-Troubleshooting.md`
- `Guide/Registered-Students-Guide.md`
- `Guide/FEATURE-OVERVIEW.md`

## Security notes

- Keep `admin/debug/` tools disabled or protected in production.
- Configure HTTPS and secure session settings in production.
- Do not commit real credentials to version control.

## License

Proprietary software for NT2 Taalles International.
