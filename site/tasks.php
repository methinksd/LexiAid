<?php
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

try {
    $conn = getDbConnection();
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get tasks for a user
            $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
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
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $tasks = [];
            while ($row = $result->fetch_assoc()) {
                $tasks[] = $row;
            }
            
            echo json_encode([
                'status' => 'success',
                'tasks' => $tasks
            ]);
            break;

        case 'POST':
            // Create a new task
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['user_id'], $data['title'], $data['category'])) {
                throw new Exception('Invalid request data');
            }

            $query = "INSERT INTO tasks (user_id, title, description, category, priority, deadline) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                "isssss",
                $data['user_id'],
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
                    'task_id' => $taskId
                ]);
            } else {
                throw new Exception('Failed to create task');
            }
            break;

        case 'PUT':
            // Update an existing task
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['task_id'])) {
                throw new Exception('Invalid request data');
            }

            $updates = [];
            $params = [];
            $types = "";

            // Build dynamic update query based on provided fields
            $allowedFields = ['title', 'description', 'category', 'priority', 'deadline', 'completed'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                    $types .= "s";
                }
            }

            if (empty($updates)) {
                throw new Exception('No fields to update');
            }

            $query = "UPDATE tasks SET " . implode(", ", $updates) . " WHERE task_id = ? AND user_id = ?";
            $types .= "ii";
            $params[] = $data['task_id'];
            $params[] = $data['user_id'];

            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Task updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update task');
            }
            break;

        case 'DELETE':
            // Delete a task
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data || !isset($data['task_id'], $data['user_id'])) {
                throw new Exception('Invalid request data');
            }

            $query = "DELETE FROM tasks WHERE task_id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $data['task_id'], $data['user_id']);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Task deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete task');
            }
            break;

        default:
            throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
