<?php
// Database connection configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');     // Change this to your MySQL username
define('DB_PASS', '');         // Change this to your MySQL password
define('DB_NAME', 'lexiaid');  // Change this to your database name

// Create a database connection
function getDbConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Set character set to utf8
        $conn->set_charset("utf8");
        
        return $conn;
    } catch (Exception $e) {
        error_log("Database connection error: " . $e->getMessage());
        throw new Exception("Database connection error occurred");
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
