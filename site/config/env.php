<?php
/**
 * LexiAid Environment Configuration
 * Phase 7: Security Enhancement - Environment Variables
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'lexiaid_user');
define('DB_PASS', 'lexiaid_secure_2025');
define('DB_NAME', 'lexiaid_db');

// Python NLP API Configuration
define('PYTHON_API_HOST', 'localhost');
define('PYTHON_API_PORT', 5000);
define('PYTHON_API_ENDPOINT', 'http://localhost:5000');

// Security Settings
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_SEARCH_QUERIES_PER_MINUTE', 20);
define('MAX_UPLOAD_SIZE_MB', 10);
define('ALLOWED_FILE_TYPES', ['pdf', 'txt', 'doc', 'docx']);

// Application Settings
define('APP_NAME', 'LexiAid');
define('APP_VERSION', '1.0.0');
define('APP_ENVIRONMENT', 'production'); // development, staging, production
define('DEBUG_MODE', false);

// Email Configuration (for notifications)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@lexiaid.com');
define('SMTP_PASSWORD', 'your_app_password_here');
define('FROM_EMAIL', 'noreply@lexiaid.com');
define('FROM_NAME', 'LexiAid System');

// File Upload Paths
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('BACKUP_PATH', __DIR__ . '/../backups/');
define('LOGS_PATH', __DIR__ . '/../logs/');

// Security Keys (Generate new ones for production)
define('ENCRYPTION_KEY', 'your-32-character-secret-key-here');
define('SESSION_SALT', 'your-unique-session-salt-here');

// Rate Limiting
define('ENABLE_RATE_LIMITING', true);
define('RATE_LIMIT_STORAGE', 'file'); // file, redis, database

// Logging
define('ENABLE_LOGGING', true);
define('LOG_LEVEL', 'WARNING'); // DEBUG, INFO, WARNING, ERROR
define('LOG_RETENTION_DAYS', 30);

// Backup Settings
define('ENABLE_AUTO_BACKUP', true);
define('BACKUP_FREQUENCY', 'daily'); // hourly, daily, weekly
define('MAX_BACKUP_FILES', 10);

// Feature Flags
define('ENABLE_SEMANTIC_SEARCH', true);
define('ENABLE_TASK_MANAGEMENT', true);
define('ENABLE_QUIZ_SYSTEM', true);
define('ENABLE_FILE_UPLOAD', true);
define('ENABLE_INSIGHTS', true);

// API Keys and External Services
define('OPENAI_API_KEY', ''); // If using OpenAI for enhanced NLP
define('GOOGLE_ANALYTICS_ID', ''); // For tracking
define('SENTRY_DSN', ''); // For error tracking

?>
