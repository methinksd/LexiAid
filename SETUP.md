# LexiAid Setup Guide

## 🚀 Quick Start

### Prerequisites
- **XAMPP** or **WAMP** (includes PHP, Apache, MySQL)
- **Python 3.8+** 
- **Git** (for cloning)

### 1. Install XAMPP
1. Download from https://www.apachefriends.org/download.html
2. Install and start Apache and MySQL services
3. Verify by visiting http://localhost

### 2. Setup Project
```bash
# Clone or copy LexiAid files to XAMPP htdocs
# For example: C:\xampp\htdocs\lexiaid\

# Navigate to project directory
cd /path/to/lexiaid

# Create Python virtual environment
python -m venv .venv

# Activate virtual environment
# Windows:
.venv\Scripts\activate
# macOS/Linux:
source .venv/bin/activate

# Install Python dependencies
pip install -r python/requirements.txt
```

### 3. Configure Database
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create database `lexiaid_db`
3. Import schema: `site/config/database.sql`
4. Update database credentials in `site/config/database.php` if needed

### 4. Test Installation
1. Visit http://localhost/lexiaid/site/
2. Run test dashboard: http://localhost/lexiaid/site/test-dashboard.html
3. Test search functionality

## 🔧 Configuration

### Database Settings
Edit `site/config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');         // Your MySQL password
define('DB_NAME', 'lexiaid_db');
```

### Python Environment
The system automatically detects and uses the virtual environment. If issues occur:
1. Ensure `.venv/Scripts/python.exe` exists (Windows)
2. Verify packages are installed: `pip list`
3. Check logs in `site/logs/search.log`

## 🧪 Testing

### Automated Tests
Use the test dashboard for comprehensive testing:
```
http://localhost/lexiaid/site/test-dashboard.html
```

### Manual Testing
1. **Search**: Try queries like "constitutional law", "criminal rights"
2. **Tasks**: Create, update, and delete tasks (requires user login)
3. **Upload**: Upload sample legal cases
4. **Insights**: View analytics dashboard

### Sample Test Data
The system includes sample legal documents in `python/legal_documents.json`:
- Miranda v. Arizona
- Brown v. Board of Education  
- Gideon v. Wainwright
- Mapp v. Ohio
- Terry v. Ohio

## 🔍 Troubleshooting

### Common Issues

**Search not working:**
- Check Python virtual environment is activated
- Verify `sentence-transformers` is installed
- Review logs in `site/logs/search.log`
- System will fallback to keyword search if needed

**Database connection errors:**
- Ensure MySQL is running in XAMPP
- Verify database credentials
- Check if `lexiaid_db` database exists

**Python script timeouts:**
- First run may take longer (model download)
- Check internet connection for model download
- System includes fallback search mechanism

### Log Files
- Search logs: `site/logs/search.log`
- Apache logs: XAMPP control panel → Logs
- PHP errors: Enable in `php.ini` or check XAMPP logs

## 🎯 Features to Test

### Core Functionality
- [x] Semantic search with natural language queries
- [x] Keyword-based fallback search
- [x] Case brief generation (requires Python models)
- [x] Auto-tagging and classification
- [x] Task management system
- [x] Study insights and analytics
- [x] Quiz system

### User Interface
- [x] Responsive design (mobile/tablet/desktop)
- [x] Modern Bootstrap-based UI
- [x] Intuitive navigation
- [x] Error handling and feedback

## 📊 Performance Notes

### First Run
- Initial model download may take 2-5 minutes
- Subsequent searches are much faster
- Fallback search provides instant results

### Production Deployment
- Consider pre-downloading ML models
- Implement proper caching mechanisms
- Configure production database settings
- Enable PHP opcache for better performance

## 🏁 Success Criteria

✅ **Setup Complete** when:
- All test dashboard checks pass ✅
- Search returns relevant results ✅  
- Database operations work ✅
- Python scripts execute successfully ✅
- UI is responsive and functional ✅

## 📞 Support

For issues or questions:
- Check the troubleshooting section above
- Review log files for error details
- Ensure all prerequisites are properly installed
- Verify file permissions are correct
