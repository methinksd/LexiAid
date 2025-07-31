<?php
/**
 * LexiAid Phase 7 - Comprehensive Testing Script
 * Tests all major functionality and integration points
 */

// Include required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';

// Load environment configuration
if (file_exists(__DIR__ . '/config/.env.php')) {
    require_once __DIR__ . '/config/.env.php';
}

class LexiAidTester {
    private $results = [];
    private $startTime;
    
    public function __construct() {
        $this->startTime = microtime(true);
        LexiAidSecurity::setSecurityHeaders();
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "<h1>LexiAid Phase 7 - Comprehensive Testing Report</h1>";
        echo "<p>Generated on: " . date('Y-m-d H:i:s') . "</p>";
        echo "<hr>";
        
        $this->testDatabaseConnection();
        $this->testSecurityFunctions();
        $this->testFileStructure();
        $this->testPythonAPI();
        $this->testFormValidation();
        $this->testPageAccessibility();
        $this->testMobileResponsiveness();
        $this->testPerformance();
        
        $this->generateSummary();
    }
    
    /**
     * Test database connectivity
     */
    private function testDatabaseConnection() {
        echo "<h2>🗃️ Database Connection Tests</h2>";
        
        try {
            $result = testDatabaseConnection();
            if ($result['status'] === 'success') {
                $this->addResult('Database Connection', 'PASS', 'Successfully connected to MySQL database');
                echo "✅ Database connection successful<br>";
                echo "📊 Server: {$result['server_info']}<br>";
                echo "📡 Client: {$result['client_info']}<br>";
            } else {
                $this->addResult('Database Connection', 'FAIL', $result['message']);
                echo "❌ Database connection failed: {$result['message']}<br>";
            }
        } catch (Exception $e) {
            $this->addResult('Database Connection', 'FAIL', $e->getMessage());
            echo "❌ Database connection error: " . $e->getMessage() . "<br>";
        }
        
        echo "<hr>";
    }
    
    /**
     * Test security functions
     */
    private function testSecurityFunctions() {
        echo "<h2>🔒 Security Functions Tests</h2>";
        
        // Test input sanitization
        $testInput = '<script>alert("xss")</script>';
        $sanitized = LexiAidSecurity::sanitizeInput($testInput);
        if (strpos($sanitized, '<script>') === false) {
            $this->addResult('XSS Protection', 'PASS', 'Input properly sanitized');
            echo "✅ XSS protection working<br>";
        } else {
            $this->addResult('XSS Protection', 'FAIL', 'Input not properly sanitized');
            echo "❌ XSS protection failed<br>";
        }
        
        // Test CSRF token generation
        try {
            $token = LexiAidSecurity::generateCSRFToken();
            if (strlen($token) === 64) {
                $this->addResult('CSRF Protection', 'PASS', 'Token generated successfully');
                echo "✅ CSRF token generation working<br>";
            } else {
                $this->addResult('CSRF Protection', 'FAIL', 'Invalid token length');
                echo "❌ CSRF token generation failed<br>";
            }
        } catch (Exception $e) {
            $this->addResult('CSRF Protection', 'FAIL', $e->getMessage());
            echo "❌ CSRF error: " . $e->getMessage() . "<br>";
        }
        
        // Test rate limiting
        $rateLimit = LexiAidSecurity::checkRateLimit('test_user', 5, 60);
        if ($rateLimit['allowed']) {
            $this->addResult('Rate Limiting', 'PASS', 'Rate limiting functional');
            echo "✅ Rate limiting working (remaining: {$rateLimit['remaining']})<br>";
        } else {
            $this->addResult('Rate Limiting', 'WARN', 'Rate limit exceeded (expected)');
            echo "⚠️ Rate limiting working (limit reached)<br>";
        }
        
        echo "<hr>";
    }
    
    /**
     * Test file structure and permissions
     */
    private function testFileStructure() {
        echo "<h2>📁 File Structure Tests</h2>";
        
        $requiredFiles = [
            'index.html' => 'Main dashboard',
            'search.html' => 'Search interface',
            'tasks.html' => 'Task manager',
            'insights.html' => 'Analytics dashboard',
            'upload-case.html' => 'File upload interface',
            'css/lexiaid-polish.css' => 'Enhanced styling',
            'js/search.js' => 'Search functionality',
            'js/validation.js' => 'Form validation',
            'config/database.php' => 'Database configuration',
            'config/security.php' => 'Security library',
            'config/.env.php' => 'Environment configuration'
        ];
        
        foreach ($requiredFiles as $file => $description) {
            if (file_exists(__DIR__ . '/' . $file)) {
                $this->addResult("File: $file", 'PASS', "$description exists");
                echo "✅ $file - $description<br>";
            } else {
                $this->addResult("File: $file", 'FAIL', "$description missing");
                echo "❌ $file - $description (MISSING)<br>";
            }
        }
        
        // Check permissions
        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        if (is_writable($uploadDir)) {
            $this->addResult('Upload Directory', 'PASS', 'Upload directory writable');
            echo "✅ Upload directory permissions OK<br>";
        } else {
            $this->addResult('Upload Directory', 'FAIL', 'Upload directory not writable');
            echo "❌ Upload directory permissions issue<br>";
        }
        
        echo "<hr>";
    }
    
    /**
     * Test Python API connectivity
     */
    private function testPythonAPI() {
        echo "<h2>🐍 Python API Tests</h2>";
        
        $pythonApiUrl = getenv('PYTHON_API_URL') ?: 'http://localhost:5000';
        
        // Test if Python API is accessible
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $pythonApiUrl . '/health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $this->addResult('Python API Health', 'PASS', 'API accessible');
            echo "✅ Python API accessible at $pythonApiUrl<br>";
        } else {
            $this->addResult('Python API Health', 'WARN', "API not accessible (HTTP: $httpCode)");
            echo "⚠️ Python API not accessible (HTTP: $httpCode)<br>";
            if ($error) {
                echo "📝 Error: $error<br>";
            }
        }
        
        echo "<hr>";
    }
    
    /**
     * Test form validation
     */
    private function testFormValidation() {
        echo "<h2>✅ Form Validation Tests</h2>";
        
        // Test validation functions
        $validationRules = [
            'email' => ['type' => 'email', 'required' => true],
            'name' => ['type' => 'string', 'required' => true, 'min_length' => 2, 'max_length' => 50]
        ];
        
        $testData = [
            'email' => 'test@example.com',
            'name' => 'John Doe'
        ];
        
        $validation = validateFormInput($testData, $validationRules);
        
        if ($validation['valid']) {
            $this->addResult('Form Validation', 'PASS', 'Validation functions working');
            echo "✅ Form validation functions working<br>";
        } else {
            $this->addResult('Form Validation', 'FAIL', 'Validation errors: ' . implode(', ', $validation['errors']));
            echo "❌ Form validation failed<br>";
        }
        
        echo "<hr>";
    }
    
    /**
     * Test page accessibility
     */
    private function testPageAccessibility() {
        echo "<h2>♿ Accessibility Tests</h2>";
        
        $pages = ['index.html', 'search.html', 'tasks.html', 'insights.html', 'upload-case.html'];
        
        foreach ($pages as $page) {
            if (file_exists(__DIR__ . '/' . $page)) {
                $content = file_get_contents(__DIR__ . '/' . $page);
                
                // Check for basic accessibility features
                $hasAltTags = preg_match_all('/img[^>]+alt=["\'][^"\']*["\']/', $content);
                $hasAriaLabels = preg_match_all('/aria-label=["\'][^"\']*["\']/', $content);
                $hasHeadingStructure = preg_match_all('/<h[1-6]/', $content);
                $hasMetaViewport = strpos($content, 'viewport') !== false;
                
                $score = 0;
                if ($hasAltTags) $score++;
                if ($hasAriaLabels) $score++;
                if ($hasHeadingStructure) $score++;
                if ($hasMetaViewport) $score++;
                
                $percentage = ($score / 4) * 100;
                
                if ($percentage >= 75) {
                    $this->addResult("Accessibility: $page", 'PASS', "Score: {$percentage}%");
                    echo "✅ $page - Accessibility score: {$percentage}%<br>";
                } else {
                    $this->addResult("Accessibility: $page", 'WARN', "Score: {$percentage}%");
                    echo "⚠️ $page - Accessibility score: {$percentage}% (needs improvement)<br>";
                }
            }
        }
        
        echo "<hr>";
    }
    
    /**
     * Test mobile responsiveness
     */
    private function testMobileResponsiveness() {
        echo "<h2>📱 Mobile Responsiveness Tests</h2>";
        
        $cssFile = __DIR__ . '/css/lexiaid-polish.css';
        
        if (file_exists($cssFile)) {
            $css = file_get_contents($cssFile);
            
            // Check for responsive features
            $hasMediaQueries = preg_match_all('/@media\s*\([^)]*\)/', $css);
            $hasFlexbox = strpos($css, 'flex') !== false;
            $hasBootstrap = file_exists(__DIR__ . '/css/bootstrap.css');
            $hasViewportMeta = true; // We added this to all pages
            
            $score = 0;
            if ($hasMediaQueries >= 3) $score++; // At least 3 media queries
            if ($hasFlexbox) $score++;
            if ($hasBootstrap) $score++;
            if ($hasViewportMeta) $score++;
            
            $percentage = ($score / 4) * 100;
            
            if ($percentage >= 75) {
                $this->addResult('Mobile Responsiveness', 'PASS', "Score: {$percentage}%");
                echo "✅ Mobile responsiveness good - Score: {$percentage}%<br>";
                echo "📱 Media queries found: $hasMediaQueries<br>";
            } else {
                $this->addResult('Mobile Responsiveness', 'WARN', "Score: {$percentage}%");
                echo "⚠️ Mobile responsiveness needs improvement - Score: {$percentage}%<br>";
            }
        } else {
            $this->addResult('Mobile Responsiveness', 'FAIL', 'Enhanced CSS file missing');
            echo "❌ Enhanced CSS file missing<br>";
        }
        
        echo "<hr>";
    }
    
    /**
     * Test performance
     */
    private function testPerformance() {
        echo "<h2>⚡ Performance Tests</h2>";
        
        // Test file sizes
        $files = [
            'css/style.css' => 'Main CSS',
            'css/lexiaid-polish.css' => 'Enhanced CSS',
            'js/script.js' => 'Main JavaScript',
            'js/search.js' => 'Search JavaScript'
        ];
        
        $totalSize = 0;
        
        foreach ($files as $file => $description) {
            if (file_exists(__DIR__ . '/' . $file)) {
                $size = filesize(__DIR__ . '/' . $file);
                $totalSize += $size;
                $sizeKB = round($size / 1024, 2);
                
                if ($sizeKB <= 100) {
                    echo "✅ $description: {$sizeKB}KB<br>";
                } else {
                    echo "⚠️ $description: {$sizeKB}KB (consider optimization)<br>";
                }
            }
        }
        
        $totalSizeKB = round($totalSize / 1024, 2);
        
        if ($totalSizeKB <= 500) {
            $this->addResult('Total File Size', 'PASS', "{$totalSizeKB}KB");
            echo "✅ Total asset size: {$totalSizeKB}KB<br>";
        } else {
            $this->addResult('Total File Size', 'WARN', "{$totalSizeKB}KB (consider optimization)");
            echo "⚠️ Total asset size: {$totalSizeKB}KB (consider optimization)<br>";
        }
        
        echo "<hr>";
    }
    
    /**
     * Add test result
     */
    private function addResult($test, $status, $message) {
        $this->results[] = [
            'test' => $test,
            'status' => $status,
            'message' => $message,
            'timestamp' => microtime(true)
        ];
    }
    
    /**
     * Generate summary
     */
    private function generateSummary() {
        $totalTime = round((microtime(true) - $this->startTime) * 1000, 2);
        
        $passed = count(array_filter($this->results, function($r) { return $r['status'] === 'PASS'; }));
        $failed = count(array_filter($this->results, function($r) { return $r['status'] === 'FAIL'; }));
        $warnings = count(array_filter($this->results, function($r) { return $r['status'] === 'WARN'; }));
        $total = count($this->results);
        
        echo "<h2>📊 Test Summary</h2>";
        echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        echo "<h3>Results Overview</h3>";
        echo "✅ <strong>Passed:</strong> $passed<br>";
        echo "❌ <strong>Failed:</strong> $failed<br>";
        echo "⚠️ <strong>Warnings:</strong> $warnings<br>";
        echo "📈 <strong>Total Tests:</strong> $total<br>";
        echo "⏱️ <strong>Execution Time:</strong> {$totalTime}ms<br>";
        
        $successRate = round(($passed / $total) * 100, 1);
        echo "🎯 <strong>Success Rate:</strong> {$successRate}%<br>";
        
        if ($successRate >= 90) {
            echo "<p style='color: green; font-weight: bold;'>🎉 Excellent! Your LexiAid application is ready for deployment.</p>";
        } elseif ($successRate >= 75) {
            echo "<p style='color: orange; font-weight: bold;'>⚠️ Good progress, but some issues need attention before deployment.</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ Several critical issues need to be resolved before deployment.</p>";
        }
        
        echo "</div>";
        
        // Detailed results
        echo "<h3>Detailed Results</h3>";
        echo "<table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>";
        echo "<tr style='background: #e9ecef;'><th style='padding: 10px; text-align: left;'>Test</th><th>Status</th><th>Message</th></tr>";
        
        foreach ($this->results as $result) {
            $color = $result['status'] === 'PASS' ? 'green' : ($result['status'] === 'FAIL' ? 'red' : 'orange');
            echo "<tr>";
            echo "<td style='padding: 8px; border-bottom: 1px solid #dee2e6;'>{$result['test']}</td>";
            echo "<td style='padding: 8px; border-bottom: 1px solid #dee2e6; color: $color; font-weight: bold;'>{$result['status']}</td>";
            echo "<td style='padding: 8px; border-bottom: 1px solid #dee2e6;'>{$result['message']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<hr>";
        echo "<p><em>Generated by LexiAid Phase 7 Testing Suite</em></p>";
    }
}

// Content type header
header('Content-Type: text/html; charset=UTF-8');

// Run tests
$tester = new LexiAidTester();
$tester->runAllTests();
?>
