/**
 * SmartCast Image URL Helper
 * Provides consistent image URL handling across all JavaScript code
 */

window.SmartCast = window.SmartCast || {};

SmartCast.ImageHelper = {
    /**
     * Get the full URL for an image path
     * @param {string} imagePath - The image path (relative or absolute)
     * @returns {string|null} - Full image URL or null if empty
     */
    getImageUrl: function(imagePath) {
        if (!imagePath) return null;
        
        // If it's already a full URL, return as is
        if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
            return imagePath;
        }
        
        // Get APP_URL from global variable or meta tag
        const appUrl = window.APP_URL || document.querySelector('meta[name="app-url"]')?.content || '';
        
        // If it starts with APP_URL, return as is
        if (imagePath.startsWith(appUrl)) {
            return imagePath;
        }
        
        // If it's a relative path starting with /, add APP_URL
        if (imagePath.startsWith('/')) {
            return appUrl + imagePath;
        }
        
        // Otherwise, assume it's a relative path and add APP_URL with leading slash
        return appUrl + '/' + imagePath.replace(/^\/+/, '');
    },

    /**
     * Get image URL with fallback
     * @param {string} imagePath - Primary image path
     * @param {string} fallbackPath - Fallback image path
     * @returns {string} - Image URL with fallback
     */
    getImageUrlWithFallback: function(imagePath, fallbackPath = null) {
        const url = this.getImageUrl(imagePath);
        
        if (url) {
            return url;
        }
        
        if (fallbackPath) {
            return this.getImageUrl(fallbackPath);
        }
        
        // Return default avatar
        const appUrl = window.APP_URL || document.querySelector('meta[name="app-url"]')?.content || '';
        return appUrl + '/public/assets/images/default-avatar.png';
    }
};

// Create global shorthand function
window.getImageUrl = SmartCast.ImageHelper.getImageUrl.bind(SmartCast.ImageHelper);
window.getImageUrlWithFallback = SmartCast.ImageHelper.getImageUrlWithFallback.bind(SmartCast.ImageHelper);
