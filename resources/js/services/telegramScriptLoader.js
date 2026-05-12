/**
 * Telegram WebApp Script Loader Utility
 * Dynamically loads the Telegram WebApp script only when needed
 */

class TelegramScriptLoader {
    constructor() {
        this.scriptLoaded = false;
        this.loadingPromise = null;
    }

    /**
     * Load the Telegram WebApp script
     * @returns {Promise} Resolves when script is loaded
     */
    loadScript() {
        // Return existing promise if already loading
        if (this.loadingPromise) {
            return this.loadingPromise;
        }

        // Return resolved promise if already loaded
        if (typeof Telegram !== 'undefined') {
            this.scriptLoaded = true;
            return Promise.resolve();
        }

        // Check if script is already in DOM
        const existingScript = document.querySelector('script[src*="telegram-web-app.js"]');
        if (existingScript) {
            if (typeof Telegram !== 'undefined') {
                this.scriptLoaded = true;
                return Promise.resolve();
            }
            
            // Wait for existing script to load
            this.loadingPromise = new Promise((resolve, reject) => {
                existingScript.onload = () => {
                    this.scriptLoaded = true;
                    this.loadingPromise = null;
                    resolve();
                };
                existingScript.onerror = () => {
                    this.loadingPromise = null;
                    reject(new Error('Failed to load Telegram WebApp script'));
                };
            });
            return this.loadingPromise;
        }

        // Create and load new script
        this.loadingPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://telegram.org/js/telegram-web-app.js?59';
            script.async = true;
            
            script.onload = () => {
                console.log('Telegram WebApp script loaded successfully');
                this.scriptLoaded = true;
                this.loadingPromise = null;
                resolve();
            };
            
            script.onerror = () => {
                console.error('Failed to load Telegram WebApp script');
                this.loadingPromise = null;
                reject(new Error('Failed to load Telegram WebApp script'));
            };
            
            document.head.appendChild(script);
        });

        return this.loadingPromise;
    }

    /**
     * Check if Telegram WebApp is available
     * @returns {boolean}
     */
    isAvailable() {
        return typeof Telegram !== 'undefined' && !!Telegram.WebApp;
    }

    /**
     * Get Telegram WebApp instance
     * @returns {object|null}
     */
    getWebApp() {
        return this.isAvailable() ? Telegram.WebApp : null;
    }

    /**
     * Initialize Telegram WebApp
     */
    initializeWebApp() {
        const webApp = this.getWebApp();
        if (webApp) {
            webApp.ready();
            webApp.expand();
            return webApp;
        }
        return null;
    }
}

// Create singleton instance
const telegramScriptLoader = new TelegramScriptLoader();

export default telegramScriptLoader;