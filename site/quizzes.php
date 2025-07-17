<?php
/**
 * LexiAid Quizzes API Endpoint
 * Handles quiz performance tracking and statistics
 */

require_once __DIR__ . '/config/database.php';

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configure CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Log requests for debugging
$logFile = __DIR__ . '/logs/quizzes.log';
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0777, true);
}
$logEntry = date('Y-m-d H:i:s') . " | " . $_SERVER['REQUEST_METHOD'] . " | " . $_SERVER['REQUEST_URI'] . "\n";
file_put_contents($logFile, $logEntry, FILE_APPEND);

try {
    // Test database connectivity first
    $conn = getDbConnection();
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get quiz performance for a user (use demo user if no user_id provided)
            $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1; // Default to demo user
            if ($userId <= 0) {
                throw new Exception('Invalid user ID');
            }

            // Get overall performance stats
            $statsQuery = "SELECT 
                            COUNT(*) as total_quizzes,
                            ROUND(AVG(score), 2) as average_score,
                            MAX(score) as highest_score,
                            COUNT(CASE WHEN score >= 70 THEN 1 END) as quizzes_passed
                         FROM quizzes 
                         WHERE user_id = ?";
            
            $stmt = $conn->prepare($statsQuery);
            if (!$stmt) {
                throw new Exception('Failed to prepare stats query: ' . $conn->error);
            }
            
            $stmt->bind_param("i", $userId);
            if (!$stmt->execute()) {
                throw new Exception('Failed to execute stats query: ' . $stmt->error);
            }
            
            $stats = $stmt->get_result()->fetch_assoc();

            // Get recent quiz history
            $historyQuery = "SELECT quiz_id, topic, score, completed_at, 
                            CASE 
                                WHEN score >= 90 THEN 'excellent'
                                WHEN score >= 70 THEN 'pass'
                                ELSE 'needs_improvement'
                            END as performance
                           FROM quizzes 
                           WHERE user_id = ? 
                           ORDER BY completed_at DESC 
                           LIMIT 10";
            
            $stmt = $conn->prepare($historyQuery);
            if (!$stmt) {
                throw new Exception('Failed to prepare history query: ' . $conn->error);
            }
            
            $stmt->bind_param("i", $userId);
            if (!$stmt->execute()) {
                throw new Exception('Failed to execute history query: ' . $stmt->error);
            }
            
            $history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            echo json_encode([
                'status' => 'success',
                'statistics' => $stats,
                'recent_history' => $history,
                'user_id' => $userId,
                'timestamp' => date('c')
            ]);
            break;

        case 'POST':
            // Submit a new quiz result
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['topic'], $data['score'])) {
                throw new Exception('Invalid request data - topic and score are required');
            }

            // Use demo user if no user_id provided
            $userId = isset($data['user_id']) ? (int)$data['user_id'] : 1;

            // Validate score is between 0 and 100
            $score = floatval($data['score']);
            if ($score < 0 || $score > 100) {
                throw new Exception('Invalid score value - must be between 0 and 100');
            }

            $query = "INSERT INTO quizzes (user_id, topic, score, details, completed_at) 
                     VALUES (?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }
            
            $details = isset($data['details']) ? json_encode($data['details']) : null;
            
            $stmt->bind_param(
                "isds",
                $userId,
                $data['topic'],
                $score,
                $details
            );
            
            if ($stmt->execute()) {
                $quizId = $conn->insert_id;
                
                // Update user's related tasks if any
                if (isset($data['task_id'])) {
                    $taskQuery = "UPDATE tasks 
                                SET completed = 1, 
                                    completion_notes = CONCAT(COALESCE(completion_notes, ''), 
                                                           'Quiz completed with score: ', ?, '%\n')
                                WHERE task_id = ? AND user_id = ?";
                    
                    $taskStmt = $conn->prepare($taskQuery);
                    if ($taskStmt) {
                        $taskStmt->bind_param("dii", $score, $data['task_id'], $userId);
                        $taskStmt->execute();
                    }
                }

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Quiz result recorded successfully',
                    'quiz_id' => $quizId,
                    'score' => $score,
                    'timestamp' => date('c')
                ]);
            } else {
                throw new Exception('Failed to record quiz result: ' . $stmt->error);
            }
            break;

        default:
            throw new Exception('Method not allowed: ' . $_SERVER['REQUEST_METHOD']);
    }

} catch (Exception $e) {
    // Log the error
    $logEntry = date('Y-m-d H:i:s') . " | ERROR: " . $e->getMessage() . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
