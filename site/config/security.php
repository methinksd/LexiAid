<?php
/**
 * LexiAid Phase 7 - Security and Validation Library
 * Enhanced security functions for input validation, sanitization, and protection
 */

class LexiAidSecurity {
    
    /**
     * Sanitize input to prevent XSS attacks
     */
    public static function sanitizeInput($input, $type = 'string') {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        // Basic sanitization
        $input = trim($input);
        $input = stripslashes($input);
        
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'html':
                return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            case 'string':
            default:
                return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
    
    /**
     * Validate input based on type and rules
     */
    public static function validateInput($input, $type, $rules = []) {
        switch ($type) {
            case 'email':
                if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                    return ['valid' => false, 'message' => 'Invalid email format'];
                }
                break;
                
            case 'url':
                if (!filter_var($input, FILTER_VALIDATE_URL)) {
                    return ['valid' => false, 'message' => 'Invalid URL format'];
                }
                break;
                
            case 'int':
                if (!filter_var($input, FILTER_VALIDATE_INT)) {
                    return ['valid' => false, 'message' => 'Must be a valid integer'];
                }
                if (isset($rules['min']) && $input < $rules['min']) {
                    return ['valid' => false, 'message' => "Must be at least {$rules['min']}"];
                }
                if (isset($rules['max']) && $input > $rules['max']) {
                    return ['valid' => false, 'message' => "Must be no more than {$rules['max']}"];
                }
                break;
                
            case 'string':
                if (isset($rules['min_length']) && strlen($input) < $rules['min_length']) {
                    return ['valid' => false, 'message' => "Must be at least {$rules['min_length']} characters"];
                }
                if (isset($rules['max_length']) && strlen($input) > $rules['max_length']) {
                    return ['valid' => false, 'message' => "Must be no more than {$rules['max_length']} characters"];
                }
                if (isset($rules['pattern']) && !preg_match($rules['pattern'], $input)) {
                    return ['valid' => false, 'message' => $rules['pattern_message'] ?? 'Invalid format'];
                }
                break;
                
            case 'file':
                return self::validateFile($input, $rules);
        }
        
        return ['valid' => true, 'message' => 'Valid'];
    }
    
    /**
     * Validate uploaded files
     */
    public static function validateFile($file, $rules = []) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'message' => 'No file uploaded or invalid upload'];
        }
        
        // Check file size
        $maxSize = $rules['max_size'] ?? (50 * 1024 * 1024); // 50MB default
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'message' => 'File size exceeds maximum allowed'];
        }
        
        // Check file type
        $allowedTypes = $rules['allowed_types'] ?? ['pdf', 'doc', 'docx', 'txt'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $allowedTypes)) {
            return ['valid' => false, 'message' => 'File type not allowed'];
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain'
        ];
        
        if (!in_array($mimeType, array_values($allowedMimes))) {
            return ['valid' => false, 'message' => 'Invalid file type detected'];
        }
        
        return ['valid' => true, 'message' => 'Valid file'];
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // Token expires after 1 hour
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Rate limiting check
     */
    public static function checkRateLimit($identifier, $maxRequests = 100, $timeWindow = 3600) {
        $cacheFile = sys_get_temp_dir() . '/lexiaid_rate_limit_' . md5($identifier) . '.json';
        
        $requests = [];
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            $requests = $data['requests'] ?? [];
        }
        
        $now = time();
        
        // Remove old requests outside the time window
        $requests = array_filter($requests, function($timestamp) use ($now, $timeWindow) {
            return ($now - $timestamp) < $timeWindow;
        });
        
        // Check if limit exceeded
        if (count($requests) >= $maxRequests) {
            return ['allowed' => false, 'message' => 'Rate limit exceeded'];
        }
        
        // Add current request
        $requests[] = $now;
        
        // Save updated requests
        file_put_contents($cacheFile, json_encode(['requests' => $requests]));
        
        return ['allowed' => true, 'remaining' => $maxRequests - count($requests)];
    }
    
    /**
     * Set security headers
     */
    public static function setSecurityHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Prevent MIME sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self';");
        
        // HSTS (if using HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
    
    /**
     * Sanitize JSON input
     */
    public static function sanitizeJSON($json) {
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON input');
        }
        
        return self::sanitizeInput($data);
    }
    
    /**
     * Generate secure random password
     */
    public static function generateSecurePassword($length = 12) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }
    
    /**
     * Hash password securely
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Encrypt sensitive data
     */
    public static function encryptData($data, $key = null) {
        if ($key === null) {
            $key = getenv('SECRET_KEY') ?: 'default_key_change_in_production';
        }
        
        $method = getenv('ENCRYPTION_METHOD') ?: 'AES-256-CBC';
        $iv = random_bytes(openssl_cipher_iv_length($method));
        $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     */
    public static function decryptData($encryptedData, $key = null) {
        if ($key === null) {
            $key = getenv('SECRET_KEY') ?: 'default_key_change_in_production';
        }
        
        $method = getenv('ENCRYPTION_METHOD') ?: 'AES-256-CBC';
        $data = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length($method);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        return openssl_decrypt($encrypted, $method, $key, 0, $iv);
    }
}

/**
 * Helper function to quickly sanitize and validate form input
 */
function validateFormInput($postData, $rules) {
    $errors = [];
    $sanitized = [];
    
    foreach ($rules as $field => $rule) {
        $value = $postData[$field] ?? '';
        
        // Sanitize
        $sanitized[$field] = LexiAidSecurity::sanitizeInput($value, $rule['type'] ?? 'string');
        
        // Validate
        $validation = LexiAidSecurity::validateInput($sanitized[$field], $rule['type'] ?? 'string', $rule);
        
        if (!$validation['valid']) {
            $errors[$field] = $validation['message'];
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $sanitized
    ];
}
?>
