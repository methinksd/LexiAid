<?php
// Simple test script to verify Python integration
echo "Testing Python integration...\n";

$scriptPath = dirname(__DIR__) . '/python/simple_search.py';
echo "Script path: $scriptPath\n";
echo "Script exists: " . (file_exists($scriptPath) ? 'YES' : 'NO') . "\n";

// Validate inputs
if (empty($scriptPath) || !file_exists($scriptPath)) {
    echo "Error: Script path is invalid or file does not exist\n";
    exit(1);
}

$query = 'constitutional law';
if (empty($query)) {
    echo "Error: Query cannot be empty\n";
    exit(1);
}

$command = sprintf('python3 %s %s 2>&1', 
    escapeshellarg($scriptPath),
    escapeshellarg($query)
);

echo "Command: $command\n";

$output = shell_exec($command);

// Check if shell_exec failed
if ($output === false) {
    echo "Error: Failed to execute command\n";
    exit(1);
}

echo "Output: $output\n";

if ($output) {
    $json = json_decode($output, true);
    if ($json) {
        echo "JSON decoded successfully\n";
        // Validate that results key exists and is an array
        if (isset($json['results']) && is_array($json['results'])) {
            echo "Results count: " . count($json['results']) . "\n";
        } else {
            echo "Warning: 'results' key missing or not an array\n";
            echo "Available keys: " . implode(', ', array_keys($json)) . "\n";
        }
    } else {
        echo "Failed to decode JSON\n";
        echo "JSON error: " . json_last_error_msg() . "\n";
    }
} else {
    echo "No output received from Python script\n";
}
?>
