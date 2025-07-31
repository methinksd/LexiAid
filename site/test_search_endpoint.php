<?php
/**
 * Test script to check search.php endpoint
 * This script will make a POST request to search.php and show the response
 */

echo "<h1>Testing Search Endpoint</h1>\n";

// Test data
$testQuery = "constitutional law";
$postData = json_encode([
    'query' => $testQuery,
    'top_k' => 3
]);

// URL to test
$url = 'http://localhost/search.php';

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($postData)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

// Check for cURL errors
if (curl_error($ch)) {
    echo "<p><strong>cURL Error:</strong> " . curl_error($ch) . "</p>\n";
} else {
    echo "<p><strong>HTTP Code:</strong> $httpCode</p>\n";
    echo "<p><strong>Content Type:</strong> $contentType</p>\n";
    echo "<p><strong>Response:</strong></p>\n";
    echo "<pre>" . htmlspecialchars($response) . "</pre>\n";
    
    // Try to parse as JSON
    $decodedResponse = json_decode($response, true);
    if ($decodedResponse !== null) {
        echo "<p><strong>JSON Response (parsed):</strong></p>\n";
        echo "<pre>" . print_r($decodedResponse, true) . "</pre>\n";
    } else {
        echo "<p><strong>JSON Parse Error:</strong> " . json_last_error_msg() . "</p>\n";
    }
}

curl_close($ch);

// Also test direct inclusion
echo "<hr><h2>Testing Direct PHP Inclusion</h2>\n";

// Set up the environment to simulate a POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [];

// Capture the output
ob_start();

// Simulate the input
$originalInput = file_get_contents('php://input');

// Temporarily redirect input
$tempFile = tempnam(sys_get_temp_dir(), 'test_input');
file_put_contents($tempFile, $postData);

try {
    // This won't work because we can't override php://input, but we can test the functions
    echo "<p>Direct inclusion test would require modifying the search.php file to accept test data.</p>\n";
    echo "<p>Test data would be: " . htmlspecialchars($postData) . "</p>\n";
    
} catch (Exception $e) {
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>\n";
}

// Clean up
if (file_exists($tempFile)) {
    unlink($tempFile);
}

echo "<p><em>Test completed at " . date('Y-m-d H:i:s') . "</em></p>\n";
?>
