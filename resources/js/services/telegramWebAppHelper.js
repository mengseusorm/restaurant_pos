/**
 * Telegram WebApp Helper - Safe API wrapper for version compatibility
 * Handles version-specific compatibility issues and provides safe method calls
 */

class TelegramWebAppHelper {
    constructor() {
        this.webApp = null;
        this.version = null;
        this.initialized = false;
    }

    /**
     * Initialize Telegram WebApp
     */
    initialize() {
        if (typeof Telegram !== 'undefined' && Telegram.WebApp) {
            this.webApp = Telegram.WebApp;
            this.version = this.webApp.version || '6.0';
            this.initialized = true;
            return true;
        }
        return false;
    }

    /**
     * Check if Telegram WebApp is available
     */
    isAvailable() {
        return this.initialized && this.webApp !== null;
    }

    /**
     * Safe version of showAlert
     * Compatible with all versions
     */
    showAlert(message) {
        if (!this.isAvailable()) {
            console.warn('Telegram WebApp not available');
            return false;
        }

        try {
            if (typeof this.webApp.showAlert === 'function') {
                this.webApp.showAlert(message);
                return true;
            }
        } catch (error) {
            console.error('Error showing alert:', error);
        }
        return false;
    }

    /**
     * Safe version of showConfirm
     * Compatible with all versions
     */
    showConfirm(message, callback) {
        if (!this.isAvailable()) {
            console.warn('Telegram WebApp not available');
            return false;
        }

        try {
            if (typeof this.webApp.showConfirm === 'function') {
                this.webApp.showConfirm(message, callback);
                return true;
            }
        } catch (error) {
            console.error('Error showing confirm:', error);
        }
        return false;
    }

    /**
     * Safe version of openLink
     * Compatible with Telegram WebApp v6.0+
     * Removed try_instant_view parameter which is not supported in v6.0
     */
    openLink(url, options = {}) {
        if (!this.isAvailable()) {
            console.warn('Telegram WebApp not available');
            return false;
        }

        try {
            if (typeof this.webApp.openLink === 'function') {
                // Remove unsupported parameters for v6.0 compatibility
                const { try_instant_view, ...safeOptions } = options;
                
                // Call without parameters for maximum compatibility
                if (Object.keys(safeOptions).length === 0) {
                    this.webApp.openLink(url);
                } else {
                    this.webApp.openLink(url, safeOptions);
                }
                return true;
            }
        } catch (error) {
            console.error('Error opening link:', error);
        }
        return false;
    }

    /**
     * Safe version of HapticFeedback.notificationOccurred
     * Gracefully handles if not available
     */
    hapticNotification(type = 'success') {
        if (!this.isAvailable()) {
            return false;
        }

        try {
            if (this.webApp.HapticFeedback && typeof this.webApp.HapticFeedback.notificationOccurred === 'function') {
                this.webApp.HapticFeedback.notificationOccurred(type);
                return true;
            }
        } catch (error) {
            console.warn('Haptic feedback not available:', error);
        }
        return false;
    }

    /**
     * Expand the WebApp to full height
     */
    expand() {
        if (!this.isAvailable()) {
            return false;
        }

        try {
            if (typeof this.webApp.expand === 'function') {
                this.webApp.expand();
                return true;
            }
        } catch (error) {
            console.error('Error expanding WebApp:', error);
        }
        return false;
    }

    /**
     * Mark WebApp as ready
     */
    ready() {
        if (!this.isAvailable()) {
            return false;
        }

        try {
            if (typeof this.webApp.ready === 'function') {
                this.webApp.ready();
                return true;
            }
        } catch (error) {
            console.error('Error calling ready:', error);
        }
        return false;
    }

    /**
     * Get initialization data (user info, etc)
     */
    getInitData() {
        if (!this.isAvailable()) {
            return null;
        }

        try {
            return this.webApp.initDataUnsafe || null;
        } catch (error) {
            console.error('Error getting init data:', error);
        }
        return null;
    }

    /**
     * Get user information if available
     */
    getUserInfo() {
        const initData = this.getInitData();
        return initData?.user || null;
    }
}

// Create and export singleton instance
const telegramWebAppHelper = new TelegramWebAppHelper();

export default telegramWebAppHelper;
