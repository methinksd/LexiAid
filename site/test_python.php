<?php
// Simple test script to verify Python integration
echo "Testing Python integration...\n";

$scriptPath = dirname(__DIR__) . '/python/simple_search.py';
echo "Script path: $scriptPath\n";
echo "Script exists: " . (file_exists($scriptPath) ? 'YES' : 'NO') . "\n";

$query = 'constitutional law';
$command = sprintf('python3 %s %s 2>&1', 
    escapeshellarg($scriptPath),
    escapeshellarg($query)
);

echo "Command: $command\n";

$output = shell_exec($command);
echo "Output: $output\n";

if ($output) {
    $json = json_decode($output, true);
    if ($json) {
        echo "JSON decoded successfully\n";
        echo "Results count: " . count($json['results']) . "\n";
    } else {
        echo "Failed to decode JSON\n";
    }
}
?>
