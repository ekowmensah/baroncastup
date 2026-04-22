<?php

/**
 * Environment Helper Function
 * 
 * Get environment variable with fallback
 */

if (!function_exists('env')) {
    /**
     * Get environment variable value
     * 
     * @param string $key Environment variable name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    function env($key, $default = null)
    {
        // Try to get from $_ENV
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        // Try to get from getenv()
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        // Try to load from .env file if it exists
        static $envLoaded = false;
        if (!$envLoaded) {
            $envFile = __DIR__ . '/../../.env';
            if (file_exists($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    // Skip comments
                    if (strpos(trim($line), '#') === 0) {
                        continue;
                    }
                    
                    // Parse KEY=VALUE
                    if (strpos($line, '=') !== false) {
                        list($envKey, $envValue) = explode('=', $line, 2);
                        $envKey = trim($envKey);
                        $envValue = trim($envValue);
                        
                        // Remove quotes
                        $envValue = trim($envValue, '"\'');
                        
                        // Set in $_ENV
                        $_ENV[$envKey] = $envValue;
                        putenv("$envKey=$envValue");
                    }
                }
                $envLoaded = true;
                
                // Try again after loading
                if (isset($_ENV[$key])) {
                    return $_ENV[$key];
                }
            }
        }
        
        // Return default
        return $default;
    }
}
