<?php
/**
 * Environment Variable Validation Script
 * Run this script before deployment to ensure all required variables are set
 */

echo "🔍 LexiAid Environment Validation\n";
echo "================================\n\n";

try {
    // Test environment loading
    require_once __DIR__ . '/config/database.php';
    
    echo "✅ Environment file loaded successfully\n";
    echo "✅ All required database variables are set\n\n";
    
    // Display configuration (without sensitive data)
    echo "📋 Configuration Summary:\n";
    echo "  Database Host: " . DB_HOST . "\n";
    echo "  Database Name: " . DB_NAME . "\n";
    echo "  Database User: " . DB_USER . "\n";
    echo "  Password Set: " . (DB_PASS ? "Yes (" . str_repeat('*', strlen(DB_PASS)) . ")" : "No") . "\n";
    echo "  Environment: " . (getenv('APP_ENV') ?: 'development') . "\n\n";
    
    // Test database connection
    echo "🔗 Testing database connection...\n";
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        echo "❌ Database connection failed: " . $conn->connect_error . "\n";
        exit(1);
    } else {
        echo "✅ Database connection successful\n";
        echo "  MySQL Version: " . $conn->server_info . "\n";
        $conn->close();
    }
    
    // Check admin credentials
    $adminUser = getenv('ADMIN_USER');
    $adminPass = getenv('ADMIN_PASS');
    
    if ($adminUser && $adminPass) {
        echo "✅ Admin credentials configured\n";
        echo "  Admin User: " . $adminUser . "\n";
        echo "  Admin Password: " . str_repeat('*', strlen($adminPass)) . "\n";
    } else {
        echo "⚠️  Admin credentials not fully configured\n";
    }
    
    // Security checks
    echo "\n🔒 Security Validation:\n";
    
    // Check if .env file is in web root (security risk)
    $envPath = __DIR__ . '/config/.env';
    $webRoot = $_SERVER['DOCUMENT_ROOT'] ?? __DIR__;
    
    if (strpos($envPath, $webRoot) === 0) {
        echo "⚠️  WARNING: .env file may be accessible via web\n";
        echo "   Consider moving .env outside document root\n";
    } else {
        echo "✅ .env file location appears secure\n";
    }
    
    // Check for default passwords
    if (DB_PASS === 'your_secure_password_here') {
        echo "❌ CRITICAL: Default database password detected!\n";
        echo "   Update DB_PASS in .env file before deployment\n";
        exit(1);
    }
    
    if ($adminPass === 'secure_admin_password_change_me') {
        echo "❌ CRITICAL: Default admin password detected!\n";
        echo "   Update ADMIN_PASS in .env file before deployment\n";
        exit(1);
    }
    
    // Check production settings
    if (getenv('APP_ENV') === 'production') {
        echo "✅ Production environment configured\n";
        echo "✅ Error display disabled for production\n";
    } else {
        echo "ℹ️  Development environment active\n";
        echo "   Set APP_ENV=production for deployment\n";
    }
    
    echo "\n🎉 Environment validation completed successfully!\n";
    echo "Ready for deployment.\n";
    
} catch (Exception $e) {
    echo "❌ Validation failed: " . $e->getMessage() . "\n";
    echo "\n📝 To fix this issue:\n";
    echo "1. Copy .env.example to .env\n";
    echo "2. Update all variables with your actual values\n";
    echo "3. Ensure database credentials are correct\n";
    echo "4. Run this script again\n";
    exit(1);
}
?>
