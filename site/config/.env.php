<?php
/**
 * LexiAid Phase 7 - Security Configuration
 * Environment variables and security settings
 */

// Database Configuration
putenv('DB_HOST=localhost');
putenv('DB_USER=lexiaid_user');
putenv('DB_PASS=secure_password_2025');
putenv('DB_NAME=lexiaid_db');

// Security Settings
putenv('SECRET_KEY=LexiAid_2025_Secure_Key_' . bin2hex(random_bytes(16)));
putenv('ENCRYPTION_METHOD=AES-256-CBC');

// Python API Configuration
putenv('PYTHON_API_URL=http://localhost:5000');
putenv('PYTHON_API_TIMEOUT=30');

// Email Configuration (if needed)
putenv('SMTP_HOST=smtp.gmail.com');
putenv('SMTP_PORT=587');
putenv('SMTP_USERNAME=support@lexiaid.com');
putenv('SMTP_PASSWORD=email_password_here');

// File Upload Settings
putenv('MAX_FILE_SIZE=52428800'); // 50MB
putenv('ALLOWED_FILE_TYPES=pdf,doc,docx,txt');
putenv('UPLOAD_PATH=' . dirname(__FILE__) . '/../uploads/');

// Security Headers
putenv('SECURITY_HEADERS=1');

// Development Mode (set to 0 in production)
putenv('DEBUG_MODE=1');

// Session Configuration
putenv('SESSION_TIMEOUT=3600'); // 1 hour
putenv('SESSION_NAME=LEXIAID_SESSION');

// Rate Limiting
putenv('RATE_LIMIT_REQUESTS=100');
putenv('RATE_LIMIT_WINDOW=3600'); // 1 hour

// Cross-Origin Resource Sharing (CORS)
putenv('CORS_ALLOWED_ORIGINS=*');
putenv('CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS');
putenv('CORS_ALLOWED_HEADERS=Content-Type,Authorization,X-Requested-With');
?>
