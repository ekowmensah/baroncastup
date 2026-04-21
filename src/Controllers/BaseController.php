<?php

namespace SmartCast\Controllers;

use SmartCast\Core\Session;

/**
 * Base Controller Class
 */
abstract class BaseController
{
    protected $session;
    
    public function __construct()
    {
        $this->session = new Session();
    }
    
    protected function view($template, $data = [])
    {
        // Extract data to variables
        extract($data);
        
        // Include the template
        $templatePath = __DIR__ . '/../../views/' . $template . '.php';
        
        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            throw new \Exception("Template not found: {$template}");
        }
    }
    
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect($path, $message = null, $type = 'info')
    {
        if ($message) {
            $this->session->flash($type, $message);
        }
        
        // Check if path is already a full URL
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            $redirectUrl = $path;
        } else {
            // Handle relative paths
            if (!$this->isModRewriteEnabled() && $path !== '/') {
                $redirectUrl = APP_URL . '/index.php' . $path;
            } else {
                $redirectUrl = APP_URL . $path;
            }
        }
        
        header("Location: " . $redirectUrl);
        exit;
    }
    
    private function isModRewriteEnabled()
    {
        // Simple check - if .htaccess exists and we can detect Apache modules
        if (!file_exists(__DIR__ . '/../../.htaccess')) {
            return false;
        }
        
        if (function_exists('apache_get_modules')) {
            return in_array('mod_rewrite', apache_get_modules());
        }
        
        // Default to true if we can't detect
        return true;
    }
    
    protected function requireAuth()
    {
        if (!$this->session->isLoggedIn()) {
            $this->redirect('/login');
        }
    }
    
    /**
     * Validate CSRF token on POST requests
     * Returns true if valid, redirects back with error if invalid
     */
    protected function validateCsrf($redirectUrl = null)
    {
        $token = $_POST['_csrf_token'] ?? '';
        if (!$this->session->validateCsrfToken($token)) {
            $url = $redirectUrl ?? ($_SERVER['HTTP_REFERER'] ?? '/');
            $this->redirect($url, 'Invalid or expired form submission. Please try again.', 'error');
            return false;
        }
        return true;
    }
    protected function requireRole($roles)
    {
        $this->requireAuth();
        
        $userRole = $this->session->getUserRole();
        
        if (is_string($roles)) {
            $roles = [$roles];
        }
        
        if (!in_array($userRole, $roles)) {
            $this->redirect('/', 'Access denied', 'error');
        }
    }
    
    protected function getCurrentUser()
    {
        if (!$this->session->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $this->session->getUserId(),
            'role' => $this->session->getUserRole(),
            'tenant_id' => $this->session->getTenantId(),
            'email' => $this->session->get('user_email')
        ];
    }
    
    protected function validateInput($data, $rules)
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
                continue;
            }
            
            if (!empty($value)) {
                if (isset($rule['min']) && strlen($value) < $rule['min']) {
                    $errors[$field] = ucfirst($field) . ' must be at least ' . $rule['min'] . ' characters';
                }
                
                if (isset($rule['max']) && strlen($value) > $rule['max']) {
                    $errors[$field] = ucfirst($field) . ' must not exceed ' . $rule['max'] . ' characters';
                }
                
                if (isset($rule['email']) && $rule['email'] && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = ucfirst($field) . ' must be a valid email address';
                }
                
                if (isset($rule['numeric']) && $rule['numeric'] && !is_numeric($value)) {
                    $errors[$field] = ucfirst($field) . ' must be a number';
                }
            }
        }
        
        return $errors;
    }
    
    protected function sanitizeInput($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    protected function uploadFile($file, $directory = 'general')
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \Exception('No file uploaded');
        }
        
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new \Exception('File size exceeds maximum allowed size');
        }
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new \Exception('File type not allowed');
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        
        // Create directory if it doesn't exist
        $uploadDir = UPLOAD_PATH . $directory . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filePath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Return relative path instead of full URL for better portability
            return 'public/uploads/' . $directory . '/' . $filename;
        } else {
            throw new \Exception('Failed to upload file');
        }
    }
    
    /**
     * Check if the request is an API request
     */
    protected function isApiRequest()
    {
        return isset($_SERVER['HTTP_ACCEPT']) && 
               strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false ||
               isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
