<?php
/**
 * Simplified Search API for Testing
 */

// Start output buffering
ob_start();

// Error reporting configuration
$isProduction = (getenv('APP_ENV') === 'production');
error_reporting(E_ALL);
ini_set('display_errors', $isProduction ? 0 : 1);
ini_set('log_errors', 1);

// Configure headers
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
    ob_clean();
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
    $query = htmlspecialchars(strip_tags($data['query']), ENT_QUOTES, 'UTF-8');
    $topK = isset($data['top_k']) ? (int)$data['top_k'] : 5;

    // Sample legal documents for testing
    $sampleDocs = [
        [
            'title' => 'Miranda v. Arizona',
            'summary' => 'Established that police must inform suspects of their rights before custodial interrogation.',
            'similarity_score' => 0.85,
            'tags' => ['Criminal Law', 'Constitutional Law'],
            'year' => 1966,
            'citation' => '384 U.S. 436 (1966)',
            'jurisdiction' => 'Federal',
            'type' => 'Supreme Court Case'
        ],
        [
            'title' => 'Brown v. Board of Education',
            'summary' => 'Ruled that racial segregation in public schools is unconstitutional.',
            'similarity_score' => 0.75,
            'tags' => ['Civil Rights', 'Constitutional Law'],
            'year' => 1954,
            'citation' => '347 U.S. 483 (1954)',
            'jurisdiction' => 'Federal',
            'type' => 'Supreme Court Case'
        ],
        [
            'title' => 'Gideon v. Wainwright',
            'summary' => 'Established right to counsel for criminal defendants who cannot afford an attorney.',
            'similarity_score' => 0.70,
            'tags' => ['Criminal Law', 'Constitutional Law'],
            'year' => 1963,
            'citation' => '372 U.S. 335 (1963)',
            'jurisdiction' => 'Federal',
            'type' => 'Supreme Court Case'
        ]
    ];
    
    // Simple keyword matching
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
    
    $results = array_slice($results, 0, $topK);

    // Clear output buffer
    ob_clean();

    // Return the search results
    echo json_encode([
        'status' => 'success',
        'results' => $results,
        'query' => $query,
        'count' => count($results),
        'search_method' => 'fallback_keyword',
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Clear output buffer
    ob_clean();
    
    error_log("Search error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>
