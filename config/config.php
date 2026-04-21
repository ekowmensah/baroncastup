<?php
/**
 * SmartCast Configuration
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'baronprimecast');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'BaronCast Voting System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/baronprimecast');
define('APP_DEBUG', true);

// Security Configuration
define('JWT_SECRET', 'your-jwt-secret-key-change-this');

// Helper function for image URLs
if (!function_exists('image_url')) {
    function image_url($imagePath) {
        if (empty($imagePath)) {
            return null;
        }
        
        // Fix malformed URLs (missing slash in http:/)
        if (strpos($imagePath, 'http:/') === 0 && strpos($imagePath, 'http://') !== 0) {
            $imagePath = str_replace('http:/', 'http://', $imagePath);
        }
        if (strpos($imagePath, 'https:/') === 0 && strpos($imagePath, 'https://') !== 0) {
            $imagePath = str_replace('https:/', 'https://', $imagePath);
        }
        
        // If it's already a full URL, return as is
        if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
            return $imagePath;
        }
        
        // If it starts with APP_URL, return as is
        if (strpos($imagePath, APP_URL) === 0) {
            return $imagePath;
        }
        
        // If it's a relative path starting with /, add APP_URL
        if (strpos($imagePath, '/') === 0) {
            return APP_URL . $imagePath;
        }
        
        // Otherwise, assume it's a relative path and add APP_URL with leading slash
        return APP_URL . '/' . ltrim($imagePath, '/');
    }
}
define('ENCRYPTION_KEY', 'your-encryption-key-change-this');
define('PASSWORD_SALT', 'your-password-salt-change-this');

// File Upload Configuration
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('UPLOAD_URL', APP_URL . '/public/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'smartcast_session');

// Multi-tenant Dashboard URLs
define('PUBLIC_URL', APP_URL);
define('ORGANIZER_URL', APP_URL . '/organizer');
define('ADMIN_URL', APP_URL . '/admin');
define('SUPERADMIN_URL', APP_URL . '/superadmin');

// CoreUI Configuration
define('COREUI_VERSION', '4.2.6');
define('COREUI_CSS', 'https://cdn.jsdelivr.net/npm/@coreui/coreui@' . COREUI_VERSION . '/dist/css/coreui.min.css');
define('COREUI_JS', 'https://cdn.jsdelivr.net/npm/@coreui/coreui@' . COREUI_VERSION . '/dist/js/coreui.bundle.min.js');

// Rate Limiting
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_WINDOW', 3600); // 1 hour

// Timezone
date_default_timezone_set('UTC');

// Include environment-specific config if exists
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}