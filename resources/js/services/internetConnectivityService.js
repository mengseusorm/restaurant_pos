import alertService from './alertService';

class InternetConnectivityService {
    constructor() {
        this.isOnline = navigator.onLine;
        this.listeners = [];
        this.checkInterval = null;
        this.lastCheckTime = Date.now();
        this.retryAttempts = 0;
        this.maxRetryAttempts = 3;
        
        // Initialize event listeners
        this.init();
    }
    
    init() {
        // Listen for browser online/offline events
        window.addEventListener('online', this.handleOnline.bind(this));
        window.addEventListener('offline', this.handleOffline.bind(this));
        
        // Start periodic connectivity checks
        this.startPeriodicCheck();
    }
    
    handleOnline() {
        this.isOnline = true;
        this.lastCheckTime = Date.now();
        this.retryAttempts = 0;
        this.notifyListeners(true);
        console.log('Internet connection restored');
    }
    
    handleOffline() {
        this.isOnline = false;
        this.lastCheckTime = Date.now();
        this.notifyListeners(false);
        console.log('Internet connection lost');
    }
    
    // Add listener for connectivity changes
    addListener(callback) {
        this.listeners.push(callback);
    }
    
    // Remove listener
    removeListener(callback) {
        this.listeners = this.listeners.filter(listener => listener !== callback);
    }
    
    // Notify all listeners about connectivity change
    notifyListeners(isOnline) {
        this.listeners.forEach(callback => {
            try {
                callback(isOnline);
            } catch (error) {
                console.error('Error in connectivity listener:', error);
            }
        });
    }
    
    // Start periodic connectivity checks
    startPeriodicCheck() {
        this.checkInterval = setInterval(() => {
            this.checkConnectivity();
        }, 30000); // Check every 30 seconds
    }
    
    // Stop periodic checks
    stopPeriodicCheck() {
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
            this.checkInterval = null;
        }
    }
    
    // Check internet connectivity
    async checkConnectivity() {
        this.lastCheckTime = Date.now();
        
        try {
            const isConnected = await this.pingServer();
            if (isConnected !== this.isOnline) {
                this.isOnline = isConnected;
                this.notifyListeners(isConnected);
            }
            this.retryAttempts = 0;
            return isConnected;
        } catch (error) {
            this.retryAttempts++;
            if (this.isOnline) {
                this.isOnline = false;
                this.notifyListeners(false);
            }
            return false;
        }
    }
    
    // Ping server to check connectivity
    async pingServer() {
        const timeout = 5000;
        const controller = new AbortController();
        
        const timeoutId = setTimeout(() => controller.abort(), timeout);
        
        try {
            const response = await fetch('/api/ping', {
                method: 'HEAD',
                signal: controller.signal,
                cache: 'no-cache',
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            });
            
            clearTimeout(timeoutId);
            return response.ok;
        } catch (error) {
            clearTimeout(timeoutId);
            
            // Try fallback method
            return this.fallbackConnectivityCheck();
        }
    }
    
    // Fallback connectivity check using external resource
    async fallbackConnectivityCheck() {
        const timeout = 3000;
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), timeout);
        
        try {
            await fetch('https://www.google.com/favicon.ico', {
                method: 'HEAD',
                mode: 'no-cors',
                signal: controller.signal,
                cache: 'no-cache'
            });
            
            clearTimeout(timeoutId);
            return true;
        } catch (error) {
            clearTimeout(timeoutId);
            return false;
        }
    }
    
    // Get current connectivity status
    getStatus() {
        return {
            isOnline: this.isOnline,
            lastCheckTime: this.lastCheckTime,
            retryAttempts: this.retryAttempts
        };
    }
    
    // Force a connectivity check
    async forceCheck() {
        return await this.checkConnectivity();
    }
    
    // Check if internet is required for action
    requireInternet(showAlert = true) {
        if (!this.isOnline && showAlert) {
            alertService.error('Internet connection required. Please check your connection and try again.');
        }
        return this.isOnline;
    }
    
    // Wrapper for API calls with connectivity check
    async withConnectivity(apiCall, retries = 2) {
        if (!this.isOnline) {
            // Try to check connectivity first
            const isConnected = await this.forceCheck();
            if (!isConnected) {
                throw new Error('No internet connection available');
            }
        }
        
        try {
            return await apiCall();
        } catch (error) {
            // If API call fails, check if it's a connectivity issue
            if (retries > 0 && (error.name === 'NetworkError' || error.code === 'NETWORK_ERROR')) {
                const isConnected = await this.forceCheck();
                if (isConnected) {
                    // Retry the API call
                    return this.withConnectivity(apiCall, retries - 1);
                }
            }
            throw error;
        }
    }
    
    // Format last check time
    formatLastCheckTime() {
        return new Date(this.lastCheckTime).toLocaleTimeString();
    }
    
    // Cleanup method
    destroy() {
        this.stopPeriodicCheck();
        window.removeEventListener('online', this.handleOnline.bind(this));
        window.removeEventListener('offline', this.handleOffline.bind(this));
        this.listeners = [];
    }
}

// Create and export singleton instance
const internetConnectivityService = new InternetConnectivityService();

export default internetConnectivityService;
