# LexiAid - Local Development Guide

**LexiAid** is now configured for local development without Docker. This guide will help you set up and run the application on your local machine.

## Prerequisites

- **PHP 7.4+** with extensions: mysqli, pdo, pdo_mysql, mbstring, gd, zip
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Python 3.8+**
- **Web Server** (Apache, Nginx, or PHP built-in server)

## Quick Setup

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd LexiAid
   ```

2. **Run the setup script:**
   ```bash
   ./setup.sh
   ```

3. **Configure environment:**
   - Edit `site/config/.env` with your database credentials
   - Default settings use `lexiaid_user`/`password` for database access

4. **Start your web server:**
   ```bash
   # Option 1: Use PHP built-in server (for development)
   cd site
   php -S localhost:8000
   
   # Option 2: Use Apache/Nginx
   # Point document root to the 'site' directory
   ```

5. **Access the application:**
   - Main application: http://localhost:8000/index.html
   - Test dashboard: http://localhost:8000/test-dashboard.html
   - Diagnostic page: http://localhost:8000/diagnostic.php

## Manual Setup

If you prefer manual setup or the script doesn't work:

### 1. Database Setup
```bash
# Create database and user
mysql -u root -p
CREATE DATABASE lexiaid CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'lexiaid_user'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON lexiaid.* TO 'lexiaid_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
mysql -u lexiaid_user -p lexiaid < site/config/database.sql
```

### 2. Python Environment
```bash
cd python
python3 -m venv venv
source venv/bin/activate  # Linux/macOS
# OR: venv\Scripts\activate  # Windows
pip install -r requirements.txt
```

### 3. PHP Configuration
Ensure these extensions are enabled in `php.ini`:
- mysqli
- pdo
- pdo_mysql
- mbstring
- gd
- zip

## Directory Structure

```
LexiAid/
├── site/                    # Web application root
│   ├── config/             # Configuration files
│   │   ├── .env           # Environment variables
│   │   ├── database.php   # Database connection
│   │   └── database.sql   # Database schema
│   ├── *.html             # Frontend pages
│   ├── *.php              # API endpoints
│   ├── css/               # Stylesheets
│   └── js/                # JavaScript files
├── python/                 # NLP/AI backend
│   ├── venv/              # Python virtual environment
│   ├── requirements.txt   # Python dependencies
│   └── *.py              # Python scripts
└── setup.sh              # Automated setup script
```

## Testing

1. **Visit the diagnostic page:**
   ```
   http://localhost:8000/diagnostic.php
   ```

2. **Run the test dashboard:**
   ```
   http://localhost:8000/test-dashboard.html
   ```

3. **Test individual components:**
   - Database connection
   - Python script execution
   - Search functionality
   - File upload

## Troubleshooting

### Common Issues

1. **Database connection fails:**
   - Check MySQL is running: `systemctl status mysql`
   - Verify credentials in `site/config/.env`
   - Check user permissions

2. **Python scripts not working:**
   - Ensure virtual environment is activated
   - Check Python path in search.php
   - Install missing dependencies: `pip install -r requirements.txt`

3. **Permission errors:**
   - Check file permissions on site directory
   - Ensure web server can read/write to necessary directories

### Log Files

- **PHP errors:** Check your web server error log
- **Python errors:** `python/search.log`
- **Application logs:** `site/logs/`

## Development Notes

- This is the original non-Docker version of LexiAid
- All Docker-related files have been removed
- The application uses local database connections
- Python scripts run via shell execution from PHP
- Environment variables are loaded from `.env` file

## Security Notes

For production deployment:
1. Change default database credentials
2. Set `APP_ENV=production` in `.env`
3. Review and update admin credentials
4. Enable HTTPS
5. Configure proper file permissions
6. Review all security settings in documentation
