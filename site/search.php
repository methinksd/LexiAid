<?php
/**
 * LexiAid Search API Endpoint
 * Handles semantic search requests and returns relevant legal documents
 */

// Start output buffering to catch any accidental output
ob_start();

// Include database configuration
require_once __DIR__ . '/config/database.php';

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

// Database-based search function
function performDatabaseSearch($query, $topK = 5) {
    try {
        $conn = getDbConnection();
        
        // Perform a basic text search on legal resources
        $searchQuery = "SELECT * FROM legal_resources 
                       WHERE title LIKE ? 
                       OR summary LIKE ? 
                       OR CAST(tags AS CHAR) LIKE ?
                       ORDER BY 
                         CASE 
                           WHEN title LIKE ? THEN 3
                           WHEN summary LIKE ? THEN 2  
                           WHEN CAST(tags AS CHAR) LIKE ? THEN 1
                           ELSE 0
                         END DESC
                       LIMIT ?";
        
        $searchTerm = "%$query%";
        $stmt = $conn->prepare($searchQuery);
        $stmt->bind_param("ssssssi", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $topK);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $results = [];
        while ($row = $result->fetch_assoc()) {
            // Calculate a simple relevance score based on matching
            $score = 0.5; // Base score
            if (stripos($row['title'], $query) !== false) $score += 0.3;
            if (stripos($row['summary'], $query) !== false) $score += 0.2;
            
            $results[] = [
                'title' => $row['title'],
                'summary' => $row['summary'],
                'similarity_score' => $score,
                'tags' => json_decode($row['tags']) ?: [],
                'year' => $row['year'],
                'citation' => $row['citation'],
                'jurisdiction' => $row['jurisdiction'],
                'type' => $row['type']
            ];
        }
        
        $conn->close();
        return $results;
        
    } catch (Exception $e) {
        error_log("Database search error: " . $e->getMessage());
        return performKeywordSearch($query, $topK); // Fallback to keyword search
    }
}

// Python-based semantic search function
function performPythonSearch($query, $topK = 5) {
    try {
        $logFile = __DIR__ . '/logs/search.log';
        
        // Path to Python script
        $scriptPath = dirname(__DIR__) . '/python/semantic_search.py';
        $fallbackScriptPath = dirname(__DIR__) . '/python/simple_search.py';
        
        // Use fallback script if main script doesn't exist or has issues
        if (!file_exists($scriptPath) || !is_readable($scriptPath)) {
            $scriptPath = $fallbackScriptPath;
        }
        
        if (!file_exists($scriptPath)) {
            error_log("Python script not found at: " . $scriptPath);
            return [];
        }
        
        // Try different Python executables
        $pythonPaths = [
            '/home/leo/Freelance Projects/LexiAid/.venv/bin/python',
            'python3',
            'python',
            '/usr/bin/python3',
            '/usr/bin/python'
        ];
        
        $pythonPath = '/home/leo/Freelance Projects/LexiAid/.venv/bin/python'; // Use virtual environment by default
        
        // Find working Python executable
        foreach ($pythonPaths as $path) {
            $testCommand = sprintf('"%s" --version 2>&1', $path);
            $testOutput = shell_exec($testCommand);
            if ($testOutput && strpos($testOutput, 'Python') !== false) {
                $pythonPath = $path;
                break;
            }
        }
        
        // Prepare command arguments safely
        $cmd = escapeshellarg($pythonPath) . " " . escapeshellarg($scriptPath) . " " . escapeshellarg($query) . " --top_k " . escapeshellarg(strval($topK));

        // Log the command for debugging
        $logEntry = date('Y-m-d H:i:s') . " | Executing Python Command: " . $cmd . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);

        // Execute command and capture output
        $output = shell_exec($cmd . " 2>&1");
        
        // Log the output for debugging
        $logEntry = date('Y-m-d H:i:s') . " | Python Output: " . ($output ?: 'No output') . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);

        if ($output !== null && trim($output) !== '') {
            // Clean up the output to extract JSON
            $lines = explode("\n", trim($output));
            $jsonLine = '';
            
            // Find the line that contains JSON (starts with { and ends with })
            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, '{') === 0 && strrpos($line, '}') === strlen($line) - 1) {
                    $jsonLine = $line;
                    break;
                }
            }
            
            if (!$jsonLine) {
                // If no single line, try to reconstruct JSON from multiple lines
                $jsonStart = strpos($output, '{');
                $jsonEnd = strrpos($output, '}');
                if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
                    $jsonLine = substr($output, $jsonStart, $jsonEnd - $jsonStart + 1);
                }
            }

            if ($jsonLine) {
                // Try to decode the JSON output
                $pythonResults = json_decode($jsonLine, true);
                
                if ($pythonResults !== null) {
                    if (isset($pythonResults['results']) && is_array($pythonResults['results'])) {
                        return $pythonResults['results'];
                    } elseif (isset($pythonResults['status']) && $pythonResults['status'] === 'error') {
                        error_log("Python script error: " . ($pythonResults['message'] ?? 'Unknown error'));
                    }
                }
            }
        }
        
        return [];
        
    } catch (Exception $e) {
        error_log("Python search error: " . $e->getMessage());
        return [];
    }
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
    $query = htmlspecialchars(strip_tags($data['query']), ENT_QUOTES, 'UTF-8');
    
    // Optional: number of results to return
    $topK = isset($data['top_k']) ? (int)$data['top_k'] : 5;
    
    // Log the search query for debugging
    $logFile = __DIR__ . '/logs/search.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    $logEntry = date('Y-m-d H:i:s') . " | Search Query: " . $query . " | Top K: " . $topK . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);

    // Try database search first
    $results = performDatabaseSearch($query, $topK);
    $searchMethod = 'database';
    
    // If database search returns no results, try Python script or fallback
    if (empty($results)) {
        // Try Python semantic search
        $results = performPythonSearch($query, $topK);
        $searchMethod = 'python';
        
        // Final fallback to keyword search if Python also fails
        if (empty($results)) {
            $results = performKeywordSearch($query, $topK);
            $searchMethod = 'fallback';
        }
    }

    // Return the search results
    ob_clean(); // Clear any accidental output
    echo json_encode([
        'status' => 'success',
        'results' => $results,
        'query' => $query,
        'count' => count($results),
        'timestamp' => date('c'),
        'search_method' => $searchMethod
    ]);

} catch (Exception $e) {
    // Clear any output
    ob_clean();
    
    // Log the error
    $logFile = __DIR__ . '/logs/search.log';
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    $logEntry = date('Y-m-d H:i:s') . " | Error: " . $e->getMessage() . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
}
?>