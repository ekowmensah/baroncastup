<?php
// Check PHP error log configuration
echo "<h2>PHP Error Log Configuration</h2>";
echo "<strong>Error Log Location:</strong> " . ini_get('error_log') . "<br>";
echo "<strong>Log Errors:</strong> " . (ini_get('log_errors') ? 'Yes' : 'No') . "<br>";
echo "<strong>Display Errors:</strong> " . (ini_get('display_errors') ? 'Yes' : 'No') . "<br>";
echo "<strong>Error Reporting Level:</strong> " . error_reporting() . "<br><br>";

// Test error logging
error_log("TEST: This is a test error log message from debug_info.php");
echo "Test error message sent to log.<br><br>";

// Show recent PHP errors if available
echo "<h3>Recent Error Log Entries (last 20 lines):</h3>";
$error_log_file = ini_get('error_log');
if ($error_log_file && file_exists($error_log_file)) {
    $lines = file($error_log_file);
    $recent_lines = array_slice($lines, -20);
    echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
    foreach ($recent_lines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "Error log file not found or not configured.<br>";
    echo "Trying common XAMPP locations...<br>";
    
    $common_paths = [
        'C:\xampp\apache\logs\error.log',
        'C:\xampp\php\logs\php_error_log',
        'C:\xampp\logs\error.log',
        dirname($_SERVER['DOCUMENT_ROOT']) . '\logs\error.log'
    ];
    
    foreach ($common_paths as $path) {
        if (file_exists($path)) {
            echo "<strong>Found log file:</strong> $path<br>";
            $lines = file($path);
            $recent_lines = array_slice($lines, -10);
            echo "<pre style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd;'>";
            foreach ($recent_lines as $line) {
                echo htmlspecialchars($line);
            }
            echo "</pre><br>";
        }
    }
}

// Show current working directory and document root
echo "<h3>Server Information:</h3>";
echo "<strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "<strong>Current Working Directory:</strong> " . getcwd() . "<br>";
echo "<strong>Script Filename:</strong> " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
?>
