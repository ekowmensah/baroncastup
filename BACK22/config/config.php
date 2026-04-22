<?php
/**
 * SmartCast Configuration
 */

require_once __DIR__ . '/../src/Helpers/env.php';

if (!function_exists('define_if_missing')) {
    function define_if_missing($name, $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }
}

if (!function_exists('env_bool')) {
    function env_bool($key, $default = false) {
        $value = env($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}

// Allow deployment-specific constants to be defined before defaults below.
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

// Database Configuration
define_if_missing('DB_HOST', env('DB_HOST', 'localhost'));
define_if_missing('DB_NAME', env('DB_NAME', 'baronprimecast'));
define_if_missing('DB_USER', env('DB_USER', 'root'));
define_if_missing('DB_PASS', env('DB_PASS', ''));
define_if_missing('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

// Application Configuration
define_if_missing('APP_NAME', env('APP_NAME', 'BaronCast Voting System'));
define_if_missing('APP_VERSION', '1.0.0');
define_if_missing('APP_URL', env('APP_URL', 'http://localhost/baronprimecast'));
define_if_missing('APP_DEBUG', env_bool('APP_DEBUG', false));

// Security Configuration
define_if_missing('JWT_SECRET', env('JWT_SECRET', 'your-jwt-secret-key-change-this'));

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
define_if_missing('ENCRYPTION_KEY', env('ENCRYPTION_KEY', 'your-encryption-key-change-this'));
define_if_missing('PASSWORD_SALT', env('PASSWORD_SALT', 'your-password-salt-change-this'));

// File Upload Configuration
define_if_missing('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define_if_missing('UPLOAD_URL', APP_URL . '/public/uploads/');
define_if_missing('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Session Configuration
define_if_missing('SESSION_LIFETIME', 3600); // 1 hour
define_if_missing('SESSION_NAME', 'baroncast_session');

// Multi-tenant Dashboard URLs
define_if_missing('PUBLIC_URL', APP_URL);
define_if_missing('ORGANIZER_URL', APP_URL . '/organizer');
define_if_missing('ADMIN_URL', APP_URL . '/admin');
define_if_missing('SUPERADMIN_URL', APP_URL . '/superadmin');

// CoreUI Configuration
define_if_missing('COREUI_VERSION', '4.2.6');
define_if_missing('COREUI_CSS', 'https://cdn.jsdelivr.net/npm/@coreui/coreui@' . COREUI_VERSION . '/dist/css/coreui.min.css');
define_if_missing('COREUI_JS', 'https://cdn.jsdelivr.net/npm/@coreui/coreui@' . COREUI_VERSION . '/dist/js/coreui.bundle.min.js');

// Rate Limiting
define_if_missing('RATE_LIMIT_REQUESTS', 100);
define_if_missing('RATE_LIMIT_WINDOW', 3600); // 1 hour

// Timezone
date_default_timezone_set('UTC');
