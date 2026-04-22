<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$uriPath = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$targetFile = $projectRoot . $uriPath;

// Let PHP's built-in server serve real files directly (css/js/images, etc.)
if ($uriPath !== '/' && file_exists($targetFile) && !is_dir($targetFile)) {
    return false;
}

require $projectRoot . DIRECTORY_SEPARATOR . 'index.php';
