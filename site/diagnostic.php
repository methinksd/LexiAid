<?php
/**
 * LexiAid Server Environment Diagnostic
 * Protected diagnostic page with authentication
 */

// Authentication check
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="LexiAid Diagnostics"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Access denied. Authentication required.';
    exit;
}

// Load environment variables for credentials
require_once __DIR__ . '/config/database.php';

$validUsername = getenv('ADMIN_USER') ?: 'admin';
$validPassword = getenv('ADMIN_PASS') ?: 'secure123';

if ($_SERVER['PHP_AUTH_USER'] !== $validUsername || $_SERVER['PHP_AUTH_PW'] !== $validPassword) {
    header('WWW-Authenticate: Basic realm="LexiAid Diagnostics"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Access denied. Invalid credentials.';
    exit;
}

// Start output buffering
ob_start();

// Configure headers
header("Content-Type: text/html; charset=UTF-8");

echo "<!DOCTYPE html>\n";
echo "<html><head><title>LexiAid Server Diagnostic</title></head><body>\n";
echo "<h1>LexiAid Server Environment Diagnostic</h1>\n";
echo "<p>Generated on: " . date('Y-m-d H:i:s') . "</p>\n";

echo "<h2>PHP Information</h2>\n";
echo "<ul>\n";
echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>\n";
echo "<li><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</li>\n";
echo "<li><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</li>\n";
echo "<li><strong>Script Directory:</strong> " . __DIR__ . "</li>\n";
echo "</ul>\n";

echo "<h2>Required Extensions</h2>\n";
echo "<ul>\n";
echo "<li><strong>MySQLi:</strong> " . (extension_loaded('mysqli') ? '✅ Available' : '❌ Missing') . "</li>\n";
echo "<li><strong>JSON:</strong> " . (extension_loaded('json') ? '✅ Available' : '❌ Missing') . "</li>\n";
echo "<li><strong>cURL:</strong> " . (extension_loaded('curl') ? '✅ Available' : '❌ Missing') . "</li>\n";
echo "</ul>\n";

echo "<h2>File Permissions</h2>\n";
$checkFiles = ['config/database.php', 'search.php', 'tasks.php', 'quizzes.php'];
echo "<ul>\n";
foreach ($checkFiles as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "<li><strong>$file:</strong> ✅ Exists (" . substr(sprintf('%o', fileperms($path)), -4) . ")</li>\n";
    } else {
        echo "<li><strong>$file:</strong> ❌ Missing</li>\n";
    }
}
echo "</ul>\n";

echo "<h2>Database Connection Test</h2>\n";
try {
    $dbHost = DB_HOST;
    $dbUser = DB_USER;
    $dbPass = DB_PASS;
    
    $conn = new mysqli($dbHost, $dbUser, $dbPass);
    if ($conn->connect_error) {
        echo "<p>❌ <strong>Connection Failed:</strong> " . $conn->connect_error . "</p>\n";
    } else {
        echo "<p>✅ <strong>Database Connection:</strong> Successful</p>\n";
        echo "<p><strong>MySQL Version:</strong> " . $conn->server_info . "</p>\n";
        
        // Try to create/select database
        $dbName = 'lexiaid';
        if ($conn->query("CREATE DATABASE IF NOT EXISTS `$dbName`")) {
            echo "<p>✅ <strong>Database Creation:</strong> Successful</p>\n";
            if ($conn->select_db($dbName)) {
                echo "<p>✅ <strong>Database Selection:</strong> Successful</p>\n";
            } else {
                echo "<p>❌ <strong>Database Selection:</strong> Failed - " . $conn->error . "</p>\n";
            }
        } else {
            echo "<p>❌ <strong>Database Creation:</strong> Failed - " . $conn->error . "</p>\n";
        }
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>Database Test Failed:</strong> " . $e->getMessage() . "</p>\n";
}

echo "<h2>Error Reporting Status</h2>\n";
echo "<ul>\n";
echo "<li><strong>Display Errors:</strong> " . (ini_get('display_errors') ? 'ON' : 'OFF') . "</li>\n";
echo "<li><strong>Log Errors:</strong> " . (ini_get('log_errors') ? 'ON' : 'OFF') . "</li>\n";
echo "<li><strong>Error Log:</strong> " . (ini_get('error_log') ?: 'Default') . "</li>\n";
echo "</ul>\n";

echo "<h2>Quick API Tests</h2>\n";
echo "<p>These tests check if PHP scripts return proper JSON responses:</p>\n";

// Test simple endpoints
$testEndpoints = [
    'test_database.php' => 'GET',
    'search_simple.php' => 'POST',
    'tasks_simple.php' => 'GET',
    'quizzes_simple.php' => 'GET'
];

echo "<ul>\n";
foreach ($testEndpoints as $endpoint => $method) {
    if (file_exists(__DIR__ . '/' . $endpoint)) {
        echo "<li><strong>$endpoint:</strong> ✅ File exists</li>\n";
    } else {
        echo "<li><strong>$endpoint:</strong> ❌ File missing</li>\n";
    }
}
echo "</ul>\n";

echo "<h2>Recommendations</h2>\n";
if (!extension_loaded('mysqli')) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>\n";
    echo "<strong>Critical:</strong> MySQLi extension is missing. Install php-mysqli or enable it in php.ini\n";
    echo "</div>\n";
}

echo "<div style='color: blue; padding: 10px; border: 1px solid blue; margin: 10px 0;'>\n";
echo "<strong>Next Steps:</strong><br>\n";
echo "1. Make sure you're accessing this through a web server (http://localhost/...)<br>\n";
echo "2. If using XAMPP/WAMP, ensure Apache and MySQL are running<br>\n";
echo "3. Check that PHP is properly configured<br>\n";
echo "4. Verify database credentials in config/database.php<br>\n";
echo "5. Test the simplified APIs in the backend test page\n";
echo "</div>\n";

echo "</body></html>\n";

// Output the buffer
ob_end_flush();
?>
