<?php
/**
 * SmartCast Autoloader
 */

// Load configuration if not already loaded
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config/config.php';
}

// Load Composer autoloader if available
$composerAutoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoloader)) {
    require_once $composerAutoloader;
}

spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $prefix = 'SmartCast\\';
    $base_dir = __DIR__ . '/../src/';
    
    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        // Get the relative class name
        $relative_class = substr($class, $len);
        
        // Replace namespace separators with directory separators
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        // If the file exists, require it
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
    
    // Handle classes without namespace (like SEOHelper)
    $possible_paths = [
        __DIR__ . '/../src/Helpers/' . $class . '.php',
        __DIR__ . '/../src/Models/' . $class . '.php',
        __DIR__ . '/../src/Controllers/' . $class . '.php',
        __DIR__ . '/../src/Services/' . $class . '.php',
        __DIR__ . '/../src/Core/' . $class . '.php'
    ];
    
    foreach ($possible_paths as $file) {
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});
