<?php
// upload_case.php
// Receives legal case content via POST, calls Python scripts for brief generation and tagging, and stores results in MySQL.

header('Content-Type: application/json');

// --- CONFIG ---
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'lexiaid';
$python_dir = __DIR__ . '/../python/';
$brief_script = escapeshellcmd($python_dir . 'brief_generator.py');
$tag_script = escapeshellcmd($python_dir . 'auto_tag.py');
$python_bin = escapeshellcmd(__DIR__ . '/../.venv/Scripts/python.exe');

// --- INPUT ---
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['content']) || strlen(trim($input['content'])) < 30) {
    echo json_encode(['status' => 'error', 'message' => 'Case content is required and must be at least 30 characters.']);
    exit;
}
$content = $input['content'];
$title = isset($input['title']) ? trim($input['title']) : '';

// --- RUN PYTHON SCRIPTS ---
function run_python($script, $arg) {
    global $python_bin;
    $cmd = "$python_bin $script --text " . escapeshellarg($arg);
    exec($cmd, $output, $ret);
    $result = implode("\n", $output);
    return json_decode($result, true);
}

$brief_result = run_python($brief_script, $content);
$tag_result = run_python($tag_script, $content);

if ($brief_result['status'] !== 'ok' || $tag_result['status'] !== 'ok') {
    echo json_encode(['status' => 'error', 'message' => 'NLP processing failed.', 'brief' => $brief_result, 'tags' => $tag_result]);
    exit;
}

$brief = $brief_result['brief'];
$categories = $tag_result['categories'];
$tags = $tag_result['tags'];

// --- STORE IN MYSQL ---
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}
$stmt = $conn->prepare("INSERT INTO legal_resources (title, content, summary, tags, category) VALUES (?, ?, ?, ?, ?)");
$summary = json_encode($brief, JSON_UNESCAPED_UNICODE);
$tags_str = implode(',', $tags);
$category_str = implode(',', $categories);
$stmt->bind_param('sssss', $title, $content, $summary, $tags_str, $category_str);
$ok = $stmt->execute();
$stmt->close();
$conn->close();

if ($ok) {
    echo json_encode(['status' => 'ok', 'message' => 'Case uploaded and processed.', 'brief' => $brief, 'categories' => $categories, 'tags' => $tags]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save to database.']);
}
