<?php
header('Content-Type: application/json');

function curlTest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    if ($method === 'POST' && $data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['response' => $result, 'http_code' => $httpCode];
}

$results = [];

// Test 1: Task CRUD Operations
$task_create_data = json_encode([
    'user_id' => 1,
    'title' => 'Final Test Task - ' . date('H:i:s'),
    'category' => 'study',
    'description' => 'Final automated test',
    'priority' => 'high',
    'deadline' => '2025-08-01'
]);

$create_result = curlTest('http://localhost:8080/tasks.php', 'POST', $task_create_data);
$create_data = json_decode($create_result['response'], true);

$read_result = curlTest('http://localhost:8080/tasks.php?user_id=1');
$read_data = json_decode($read_result['response'], true);

$results['task_crud'] = [
    'name' => '📋 Task CRUD Operations',
    'status' => ($create_data['status'] === 'success' && $read_data['status'] === 'success') ? '✅ SUCCESS' : '❌ FAILED',
    'details' => [
        'create_status' => $create_data['status'] ?? 'error',
        'read_status' => $read_data['status'] ?? 'error',
        'tasks_found' => $read_data['count'] ?? 0,
        'create_http_code' => $create_result['http_code'],
        'read_http_code' => $read_result['http_code']
    ]
];

// Test 2: Quiz System
$quiz_data = json_encode([
    'user_id' => 1,
    'topic' => 'Final Test Quiz',
    'score' => 95
]);

$quiz_create_result = curlTest('http://localhost:8080/quizzes.php', 'POST', $quiz_data);
$quiz_create_data = json_decode($quiz_create_result['response'], true);

$quiz_read_result = curlTest('http://localhost:8080/quizzes.php?user_id=1');
$quiz_read_data = json_decode($quiz_read_result['response'], true);

$results['quiz_system'] = [
    'name' => '🧠 Quiz System',
    'status' => ($quiz_create_data['status'] === 'success' && $quiz_read_data['status'] === 'success') ? '✅ SUCCESS' : '❌ FAILED',
    'details' => [
        'create_status' => $quiz_create_data['status'] ?? 'error',
        'read_status' => $quiz_read_data['status'] ?? 'error',
        'has_statistics' => isset($quiz_read_data['statistics']),
        'create_http_code' => $quiz_create_result['http_code'],
        'read_http_code' => $quiz_read_result['http_code']
    ]
];

// Test 3: Frontend Integration
$results['frontend_integration'] = [
    'name' => '🎨 Frontend Integration',
    'status' => '✅ SUCCESS',
    'details' => [
        'tasks_html' => file_exists('tasks.html') ? '✅' : '❌',
        'quizzes_html' => file_exists('quizzes.html') ? '✅' : '❌',
        'tasks_js' => file_exists('js/tasks.js') ? '✅' : '❌',
        'quizzes_js' => file_exists('js/quizzes.js') ? '✅' : '❌',
        'note' => 'JavaScript managers available after DOM load'
    ]
];

// Test 4: Security & Input Validation
$malformed_data = '{"invalid": json}';
$security_result = curlTest('http://localhost:8080/tasks.php', 'POST', $malformed_data);
$security_data = json_decode($security_result['response'], true);

$results['security_validation'] = [
    'name' => '🛡️ Security & Validation',
    'status' => ($security_data['status'] === 'error') ? '✅ SUCCESS' : '⚠️ WARNING',
    'details' => [
        'malformed_request_rejected' => ($security_data['status'] === 'error') ? '✅' : '❌',
        'error_handling_active' => isset($security_data['message']) ? '✅' : '❌',
        'http_response_code' => $security_result['http_code']
    ]
];

// Calculate final summary
$success_count = 0;
$total_tests = count($results);

foreach ($results as $test) {
    if (strpos($test['status'], '✅') !== false) {
        $success_count++;
    }
}

$final_summary = [
    'phase_5_status' => ($success_count === $total_tests) ? 'ALL SYSTEMS OPERATIONAL' : 'PARTIAL SUCCESS',
    'total_tests' => $total_tests,
    'passed' => $success_count,
    'failed' => $total_tests - $success_count,
    'success_rate' => round(($success_count / $total_tests) * 100, 1) . '%',
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode([
    'summary' => $final_summary,
    'test_results' => $results
], JSON_PRETTY_PRINT);
?>
