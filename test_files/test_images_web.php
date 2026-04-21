<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Core/Database.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Image Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-item { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .test-image { max-width: 200px; max-height: 150px; border: 2px solid #ccc; }
        .success { border-color: green; }
        .error { border-color: red; }
    </style>
</head>
<body>
    <h1>🧪 SmartCast Image Test Page</h1>
    
    <?php
    try {
        $db = new \SmartCast\Core\Database();
        
        // Test event images
        echo "<h2>📅 Event Images</h2>";
        $events = $db->select("SELECT id, name, featured_image FROM events WHERE featured_image IS NOT NULL LIMIT 3");
        
        foreach ($events as $event) {
            $imageUrl = image_url($event['featured_image']);
            $filePath = __DIR__ . '/' . $event['featured_image'];
            $fileExists = file_exists($filePath);
            
            echo "<div class='test-item " . ($fileExists ? 'success' : 'error') . "'>";
            echo "<h3>Event: " . htmlspecialchars($event['name']) . "</h3>";
            echo "<p><strong>DB Path:</strong> " . htmlspecialchars($event['featured_image']) . "</p>";
            echo "<p><strong>Final URL:</strong> " . htmlspecialchars($imageUrl) . "</p>";
            echo "<p><strong>File Exists:</strong> " . ($fileExists ? '✅ YES' : '❌ NO') . "</p>";
            
            if ($fileExists) {
                echo "<img src='" . htmlspecialchars($imageUrl) . "' class='test-image' alt='Event Image'>";
            } else {
                echo "<div style='width: 200px; height: 150px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border: 2px dashed #ccc;'>Image Not Found</div>";
            }
            echo "</div>";
        }
        
        // Test contestant images
        echo "<h2>👤 Contestant Images</h2>";
        $contestants = $db->select("SELECT id, name, image_url FROM contestants WHERE image_url IS NOT NULL LIMIT 3");
        
        foreach ($contestants as $contestant) {
            $imageUrl = image_url($contestant['image_url']);
            $filePath = __DIR__ . '/' . $contestant['image_url'];
            $fileExists = file_exists($filePath);
            
            echo "<div class='test-item " . ($fileExists ? 'success' : 'error') . "'>";
            echo "<h3>Contestant: " . htmlspecialchars($contestant['name']) . "</h3>";
            echo "<p><strong>DB Path:</strong> " . htmlspecialchars($contestant['image_url']) . "</p>";
            echo "<p><strong>Final URL:</strong> " . htmlspecialchars($imageUrl) . "</p>";
            echo "<p><strong>File Exists:</strong> " . ($fileExists ? '✅ YES' : '❌ NO') . "</p>";
            
            if ($fileExists) {
                echo "<img src='" . htmlspecialchars($imageUrl) . "' class='test-image' alt='Contestant Image'>";
            } else {
                echo "<div style='width: 200px; height: 150px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border: 2px dashed #ccc;'>Image Not Found</div>";
            }
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='test-item error'>";
        echo "<h3>❌ Database Error</h3>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
    ?>
    
    <h2>📊 Summary</h2>
    <div class="test-item">
        <p><strong>✅ Green borders:</strong> Images working correctly</p>
        <p><strong>❌ Red borders:</strong> Images with issues</p>
        <p><strong>Test URL:</strong> <a href="<?= APP_URL ?>/test_images_web.php">Refresh Test</a></p>
    </div>
</body>
</html>
