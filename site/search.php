<?php
// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configure CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validate input
    if (!isset($data['query']) || empty($data['query'])) {
        throw new Exception('Search query is required');
    }

    // Sanitize the query
    $query = filter_var($data['query'], FILTER_SANITIZE_STRING);
    
    // Optional: number of results to return
    $topK = isset($data['top_k']) ? (int)$data['top_k'] : 5;    // Path to Python script
    $scriptPath = dirname(__DIR__) . '/python/semantic_search.py';
    
    if (!file_exists($scriptPath)) {
        throw new Exception('Semantic search script not found');
    }

    // Execute Python script using py command
    $command = sprintf('py %s %s --top_k %s 2>&1', 
        escapeshellarg($scriptPath),
        escapeshellarg($query),
        escapeshellarg($topK)
    );

    // Execute the command
    $output = shell_exec($command);

    if ($output === null) {
        throw new Exception('Failed to execute search command');
    }

    // Try to decode the JSON output
    $results = json_decode($output, true);
    
    if ($results === null) {
        // Log the raw output for debugging
        error_log("Python script output: " . $output);
        throw new Exception('Invalid response from search engine');
    }

    // Log the search query (optional)
    $logFile = __DIR__ . '/logs/search.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    $logEntry = date('Y-m-d H:i:s') . " | Query: " . $query . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    // Return the search results
    echo json_encode([
        'status' => 'success',
        'results' => $results['results'] ?? [],
        'query' => $query,
        'timestamp' => date('c')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
<script>
fetch('search.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        query: 'constitutional law cases'
    })
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
</script>