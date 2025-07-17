<?php
/**
 * Simplified Quizzes API for Testing
 */

// Start output buffering
ob_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configure headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Return sample quiz data
            $sampleStats = [
                'total_quizzes' => 3,
                'average_score' => 85.33,
                'highest_score' => 92.0,
                'quizzes_passed' => 3
            ];

            $sampleHistory = [
                [
                    'quiz_id' => 1,
                    'topic' => 'Constitutional Law',
                    'score' => 85.5,
                    'completed_at' => '2025-07-01 14:30:00',
                    'performance' => 'pass'
                ],
                [
                    'quiz_id' => 2,
                    'topic' => 'Contract Law',
                    'score' => 92.0,
                    'completed_at' => '2025-06-30 16:45:00',
                    'performance' => 'excellent'
                ],
                [
                    'quiz_id' => 3,
                    'topic' => 'Criminal Procedure',
                    'score' => 78.5,
                    'completed_at' => '2025-06-29 11:20:00',
                    'performance' => 'pass'
                ]
            ];

            ob_clean();
            echo json_encode([
                'status' => 'success',
                'statistics' => $sampleStats,
                'recent_history' => $sampleHistory,
                'user_id' => 1,
                'timestamp' => date('c')
            ], JSON_PRETTY_PRINT);
            break;

        case 'POST':
            // Simulate quiz submission
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['topic'], $data['score'])) {
                throw new Exception('Invalid request data - topic and score are required');
            }

            $score = floatval($data['score']);
            if ($score < 0 || $score > 100) {
                throw new Exception('Invalid score value - must be between 0 and 100');
            }

            ob_clean();
            echo json_encode([
                'status' => 'success',
                'message' => 'Quiz result recorded successfully',
                'quiz_id' => rand(100, 999),
                'score' => $score,
                'timestamp' => date('c')
            ], JSON_PRETTY_PRINT);
            break;

        default:
            throw new Exception('Method not allowed: ' . $_SERVER['REQUEST_METHOD']);
    }

} catch (Exception $e) {
    ob_clean();
    error_log("Quizzes error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>
