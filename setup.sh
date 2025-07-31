#!/bin/bash

# LexiAid Local Setup Script
echo "🔧 Setting up LexiAid for local development..."

# Check if MySQL is running
if ! pgrep -x "mysqld" > /dev/null; then
    echo "❌ MySQL is not running. Please start MySQL first."
    echo "   Ubuntu/Debian: sudo systemctl start mysql"
    echo "   macOS: brew services start mysql"
    exit 1
fi

# Check if Python 3 is available
if ! command -v python3 &> /dev/null; then
    echo "❌ Python 3 is not installed. Please install Python 3.8 or higher."
    exit 1
fi

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 7.4 or higher."
    exit 1
fi

echo "✅ Prerequisites check passed"

# Set up Python virtual environment
if [ ! -d "python/venv" ]; then
    echo "📦 Creating Python virtual environment..."
    cd python
    python3 -m venv venv
    source venv/bin/activate
    pip install -r requirements.txt
    cd ..
    echo "✅ Python environment setup complete"
else
    echo "✅ Python environment already exists"
fi

# Import database schema
echo "🗄️ Setting up database..."
mysql -u root -p < site/config/database.sql

echo "🎉 Setup complete!"
echo ""
echo "📝 Next steps:"
echo "1. Update site/config/.env with your database credentials"
echo "2. Make sure your web server (Apache/Nginx) is running"
echo "3. Point your web server to the 'site' directory"
echo "4. Access the application through your web server"
echo ""
echo "🔍 For testing, you can use PHP's built-in server:"
echo "   cd site && php -S localhost:8000"
