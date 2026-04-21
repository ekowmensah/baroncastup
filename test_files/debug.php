<?php
/**
 * Debug and Quick Access Page
 */
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCast Debug & Quick Access</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .card { background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #007bff; }
        .card h3 { margin-top: 0; color: #007bff; }
        .url-list { list-style: none; padding: 0; }
        .url-list li { margin: 8px 0; }
        .url-list a { color: #007bff; text-decoration: none; padding: 5px 10px; background: #e9ecef; border-radius: 4px; display: inline-block; }
        .url-list a:hover { background: #007bff; color: white; }
        .status { padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .status.ok { background: #d4edda; color: #155724; }
        .status.error { background: #f8d7da; color: #721c24; }
        .status.warning { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 SmartCast Debug & Quick Access</h1>
        
        <div class="grid">
            <!-- Public URLs -->
            <div class="card">
                <h3>📱 Public URLs</h3>
                <ul class="url-list">
                    <li><a href="<?= APP_URL ?>/" target="_blank">Homepage</a></li>
                    <li><a href="<?= APP_URL ?>/login" target="_blank">Login</a></li>
                    <li><a href="<?= APP_URL ?>/register" target="_blank">Register</a></li>
                    <li><a href="<?= APP_URL ?>/events" target="_blank">Events</a></li>
                </ul>
            </div>

            <!-- Alternative URLs (if mod_rewrite not working) -->
            <div class="card">
                <h3>🔧 Alternative URLs</h3>
                <p><small>Use these if mod_rewrite is not enabled:</small></p>
                <ul class="url-list">
                    <li><a href="<?= APP_URL ?>/index.php" target="_blank">Homepage</a></li>
                    <li><a href="<?= APP_URL ?>/index.php/login" target="_blank">Login</a></li>
                    <li><a href="<?= APP_URL ?>/index.php/register" target="_blank">Register</a></li>
                    <li><a href="<?= APP_URL ?>/index.php/events" target="_blank">Events</a></li>
                </ul>
            </div>

            <!-- Dashboard URLs -->
            <div class="card">
                <h3>🎛️ Dashboard URLs</h3>
                <ul class="url-list">
                    <li><a href="<?= APP_URL ?>/organizer" target="_blank">Organizer Dashboard</a></li>
                    <li><a href="<?= APP_URL ?>/admin" target="_blank">Admin Dashboard</a></li>
                    <li><a href="<?= APP_URL ?>/superadmin" target="_blank">Super Admin</a></li>
                </ul>
            </div>

            <!-- API URLs -->
            <div class="card">
                <h3>🔌 API URLs</h3>
                <ul class="url-list">
                    <li><a href="<?= APP_URL ?>/api/events" target="_blank">Events API</a></li>
                    <li><a href="<?= APP_URL ?>/api/events/1" target="_blank">Single Event API</a></li>
                </ul>
            </div>

            <!-- System Tools -->
            <div class="card">
                <h3>🛠️ System Tools</h3>
                <ul class="url-list">
                    <li><a href="<?= APP_URL ?>/system_check.php" target="_blank">System Health Check</a></li>
                    <li><a href="<?= APP_URL ?>/test_routes.php" target="_blank">Route Testing</a></li>
                    <li><a href="<?= APP_URL ?>/debug.php" target="_blank">This Debug Page</a></li>
                </ul>
            </div>

            <!-- Quick Status -->
            <div class="card">
                <h3>📊 Quick Status</h3>
                <?php
                $checks = [];
                
                // Check database
                try {
                    if (class_exists('SmartCast\Core\Database')) {
                        $db = new SmartCast\Core\Database();
                        $db->getConnection();
                        $checks['Database'] = 'ok';
                    } else {
                        $checks['Database'] = 'error';
                    }
                } catch (Exception $e) {
                    $checks['Database'] = 'error';
                } catch (Error $e) {
                    $checks['Database'] = 'error';
                }
                
                // Check .htaccess
                $checks['.htaccess'] = file_exists(__DIR__ . '/.htaccess') ? 'ok' : 'warning';
                
                // Check core files
                $checks['Core Files'] = (
                    file_exists(__DIR__ . '/src/Core/Application.php') &&
                    file_exists(__DIR__ . '/src/Core/Router.php') &&
                    file_exists(__DIR__ . '/src/Controllers/HomeController.php')
                ) ? 'ok' : 'error';
                
                foreach ($checks as $item => $status) {
                    echo "<p>$item: <span class='status $status'>" . strtoupper($status) . "</span></p>";
                }
                ?>
            </div>
        </div>

        <div style="background: #e9ecef; padding: 15px; border-radius: 6px; margin-top: 20px;">
            <h3>🔍 Troubleshooting Steps</h3>
            <ol>
                <li><strong>If getting 404 errors:</strong>
                    <ul>
                        <li>Try the "Alternative URLs" above (with /index.php/)</li>
                        <li>Check if mod_rewrite is enabled in Apache</li>
                        <li>Verify .htaccess file exists and is readable</li>
                    </ul>
                </li>
                <li><strong>Enable mod_rewrite in XAMPP:</strong>
                    <ul>
                        <li>Open <code>xampp/apache/conf/httpd.conf</code></li>
                        <li>Uncomment: <code>LoadModule rewrite_module modules/mod_rewrite.so</code></li>
                        <li>Find <code>&lt;Directory "C:/xampp/htdocs"&gt;</code></li>
                        <li>Change <code>AllowOverride None</code> to <code>AllowOverride All</code></li>
                        <li>Restart Apache</li>
                    </ul>
                </li>
                <li><strong>Database Issues:</strong>
                    <ul>
                        <li>Import <code>smartcast.sql</code> into MySQL</li>
                        <li>Update database credentials in <code>config/config.php</code></li>
                        <li>Ensure MySQL service is running</li>
                    </ul>
                </li>
            </ol>
        </div>

        <div style="text-align: center; margin-top: 20px; color: #6c757d;">
            <p><small>SmartCast Debug Page | Generated: <?= date('Y-m-d H:i:s') ?></small></p>
        </div>
    </div>
</body>
</html>
