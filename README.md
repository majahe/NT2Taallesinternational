# NT2 Taalles International - Complete Learning Management System

A comprehensive PHP-based Learning Management System (LMS) for NT2 (Dutch as a Second Language) courses, featuring course registration, online learning platform, student management, and administrative tools.

## 📋 Table of Contents

- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [Project Structure](#-project-structure)
- [Quick Start](#-quick-start)
- [Setup Instructions](#️-setup-instructions)
- [Configuration](#-configuration)
- [Admin Panel](#-admin-panel)
- [Student Portal](#-student-portal)
- [LMS Features](#-lms-features)
- [Security](#-security)
- [Documentation](#-documentation)
- [Support](#-support)

## 🚀 Features

### Public Website
- **Responsive Design**: Mobile-friendly layout with modern CSS
- **Contact Form**: Secure form processing with server-side validation and CSRF protection
- **Course Registration**: Student registration system for Dutch language courses
- **Multi-language Course Pages**: English to Dutch and Russian to Dutch course information pages
- **Email Notifications**: Automated email system using PHPMailer

### Admin Panel
- **Dashboard**: Comprehensive overview of all registrations and system statistics
- **Registered Students Management**: Complete student lifecycle management with:
  - Start/end dates tracking
  - Payment status monitoring (Paid/Pending/Partial)
  - Personal information management
  - Search and filter functionality
  - Revenue tracking and statistics
- **Payment Tracking**: Monitor student payments with status indicators and printable reports
- **Course Planning**: Schedule and manage course sessions
- **Course Management**: Create and manage courses, modules, and lessons
- **Assignment Management**: Create assignments for lessons with multiple question types:
  - Multiple Choice
  - Fill in the Blank
  - Essay (manual grading)
  - File Upload (manual grading)
- **Video Upload**: Upload and manage course videos (MP4, MOV, AVI, WebM, max 500MB)
- **Student Access Control**: Grant course access to registered students

### Student Portal (LMS)
- **Student Authentication**: Secure login system for enrolled students
- **Course Browser**: View all enrolled courses with progress tracking
- **Video Lessons**: Watch course videos with automatic progress tracking
- **Interactive Lessons**: Complete lessons with text content and video
- **Assignment System**: Complete assignments and receive feedback
- **Progress Tracking**: Monitor course completion and achievement statistics
- **Responsive Design**: Full functionality on desktop, tablet, and mobile devices

### Technical Features
- **Database Integration**: MySQL database with prepared statements and Query Builder
- **CSRF Protection**: Cross-Site Request Forgery protection on all forms
- **Secure Authentication**: Separate admin and student authentication systems
- **Error Handling**: Comprehensive error handling and logging
- **Session Management**: Secure session handling for admin and student access

## 🛠 Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Email**: PHPMailer 6.8+
- **Web Server**: IIS (with web.config) or Apache (with .htaccess)
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Video Formats**: MP4, MOV, AVI, WebM

## 📁 Project Structure

```
NT2TaallesInternational/
├── index.php                              # Main homepage
├── web.config                             # IIS server configuration
├── .htaccess                              # Apache server configuration
├── README.md                              # This file
│
├── admin/                                 # Admin panel
│   ├── auth/                             # Authentication module
│   │   ├── index.php                     # Admin login page
│   │   ├── logout.php                    # Admin logout
│   │   └── change_password.php           # Password management
│   │
│   ├── dashboard/                        # Dashboard module
│   │   └── dashboard.php                 # Main admin interface
│   │
│   ├── students/                         # Student management module
│   │   ├── registered_students.php       # Registered students management
│   │   └── grant_course_access.php       # Grant LMS access to students
│   │
│   ├── payments/                         # Payment management module
│   │   ├── pending_payments.php         # Payment tracking interface
│   │   └── print_pending_payments.php   # Printable payment reports
│   │
│   ├── planning/                         # Course planning module
│   │   └── planning.php                 # Course scheduling interface
│   │
│   ├── courses/                          # Course management module
│   │   ├── manage_courses.php           # Create/edit courses
│   │   ├── manage_modules.php           # Create/edit modules
│   │   ├── manage_lessons.php           # Create/edit lessons
│   │   ├── upload_video.php             # Video upload interface
│   │   └── manual_upload.php            # Manual video path entry
│   │
│   ├── assignments/                      # Assignment management module
│   │   ├── manage_assignments.php       # Create/edit assignments
│   │   ├── view_submissions.php         # View student submissions
│   │   ├── create_assignment.php        # Assignment creation form
│   │   └── edit_assignment.php          # Assignment editing form
│   │
│   └── debug/                            # Debug utilities
│       ├── fix_password.php              # Password recovery tool
│       ├── server_diagnostic.php         # Server diagnostics
│       └── upload_test.php               # Upload testing
│
├── student/                              # Student portal (LMS)
│   ├── auth/                            # Student authentication
│   │   ├── login.php                    # Student login page
│   │   ├── logout.php                   # Student logout
│   │   └── register_password.php        # Password registration
│   │
│   ├── dashboard/                       # Student dashboard
│   │   ├── dashboard.php                # Main student dashboard
│   │   └── my_courses.php               # Course list view
│   │
│   ├── course/                          # Course viewing module
│   │   ├── view_course.php              # Course overview
│   │   ├── view_lesson.php              # Lesson viewer
│   │   ├── assignment.php               # Assignment viewer
│   │   ├── submit_assignment.php        # Assignment submission handler
│   │   └── assignment_result.php        # Assignment results viewer
│   │
│   └── progress/                        # Progress tracking module
│       └── my_progress.php              # Detailed progress view
│
├── assets/                               # Static files
│   ├── css/                             # Stylesheets
│   │   ├── style.css                    # Main styles
│   │   ├── contact.css                  # Contact page styles
│   │   ├── course.css                   # Course page styles
│   │   ├── about.css                    # About page styles
│   │   ├── student_portal.css           # Student portal styles
│   │   └── course_viewer.css            # Course viewer styles
│   │
│   ├── img/                             # Images
│   │   └── LOGO.png                     # Site logo
│   │
│   └── js/                              # JavaScript files
│       └── progress_tracker.js          # Progress tracking script
│
├── config/                               # Configuration directory
│   └── (configuration files)
│
├── database/                             # Database utilities
│   ├── setup_database.php               # Database setup utility
│   ├── update_database.php              # Database update utility
│   ├── update_lms_tables.php            # LMS tables setup
│   └── migrate_admin_files.php          # Migration utilities
│
├── handlers/                             # Form processors
│   ├── submit_contact.php               # Contact form handler
│   ├── submit_registration.php          # Registration form handler
│   ├── upload_video.php                 # Video upload handler
│   └── update_progress.php              # Progress update handler
│
├── includes/                             # Shared PHP files
│   ├── config.php                       # Main configuration loader
│   ├── db_connect.php                   # Database connection
│   ├── database/
│   │   └── QueryBuilder.php            # Database query builder
│   ├── functions.php                    # Utility functions
│   ├── header.php                       # Site header
│   ├── footer.php                       # Site footer
│   ├── student_header.php               # Student portal header
│   ├── admin_auth.php                   # Admin authentication helper
│   ├── student_auth.php                 # Student authentication helper
│   ├── csrf.php                         # CSRF protection functions
│   ├── error_handler.php                # Error handling
│   ├── errors/
│   │   └── 500.php                      # Error pages
│   ├── email_template.php               # Email templates
│   │
│   └── PHPMailer/                       # Email library
│       └── src/                         # PHPMailer source files
│
├── pages/                                # Content pages
│   ├── about.php                        # About page
│   ├── contact.php                      # Contact form
│   ├── contact_success.php             # Contact success page
│   ├── cursus-engels-nederlands.php   # English to Dutch course
│   ├── cursus-russisch-nederlands.php # Russian to Dutch course
│   ├── register.php                     # Course registration form
│   └── register_success.php            # Registration confirmation page
│
└── Guide/                                # Documentation
    ├── README.md                        # Documentation index
    ├── LMS-User-Guide.md                # LMS user guide (Dutch)
    ├── LMS-Quick-Start.md               # Quick start guide
    ├── LMS-Troubleshooting.md           # Troubleshooting guide
    ├── LMS-Windows-Setup.md             # Windows setup guide
    ├── Registered-Students-Guide.md     # Registered students guide
    ├── Registered-Students-Quick-Setup.md # Quick setup guide
    ├── FEATURE-OVERVIEW.md              # Visual feature guide
    ├── IMPLEMENTATION-SUMMARY.md        # Technical documentation
    ├── Security-Architecture-Guide.md   # Security architecture
    ├── Admin-Security-Migration-Guide.md # Security migration guide
    ├── CSRF-Testing-Guide.md            # CSRF testing guide
    ├── GitHub-Setup-Guide.md            # GitHub setup instructions
    ├── GitHub-Update-Guide.md           # GitHub update instructions
    ├── PHP-Upload-Fix.md                # Upload troubleshooting
    ├── PowerShell-Fix.md                # PowerShell fixes
    ├── Strato-Setup.md                  # Strato hosting setup
    └── Strato-VPS-Windows.md            # Strato VPS Windows setup
```

## 🚀 Quick Start

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd NT2TaallesInternational
   ```

2. **Setup database**
   ```bash
   php database/setup_database.php
   php database/update_lms_tables.php
   ```

3. **Configure settings**
   - Edit `includes/config.php` with your database and email settings

4. **Access admin panel**
   - Navigate to `/admin/auth/index.php`
   - Login with admin credentials

5. **Grant student access**
   - Go to Admin Dashboard → Registered Students
   - Click "Grant Course Access" for students
   - Students can then login at `/student/auth/login.php`

## 🛠️ Setup Instructions

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- IIS web server (Windows) or Apache (Linux)
- Composer (for PHPMailer dependencies, if needed)

### 1. Database Setup

1. **Initial database setup**:
   ```bash
   php database/setup_database.php
   ```

2. **Setup LMS tables**:
   ```bash
   php database/update_lms_tables.php
   ```

3. **Update database** (if needed):
   ```bash
   php database/update_database.php
   ```

### 2. Configuration

1. **Configure database and email settings** in `includes/config.php`:
   ```php
   // Database Configuration
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'nt2_db');

   // SMTP Configuration
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_USERNAME', 'your_email@gmail.com');
   define('SMTP_PASSWORD', 'your_app_password');
   define('SMTP_FROM_EMAIL', 'your_email@gmail.com');
   define('SMTP_FROM_NAME', 'NT2 Taalles International');

   // Admin Configuration
   define('ADMIN_EMAIL', 'admin@yourdomain.com');

   // Website Configuration
   define('WEBSITE_URL', 'https://yourdomain.com');
   ```

2. **Create upload directories**:
   ```bash
   mkdir uploads
   mkdir uploads/videos
   chmod 755 uploads
   chmod 755 uploads/videos
   ```

### 3. Web Server Configuration

#### IIS Setup (Windows)
- Ensure PHP is installed and configured
- Set document root to project directory
- The `web.config` file is already configured for URL rewriting
- Ensure PHP uploads are enabled (`upload_max_filesize` and `post_max_size`)

#### Apache Setup (Linux)
- Ensure PHP is installed and configured
- Enable mod_rewrite
- The `.htaccess` file is already configured
- Set proper file permissions

### 4. Email Configuration

1. **Gmail SMTP Setup**:
   - Enable 2-factor authentication
   - Generate an App Password
   - Use the App Password in your `config.php` file

2. **Other SMTP Providers**:
   - Update SMTP_HOST, SMTP_PORT, and credentials accordingly

## 🔧 Configuration

### Main Configuration File

The application uses `includes/config.php` for all configuration settings including:
- Database connection details
- SMTP email settings
- Admin email address
- Website URL

### Database Schema

The database includes the following main tables:
- `registrations` - Student course registrations
- `admins` - Admin user accounts
- `courses` - Course information
- `modules` - Course modules
- `lessons` - Course lessons
- `assignments` - Assignment definitions
- `assignment_submissions` - Student assignment submissions
- `student_course_access` - Student course enrollment
- `lesson_progress` - Student lesson completion tracking
- Additional tables for payments and planning

## 👨‍💼 Admin Panel

### Access

- **Login URL**: `/admin/auth/index.php`
- **Dashboard**: `/admin/dashboard/dashboard.php` (requires authentication)

### Features

- **Dashboard**: View and manage all course registrations
- **Registered Students**: Manage enrolled students with full details
- **Payments**: Track and manage student payments
- **Planning**: Schedule and manage course sessions
- **Course Management**: Create courses, modules, and lessons
- **Assignment Management**: Create and grade assignments
- **Video Upload**: Upload course videos
- **Student Access**: Grant LMS access to registered students
- **Settings**: Change admin password

### Admin Modules

1. **Authentication** (`admin/auth/`)
   - Secure login system
   - Password management
   - Session handling

2. **Dashboard** (`admin/dashboard/`)
   - Registration overview
   - Status management
   - Statistics

3. **Students** (`admin/students/`)
   - Registered students management
   - Student information editing
   - Payment status tracking
   - Course access management

4. **Payments** (`admin/payments/`)
   - Payment tracking interface
   - Printable payment reports
   - Payment status management

5. **Planning** (`admin/planning/`)
   - Course scheduling
   - Session management

6. **Courses** (`admin/courses/`)
   - Course creation and editing
   - Module management
   - Lesson management
   - Video upload

7. **Assignments** (`admin/assignments/`)
   - Assignment creation
   - Submission viewing
   - Grading interface

## 🎓 Student Portal

### Access

- **Login URL**: `/student/auth/login.php`
- **Dashboard**: `/student/dashboard/dashboard.php` (requires authentication)

### Features

- **Dashboard**: Overview of enrolled courses and progress
- **My Courses**: Browse all enrolled courses
- **Course Viewer**: Watch videos and read lesson content
- **Assignments**: Complete assignments and view results
- **Progress Tracking**: Monitor course completion and achievements

### Student Workflow

1. **Registration**: Student registers via public website
2. **Admin Approval**: Admin grants course access
3. **Password Setup**: Student sets up password (if not set)
4. **Login**: Student logs into portal
5. **Course Access**: View and complete courses
6. **Progress**: Track completion and achievements

## 📚 LMS Features

### Course Structure

- **Courses**: Top-level course containers
  - Title, description, level
  - Language from/to settings
  - Active/inactive status

- **Modules**: Course sections
  - Ordered modules within courses
  - Title and description

- **Lessons**: Individual learning units
  - Text content
  - Video integration
  - Sequential unlocking
  - Preview option (free lessons)

### Assignment System

- **Question Types**:
  - Multiple Choice (auto-graded)
  - Fill in the Blank (auto-graded)
  - Essay (manual grading)
  - File Upload (manual grading)

- **Features**:
  - Points system
  - Required/optional assignments
  - Automatic feedback for auto-graded questions
  - Manual grading interface for admins

### Progress Tracking

- **Lesson Completion**: Automatic tracking when videos are watched
- **Progress Percentage**: Calculated per course
- **Statistics**: Total points, completed lessons, overall progress
- **Achievement Tracking**: Points and completion records

## 🔒 Security

- **Prepared Statements**: All database queries use prepared statements
- **Password Hashing**: Admin and student passwords are hashed using secure methods
- **Session Management**: Secure session handling for admin and student access
- **CSRF Protection**: Form tokens for secure submissions
- **Input Validation**: Server-side validation for all forms
- **Access Control**: Role-based access (admin vs student)
- **File Upload Security**: Type validation and size limits for video uploads

## 📚 Documentation

Comprehensive documentation is available in the `Guide/` directory:

### Getting Started
- **[Guide/README.md](Guide/README.md)** - Documentation index and navigation
- **[LMS-Quick-Start.md](Guide/LMS-Quick-Start.md)** - Quick start guide for LMS
- **[LMS-Windows-Setup.md](Guide/LMS-Windows-Setup.md)** - Windows setup guide

### User Guides
- **[LMS-User-Guide.md](Guide/LMS-User-Guide.md)** - Complete LMS user guide (Dutch)
- **[Registered-Students-Guide.md](Guide/Registered-Students-Guide.md)** - Registered students management guide
- **[Registered-Students-Quick-Setup.md](Guide/Registered-Students-Quick-Setup.md)** - Quick setup guide

### Technical Documentation
- **[FEATURE-OVERVIEW.md](Guide/FEATURE-OVERVIEW.md)** - Visual feature overview
- **[IMPLEMENTATION-SUMMARY.md](Guide/IMPLEMENTATION-SUMMARY.md)** - Technical implementation details
- **[Security-Architecture-Guide.md](Guide/Security-Architecture-Guide.md)** - Security architecture overview

### Setup & Deployment
- **[GitHub-Setup-Guide.md](Guide/GitHub-Setup-Guide.md)** - GitHub setup instructions
- **[GitHub-Update-Guide.md](Guide/GitHub-Update-Guide.md)** - GitHub update instructions
- **[Strato-Setup.md](Guide/Strato-Setup.md)** - Strato hosting setup
- **[Strato-VPS-Windows.md](Guide/Strato-VPS-Windows.md)** - Strato VPS Windows setup

### Troubleshooting
- **[LMS-Troubleshooting.md](Guide/LMS-Troubleshooting.md)** - LMS troubleshooting guide
- **[PHP-Upload-Fix.md](Guide/PHP-Upload-Fix.md)** - Upload troubleshooting
- **[CSRF-Testing-Guide.md](Guide/CSRF-Testing-Guide.md)** - CSRF testing guide

## 🌐 Website Pages

### Public Pages

- **Home** (`index.php`) - Main landing page with course overview
- **About** (`pages/about.php`) - About the school
- **Contact** (`pages/contact.php`) - Contact form
- **Register** (`pages/register.php`) - Course registration form
- **Courses**:
  - English to Dutch (`pages/cursus-engels-nederlands.php`)
  - Russian to Dutch (`pages/cursus-russisch-nederlands.php`)

### Success Pages

- **Contact Success** (`pages/contact_success.php`)
- **Registration Success** (`pages/register_success.php`)

## 📧 Email System

The website uses PHPMailer for sending automated emails:
- Registration confirmations
- Contact form submissions
- Admin notifications

Configure SMTP settings in `includes/config.php`.

## 🐛 Debugging

Debug utilities are available in `admin/debug/`:
- `fix_password.php` - Password recovery tool
- `server_diagnostic.php` - Server diagnostics
- `upload_test.php` - Upload testing

**Note**: Remove or secure debug tools in production environments.

## 🔄 Version History

- **v2.0** - Added LMS (Learning Management System) with courses, modules, lessons, assignments, and student portal
- **v1.5** - Added Registered Students Management System with payment tracking
- **v1.0** - Initial release with course registration and admin dashboard

## 📝 License

This project is proprietary software for NT2 Taalles International.

## 🆘 Support

For support and questions:
- **Email**: Info@nt2taallesinternational.com
- **Website**: nt2taallesinternational.com
- **Documentation**: See `Guide/` directory for detailed guides

---

**Last Updated**: December 2024  
**Version**: 2.0  
**Status**: Production Ready
