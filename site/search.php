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

// Simple keyword-based fallback search
function performKeywordSearch($query, $topK = 5) {
    // Sample legal documents for fallback
    $sampleDocs = [
        [
            'title' => 'Miranda v. Arizona',
            'summary' => 'Established that police must inform suspects of their rights before custodial interrogation.',
            'similarity_score' => 0.85,
            'tags' => ['Criminal Law', 'Constitutional Law'],
            'year' => 1966
        ],
        [
            'title' => 'Brown v. Board of Education',
            'summary' => 'Ruled that racial segregation in public schools is unconstitutional.',
            'similarity_score' => 0.75,
            'tags' => ['Civil Rights', 'Constitutional Law'],
            'year' => 1954
        ],
        [
            'title' => 'Gideon v. Wainwright',
            'summary' => 'Established right to counsel for criminal defendants who cannot afford an attorney.',
            'similarity_score' => 0.70,
            'tags' => ['Criminal Law', 'Constitutional Law'],
            'year' => 1963
        ]
    ];
    
    $queryLower = strtolower($query);
    $results = [];
    
    foreach ($sampleDocs as $doc) {
        $searchText = strtolower($doc['title'] . ' ' . $doc['summary'] . ' ' . implode(' ', $doc['tags']));
        
        // Simple keyword matching
        $queryWords = explode(' ', $queryLower);
        $matches = 0;
        foreach ($queryWords as $word) {
            if (strpos($searchText, $word) !== false) {
                $matches++;
            }
        }
        
        if ($matches > 0) {
            $doc['similarity_score'] = min(0.95, $matches / count($queryWords));
            $results[] = $doc;
        }
    }
    
    // Sort by similarity score
    usort($results, function($a, $b) {
        return $b['similarity_score'] <=> $a['similarity_score'];
    });
    
    return array_slice($results, 0, $topK);
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
    $fallbackScriptPath = dirname(__DIR__) . '/python/search_with_fallback.py';
    
    if (!file_exists($scriptPath)) {
        // Try fallback script
        if (file_exists($fallbackScriptPath)) {
            $scriptPath = $fallbackScriptPath;
        } else {
            throw new Exception('Search script not found');
        }
    }

    // Execute Python script using the virtual environment Python
    $pythonPath = dirname(__DIR__) . '/.venv/Scripts/python.exe';
    
    // Check if virtual environment Python exists, fallback to system Python
    if (!file_exists($pythonPath)) {
        $pythonPath = 'python'; // Fallback to system Python
    }
    
    $command = sprintf('"%s" %s %s --top_k %s 2>&1', 
        $pythonPath,
        escapeshellarg($scriptPath),
        escapeshellarg($query),
        escapeshellarg($topK)
    );

    // Execute the command
    $output = shell_exec($command);
    
    // Log the command and output for debugging
    $logFile = __DIR__ . '/logs/search.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    $logEntry = date('Y-m-d H:i:s') . " | Command: " . $command . "\n";
    $logEntry .= date('Y-m-d H:i:s') . " | Output: " . ($output ?: 'No output') . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    if ($output === null || trim($output) === '') {
        // Fallback to simple keyword search if Python fails
        $fallbackResults = performKeywordSearch($query, $topK);
        echo json_encode([
            'status' => 'success',
            'results' => $fallbackResults,
            'query' => $query,
            'timestamp' => date('c'),
            'note' => 'Using fallback search due to Python execution issue'
        ]);
        exit();
    }

    // Fix: Find the first valid JSON object and trim trailing non-JSON
    $jsonStart = strpos($output, '{');
    $jsonEnd = strrpos($output, '}');
    if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
        $output = substr($output, $jsonStart, $jsonEnd - $jsonStart + 1);
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