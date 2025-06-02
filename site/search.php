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

    if (!$data || !isset($data['query'])) {
        throw new Exception('Invalid request format');
    }

    // Sanitize the search query
    $query = filter_var($data['query'], FILTER_SANITIZE_STRING);
    
    // Log the search query (optional)
    $logFile = __DIR__ . '/logs/search.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    $logEntry = date('Y-m-d H:i:s') . " | Query: " . $query . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    // Generate dummy search results
    $dummyResults = [
        [
            'title' => 'Marbury v. Madison',
            'summary' => 'Landmark case establishing judicial review. The Supreme Court asserted its power to review acts of Congress and determine their constitutionality.',
            'matched_keywords' => ['judicial review', 'constitution', 'Supreme Court'],
            'tags' => ['Constitutional Law', 'Judicial Power', 'Historical'],
            'relevance_score' => 0.95
        ],
        [
            'title' => 'Brown v. Board of Education',
            'summary' => 'Supreme Court case that declared state laws establishing separate public schools for black and white students unconstitutional.',
            'matched_keywords' => ['education', 'equal protection', 'segregation'],
            'tags' => ['Civil Rights', 'Constitutional Law', 'Education'],
            'relevance_score' => 0.87
        ],
        [
            'title' => 'Miranda v. Arizona',
            'summary' => 'Established that police must inform suspects of their rights before custodial interrogation.',
            'matched_keywords' => ['rights', 'criminal procedure', 'police'],
            'tags' => ['Criminal Law', 'Constitutional Law', 'Police Procedure'],
            'relevance_score' => 0.82
        ]
    ];

    // Prepare response
    $response = [
        'status' => 'success',
        'query' => $query,
        'timestamp' => date('c'),
        'results' => $dummyResults
    ];

    // Send response
    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(400);
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