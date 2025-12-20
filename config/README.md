# Environment Configuration

This directory contains environment variable configuration files.

## Setup Instructions

1. **Create `.env` file**: Copy `.env.example` to `.env` in this directory:
   ```
   cp config/.env.example config/.env
   ```
   Or manually create `config/.env` with your actual credentials.

2. **Configure your credentials**: Edit `config/.env` and replace the placeholder values with your actual:
   - Database credentials (DB_HOST, DB_USER, DB_PASS, DB_NAME)
   - SMTP credentials (SMTP_USERNAME, SMTP_PASSWORD, etc.)
   - Other configuration values

3. **Security Note**: The `.env` file is already in `.gitignore` and will NOT be committed to version control. Never commit actual credentials.

## Example `.env` file structure:

```
DB_HOST=localhost
DB_USER=root
DB_PASS=your_actual_password
DB_NAME=nt2_db

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
SMTP_FROM_EMAIL=your_email@gmail.com
SMTP_FROM_NAME=NT2 Taalles International

ADMIN_EMAIL=info@nt2taallesinternational.com
WEBSITE_URL=https://nt2taallesinternational.com

SMTP_SSL_VERIFY=false
SMTP_DEBUG=false
```

## Production Deployment

When deploying to production:
1. Set `SMTP_SSL_VERIFY=true` for secure email connections
2. Ensure HTTPS is enabled and uncomment `enforceHttps()` in `includes/https_enforcer.php`
3. Use strong, unique passwords for all credentials
4. Never commit the `.env` file to version control

