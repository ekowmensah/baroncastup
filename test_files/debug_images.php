<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';

echo "=== IMAGE PATH DEBUG ANALYSIS ===\n\n";

// Check APP_URL
echo "1. APP_URL Configuration:\n";
echo "   APP_URL: " . APP_URL . "\n";
echo "   UPLOAD_URL: " . UPLOAD_URL . "\n";
echo "   UPLOAD_PATH: " . UPLOAD_PATH . "\n\n";

// Test image_url function
echo "2. Testing image_url() function:\n";
$testPaths = [
    'uploads/events/test.jpg',
    '/uploads/events/test.jpg', 
    'http://localhost/smartcast/uploads/events/test.jpg',
    ''
];

foreach ($testPaths as $path) {
    $result = image_url($path);
    echo "   Input: '$path'\n";
    echo "   Output: '$result'\n\n";
}

// Check database content
try {
    $db = new \SmartCast\Core\Database();
    
    echo "3. Database Image Paths:\n\n";
    
    // Check events
    echo "   EVENTS:\n";
    $events = $db->select("SELECT id, name, featured_image FROM events WHERE featured_image IS NOT NULL AND featured_image != '' LIMIT 5");
    foreach ($events as $event) {
        echo "     Event #{$event['id']}: {$event['name']}\n";
        echo "     Raw DB path: '{$event['featured_image']}'\n";
        echo "     image_url(): '" . image_url($event['featured_image']) . "'\n";
        echo "     File exists: " . (file_exists(UPLOAD_PATH . '/' . ltrim($event['featured_image'], '/')) ? 'YES' : 'NO') . "\n\n";
    }
    
    // Check contestants
    echo "   CONTESTANTS:\n";
    $contestants = $db->select("SELECT id, name, image_url FROM contestants WHERE image_url IS NOT NULL AND image_url != '' LIMIT 5");
    foreach ($contestants as $contestant) {
        echo "     Contestant #{$contestant['id']}: {$contestant['name']}\n";
        echo "     Raw DB path: '{$contestant['image_url']}'\n";
        echo "     image_url(): '" . image_url($contestant['image_url']) . "'\n";
        echo "     File exists: " . (file_exists(UPLOAD_PATH . '/' . ltrim($contestant['image_url'], '/')) ? 'YES' : 'NO') . "\n\n";
    }
    
} catch (Exception $e) {
    echo "   Database Error: " . $e->getMessage() . "\n\n";
}

// Check upload directory
echo "4. Upload Directory Check:\n";
echo "   Upload path exists: " . (is_dir(UPLOAD_PATH) ? 'YES' : 'NO') . "\n";
if (is_dir(UPLOAD_PATH)) {
    echo "   Upload path writable: " . (is_writable(UPLOAD_PATH) ? 'YES' : 'NO') . "\n";
    echo "   Files in upload directory:\n";
    $files = scandir(UPLOAD_PATH);
    $imageFiles = array_filter($files, function($file) {
        return preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
    });
    foreach (array_slice($imageFiles, 0, 10) as $file) {
        echo "     - $file\n";
    }
}

echo "\n=== END DEBUG ===\n";
?>
