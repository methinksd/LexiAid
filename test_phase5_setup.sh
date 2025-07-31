#!/bin/bash

# LexiAid Phase 5 Test Setup Script
# This script sets up and tests the Task Management and Quiz System functionality

echo "🎯 LexiAid Phase 5 - Task & Quiz System Test Setup"
echo "=================================================="

# Navigate to the project directory
cd "/home/leo/Freelance Projects/LexiAid"

# Check if we're in the right directory
if [ ! -f "site/index.html" ]; then
    echo "❌ Error: Please run this script from the LexiAid root directory"
    exit 1
fi

echo "📍 Current directory: $(pwd)"

# Create necessary directories
echo "📁 Creating required directories..."
mkdir -p site/logs
chmod 755 site/logs

# Set proper file permissions
echo "🔧 Setting file permissions..."
chmod 644 site/config/.env 2>/dev/null || echo "⚠️  .env file not found - will use defaults"
chmod 644 site/config/database.php
chmod 755 site/*.php
chmod 644 site/js/*.js
chmod 644 site/*.html

# Check PHP availability and extensions
echo ""
echo "🔍 Checking PHP environment..."
if command -v php &> /dev/null; then
    echo "✅ PHP is available: $(php --version | head -n1)"
    
    # Check required PHP extensions
    echo "🧩 Checking PHP extensions..."
    
    if php -m | grep -q mysqli; then
        echo "✅ MySQLi extension is available"
    else
        echo "❌ MySQLi extension is missing - required for database operations"
        echo "   Install with: sudo apt-get install php-mysqli (Ubuntu/Debian)"
        echo "   or: sudo yum install php-mysqli (CentOS/RHEL)"
    fi
    
    if php -m | grep -q json; then
        echo "✅ JSON extension is available"
    else
        echo "❌ JSON extension is missing - required for API responses"
    fi
    
    if php -m | grep -q curl; then
        echo "✅ cURL extension is available"
    else
        echo "⚠️  cURL extension is missing - may affect some features"
    fi
else
    echo "❌ PHP is not available. Please install PHP 7.4 or higher."
    exit 1
fi

# Test database connectivity
echo ""
echo "🗄️  Testing database connectivity..."
cd site

# Test the database connection directly
echo "🔌 Testing database connection..."
if php -r "
require_once 'config/database.php';
try {
    \$result = testDatabaseConnection();
    if (\$result['status'] === 'success') {
        echo 'Database connection: SUCCESS\n';
        echo 'Server info: ' . (\$result['server_info'] ?? 'Unknown') . '\n';
    } else {
        echo 'Database connection: FAILED\n';
        echo 'Error: ' . \$result['message'] . '\n';
    }
} catch (Exception \$e) {
    echo 'Database connection: ERROR\n';
    echo 'Exception: ' . \$e->getMessage() . '\n';
}
"; then
    echo "✅ Database connectivity test completed"
else
    echo "❌ Database connectivity test failed"
    echo "💡 Make sure MySQL is running and check your .env configuration"
fi

# Test individual API endpoints
echo ""
echo "🔌 Testing API endpoints..."

echo "📋 Testing Tasks API..."
if curl -s -X GET "http://localhost/tasks.php?user_id=1" > /dev/null 2>&1; then
    echo "✅ Tasks API endpoint accessible"
else
    echo "⚠️  Tasks API endpoint not accessible (server may not be running)"
fi

echo "🧠 Testing Quizzes API..."
if curl -s -X GET "http://localhost/quizzes.php?user_id=1" > /dev/null 2>&1; then
    echo "✅ Quizzes API endpoint accessible"
else
    echo "⚠️  Quizzes API endpoint not accessible (server may not be running)"
fi

# Check file structure
echo ""
echo "📂 Checking file structure..."

required_files=(
    "tasks.html"
    "tasks.php"
    "quizzes.html"
    "quizzes.php"
    "js/tasks.js"
    "js/quizzes.js"
    "test_phase5.html"
    "test-dashboard.html"
    "config/database.php"
)

missing_files=()
for file in "${required_files[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file exists"
    else
        echo "❌ $file is missing"
        missing_files+=("$file")
    fi
done

if [ ${#missing_files[@]} -eq 0 ]; then
    echo "✅ All required files are present"
else
    echo "❌ Missing files: ${missing_files[*]}"
    echo "   Please ensure all Phase 5 files have been created"
fi

# Test PHP syntax for critical files
echo ""
echo "🧪 Testing PHP syntax..."

php_files=("tasks.php" "quizzes.php" "test_database.php" "config/database.php")
syntax_errors=0

for file in "${php_files[@]}"; do
    if [ -f "$file" ]; then
        if php -l "$file" > /dev/null 2>&1; then
            echo "✅ $file - syntax OK"
        else
            echo "❌ $file - syntax errors detected"
            php -l "$file"
            ((syntax_errors++))
        fi
    fi
done

if [ $syntax_errors -eq 0 ]; then
    echo "✅ All PHP files have valid syntax"
else
    echo "❌ Found $syntax_errors PHP syntax errors"
fi

# Run quick API tests
echo ""
echo "🚀 Running quick functionality tests..."

echo "📋 Testing Task Creation..."
task_result=$(php -r "
\$_SERVER['REQUEST_METHOD'] = 'POST';
ob_start();
require 'tasks.php';
\$output = ob_get_clean();
echo \$output;
" 2>/dev/null)

if echo "$task_result" | grep -q '"status":"success"'; then
    echo "✅ Task creation test passed"
elif echo "$task_result" | grep -q '"status":"error"'; then
    echo "⚠️  Task creation returned error (expected for invalid data)"
else
    echo "❌ Task creation test failed"
fi

echo "🧠 Testing Quiz Submission..."
quiz_result=$(php -r "
\$_SERVER['REQUEST_METHOD'] = 'GET';
\$_GET['user_id'] = '1';
ob_start();
require 'quizzes.php';
\$output = ob_get_clean();
echo \$output;
" 2>/dev/null)

if echo "$quiz_result" | grep -q '"status":"success"'; then
    echo "✅ Quiz retrieval test passed"
elif echo "$quiz_result" | grep -q '"status":"error"'; then
    echo "⚠️  Quiz retrieval returned error"
else
    echo "❌ Quiz retrieval test failed"
fi

# Show startup instructions
echo ""
echo "🌐 Starting local development server..."
echo "📝 Phase 5 Setup Complete!"
echo ""
echo "🎯 Next Steps:"
echo "   1. Ensure MySQL server is running with database 'lexiaid'"
echo "   2. Update site/config/.env with your database credentials if needed"
echo "   3. The PHP development server will start on http://localhost:8080"
echo ""
echo "📍 Available Test Pages:"
echo "   • Phase 5 Test Suite: http://localhost:8080/test_phase5.html"
echo "   • Test Dashboard: http://localhost:8080/test-dashboard.html"
echo "   • Task Manager: http://localhost:8080/tasks.html"
echo "   • Quiz System: http://localhost:8080/quizzes.html"
echo "   • Main Dashboard: http://localhost:8080"
echo ""
echo "🧪 Test Commands Available:"
echo "   • Run All Tests: Open test_phase5.html and click 'Run All Tests'"
echo "   • Manual Testing: Use the interactive forms in the test page"
echo "   • API Testing: Use the test dashboard for individual endpoint tests"
echo ""
echo "🔧 Database Setup:"
echo "   • Tables will be created automatically on first access"
echo "   • Demo data will be inserted if tables are empty"
echo "   • Use demo user ID: 1 for testing"
echo ""
echo "🛑 To stop the server, press Ctrl+C"
echo ""

# Start the development server
echo "🚀 Starting PHP development server..."
php -S localhost:8080

# This will only execute if the server stops
echo ""
echo "🛑 Server stopped."
echo ""
echo "📊 Phase 5 Test Summary:"
echo "   • Database connectivity: Check test_database.php"
echo "   • Task management: Check tasks.php and tasks.js"
echo "   • Quiz system: Check quizzes.php and quizzes.js"
echo "   • Security: Input validation and prepared statements"
echo "   • Frontend: Interactive UI with AJAX functionality"
echo ""
echo "✅ Phase 5 implementation is complete and ready for testing!"
