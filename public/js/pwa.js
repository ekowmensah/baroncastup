// SmartCast PWA JavaScript
class SmartCastPWA {
    constructor() {
        this.init();
    }

    async init() {
        // Register service worker
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.register('/smartcast/public/sw.js');
                console.log('SmartCast PWA: Service Worker registered successfully', registration);
                
                // Handle service worker updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            this.showUpdateNotification();
                        }
                    });
                });
            } catch (error) {
                console.error('SmartCast PWA: Service Worker registration failed', error);
            }
        }

        // Initialize install prompt
        this.initInstallPrompt();
        
        // Initialize push notifications
        this.initPushNotifications();
        
        // Initialize offline detection
        this.initOfflineDetection();
        
        // Initialize background sync
        this.initBackgroundSync();
    }

    initInstallPrompt() {
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('SmartCast PWA: Install prompt available');
            e.preventDefault();
            deferredPrompt = e;
            this.showInstallButton(deferredPrompt);
        });

        window.addEventListener('appinstalled', () => {
            console.log('SmartCast PWA: App installed successfully');
            this.hideInstallButton();
            this.showInstallSuccessMessage();
        });
    }

    showInstallButton(deferredPrompt) {
        // Don't show if app is already installed
        if (this.isInstalled()) {
            console.log('SmartCast PWA: App already installed, not showing install button');
            return;
        }

        // Don't show if user has dismissed it before
        if (localStorage.getItem('smartcast-install-dismissed') === 'true') {
            console.log('SmartCast PWA: Install button previously dismissed');
            return;
        }

        // Create install button if it doesn't exist
        if (!document.getElementById('pwa-install-btn')) {
            const installBtn = document.createElement('button');
            installBtn.id = 'pwa-install-btn';
            installBtn.className = 'btn btn-primary btn-sm position-fixed';
            installBtn.style.cssText = `
                bottom: 20px;
                right: 20px;
                z-index: 1050;
                border-radius: 50px;
                padding: 12px 20px;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
                animation: slideInUp 0.5s ease-out, pulse 2s infinite 0.5s;
                opacity: 0;
                animation-fill-mode: forwards;
            `;
            installBtn.innerHTML = '<i class="fas fa-download me-2"></i>Install App';
            
            // Add close button
            const closeBtn = document.createElement('span');
            closeBtn.innerHTML = '&times;';
            closeBtn.style.cssText = `
                position: absolute;
                top: -5px;
                right: -5px;
                background: #dc3545;
                color: white;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                font-size: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                font-weight: bold;
            `;
            closeBtn.title = 'Dismiss';
            
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.dismissInstallButton();
            });

            installBtn.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log('SmartCast PWA: Install prompt result:', outcome);
                    
                    if (outcome === 'accepted') {
                        localStorage.setItem('smartcast-install-accepted', 'true');
                    } else {
                        localStorage.setItem('smartcast-install-dismissed', 'true');
                    }
                    
                    this.hideInstallButton();
                    deferredPrompt = null;
                }
            });

            installBtn.appendChild(closeBtn);
            document.body.appendChild(installBtn);

            // Add CSS animations
            if (!document.getElementById('pwa-styles')) {
                const style = document.createElement('style');
                style.id = 'pwa-styles';
                style.textContent = `
                    @keyframes pulse {
                        0% { transform: scale(1); }
                        50% { transform: scale(1.05); }
                        100% { transform: scale(1); }
                    }
                    @keyframes slideInUp {
                        from {
                            transform: translateY(100px);
                            opacity: 0;
                        }
                        to {
                            transform: translateY(0);
                            opacity: 1;
                        }
                    }
                    @keyframes slideOutDown {
                        from {
                            transform: translateY(0);
                            opacity: 1;
                        }
                        to {
                            transform: translateY(100px);
                            opacity: 0;
                        }
                    }
                `;
                document.head.appendChild(style);
            }

            // Auto-hide after 30 seconds
            setTimeout(() => {
                this.autoHideInstallButton();
            }, 30000);

            console.log('SmartCast PWA: Install button shown, will auto-hide in 30 seconds');
        }
    }

    hideInstallButton() {
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.style.animation = 'slideOutDown 0.3s ease-in forwards';
            setTimeout(() => {
                if (installBtn.parentNode) {
                    installBtn.remove();
                }
            }, 300);
        }
    }

    dismissInstallButton() {
        console.log('SmartCast PWA: Install button dismissed by user');
        localStorage.setItem('smartcast-install-dismissed', 'true');
        this.hideInstallButton();
        this.showNotification('Install prompt dismissed. You can still install from your browser menu.', 'info');
    }

    autoHideInstallButton() {
        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            console.log('SmartCast PWA: Auto-hiding install button after 30 seconds');
            this.hideInstallButton();
        }
    }

    showInstallSuccessMessage() {
        localStorage.setItem('smartcast-install-accepted', 'true');
        this.showNotification('SmartCast installed successfully! 🎉', 'success');
    }

    showUpdateNotification() {
        const updateBtn = document.createElement('div');
        updateBtn.className = 'alert alert-info alert-dismissible fade show position-fixed';
        updateBtn.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 1060;
            max-width: 350px;
        `;
        updateBtn.innerHTML = `
            <strong>Update Available!</strong>
            <p class="mb-2">A new version of SmartCast is available.</p>
            <button type="button" class="btn btn-sm btn-primary me-2" onclick="window.location.reload()">
                Update Now
            </button>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(updateBtn);
        
        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (updateBtn.parentNode) {
                updateBtn.remove();
            }
        }, 10000);
    }

    async initPushNotifications() {
        if ('Notification' in window && 'serviceWorker' in navigator) {
            // Request notification permission
            if (Notification.permission === 'default') {
                // Don't request immediately - wait for user interaction
                this.addNotificationPrompt();
            }
        }
    }

    addNotificationPrompt() {
        // Add notification permission request to voting forms
        const voteForms = document.querySelectorAll('form[id*="vote"]');
        voteForms.forEach(form => {
            form.addEventListener('submit', () => {
                if (Notification.permission === 'default') {
                    setTimeout(() => {
                        this.requestNotificationPermission();
                    }, 2000);
                }
            });
        });
    }

    async requestNotificationPermission() {
        try {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                console.log('SmartCast PWA: Notification permission granted');
                this.showNotification('You\'ll now receive voting updates!', 'success');
            }
        } catch (error) {
            console.error('SmartCast PWA: Notification permission error', error);
        }
    }

    initOfflineDetection() {
        window.addEventListener('online', () => {
            console.log('SmartCast PWA: Back online');
            this.showNotification('You\'re back online! 🌐', 'success');
            this.syncOfflineData();
        });

        window.addEventListener('offline', () => {
            console.log('SmartCast PWA: Gone offline');
            this.showNotification('You\'re offline. Some features may be limited.', 'warning');
        });

        // Initial check
        if (!navigator.onLine) {
            this.showNotification('You\'re currently offline.', 'warning');
        }
    }

    initBackgroundSync() {
        // Register for background sync when forms are submitted offline
        if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', (e) => {
                    if (!navigator.onLine) {
                        e.preventDefault();
                        this.queueOfflineAction(form);
                    }
                });
            });
        }
    }

    async queueOfflineAction(form) {
        try {
            const registration = await navigator.serviceWorker.ready;
            await registration.sync.register('background-vote-sync');
            console.log('SmartCast PWA: Background sync registered');
            this.showNotification('Your vote will be submitted when you\'re back online.', 'info');
        } catch (error) {
            console.error('SmartCast PWA: Background sync registration failed', error);
        }
    }

    async syncOfflineData() {
        // Trigger background sync
        if ('serviceWorker' in navigator) {
            try {
                const registration = await navigator.serviceWorker.ready;
                if ('sync' in registration) {
                    await registration.sync.register('background-vote-sync');
                }
            } catch (error) {
                console.error('SmartCast PWA: Sync failed', error);
            }
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 1060;
            max-width: 350px;
            animation: slideInRight 0.3s ease-out;
        `;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    // Utility method to check if app is installed
    isInstalled() {
        // Check if running in standalone mode (installed PWA)
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
                           window.navigator.standalone === true;
        
        // Check if user has previously accepted installation
        const wasAccepted = localStorage.getItem('smartcast-install-accepted') === 'true';
        
        // Check if running from home screen (Android)
        const isFromHomeScreen = document.referrer.includes('android-app://');
        
        return isStandalone || wasAccepted || isFromHomeScreen;
    }

    // Utility method to check if app is running as PWA
    isPWA() {
        return this.isInstalled() || 
               document.referrer.includes('android-app://') ||
               window.location.search.includes('utm_source=pwa');
    }

    // Utility method to reset install prompt state (for testing)
    resetInstallPrompt() {
        localStorage.removeItem('smartcast-install-dismissed');
        localStorage.removeItem('smartcast-install-accepted');
        console.log('SmartCast PWA: Install prompt state reset');
    }
}

// Initialize PWA when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.smartcastPWA = new SmartCastPWA();
    
    // Add PWA-specific styling if running as PWA
    if (window.smartcastPWA.isPWA()) {
        document.body.classList.add('pwa-mode');
        
        // Add PWA-specific CSS
        const pwaStyle = document.createElement('style');
        pwaStyle.textContent = `
            .pwa-mode .navbar {
                padding-top: env(safe-area-inset-top, 0);
            }
            .pwa-mode body {
                padding-bottom: env(safe-area-inset-bottom, 0);
            }
        `;
        document.head.appendChild(pwaStyle);
    }
});
