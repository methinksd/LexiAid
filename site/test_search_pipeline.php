<?php
/**
 * Test script for LexiAid search pipeline
 * Tests the complete PHP -> Python -> JSON response flow
 */

// Include the search functionality
require_once 'search.php';

// Set headers for JSON response
header("Content-Type: application/json; charset=UTF-8");

// Test data
$testQueries = [
    "criminal law rights",
    "constitutional amendments",
    "search and seizure",
    "due process",
    "first amendment"
];

$results = [];

foreach ($testQueries as $query) {
    echo "Testing query: $query\n";
    
    // Simulate POST request data
    $_POST = [];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Simulate JSON input
    $testData = json_encode(['query' => $query, 'top_k' => 3]);
    
    // Test the search function directly
    try {
        $searchResults = performPythonSearch($query, 3);
        
        $results[] = [
            'query' => $query,
            'status' => 'success',
            'results' => $searchResults,
            'count' => count($searchResults)
        ];
        
        echo "✓ Success: Found " . count($searchResults) . " results\n";
        
    } catch (Exception $e) {
        $results[] = [
            'query' => $query,
            'status' => 'error',
            'message' => $e->getMessage()
        ];
        
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "\n=== COMPLETE TEST RESULTS ===\n";
echo json_encode($results, JSON_PRETTY_PRINT);
?>
