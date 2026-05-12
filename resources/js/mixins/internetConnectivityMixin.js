// Internet connectivity mixin for global use
export default {
    data() {
        return {
            internetConnectivity: {
                isOnline: navigator.onLine,
                lastCheckTime: Date.now()
            }
        }
    },
    
    methods: {
        // Check if internet is available before performing actions
        requiresInternet(action = null) {
            if (!navigator.onLine) {
                this.$store.dispatch('showInternetAlert');
                return false;
            }
            
            // Additional server connectivity check
            return this.verifyServerConnection()
                .then(() => {
                    this.internetConnectivity.isOnline = true;
                    this.internetConnectivity.lastCheckTime = Date.now();
                    return true;
                })
                .catch(() => {
                    this.internetConnectivity.isOnline = false;
                    this.internetConnectivity.lastCheckTime = Date.now();
                    this.$store.dispatch('showInternetAlert');
                    return false;
                });
        },
        
        // Verify server connection
        verifyServerConnection() {
            return new Promise((resolve, reject) => {
                const timeout = 3000;
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), timeout);
                
                fetch('/api/ping', {
                    method: 'HEAD',
                    signal: controller.signal,
                    cache: 'no-cache'
                })
                .then(response => {
                    clearTimeout(timeoutId);
                    if (response.ok) {
                        resolve();
                    } else {
                        reject();
                    }
                })
                .catch(() => {
                    clearTimeout(timeoutId);
                    reject();
                });
            });
        },
        
        // Wrapper for API calls with internet check
        async makeApiCall(apiCallFunction) {
            const hasInternet = await this.requiresInternet();
            if (hasInternet) {
                return apiCallFunction();
            } else {
                throw new Error('No internet connection');
            }
        }
    },
    
    mounted() {
        // Listen for online/offline events
        window.addEventListener('online', () => {
            this.internetConnectivity.isOnline = true;
            this.internetConnectivity.lastCheckTime = Date.now();
        });
        
        window.addEventListener('offline', () => {
            this.internetConnectivity.isOnline = false;
            this.internetConnectivity.lastCheckTime = Date.now();
        });
    }
}
