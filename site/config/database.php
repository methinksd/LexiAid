<?php
/**
 * Database connection configuration for LexiAid
 * Updated with better error handling and security
 */

// Load environment variables
function loadEnvFile($filepath) {
    if (!file_exists($filepath)) {
        return;
    }
    
    $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        throw new Exception("Failed to read environment file: $filepath");
    }
    
    foreach ($lines as $lineNumber => $line) {
        $line = trim($line);
        
        // Skip empty lines and comments
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        // Check if line contains '=' before splitting
        if (strpos($line, '=') === false) {
            error_log("Warning: Invalid environment line at $filepath:$lineNumber - missing '=' character");
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Validate environment variable name
        if (!preg_match('/^[A-Z_][A-Z0-9_]*$/i', $name)) {
            error_log("Warning: Invalid environment variable name '$name' at $filepath:$lineNumber");
            continue;
        }
        
        // Handle quoted values - remove surrounding quotes
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        
        // Only set if not already present in environment
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Validate required environment variables
function validateRequiredEnvVars($required) {
    $missing = [];
    foreach ($required as $var) {
        $value = getenv($var);
        if ($value === false || $value === '') {
            $missing[] = $var;
        }
    }
    
    if (!empty($missing)) {
        throw new Exception(
            'Missing required environment variables: ' . implode(', ', $missing) . 
            '. Please configure these in your .env file or environment.'
        );
    }
}

// Load environment configuration
loadEnvFile(__DIR__ . '/.env');

// Validate required database environment variables
$requiredVars = ['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_NAME'];
validateRequiredEnvVars($requiredVars);

// Database connection configuration - no fallbacks for security
define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_NAME', getenv('DB_NAME'));

// Error reporting configuration based on environment
$isProduction = (getenv('APP_ENV') === 'production');
error_reporting(E_ALL);
ini_set('display_errors', $isProduction ? 0 : 1);
ini_set('log_errors', 1);

// Create a database connection
function getDbConnection() {
    try {
        // Check if MySQLi extension is loaded
        if (!extension_loaded('mysqli')) {
            throw new Exception("MySQLi extension is not loaded. Please install php-mysqli.");
        }

        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Create database if it doesn't exist
        $dbName = DB_NAME;
        $createDbQuery = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8 COLLATE utf8_general_ci";
        if (!$conn->query($createDbQuery)) {
            throw new Exception("Error creating database: " . $conn->error);
        }

        // Select the database
        if (!$conn->select_db($dbName)) {
            throw new Exception("Error selecting database: " . $conn->error);
        }

        // Set character set to utf8
        $conn->set_charset("utf8");
        
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        throw new Exception("Database connection error: " . $e->getMessage());
    }
}

// Function to test database connection and create tables if they don't exist
function testDatabaseConnection() {
    try {
        $conn = getDbConnection();
        
        // Test basic connectivity
        $result = $conn->query("SELECT 1 as test");
        if (!$result) {
            throw new Exception("Cannot execute test query");
        }
        
        // Create tables if they don't exist (for demo purposes)
        createTablesIfNotExist($conn);
        
        return [
            'status' => 'success',
            'message' => 'Database connection successful',
            'server_info' => $conn->server_info,
            'client_info' => $conn->client_info
        ];
        
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
}

// Function to create demo tables
function createTablesIfNotExist($conn) {
    // Create users table
    $usersTable = "CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Create tasks table
    $tasksTable = "CREATE TABLE IF NOT EXISTS tasks (
        task_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        category VARCHAR(50) NOT NULL,
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        deadline DATETIME,
        completed BOOLEAN DEFAULT FALSE,
        completion_notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
    
    // Create quizzes table
    $quizzesTable = "CREATE TABLE IF NOT EXISTS quizzes (
        quiz_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        topic VARCHAR(100) NOT NULL,
        score DECIMAL(5,2) NOT NULL,
        details JSON,
        completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )";
    
    // Create legal_resources table for search functionality
    $resourcesTable = "CREATE TABLE IF NOT EXISTS legal_resources (
        resource_id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        summary TEXT,
        year YEAR,
        tags JSON,
        citation VARCHAR(255),
        jurisdiction VARCHAR(100),
        type VARCHAR(50),
        content TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // Execute table creation queries
    $tables = [
        'users' => $usersTable,
        'tasks' => $tasksTable,
        'quizzes' => $quizzesTable,
        'legal_resources' => $resourcesTable
    ];
    
    foreach ($tables as $tableName => $query) {
        if (!$conn->query($query)) {
            throw new Exception("Error creating table $tableName: " . $conn->error);
        }
    }
    
    // Insert demo data if tables are empty
    insertDemoData($conn);
}

// Function to insert demo data
function insertDemoData($conn) {
    // Check if demo user exists
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE username = 'demo_student'");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Insert demo user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $username = 'demo_student';
        $email = 'demo@lexiaid.com';
        $password_hash = password_hash('demo123', PASSWORD_DEFAULT);
        $stmt->bind_param("sss", $username, $email, $password_hash);
        $stmt->execute();
        $demoUserId = $conn->insert_id;
        
        // Insert demo tasks
        $demoTasks = [
            ['Constitutional Law Brief', 'Marbury v. Madison case analysis - 3-5 pages', 'brief', 'high', '2025-07-03 23:59:59'],
            ['Read Supreme Court Cases', 'Review Brown v. Board of Education and Miranda v. Arizona', 'reading', 'medium', '2025-07-05 18:00:00'],
            ['Contract Law Quiz', 'Complete practice quiz on contract formation', 'quiz', 'medium', '2025-07-04 15:00:00'],
            ['Research Property Law', 'Find 5 relevant cases on adverse possession', 'research', 'low', '2025-07-07 12:00:00'],
            ['Criminal Law Essay', 'Write analysis on Fourth Amendment protections', 'essay', 'high', '2025-07-06 23:59:59']
        ];
        
        $taskStmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, category, priority, deadline) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($demoTasks as $task) {
            $taskStmt->bind_param("isssss", $demoUserId, $task[0], $task[1], $task[2], $task[3], $task[4]);
            $taskStmt->execute();
        }
        
        // Insert demo quiz results
        $demoQuizzes = [
            ['Constitutional Law', 85.5, '{"questions": 20, "correct": 17, "topics": ["First Amendment", "Due Process"]}'],
            ['Contract Law', 92.0, '{"questions": 15, "correct": 14, "topics": ["Formation", "Consideration"]}'],
            ['Criminal Procedure', 78.5, '{"questions": 25, "correct": 19, "topics": ["Miranda Rights", "Search & Seizure"]}']
        ];
        
        $quizStmt = $conn->prepare("INSERT INTO quizzes (user_id, topic, score, details) VALUES (?, ?, ?, ?)");
        foreach ($demoQuizzes as $quiz) {
            $quizStmt->bind_param("isds", $demoUserId, $quiz[0], $quiz[1], $quiz[2]);
            $quizStmt->execute();
        }
        
        // Insert demo legal resources
        $demoResources = [
            ['Miranda v. Arizona', 'Established that police must inform suspects of their rights before custodial interrogation.', 1966, '["Criminal Law", "Constitutional Law"]', '384 U.S. 436 (1966)', 'Federal', 'Supreme Court Case'],
            ['Brown v. Board of Education', 'Ruled that racial segregation in public schools is unconstitutional.', 1954, '["Civil Rights", "Constitutional Law"]', '347 U.S. 483 (1954)', 'Federal', 'Supreme Court Case'],
            ['Marbury v. Madison', 'Established the principle of judicial review in the United States.', 1803, '["Constitutional Law", "Judicial Review"]', '5 U.S. 137 (1803)', 'Federal', 'Supreme Court Case'],
            ['Gideon v. Wainwright', 'Established right to counsel for criminal defendants who cannot afford an attorney.', 1963, '["Criminal Law", "Constitutional Law"]', '372 U.S. 335 (1963)', 'Federal', 'Supreme Court Case']
        ];
        
        $resourceStmt = $conn->prepare("INSERT INTO legal_resources (title, summary, year, tags, citation, jurisdiction, type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($demoResources as $resource) {
            $resourceStmt->bind_param("sisssss", $resource[0], $resource[1], $resource[2], $resource[3], $resource[4], $resource[5], $resource[6]);
            $resourceStmt->execute();
        }
    }
}

// Function to sanitize user input
function sanitizeInput($input, $conn = null) {
    if (is_array($input)) {
        return array_map(function($item) use ($conn) {
            return sanitizeInput($item, $conn);
        }, $input);
    }
    
    if ($conn !== null) {
        return $conn->real_escape_string($input);
    }
    
    return filter_var($input, FILTER_SANITIZE_STRING);
}
