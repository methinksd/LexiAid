<?php
/**
 * LexiAid Tasks API Endpoint
 * Handles CRUD operations for user tasks
 */

require_once __DIR__ . '/config/database.php';

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configure CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Log requests for debugging
$logFile = __DIR__ . '/logs/tasks.log';
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
            // Get tasks for a user (use demo user if no user_id provided)
            $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 1; // Default to demo user
            if ($userId <= 0) {
                throw new Exception('Invalid user ID');
            }

            $query = "SELECT t.*, 
                        CASE 
                            WHEN t.deadline < NOW() THEN 'overdue'
                            WHEN t.deadline <= DATE_ADD(NOW(), INTERVAL 2 DAY) THEN 'due-soon'
                            ELSE 'upcoming'
                        END as status
                     FROM tasks t 
                     WHERE t.user_id = ? 
                     ORDER BY t.deadline ASC";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }
            
            $stmt->bind_param("i", $userId);
            if (!$stmt->execute()) {
                throw new Exception('Failed to execute query: ' . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $tasks = [];
            while ($row = $result->fetch_assoc()) {
                $tasks[] = $row;
            }
            
            echo json_encode([
                'status' => 'success',
                'tasks' => $tasks,
                'count' => count($tasks),
                'user_id' => $userId,
                'timestamp' => date('c')
            ]);
            break;

        case 'POST':
            // Create a new task
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['title'], $data['category'])) {
                throw new Exception('Invalid request data - title and category are required');
            }

            // Use demo user if no user_id provided
            $userId = isset($data['user_id']) ? (int)$data['user_id'] : 1;
            
            $query = "INSERT INTO tasks (user_id, title, description, category, priority, deadline) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }
            
            $stmt->bind_param(
                "isssss",
                $userId,
                $data['title'],
                $data['description'] ?? '',
                $data['category'],
                $data['priority'] ?? 'medium',
                $data['deadline'] ?? null
            );
            
            if ($stmt->execute()) {
                $taskId = $conn->insert_id;
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Task created successfully',
                    'task_id' => $taskId,
                    'timestamp' => date('c')
                ]);
            } else {
                throw new Exception('Failed to create task: ' . $stmt->error);
            }
            break;

        case 'PUT':
            // Update an existing task
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['task_id'])) {
                throw new Exception('Invalid request data - task_id is required');
            }

            // Use demo user if no user_id provided
            $userId = isset($data['user_id']) ? (int)$data['user_id'] : 1;

            $updates = [];
            $params = [];
            $types = "";

            // Build dynamic update query based on provided fields
            $allowedFields = ['title', 'description', 'category', 'priority', 'deadline', 'completed'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                    // Handle boolean values for completed field
                    if ($field === 'completed') {
                        $types .= "i";
                        $params[count($params)-1] = $data[$field] ? 1 : 0;
                    } else {
                        $types .= "s";
                    }
                }
            }

            if (empty($updates)) {
                throw new Exception('No fields to update');
            }

            $query = "UPDATE tasks SET " . implode(", ", $updates) . " WHERE task_id = ? AND user_id = ?";
            $types .= "ii";
            $params[] = $data['task_id'];
            $params[] = $userId;

            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }
            
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $affectedRows = $stmt->affected_rows;
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Task updated successfully',
                    'affected_rows' => $affectedRows,
                    'timestamp' => date('c')
                ]);
            } else {
                throw new Exception('Failed to update task: ' . $stmt->error);
            }
            break;

        case 'DELETE':
            // Delete a task
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['task_id'])) {
                throw new Exception('Invalid request data - task_id is required');
            }

            // Use demo user if no user_id provided
            $userId = isset($data['user_id']) ? (int)$data['user_id'] : 1;

            $query = "DELETE FROM tasks WHERE task_id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . $conn->error);
            }
            
            $stmt->bind_param("ii", $data['task_id'], $userId);
            
            if ($stmt->execute()) {
                $affectedRows = $stmt->affected_rows;
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Task deleted successfully',
                    'affected_rows' => $affectedRows,
                    'timestamp' => date('c')
                ]);
            } else {
                throw new Exception('Failed to delete task: ' . $stmt->error);
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
