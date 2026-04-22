<?php

namespace SmartCast\Helpers;

class ImageHelper
{
    /**
     * Get the full URL for an image path
     * Handles both relative paths and full URLs
     */
    public static function getImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return null;
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
    
    /**
     * Get image URL with fallback to default avatar
     */
    public static function getImageUrlWithFallback($imagePath, $fallbackPath = null)
    {
        $url = self::getImageUrl($imagePath);
        
        if ($url) {
            return $url;
        }
        
        if ($fallbackPath) {
            return self::getImageUrl($fallbackPath);
        }
        
        // Return default avatar or placeholder
        return APP_URL . '/public/assets/images/default-avatar.png';
    }
    
    /**
     * Check if image file exists
     */
    public static function imageExists($imagePath)
    {
        if (empty($imagePath)) {
            return false;
        }
        
        // Convert to file system path
        $filePath = self::getFileSystemPath($imagePath);
        
        return file_exists($filePath);
    }
    
    /**
     * Convert image URL/path to file system path
     */
    public static function getFileSystemPath($imagePath)
    {
        if (empty($imagePath)) {
            return null;
        }
        
        // Remove APP_URL if present
        $path = str_replace(APP_URL, '', $imagePath);
        
        // Remove leading slash and add document root
        $path = ltrim($path, '/');
        
        return __DIR__ . '/../../' . $path;
    }
}
?>
