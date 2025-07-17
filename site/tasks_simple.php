<?php
/**
 * Simplified Tasks API for Testing
 */

// Start output buffering
ob_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configure headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
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
            // Return sample tasks
            $sampleTasks = [
                [
                    'task_id' => 1,
                    'user_id' => 1,
                    'title' => 'Constitutional Law Brief',
                    'description' => 'Marbury v. Madison case analysis - 3-5 pages',
                    'category' => 'brief',
                    'priority' => 'high',
                    'deadline' => '2025-07-03 23:59:59',
                    'completed' => 0,
                    'status' => 'due-soon',
                    'created_at' => '2025-07-01 10:00:00'
                ],
                [
                    'task_id' => 2,
                    'user_id' => 1,
                    'title' => 'Read Supreme Court Cases',
                    'description' => 'Review Brown v. Board of Education and Miranda v. Arizona',
                    'category' => 'reading',
                    'priority' => 'medium',
                    'deadline' => '2025-07-05 18:00:00',
                    'completed' => 0,
                    'status' => 'upcoming',
                    'created_at' => '2025-07-01 10:30:00'
                ],
                [
                    'task_id' => 3,
                    'user_id' => 1,
                    'title' => 'Contract Law Quiz',
                    'description' => 'Complete practice quiz on contract formation',
                    'category' => 'quiz',
                    'priority' => 'medium',
                    'deadline' => '2025-07-04 15:00:00',
                    'completed' => 0,
                    'status' => 'upcoming',
                    'created_at' => '2025-07-01 11:00:00'
                ]
            ];

            ob_clean();
            echo json_encode([
                'status' => 'success',
                'tasks' => $sampleTasks,
                'count' => count($sampleTasks),
                'user_id' => 1,
                'timestamp' => date('c')
            ], JSON_PRETTY_PRINT);
            break;

        case 'POST':
            // Simulate task creation
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['title'], $data['category'])) {
                throw new Exception('Invalid request data - title and category are required');
            }

            ob_clean();
            echo json_encode([
                'status' => 'success',
                'message' => 'Task created successfully',
                'task_id' => rand(100, 999),
                'timestamp' => date('c')
            ], JSON_PRETTY_PRINT);
            break;

        default:
            throw new Exception('Method not allowed: ' . $_SERVER['REQUEST_METHOD']);
    }

} catch (Exception $e) {
    ob_clean();
    error_log("Tasks error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    ], JSON_PRETTY_PRINT);
}
?>
