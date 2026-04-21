<?php
require_once __DIR__ . '/config/config.php';

echo "=== TESTING IMAGE ACCESS ===\n\n";

// Test file that exists
$testFile = 'public/uploads/nominees/nominees_68ecb146af64c.jpeg';
$fullPath = __DIR__ . '/' . $testFile;

echo "1. File System Test:\n";
echo "   Relative path: $testFile\n";
echo "   Full path: $fullPath\n";
echo "   File exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
echo "   File size: " . (file_exists($fullPath) ? filesize($fullPath) . ' bytes' : 'N/A') . "\n\n";

// Test URL construction
echo "2. URL Construction Test:\n";
echo "   APP_URL: " . APP_URL . "\n";
echo "   UPLOAD_URL: " . UPLOAD_URL . "\n\n";

// Test different path formats
$testPaths = [
    'public/uploads/nominees/nominees_68ecb146af64c.jpeg',
    '/public/uploads/nominees/nominees_68ecb146af64c.jpeg',
    'uploads/nominees/nominees_68ecb146af64c.jpeg',
    '/uploads/nominees/nominees_68ecb146af64c.jpeg'
];

echo "3. Path Format Tests:\n";
foreach ($testPaths as $path) {
    $url = image_url($path);
    echo "   Input: '$path'\n";
    echo "   Output: '$url'\n";
    echo "   Expected URL: " . APP_URL . "/public/uploads/nominees/nominees_68ecb146af64c.jpeg\n\n";
}

// Test what the database should store
echo "4. Recommended Database Storage:\n";
echo "   Current DB format: 'http://localhost/smartcast/public/uploads/nominees/nominees_68ecb146af64c.jpeg'\n";
echo "   Recommended format: 'public/uploads/nominees/nominees_68ecb146af64c.jpeg'\n";
echo "   Or: '/public/uploads/nominees/nominees_68ecb146af64c.jpeg'\n\n";

echo "=== END TEST ===\n";
?>
