#!/bin/bash

# LexiAid Phase 5 Setup Script
# This script helps set up the development environment for LexiAid

echo "🚀 LexiAid Phase 5 Setup"
echo "========================="

# Check if we're in the right directory
if [ ! -f "site/index.html" ]; then
    echo "❌ Error: Please run this script from the LexiAid root directory"
    exit 1
fi

echo "📁 Setting up directories..."
mkdir -p site/logs
chmod 755 site/logs

echo "🔧 Setting file permissions..."
chmod 644 site/config/.env
chmod 644 site/config/database.php
chmod 755 site/*.php

echo "🌐 Starting local web server..."
echo "📝 Note: Make sure your MySQL server is running with the database 'lexiaid'"
echo "🔑 Database credentials (from .env): root / (no password)"
echo ""
echo "📍 Starting PHP development server on http://localhost:8080"
echo "🎯 You can access:"
echo "   • Main Dashboard: http://localhost:8080"
echo "   • Tasks Manager: http://localhost:8080/tasks.html"
echo "   • Quiz System: http://localhost:8080/quizzes.html"
echo "   • Test Dashboard: http://localhost:8080/test-dashboard.html"
echo ""
echo "🛑 Press Ctrl+C to stop the server"
echo ""

# Navigate to site directory and start server
cd site
php -S localhost:8080
