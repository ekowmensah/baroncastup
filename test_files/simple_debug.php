<?php
/**
 * Simple Debug Page - No class dependencies
 */

// Get configuration if possible
$config_loaded = false;
if (file_exists(__DIR__ . '/config/config.php')) {
    try {
        require_once __DIR__ . '/config/config.php';
        $config_loaded = true;
    } catch (Exception $e) {
        $config_error = $e->getMessage();
    }
}

$app_url = $config_loaded ? APP_URL : 'http://localhost/smartcast';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BaronCast - Simple Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .status { padding: 5px 10px; border-radius: 4px; font-weight: bold; display: inline-block; margin: 2px; }
        .ok { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; }
        .url-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .url-box { background: #f8f9fa; padding: 15px; border-radius: 6px; }
        .url-box h3 { margin-top: 0; color: #007bff; }
        .url-list { list-style: none; padding: 0; }
        .url-list li { margin: 5px 0; }
        .url-list a { color: #007bff; text-decoration: none; }
        .url-list a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 SmartCast Simple Debug</h1>
        
        <h2>📋 File Check</h2>
        <?php
        $files_to_check = [
            'Config' => 'config/config.php',
            'Autoloader' => 'includes/autoloader.php',
            'Index' => 'index.php',
            '.htaccess' => '.htaccess',
            'Application' => 'src/Core/Application.php',
            'Router' => 'src/Core/Router.php',
            'Database' => 'src/Core/Database.php',
            'HomeController' => 'src/Controllers/HomeController.php',
            'AuthController' => 'src/Controllers/AuthController.php'
        ];
        
        foreach ($files_to_check as $name => $file) {
            $exists = file_exists(__DIR__ . '/' . $file);
            $status = $exists ? 'ok' : 'error';
            echo "<span class='status $status'>$name</span> ";
        }
        ?>
        
        <h2>🔧 Configuration Status</h2>
        <?php if ($config_loaded): ?>
            <span class="status ok">Config Loaded</span>
            <p><strong>App URL:</strong> <?= APP_URL ?></p>
            <p><strong>Debug Mode:</strong> <?= APP_DEBUG ? 'ON' : 'OFF' ?></p>
            <p><strong>Database:</strong> <?= DB_HOST ?>/<?= DB_NAME ?></p>
        <?php else: ?>
            <span class="status error">Config Failed</span>
            <?php if (isset($config_error)): ?>
                <p><strong>Error:</strong> <?= htmlspecialchars($config_error) ?></p>
            <?php endif; ?>
        <?php endif; ?>
        
        <h2>🌐 Test URLs</h2>
        <div class="url-grid">
            <div class="url-box">
                <h3>Standard URLs (with mod_rewrite)</h3>
                <ul class="url-list">
                    <li><a href="<?= $app_url ?>/" target="_blank">Homepage</a></li>
                    <li><a href="<?= $app_url ?>/login" target="_blank">Login</a></li>
                    <li><a href="<?= $app_url ?>/register" target="_blank">Register</a></li>
                    <li><a href="<?= $app_url ?>/events" target="_blank">Events</a></li>
                    <li><a href="<?= $app_url ?>/api/events" target="_blank">API Events</a></li>
                </ul>
            </div>
            
            <div class="url-box">
                <h3>Alternative URLs (without mod_rewrite)</h3>
                <ul class="url-list">
                    <li><a href="<?= $app_url ?>/index.php" target="_blank">Homepage</a></li>
                    <li><a href="<?= $app_url ?>/index.php/login" target="_blank">Login</a></li>
                    <li><a href="<?= $app_url ?>/index.php/register" target="_blank">Register</a></li>
                    <li><a href="<?= $app_url ?>/index.php/events" target="_blank">Events</a></li>
                    <li><a href="<?= $app_url ?>/index.php/api/events" target="_blank">API Events</a></li>
                </ul>
            </div>
        </div>
        
        <h2>🛠️ Quick Fixes</h2>
        <div style="background: #e9ecef; padding: 15px; border-radius: 6px;">
            <h3>If you're getting 404 errors:</h3>
            <ol>
                <li><strong>Try the Alternative URLs above</strong> (with /index.php/)</li>
                <li><strong>Enable mod_rewrite in XAMPP:</strong>
                    <ul>
                        <li>Open XAMPP Control Panel</li>
                        <li>Stop Apache</li>
                        <li>Click "Config" next to Apache → "Apache (httpd.conf)"</li>
                        <li>Find and uncomment: <code>LoadModule rewrite_module modules/mod_rewrite.so</code></li>
                        <li>Find <code>&lt;Directory "C:/xampp/htdocs"&gt;</code></li>
                        <li>Change <code>AllowOverride None</code> to <code>AllowOverride All</code></li>
                        <li>Save and restart Apache</li>
                    </ul>
                </li>
                <li><strong>Check database:</strong>
                    <ul>
                        <li>Start MySQL in XAMPP</li>
                        <li>Import <code>smartcast.sql</code> via phpMyAdmin</li>
                        <li>Update database credentials in <code>config/config.php</code></li>
                    </ul>
                </li>
            </ol>
        </div>
        
        <h2>📊 Server Information</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: #f8f9fa;">
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>PHP Version</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;"><?= PHP_VERSION ?></td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>Server Software</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></td>
            </tr>
            <tr style="background: #f8f9fa;">
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>Document Root</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;"><?= $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown' ?></td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>Current Directory</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;"><?= __DIR__ ?></td>
            </tr>
        </table>
        
        <div style="text-align: center; margin-top: 30px; color: #6c757d;">
            <p><small>SmartCast Simple Debug | <?= date('Y-m-d H:i:s') ?></small></p>
            <p><small>If this page works, your basic PHP setup is correct!</small></p>
        </div>
    </div>
</body>
</html>
