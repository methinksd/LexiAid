# LexiAid Setup Guide

LexiAid is a comprehensive legal research and study assistant platform that combines semantic search, case brief generation, and task management. This guide provides step-by-step instructions for setting up LexiAid on both Windows and Linux systems.

## 📋 System Requirements

### Minimum Requirements
- **Operating System**: Windows 10+ or Linux (Ubuntu 18.04+, CentOS 7+, or equivalent)
- **RAM**: 4GB minimum, 8GB recommended
- **Storage**: 2GB free space minimum (additional space for ML models)
- **Internet**: Required for initial ML model download

### Software Dependencies
- **PHP**: 7.4+ with extensions (json, pdo, mysqli)
- **Apache**: 2.4+
- **MySQL**: 5.7+ or MariaDB 10.3+
- **Python**: 3.8+
- **Git**: For version control and cloning

---

## 🖥️ Windows Setup

### Step 1: Install XAMPP
1. **Download XAMPP**:
   - Visit https://www.apachefriends.org/download.html
   - Download the latest version for Windows (PHP 7.4+)

2. **Install XAMPP**:
   - Run the installer as Administrator
   - Choose installation directory (default: `C:\xampp`)
   - Select components: Apache, MySQL, PHP, phpMyAdmin

3. **Start Services**:
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL** services
   - Verify installation by visiting http://localhost

### Step 2: Install Python
1. **Download Python**:
   - Visit https://www.python.org/downloads/
   - Download Python 3.8+ for Windows

2. **Install Python**:
   - Run installer and check "Add Python to PATH"
   - Verify installation: Open Command Prompt and run `python --version`

### Step 3: Install Git (Optional but Recommended)
1. Download from https://git-scm.com/download/win
2. Install with default settings
3. Verify: `git --version` in Command Prompt

### Step 4: Setup LexiAid Project
```cmd
# Navigate to XAMPP htdocs directory
cd C:\xampp\htdocs

# Clone the project (if using Git)
git clone <repository-url> lexiaid
# OR copy the LexiAid folder manually to C:\xampp\htdocs\lexiaid

# Navigate to project directory
cd lexiaid

# Create Python virtual environment
python -m venv .venv

# Activate virtual environment
.venv\Scripts\activate

# Upgrade pip
python -m pip install --upgrade pip

# Install Python dependencies
pip install -r python/requirements.txt
```

---

## 🐧 Linux Setup

### Step 1: Install LAMP Stack

#### Ubuntu/Debian:
```bash
# Update package list
sudo apt update

# Install Apache, MySQL, PHP and required extensions
sudo apt install apache2 mysql-server php php-mysqli php-json php-pdo libapache2-mod-php

# Install additional PHP extensions
sudo apt install php-curl php-gd php-mbstring php-xml php-zip

# Install phpMyAdmin (optional but recommended)
sudo apt install phpmyadmin

# Enable Apache modules
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### CentOS/RHEL/Fedora:
```bash
# Install EPEL repository (CentOS/RHEL)
sudo yum install epel-release  # CentOS 7
# OR
sudo dnf install epel-release  # CentOS 8/Fedora

# Install Apache, MySQL/MariaDB, PHP
sudo yum install httpd mariadb-server php php-mysqli php-json php-pdo
# OR for newer versions:
sudo dnf install httpd mariadb-server php php-mysqli php-json php-pdo

# Start and enable services
sudo systemctl start httpd mariadb
sudo systemctl enable httpd mariadb
```

### Step 2: Install Python 3.8+
#### Ubuntu/Debian:
```bash
# Install Python 3 and pip
sudo apt install python3 python3-pip python3-venv

# Verify installation
python3 --version
```

#### CentOS/RHEL/Fedora:
```bash
# Install Python 3 and pip
sudo yum install python3 python3-pip  # CentOS 7
# OR
sudo dnf install python3 python3-pip  # CentOS 8/Fedora

# Verify installation
python3 --version
```

### Step 3: Configure MySQL/MariaDB
```bash
# Run security installation
sudo mysql_secure_installation

# Log into MySQL
sudo mysql -u root -p

# Create database user (optional, for security)
CREATE USER 'lexiaid_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON *.* TO 'lexiaid_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 4: Setup LexiAid Project
```bash
# Navigate to web directory
cd /var/www/html

# Clone the project (requires appropriate permissions)
sudo git clone <repository-url> lexiaid
# OR copy the LexiAid folder manually

# Set proper ownership and permissions
sudo chown -R www-data:www-data lexiaid/
sudo chmod -R 755 lexiaid/

# Navigate to project directory
cd lexiaid

# Create Python virtual environment
python3 -m venv .venv

# Activate virtual environment
source .venv/bin/activate

# Upgrade pip
pip install --upgrade pip

# Install Python dependencies
pip install -r python/requirements.txt
```

---

## 🗄️ Database Configuration

### Step 1: Create Database
#### Using phpMyAdmin (All Platforms):
1. Open phpMyAdmin:
   - **Windows (XAMPP)**: http://localhost/phpmyadmin
   - **Linux**: http://localhost/phpmyadmin (if installed)

2. Create database:
   - Click "New" in the left sidebar
   - Database name: `lexiaid_db`
   - Collation: `utf8mb4_general_ci`
   - Click "Create"

3. Import schema:
   - Select the `lexiaid_db` database
   - Go to "Import" tab
   - Choose file: `site/config/database.sql`
   - Click "Go"

#### Using MySQL Command Line:
```bash
# Log into MySQL
mysql -u root -p

# Create database
CREATE DATABASE lexiaid_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

# Import schema
USE lexiaid_db;
SOURCE /path/to/lexiaid/site/config/database.sql;
EXIT;
```

### Step 2: Configure Database Connection
Edit `site/config/database.php`:

#### For Default XAMPP/Local Setup:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');         // Empty for XAMPP default
define('DB_NAME', 'lexiaid_db');
```

#### For Production/Secure Setup:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'lexiaid_user');
define('DB_PASS', 'your_secure_password');
define('DB_NAME', 'lexiaid_db');
```

---

## ⚙️ Final Configuration

### Step 1: Set File Permissions (Linux Only)
```bash
# Make sure web server can read/write necessary files
sudo chown -R www-data:www-data /var/www/html/lexiaid/
sudo chmod -R 755 /var/www/html/lexiaid/
sudo chmod -R 777 /var/www/html/lexiaid/site/logs/  # For log files
```

### Step 2: Configure Virtual Environment Detection
The system automatically detects Python virtual environments. Ensure the `.venv` directory is in your project root:

#### Windows Structure:
```
C:\xampp\htdocs\lexiaid\
├── .venv\
│   └── Scripts\
│       └── python.exe
└── python\
    └── requirements.txt
```

#### Linux Structure:
```
/var/www/html/lexiaid/
├── .venv/
│   └── bin/
│       └── python
└── python/
    └── requirements.txt
```

### Step 3: Test Installation

#### Access LexiAid:
- **Windows (XAMPP)**: http://localhost/lexiaid/site/
- **Linux**: http://localhost/lexiaid/site/

#### Run Comprehensive Tests:
1. **Test Dashboard**: http://localhost/lexiaid/site/test-dashboard.html
   - This runs automated tests for all components
   - Check that all tests pass ✅

2. **Manual Testing**:
   - **Search**: Try queries like "constitutional law", "criminal rights"
   - **Case Upload**: Upload sample legal documents
   - **Task Management**: Create and manage study tasks
   - **Insights**: View analytics dashboard

---

## 🔧 Advanced Configuration

## 🔧 Advanced Configuration

### Apache Virtual Host (Optional)
For a custom domain or better organization:

#### Windows (XAMPP):
1. Edit `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/lexiaid/site"
    ServerName lexiaid.local
    ServerAlias www.lexiaid.local
</VirtualHost>
```

2. Edit `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 lexiaid.local
```

#### Linux:
1. Create virtual host file:
```bash
sudo nano /etc/apache2/sites-available/lexiaid.conf
```

2. Add configuration:
```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html/lexiaid/site
    ServerName lexiaid.local
    ErrorLog ${APACHE_LOG_DIR}/lexiaid_error.log
    CustomLog ${APACHE_LOG_DIR}/lexiaid_access.log combined
</VirtualHost>
```

3. Enable site:
```bash
sudo a2ensite lexiaid.conf
sudo systemctl reload apache2
```

4. Add to hosts file:
```bash
echo "127.0.0.1 lexiaid.local" | sudo tee -a /etc/hosts
```

### Performance Optimization

#### Python Model Caching:
The first search may take 2-5 minutes as ML models download. To pre-download:

```bash
# Activate virtual environment
# Windows:
.venv\Scripts\activate
# Linux:
source .venv/bin/activate

# Pre-download models
python -c "from sentence_transformers import SentenceTransformer; SentenceTransformer('all-MiniLM-L6-v2')"
```

#### PHP Configuration:
Edit your `php.ini` file for better performance:
```ini
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
```

---

## 🔍 Troubleshooting

### Platform-Specific Issues

#### Windows-Specific:
- **Python not found**: Ensure Python is added to PATH during installation
- **Permission errors**: Run Command Prompt as Administrator
- **Port conflicts**: XAMPP ports may conflict with IIS or Skype
  - Change Apache port in XAMPP Control Panel → Config → httpd.conf

#### Linux-Specific:
- **Permission denied**: Use `sudo` for file operations in `/var/www/html/`
- **Service not starting**: Check status with `systemctl status apache2 mysql`
- **SELinux issues** (RHEL/CentOS): May need to configure SELinux policies
  ```bash
  sudo setsebool -P httpd_can_network_connect 1
  sudo setsebool -P httpd_execmem 1
  ```

### Common Issues (All Platforms)

### Common Issues (All Platforms)

**Search not working:**
- Check Python virtual environment is activated
- Verify `sentence-transformers` is installed: `pip list | grep sentence`
- Review logs in `site/logs/search.log`
- Ensure internet connection for initial model download
- System will fallback to keyword search if ML search fails

**Database connection errors:**
- **Windows**: Ensure MySQL is running in XAMPP Control Panel
- **Linux**: Check service status: `sudo systemctl status mysql`
- Verify database credentials in `site/config/database.php`
- Confirm `lexiaid_db` database exists
- Test connection: `mysql -u root -p -e "SHOW DATABASES;"`

**Python virtual environment issues:**
- **Windows**: Ensure `.venv\Scripts\python.exe` exists
- **Linux**: Ensure `.venv/bin/python` exists
- Recreate if needed: Delete `.venv` folder and run setup again
- Check Python version: Should be 3.8+

**Permission errors:**
- **Windows**: Run Command Prompt as Administrator
- **Linux**: Use `sudo` for system directories, check file ownership
- Ensure web server can read project files

**Port already in use:**
- **Windows**: Check XAMPP for port conflicts (usually 80, 443, 3306)
- **Linux**: Check with `sudo netstat -tulpn | grep :80`
- Change ports in Apache/MySQL configuration if needed

**Model download timeout:**
- First search may take 2-5 minutes for model download
- Ensure stable internet connection
- Models are cached after first download
- Check available disk space (models ~500MB)

### Log Files and Debugging

#### Important Log Locations:
- **Search logs**: `site/logs/search.log`
- **Apache logs (Windows)**: `C:\xampp\apache\logs\error.log`
- **Apache logs (Linux)**: `/var/log/apache2/error.log`
- **MySQL logs (Linux)**: `/var/log/mysql/error.log`
- **PHP errors**: Enable in `php.ini` or check Apache error logs

#### Enable Debug Mode:
1. Set debug mode in PHP files (check individual PHP files for debug flags)
2. Monitor logs in real-time:
   ```bash
   # Linux
   tail -f /var/log/apache2/error.log
   tail -f /var/www/html/lexiaid/site/logs/search.log
   
   # Windows (PowerShell)
   Get-Content C:\xampp\apache\logs\error.log -Wait
   ```

---

## 🧪 Testing & Validation

### Automated Test Suite
Access the comprehensive test dashboard:
```
http://localhost/lexiaid/site/test-dashboard.html
```

The test dashboard checks:
- ✅ Database connectivity
- ✅ Python environment setup
- ✅ ML model availability
- ✅ Search functionality (semantic + fallback)
- ✅ File upload capabilities
- ✅ API endpoints

### Manual Testing Checklist

#### Core Features:
- [ ] **Search**: Test with queries like "constitutional law", "criminal procedure"
- [ ] **Case Upload**: Upload PDF legal documents
- [ ] **Case Brief Generation**: Generate automated summaries
- [ ] **Task Management**: Create, edit, delete study tasks
- [ ] **Quiz System**: Take practice quizzes
- [ ] **Insights Dashboard**: View study analytics

#### UI/UX Testing:
- [ ] **Responsive Design**: Test on mobile, tablet, desktop
- [ ] **Navigation**: All menu items work correctly
- [ ] **Forms**: Submit forms without errors
- [ ] **Error Handling**: Graceful error messages

### Sample Test Data
The system includes pre-loaded sample cases in `python/legal_documents.json`:
- Miranda v. Arizona (Criminal Law)
- Brown v. Board of Education (Constitutional Law)
- Gideon v. Wainwright (Criminal Procedure)
- Mapp v. Ohio (Fourth Amendment)
- Terry v. Ohio (Search and Seizure)

Use these for testing search and brief generation features.

---

## 🚀 Production Deployment

### Security Considerations
1. **Change default passwords**: Update MySQL root password and create dedicated user
2. **Update database.php**: Use secure credentials, not root user
3. **File permissions**: Restrict write permissions to necessary directories only
4. **PHP security**: Disable dangerous functions, enable security headers
5. **HTTPS**: Configure SSL certificates for production
6. **Firewall**: Configure appropriate firewall rules

### Performance Optimization
1. **Pre-download ML models**: Run initial search to cache models
2. **Enable PHP OPcache**: Improves PHP performance significantly
3. **Database optimization**: Index frequently queried fields
4. **Caching**: Implement Redis or Memcached for session storage
5. **CDN**: Use CDN for static assets (CSS, JS, images)

### Monitoring
- Set up log rotation for application logs
- Monitor disk space (ML models can be large)
- Track database performance
- Monitor Python process memory usage

---

## ✅ Success Criteria

**Setup is complete when:**
- [ ] All test dashboard checks pass ✅
- [ ] Search returns relevant results for legal queries ✅
- [ ] Database operations (CRUD) work correctly ✅
- [ ] Python scripts execute without errors ✅
- [ ] UI is responsive across devices ✅
- [ ] File uploads process successfully ✅
- [ ] Case brief generation works ✅

## 📞 Support & Resources

### Documentation
- **Project Documentation**: `documentation.txt`
- **API Reference**: Check individual PHP files for endpoint documentation
- **Testing Reports**: `TESTING_REPORT.md` and `FINAL_TESTING_REPORT.md`

### Getting Help
1. **Check logs**: Always check error logs first
2. **Test dashboard**: Use automated tests to identify issues
3. **Verify dependencies**: Ensure all required software is installed
4. **Check permissions**: Verify file and directory permissions
5. **Review configuration**: Double-check database and Python settings

### System Requirements Reminder
- **Storage**: Ensure 2GB+ free space for ML models
- **Memory**: 4GB RAM minimum, 8GB recommended for better performance
- **Network**: Internet required for initial ML model download
- **Browser**: Modern browser with JavaScript enabled

---

## 📝 Quick Reference Commands

### Windows Commands:
```cmd
# Start XAMPP services
# Use XAMPP Control Panel

# Activate Python environment
.venv\Scripts\activate

# Check Python packages
pip list

# View logs
type C:\xampp\apache\logs\error.log
```

### Linux Commands:
```bash
# Start/stop services
sudo systemctl start apache2 mysql
sudo systemctl stop apache2 mysql

# Activate Python environment
source .venv/bin/activate

# Check service status
sudo systemctl status apache2 mysql

# View logs
sudo tail -f /var/log/apache2/error.log
sudo tail -f /var/www/html/lexiaid/site/logs/search.log

# File permissions
sudo chown -R www-data:www-data /var/www/html/lexiaid/
sudo chmod -R 755 /var/www/html/lexiaid/
```

---

*Last updated: July 2025*
*For the most current setup instructions, check the project repository.*
