# NT2 Taalles International Website

A comprehensive PHP-based website for NT2 (Dutch as a Second Language) course registration and management system.

## 📋 Table of Contents

- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [Project Structure](#-project-structure)
- [Setup Instructions](#️-setup-instructions)
- [Configuration](#-configuration)
- [Security](#-security)
- [Admin Panel](#-admin-panel)
- [Support](#-support)

## 🚀 Features

- **Responsive Design**: Mobile-friendly layout with modern CSS
- **Contact Form**: Secure form processing with server-side validation
- **Course Registration**: Student registration system for Dutch language courses
- **Admin Dashboard**: Management interface for viewing and managing registrations
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
├── index.php                           # Main homepage
├── web.config                          # IIS server configuration
│
├── admin/                              # Admin panel
│   ├── index.php                      # Admin login page
│   ├── dashboard.php                  # Main admin interface
│   ├── change_password.php            # Password management
│   ├── logout.php                     # Admin logout
│   └── fix_password.php               # Password recovery
│
├── assets/                             # Static files
│   ├── css/                           # Stylesheets
│   │   ├── style.css                  # Main styles
│   │   ├── contact.css                # Contact page styles
│   │   ├── course.css                 # Course page styles
│   │   └── about.css                  # About page styles
│   └── img/                           # Images
│       └── LOGO.png                   # Site logo
│
├── config/                             # Configuration files
│   └── .env                           # Environment variables (sensitive data)
│
├── database/                           # Database utilities
│   ├── database_setup.sql             # Database creation script
│   └── setup_database.php             # Database setup utility
│
├── handlers/                           # Form processors
│   ├── submit_contact.php             # Contact form handler
│   └── submit_registration.php        # Registration form handler
│
├── includes/                           # Shared PHP files
│   ├── config.php                     # Main configuration loader
│   ├── db_connect.php                 # Database connection
│   ├── functions.php                   # Utility functions
│   ├── header.php                     # Site header
│   ├── footer.php                     # Site footer
│   ├── email_template.php             # Email templates
│   └── PHPMailer/                     # Email library
│       └── src/                       # PHPMailer source files
│
└── pages/                              # Content pages
    ├── about.php                      # About page
    ├── contact.php                    # Contact form
    ├── contact_success.php            # Contact success page
    ├── cursus-engels-nederlands.php  # English to Dutch course
    ├── cursus-russisch-nederlands.php # Russian to Dutch course
    ├── register.php                   # Course registration form
    └── register_success.php           # Registration confirmation page
```

## 🛠️ Setup Instructions

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- IIS web server
- Composer (for PHPMailer dependencies)

### 1. Database Setup

1. Create a MySQL database named `nt2_db`
2. Run the database setup script:
   ```sql
   -- Execute database/database_setup.sql
   ```
3. Or use the setup utility:
   ```bash
   php database/setup_database.php
   ```

### 2. Environment Configuration

1. **Create environment file**:
   ```bash
   # Copy the template and configure
   cp config/.env.example config/.env
   ```

2. **Configure your `.env` file**:
   ```env
   # Database Configuration
   DB_HOST=localhost
   DB_USER=your_username
   DB_PASS=your_password
   DB_NAME=nt2_db

   # SMTP Configuration
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=587
   SMTP_USERNAME=your_email@gmail.com
   SMTP_PASSWORD=your_app_password
   SMTP_FROM_EMAIL=your_email@gmail.com
   SMTP_FROM_NAME=NT2 Taalles International

   # Admin Configuration
   ADMIN_EMAIL=admin@yourdomain.com

   # Website Configuration
   WEBSITE_URL=https://yourdomain.com

   # SSL Settings (for local development)
   SMTP_SSL_VERIFY=false
   SMTP_DEBUG=false
   ```

### 3. Web Server Configuration

1. **IIS Setup**:
   - Ensure PHP is installed and configured
   - Set document root to project directory
   - Configure URL rewriting if needed

2. **File Permissions**:
   - Ensure web server has read access to all files
   - Write access to logs directory (if applicable)

### 4. Email Configuration

1. **Gmail SMTP Setup**:
   - Enable 2-factor authentication
   - Generate an App Password
   - Use the App Password in your `.env` file

2. **Other SMTP Providers**:
   - Update SMTP_HOST, SMTP_PORT, and credentials accordingly

## 🔧 Configuration

### Environment Variables

The application uses environment variables stored in `config/.env` for sensitive configuration:

| Variable | Description | Example |
|----------|-------------|---------|
| `DB_HOST` | Database host | `localhost` |
| `DB_USER` | Database username | `root` |
| `DB_PASS` | Database password | `your_password` |
| `DB_NAME` | Database name | `nt2_db` |
| `SMTP_HOST` | SMTP server | `smtp.gmail.com` |
| `SMTP_USERNAME` | Email username | `your_email@gmail.com` |
| `SMTP_PASSWORD` | Email password | `your_app_password` |
| `ADMIN_EMAIL` | Admin contact email | `admin@yourdomain.com` |
| `WEBSITE_URL` | Website URL | `https://yourdomain.com` |

### Configuration Loading

The main configuration is loaded through `includes/config.php`, which reads from the `.env` file:

```php
// Example configuration loading
require_once 'config.php';
$db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
```

## 🔒 Security

### Security Features

- **Input Validation**: All form inputs are sanitized and validated
- **Prepared Statements**: Database queries use prepared statements to prevent SQL injection
- **Environment Variables**: Sensitive data stored in `.env` file (not in version control)
- **Admin Authentication**: Admin panel requires secure login
- **CSRF Protection**: Form submissions include security tokens

### Security Best Practices

1. **Never commit `.env` file to version control**
2. **Use strong passwords for database and admin accounts**
3. **Keep PHP and MySQL updated**
4. **Regular security audits of form inputs**
5. **Monitor admin access logs**

## 👨‍💼 Admin Panel

### Access

- Navigate to `/admin/` to access the admin panel
- Default login credentials should be set during initial setup
- Change default password immediately after first login

### Features

- **Dashboard**: Overview of registrations and system status
- **Registration Management**: View and manage student registrations
- **Password Management**: Change admin password
- **System Logs**: Monitor system activity

### Admin Functions

- View all course registrations
- Export registration data
- Manage system settings
- Monitor email notifications

## 📞 Support

### Technical Support

For technical support or questions about the website:

- **Email**: Contact the development team
- **Documentation**: Refer to this README and inline code comments
- **Issues**: Report bugs or feature requests through the development team

### Development

- **Code Structure**: Follow PSR-4 autoloading standards
- **Database**: Use prepared statements for all queries
- **Security**: Validate all user inputs
- **Testing**: Test all forms and admin functions before deployment

---

**Version**: 2.0  
**Last Updated**: 2024  
**License**: Private - NT2 Taalles International# NT2Taallesinternational
