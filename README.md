# NT2 Taalles International Website

A comprehensive PHP-based website for NT2 (Dutch as a Second Language) course registration and management system.

## 📋 Table of Contents

- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [Project Structure](#-project-structure)
- [Setup Instructions](#️-setup-instructions)
- [Configuration](#-configuration)
- [Admin Panel](#-admin-panel)
- [Documentation](#-documentation)
- [Support](#-support)

## 🚀 Features

- **Responsive Design**: Mobile-friendly layout with modern CSS
- **Contact Form**: Secure form processing with server-side validation
- **Course Registration**: Student registration system for Dutch language courses
- **Admin Dashboard**: Management interface for viewing and managing registrations
- **Registered Students Management**: Complete student lifecycle management with start/end dates, payment tracking, and personal information
- **Payment Tracking**: Monitor student payments with status indicators and printable reports
- **Course Planning**: Schedule and manage course sessions
- **Email Notifications**: Automated email system using PHPMailer
- **Database Integration**: MySQL database with prepared statements
- **Multi-language Support**: English to Dutch and Russian to Dutch course pages

## 🛠 Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Email**: PHPMailer 6.8+
- **Web Server**: IIS (with web.config)
- **Frontend**: HTML5, CSS3, JavaScript

## 📁 Project Structure

```
NT2TaallesInternational/
├── index.php                              # Main homepage
├── web.config                             # IIS server configuration
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
│   │   └── registered_students.php       # Registered students management
│   │
│   ├── payments/                         # Payment management module
│   │   ├── pending_payments.php         # Payment tracking interface
│   │   └── print_pending_payments.php   # Printable payment reports
│   │
│   ├── planning/                         # Course planning module
│   │   └── planning.php                 # Course scheduling interface
│   │
│   └── debug/                            # Debug utilities
│       ├── fix_password.php              # Password recovery tool
│       └── planning_fixed.php           # Planning fix utility
│
├── assets/                               # Static files
│   ├── css/                             # Stylesheets
│   │   ├── style.css                    # Main styles
│   │   ├── contact.css                  # Contact page styles
│   │   ├── course.css                   # Course page styles
│   │   └── about.css                    # About page styles
│   │
│   ├── img/                             # Images
│   │   └── LOGO.png                     # Site logo
│   │
│   └── Video/                           # Video assets
│
├── config/                               # Configuration directory
│   └── (configuration files)
│
├── database/                             # Database utilities
│   ├── setup_database.php               # Database setup utility
│   ├── update_database.php              # Database update utility
│   └── update_schema.sql                # Database schema updates
│
├── handlers/                             # Form processors
│   ├── submit_contact.php               # Contact form handler
│   └── submit_registration.php          # Registration form handler
│
├── includes/                             # Shared PHP files
│   ├── config.php                       # Main configuration loader
│   ├── db_connect.php                   # Database connection
│   ├── functions.php                    # Utility functions
│   ├── header.php                       # Site header
│   ├── footer.php                       # Site footer
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
    ├── GitHub-Setup-Guide.md           # GitHub setup instructions
    ├── GitHub-Update-Guide.md          # GitHub update instructions
    ├── Registered-Students-Quick-Setup.md # Quick setup guide
    ├── Registered-Students-Guide.md    # Complete user guide
    ├── FEATURE-OVERVIEW.md             # Visual feature guide
    └── IMPLEMENTATION-SUMMARY.md       # Technical documentation
```

## 🛠️ Setup Instructions

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- IIS web server
- Composer (for PHPMailer dependencies, if needed)

### 1. Database Setup

1. **Create the database**:
   ```bash
   php database/setup_database.php
   ```

2. **Update the database** (if needed):
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

### 3. Web Server Configuration

1. **IIS Setup**:
   - Ensure PHP is installed and configured
   - Set document root to project directory
   - The `web.config` file is already configured for URL rewriting

2. **File Permissions**:
   - Ensure web server has read access to all files
   - Write access may be needed for logs (if applicable)

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
- Additional tables for registered students, payments, and planning

Run `database/setup_database.php` to initialize the database schema.

## 👨‍💼 Admin Panel

### Access

- **Login URL**: `/admin/auth/index.php`
- **Dashboard**: `/admin/dashboard/dashboard.php` (requires authentication)

### Features

- **Dashboard**: View and manage all course registrations
- **Registered Students**: Manage enrolled students with full details
- **Payments**: Track and manage student payments
- **Planning**: Schedule and manage course sessions
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

4. **Payments** (`admin/payments/`)
   - Payment tracking interface
   - Printable payment reports
   - Payment status management

5. **Planning** (`admin/planning/`)
   - Course scheduling
   - Session management

## 📚 Documentation

Comprehensive documentation is available in the `Guide/` directory:

- **[Guide/README.md](Guide/README.md)** - Documentation index and navigation
- **[GitHub-Setup-Guide.md](Guide/GitHub-Setup-Guide.md)** - GitHub setup instructions
- **[GitHub-Update-Guide.md](Guide/GitHub-Update-Guide.md)** - GitHub update instructions
- **[Registered-Students-Quick-Setup.md](Guide/Registered-Students-Quick-Setup.md)** - Quick setup guide for registered students feature
- **[Registered-Students-Guide.md](Guide/Registered-Students-Guide.md)** - Complete user guide
- **[FEATURE-OVERVIEW.md](Guide/FEATURE-OVERVIEW.md)** - Visual feature overview
- **[IMPLEMENTATION-SUMMARY.md](Guide/IMPLEMENTATION-SUMMARY.md)** - Technical implementation details

## 🔒 Security

- **Prepared Statements**: All database queries use prepared statements
- **Password Hashing**: Admin passwords are hashed using SHA2
- **Session Management**: Secure session handling for admin access
- **Input Validation**: Server-side validation for all forms
- **CSRF Protection**: Form tokens for secure submissions

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
- `planning_fixed.php` - Planning fix utility

**Note**: Remove or secure debug tools in production environments.

## 📝 License

This project is proprietary software for NT2 Taalles International.

## 🆘 Support

For support and questions:
- **Email**: Info@nt2taallesinternational.com
- **Website**: nt2taallesinternational.com
- **Documentation**: See `Guide/` directory for detailed guides

---

**Last Updated**: 2024
**Version**: 1.0
