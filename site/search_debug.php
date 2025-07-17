<?php
/**
 * Minimal Search API Test
 * This is a simplified version to test if the basic search endpoint works
 */

// Start output buffering
ob_start();

// Set JSON content type immediately
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

try {
    // Clear any previous output
    ob_clean();
    
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed. Expected POST.');
    }
    
    // Get input data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validate input
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }
    
    if (!isset($data['query']) || empty($data['query'])) {
        throw new Exception('Query parameter is required');
    }
    
    $query = $data['query'];
    $topK = isset($data['top_k']) ? (int)$data['top_k'] : 5;
    
    // Simple mock search results
    $results = [
        [
            'title' => 'Miranda v. Arizona (Test Result)',
            'summary' => 'Mock result for query: ' . $query,
            'similarity_score' => 0.95,
            'tags' => ['Criminal Law', 'Constitutional Law'],
            'year' => 1966,
            'citation' => '384 U.S. 436',
            'jurisdiction' => 'Federal',
            'type' => 'Supreme Court Case'
        ],
        [
            'title' => 'Brown v. Board (Test Result)',
            'summary' => 'Another mock result for query: ' . $query,
            'similarity_score' => 0.85,
            'tags' => ['Civil Rights', 'Constitutional Law'],
            'year' => 1954,
            'citation' => '347 U.S. 483',
            'jurisdiction' => 'Federal',
            'type' => 'Supreme Court Case'
        ]
    ];
    
    // Return successful response
    echo json_encode([
        'status' => 'success',
        'results' => array_slice($results, 0, $topK),
        'query' => $query,
        'count' => min(count($results), $topK),
        'timestamp' => date('c'),
        'search_method' => 'mock',
        'debug_info' => [
            'input_received' => $input,
            'parsed_data' => $data,
            'server_method' => $_SERVER['REQUEST_METHOD']
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    // Clear any output
    ob_clean();
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('c'),
        'debug_info' => [
            'server_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'input_received' => file_get_contents('php://input'),
            'json_error' => json_last_error_msg()
        ]
    ], JSON_PRETTY_PRINT);
}
?>
