<?php
/**
 * Simple Database Connection Test
 * This file tests the database connection and provides debugging information
 */

// Start output buffering to prevent any accidental output
ob_start();

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configure headers first
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

try {
    // Test basic PHP functionality
    $phpVersion = phpversion();
    
    // Test MySQLi extension
    if (!extension_loaded('mysqli')) {
        throw new Exception("MySQLi extension is not loaded");
    }

    // Database connection parameters
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = 'Chegengangav2.1';
    $dbName = 'lexiaid_db';

    // Test basic connection without database selection
    $conn = new mysqli($dbHost, $dbUser, $dbPass);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Try to create database if it doesn't exist
    $createDbResult = $conn->query("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8 COLLATE utf8_general_ci");
    if (!$createDbResult) {
        throw new Exception("Cannot create database: " . $conn->error);
    }

    // Select the database
    if (!$conn->select_db($dbName)) {
        throw new Exception("Cannot select database: " . $conn->error);
    }

    // Test basic query
    $testResult = $conn->query("SELECT 1 as test_value, NOW() as server_time");
    if (!$testResult) {
        throw new Exception("Cannot execute test query: " . $conn->error);
    }

    $testRow = $testResult->fetch_assoc();

    // Test table creation and basic operations
    $tableTests = [];
    
    // Test creating a sample table
    $createTableQuery = "CREATE TABLE IF NOT EXISTS test_table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createTableQuery)) {
        $tableTests['test_table_creation'] = 'success';
        
        // Insert sample data
        $insertQuery = "INSERT INTO test_table (name) VALUES ('Test Entry') ON DUPLICATE KEY UPDATE name=name";
        if ($conn->query($insertQuery)) {
            $tableTests['test_data_insertion'] = 'success';
            
            // Retrieve sample data
            $selectQuery = "SELECT * FROM test_table LIMIT 1";
            $selectResult = $conn->query($selectQuery);
            if ($selectResult && $selectResult->num_rows > 0) {
                $sampleData = $selectResult->fetch_assoc();
                $tableTests['test_data_retrieval'] = 'success';
            } else {
                $sampleData = null;
                $tableTests['test_data_retrieval'] = 'no_data';
            }
        } else {
            $tableTests['test_data_insertion'] = 'failed: ' . $conn->error;
            $sampleData = null;
        }
        
        // Clean up test table
        $conn->query("DROP TABLE IF EXISTS test_table");
        $tableTests['test_cleanup'] = 'success';
    } else {
        $tableTests['test_table_creation'] = 'failed: ' . $conn->error;
        $sampleData = null;
    }

    // Get server info
    $serverInfo = $conn->server_info;
    $clientInfo = mysqli_get_client_info();

    // Close connection
    $conn->close();

    // Clear any output buffer
    ob_clean();

    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Database connection successful',
        'php_version' => $phpVersion,
        'mysqli_available' => extension_loaded('mysqli'),
        'connection_info' => [
            'host' => $dbHost,
            'database' => $dbName,
            'server_info' => $serverInfo,
            'client_info' => $clientInfo,
            'test_query_result' => $testRow
        ],
        'table_tests' => $tableTests,
        'sample_data' => $sampleData,
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Clear any output buffer
    ob_clean();
    
    // Log the error
    error_log("Database test error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'php_version' => phpversion(),
        'mysqli_available' => extension_loaded('mysqli'),
        'debug_info' => [
            'error_file' => __FILE__,
            'error_line' => __LINE__
        ],
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>
